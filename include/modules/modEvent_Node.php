<?

class Event_Node extends Node {
   var $struct = array(
                       "node_type" => "Event_Node",
                       "title"     => array(
                                            "prop_type" => "text",
                                            "values"    => "New Event"),
                       "body"     => array(
                                            "prop_type" => "textarea"),
                       "start_day" => array(
                                            "prop_type" => "text",
                                            "values" => ""),
                       "end_day" => array(
                                            "prop_type" => "text",
                                            "values" => ""),
                       "start_time" => array(
                                            "prop_type" => "text",
                                            "values" => ""),
                       "end_time" => array(
                                            "prop_type" => "text",
                                            "values" => "")
                      );

   function calc_recurringDailyDates($start,$end,$freq) {
      if ($start <= $end) {
         $dates = array();
         $curdate = $start;
         do {
            $dates[] = $curdate;
            $curdate += (86400 * $freq);
         } while ($curdate <= $end);
         return $dates;
      } else {
         die('Exception: Start Day must be before End Day');
      }
   }

   function GetArrayOfDays ()
   {
      $days = array();
      $start_day_timestamp = strtotime($this->start_day->value);
      $end_day_timestamp = strtotime($this->end_day->value);
      foreach ($this->calc_recurringDailyDates($start_day_timestamp, $end_day_timestamp, 1) as $day_timestamp) {
         $days[] = Calendar_Factory::createByTimestamp('Day', $day_timestamp);
      }
      return $days;
   }

   function calc_hours ($start, $end, $freq) {
      if ($start <= $end) {
         $dates = array();
         $curdate = $start;
         do {
            $dates[] = $curdate;
            $curdate += ((60 * 60) * $freq);
         } while ($curdate <= $end);
         return $dates;
      } else {
//echo $start;
//echo date('r', $start);
//echo '|';
//echo $end;
//echo datE('r', $end);
         die('Exception: Start Time must be before End Day');
      }
   }


   function GetArrayOfHours ()
   {
      $hours = array();
      $start_time_timestamp = strtotime($this->start_day->value.' '.$this->start_time->value);
//echo $this->end_day->value.' '.$this->end_time->value;
//echo '<br>';
      $end_time_timestamp = strtotime($this->end_day->value.' '.$this->end_time->value);
      foreach ($this->calc_hours($start_time_timestamp, $end_time_timestamp, 1) as $hour_timestamp) {
         $hours[] = Calendar_Factory::createByTimestamp('Hour', $hour_timestamp);
      }
      return $hours;
   }

   function categories ($name, $value)
   {
      $childrenIds = array();
      if ($this->id) {
         $importedNodesElement = new Tag('select', array('name'=>'nodes_to_detach[]', 'multiple'=>'multiple', 'size'=>'10'));

         foreach ($this->get_array_of_children() as $child) {
            $pid = $child->GetParentId(0);
            $pa = Node::new_instance($pid);
            $option = new Tag('option', array('value'=>$child->id));
            $option->AddElement($pa->get_property_member('title', 'value').'-'.$child->title->value);
            $importedNodesElement->AddElement($option);
            $childrenIds[] = $child->id;
         }

         $div = new Tag('div');
         $selectElement = new Tag('select', array('name'=>'nodes_to_attach[]', 'multiple'=>'multiple', 'size'=>'10'));
         $parentNode = Node::new_instance($this->GetParentId(0));
         $parentParentNode = Node::new_instance($parentNode->GetParentId(0));
         $parentParentNode->get_child(0)->RecursiveOptionElement(array(), $childrenIds, $selectElement, 0, 
                            array('BusinessDirectoryEntries_Node'), TRUE); //, 0, 

                                           //array());
         $attachButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Attach'));
         $detachButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Detach'));
         $div->AddElement($selectElement);
         $div->AddElement($attachButton);
         $div->AddElement($detachButton);
         $div->AddElement($importedNodesElement);
         return $div;
      } else {
         $p = new Tag('p');
         $p->AddElement('Select Categories After creation');
         return $p;
      }
   }

   function doAttach ($container)
   {
      $nodesToAttach = SiG_Session::Instance()->Request('nodes_to_attach', NULL);
      if ($nodesToAttach) {
         foreach ($nodesToAttach as $nodeId) {
            $node = Node::new_instance($nodeId);
            $node->new = TRUE; //$this->new;
            $node->AttachTo($this->id);
         }
      }

      $this->doEdit($container);
   }

   function doDetach ($container)
   {
      $nodesToDetach = SiG_Session::Instance()->Request('nodes_to_detach', NULL);
      if ($nodesToDetach) {
         foreach ($nodesToDetach as $nodeId) {
            $node = Node::new_instance($nodeId);
            $node->new = TRUE; //$this->new;
            $node->DetachFrom($this->id);
         }
      }

      $this->doEdit($container);
   }

function calc_duration ($time_a,$time_b) {
   $d = abs($time_b-$time_a);
   $duration = array();
   if ($d >= 86400) {
      $duration['days'] = floor($d / 86400);
      $d %= 86400;
   }
   if (isset($duration['days']) || $d >= 3600) {
      if ($d) $duration['hours'] = floor($d / 3600);
      else $duration['hours'] = 0;
      $d %= 3600;
   }
   if (isset($duration['hours']) || $d >= 60) {
      if ($d) $duration['minutes'] = floor($d / 60);
      else $duration['minutes'] = 0;
      $d %= 60;
   }
   $duration['seconds'] = $d;
   return $duration;
}


   function ActiveHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
   {
      $div = new Tag('div');
      $div->AddElement('on '.$this->start_day->value.' at '.$this->start_time->value);

      $start_timestamp = strtotime($this->start_day->value.' '.$this->start_time->value);
      $end_timestamp = strtotime($this->end_day->value.' '.$this->end_time->value);

      $duration = $this->calc_duration ($start_timestamp, $end_timestamp);

      $dur = ' This event lasts for';
      foreach ($duration as $unit=>$value) {
         $dur .= ' '.$value.' '.$unit;
      }

      $div->AddElement($dur);

      $container->AddElement($div);
   }
}

?>
