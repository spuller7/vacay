<?php
//////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2008, Quantum Signal, LLC
// 
// This data and information is proprietary to, and a valuable trade 
// secret of, Quantum Signal, LLC.  It is given in confidence by Quantum 
// Signal, LLC. Its use, duplication, or disclosure is subject to the
// restrictions set forth in the License Agreement under which it has 
// been distributed.
//
//////////////////////////////////////////////////////////////////////

	require_once( dirname( __FILE__ )."/DB/DBManager.php" );
	require_once( dirname( __FILE__ )."/./Roles.php" );
	require_once( dirname( __FILE__ )."/./utils.php" );

if ( isCommandLine() )
{
	return;
}


	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	if ( session_id() == "" )
	{
		session_start();
	}
	
  function authenticate() {
    header('WWW-Authenticate: Digest realm=timekeeping,qop="auth",nonce="'.uniqid().'",opaque="'.md5("timekeeping").'"');
    header('HTTP/1.0 401 Unauthorized');
    echo "You must enter a valid login ID and password to access this resource\n";
    exit;
  }
  
  function logout() {
  	session_destroy();
  	authenticate();
  	exit;
  }
  
	// function to parse the http auth header
	function http_digest_parse($txt)
	{
	    // protect against missing data
	    $needed_parts = array('nonce'=>1,'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
	    $data = array();

      preg_match_all('@(?P<key>\w+)=(?:(?:"(?P<value1>[^"]+)")|(?:\'(?P<value2>[^\']+)\')|(?P<value3>[^,]+))@', $txt, $matches, PREG_SET_ORDER);
	
	    foreach ($matches as $m) {
	    		$key = $m['key'];
	    		if ( $m['value1'] )
	    		{
	    			$data[$key] = $m['value1'];
	    		}
	    		else if ( $m['value2'] )
	    		{
	    			$data[$key] = $m['value2'];
	    		}
	    		else 
	    		{
	    			$data[$key] = $m['value3'];
	    		}
	        unset($needed_parts[$key]);
	    }
	    return $needed_parts ? false : $data;
	}
  
	if ( !isset($_SESSION['USER_NAME']) )
	{
		if ( !isset($_SERVER['HTTP_USER_AGENT']))
		{
			authenticate();
			exit();
		}
		
		if ( $_SERVER['HTTP_USER_AGENT'] == 'TimeTrayClient' )
		{
			if ( !isset($_SERVER['PHP_AUTH_DIGEST']) )
			{
				authenticate();
				exit();
			}
			
			if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])))
			{
				authenticate();
				exit();
			}
			
			$userName = $data['username'];
			$sth = executeSQL( "SELECT passwordHash FROM Users WHERE Username = :userName",
				array( 'userName' => $userName) );
				
			$hash = $sth->fetchColumn(0);
			if ( $hash == null )
			{
				authenticate();		
				exit();
			}
			
			$A1 = $hash;
			$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
			$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
	
			if ( $valid_response != $data['response'] )
			{
				authenticate();
				exit();
			}
		}
		else
		{
			$userName = "";
			//import_request_variables( 'p', 'login_' );
			extract($_POST, EXTR_PREFIX_ALL, 'login');
			//!!passwordHash is plain text since the LDAP switch!!
			if ( isset( $login_username ) && isset( $login_passwordHash ) )
			{
				$login_username = strtolower($login_username);
				if ( !isset( $_SESSION["salt"] ) )
				{
					$salt = md5(uniqId());
					$_SESSION["salt"] = $salt;
				}
				$skipLdap = false;
				$ldapPass = false;
				$inLdap = false;
				$conn = ldap_connect("ldaps://ldap.mgmt.quantumsignal.com");
				ldap_bind($conn) or $skipLdap = true;
				if (!$skipLdap)
				{
					$result = ldap_search($conn,"ou=People,dc=ldap,dc=internal,dc=quantumsignal,dc=com","uid=".$login_username);
					$info = ldap_get_entries($conn, $result);
					if (isset($info[0]['dn']))
					{
						$inLdap = true;
						if (ldap_bind($conn, $info[0]['dn'], $login_passwordHash)) $ldapPass = true;
					}
				}
				

				$salt = $_SESSION["salt"];
				$sth = executeSQL( "SELECT passwordHash FROM Users WHERE Username = :userName",
					array( 'userName' => $login_username) );
					
				$hash = $sth->fetchColumn(0);
				if ($inLdap && !$ldapPass)
				{
					$msg = "LDAP password mismatch";
				}
				elseif ( $hash != null )
				{
					if ($ldapPass) $userName = $login_username;
					//$fullHash = md5( $salt.":".$hash.":".$salt );
					//if ( $fullHash == $login_passwordHash )
					elseif ( $hash == md5($login_username.":timekeeping:".$login_passwordHash) )
					{
						$userName = $login_username;							
					}
					else								
						$msg = "Password mismatch";
				}
				elseif($ldapPass)
				{
					//$msg = "Username in LDAP but local Timekeeping account required!";
					$db = new DBManager();
			        $pdo = $db->pdo();
			        $sth = $pdo->prepare( "INSERT INTO Users ( Username, PasswordHash, FullName, State, PartTime, CreateDate, LastUpdate ) VALUES ( :username, :passwordHash, :fullName, :state, :parttime, NOW(), NOW() )" );
			        $sth->execute( array ('username' => $login_username, 'passwordHash' => md5($login_username.":timekeeping:".$login_passwordHash), 'fullName' => "", 'state' => "active", 'parttime' => 0 ) );
			        $mysql_id = $pdo->lastInsertId();
					$sth = $pdo->prepare( "REPLACE INTO Roles ( UserID, Role, CreateDate, LastUpdate ) VALUES ( :UserID, :Role, NOW(), NOW() )" );
					$sth->execute( array( 'UserID' => $mysql_id, 'Role' => "TRACKER" ) );
					
					$userName = $login_username;
				}
				else
				{								
					$msg = "Unknown user";
				}
			}
			else
			{
				//$msg = "No Posted Data";
			}
	
			if ( $userName == "" )
			{
				$salt = md5(uniqId());
				$_SESSION["salt"] = $salt;
				include( dirname( __FILE__ )."/./login.php" );
				if (isset($_SESSION['timedout']) && $_SESSION['timedout'])
				{
					echo "<br/><br/><center><div class='ErrorText'>Your session has timed out due to inactivity.</div><center>\n";
					$_SESSION['timedout'] = false;
				}
				else
				{
					if (isset($msg) && ($msg != ""))
						echo "<br/><br/><center><div class='ErrorText'>There was an error reported. The error was: ".$msg."</div><center>\n";
				}
				exit();
			}
			
		}
		
		$sth = executeSQL( "SELECT UserID FROM Users WHERE Username = :userName",
			array( 'userName' => $login_username) );
			
		$userID = $sth->fetchColumn(0);
		
	  $_SESSION['USER_NAME'] = $userName;
	  $_SESSION['USERID'] = $userID;
	  $_SESSION['ROLES'] = Roles::getRoles( $userID );
	}	 
	else
	{
	}
?>
