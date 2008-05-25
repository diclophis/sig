<?

$hidden_hash_var='your_password_here';

class Authentication {
	function start () {
           Authentication::load_userdata();
return;
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
      global $siteId;

      if (!$user_name || !$password) {
         $error .=  ' ERROR - Missing user name or password ';
         return false;
      } else {
         $user_name = strtolower($user_name);
         $password = strtolower($password);
         $sql = "SELECT * FROM user WHERE user_name='".$user_name."' "
              . "AND password='".md5($password)."' "
              . "AND site_id='".$siteId."'";
         $qhandle = New Query($sql, "fetch_row" ,0);
         if ($qhandle) {
            $data = $qhandle->execute();
            //print_r($data);
            if ($data[is_confirmed] == '1') {
               Authentication::user_set_tokens($user_name, $data[user_id]); //$user_name);
               return true;
            } else {
               $error .=  " ERROR - You haven't Confirmed Your Account Yet ";
					return false;
				}
			} else {
				$error .=  ' ERROR - User not found or password incorrect ';
				return false;
			}
		}
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

	function user_confirm($hash,$email) {
		global $db, $feedback, $hidden_hash_var;;

		/*
			Call this function on the user confirmation page,
			which they arrive at when the click the link in the
			account confirmation email
		*/

 		//verify that they didn't tamper with the email address
      $new_hash = md5($email.$hidden_hash_var);

      if ($new_hash && ($new_hash==$hash)) {
			//find this record in the db
			$sql = "SELECT * FROM user WHERE confirm_hash='".$hash."' "
			     . "AND is_confirmed=0 LIMIT 1";
			$qhandle = New Query($sql);
			if (!$qhandle || $qhandle->numrows < 1) {
				$error .= ' ERROR - Hash Not Found ';
				return false;
			} else {
				//confirm the email and set account to active
				//$feedback .= ' User Account Updated - You Are Now Logged In <a href=/>Main Page</a>';
				Authentication::user_set_tokens($db->result($result,0,'user_name'));
				$sql = "UPDATE user SET email='".$email."', is_confirmed='1' WHERE confirm_hash='".$hash."'";
				$rhandle = New Query($sql);
				return header("Location: /my/?");
			}
		} else {
			$error .= ' HASH INVALID - UPDATE FAILED ';
			return false;
		}
	}

	function user_change_password($new_password1,$new_password2,$change_user_name,$old_password) {
		global $db, $feedback, $siteId;
		//new passwords present and match?
		if ($new_password1 && ($new_password1==$new_password2)) {
			//is this password long enough?
			if ($this->account_pwvalid($new_password1)) {
				//all vars are present?
				if ($change_user_name && $old_password) {
					//lower case everything
					$change_user_name=strtolower($change_user_name);
					$old_password=strtolower($old_password);
					$new_password1=strtolower($new_password1);
					$sql="SELECT * FROM user WHERE user_name='$change_user_name' AND password='". md5($old_password) ."' AND site_id='$siteId'";
					$result=$db->query($sql);
					if (!$result || $db->numrows($result) < 1) {
						$feedback .= ' User not found or bad password '.$db->error();
						return false;
					} else {
						$sql="UPDATE user SET password='". md5($new_password1). "' ".
							"WHERE user_name='$change_user_name' AND password='". md5($old_password). "' ".
							"AND site_id='$siteId'";
						$result=$db->query($sql);
						if (!$result || $db->affected_rows($result) < 1) {
							$feedback .= ' NOTHING Changed '.$db->error();
							return false;
						} else {
							$feedback .= ' Password Changed ';
							return true;
						}
					}
				} else {
					$feedback .= " Must Provide User Name And Old Password ";
					return false;
				}
			} else {
				$feedback .= " New Passwords Doesn't Meet Criteria ";
				return false;
			}
		} else {
			return false;
			$feedback .= ' New Passwords Must Match ';
		}
	}

	function user_lost_password ($email,$user_name) {
		global $db, $feedback,$hidden_hash_var, $siteId;
		if ($email && $user_name) {
			$user_name=strtolower($user_name);
			$sql="SELECT * FROM user WHERE user_name='$user_name' AND email='$email' AND site_id='$siteId'";
			$result=$db->query($sql);
			if (!$result || $db->numrows($result) < 1) {
				//no matching user found
				$feedback .= ' ERROR - Incorrect User Name Or Email Address ';
				return false;
			} else {
				//create a secure, new password
				$new_pass=strtolower(substr(md5(time().$user_name.$hidden_hash_var),1,14));

				//update the database to include the new password
				$sql="UPDATE user SET password='". md5($new_pass) ."' WHERE user_name='$user_name' AND site_id='$siteId'";
				$result=$db->query($sql);

				//send a simple email with the new password
				mail ($email,'Password Reset','Your Password '.
					'has been reset to: '.$new_pass,'From: noreply@company.com');
				$feedback .= ' Your new password has been emailed to you. ';
				return true;
			}
		} else {
			$feedback .= ' ERROR - User Name and Email Address Are Required ';
			return false;
		}
	}

	function user_change_email ($password1,$new_email,$user_name) {
		global $feedback,$hidden_hash_var;
		if (validate_email($new_email)) {
			$hash=md5($new_email.$hidden_hash_var);
			//change the confirm hash in the db but not the email - 
			//send out a new confirm email with a new hash
			$user_name=strtolower($user_name);
			$password1=strtolower($password1);
			$sql="UPDATE user SET confirm_hash='$hash' WHERE user_name='$user_name' AND password='". md5($password1) ."'";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= ' ERROR - Incorrect User Name Or Password ';
				return false;
			} else {
				$feedback .= ' Confirmation Sent ';
				user_send_confirm_email($new_email,$hash);
				return true;
			}
		} else {
			$feedback .= ' New Email Address Appears Invalid ';
			return false;
		}
	}

