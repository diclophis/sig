<?php

class SiG_Session extends SiG_Model {
   var $request;
   var $files;
   var $parentNodes;
   function SiG_Session ()
   {
      $this->request = $_REQUEST;
      $this->files = $_FILES;
      $this->parentNodes = array();
   }

   static function Instance ()
   {
      static $instance;

      if ($instance == NULL) {
         $instance = new SiG_Session();
      }

      return $instance;
   }

   function Request ($key, $default = NULL)
   {
      return (isset($this->request[$key]) ? $this->request[$key] : $default);
   }

   function Dispatch ($container, $model, $actionKey, $defaultAction, $actionPrefix = 'do')
   {
      $action = SiG_Session::Instance()->Request($actionKey, $defaultAction);

      $action = str_replace(' ', '_', $action);

      $actionMethod = $actionPrefix.$action;

      if (method_exists($model, $actionMethod)) {
         return $model->$actionMethod($container);
      } else {
         die('SiG_Session::Dispatch - Unknown Method ('.$actionMethod.')');
      }
   }

   function IsUploadedFile ($key)
   {
      if (array_key_exists($key, $this->files)) {
         return is_uploaded_file($this->files[$key]['tmp_name']);
      } else {
         return FALSE;
      }
   }

   function UploadedFilename ($key)
   {
      return $this->files[$key]['name'];
   }

   function MoveUploadedFile ($key, $destination)
   {
      return move_uploaded_file($this->files[$key]['tmp_name'], $destination);
   }

   function GetUserData ()
   {
      global $user_ID;
      $user = new StdClass();
      $user->ID = 1;
      $user->user_nickname = "Jon";
      $user->user_level = 10;
      return $user;
   }

   function GetUserProfileElement ($id)
   {
      $div = new Tag('div');
      if ($id) {
         $userdata = SiG_Session::GetUserData($id);
         $div->AddElement($userdata->user_nickname);
      } else {
         $div->AddElement('Anonymous');
      }
      return $div;
   }

   function UserHavePerm ($level = 8)
   {
      return (SiG_Session::Instance()->GetUserData()->user_level > $level);
   }
}

?>
