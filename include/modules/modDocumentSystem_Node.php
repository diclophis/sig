<?php

class DocumentSystem_Node extends System_Node {
   var $struct = array( "node_type"       => "DocumentSystem_Node",

                        "title"           => array( "prop_type" => "text",
                                                    "values"    => ""));

   var $allowed_children_types = array(
                                       "Paged Content" => "Content_Node",
                                       "File" => "Document_Node",
                                       "Folder" => "Folder_Node",
                                       "Mandelbrot Set" => "Mandel_Node"
                                      );

   function BrowseInfoElement ()
   {
      $div = new Tag('div');
      $div->AddElement('This area can be used to store your uploaded files');
      return $div;
   }
}

?>
