<?

class SiG_Plugin_View extends SiG_View {
   function SiG_Plugin_View ()
   {
      add_action('wp_head', array($this, 'echoWpHead'));
   }

   function echoWpHead ()
   {
      echo '<!-- SiG Plugin Header -->';
   }
}

?>
