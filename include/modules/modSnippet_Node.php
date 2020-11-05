<?php

/* Code that displays the code */
class Snippet_Node extends Node {
   var $struct = array(
      "node_type"       => "Snippet_Node",
      "title"           => array( "prop_type" => "text",
                                  "values"    => ""),
      "body"            => array( "prop_type" => "textarea",
                                  "values"    => "")
   );

   function DefaultHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
   {
      $header = new Tag('h3');
      $header->AddElement('Code Listing');
      $code = $this->get_property_member('body','value');
      $snippet = highlight_string($code, TRUE);
      $div = new Tag('div', array('style'=>'font-size: xx-small;'));
      $div->AddElement($snippet);
      $container->AddElement($header);
      $container->AddElement($div);
   }

   function DrawEmbeded ()
   {
      $code = $this->get_property_member('body','value');
      $snippet = highlight_string($code, TRUE);
      $p = new Tag('p');
      $p->AddElement($snippet);

      return $p->DrawElements();
   }

   
}
?>
