<?php

   class Node {
      var $id;
      var $parent_id;
      var $node_type;
      var $site_id;
      var $user_id;
      var $group_id;
      var $timestamp;
      var $perms;
      var $umask;
      var $struct = array( "node_type" => "Node",
                           "title"     => array( "prop_type" => "text",
                                                 "values"    => ""));

      var $allowed_children_types = array();

      var $acting_parent;


      var $new = FALSE;

      var $default_priority = 0;

      function Node ($id, $acting_parent = 0, $is_cached=TRUE, $cache_timeout=0)
      {
         $this->id = $id;
         $this->acting_parent = $acting_parent;
         $this->preload_cache();
      }

      function new_instance ($node_id, $acting_parent = 0)
      {
         if (is_numeric($node_id) && $node_id > 0) {
            $classname = New Property ($node_id, 'node_type');
            if (strlen($classname->value)) {
               $nodetype = $classname->value;
               $obj = new $nodetype($node_id, $acting_parent);
               return $obj;
            } else {
               return New Node($node_id);
            }
         } else {
            return New Node(0);
         }
      }

      function preload_cache ()
      {
         $query = "SELECT "
                . SiG_Controller::GetTablePrefix()."node_data.*, "
                . SiG_Controller::GetTablePrefix()."node_rel.parent_id, "
                . SiG_Controller::GetTablePrefix()."node_rel.orderby "
                . "FROM "
                . SiG_Controller::GetTablePrefix()."node_data "
                . "JOIN "
                . SiG_Controller::GetTablePrefix()."node_rel "
                . "ON "
                . SiG_Controller::GetTablePrefix()."node_data.id="
                . SiG_Controller::GetTablePrefix()."node_rel.node_id "
                . "WHERE "
                . SiG_Controller::GetTablePrefix()."node_data.id='".$this->id."' "
                . "AND "
                . SiG_Controller::GetTablePrefix()."node_rel.parent_id='".$this->acting_parent."' "
                . "LIMIT 1";
         $results = new Query($query);
         if ($data = $results->execute()) {
            while (list($key, $val) = each ($data)) {
               $this->$key = $val;
            }
         }

         $this->owner_perms = substr ($this->perms, 0, 1);
         $this->group_perms = substr ($this->perms, 1, 1);
         $this->public_perms = substr ($this->perms, 2, 1);

         $this->umask_owner_perms = substr ($this->umask, 0, 1);
         $this->umask_group_perms = substr ($this->umask, 1, 1);
         $this->umask_public_perms = substr ($this->umask, 2, 1);

         while (list($key, $name) = each($this->struct)) {
            $this->get_property_member($key,'value');  // might need to do more stuff
         }
      }

      function get_property_member ($property, $member = 'value')
      {
         $this->$property = new Property ($this->id, $property);
         return ($this->$property->$member);
      }

      function get_array_of_properties ()
      {
         return $this->struct;
      }

      function get_array_of_parents ()
      {
         if (!isset($this->arrayOfParents)) {
            if ($this->id) {
               $parentNode = Node::new_instance($this->parent_id);
               $this->arrayOfParents = $parentNode->get_array_of_peers();
            } else {
               $this->arrayOfParents = NULL;
            }
         }
         return $this->arrayOfParents;
      }

      function get_num_of_parents ()
      {
         if (!isset($this->numOfParents)) {
            $this->numOfParents = count($this->get_array_of_parents());
         }
         return $this->numOfParents;
      }

      function get_array_of_children ($sortProperty = NULL, $sortDirection = NULL)
      {
         if (!isset($this->arrayOfChildren)) {
            $this->arrayOfChildren = array();
            if ($sortProperty) {
               $propertyType = new Property_Type($sortProperty);
               $join = "JOIN "
                     . SiG_Controller::GetTablePrefix()."property_data "
                     . "ON "
                     . SiG_Controller::GetTablePrefix()."property_data.node_id="
                     . SiG_Controller::GetTablePrefix()."node_rel.node_id ";

               $where = "AND "
                      . SiG_Controller::GetTablePrefix()."property_data.type_id='".$propertyType->id."' ";

               $order = "ORDER BY "
                      . SiG_Controller::GetTablePrefix()."property_data.value "
                      . $sortDirection;
            } else {
               $join = "";
               $where = "";
               $order = "ORDER BY orderby ASC";
            }

            $query = "SELECT "
                   . SiG_Controller::GetTablePrefix()."node_rel.node_id "
                   . "FROM "
                   . SiG_Controller::GetTablePrefix()."node_rel "
                   . $join
                   . "WHERE "
                   . SiG_Controller::GetTablePrefix()."node_rel.parent_id='".$this->id."' "
                   . "AND "
                   . SiG_Controller::GetTablePrefix()."node_rel.site_id='".SiG_Controller::GetSiteId()."' "
                   . $where
                   . $order;
            $results = New Query ($query);
            while ($data = $results->execute()) {
               $returnNode = Node::new_instance($data->node_id, $this->id);
               //TODO if ($returnNode->have_read_perms()) {
                  $this->arrayOfChildren[] = $returnNode;
               //}
            }
         }
         return $this->arrayOfChildren;
      }

      function get_num_of_children ()
      {
         if (!isset($this->numOfChildren)) {
            $this->numOfChildren = count($this->get_array_of_children());
         }
         return $this->numOfChildren;
      }


      function get_array_of_peers ($parent_id)
      {
         if (!isset($this->arrayOfPeers)) {
            $parentNode = Node::new_instance($parent_id);
            $this->arrayOfPeers = $parentNode->get_array_of_children();
         }
         return $this->arrayOfPeers;
      }

      function get_num_of_peers ($parent_id)
      {
         if (!isset($this->numOfPeers)) {
            $this->numOfPeers = count($this->get_array_of_peers($parent_id));
         }
         return $this->numOfPeers;
      }

      function TitleValue ()
      {
         return $this->get_property_member('title', 'value');
      }

      function BrowseTitleElement ()
      {
         return $this->TitleValue(); //$this->get_property_member('title', 'value');
      }

      function BreadcrumbTitleElement ()
      {
         return $this->get_property_member('title', 'value');
      }

      function IsAncestor ($id)
      {
         $match = FALSE;
         foreach ($this->bread_crumb() as $crumb) {
            if ($crumb['id'] == $id) {
               $match = TRUE;
            }
         }
         return $match;
      }

      function bread_crumb ()
      {
         $return = array();
         if ($this->id) {
            $parents = $this->GetParentIds();
            $return[] = array('id' => $this->id, 'parents' => ($parents), 'title' => $this->BreadcrumbTitleElement()); 
            //$parents = $this->GetParentIds();
            $parentId = $parents[0];
            $parentNode = Node::new_instance($parentId);
            $arrayToMerge = $parentNode->bread_crumb();
            $return = array_merge($return, $arrayToMerge);
         }

         return $return;
      }

      function get_child ($number)
      {
         if ($this->get_num_of_children()) {
            return $this->arrayOfChildren[$number];
         } else {
            return NULL;
         }
      }

      function GetParentIds ()
      {
         $parentIds = array();
         $query = "SELECT parent_id FROM ".SiG_Controller::GetTablePrefix()."node_rel "
                . "WHERE node_id='".$this->id."' "
                . "ORDER BY node_id ASC"; 
         $results = new Query($query);
         while ($data = $results->execute()) {
            $parentIds[] = $data->parent_id;
         }

         return $parentIds;
      }

      function GetParentId ($priority = 0)
      {
         $parentIds = $this->GetParentIds();
         if (isset($parentIds[$priority])) {
            return $parentIds[$priority];
         } else {
            die('Unknown Parent');
         }
      }

      function AttachTo ($parent_id, $orderby = 0, $priority = 0)
      {
         $query = ($this->new 
                    ? "REPLACE ".SiG_Controller::GetTablePrefix()."node_rel SET "
                    : "UPDATE ".SiG_Controller::GetTablePrefix()."node_rel SET "
                  )
                . "site_id='".SiG_Controller::GetSiteId()."', "
                . "node_id='".$this->id."', "
                . "parent_id='".$parent_id."', "
                . "orderby='".$orderby."' "
                . ($this->new 
                    ? "" 
                    : "WHERE node_id='".$this->id."'"
                  );
         $results = new Query($query);
      }

      function DetachFrom ($parent_id)
      {
         $query = "DELETE FROM "
                . SiG_Controller::GetTablePrefix()."node_rel "
                . "WHERE "
                . "node_id='".$this->id."' "
                . "AND "
                . "parent_id='".$parent_id."'";
         $results = new Query($query);
      }

      function create ($nodeValues, $returnType)
      {
         $nodeValues['perms'] = 777;
         $nodeValues['umask'] = 777;

         $query = "INSERT INTO ".SiG_Controller::GetTablePrefix()."node_data VALUES (NULL, "
                . "'".SiG_Session::Instance()->GetUserData()->ID."', "
                . "'".'1'."', " //FIX TODO $nodeValues['group_id']."', "
                . "'".time()."', "
                . "'".$nodeValues['perms']."', "
                . "'".$nodeValues['umask']."')";
         $results = New Query($query);

         
         $newNode = new $returnType($results->insertid);
         $newNode->new = TRUE;
         foreach ($nodeValues['parent_ids'] as $orderby => $parent_id) {
            $newNode->AttachTo($parent_id, $orderby, 0);
         }

         return $newNode;
      }

      function update ($nodeValues, $newStruct = NULL)
      {
         //$nodeValues[perms] = "$nodeValues[o]"."$nodeValues[g]"."$nodeValues[n]";
         //$nodeValues[umask] = "$nodeValues[o_umask]"."$nodeValues[g_umask]"."$nodeValues[n_umask]";
         $nodeValues['perms'] = 777;
         $nodeValues['umask'] = 777;

         if ($newStruct) {
            foreach ($newStruct as $key => $value) {
               $this->$key = New Property ($this->id, $key);
               $this->$key->set_value($value, $this->new);
            }
         }

         $query = "UPDATE ".SiG_Controller::GetTablePrefix()."node_data SET "
                . "user_id='".SiG_Session::Instance()->GetUserData()->ID."', "
                . "group_id='".'1'."', " //FIX TODO $nodeValues['group_id']."', "
                . "timestamp='".time()."', "
                . "perms='".$nodeValues['perms']."', "
                . "umask='".$nodeValues['umask']."' "
                . "WHERE id='{$this->id}'";
         $results = New Query($query);
         if ($results->affected_rows) {
            return TRUE;
         } else {
            return FALSE;
         }
      }

      function doSave ($container)
      {
         $nodeValues = SiG_Session::Instance()->Request('nodeValues');
         $struct = SiG_Session::Instance()->Request('struct');

         if ($this->update($nodeValues, $struct)) {
            $this->doEdit($container);
         }
      }

      function doDone ($container)
      {
         $parentNode = Node::new_instance($this->GetParentId());
         $parentNode->doBrowse($container);
      }

      function doNew ($container)
      {
         $currentId = SiG_Session::Instance()->Request('current_id');
         $nodeValues = SiG_Session::Instance()->Request('nodeValues');
         $struct = SiG_Session::Instance()->Request('struct');

         if ( !empty($struct['node_type']) ) {
            $class =  $struct['node_type'];
            $node = New $class(NULL);
            $node->primary_parent_id = $currentId;
            //$node->group_id = 1;//$GLOBALS[userData][group_id];
            $node->HtmlForm($container);
            $buttonsFieldset = new Tag('fieldset');
            $buttonsLegend = new Tag('legend');
            //$buttonsLegend->AddElement('Actions');
            //$buttonsFieldset->AddElement($buttonsLegend);
            $createButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Create'));
            $cancelButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Cancel'));
            $buttonsFieldset->AddElement($createButton);
            $buttonsFieldset->AddElement($cancelButton);
            $container->AddElement($buttonsFieldset);
         }
      }

      function doCancel ($container)
      {
         $nodeValues = SiG_Session::Instance()->Request('nodeValues');
         $parent_ids = $nodeValues['parent_ids'];

         $node = Node::new_instance($parent_ids[0]);

         $node->doBrowse($container);
      }

      function doCreate ($container)
      {
         $nodeValues = SiG_Session::Instance()->Request('nodeValues');
         $struct = SiG_Session::Instance()->Request('struct');

         $node = call_user_func_array(array($struct['node_type'], 'create'), array($nodeValues, $struct['node_type']));
         $node->update($nodeValues, $struct);
         $node->doEdit($container);
      }

      function doDelete ($container)
      {
         $currentId = SiG_Session::Instance()->Request('current_id');
         $currentNode = Node::new_instance($currentId);

         $p = new Tag('p');

         if ($this->id) {
            if ($this->delete()) {
               $p->SetAttribute('class', 'sig_notice_ok');
               $p->AddElement('Successfully Deleted!');
               $container->AddElement($p);
               $currentNode->doBrowse($container);
            } else {
               $p->SetAttribute('class', 'sig_notice_error');
               $p->AddElement('System Error - Node::doDelete()');
               $container->AddElement($p);
            }
         } else {
            $p->SetAttribute('class', 'sig_notice_warning');
            $p->AddElement('Error you must select an element to delete');
            $container->AddElement($p);
            $currentNode->doBrowse($container);
         }
      }

      function delete ()
      {
         $deleteSystemNode = Node::GetSystemNodeByTitle('Recycle Bin');
         $deletedNode = SiG_Admin_Model::CreateNode('Deleted_Node', 'Deleted: '.$this->TitleValue(), $deleteSystemNode->id, 0);
         $this->AttachTo($deletedNode->id);
         return TRUE;
      }

      function remove ()
      {
         die('Irreversable');
         $count = $this->get_num_of_children();
         if ($count) {
            $arrayOfChildren = $this->get_array_of_children();
            for ($i=0; $i<$count; $i++) {
               if ($arrayOfChildren[$i]->delete()) {
                  unset ($childNode);
               } else {
                  echo "error maybeeeee";
               }
            }
         }

         foreach ($this->struct as $key=>$default) {
            $prop = New Property ($this->id, $key);
            $prop->erase();
         }

         $query = "DELETE FROM ".SiG_Controller::GetTablePrefix()."node_data WHERE id='{$this->id}'";
         $results = New Query($query);
         if (!$results->affected_rows) {
            echo "error?";
         } else {
            return $results;
         }
      }

      function get_cache ()
      {
         //TODO
         return;
         if ($this->cacheHandler->cacheObjectTimedOut()) {
            $this->update_cache();
         } else {
            //$this = unserialize($this->cacheHandler->loadCacheObject());
         }
      }

      function update_cache ()
      {
         $this->preload_cache();
         $this->cacheHandler->updateCacheObject(serialize($this));
      }

      function remove_cache ()
      {
         if ($this->is_cached) {
            $this->cacheHandler->deleteCacheObject();
         }
      }

      function HtmlForm ($container)
      {
         if ($this->id) {
            $container->AddElement($this->BreadcrumbFieldsetElement());
         }

         $nodeId = new Tag('input', array('type'=>'hidden', 'name'=>'node_ids[]', 'value'=>$this->id));
         $groupId = new Tag('input', array('type'=>'hidden', 'name'=>'nodeValues[group_id]', 'value'=>$this->group_id));
         $container->AddElement($nodeId);
         $container->AddElement($groupId);

         $parentFieldset = new Tag('fieldset', array('class'=>'sig_form_hidden'));
         $parentLegend = new Tag('legend');
         $parentLegend->AddElement('Parents');
         $parentFieldset->AddElement($parentLegend);

         if (isset($this->primary_parent_id)) {
            $parentIds[] = $this->primary_parent_id;
         } else {
            $parentIds = $this->GetParentIds();
         }

         $parentSelectElement = new Tag('select', array(
                                                     'name'=>'nodeValues[parent_ids][]', 
                                                     'multiple'=>'multiple', 
                                                     'size'=>'10'));
         $rootNode = new Node(0);
         $rootNode->RecursiveOptionElement($parentIds,  
                                           array(),
                                           $parentSelectElement, 
                                           0, 
                                           array('WordpressPage_Node', 'DeleteSystem_Node'), 
                                           FALSE);
         $parentFieldset->AddElement($parentSelectElement);
         $container->AddElement($parentFieldset);

         $struct = SiG_Session::Instance()->Request('struct');
         foreach ($this->struct as $prop_name => $def) { 
            $fieldsetElement = new Tag('fieldset', array('id'=>'sig_fs_'.$prop_name));
            if ($this->id) {
               $value = $this->get_property_member($prop_name, 'value');
            } elseif (isset($struct[$prop_name])) {
               //$value = $struct[$prop_name];
               //HACK TODO FIX
               $value = "";
            } else {
               if (is_array($def) && array_key_exists('values', $def)) {
                  $value = $def['values'];
               } else {
                  $value = "TODO";
               }
            }

            $fieldName = "struct[".$prop_name."]";
            $legendElement = new Tag('legend');
            $legendElement->AddElement(str_replace('_', ' ', ucwords($prop_name)));
            $fieldsetElement->AddElement($legendElement);
            if (is_array($def)) {
               if ($def['prop_type'] == 'callback') {
                  $callback = $def['name'];
                  $formElement = $this->$callback($prop_name, $value);
               } elseif ($def['prop_type'] == 'text') {
                  $formElement = new Tag('input', array('type'=>'text', 'name'=>$fieldName, 'value'=>$value));
               } elseif ($def['prop_type'] == 'textarea') {
                  $formElement = new Tag('textarea', array('name'=>$fieldName));
                  $formElement->AddElement($value);
               } elseif ($def['prop_type'] == 'dropdown') {
                  $formElement = new Tag('p');
               } elseif (is_array($def['values'])) {
                  if (count($def['prop_type'])) {
                  $formElement = new Tag('p');
                  } else {
                  $formElement = new Tag('p');
                  }
               } else { 
                  $formElement = new Tag('p');
               }
            } elseif ($prop_name == 'node_type') {
               $fieldsetElement->AddElement($this->NodeTypeSelectElement());
                  $formElement = new Tag('p');
            }

            $fieldsetElement->AddElement($formElement);
            $container->AddElement($fieldsetElement);
         }
      }

      function doEdit ($container)
      {
         if ($this->id) {
            $this->HtmlForm($container);
            $submitFieldset = new Tag('fieldset');
            $submitLegend = new Tag('legend');
            //$submitLegend->AddElement('Actions');
            //$submitFieldset->AddElement($submitLegend);
            $saveButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Save'));
            $doneButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Done'));
            $submitFieldset->AddElement($saveButton);
            $submitFieldset->AddElement($doneButton);
            $container->AddElement($submitFieldset);
         } else {
            $p = new Tag('p');
            $p->AddElement('Error you must select a node');
            $container->AddElement($p);
         }
      }

      function get_array_of_installed_modules ()
      {
         // needs to read the modules dir!!!
         return array(//"Node" => "Node",
                      //"My Art" => "DocumentSystem_Node",
                      //"Folder" => "Folder_Node",

                      "My Documents" => "DocumentSystem_Node",
                      "Folder" => "Folder_Node",
                      "File" => "Document_Node",
                      "Paged Content" => "Content_Node",
                      "Mandelbrot Set" => "Mandel_Node",
 
                      "Calendar System" => "CalendarSystem_Node",
                      "Calendar" => "Calendar_Node",
                      "Event" => "Event_Node",

                      "Image Galleries" => "GallerySystem_Node",
                      "Gallery" => "Gallery_Node",
                      "Image" => "Image_Node",

                      "Online Polls" => "PollSystem_Node",
                      "Poll" => "Poll_Node",
                      "Vote" => "Vote_Node",

                      "Code Snippets" => "SnippetSystem_Node",
                      "Code Snippet" => "Snippet_Node",

                      "Discussion Forums" => "ForumSystem_Node",
                      "Forum" => "Forum_Node",
                      "Thread" => "Thread_Node",

                      "Business Directories" => "BusinessDirectorySystem_Node",
                      "Business Directory" => "BusinessDirectory_Node",
                      "Business Directory Categories" => "BusinessDirectoryCategories_Node",
                      "Business Directory Category" => "BusinessDirectoryCategory_Node",
                      "Business Directory Entries" => "BusinessDirectoryEntries_Node",
                      "Business Directory Entry" => "BusinessDirectoryEntry_Node",

                      "Pages" => "WordpressPageSystem_Node",
                      "Page" => "WordpressPage_Node",

                      "Recycle Bin" => "DeleteSystem_Node",
                      );
      }

      function NodeTypeSelectElement ($parseAllowed = false)
      {
         if ($parseAllowed && count($this->allowed_children_types)) {
            $types = $this->allowed_children_types;
            reset($types);
            $default = current($types);
         } else {
            $types = $this->get_array_of_installed_modules();
            $default = $this->struct['node_type'];
         }

         $select = new Tag('select', array('name'=>'struct[node_type]'));

         foreach ($types as $key => $value) {
            $option = new Tag('option', array('value'=>$value));
            $option->AddElement($key);
            if ($value == $default) {
               $option->SetAttribute('selected', 'true');
            }
            $select->AddElement($option);
         }

         return $select;
      }

      static function RequestedInstances ()
      {
         static $nodes;

         if (!isset($nodes)) {
            $nodes = array();
            $node_ids = SiG_Session::Instance()->Request('node_ids', array(0));

            foreach ($node_ids as $node_id) {
               $nodes[$node_id] = Node::new_instance($node_id);
            }
         }

         return $nodes;
      }

      function RecursiveOptionElement ($selected,$skip,$container,$depth=0,$filterClasses=array(),$disableSystems=TRUE)
      {
         foreach ($this->get_array_of_children() as $child) {
            $class = get_class($child);
            if (!in_array($class, $filterClasses) && !in_array($child->id, $skip)) {
               $option = new Tag('option', array('value'=>$child->id));
               if ($disableSystems && $depth == 0) {
                  $option->SetAttribute('disabled', 'true');
               }
               if (in_array($child->id, $selected)) {
                  $option->SetAttribute('selected', 'selected');
               }
               $option->AddElement(str_repeat('-', $depth).$child->TitleValue());
               $container->AddElement($option);
               $child->RecursiveOptionElement ($selected, $skip, $container, $depth + 1, $filterClasses, $disableSystems);
            }
         }
      }

      function doBrowse ($container)
      {
         $currentId = new Tag('input', array('type'=>'hidden', 'name'=>'current_id', 'value'=>$this->id));

         $container->AddElement($currentId);
         $container->AddElement($this->BreadcrumbFieldsetElement());
         $container->AddElement($this->ListingFieldsetElement());

         if ($this->id) {
            $container->AddElement($this->SubmitFieldsetElement());
         }
      }

      function BreadcrumbFieldsetElement ()
      {
         $breadcrumbFieldset = new Tag('fieldset');
         $aA = (new Tag('a', array('href'=>'index.php')));
         $aA->AddElement('Site');
         $breadcrumbLegend = new Tag('legend');
         $breadcrumbLegend->AddElement($aA);
         $breadcrumbFieldset->AddElement($breadcrumbLegend);
         $breadcrumbFieldset->AddElement(' / ');
         $a = (new Tag('a', array('href'=>'?model=Explorer')));
         $a->AddElement('Explorer');
         $breadcrumbFieldset->AddElement($a);
         $breadcrumbs = array_reverse($this->bread_crumb());
         foreach ($breadcrumbs as $crumb) {
            $breadcrumbFieldset->AddElement(' / ');
            $a = (new Tag('a', array('href'=>'?page=sig&model=Explorer&node_ids[]='.$crumb['id'])));
            $a->AddElement($crumb['title']);
            $breadcrumbFieldset->AddElement($a);
         }
 
         return $breadcrumbFieldset;
      }
/*
      function BreadcrumbElement ()
      {
         $breadcrumbFieldset = new Tag('div', array('id'=>'breadcrumb'));
         $aA = (new Tag('a', array('href'=>'index.php')));
         $aA->AddElement('Home');
         $breadcrumbFieldset->AddElement(' / ');
         $breadcrumbs = array_reverse($this->bread_crumb());
         foreach ($breadcrumbs as $crumb) {
            $breadcrumbFieldset->AddElement(' / ');
            $a = (new Tag('a', array('href'=>'?active_id='.$crumb['id'])));
            $a->AddElement($crumb['title']);
            $breadcrumbFieldset->AddElement($a);
         }
 
         return $breadcrumbFieldset;
      }
*/
 
      function ListingFieldsetElement ()
      {
         $listingFieldset = new Tag('fieldset');
         //$listingLegend = new Tag('legend');
         //$listingLegend->AddElement('Listing');
         //$listingFieldset->AddElement($listingLegend);
         $listingList = new Tag('ul', array('id'=>'listingList'));
         if ($this->get_num_of_children()) {
            foreach ($this->get_array_of_children() as $child) {
               SiG_Session::Dispatch($listingList, $child, 'view', 'Listing', 'view');
            }
         }
         $listingFieldset->AddElement($listingList);
         return $listingFieldset;
      }

      function SubmitFieldsetElement ()
      {
         $submitFieldset = new Tag('fieldset');
         $submitLegend = new Tag('legend');
         //$submitLegend->AddElement('Actions');
         //$submitFieldset->AddElement($submitLegend);
         $editButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Edit'));
         $deleteButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Delete'));
         $repairButton = new Tag('input', array(
                                             '_onclick'=>"alert(Sortable.serialize('listingList')); return false;", 
                                             'type'=>'submit', 'name'=>'action', 'value'=>'Set Order'));
         $newButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'New'));
         $submitFieldset->AddElement($editButton);
         $submitFieldset->AddElement($deleteButton);
         $submitFieldset->AddElement($repairButton);
         if (count($this->allowed_children_types)) {
         $submitFieldset->AddElement($newButton);
         $submitFieldset->AddElement($this->NodeTypeSelectElement(TRUE));
         }
         return $submitFieldset;
      }

      function viewListing ($container)
      {
         $div = new Tag('li', array('id'=>'id_'.$this->id, 'class'=>'orderable'));
         $idInput = new Tag('input', array('type'=>'checkbox', 'name'=>'node_ids[]', 'value'=>$this->id));
         $div->AddElement($idInput);
         $obInput = new Tag('input', array(
                                        'type'=>'text', 
                                        'name'=>'orderbys['.$this->id.']', 
                                        'value'=>$this->orderby, 
                                        'size'=>'2',
                                        'class'=>'sig_form_hidden'));
         $div->AddElement($obInput);
         $title = $this->BrowseTitleElement();
         $a = new Tag('a', array('href'=>'?page=sig&model=Explorer&node_ids[]='.$this->id));
         $a->AddElement($title);
         $div->AddElement($a);
         $container->AddElement($div);
      }

      function get_num_of_properties_in_children ($property)
      {
         $totals = array();
         foreach ($this->get_array_of_children() as $child) {
            $value = $child->get_property_member($property, 'value');
            $totals[] = $value;
            $totals = array_merge($child->get_num_of_properties_in_children($property), $totals);
            unset($child);
         }
         return $totals;
      }

      function GetSystemNodeByTitle ($title)
      {
         $nodes = array();

         $titleProperty = new Property_Type('title');
         $nodes = $titleProperty->get_array_of_nodes_having($title, 'System_Node');
         foreach ($nodes as $node) {
            if ($node->parent_id == 0) {
               return $node;
            }
         }
      }

      function doSet_Order ($container)
      {
         $orderbys = SiG_Session::Instance()->Request('orderbys');
         $currentId = SiG_Session::Instance()->Request('current_id');
         $parentNode = Node::new_instance($currentId);
         foreach ($orderbys as $id => $orderBy) {
            $childNode = Node::new_instance($id);
            $childNode->SetOrderBy($parentNode->id, $orderBy);
         }

         $parentNode->doBrowse($container);
      }

      function SetOrderBy ($parentId, $orderBy)
      {
         $query = "UPDATE "
                . SiG_Controller::GetTablePrefix()."node_rel "
                . "SET "
                . "orderby=".$orderBy." "
                . "WHERE "
                . "node_id='".$this->id."' "
                . "AND "
                . "parent_id='".$parentId."'";
         $qhandle = New Query($query);
      }
   }

?>
