<?php

   class Vote_Node extends Poll_Node {
      var $struct = array( "node_type"       => "Vote_Node",
                           //"title"           => array( "prop_type" => "callback",
                           //                            "name" => "hidden_title"),
                           "choice"           => array( "prop_type" => "callback",
                                                       "name" => "get_option_dropdown"));

      //var $allowed_children_types = array ();

      function hidden_title ($value)
      {
         $titleElement = new Tag('input', array('name'=>'struct[title]', 'value'=>$_SERVER['REMOTE_ADDR']));
         return $titleElement;
      }

      function get_option_dropdown ($value)
      {
         if ($this->primary_parent_id) {
            $parent = Node::new_instance($this->primary_parent_id);
            $options = $parent->get_array_of_options();
            $select = new Tag('select', array('name'=>'struct[choice]'));
            foreach ($options as $key=>$title) {
               $option = new Tag('option', array('value'=>$title));
               if ($value == $title) {
                  $option->SetAttribute('selected', 'true');
               }
               $option->AddElement($key);
               $select->AddElement($option);
            }

            return $select;
         } else {
            //throw exception
            //return false;
            die('Exception: Vote Nodes cannot be a child of root');
         }
      }

      function BrowseTitleElement ()
      {
         return 'Vote';
      }

      function BreadcrumbTitleElement ()
      {
         return 'Vote';
      }
   }

?>
