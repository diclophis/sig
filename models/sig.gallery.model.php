<?php

/*

SiG | Templated auto thumbnailing image gallery utilizing zip files as archives

Copyright 2002/2003 Jon Bardin
released under the terms outlined in the GPL <www.gnu.org/gpl>

*/

//require('pclzip.lib.php');

class SiG_Gallery_Model {

   function SiG_Gallery_Model ()
   {

      $readerUrl = SiG::BaseUrl().'/gallery/index.php/';

      $imageUrl = SiG::BaseUrl().'/gallery/index.php/';

      $pathInfo = substr($_SERVER['PATH_INFO'], 1);

      $zipRepository = 'zips/';

      $maxUploads = 20;

      $timeout = 60;

      $path = 'tmp/';

      $browseX = 290;
      $thumbX = 240;

      $folderDelim = ";";
      $imageDelim = "$";
   }

/*

if (!function_exists('file_get_contents')) { function file_get_contents($filename)
   {
      $fd = fopen($filename, 'rb');
      $content = fread($fd, filesize($filename));
      fclose($fd);
      return $content;
   }
}
*/


   function getAllArchives ($htmlDataFormat = '<a href="%2$s%1$s/">%1$s</a> -- <a href="%2$sremove/%1$s">x</a><br>') {
   global $readerUrl, $zipRepository;

   $d = dir($zipRepository);
   while (false !== ($entry = $d->read())) {
      if (preg_match('/.zip/i', $entry)) {
         $return[$entry] = sprintf($htmlDataFormat, $entry, $readerUrl);
      }
   }
   $d->close();

   return $return;
   }

   function getArchiveImages ($archiveName) {

   global $zipRepository;

   $archive = New PclZip($zipRepository.$archiveName);

   foreach ($archive->listContent() as $index => $data) {
      $fileName = $data['filename'];

      if (($data['folder'] == 1) == true) {
         $index++;
         $map[$fileName] = $index;
         $dir[$index]['Name'] = $fileName;
         $dir[$index]['Images'] = array();
      } else {
         if (dirname($filename) == '.') {
            $map['./'] = 0;
            $dir[0]['Name'] = './';
            $dir[0]['Images'] = array();
         }

         $dir[$map[dirname($fileName).'/']]['Images'][$index] = basename($fileName);
      }
   }

   uasort($dir, 'sortArchiveFolders');
   $dir = array_map('cleanEntryName', $dir);

   foreach ($dir as $folderIndex => $folderData) {
      if (is_array($folderData)) {
      uasort($folderData['Images'], 'sortFolderImages');
      $dir[$folderIndex]['Images'] = array_map ('cleanEntryName', $folderData['Images']);
      }
   }

   return array('dir' => $dir, 'map' => $map);
   }

   function cleanEntryName ($name) {
   global $folderDelim;

   if (is_array($name) && isset($name['Name'])) {
      $name['Name'] = basename(stripDelim($name['Name'], $folderDelim));
      return $name;
   } else {
      return basename(stripDelim($name, $folderDelim));
   }
   }

   function stripDelim ($string, $delim) {
   //if (preg_match('/\-/i', $string)) {
   //   return substr($string, 0, strpos($string, ));
   //} else {
   //   return $string;
   //}

   return str_replace($delim, '', $string);
   }

   function sortArchiveFolders ($a, $b) {
   global $folderDelim;

   if (preg_match("/\\".$folderDelim."/i", $a['Name'])) {
      list($nameOne, $sortOne) = explode($folderDelim, $a['Name']);
   } else {
      $sortOne = $a['Name'];
   }

   if (preg_match("/\\".$folderDelim."/i", $b['Name'])) {
      list($nameTwo, $sortTwo) = explode($folderDelim, $b['Name']);
   } else {
      $sortTwo = $b['Name'];
   }

   if ($sortOne == './') { return 1; }

   if ($sortOne == $sortTwo) { return 0; }

   return (($sortTwo > $sortOne) ? -1 : 1); 
   }

   function sortFolderImages ($a, $b) {
   global $imageDelim;

   if (preg_match('/\-/i', $a)) {
      list($nameOne, $sortOne) = explode($imageDelim, $a);
   } else {
      $sortOne = $a;
   }

   if (preg_match('/\-/i', $b)) {
      list($nameTwo, $sortTwo) = explode($imageDelim, $b);
   } else {
      $sortTwo = $b;
   }

   if ($sortOne == $sortTwo) { return 0; }

   return (($sortTwo > $sortOne) ? -1 : 1);
   }

