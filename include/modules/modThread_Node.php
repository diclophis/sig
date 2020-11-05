<?php

   class Thread_Node extends Forum_Node {
      var $struct = array( "node_type" => "Thread_Node",
                           "title"     => array( "prop_type" => "text",
                                                "values"    => ""),
                           "body"   => array( "prop_type" => "textarea",
                                                 "values"    => ""));

      var $allowed_children_types = array( "Thread" => "Thread_Node" );

      function get_forum_id ()
      {
         if (!isset($this->forum_id)) {
            if ($this->node_type == "Thread_Node") {
               $parent = Node::new_instance ($this->parent_id);
               $this->forum_id = $parent->get_forum_id();
            } elseif ($this->node_type == "Forum_Node") {
               $this->forum_id = $this->id;
            }
         }

         return $this->forum_id;
      }  
   }

?>
