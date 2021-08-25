<?php

class Property {
   function Property ($node_id, $type)
   {
      $this->property_type = New Property_type($type);
      $this->node_id = intval($node_id);
      $this->type_id = intval($this->property_type->id);
      $query = "SELECT * "
             . "FROM ".SiG_Controller::GetTablePrefix()."property_data "
             . "WHERE ".SiG_Controller::GetTablePrefix()."property_data.type_id='{$this->property_type->id}' " 
             . "AND ".SiG_Controller::GetTablePrefix()."property_data.node_id='$this->node_id'";
      $results = New Query($query);
      $data = $results->execute();
      if (is_object($data)) {
        $this->id = $data->id;
        $this->value = $data->value;
      } else {
         $this->id = 0;
         $this->value = NULL;
      }
   }

   function set_value ($value, $new = false)                // Sets the member to the new value
   {
     global $db;

      $query =  ($new ? "REPLACE ".SiG_Controller::GetTablePrefix()."property_data SET " 
                      : "UPDATE ".SiG_Controller::GetTablePrefix()."property_data SET ")
                . "id='".$this->id."', "
                . "type_id='".$this->type_id."', "
                . "node_id='".$this->node_id."', "
                . "value='".mysqli_real_escape_string($db->conn, stripslashes($value))."' "
                . ($new ? '' : "WHERE id='".$this->id."'");

      $results = New Query($query, 1);

      $this->value = $value;
      
   }

   function erase ()
   {
      $query = "DELETE FROM ".SiG_Controller::GetTablePrefix()."property_data WHERE id='{$this->id}'";
      $result = New Query ($query);
      return $result;
   }
}

?>
