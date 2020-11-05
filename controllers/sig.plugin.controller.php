<?php

class SiG_Plugin_Controller extends SiG_Controller {

   static $default_node_id;

   function SiG_Plugin_Controller ()
   {
      $this->Main();
   }

   function Main ()
   {
      $htmlContainer = new Tag('html');
      $bodyContainer = new Tag('body');

      $node_id = SiG_Session::Instance()->Request('node_id', NULL);
      $node = Node::new_instance($node_id);

      $activeId = SiG_Session::Instance()->Request('active_id', NULL);
      $activeNode = Node::new_instance($activeId);

      $navigationDiv = new Tag('div', array('id'=>'navigation'));
      $this->NavigationElements($navigationDiv);
      $bodyContainer->AddElement($this->BreadcrumbElement($node, $activeNode));

      if ($node->id == 0) {
         $bodyContainer->AddElement($navigationDiv);
         $newsDiv = new Tag('div', array('id'=>'news'));
         //TODO what is the default page?
         //$newsNode = Node::new_instance(11);
         $children = Node::GetSystemNodeByTitle("Pages")->get_array_of_children();
         if (count($children) > 0) {
           $newsNode = $children[0];
           self::$default_node_id = $newsNode->id;
           $newsNode->DefaultHtmlData($newsDiv, $node, $node); 
           $bodyContainer->AddElement($newsDiv);
         }
      } else {
         $contentDiv = new Tag('div', array('id'=>'content'));
         $node->DefaultHtmlData($contentDiv, NULL, $activeNode);
         $bodyContainer->AddElement($contentDiv);
      }


      $footerDiv = new Tag('div', array('id'=>'footer'));
      //$footerDiv->AddElement(SiG_Controller::GoogleAdsense());
      $bodyContainer->AddElement($footerDiv);
      $htmlContainer->AddElement(SiG_Controller::HeadElement());
      $htmlContainer->AddElement($bodyContainer);
      echo $htmlContainer->DrawElements();
   }

   function NavigationElements ($container)
   {
      $root = Node::GetSystemNodeByTitle("Pages");
      $ul = new Tag('ul');
      foreach ($root->get_array_of_children() as $child) {
         $li = new Tag('li');
         $a = new Tag('a', array('href'=>'?node_id='.$child->id));
         $a->AddElement($child->BrowseTitleElement());
         $li->AddElement($a);
         $ul->AddElement($li);
      }
      $container->AddElement($ul);
   }

   function BreadcrumbElement ($pageNode, $activeNode)
   {
      $breadcrumbFieldset = new Tag('div', array('id'=>'breadcrumb'));

      $homeA = new Tag('a', array('href'=>'?'));
      $homeA->AddElement('RisingCode.com');
      $breadcrumbFieldset->AddElement($homeA);

      if ($pageNode->id) {
         $breadcrumbFieldset->AddElement('&nbsp;/&nbsp;');
         $pageA = (new Tag('a', array('href'=>'?node_id='.$pageNode->id)));
         $pageA->AddElement($pageNode->TitleValue());
         $breadcrumbFieldset->AddElement($pageA);
      }

      if ($activeNode->id) {
         //$breadcrumbs = ($activeNode->bread_crumb());
         //foreach ($breadcrumbs as $crumb) {
            //$breadcrumbFieldset->AddElement('&nbsp;/&nbsp;');
         //   if (in_array($pageNode->id, $crumb['parents'])) { break; }
            $breadcrumbFieldset->AddElement('&nbsp;/&nbsp;');
            $a = (new Tag('a', array('href'=>'?node_id='.$pageNode->id.'&active_id='.$activeNode->id)));
            $a->AddElement($activeNode->TitleValue());
            $breadcrumbFieldset->AddElement($a);
         //}
      }
      return $breadcrumbFieldset;
   }


   function Permalink ()
   {
   //TODO wtf is this for?
      //return '/?node_id='.SiG_Session::Instance()->Request('node_id', 33);
      $children = Node::GetSystemNodeByTitle("Pages")->get_array_of_children();
      if (count($children) > 0) {
        $newsNode = $children[0];
        return '/?node_id='.SiG_Session::Instance()->Request('node_id', $newsNode->id);
      } else {
        return '/';
      }
   }
}

?>
