<?

class BusinessDirectoryCategories_Node extends System_Node {
   var $struct = array(
                    "node_type" => "BusinessDirectoryCategories_Node",
                    
                    "title" => array(
                                  "prop_type" => "text",
                                  "values" => "user should never see edit form"
                               )
                 );

   var $allowed_children_types = array(
                                    "Business Directory Category" => "BusinessDirectoryCategory_Node"
                                 );

   function BrowseInfoElement ()
   {
      $p = new Tag('div');
      $p->AddElement('Categories');
      return $p;
   }
}

?>
