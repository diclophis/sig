<?php

class PollSystem_Node extends System_Node {
   var $struct = array( "node_type"       => "PollSystem_Node",

                        "title"           => array( "prop_type" => "text",
                                                    "values"    => ""));

   var $allowed_children_types = array("Poll" => "Poll_Node");
   function BrowseInfoElement ()
   {
      $div = new Tag('div');
      $div->AddElement('This area can be used to store your Polls<br/>');
      return $div;
   }
}

?>
