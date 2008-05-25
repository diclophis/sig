<?

class CalendarSystem_Node extends System_Node {
   var $struct = array( "node_type"       => "CalendarSystem_Node",

                        "title"           => array( "prop_type" => "text",
                                                    "values"    => ""));

   var $allowed_children_types = array("Calendar" => "Calendar_Node");
   function BrowseInfoElement ()
   {
      $div = new Tag('div');
      $div->AddElement('This area can be used to store Calendar Events<br/>');
      return $div;
   }
}

?>
