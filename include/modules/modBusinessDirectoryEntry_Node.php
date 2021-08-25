<?php

class BusinessDirectoryEntry_Node extends Node {
   var $struct = array(
                       "node_type" => "BusinessDirectoryEntry_Node",
                       "business_name"     => array(
                                            "prop_type" => "text",
                                            "values"    => "New Business Entry"),
                       "contact_name" => array(
                                            "prop_type" => "text",
                                            "values"    => ""),
                       "status" => array(
                                      "prop_type" => "callback",
                                      "name" => "statusSelectElement"),
                       "listing_type" => array(
                                            "prop_type" => "callback",
                                            "name" => "listing_typeSelectElement"),
                       "categories" => array(
                                          "prop_type" => "callback",
                                          "name" => "categories"),
                       "street_address" => array(
                                              "prop_type" => "text",
                                              "values" => ""),
                       "city" => array(
                                    "prop_type" => "text",
                                    "values" => ""),
                       "state" => array(
                                     "prop_type" => "callback",
                                     "name" => "stateSelectElement"),
                       "numbers" => array(
                                            "prop_type" => "textarea",
                                            "values" => ""),
                       "urls" => array(
                                    "prop_type" => "textarea",
                                    "values" => ""),
                       "company_brief" => array(
                                             "prop_type" => "textarea",
                                             "values" => ""),
                       "company_spotlight" => array(
                                             "prop_type" => "textarea",
                                             "values" => ""),
                       "company_logo" => array(
                                            "prop_type" => "callback",
                                            "name" => "company_logo"),
                       "company_picture" => array(
                                               "prop_type" => "callback",
                                               "name" => "company_picture")
                      );

   var $statusOptions = array('1'=>'Not Approved', '2'=>'Approved');
   var $listing_typeOptions = array('1'=>'Free', '2'=>'Professional');

   var $states = array(
           'AL' => 'Alabama',
           'AK' => 'Alaska',
           'AZ' => 'Arizona',
           'AR' => 'Arkansas',
           'CA' => 'California',
           'CO' => 'Colorado',
           'CT' => 'Connecticut',
           'DE' => 'Delaware',
           'DC' => 'Dist Columbia',
           'FL' => 'Florida',
           'GA' => 'Georgia',
           'HI' => 'Hawaii',
           'ID' => 'Idaho',
           'IL' => 'Illinois',
           'IN' => 'Indiana',
           'IA' => 'Iowa',
           'KS' => 'Kansas',
           'KY' => 'Kentucky',
           'LA' => 'Louisiana',
           'ME' => 'Maine',
           'MD' => 'Maryland',
           'MA' => 'Massachusetts',
           'MI' => 'Michigan',
           'MN' => 'Minnesota',
           'MS' => 'Mississippi',
           'MO' => 'Missouri',
           'MT' => 'Montana',
           'NE' => 'Nebraska',
           'NV' => 'Nevada',
           'NH' => 'New Hampshire',
           'NJ' => 'New Jersey',
           'NM' => 'New Mexico',
           'NY' => 'New York',
           'NC' => 'North Carolina',
           'ND' => 'North Dakota',
           'OH' => 'Ohio',
           'OK' => 'Oklahoma',
           'OR' => 'Oregon',
           'PA' => 'Pennsylvania',
           'RI' => 'Rhode Island',
           'SC' => 'South Carolina',
           'SD' => 'South Dakota',
           'TN' => 'Tennessee',
           'UT' => 'Utah',
           'VT' => 'Vermont',
           'VA' => 'Virginia',
           'WA' => 'Washington',
           'WV' => 'West Virginia',
           'WI' => 'Wisconsin',
           'WY' => 'Wyoming'
    );

   function TitleValue ()
   {
      return $this->get_property_member('business_name', 'value');
   }

