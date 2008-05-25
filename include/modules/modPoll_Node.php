<?

   class Poll_Node extends Node {
      var $struct = array( "node_type"       => "Poll_Node",

                           "title"           => array( "prop_type" => "text",
                                                       "values"    => "New Poll"),

                           "ballot"            => array( "prop_type" => "textarea",
                                                       "values"    => ""),

                           /*
                           "active" => array( "prop_type" => "dropdown",
                                              "values"    => array( "Yes" => 1,
                                                                    "No"  => 0 )),

                           "on_default_page" => array( "prop_type" => "dropdown",
                                                       "values"    => array( "Yes" => 1,
                                                                             "No"  => 0 )),

                           "on_default_nav"  => array( "prop_type" => "dropdown",
                                                       "values"    => array( "Yes" => 1,
                                                                             "No"  => 0 )));
                           */
                           );

      var $allowed_children_types = array ("Vote" => "Vote_Node");

      function get_array_of_options ()
      {
         $array = array();
         $b = $this->get_property_member('ballot', 'value');
         $arrayOfOptions = explode("\n", $b);
         foreach ($arrayOfOptions as $option) {
            //$temp] = explode ("|", $option);
            $option = trim($option);
            $array[$option] = $option; //$temp[0];
         }
         return $array;
      }

      function DefaultHtmlData ($container, $parentId)
      {
         $div = new Tag('div');
                  $form = new Tag('form', array('action'=>SiG_Plugin_Controller::Permalink(), 'method'=>'post'));
         if ($this->id && $this->parent_id) {
            $status = SiG_Session::Instance()->Request('action', NULL);
            switch ($status) {
               default:
                  //$form = new Tag('form', array('action'=>SiG_Plugin_Controller::Permalink(), 'method'=>'post'));
                  $node = New Vote_Node(NULL);
                  $node->primary_parent_id = $this->id;
                  //$node->user_id = 1;//$GLOBALS[userData][user_id];
                  //$node->group_id = 1;//$GLOBALS[userData][group_id];
                  $node->HtmlForm($form);
                  $voteButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Vote'));
                  $form->AddElement($voteButton);
               break;

               case 'Vote':
                  $nodeValues = SiG_Session::Instance()->Request('nodeValues');
                  $struct = SiG_Session::Instance()->Request('struct');
                  $node = call_user_func_array(array($struct['node_type'], 'create'), array($nodeValues, $struct['node_type']));
                  $node->new = TRUE;
                  //$node->parent_id = $nodeValues['parent_id'];
                  //$node->primary_parent_id = $this->id;
                  $node->update($nodeValues, $struct);
               break;
            }
            $ar = $this->get_num_of_properties_in_children('choice');
            $results = array_count_values($ar);

            $p = new Tag('p');
            $p->AddElement($this->get_property_member('title', 'value').'<br/>');

            foreach ($results as $option => $count) {
               $p->AddElement($option.': '.$count.'<br/>');
            }

            $div->AddElement($p);

            $div->AddElement($form);
         }
         $container->AddElement($div);
      }
   }

?>
