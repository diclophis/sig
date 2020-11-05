<?php

class BusinessDirectoryCategory_Node extends Node {
   var $struct = array(
                    "node_type" => "BusinessDirectoryCategory_Node",
                    
                    "title" => array(
                                  "prop_type" => "text",
                                  "values" => "New Business Category"
                               )
                 );

   var $allowed_children_types = array(
                                    "Business Directory Category"=>"BusinessDirectoryCategory_Node",
                                 );
}

?>
