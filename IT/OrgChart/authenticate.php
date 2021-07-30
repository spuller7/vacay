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

	require_once( dirname( __FILE__ )."/./config.php" );
	require_once( dirname( __FILE__ )."/DB/DBManager.php" );
	require_once( dirname( __FILE__ )."/./Roles.php" );
	require_once( dirname( __FILE__ )."/./utils.php" );
	require_once( dirname( __FILE__ )."/./LdapLib.php" );

function isCommandLine()
{
	global $argc;
	return isset($argc);
}

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
		
		$userName = "";
		//import_request_variables( 'p', 'login_' );  removed in 5.4
		extract($_POST, EXTR_PREFIX_ALL, 'login');
		extract($_GET, EXTR_PREFIX_ALL, 'login');
		if ( isset( $login_username ) && isset( $login_password ) )
		{
			$login_username = strtolower($login_username);
			if ( LdapUser::authenticateUser( $login_username, $login_password ) )
			{
				if ( LdapGroup::isUserInGroup( $login_username, "opinionmakers" ) )
				{
					$user = LdapUser::FetchOne( $login_username );
					$_SESSION['USER_NAME'] = $login_username;
					$_SESSION['USERID'] = $user->uid;
					$_SESSION['USERIDMAP'] = LdapUser::getUserMap();
					$_SESSION['USERNAMEMAP'] = array_flip( $_SESSION['USERIDMAP'] );
					$_SESSION['USER'] = $user;
				}
			}
		}
	/*	
		if ( !isset($_SESSION['USER_NAME']))
		{
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
		}*/
	}	 
?>
