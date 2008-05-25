<?

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

	function Database ($sys_dbhost, $sys_dbuser, $sys_dbpasswd) {
		$this->username = $sys_dbuser;
		$this->password = $sys_dbpasswd;
		$this->hostname = $sys_dbhost;
		$this->error = false;
		$this->connect();
	}

   function connect() {
		//global $sys_dbhost,$sys_dbuser,$sys_dbpasswd;

      $this->conn = mysql_connect($this->hostname,$this->username,$this->password);
      if (!$this->conn) {
         $this->error = true;
         echo $this->debug_msq = mysql_error();
         return false;
      }
      return true;
   }

	function disconnect() {
		mysql_close($this->conn);
	}

   function query($dbname, $qstring, $print=0) {
      //global $sys_dbname;
      return mysql_db_query($dbname,$qstring);
   }

   function numrows($qhandle) { // return only if qhandle exists, otherwise 0
      if (is_resource($qhandle)) {
         return mysql_numrows($qhandle);
      } else {
         return 0;
      }
   }

   function result($qhandle,$row,$field) {
      return mysql_result($qhandle,$row,$field);
   }

   function numfields($lhandle) {
      return mysql_numfields($lhandle);
   }

   function fieldname($lhandle,$fnumber) {
      return mysql_fieldname($lhandle,$fnumber);
   }

   function affected_rows($qhandle) {
      return mysql_affected_rows();
   }

   function fetch_array($qhandle) {
      echo mysql_error();
      return mysql_fetch_array($qhandle, MYSQL_ASSOC);
   }

   function fetch_object($qhandle) {
      echo mysql_error();
      return mysql_fetch_object($qhandle);
   }

   function fetch_row($qhandle) {
      return mysql_fetch_row($qhandle);
   }
	
   function insertid() {
      return mysql_insert_id();
   }

   function error() {
      return mysql_error();
   }
}

?>
