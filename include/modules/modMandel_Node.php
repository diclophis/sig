<?

class Mandel_Node extends Node {

      var $struct = array( "node_type" => "Mandel_Node",
                           "title"     => array( "prop_type" => "text",
                                                "values"    => ""),
                           "maxiter"     => array( "prop_type" => "text",
                                                "values"    => ""),
                           "centerX"     => array( "prop_type" => "text",
                                                "values"    => ""),
                           "centerY"     => array( "prop_type" => "text",
                                                "values"    => ""),
                           "squareWidth"     => array( "prop_type" => "text",
                                                "values"    => ""),
                           "modeX"     => array( "prop_type" => "text",
                                                "values"    => ""),
                           "modeY"     => array( "prop_type" => "text",
                                                "values"    => ""));

   function DefaultHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
   {
      $maxiter = $this->get_property_member('maxiter', 'value');

      if (array_key_exists('fractal_x', $_REQUEST) && array_key_exists('fractal_y', $_REQUEST)) {
         $centerX = ((-$_REQUEST['bb'] + $_REQUEST['fractal_x']) / $_REQUEST['aa']) + $_REQUEST['centerX'];
         $centerY = ((-$_REQUEST['bb'] + $_REQUEST['fractal_y']) / $_REQUEST['aa']) + $_REQUEST['centerY'];
         $squareWidth = $_REQUEST['zoom'] * $_REQUEST['squareWidth'];
      } else {
         $centerX = $this->get_property_member('centerX', 'value');
         $centerY = $this->get_property_member('centerY', 'value');
         $squareWidth = $this->get_property_member('squareWidth', 'value');
      }

      $modeX = $this->get_property_member('modeX', 'value');
      $modeY = $this->get_property_member('modeY', 'value');

      $halfModeX = $modeX / 2;
      $halfModeY = $modeY / 2;

      $halfWidth = $squareWidth / 2;

      /* Get the corners */
      $leftX = 0 - $halfWidth;
      $rightX = 0 + $halfWidth;
      $lowerY = 0 - $halfWidth;
      $upperY = 0 + $halfWidth;


      /* I think this is for zoom */
      $xModPercent = -$leftX / (-$leftX - -$rightX);
      $yModPercent = (1 - $lowerY) / ((1 - $lowerY) - (1 - $upperY));
      $bb = $xModPercent * $modeY;
      $aa = $yModPercent * $modeY - $bb;

      $store_dir = ABSPATH.'/images/fractals/';

      $temp_id=sprintf("%f.%f.%f.%f.%f.%f.%f.png",$this->id,$this->maxiter->value,$centerX,$centerY,$squareWidth,$modeX,$modeY);

      if (!file_exists($store_dir.$temp_id)) {
         $this->im = ImageCreate($modeX, $modeY);
         $this->colors = $this->initcolors();

         for ($screenX=0; $screenX<=$halfModeX; $screenX++) {
            $halfScreenX = $screenX + $halfModeX;
            $sea1 = ((-$bb + $screenX) / $aa) + $centerX;
            $sea3 = ((-$bb + $halfScreenX) / $aa) + $centerX;
            for ($screenY=0; $screenY<=$modeY; $screenY++) {
               $sea2 = ((-$bb + $screenY) / $aa) + $centerY;
               $this->mandelIterate($sea1, $sea2, $screenX, $screenY);
               $this->mandelIterate($sea3, $sea2, $halfScreenX, $screenY);
            }
         }

         imagepng($this->im, $store_dir.$temp_id);
      }

      $return = '<form method="post" action="'.SiG_Plugin_Controller::Permalink().'">
                 <input type="hidden" name="parent_id" value="'.$this->id.'"/>
                 <input type="hidden" name="centerX" value="'.$centerX.'" />
                 <input type="hidden" name="centerY" value="'.$centerY.'" />
                 <input type="hidden" name="aa" value="'.$aa.'" />
                 <input type="hidden" name="bb" value="'.$bb.'" />
                 <input type="hidden" name="squareWidth" value="'.$squareWidth.'" />
                 <input type="hidden" name="zoom" value="0.75" />
                 <input type="image" src="/images/fractals/'.$temp_id.'" name="fractal" /></form>';

      /*
      $return .= '<a href="admin/node/?status=New&struct[node_type]=Mandel_Node&node_id_c='
               . $this->id
               . '&struct[squareWidth]='.$squareWidth
               . '&struct[centerX]='.$centerX
               . '&struct[centerY]='.$centerY
               . '&struct[modeX]='.$modeX
               . '&struct[modeY]='.$modeY
               . '&struct[maxiter]='.$this->maxiter->value.'">Bookmark current fractal</a>';


      $return .= '<ul>Bookmarks';
      foreach ($this->get_array_of_children() as $child) {
         $return .= '<li><a href="?parent_id='.$child->id.'">'.$child->title->value.'</a></li>';
      }
      $return .= '</ul>';
      */

      //$div = new Tag('div');
      //$div->AddElement($return);
      $container->AddElement($return);
   }

   function initcolors ()
   {
      $colors = 3;
      (int) $timesPerColor = (int) $this->maxiter->value / $colors;
      (int) $inc = (int) (256 / $timesPerColor);

      for ($i=0; $i<$timesPerColor; $i++) {
         $red = $i * $inc;
         $c[] = imagecolorallocate($this->im, $red, 0, 0);
      }

      for ($i=0; $i<$timesPerColor; $i++) {
         $green = $i * $inc;
         $red-=$inc;
         $c[] = imagecolorallocate($this->im, $red, $green, 0);
      }

      for ($i=0; $i<$timesPerColor; $i++) {
         $blue = $i * $inc;
         $c[] = imagecolorallocate($this->im, 0, 0, $blue);
      }

      return $c;
   }

   function mandelIterate ($seadX, $seadY, $scrX, $scrY)
   {
      $X = $seadX;
      $Y = $seadY;
      $maxiter = $this->maxiter->value;
      do {
         $tempX = $X * $X - $Y * $Y + $seadX;
         $tempY = 2 * $X * $Y + $seadY;
         $X = $tempX;
         $Y = $tempY;
         $modulus = $X * $X + $Y * $Y;
         $maxiter--;
      } while ($maxiter>0 && $modulus < 4);

      imagesetpixel ($this->im, $scrX, $scrY, $this->colors[$maxiter]);
   }
}

?>
