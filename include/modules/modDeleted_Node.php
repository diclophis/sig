<?

class Deleted_Node extends Node {
   var $struct = array("node_type" => "Node",
                       "title"     => array( "prop_type" => "text",
                                              "values"    => ""),
                       "deleted_node_parent_id" => array("prop_type" => "text",
                                                          "values" => "")
                      );

   function doRestore ($container)
   {
      print_r($this);
   }
}

?>
