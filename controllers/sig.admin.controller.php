<?php

class SiG_Admin_Controller extends SiG_Controller {
   function SiG_Admin_Controller ()
   {
      $this->Main();
   }

   function Main ()
   {
      $htmlContainer = new Tag('html');
      $bodyContainer = new Tag('body');

      $container = new Tag('div', array('class' => 'wrap'));
      $form = new Tag('form', array('action'=>'?', 'method'=>'post', 'enctype'=>'multipart/form-data'));

      $default = 'Configure';

      if (SiG_Admin_Model::TablesExist()) {
        $default = 'Explorer';
      }

      SiG_Session::Dispatch($form, $this, 'model', $default);

      $container->AddElement($form);
      $bodyContainer->AddElement($container);
      $htmlContainer->AddElement(SiG_Controller::HeadElement(TRUE));
      $htmlContainer->AddElement($bodyContainer);
      echo $htmlContainer->DrawElements();
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
