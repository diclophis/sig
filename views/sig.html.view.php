<?php

class SiG_Html_View extends SiG_View {
   var $attributes;
   function SiG_Html_View ($name, $attributes = array())
   {
      $this->SetName($name);
      $this->SetAttributes($attributes);
   }

   function SetAttributes ($attributes = array())
   {
      if (is_array($attributes)) {
         $this->attributes = $attributes;
      } else {
         die('SiG_Html_View::SetAttributes - Invalaid $attributes');
      }
   }

   function SetAttribute ($key, $value)
   {
      $this->attributes[$key] = $value;
   }

   function DrawElements ()
   {
      $return = '<';
      $return .= $this->name;
      $return .= ' ';
      foreach ($this->attributes as $attribute => $value) {
         $return .= $attribute.'="'.$value.'" ';
      }
      $return .= '>';
      $return .= parent::DrawElements();
      $return .= '</'.$this->name.'>';
      return $return;
   }
}

class Tag extends SiG_Html_View {}
class TagGroup extends SiG_View {
   function TagGroup ()
   {
      //
   }
}

?>
