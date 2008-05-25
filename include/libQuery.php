<?

class Query {
	function Query ($query, $debug = 1) {
/*
      global $wpdb;

      $wpdb->query($query);

      $this->numrows = $wpdb->num_rows;
      $this->affected_rows = $wpdb->rows_affected;
      $this->insertid = $wpdb->insert_id;

      $this->pointer = 0;

      if (count($wpdb->last_result)) {
         $this->results = $wpdb->last_result;
      } else {
         $this->results = array();
      }
*/

      
		global $db, $rpc_server, $rpc_server_port, $sys_dbhost, $sys_dbuser, $sys_dbpasswd, $sys_dbname;

		if (!is_object($db)) {
			$db = New Database ($sys_dbhost, $sys_dbuser, $sys_dbpasswd);
		}

		//$GLOBALS[dbcalls]++;

		$this->query = $query;
		$this->result = $db->query($sys_dbname, $this->query);
		if ($db->numrows($this->result)) {
			$this->numrows = $db->numrows($this->result);
		} else {
			$this->affected_rows = $db->affected_rows($this->result);
			$this->insertid = $db->insertid($this->result);
		}

		if ($debug) {
         //$db->error();
			//echo $db->debug_msg;
		}
	}

	function execute ($type = 'object') {
		global $db, $sys_dbname;

		$fetch = 'fetch_'.$type;


		return $db->$fetch($this->result);

      /*
      if ($this->pointer < count($this->results)) {
         return $this->results[$this->pointer++];
      }
      */
	}
}

?>
