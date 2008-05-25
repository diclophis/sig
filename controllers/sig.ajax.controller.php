<?

class SiG_Ajax_Controller extends SiG_Controller {
   //var responses = array();

   function SiG_Ajax_Controller ()
   {

   }

   /*
   function CreateModelFromRequest ()
   {
      $modelName = isset($_REQUEST['model']) ? $_REQUEST['model'] : 'SiG_Ajax_Model';
      return new $modelName();
   }
   */

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

/*
$controller = new SiG_Ajax_Controller ();
$controller->SetHeaders();

$model = SiG_Ajax_Controller::CreateModelFromRequest();
$model->SetViewFromRequest();
*/

?>
