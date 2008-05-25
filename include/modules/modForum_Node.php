<?

   class Forum_Node extends Node {
      var $struct = array( "node_type" => "Forum_Node",
                           "title"     => array( "prop_type" => "text",
                                                "values"    => "New Forum"),
                           "body"   => array( "prop_type" => "textarea",
                                                 "values"    => ""),

                           "active_by_default" => array( "prop_type" => "callback",
                                                         "name"    => "activeByDefaultElement"));

      var $allowed_children_types = array ( "Forum" => "Forum_Node",
                                            "Thread" => "Thread_Node");

      var $active_by_default_options = array('1'=>'True', '0'=>'False');

      function activeByDefaultElement ($name, $value)
      {
         $stateSelect = new Tag('select', array('name'=>'struct['.$name.']'));
         foreach ($this->active_by_default_options as $abrv=>$full) {
            $option = new Tag('option', array('value'=>$abrv));
            if ($value == $abrv) {
               $option->SetAttribute('selected', 'selected');
            }
            $option->AddElement($full);
            $stateSelect->AddElement($option);
         }
         return $stateSelect;
      }

      function sort_nodes_by_type ($a)
      {
         if ($a->struct['node_type'] == $b->struct['node_type']) {
	         return 0;
	      } else {
	         switch ($a->struct['node_type']):
               case "Forum_Node":
	               return -1;
	            break;

               case "Thread_Node":
                  return 1;
               break;
            endswitch;
         }
      }

      function DefaultHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
      {
         if ($this->active_by_default->value) {
            $this->ActiveHtmlData($container, $this);
         } else {
            $totals = @array_count_values($this->get_num_of_properties_in_children('node_type'));
            $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$this->id));
            $a->AddElement($this->title->value.'&nbsp;('.intval($totals['Thread_Node']).')');
            $container->AddElement($a);
         }
      }

      function ActiveHtmlData ($container, $parentNode = NULL)
      {
         $prev_type = NULL;

         $status = SiG_Session::Instance()->Request('action', NULL);

            switch ($status) {
               case 'Post':
                  $nodeValues = SiG_Session::Instance()->Request('nodeValues');
                  $struct = SiG_Session::Instance()->Request('struct');
                  $node = call_user_func_array(array($struct['node_type'], 'create'), array($nodeValues, $struct['node_type']));
                  $node->new = TRUE;
                  $node->update($nodeValues, $struct);
               break;
            }

         $children = $this->get_array_of_children();
         //usort($children, array($this, "sort_nodes_by_type"));


         switch ($this->struct['node_type']):
            case "Forum_Node":
               //$parentNode = Node::new_instance($this->parent_id);
               //if ($parentNode->parent_id) {
               //   $thisA = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$parentNode->id));
               //   $thisA->AddElement('Return to ... '.$parentNode->title->value);
               //   $container->AddElement($thisA);
               //}
            break;

            case "Thread_Node":
            break;
         endswitch;


         $table = new Tag('table');
         foreach ($children as $child) {
            if ($child->struct['node_type'] != $prev_type && ($this->id == $parentNode->id)) {
               if(isset($prev_type)) {
                  $container->AddElement($table);
                  $table = new Tag('table');
               }

               switch ($child->struct['node_type']):
                  case "Forum_Node":
                     $tr = new Tag('tr');
                     $titleCell = new Tag('td', array('colspan'=>'2'));
                     $titleCell->AddElement('Title');
                     $forumsCell = new Tag('td');
                     $forumsCell->AddElement('Forums');
                     $threadsCell = new Tag('td');
                     $threadsCell->AddElement('Threads');
                     $lastpostCell = new Tag('td');
                     $lastpostCell->AddElement('Last Post');
                     $modCell = new Tag('td');
                     $modCell->AddElement('Moderator');

                     $tr->AddElement($titleCell);
                     $tr->AddElement($forumsCell);
                     $tr->AddElement($threadsCell);
                     $tr->AddElement($lastpostCell);
                     $tr->AddElement($modCell);
                 break;

                 case "Thread_Node":
                     $tr = new Tag('tr');
                     $userCell = new Tag('td');
                     $userCell->AddElement('User');
                     $commentCell = new Tag('td');
                     $commentCell->AddElement('Comment');

                     $tr->AddElement($userCell);
                     $tr->AddElement($commentCell);
                 break;
               endswitch;
               $table->AddElement($tr);
            }

            $totals = @array_count_values($child->get_num_of_properties_in_children('node_type'));

            switch ($child->struct['node_type']):
               case "Forum_Node":
                     $tr = new Tag('tr');

                     $iconTd = new Tag('td');
                     $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$child->id));
                     $a->AddElement('<img src="images/folder.png"/>');
                     $iconTd->AddElement($a);

                     $titleTd = new Tag('td');
                     $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$child->id));
                     $a->AddElement($child->title->value);
                     //$a->AddElement($body);
                     $p = new Tag('br');
                     $p->AddElement($child->body->value);
                     $titleTd->AddElement($a);
                     $titleTd->AddElement($p);

                     $forumTotalsTd = new Tag('td');
                     if (array_key_exists('Forum_Node', $totals)) {
                        $forumTotalsTd->AddElement(intval($totals['Forum_Node']));
                     } else {
                        $forumTotalsTd->AddElement("0");
                     }
            

                     $threadTotalsTd = new Tag('td');
                     if (array_key_exists('Thread_Node', $totals)) {
                        $threadTotalsTd->AddElement(intval($totals['Thread_Node']));
                     } else {
                        $threadTotalsTd->AddElement("0");
                     }


                     $latest = $child->get_child($child->get_num_of_children()-1);
                     $latestTd = new Tag('td');
                     if (is_object($latest)) {
                        $timestampDiv = new Tag('div');
                        $timestampDiv->AddElement(date("m/d", $latest->timestamp));
                        $latestTd->AddElement($timestampDiv);
                        $latestTd->AddElement(SiG_Session::GetUserProfileElement($latest->user_id));
                     } else {
                        $latestText = "no posts";
                        $latestTd->AddElement($latestText);
                     }


                     $modTd = new Tag('td');
                     $modTd->AddElement(SiG_Session::GetUserProfileElement($child->user_id));

                     $tr->AddElement($iconTd);
                     $tr->AddElement($titleTd);
                     $tr->AddElement($forumTotalsTd);
                     $tr->AddElement($threadTotalsTd);
                     $tr->AddElement($latestTd);
                     $tr->AddElement($modTd);
               break;

               case "Thread_Node":
                  $trMain = new Tag('tr');
                  $trSub = new Tag('tr');

                  $userTd = new Tag('td', array('class'=>'profile', 'rowspan'=>'2'));
                  $userTd->AddElement('User Profile');

                  $commentTd = new Tag('td');
                  $commentTd->AddElement($child->body->value);

                  $titleTd = new Tag('td');
                  $titleTd->AddElement($child->title->value);

                  $trMain->AddElement($userTd);
                  $trMain->AddElement($titleTd);
                  $trSub->AddElement($commentTd);

                  $tr = new TagGroup(NULL);//array($trMain, $trSub);
                  $tr->AddElement($trMain);
                  $tr->AddElement($trSub);

               break;
            endswitch;
            $prev_type = $child->struct['node_type'];
            $table->AddElement($tr);
         }
         
         $container->AddElement($table);

         $ptot = @array_count_values($this->get_num_of_properties_in_children('node_type'));
         if (!array_key_exists('Forum_Node', $ptot)) {
            $form = new Tag('form', array('action'=>SiG_Plugin_Controller::Permalink(), 'method'=>'post'));
                  //$activeIdElement = new Tag('input', array('type'=>'hidden', 'name'=>'active_id', 'value'=>$active
            $node = New Thread_Node(NULL);
            $node->primary_parent_id = $this->id;
                //$node->user_id = 1;//$GLOBALS[userData][user_id];
                 //$node->group_id = 1;//$GLOBALS[userData][group_id];
            $node->HtmlForm($form);
            $voteButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Post'));
            $activeIdElement = new Tag('input', array('type'=>'hidden', 'name'=>'active_id', 'value'=>$this->id));
            $form->AddElement($activeIdElement);
            $form->AddElement($voteButton);
            $makePost = new Tag('h3');
            $makePost->AddElement('Make a Comment');
            $container->AddElement($makePost);
            $container->AddElement($form);
         }

      }
   }

?>