   function statusSelectElement ($name, $value)
   {
      $stateSelect = new Tag('select', array('name'=>'struct['.$name.']'));
      foreach ($this->statusOptions as $abrv=>$full) {
         $option = new Tag('option', array('value'=>$abrv));
         if ($value == $abrv) {
            $option->SetAttribute('selected', 'selected');
         }
         $option->AddElement($full);
         $stateSelect->AddElement($option);
      }
      return $stateSelect;
   }

   function listing_typeSelectElement ($name, $value)
   {
      $stateSelect = new Tag('select', array('name'=>'struct['.$name.']'));
      foreach ($this->listing_typeOptions as $abrv=>$full) {
         $option = new Tag('option', array('value'=>$abrv));
         if ($value == $abrv) {
            $option->SetAttribute('selected', 'selected');
         }
         $option->AddElement($full);
         $stateSelect->AddElement($option);
      }
      return $stateSelect;
   }

   function stateSelectElement ($name, $value)
   {
      $stateSelect = new Tag('select', array('name'=>'struct['.$name.']'));
      $option = new Tag('option', array('value'=>''));
      $option->AddElement('Select State');
      $stateSelect->AddElement($option);
      foreach ($this->states as $abrv=>$full) {
         $option = new Tag('option', array('value'=>$abrv));
         if ($value == $abrv) {
            $option->SetAttribute('selected', 'selected');
         }
         $option->AddElement($full);
         $stateSelect->AddElement($option);
      }
      return $stateSelect;
   }

   function company_logo ($name, $value)
   {
      return Image_Node::ImageUploadElement($this, $name, $value);
   }

   function company_picture ($name, $value)
   {
      return Image_Node::ImageUploadElement($this, $name, $value);
   }

   function categories ($name, $value)
   {
      $childrenIds = array();
      if ($this->id) {
         $importedNodesElement = new Tag('select', array('name'=>'nodes_to_detach[]', 'multiple'=>'multiple', 'size'=>'10'));

         foreach ($this->get_array_of_children() as $child) {
            $pid = $child->GetParentId(0);
            $pa = Node::new_instance($pid);
            $option = new Tag('option', array('value'=>$child->id));
            $option->AddElement($pa->get_property_member('title', 'value').'-'.$child->title->value);
            $importedNodesElement->AddElement($option);
            $childrenIds[] = $child->id;
         }

         $div = new Tag('div');
         $selectElement = new Tag('select', array('name'=>'nodes_to_attach[]', 'multiple'=>'multiple', 'size'=>'10'));
         $parentNode = Node::new_instance($this->GetParentId(0));
         $parentParentNode = Node::new_instance($parentNode->GetParentId(0));
         $parentParentNode->get_child(0)->RecursiveOptionElement(array(), $childrenIds, $selectElement, 0, 
                            array('BusinessDirectoryEntries_Node'), TRUE); //, 0, 

                                           //array());
         $attachButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Attach'));
         $detachButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Detach'));
         $div->AddElement($selectElement);
         $div->AddElement($attachButton);
         $div->AddElement($detachButton);
         $div->AddElement($importedNodesElement);
         return $div;
      } else {
         $p = new Tag('p');
         $p->AddElement('Select Categories After creation');
         return $p;
      }
   }

   function doAttach ($container)
   {
      $nodesToAttach = SiG_Session::Instance()->Request('nodes_to_attach', NULL);
      if ($nodesToAttach) {
         foreach ($nodesToAttach as $nodeId) {
            $node = Node::new_instance($nodeId);
            $node->new = TRUE;
            $node->AttachTo($this->id);
         }
      }

      $this->doEdit($container);
   }

   function doDetach ($container)
   {
      $nodesToDetach = SiG_Session::Instance()->Request('nodes_to_detach', NULL);
      if ($nodesToDetach) {
         foreach ($nodesToDetach as $nodeId) {
            $node = Node::new_instance($nodeId);
            $node->new = TRUE;
            $node->DetachFrom($this->id);
         }
      }

      $this->doEdit($container);
   }
}

?>
