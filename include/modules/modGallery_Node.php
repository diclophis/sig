<?php

   class Gallery_Node extends Folder_Node {
      var $struct = array( "node_type" => "Gallery_Node",
                           "title"     => array( "prop_type" => "text",
                                                 "values"    => "New Gallery"),

                           /*
                           "body"      => array( "prop_type" => "callback",
                                                 "name" => "image_upload"),
                           "on_default_page" => array( "prop_type" => "dropdown",
                                                       "values"    => array( "Yes" => 1,
                                                                             "No"  => 0 )),

                           "on_default_nav"  => array( "prop_type" => "dropdown",
                                                       "values"    => array( "Yes" => 1,
                                                                             "No"  => 0 )));
                           */
                           );


      var $allowed_children_types = array ("Image"=>"Image_Node"); //Not sure what to allow here!

      function DefaultHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
      {
         
            $div = new Tag('div', array('class'=>'Gallery_Node'));
            if ($this->get_num_of_children()) {
               $div->AddElement("Thumbnails for ".$this->title->value);

               $table = new Tag('table', array('style'=>'width: 100%;'));
               $thumbCount = 0;
               foreach ($this->get_array_of_children() as $child) {
                  if(($thumbCount % 4) == 0) {
                     $tr = new Tag('tr');
                     $table->AddElement($tr);
                  }
                  $td = new Tag('td');
                  $child->DefaultHtmlData($td, $this);
                  $tr->AddElement($td);
                  $thumbCount++;
               }
               $div->AddElement($table);
            }
            $container->AddElement($div);
      }

      function ActiveHtmlData ($container, $parentNode)
      {
         $this->DefaultHtmlData($container, $parentNode);
      }

      /*
      function HtmlData ($container)
      {
         $activeId = SiG_Session::Instance()->Request('active_id', NULL);

         if ($activeId) {
            $node = Node::new_instance($activeId);
            $node->FormattedBody($container, $this->id);
         } else {
            $div = new Tag('div', array('class'=>'Gallery_Node'));
            if ($this->get_num_of_children()) {
               $div->AddElement("Thumbnails for ".$this->title->value);

               $table = new Tag('table', array('style'=>'width: 100%;'));
               $thumbCount = 0;
               foreach ($this->get_array_of_children() as $child) {
                  if(($thumbCount % 4) == 0) {
                     $tr = new Tag('tr');
                     $table->AddElement($tr);
                  }
                  $td = new Tag('td');
                  $child->FormattedBody($td, $this->id);
                  $tr->AddElement($td);
                  $thumbCount++;
               }
               $div->AddElement($table);
            }
            $container->AddElement($div);
         }
      }
      */
   }

?>
