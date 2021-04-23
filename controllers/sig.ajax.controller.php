<?php

class SiG_Ajax_Controller extends SiG_Controller {
   function SiG_Ajax_Controller ()
   {

   }

   function SetHeaders ()
   {
      header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
      header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
      header("Cache-Control: no-store, no-cache, must-revalidate");
      header("Cache-Control: post-check=0, pre-check=0", false);
      header("Pragma: no-cache");
      header('Content-Type: text/xml');
   }

   function AddResponse ($response)
   {
      $this->responses[] = $response;
   }

   function Send ()
   {
      echo '<?xml version="1.0" encoding="ISO-8859-1"?>';
      echo "\n";
      echo '<ajax-response>';

      foreach ($this->responses as $response) {
         echo $response;
      }

      echo '</ajax-response>';
   }
}

?>
