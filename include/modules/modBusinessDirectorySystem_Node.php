<?

class BusinessDirectorySystem_Node extends System_Node {
   var $struct = array( "node_type"       => "BusinessDirectorySystem_Node",

                        "title"           => array( "prop_type" => "text",
                                                    "values"    => ""));

   var $allowed_children_types = array("Business Directory" => "BusinessDirectory_Node");

   function BrowseInfoElement ()
   {
      $div = new Tag('div');
      $div->AddElement('This area is used to store Business Directories<br/>');
      return $div;
   }
}

?>
