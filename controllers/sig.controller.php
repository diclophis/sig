<?

class SiG_Controller {
   function SiG_Controller ()
   {
   }

   function ParseRequestIntoObject ($field, $default)
   {
      $className = isset($_REQUEST[$field]) ? $_REQUEST[$field] : $default;
      if (!class_exists($className)) {
         $classFilename = SiG_Controller::BasePath().'/'.$field.'s/'.str_replace('_', '.', strtolower($className)).'.php';
         if (file_exists($classFilename)) {
            include($classFilename);
         } else {
            die('Unknown Class');
         }
      }

      return new $className($parameter);
   }

   function CreateControllerFromRequest ($default = 'SiG_Controller')
   {
      return SiG_Controller::ParseRequestIntoObject('controller', $default); 
   }

   function CreateModelFromRequest ($default = 'SiG_Model')
   {
      return SiG_Controller::ParseRequestIntoObject('model', $default); 
   }

   function CreateViewFromRequest ($default = 'SiG_View')
   {
      return SiG_Controller::ParseRequestIntoObject('view', $default); 
   }

   function BasePath ()
   {
      return ABSPATH;
   }

   function BaseUrl ()
   {
      return '';
   }

//TODO fix this
   function HeadElement ($admin = FALSE)
   {
      $head = new Tag('head');
      $cssLink = new Tag('link', array(
         'rel'=>'stylesheet', 
         'href'=>SiG_Controller::BaseUrl().'/css/style.css', 
         'type'=>'text/css'));
      $head->AddElement($cssLink);
if ($admin) {
      $head->AddElement(
'
      <script type="text/javascript" src="'.SiG_Controller::BaseUrl().'/js/prototype-1.4.0.js"></script>
      <script type="text/javascript" src="'.SiG_Controller::BaseUrl().'/js/behaviour-1.0.0.js"></script>
      <script type="text/javascript" src="'.SiG_Controller::BaseUrl().'/js/scriptaculous-dist/src/scriptaculous.js"></script>
      <script type="text/javascript" src="'.SiG_Controller::BaseUrl().'/js/onload.js"></script>
      <script type="text/javascript">
      Behaviour.addLoadEvent (bodyOnLoad);
      </script>
'
      );
}
      $head->AddElement(SiG_Controller::GoogleUrchin());
      return $head;
   }

   function GetTablePrefix ()
   {
      global $table_prefix;
      return $table_prefix.'sig_';
   }

   function GetSiteId ()
   {
      return '1';
   }

   function GoogleUrchin ()
   {
      return $code = '
      <!-- google urchin code goes here -->
      ';
   }

   function GoogleAdsense ()
   {
      return $code = '
      <!-- google adsense code goes here -->
      ';
   }

}

?>
