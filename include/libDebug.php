<?php

   class Debug {
      var $messages;

      function Debug ()
      {
         //ummm
      }

      function add_message ($file, $line, $message, $type = 'plain')
      {
         global $debug;
         if (!is_object($debug)) {
            $GLOBALS['debug'] = New Debug();
         }

         if ($type == 'error') {
            $message = '<b>'.$message.'</b>';
         }

         $debug->messages .= '<br> "'.$message.'" - In '.$file.'  On line '.$line;
      }
   }
?>
