<?
   class Property_type {
      function Property_type ($name)
      {
         $this->name = $name;
         $query  = "SELECT id FROM ".SiG_Controller::GetTablePrefix()."property_types ";
         $query .= "WHERE name='{$this->name}' LIMIT 1";
         $results = New Query($query);
         if ($data = $results->execute()) {
            //HACK
            $this->id = $data->id;
         } else {
            $this->id = 0;
            Debug::add_message(__file__, __line__, "fail of proptype id fetch of ".$name, 'error');
            $q = new Query('INSERT INTO '.SiG_Controller::GetTablePrefix().'property_types VALUES ("", "'.$name.'")');
            if ($data2 = $results->execute()) {
               //return new Property_type($name);
            } else {
               Debug::add_message(__file__, __line__, "fail to create proptype id of ".$name, 'error');
            }
         }
      }

      function return_nodes_having ($a)
      {
         global $siteId;

         $query .= "SELECT node_id ";
         $query .= "FROM ".SiG_Controller::GetTablePrefix()."property_data WHERE ".SiG_Controller::GetTablePrefix()."property_data.node_id=".SiG_Controller::GetTablePrefix()."node_data.id AND ".SiG_Controller::GetTablePrefix()."node_data.site_id='$siteId' AND ";

         while (list($key, $val) = each ($a)) {
            $valueToMatch = $key;
            if (is_object($val)) {
               $query .= SiG_Controller::GetTablePrefix()."property_data.type_id='{$val->id}' AND ";
               $query .= SiG_Controller::GetTablePrefix()."property_data.value='{$valueToMatch}' ";
            }
            if ($i++ < (count($a)-1)) {
               $query .= "OR ";
            }
         }

         $results = New Query($query);
         while ($data = $results->execute()) {
            $node_ids[node_id] = $data[node_id];
         }

         $ar = array_count_values ($node_ids);

         while (list($node_id, $times) = each ($ar)) {
            if ($times >= count ($a)) {
               echo $node_id;
            }
         }

/*
         $query .= "SELECT node_id ";
         $query .= "FROM property_data ";
         $query .= "WHERE property_data.type_id='{$this->id}' ";
         $query .= "AND property_data.value='{$value}'";
         $results = New Query($query);
         while ($data = $results->execute()) {
            $return[] = Node::new_instance($data[node_id]);
         }
         return $return;
*/
      }

      function get_array_of_nodes_having ($value, $returnNodeType = "Node")
      {
         $return = array();

         $query = "SELECT node_id "
                . "FROM "
                . SiG_Controller::GetTablePrefix()."property_data, "
                . SiG_Controller::GetTablePrefix()."node_data "
                . "WHERE "
                . SiG_Controller::GetTablePrefix()."property_data.type_id='".$this->id."' "
                . "AND "
                . SiG_Controller::GetTablePrefix()."property_data.value='".$value."' "
                . "AND "
                . SiG_Controller::GetTablePrefix()."node_data.id="
                . SiG_Controller::GetTablePrefix()."property_data.node_id "
                //. "AND "
                //. SiG_Controller::GetTablePrefix()."node_data.site_id='".SiG_Controller::GetSiteId()."' "
                . "ORDER BY node_id DESC";
         $results = New Query($query);
         while ($data = $results->execute()) {
            $returnNode = Node::new_instance($data->node_id);
            //TODO if ($returnNode->have_read_perms()) {
               $return[$returnNode->id] = $returnNode;
            //}
         }
         return $return;
      }

      function get_array_of_types ()
      {
         //ummm
      }
   }
?>
