<?

class SnippetSystem_Node extends System_Node {
   var $struct = array( "node_type"       => "SnippetSystem_Node",

                        "title"           => array( "prop_type" => "text",
                                                    "values"    => ""));

   var $allowed_children_types = array("Code Snippet" => "Snippet_Node");
   function BrowseInfoElement ()
   {
      $div = new Tag('div');
      $div->AddElement('This area can be used to store your code snippets');
      return $div;
   }

}

?>