	function user_send_confirm_email($email,$hash) {
		global $siteUrl;
		$message = "Thank You For Registering ".
			"Simply follow this link to confirm your registration -- You will be asked to log in with your new username/pass: ".
			"$siteUrl/my/newuser.php?status=confirm&hash=$hash&email=". urlencode($email).
			" Once you confirm, you can use the services on the site.";
		mail ($email,'SiG Registration Confirmation',$message,'From: noreply@dev.sig.mine.nu');
	}

   function user_register($user_name,$password1,$password2,$email,$real_name) {
      global $db, $feedback,$hidden_hash_var, $siteId;
      //all vars present and passwords match?
         if ($user_name && $password1 && $password1==$password2 && $email && Authentication::validate_email($email)) {
         //password and name are valid?
         if (Authentication::account_namevalid($user_name) && Authentication::account_pwvalid($password1)) {
            $user_name=strtolower($user_name);
            $password1=strtolower($password1);

            //does the name exist in the database?
            $sql = "SELECT * FROM user WHERE user_name='$user_name' AND site_id='$siteId'";
            $qhandle = New Query($sql);

            if ($qhandle && $qhandle->numrows > 0) {
               $feedback .=  ' ERROR - USER NAME EXISTS ';
               return false;
            } else {
               //create a new hash to insert into the db and the confirmation email
               $hash=md5($email.$hidden_hash_var);
               $sql = "INSERT INTO user "
                    . "(user_name,real_name,password,email,remote_addr,confirm_hash,is_confirmed,site_id) "
                    . "VALUES ('$user_name','$real_name','". md5($password1) ."','$email','$GLOBALS[REMOTE_ADDR]','$hash','0','$siteId')";
               $rhandle = New Query($sql);
               if (!$qhandle) {
                  $feedback .= ' ERROR - '.Authentication::error();
                  return false;
               } else {
                  //send the confirm email
                  Authentication::user_send_confirm_email($email,$hash);
                  $feedback .= ' Successfully Registered. You Should Have a Confirmation Email Waiting <a href="/">Back</a>';
                  return true;
               }
            }
         } else {
            $feedback .=  ' Account Name or Password Invalid ';
            return false;
         }
      } else {
         $feedback .=  ' ERROR - Must Fill In User Name, Matching Passwords, And Provide Valid Email Address ';
         return false;
      }
   }

	function user_getid() {
		global $G_USER_RESULT, $siteId, $db;
		//see if we have already fetched this user from the db, if not, fetch it
		if (!$G_USER_RESULT) {
			$G_USER_RESULT = $db->query("SELECT * FROM user WHERE user_name='" . $this->user_getname() . "' AND site_id='$siteId'");
		}
		if ($G_USER_RESULT && $db->numrows($G_USER_RESULT) > 0) {
			return $db->result($G_USER_RESULT,0,'user_id');
		} else {
			return false;
		}
	}

	function user_getrealname() {
		global $G_USER_RESULT, $siteId;
		//see if we have already fetched this user from the db, if not, fetch it
		if (!$G_USER_RESULT) {
			$G_USER_RESULT=db_query("SELECT * FROM user WHERE user_name='" . user_getname() . "' AND site_id='$siteId'");
		}
		if ($G_USER_RESULT && db_numrows($G_USER_RESULT) > 0) {
			return db_result($G_USER_RESULT,0,'real_name');
		} else {
			return false;
		}
	}

