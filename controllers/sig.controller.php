<?php

class SiG_Controller {
   function SiG_Controller ()
   {
   }

   function ParseRequestIntoObject ($field, $default)
   {
      $className = isset($_REQUEST[$field]) ? $_REQUEST[$field] : $default;
      if (!class_exists($className)) {
         $classFilename = SiG_Controller::BasePath().'/'.$field.'s/'.str_replace('_', '.', strtolower($className)).'.php';
         if (file_exists($classFilename)) {
            include($classFilename);
         } else {
            die('Unknown Class');
         }
      }

      return new $className($parameter);
   }

   function CreateControllerFromRequest ($default = 'SiG_Controller')
   {
      return SiG_Controller::ParseRequestIntoObject('controller', $default); 
   }

   function CreateModelFromRequest ($default = 'SiG_Model')
   {
      return SiG_Controller::ParseRequestIntoObject('model', $default); 
   }

   function CreateViewFromRequest ($default = 'SiG_View')
   {
      return SiG_Controller::ParseRequestIntoObject('view', $default); 
   }

   function BasePath ()
   {
      return ABSPATH;
   }

   function BaseUrl ()
   {
      return '';
   }

   function HeadElement ($admin = FALSE)
   {
      $head = new Tag('head');
      return $head;
   }

   function GetTablePrefix ()
   {
      global $table_prefix;
      return $table_prefix.'sig_';
   }

   function GetSiteId ()
   {
      return '1';
   }
}

?>
