<?

class Folder_Node extends Node {
   var $struct = array( "node_type"       => "Folder_Node",

                           "title"           => array( "prop_type" => "text",
                                                       "values"    => "New Folder"));

   var $allowed_children_types = array(
                                           'Folder'=>'Folder_Node',
                                           'Paged Content'=>'Content_Node'
                                         );
   function DefaultHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
   {
      foreach ($this->get_array_of_children() as $child) {
         $child->DefaultHtmlData($container, $parentNode, $activeNode);
      }
   }
}

?>
