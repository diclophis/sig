<?php

   class Content_Node extends Node {
      var $struct = array( "node_type"       => "Content_Node",

                           "title"           => array( "prop_type" => "text",
                                                       "values"    => ""),

                           "body"            => array( "prop_type" => "textarea",
                                                       "values"    => ""),
                           "publish_on"            => array( "prop_type" => "text",
                                                       "values"    => ""),
                           "unpublish_on"            => array( "prop_type" => "text",
                                                       "values"    => ""),
/*

                           "on_default_page" => array( "prop_type" => "dropdown",
                                                       "values"    => array( "Yes" => 1,
                                                                             "No"  => 0 )),

                           "on_default_nav"  => array( "prop_type" => "dropdown",
                                                       "values"    => array( "Yes" => 1,
                                                                             "No"  => 0 ))
*/
                         );
      var $allowed_children_types = array(
                                           //'Folder'=>'Folder_Node',
                                           'Paged Content'=>'Content_Node'
                                         );


      /**
      * Get next href
      *
      * @param $n int
      * @access public
      * @return string
      */
      function get_next_href ($n = 0)
      {
         $ar = $this->get_child($n);
         return $html = '<a href="'.SiG_Plugin_Controller::Permalink().'&active_id='.$ar->id.'">';
      }

      /**
      * Format body property
      *
      * Pass the body text through several filters
      * [next]text[/next] changes the text into a link to the next peer (next page)
      * [pagenav] is the (1,2,3,4) page links
      * [prev]text[/prev] is the back a page link
      * [link=http://yahoo.com]text[/link] changes text into a link with the specified URL
      * [more]text[/more] changes the text into a link to the first child node   
      *
      * @access public
      * @return string
      */
   function get_body ($active = FALSE) 
   {
      $this->bodytext = $this->get_property_member('body','value');
      $pagenav = "&nbsp;(&nbsp;";
            
      if ($active) {
         $parentIds = $this->GetParentIds();
         $parentNode = Node::new_instance($parentIds[0]);
         if ($parentNode instanceof Content_Node) {
            $referenceNode = $parentNode;
         } else {
            $referenceNode = $this;
         }
      } else {
         $referenceNode = $this;
      }

      $count = $referenceNode->get_num_of_children();
      for ($i=0; $i < $count; $i++) {
         $ar = $referenceNode->get_child($i);
         if ($this->id != $ar->id) {
               $pagenav .= '<a href="'.SiG_Plugin_Controller::Permalink().'&active_id='.$ar->id.'">'.($i+1).'</a>&nbsp;';
         } else {
            $this->childNumber = $i;
            $pagenav .= ($i+1)."&nbsp;";
         }
      }

      $pagenav .= ")";
      $this->bodytext = str_replace('[pagenav]', $pagenav, $this->bodytext);

      if (ereg('\[prev\]', $this->bodytext) && ereg('\[/prev\]', $this->bodytext)) {
         $this->bodytext = str_replace('[prev]', $referenceNode->get_next_href($this->childNumber-1), $this->bodytext);
         $this->bodytext = str_replace('[/prev]', '</a>', $this->bodytext);
      }

      if (ereg('\[next\]', $this->bodytext) && ereg('\[/next\]', $this->bodytext)) {
         $this->bodytext = str_replace('[next]', $referenceNode->get_next_href($this->childNumber+1), $this->bodytext);
         $this->bodytext = str_replace('[/next]', '</a>', $this->bodytext);
      }

      while (ereg('\[page=([0-9])*\]', $this->bodytext, $reg)) {
         $this->bodytext = str_replace('[page=&quot;javascript', '[page=&quot; javascript', $this->bodytext);
         $page = intval($reg[1]);
         $this->bodytext = ereg_replace('\[page='.$page.'\]',$referenceNode->get_next_href(intval($page-1)),$this->bodytext);
         $this->bodytext = str_replace('[/page]', '</a>', $this->bodytext);
      }

      while (ereg('\[node=([0-9]*)\]', $this->bodytext, $reg)) {
         $nodeId = (intval($reg[1]));
         $nodeInstance = Node::new_instance($nodeId);
         $div = new Tag('br'); 
         //if ($active) {
         //   $nodeInstance->ActiveHtmlData($div, $this, $this);
         //} else {
         //}
         $this->bodytext = ereg_replace('\[node='.$nodeId.'\]',$nodeInstance->DrawEmbeded(),$this->bodytext);
      }

      return $this->bodytext;

   }

   function DefaultHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
   {
      $title = $this->get_property_member('title','value');
      $body = $this->get_body();
      $container->AddElement($this->PostElement($title, $body));
   }

   function ActiveHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
   {
      $title = $this->get_property_member('title','value');
      $body = $this->get_body(TRUE);
      $container->AddElement($this->PostElement($title, $body));
   }

   function PostElement ($title, $body)
   {
      $div = new Tag('div', array('class'=>'post'));
      $em = new Tag('h1');
      $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$this->id));
      $a->AddElement($title);
      $em->AddElement($a);

      $p = new Tag('p');
      $p->AddElement($body);

      $div->AddElement($em);
      $div->AddElement($p);
      return $div;
   }
}

?>
