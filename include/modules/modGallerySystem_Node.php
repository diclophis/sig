<?php

class GallerySystem_Node extends System_Node {
   var $struct = array( "node_type"       => "GallerySystem_Node",

                        "title"           => array( "prop_type" => "text",
                                                    "values"    => ""));

   var $allowed_children_types = array("Gallery" => "Gallery_Node");

   function BrowseInfoElement ()
   {
      $div = new Tag('div');
      $div->AddElement('This area can be used to manage Image Galleries<br/>');
      return $div;
   }
}

?>
