<?php

class ForumSystem_Node extends System_Node {
   var $struct = array( "node_type"       => "ForumSystem_Node",

                        "title"           => array( "prop_type" => "text",
                                                    "values"    => ""));

   var $allowed_children_types = array("Forum" => "Forum_Node");
   function BrowseInfoElement ()
   {
      $div = new Tag('div');
      $div->AddElement('This area is where you create Forums');
      return $div;
   }
}

?>
