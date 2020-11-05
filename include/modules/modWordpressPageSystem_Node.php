<?php

class WordpressPageSystem_Node extends System_Node {
   var $struct = array( "node_type"       => "WordpressPageSystem_Node",

                        "title"           => array( "prop_type" => "text",
                                                    "values"    => ""));

   var $allowed_children_types = array("Wordpress Page" => "WordpressPage_Node");

   function BrowseInfoElement ()
   {
      $div = new Tag('div');
      $div->AddElement('This area is how you publish your Nodes');
      return $div;
   }
}

?>