   function getNextFolderIndex ($archiveName, $folderIndex) {

   $archiveImages = getArchiveImages($archiveName);

   $arrayOfIndexes = array_keys($archiveImages['dir']);

   $position = array_search($folderIndex, $arrayOfIndexes);

   if ($position < (count($arrayOfIndexes) - 1)) {
      return $arrayOfIndexes[$position + 1];
   } else {
      return false;
   }
   }

   function getPreviousFolderIndex ($archiveName, $folderIndex) {

   $archiveImages = getArchiveImages($archiveName);

   $arrayOfIndexes = array_keys($archiveImages['dir']);

   $position = array_search($folderIndex, $arrayOfIndexes);

   if ($position > 0) {
      return $arrayOfIndexes[$position-1];
   } else {
      return false;
   }
   }

   function getArchiveHtml ($archiveName, 
                         $expandFolder = array(), 
                         $parentHtmlFormat = '<a href="%1$s">Up a level</a><br />',
                         $folderEntryHtmlFormat = '<p><a href="%6$s%5$s/%4$s">%3$s (%2$s) <ul style="list-style-type: none;">%1$s</ul></p>',
                         $imageEntryHtmlFormat = '<li><a href="%5$s%4$s/%3$s,%2$s">
                                                  <img src="%5$s%4$s/%3$s,%2$s.browse" style="vertical-align: middle; border: 1px solid black;" border="0"/></a> %1$s</li>') {
   global $readerUrl;

   $archiveImages = getArchiveImages($archiveName);

   $return .= sprintf($parentHtmlFormat, $readerUrl);

   foreach ($archiveImages['dir'] as $folderIndex => $data) {
      $imageEntryHtml = '';
      if (in_array($folderIndex, $expandFolder)) {
         foreach ($data['Images'] as $imgIndex => $imgName) {
            $imageEntryHtml .= sprintf($imageEntryHtmlFormat, $imgName, $imgIndex, $folderIndex, $archiveName, $readerUrl);
         }
      }

      $return .= sprintf($folderEntryHtmlFormat, $imageEntryHtml, count($data['Images']), $data['Name'], $folderIndex, $archiveName, $readerUrl);
   }

   return $return;
   }

   function getFolderHtml ($archiveName, $folderIndex, $parentHtmlFormat = '<a href="%2$s%1$s">%1$s</a><br />%3$s',
                                                    $imageEntryHtmlFormat = 
'<a href="%5$s%4$s/%3$s,%2$s"><img src="%5$s%4$s/%3$s,%2$s.thumbnail" title="%1$s" alt="%1$s" border="0" /></a>',
$mod = 1, $modHtmlAlt = '<br />') {
   global $readerUrl;

   $archiveImages = getArchiveImages($archiveName);

   //uasort($archiveImages['dir'][$folderIndex]['Images'], 'sortFolderImages');

   $m = 0;
               
   foreach ($archiveImages['dir'][$folderIndex]['Images'] as $imgIndex => $imgTitle) {
      if (($m++ % $mod) == 0) {
         $imageEntriesHtml .= $modHtmlAlt;
      }

      $imageEntriesHtml .= sprintf($imageEntryHtmlFormat, $imgTitle, $imgIndex, $folderIndex, $archiveName, $readerUrl);
   }

   $return = sprintf($parentHtmlFormat, $archiveName, $readerUrl, $imageEntriesHtml);

   return $return;
   }

   function getImageHtml ($archiveName, $folderIndex, $imageIndex, $parentHtmlFormat = '<p align="center"><a href="%4$s%3$s/%2$s">%1$s</a>&nbsp;',
                                                                $nextHtmlFormat = '<a href="%5$s%4$s/%3$s,%2$s">%1$s</a>&nbsp;',
                                                                $imageHtmlFormat = '</p><p align="center"><img src="%5$s%4$s/%3$s,%2$s.%1$s" alt="%2$s" title="%1$s"></p>') {
   global $readerUrl;

   $archiveImages = getArchiveImages($archiveName);

   $return = sprintf($parentHtmlFormat, $archiveImages['dir'][$folderIndex]['Name'], $folderIndex, $archiveName, $readerUrl);

   $i = 0;

   //uasort($archiveImages['dir'][$folderIndex]['Images'], 'sortFolderImages');

   foreach ($archiveImages['dir'][$folderIndex]['Images'] as $tmpIndex => $tmpTitle) {
      if ($i++) {
         $return .= ',';
      }

      if ($imageIndex == $tmpIndex) {
         $return .= $i;
         $imageName = $tmpTitle;
      } else {
         $return .= sprintf($nextHtmlFormat, $i, $tmpIndex, $folderIndex, $archiveName, $readerUrl);
      } 
   }

   $return .= sprintf($imageHtmlFormat, $imageName, $imageIndex, $folderIndex, $archiveName, $readerUrl); 

   return $return;
   }

