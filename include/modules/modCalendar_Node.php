<?

class Calendar_Node extends Node {
      var $struct = array( "node_type" => "Calendar_Node",
                           "title"     => array( "prop_type" => "text",
                                                "values"    => ""));

   var $allowed_children_types = array ('Event'=>'Event_Node');

   var $calendar_instance;

   function CalendarInstance ()
   {
      $calendarType = SiG_Session::Instance()->Request('calendarType', 'Month');
      $calendarYear = SiG_Session::Instance()->Request('calendarYear', date('Y'));
      $calendarMonth = SiG_Session::Instance()->Request('calendarMonth', date('m'));
      $calendarDay = SiG_Session::Instance()->Request('calendarDay', date('d'));
      if ($this->calendar_instance == NULL) {
         $this->calendar_instance = Calendar_Factory::create($calendarType, $calendarYear, $calendarMonth, $calendarDay);
      }

      return $this->calendar_instance;
   }

   function DefaultHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
   {
      $calendarType = SiG_Session::Instance()->Request('calendarType', 'Month');
      $todaysEvents = array();
      switch ($calendarType) {
         case 'Day':
            $day = $this->CalendarInstance();

            $eventHours = array();
            foreach ($this->get_array_of_children() as $child) {
               if (in_array($day, $child->GetArrayOfDays())) {
                  $todaysEvents[] = $child;
                  foreach ($child->GetArrayOfHours() as $hour) {
                     $eventHours[] = $hour;
                     $hourToEvents[$hour->getTimestamp()][] = $child;
                  }
               }
            }

            $day->build($eventHours);
           
            $table = new Tag('table', array('class'=>'sig_calendar'));
 
            $headerTr = new Tag('tr');
            $hourTd = new Tag('td', array('class'=>'sig_calendar_hour'));
            $hourTd->AddElement('Hour');
            $headerTr->AddElement($hourTd);
            foreach ($todaysEvents as $event) {
               $eventTd = new Tag('td', array('class'=>'sig_calendar_event_header')); 
               $eventTd->AddElement($event->title->value);
               $headerTr->AddElement($eventTd);
            }

            $table->AddElement($headerTr);
            
            while ($hour = $day->fetch()) {
               $tr = new Tag('tr');
               $td = new Tag('td', array('class'=>'sig_calendar_hour'));
               $td->AddElement($hour->thisHour());
               $tr->AddElement($td);

               if ($hour->isSelected()) {
                  foreach ($todaysEvents as $event) {
                     if (in_array($event, $hourToEvents[$hour->getTimestamp()])) {
                        $td = new Tag('td', array('class'=>'sig_calendar_event_hour'));
                        //$td->AddElement($event->title->value);
                     } else {
                        $td = new Tag('td', array('class'=>'sig_calendar_day_hour'));
                     }
                     $tr->AddElement($td);
                  }
               } else {
                  $td = new Tag('td', array('class'=>'sig_calendar_day_hour', 'colspan'=>count($todaysEvents)));
                  $tr->AddElement($td);
               }

               $table->AddElement($tr);
            }

            $container->AddElement($table);
         break;

         case 'Month':
      $Month = $this->CalendarInstance();
      $Uri = new Calendar_Util_Uri('calendarDay','calendarMonth'); 

      foreach ($this->get_array_of_children() as $child) {
         foreach ($child->GetArrayOfDays() as $day) {
            $eventDays[] = $day;
            $dayToEvent[$day->getTimestamp()][] = $child;
         }
      }

      $Month->build($eventDays);

      $table = new Tag('table', array('class'=>'sig_calendar'));

      $headerTr = new Tag('tr');
      $prevTd = new Tag('td', array('class'=>'sig_calendar_previous'));
      $prevA = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&'.$Uri->prev($Month, 'month')));
      $prevA->AddElement('<<');
      $prevTd->AddElement($prevA);
      $currTd = new Tag('td', array('class'=>'sig_calendar_current', 'colspan'=>'5'));
      $currTd->AddElement(Calendar_Util_Textual::thisMonthName($Month));
      $nextTd = new Tag('td', array('class'=>'sig_calendar_next'));
      $nextA = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&'.$Uri->next($Month, 'month')));
      $nextA->AddElement('>>');
      $nextTd->AddElement($nextA);
      $headerTr->AddElement($prevTd);
      $headerTr->AddElement($currTd);
      $headerTr->AddElement($nextTd);
      $table->AddElement($headerTr);

      $tr = new Tag('tr');
      foreach(Calendar_Util_Textual::orderedWeekdays($Month, 'two') as $weekdayName) {
         $td = new Tag('td', array('class'=>'sig_calendar_weekday'));
         $td->AddElement($weekdayName);
         $tr->AddElement($td);
      }
      $table->AddElement($tr); 

      while ($Day = $Month->fetch()) {
         if ($Day->isFirst()) { // Check for the start of a week
            $tr = new Tag('tr', array('class'=>'sig_calendar_week'));
         }

         if ($Day->isEmpty()) { // Check to see if day is empty
            $td = new Tag('td', array('class'=>'sig_calendar_empty_day'));
            $td->AddElement('&nbsp;');
         } elseif ($Day->isSelected()) {
            $td = new Tag('td', array('class'=>'sig_calendar_event_day'));
            $dayDiv = new Tag('div');
            $dayA = new Tag('a', 
               array('href'=>SiG_Plugin_Controller::Permalink().'&calendarType=Day&calendarDay='.$Day->thisDay()));
            $dayA->AddElement($Day->thisDay());
            $dayDiv->AddElement($dayA);
            $td->AddElement($dayDiv);
            foreach ($dayToEvent[$Day->getTimestamp()] as $event) {
               $eventDiv = new Tag('div', array('class'=>'sig_calendar_event'));
               $eventA = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$event->id));
               $eventA->AddElement($event->title->value);
               $eventDiv->AddElement($eventA);
               $td->AddElement($eventDiv);
            }
         } else {
            $td = new Tag('td', array('class'=>'sig_calendar_day'));
            $dayDiv = new Tag('div');
            $dayA = new Tag('a', 
               array('href'=>SiG_Plugin_Controller::Permalink().'&calendarType=Day&calendarDay='.$Day->thisDay()));
            $dayA->AddElement($Day->thisDay());
            $dayDiv->AddElement($dayA);
            $td->AddElement($dayDiv);
         }
         $tr->AddElement($td);

         if ($Day->isLast()) { // Check for the end of a week
            $table->AddElement($tr);
         }
      }

      $container->AddElement($table);
         break;
      }
   }

   /*
   function pathos_datetime_duration($time_a,$time_b)
   {
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
   */
}
