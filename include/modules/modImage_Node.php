<?

   class Image_Node extends Node {
      var $struct = array( "node_type" => "Image_Node",
                           "title"     => array( "prop_type" => "text",
                                                 "values"    => "New Image"),

                           "image"      => array( "prop_type" => "callback",
                                                 "name" => "image_upload"),
                           /*
                           "on_default_page" => array( "prop_type" => "dropdown",
                                                       "values"    => array( "Yes" => 1,
                                                                             "No"  => 0 )),

                           "on_default_nav"  => array( "prop_type" => "dropdown",
                                                       "values"    => array( "Yes" => 1,
                                                                             "No"  => 0 )));
                           */
                           );


      var $allowed_children_types = array (); //Not sure what to allow here!

      function ImagePath ($imageName)
      {
         $path = SiG_Controller::BasePath().'/tmp/'.$imageName;
         return $path;
      }

      function ImageUrl ($imageName)
      {
         $url = SiG_Controller::BaseUrl().'/tmp/'.$imageName;
         return $url;
      }

      function UnlinkFile ($filename)
      {
         if (is_file($filename)) {
            return unlink($filename);
         } else {
            return FALSE;
         }
      }


/*      
      function purge_old_file ()
      {
         $image_filename = $this->get_property_member('body', 'value');
         $thumbnail_filename = 'thumbnail_'.$image_filename;
         
         Image_Node::UnlinkFile(Image_Node::ImagePath($image_filename));
         Image_Node::UnlinkFile(Image_Node::ImagePath($thumbnail_filename)); 
      }

      function delete ()
      {
         $this->purge_old_file();
         return parent::delete();
      }
*/

      function ImageUploadElement ($node, $fieldname, $filename)
      {
         $container = new Tag('div');
         if (SiG_Session::Instance()->IsUploadedFile($fieldname)) {
            $image_filename = $node->get_property_member($fieldname, 'value');
            $thumbnail_filename = 'thumbnail_'.$image_filename;
         
            Image_Node::UnlinkFile(Image_Node::ImagePath($image_filename));
            Image_Node::UnlinkFile(Image_Node::ImagePath($thumbnail_filename)); 
            
            //$this->purge_old_file();
            $new_name = uniqid(5).basename(SiG_Session::Instance()->UploadedFilename($fieldname));
            $thumbnail = 'thumbnail_'.$new_name;
            
            $store_file_name = Image_Node::ImagePath($new_name); //$this->image_path.$new_name;
            $store_file_thumbnail_name = Image_Node::ImagePath($thumbnail); //$this->image_path.$thumbnail;

            SiG_Session::Instance()->MoveUploadedFile($fieldname, $store_file_name);

            Image_Node::MakeThumbnail(100,100, $store_file_name, $store_file_thumbnail_name);

            $node->$fieldname->set_value($new_name, $node->new);
            
            $http_path_to_image = Image_Node::ImageUrl($thumbnail);
            $filename = $new_name;
         } elseif ($this->id && $this->new) {
            $this->$fieldname->set_value('', $this->new);
         }

         $p = new Tag('p');
         $a = new Tag('a', array('href'=>Image_Node::ImageUrl($filename), 'target'=>'_new'));
         $img = new Tag('img', array('src'=>Image_Node::ImageUrl('thumbnail_'.$filename)));
         $a->AddElement($img);
         $p->AddElement($a);
         $container->AddElement($p);

         $uploadElement = new Tag('input', array('type'=>'file', 'name'=>$fieldname));
         $container->AddElement($uploadElement);
         return $container;
      }

      function BrowseTitleElement ()
      {
         $filename = $this->get_property_member('image', 'value');
         $img = new Tag('img', array('src'=>Image_Node::ImageUrl('thumbnail_'.$filename)));
         return $img;
      }

      function image_upload ($name, $value)
      {
         return Image_Node::ImageUploadElement($this, $name, $value);
      }

      function DrawEmbeded ()
      {
         $filename = $this->get_property_member('image', 'value');
         $store_filename = $filename;
         if (strlen($store_filename)) {
            $image_info = getimagesize(Image_Node::ImagePath($store_filename));
         } else {
            $image_info = array();
         }
         $thumbnail = "thumbnail_".$filename;
         $http_path_to_image = Image_Node::ImageUrl($filename);
         $http_path_to_thumbnail = Image_Node::ImageUrl($thumbnail);

         $title = ($this->title->value);
         if (strlen($filename)) {
            $img = new Tag('img', array('class'=>'Embeded_Image_Node', 'src'=>$http_path_to_thumbnail, 'alt'=>$title));
            return $img->DrawElements();
         } else {
            return NULL;
         }
      }

      function DefaultHtmlData ($container, $parentNode = NULL)
      {
         $filename = $this->get_property_member('image', 'value');
         $store_filename = $filename;
         if (strlen($store_filename)) {
            $image_info = getimagesize(Image_Node::ImagePath($store_filename));
         } else {
            $image_info = array();
         }
         $thumbnail = "thumbnail_".$filename;
         $http_path_to_image = Image_Node::ImageUrl($filename);
         $http_path_to_thumbnail = Image_Node::ImageUrl($thumbnail);

         $div = new Tag('div', array('class'=>'Image_Node'));

         $titleP = new Tag('p');
         $titleP->AddElement($this->title->value);
         $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$this->id));
         if (strlen($filename)) {
            $div->AddElement($titleP);
            $img = new Tag('img', array('src'=>$http_path_to_thumbnail));
            $a->AddElement($img);
         } else {
            $a->AddElement($titleP);
         }
         $div->AddElement($a);

         $infoP = new Tag('p');
         $infoP->AddElement($image_info[0].'x'.$image_info[1]);
         $div->AddElement($infoP);

         $container->AddElement($div);
      }

      function ActiveHtmlData ($container, $parentNode = NULL)
      {
         $filename = $this->get_property_member('image', 'value');
         $store_filename = $filename;
         if (strlen($store_filename)) {
            $image_info = getimagesize(Image_Node::ImagePath($store_filename));
         } else {
            $image_info = array();
         }
         $thumbnail = "thumbnail_".$filename;
         $http_path_to_image = Image_Node::ImageUrl($filename);
         $http_path_to_thumbnail = Image_Node::ImageUrl($thumbnail);

         $activeId = SiG_Session::Instance()->Request('active_id', NULL);

         $div = new Tag('div', array('class'=>'Image_Node'));

         if ($parentNode->get_num_of_children() > 1) {
               $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$parentNode->id));
               $a->AddElement('Thumbnails...');
               //$div->AddElement($a);
         } else {
                  $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink()));
                  $a->AddElement('Back to Thumbnail');
               //   $div->AddElement($a);
         }


         $peerP = new Tag('p');
         $peerP->AddElement($a);
         if ($parentNode->get_num_of_children() > 1) {
            $count = 0;
            $peerP->AddElement('&nbsp;(&nbsp;');
            foreach ($parentNode->get_array_of_children() as $peer) {
               $count++;
               if ($peer->id != $this->id) {
                  $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$peer->id));
                  $a->AddElement($count);
                  $peerP->AddElement($a);
               } else {
                  $peerP->AddElement($count);
               }
               $peerP->AddElement('&nbsp;');
            }
            $peerP->AddElement(')</br>');
         }

         $div->AddElement($peerP);

         $imgA = new Tag('a', array('target'=>'new', 'href'=>$http_path_to_image));
         $img = new Tag('img', array('src'=>$http_path_to_image, 'style'=>'width: 100%;'));
         $imgA->AddElement($img);
         $div->AddElement($imgA);

         $container->AddElement($div);
      }

      /*
      function FormattedBody ($container, $parent_id)
      {
         $filename = $this->get_property_member('image', 'value');
         $store_filename = $filename;
         if (strlen($store_filename)) {
            $image_info = getimagesize(Image_Node::ImagePath($store_filename));
         } else {
            $image_info = array();
         }
         $thumbnail = "thumbnail_".$filename;
         $http_path_to_image = Image_Node::ImageUrl($filename);
         $http_path_to_thumbnail = Image_Node::ImageUrl($thumbnail);

         $activeId = SiG_Session::Instance()->Request('active_id', NULL);

         $div = new Tag('div', array('class'=>'Image_Node'));
         if ($activeId == $this->id) {
               if ($this->get_num_of_peers($parent_id) > 1) {
                  $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$this->parent_id));
                  $a->AddElement('Back to Thumbnails...');
                  $div->AddElement($a);
               } else {
                  $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink()));
                  $a->AddElement('Back to Thumbnail');
                  $div->AddElement($a);
               }


               $peerP = new Tag('p');
               if ($this->get_num_of_peers($parent_id) > 1) {
                  $count = 0;
                  $peerP->AddElement('(&nbsp;');
                  foreach ($this->get_array_of_peers($parent_id) as $peer) {
                     $count++;
                     if ($peer->id != $this->id) {
                        $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$peer->id));
                        $a->AddElement($count);
                        $peerP->AddElement($a);
                     } else {
                        $peerP->AddElement($count);
                     }
                     $peerP->AddElement('&nbsp;');
                  }
                  $peerP->AddElement(')</br>');
               }
               $div->AddElement($peerP);

               $img = new Tag('img', array('src'=>$http_path_to_image, 'style'=>'width: 100%;'));
               $div->AddElement($img);
            //}
         } else {
            //Thumbnail

            $titleP = new Tag('p');
            $titleP->AddElement($this->title->value);
            $a = new Tag('a', array('href'=>SiG_Plugin_Controller::Permalink().'&active_id='.$this->id));
            if (strlen($filename)) {
               $div->AddElement($titleP);
               $img = new Tag('img', array('src'=>$http_path_to_thumbnail));
               $a->AddElement($img);
            } else {
               $a->AddElement($titleP);
            }
            $div->AddElement($a);

            $infoP = new Tag('p');
            $infoP->AddElement($image_info[0].'x'.$image_info[1]);
            $div->AddElement($infoP);

         }
         $container->AddElement($div);
      }
      */

      function depc_make_thumbnail($width, $height, $src, $dst)
      {
         system("/usr/bin/convert" . " -geometry " . $width. "x" . $height . " \"" . $src . "\" \"" . $dst . "\"");
         return filesize($dst);
      }

      function MakeThumbnail ($max_x, $height, $sourcefile, $targetfile)
      {
         $picsize=getimagesize($sourcefile);
         $source_x = $picsize[0];
         $source_y  = $picsize[1];
         switch ($picsize[2]) {
            case 1:
               $source_id = imageCreateFromGif($sourcefile);
            break;

            case 2:
               $source_id = imageCreateFromJpeg($sourcefile);
            break;

            case 3:
               $source_id = imageCreateFromPng($sourcefile);
            break;
         }

         $dest_x = $max_x;
         $zoom = 1 - (($source_x - $max_x) / $source_x);
         $dest_y = intval($zoom * $source_y);
         $target_id=imagecreatetruecolor($dest_x, $dest_y);
         $target_pic=imagecopyresized($target_id,$source_id,
                                      0,0,0,0,
                                      $dest_x,$dest_y,
                                      $source_x,$source_y);
         imagedestroy($source_id);
         switch ($picsize[2]) {
            case 1:
               die('gif support?');
            break;

            case 2:
               imagejpeg($target_id,$targetfile,100);
            break;

            case 3:
               imagepng($target_id,$targetfile,100);
            break;
         }
         return true;
      }
   }

?>
