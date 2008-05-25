<?

/**
* Cache file class
*
* This class is used to manage cache files
*
* @version 0.0.9
* @author Jon Bardin <jon_bardin@softhome.net>
*/

class Cache {
	/**
	* Cache object Id.
	*
	* @var string cacheObjectId
	* @access private
	*/
	var $cacheObjectId = "";

	/**
	* Cache object timeout.
	*
	* @var integer [$timeout] timeout
	* @access private
	*/
	var $timeout;

	/**
	* Constructor
	*
	* Sets cache object path
	*
	* @param string cacheObjectId
	* @param integer timeout
	* @return boolean
	* @access public
	*/
	function Cache ($cacheObjectId, $timeout) {
		//$this->cacheObjectId = '/home/jeftep/tmp/pagecache/'.$cacheObjectId;
		$this->cacheObjectId = '/tmp/SiG-'.$cacheObjectId;
		$this->timeout = $timeout;
		return true;
	}

	/**
	* Create cache object.
	*
	* Creates a cache object with passed contents
	*
	* @param string contents
	* @return boolean
	* @access private
	*/
	function createCacheObject ($contents) {
		$fp = fopen ($this->cacheObjectId, w);
		if (!empty($fp)) {
			if (fputs ($fp, $contents)) {
				fclose($fp);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	* Updates cache object.
	*
	* Updates a cache object with passed contents
	*
	* @param string contents
	* @return string contents
	* @access public
	*/
	function updateCacheObject ($contents) {
		//check to see if object exists
		if ($this->cacheObjectExists()) {
			//check if its locked
			if (!$this->cacheObjectLocked()) {
				//if object exists lock it
				$this->lockCacheObject();

				//then delete the old file and create new one
				//$this->deleteCacheObject();
				$this->createCacheObject($contents);
				$this->unlockCacheObject();
				return $contents;
			} else {
				return $this->loadLockedObject();
			}
		} else {
			$this->createCacheObject($contents);
			return $contents;
		}
	}

	/**
	* Load cache object.
	*
	* Loads cache object and returns its contents
	*
	* @return string contents
	* @access public
	*/
	function loadCacheObject () {
			if (!$this->cacheObjectLocked()) {
				return $this->cacheObjectContents($this->cacheObjectId);
			} else {
				return $this->loadLockedObject();
			}
	}

	/**
	* Delete cache object.
	*
	* Deletes a cache object
	*
	* @return boolean
	* @access private
	*/
	function deleteCacheObject () {
		if (!$this->cacheObjectLocked() && is_file($this->cacheObjectId)) {
			if (unlink($this->cacheObjectId)) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	/**
	* Lock cache object.
	*
	* Locks a cache object for updating
	*
	* @return boolean
	* @access private
	*/
	function lockCacheObject () {
		if (copy($this->cacheObjectId, $this->cacheObjectId.'.lock')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Unlock cache object.
	*
	* Unlocks a cache object for updating
	*
	* @return boolean
	* @access private
	*/
	function unlockCacheObject () {
		if (unlink($this->cacheObjectId.'.lock')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Check cache object.
	*
	* Checks to see if the cache object exists or not
	*
	* @return boolean
	* @access private
	*/
	function cacheObjectExists () {
		if (is_file($this->cacheObjectId)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Determines if cache object is locked.
	*
	* Checks to see if the cache object is locked or not
	*
	* @return boolean
	* @access private
	*/
	function cacheObjectLocked () {
		if (is_file($this->cacheObjectId.'.lock')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Cached object has timed out.
	*
	* Checks to see if the cache object has expired or not
	*
	* @return boolean
	* @access public
	*/
	function cacheObjectTimedOut () {
		if ($this->cacheObjectExists()) {
			if (time() - filemtime($this->cacheObjectId) > $this->timeout) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	/**
	* Load locked cache object.
	*
	* Loads a locked cache object and returns its contents
	*
	* @return string
	* @access public
	*/
	function loadLockedObject() {
		return $this->cacheObjectContents($this->cacheObjectId.'.lock');
	}

	/**
	* Cache object contents.
	*
	* Loads a file and returns its contents
	*
	* @param string filename
	* @return string
	* @access private
	*/
	function cacheObjectContents ($filename) {
		$fd = fopen ($filename, "r");
		if (!empty($fd)) {
			$contents = fread ($fd, filesize ($filename));
			fclose ($fd);
			return $contents;
		} else {
			die;
		}
	}
}

?>
