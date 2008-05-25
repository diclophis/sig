<?

   class Document_Node extends Node {
      var $struct = array( "node_type" => "Document_Node",
                           "title"     => array( "prop_type" => "text",
                                                 "values"    => ""),

                           "file"      => array( "prop_type" => "callback",
                                                 "name" => "file_upload"),
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

      function purge_old_file ()
      {
         $image_filename = $this->get_property_member('file', 'value');

         if (is_file($this->ImagePath($image_filename))) {
            unlink($this->ImagePath($image_filename));
         }
      }

      function delete ()
      {
         $this->purge_old_file();
         return parent::delete();
      }

      function file_upload ($name, $filename)
      {
         $container = new Tag('div');
         if (SiG_Session::Instance()->IsUploadedFile('upload_file')) {
            $this->purge_old_file();
            $new_name = uniqid(5).basename(SiG_Session::Instance()->UploadedFilename('upload_file'));

            $store_file_name = $this->ImagePath($new_name); //$this->image_path.$new_name;

            SiG_Session::Instance()->MoveUploadedFile('upload_file', $store_file_name);

            $this->$name->set_value($new_name, $this->new);
            $http_path_to_image = $this->ImageUrl($new_name);
            $filename = $new_name;
         } elseif ($this->id && $this->new) {
            $this->$name->set_value('', $this->new);
         }

         if (strlen($filename)) {
            $p = new Tag('p');
            $a = new Tag('a', array('href'=>$this->ImageUrl($filename), 'target'=>'_new'));
            $a->AddElement('Download');
            $p->AddElement($a);
            $container->AddElement($p);
         }

         $uploadElement = new Tag('input', array('type'=>'file', 'name'=>'upload_file'));
         $container->AddElement($uploadElement);
         return $container;
      }

      function filesize_format ($bytes, $decimal='.', $spacer=' ', $lowercase=false) {
         $bytes = max(0, (int)$bytes);
         $units = array('YB' => 1208925819614629174706176, // yottabyte
                 'ZB' => 1180591620717411303424,    // zettabyte
                 'EB' => 1152921504606846976,      // exabyte
                 'PB' => 1125899906842624,          // petabyte
                 'TB' => 1099511627776,            // terabyte
                 'GB' => 1073741824,                // gigabyte
                 'MB' => 1048576,                  // megabyte
                 'KB' => 1024,                      // kilobyte
                 'B'  => 0);                        // byte

         foreach ($units as $unit => $qty) {
            if ($bytes >= $qty) {
               $broken_number = explode('.', $bytes);
               //if (!$broken_number[1])
                  return number_format(!$qty ? $bytes: $bytes /= $qty).$spacer.$unit;
               //else
               //   return number_format(!$qty ? $bytes: $bytes /= $qty).$decimal.substr($broken_number[1], 0, 2).$spacer.$unit;
            }
         }
      }
      function DefaultHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
      {
         $filename = $this->get_property_member('file', 'value');
         $p = new Tag('p');
         $p->AddElement($this->filesize_format(filesize($this->ImagePath($filename))));
         $a = new Tag('a', array('href'=>$this->ImageUrl($filename)));
         $a->AddElement('&nbsp;Download');
         $p->AddElement($a);
         $container->AddElement($p);
      }


   }

?>
