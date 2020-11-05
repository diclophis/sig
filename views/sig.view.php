<?php

class SiG_View {
   var $name;
   var $elements = array();
   function SiG_View ($name)
   {
   }

   function SetName ($name)
   {
      $this->name = $name;
   }

   function AddElement ($element, $id = NULL)
   {
      if ($id != NULL) {
         $this->elements[$id] = $element;
      } else {
         $this->elements[] = $element;
      }
   }

   function PopElement ($id = NULL)
   {
      if ($id === NULL) {
         $return = array_pop($this->elements);
      } else {
         unset($this->elements[$id]);
      }
      
      return $return;
   }

   function DrawElements ()
   {
      $return = '';
      foreach ($this->elements as $element) {
         if (is_object($element) && is_subclass_of($this, 'SiG_View')) {
            $return .= $element->DrawElements();
         } elseif (is_array($element)) {
            
         } else {
            $return .= $element;
         }
      }
      return $return;
   }

   /*
   function TableIterater ($object, $initialMethod, $rowMethod, $columnMethod, $finalMethod)
   {
      $return = '';

      $return .= $object->$initalMethod();

      foreach ($object->$rowMethod() as $row) {
         $return .= $row;
         foreach ($object->$columnMethod() as $col) {
            $return .= $col;
         }
      }

      $return .= $object->$finalMethod();

      return $return;
   }
   */
}

?>
