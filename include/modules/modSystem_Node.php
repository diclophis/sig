<?php

class System_Node extends Node {

   function viewListing ($container)
   {
      $div = new Tag('li');
      $title = $this->BrowseTitleElement();
      $a = new Tag('a', array('href'=>'?page=sig&model=Explorer&node_ids[]='.$this->id));
      $a->AddElement($title);
      $div->AddElement($a);
      $div->AddElement($this->BrowseInfoElement());

      $container->AddElement($div);
   }

   function BrowseInfoElement ()
   {
      return;
   }
}

?>
