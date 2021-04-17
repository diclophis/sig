<?php

/**
* Mysql DB API
*
* Contains methods related to accessing a MySQL server
*
* @version 0.0.9
* @author Jon Bardin <jon_bardin@softhome.net>
*/

class Database {
  /**
  * Database hostname
  *
  * @var string sys_dbhost
  * @access private
  */
  var $sys_dbhost;

  /**
  * Database username
  *
  * @var string sys_dbuser
  * @access private
  */
  var $sys_dbuser;

  /**
  * Database password
  *
  * @var string sys_dbpasswd
  * @access private
  */
  var $sys_dbpasswd;

  /**
  * Database dbname
  *
  * @var string sys_dbname
  * @access private
  */
  var $sys_dbname;

  /**
  * Connection
  *
  * @var integer conn
  * @access private
  */
  var $conn;

  /**
  * Query string
  *
  * @var string qstring
  * @access private
  */
   var $qstring;

  /**
  * Query handler
  *
  * @var string qhandle
  * @access private
  */
   var $qhandle;

  /**
  * Result handler
  *
  * @var string sys_dbhost
  * @access private
  */
   var $lhandle;

  function Database ($sys_dbhost, $sys_dbuser, $sys_dbpasswd, $sys_dbname) {
    $this->username = $sys_dbuser;
    $this->password = $sys_dbpasswd;
    $this->hostname = $sys_dbhost;
    $this->dbname = $sys_dbname;
    $this->error = false;
    $this->connect();
  }

   function connect() {
    //global $sys_dbhost,$sys_dbuser,$sys_dbpasswd;

      $this->conn = mysqli_connect($this->hostname,$this->username,$this->password,$this->dbname);
      if (!$this->conn) {
         $this->error = true;

         $msg = mysqli_connect_error();
         $this->DrawError($msg);

         throw new Exception("wtf");
         return false;
      }
      return true;
   }

   function DrawError($msg) {
      $htmlContainer = new Tag('html');
      $bodyContainer = new Tag('body');
      $navigationDiv = new Tag('div', array('id'=>'navigation'));
      $navigationDiv->AddElement($msg);
      $bodyContainer->AddElement($navigationDiv);
      $htmlContainer->AddElement($bodyContainer);
      echo $htmlContainer->DrawElements();
   }


    function disconnect() {
      mysqli_close($this->conn);
    }

   function query($dbname, $qstring, $print=0) {
      //global $sys_dbname;
      return mysqli_query($this->conn,$qstring);
   }

   function numrows($qhandle) { // return only if qhandle exists, otherwise 0
      if (is_resource($qhandle)) {
         return mysqli_numrows($qhandle);
      } else {
         return 0;
      }
   }

   function result($qhandle,$row,$field) {
      return mysqli_result($qhandle,$row,$field);
   }

   function numfields($lhandle) {
      return mysqli_numfields($lhandle);
   }

   function fieldname($lhandle,$fnumber) {
      return mysqli_fieldname($lhandle,$fnumber);
   }

   function affected_rows($qhandle) {
      return mysqli_affected_rows($this->conn);
   }

   function fetch_array($qhandle) {
      if ($qhandle === FALSE) {
        //$this->DrawError(mysqli_error($this->conn));
      } else {
        return mysqli_fetch_array($qhandle, mysqli_ASSOC);
      }
   }

   function fetch_object($qhandle) {
      if ($qhandle === FALSE) {
        //$this->DrawError(mysqli_error($this->conn));
      } else {
        return mysqli_fetch_object($qhandle);
      }
   }

   function fetch_row($qhandle) {
      return mysqli_fetch_row($qhandle);
   }
  
   function insertid() {
      return mysqli_insert_id($this->conn);
   }

   function error() {
      return mysqli_error($this->conn);
   }
}

?>
