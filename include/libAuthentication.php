<?php

$hidden_hash_var='your_password_here';

class Authentication {
  function start () {
      Authentication::load_userdata();
  }

/*
    global $db;

    $GLOBALS[ref_url] = $GLOBALS[PHP_SELF]."?".$GLOBALS[QUERY_STRING];

    session_cache_limiter('nocache');
    session_start();

    if (!session_is_registered("user_name")) {
      session_destroy();
    } else {
      session_register("user_name");
      session_register("user_id");
      session_register("id_hash");
      session_register("userData");
      Authentication::load_userdata();
    }
  }
*/

  function user_isloggedin() {
    global $hidden_hash_var, $LOGGED_IN;
    if (isset($LOGGED_IN)) {
      return $LOGGED_IN;
    }
//echo "<pre>";
//print_r($GLOBALS);
    if ($GLOBALS["user_name"] && $GLOBALS["id_hash"]) {
      $hash=md5($GLOBALS["user_name"].$hidden_hash_var);
      if ($hash == $GLOBALS["id_hash"]) {
        $LOGGED_IN=true;
        return true;
      } else {
        $LOGGED_IN=false;
        return false;
      }
    } else {
      $LOGGED_IN=false;
      return false;
    }
  }

   function user_login ($user_name,$password) {
               Authentication::user_set_tokens($user_name, $data[user_id]); //$user_name);
  }

   function user_logout() {
      session_destroy();
      return true;
  }

   function user_set_tokens($user_name, $user_id) {
      global $hidden_hash_var;

      session_start();
      session_register("user_id");
      session_register("user_name");
      session_register("id_hash");

      if (!$user_id) {
         $error .=  ' ERROR - User Name Missing When Setting Tokens ';
         return false;
      }

      $GLOBALS["user_id"] = $user_id;
      $GLOBALS["user_name"] = $user_name;
      $GLOBALS["id_hash"] = md5($GLOBALS["user_name"].$hidden_hash_var);
   }


  function user_getname() {
    if ($this->user_isloggedin()) {
      return $GLOBALS['user_name'];
    } else {
      //look up the user some day when we need it
      return ' ERROR - Not Logged In ';
    }
  }


  function load_userdata() {
    if (!isset($GLOBALS["userData"]) && !empty($GLOBALS["user_id"])) {
      $user['user_name'] = 'root';
      $user['user_id'] = 1;
      $user['site_id'] = 1;
      $user['groups'] = array();
      
      $GLOBALS["userData"] = $user;
    }

    return true;

    //global $db, $siteId;
    //if (!isset($GLOBALS["userData"]) && !empty($GLOBALS["user_id"])) {
    //  $query = "SELECT * FROM user WHERE user_id='".$GLOBALS["user_id"]."' AND site_id='$siteId'";
    //  $qhandle = New Query($query);
    //  if ($qhandle) {
    //    $data = $qhandle->execute();
    //    if ($GLOBALS["userData"] = $data) {
    //      return 1;
    //    } else {
    //      return 0;
    //    }
    //  } else {
    //    return 0;
    //  }
    //} else {
    //  return 0;
    //}
  }

  function user_in_group($group) {
    $array = $GLOBALS["userData"];
          $groups = explode(',', $array[groups]);  
    if (in_array($group, $groups)) {
      return 1;
    } else {
      return 0;
    }
  }

  function checklogin () {
    global $PHP_SELF, $QUERY_STRING, $siteUrl;

    $url = $PHP_SELF."?".$QUERY_STRING;
    $url = urlencode($url);

    if ( !Authentication::user_isloggedin() ) {
      header("Location: $siteUrl/my/login.php?refering_url=$url");
    } else {
      return 1;
    }
  }
}

?>
