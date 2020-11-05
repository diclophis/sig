<?php

class DeleteSystem_Node extends System_Node {
   var $struct = array( "node_type"       => "DeleteSystem_Node",

                        "title"           => array( "prop_type" => "text",
                                                    "values"    => ""));

   var $allowed_children_types = array();

   function BrowseInfoElement ()
   {
      $div = new Tag('div');
      $div->AddElement('This area is where your deleted Nodes go');
      return $div;
   }

   function SubmitFieldsetElement ()
   {
      $submitFieldset = new Tag('fieldset');
      $submitLegend = new Tag('legend');
      //$submitLegend->AddElement('Actions');
      //$submitFieldset->AddElement($submitLegend);
      $restoreButton = new Tag('input', array('disabled'=>'true', 'type'=>'submit', 'name'=>'action', 'value'=>'Restore'));
      //$removeButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Remove'));
      $submitFieldset->AddElement($restoreButton);
      return $submitFieldset;
   }
}

?>