   function getImageDataAndDie ($archiveName, $folderIndex, $imageIndex, $imageType) {
   global $zipRepository, $path, $timeout, $browseX, $thumbX;

   $cacheFileName = $path.$archiveName.$folderIndex.$imageIndex.$imageType;

   if (!is_file($cacheFileName) || (time() - filectime($cacheFileName)) > $timeout) {

      $archive = New PclZip($zipRepository.$archiveName);

      $data = ($archive->extractByIndex(intval($imageIndex), PCLZIP_OPT_PATH, $path, PCLZIP_OPT_REMOVE_ALL_PATH));

      if ($imageType == 'browse') {
         if (is_file($cacheFileName)) { unlink($cacheFileName); }
         resizeToFile($data[0]['filename'], $cacheFileName, $browseX);
         unlink($data[0]['filename']);
      } elseif ($imageType == 'thumbnail') {
         if (is_file($cacheFileName)) { unlink($cacheFileName); }
         resizeToFile($data[0]['filename'], $cacheFileName, $thumbX);
         unlink($data[0]['filename']);
      } else {
         rename($data[0]['filename'], $cacheFileName);
      }

      chmod($cacheFileName, 777);
   }

   readfile($cacheFileName);
   die();
   }

   function resizeToFile ($sourcefile, $targetfile, $max_x) {
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

   $target_id=imagecreate($dest_x, $dest_y);

   $target_pic=imagecopyresized($target_id,$source_id,
                                  0,0,0,0,
                                  $dest_x,$dest_y,
                                  $source_x,$source_y);

   imagedestroy($source_id);
   imagejpeg ($target_id,$targetfile,100);
   return true;
   }

/*

if (basename(__FILE__) == basename($GLOBALS['SCRIPT_FILENAME'])) {

   list($archiveName, $archiveFolder) = explode('/', $pathInfo);

   if (strlen($archiveName)) {
      switch ($archiveName) {
         default:
            if (strlen($archiveFolder)) {
               list($folderIndex, $folderImage) = explode(',', $archiveFolder);
               if (strlen($folderImage)) {
                  list($imageIndex, $imageType) = explode('.', $folderImage);
                  if (strlen($imageType)) {
                     echo getImageDataAndDie($archiveName, $folderIndex, $imageIndex, $imageType);
                  } else {
                     echo '<p align="center">Level 3</p>';
                     echo getImageHtml($archiveName, $folderIndex, $imageIndex);
                  }
               } else {
                  echo 'Level 2<br />';
                  echo getFolderHtml($archiveName, $folderIndex);
               }
            } else {
               echo 'Level 1<br />';
               echo getArchiveHtml($archiveName);
            }
         break;

         case 'upload':
            if ($_REQUEST['status'] == 'Send') {
               foreach ($_FILES as $name => $data) {
                  if (move_uploaded_file($data['tmp_name'], $zipRepository.$data['name'])) {
                     echo 'Success '.$name.'<br />';
                  } else {
                     echo 'Error';
                  }
               }

               //header('Location: '.$readerUrl);
            } elseif ($_REQUEST['status'] == 'Cancel') {
               header('Location: '.$readerUrl);
            }

            $numOfUploads = intval($archiveFolder);
            if ($numOfUploads == 0) {
               $numOfUploads = 1;
            } elseif ($_REQUEST['status'] == 'More' && $numOfUploads < $maxUploads) {
               $numOfUploads = $archiveFolder + 1;
            } elseif ($_REQUEST['status'] == 'Less' && $numOfUploads > 1) {
               $numOfUploads = $archiveFolder - 1;
            }

            echo '<html>
                  <head>
                  <title>test</title>
                  </head>
                  <body>
                  <form enctype="multipart/form-data" action="'.$readerUrl.'upload/'.$numOfUploads.'" method="post">';

            for ($i=0; $i<$numOfUploads && $i<$maxUploads; $i++) {
               echo 'Send this file: <input name="file-'.$i.'" type="file"><br />';
            }

            echo '<input type="submit" name="status" value="More">
                  <input type="submit" name="status" value="Less">
                  <input type="submit" name="status" value="Send">
                  <input type="submit" name="status" value="Cancel">
                  </form>';
         break;

         case 'remove':
            die('Not enabled');
            unlink($zipRepository.$archiveFolder);
            header('Location: '.$readerUrl);
         break;
      }
   } else {
      echo 'Level 0<br />';
      foreach (getAllArchives() as $archiveName => $archiveUrl) {
         echo $archiveUrl;
      }
      echo '<a href="'.$readerUrl.'upload">Upload</a>';
   }
}

*/

?>
