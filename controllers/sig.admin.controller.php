<?php

class SiG_Admin_Controller extends SiG_Controller {
   function SiG_Admin_Controller ()
   {
      //add_action('admin_menu', array($this, 'actionAdminMenu'));
      //add_action('admin_head', array($this, 'actionAdminHead'));
      $this->Main();
   }

   function actionAdminMenu ()
   {
      //add_management_page('SiG', 'SiG', 0, SiG_Controller::BasePath(), array($this, 'Main'));
   }

   function actionAdminHead ()
   {
      echo '<!-- Admin::actionAdminHead -->';
   }

   function Main ()
   {
      $htmlContainer = new Tag('html');
      $bodyContainer = new Tag('body');

      $container = new Tag('div', array('class' => 'wrap'));
      $form = new Tag('form', array('action'=>'?', 'method'=>'post', 'enctype'=>'multipart/form-data'));
      SiG_Session::Dispatch($form, $this, 'model', 'Default');
      $container->AddElement($form);
      $bodyContainer->AddElement($container);
      $htmlContainer->AddElement(SiG_Controller::HeadElement(TRUE));
      $htmlContainer->AddElement($bodyContainer);
      echo $htmlContainer->DrawElements();

      //echo $container->DrawElements();
   }

   function doDefault ($container)
   {
      $defaultFieldset = new Tag('fieldset');
      $defaultLegend = new Tag('legend');
      $defaultLegend->AddElement('SiG Management Interface');
      $defaultFieldset->AddElement($defaultLegend);

      $ul = new Tag('ul');
      if (SiG_Admin_Model::TablesExist()) {
         $li = new Tag('li');
         $explorerButton = new Tag('input', array('type'=>'submit', 'name'=>'model', 'value'=>'Explorer'));
         $li->AddElement($explorerButton);
         $ul->AddElement($li);
      } else {
         $p = new Tag('p', array('class'=>'sig_notice_error'));
         $p->AddElement('You must configure SiG for the first time');
         $defaultFieldset->AddElement($p);
      }

      $li = new Tag('li');
      $configButton = new Tag('input', array('type'=>'submit', 'name'=>'model', 'value'=>'Configure'));
      $li->AddElement($configButton);
      $ul->AddElement($li);

      $defaultFieldset->AddElement($ul);
      $container->AddElement($defaultFieldset);
   }

   function doExplorer ($container)
   {
      $modelElement = new Tag('input', array('type'=>'hidden', 'name'=>'model', 'value'=>'Explorer'));
      $container->AddElement($modelElement);
      foreach (Node::RequestedInstances() as $node) {
         SiG_Session::Dispatch($container, $node, 'action', 'Browse');
      }
   }

   function doConfigure ($container)
   {
      $modelElement = new Tag('input', array('type'=>'hidden', 'name'=>'model', 'value'=>'Configure'));
      $container->AddElement($modelElement);
      $adminModel = new SiG_Admin_Model ();
      SiG_Session::Dispatch($container, $adminModel, 'action', 'Default');
   }
}

?>