	function user_getemail() {
		global $G_USER_RESULT, $siteId;
		//see if we have already fetched this user from the db, if not, fetch it
		if (!$G_USER_RESULT) {
			$G_USER_RESULT=db_query("SELECT * FROM user WHERE user_name='" . user_getname() . "' AND site_id='$siteId'");
		}
		if ($G_USER_RESULT && db_numrows($G_USER_RESULT) > 0) {
			return db_result($G_USER_RESULT,0,'email');
		} else {
			return false;
		}
	}

	function user_getname() {
		if ($this->user_isloggedin()) {
			return $GLOBALS['user_name'];
		} else {
			//look up the user some day when we need it
			return ' ERROR - Not Logged In ';
		}
	}

	function account_pwvalid($pw) {
		if (strlen($pw) < 6) {
			$feedback .= " Password must be at least 6 characters. ";
			return false;
		}
		return true;
	}

	function account_namevalid($name) {
		// no spaces
		if (strrpos($name,' ') > 0) {
			$feedback .= " There cannot be any spaces in the login name. ";
			return false;
		}

		// must have at least one character
		if (strspn($name,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") == 0) {
			$feedback .= "There must be at least one character.";
			return false;
		}

		// must contain all legal characters
		if (strspn($name,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_") != strlen($name)) {
			$feedback .= " Illegal character in name. ";
			return false;
		}

		// min and max length
		if (strlen($name) < 5) {
			$feedback .= " Name is too short. It must be at least 5 characters. ";
			return false;
		}
		if (strlen($name) > 15) {
			$feedback .= "Name is too long. It must be less than 15 characters.";
			return false;
		}

		// illegal names
		if (eregi("^((root)|(bin)|(daemon)|(adm)|(lp)|(sync)|(shutdown)|(halt)|(mail)|(news)"
			. "|(uucp)|(operator)|(games)|(mysql)|(httpd)|(nobody)|(dummy)"
			. "|(www)|(cvs)|(shell)|(ftp)|(irc)|(debian)|(ns)|(download))$",$name)) {
			$feedback .= "Name is reserved.";
			return 0;
		}
		if (eregi("^(anoncvs_)",$name)) {
			$feedback .= "Name is reserved for CVS.";
			return false;
		}

		return true;
	}

	function validate_email ($address) {
		return (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'. '@'. '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.' . '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $address));
	}

	function load_userdata() {

$user['user_name'] = 'root';
$user['user_id'] = 1;
$user['site_id'] = 1;
$user['groups'] = array();

$GLOBALS["userData"] = $user;
return true;
		global $db, $siteId;
		if (!isset($GLOBALS["userData"]) && !empty($GLOBALS["user_id"])) {
			$query = "SELECT * FROM user WHERE user_id='".$GLOBALS["user_id"]."' AND site_id='$siteId'";
			$qhandle = New Query($query);
			if ($qhandle) {
				$data = $qhandle->execute();
				if ($GLOBALS["userData"] = $data) {
					return 1;
				} else {
					return 0;
				}
			} else {
				return 0;
			}
		} else {
			return 0;
		}
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

	function printHtmlLogin ($url = "/") {
		global $form, $user, $siteUrl;
		$form->printHtmlFormField("openhtml", "Login form", "post", $siteUrl."/my/login.php");
		$form->printHtmlFormField("text", "User Name", "user", $user);
		$form->printHtmlFormField("password", "Password", "pass", "");
		$form->printHtmlFormField("hidden", "", "refering_url", "$url");
		$form->printHtmlFormField("submit", "Action", "status", "Login");
		$form->printHtmlFormField("closehtml", "Use this form to login","","");
	}

	function printHtmlNewUser ($url) {
		global $form;
		$form->printHtmlFormField("openhtml", "New User form", "post", "/my/newuser.php");
		$form->printHtmlFormField("text", "User Name", "user", "");
		$form->printHtmlFormField("password", "Password", "pass1", "");
		$form->printHtmlFormField("password", "Password again", "pass2", "");
   	$form->printHtmlFormField("text", "Email", "email", "");
		$form->printHtmlFormField("text", "Real Name", "name", "");
		$form->printHtmlFormField("hidden", "", "refering_url", "$url");
 		$form->printHtmlFormField("submit", "Action", "status", "Register");
		$helpstring =  "
					This form is used to register users. Upon completion of this form<br>
					a conformation email will be sent to the address specified. Follow<br>
					the link in the email to complete the registration proccess.<br>
					<ul>Limitations
						<li>Username must be 6-14 characters long and can not have any strange characters
						<li>Password must be at least 6 characters
						<li>Email must be valid
					</ul><br>
					Your information is confidential and will not be distributed.

							";
		$form->printHtmlFormField("closehtml", $helpstring,"","");
	}
}

?>
