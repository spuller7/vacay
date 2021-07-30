<?php

require_once('Parser.php');
require_once('config.php');

$stateToIcon = array();
$stateToIcon["PENDING"]="mag_inv.png";
$stateToIcon["BAD"]="red_thumb_down.png";
$stateToIcon["FIXING"]="pencil_wrench.png";
$stateToIcon["FIXED"]="wrench.png";
$stateToIcon["GOOD"]="green_thumb_up.png";
$stateToIcon["OBSOLETE"]="3.5in_floppy.png";

function GetElapsedMinutes( $when )
{
	return round(time()-$when)/60;
}

function GetTimeString( $elapsedMinutes )
{
	if ( $elapsedMinutes > 60*24*7 )
	{
		return round( $elapsedMinutes/(60*24*7))."wk";
	}
	else if ( $elapsedMinutes >= 60*24 )
	{
		return round( $elapsedMinutes/(60*24))."d";
	}
	else if ( $elapsedMinutes >= 60 )
	{
		return floor( $elapsedMinutes/60)."h";
	}
	else
	{
		return round($elapsedMinutes)."m";
	}
}

function createRedis()
{
	$redis = new Redis();
	$redis->connect( $redisIP, $redisPort);	
	return $redis;
}

function createDB()
{
	$db = new PDO($dbConnect, $dbUser, $dbPW );
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	return $db;
}

$redis = createRedis();
$db = createDB();

//NOT_DB_READY
function addMsgToDateMap( $msgId, $date )
{
	global $redis;
	
	$redis->sAdd( "IT.msgstore.dateMap.".$date, $msgId );
}

//NOT_DB_READY
function removeMsgFromDateMap( $msgId, $date )
{
	global $redis;
	
	$redis->sRem("IT.msgstore.dateMap.".$date, $msgId );
}

//DB_READY
function setMsgFlag( $id, $key, $value )
{
	global $redis;
	global $db;

	if ( $id == null )
		return;
	
	$rediskey = "IT.msgstore.mail.".$id;
	$redis->hset( $rediskey, $key, $value );
	
	$stmt = $db->prepare('REPLACE INTO MSG_PROPS (MSG_ID, PROP_KEY, PROP_VALUE) VALUES (?,?,?)');
	$stmt->execute(array($id, $key, $value));
}

//NOT_DB_READY
function hasMsg( $info )
{
	global $redis;
	$key = "IT.msgstore.mail.".$info->message_id;
	$state = $redis->hget( $key, "state" );	
	return $state;
}

//DB_READY
function createMsg( $info, $body, $structure )
{
	global $redis;
	global $db;

	$redis->sAdd("IT.msgstore.idlist", $info->message_id);
	$key = "IT.msgstore.mail.".$info->message_id;
	$redis->hset( $key, "headers", json_encode($info) );
	$redis->hset( $key, "body", $body );
	$redis->hset( $key, "structure", json_encode($structure) );
	$redis->hset( $key, "state", "NEW" );
	setMsgFlag( $info->message_id, "READ", 0 );	
	addMsgtoDateMap( $info->message_id, $info->udate );
	
	$stmt = $db->prepare('REPLACE INTO MESSAGES (MSG_ID, MSG_DATETIME, HEADERS, STRUCTURE, BODY, STATE, ISREAD) VALUES (?,from_unixtime(?),?,?,?,?,?)');
	$stmt->execute(array($info->message_id, $info->udate,json_encode($info),json_encode($structure),$body,"NEW",0));	
}

//NOT_DB_READY
function getMsg( $id, $withBody = true )
{
	global $redis;
	
	$key = "IT.msgstore.mail.".$id;
	$result = $redis->hgetall( $key);
	if ( isset( $result["headers"] ) )
		$result["headers"] = json_decode($result["headers"]);
	else
		$result["headers"] = new stdClass();
	$result["id"] = $id;
	if ( !$withBody )
		unset($result["body"]);
	return $result;
}

//NOT_DB_READY
function getInfAgeLimit( $key )
{
	global $redis;
	$val = $redis->hget( "IT.tracker.infrastructure", $key.".ageLimit");
	if ( $val == null )
		return 24;
	else
		return $val;
}

//NOT_DB_READY
function setAgeLimit( $key, $limit )
{
	global $redis;
	$val = $redis->hset( "IT.tracker.infrastructure", $key.".ageLimit", $limit);
}

//NOT_DB_READY
function getInfrastructureKeys( $msg )
{
	$result = array();
	foreach ( $msg as $k => $v )
	{
		if( preg_match_all("/(infrastructurelabel)\.([a-zA-Z0-9-_\/\:\.\s]+)/", $k, $matches ) )
		{
			$key = $matches[2][0];
			$result[$k] = $key;
		}
	}
	return $result;
}

//NOT_DB_READY
function getHostKeys( $msg )
{
	$result = array();
	foreach ( $msg as $k => $v )
	{
		if ( preg_match_all("/(hostlabel)\.([a-zA-Z0-9-]+)\.([a-zA-Z0-9_\.\-]+)/", $k, $matches) )
		{
			$result[$k] = new stdClass();
			$result[$k]->host = $matches[2][0];
			$result[$k]->key = $matches[3][0];
			$result[$k]->value = $v;
		}
	}
	return $result;	
}

//NOT_DB_READY
function resetStatus( $host, $key )
{
	global $redis;
	
	$redis->hdel("IT.tracker.hosts.".$host, $key.".when" );
	$redis->hdel("IT.tracker.hosts.".$host, $key.".state" );
	$redis->hdel("IT.tracker.hosts.".$host, $key.".id" );
}


//NOT_DB_READY
function setHostNote( $host, $note )
{
	global $redis;
	
	if ( $note == "" || !isset($note) )
	{
		$redis->hdel("IT.tracker.hosts.".$host, "NOTE.content");
		$redis->hdel("IT.tracker.hosts.".$host, "NOTE.when");
	}
	else	
	{
		$redis->hset("IT.tracker.hosts.".$host, "NOTE.content", $note );
		$redis->hset("IT.tracker.hosts.".$host, "NOTE.when", time() );
	}
}

//NOT_DB_READY
function removeEmail ( $id )
{
	global $redis;
	
	$msg = getMsg( $id, false );
	$headers = $msg["headers"];
	
	foreach ( $msg as $k => $v )
	{
		if ( preg_match_all("/(hostlabel)\.([a-zA-Z0-9\-]+)\.([a-zA-Z0-9\s]+)/", $k, $matches) )
		{
			$host = $matches[2][0];
			$key = $matches[3][0];
			resetStatus( $host, $key );
		}
	}	
	
	$keys = getInfrastructureKeys( $msg );
	foreach ( $keys as $key => $label )
	{
		$redis->hdel("IT.tracker.infrastructure",$label.".when" );
		$redis->hdel("IT.tracker.infrastructure",$label.".state" );
		$redis->hdel("IT.tracker.infrastructure",$label.".id" );
	}
	
	$redis->sRem( "IT.alarms.messages", $id );

	$key = "IT.msgstore.mail.".$id;
	removeMsgFromDateMap( $id, $headers->udate );
	//TODO: extract from hosts
	$redis->del($key);
	$redis->sRem("IT.msgstore.idlist", $id);	
}

//NOT_DB_READY
function setInfrastructureFlag( $key, $date, $state, $id, $bForce = false )
{
	global $redis;
	
	$state = strtoupper($state);
	$key = strtoupper	($key);
	
	$lastDate = 1*$redis->hget("IT.tracker.infrastructure", $key.".when");
	if ( $bForce || $lastDate < $date )
	{
		$redis->hset("IT.tracker.infrastructure", $key.".when", $date );
		$redis->hset("IT.tracker.infrastructure", $key.".state", $state );
		$redis->hset("IT.tracker.infrastructure", $key.".id", $id );
		
		$mailkey = "IT.msgstore.mail.".$id;
		$redis->hset($mailkey,"infrastructurelabel.".$key, $state );
		
		echo "Updated ".$key." ".$date." ".$state." ".$id."\n";
		return true;
	}
	else
	{
		//echo "Skipping old record ".$host." ".$key." ".$date." ".$state."\n";
		return false;
	}
}

//NOT_DB_READY
function setHostFlag( $host, $key, $date, $state, $id, $bForce = false )
{
	global $redis;

	$host = strtolower($host);
	$state = strtoupper($state);
	$key = strtoupper($key);
	
	$lastDate = 1*$redis->hget("IT.tracker.hosts.".$host, $key.".when");
	if ( $lastDate < $date || $bForce )
	{
		$redis->hset("IT.tracker.hosts.".$host, $key.".when", $date );
		$redis->hset("IT.tracker.hosts.".$host, $key.".state", $state );
		$redis->hset("IT.tracker.hosts.".$host, $key.".id", $id );
		
		$mailkey = "IT.msgstore.mail.".$id;
		$redis->hset($mailkey,"hostlabel.".$host.".".$key, $state );
		
		echo "Updated ".$host." ".$key." ".$date." ".$state." ".$id."\n";
	}
	else
	{
		//echo "Skipping old record ".$host." ".$key." ".$date." ".$state."\n";
	}
}

//NOT_DB_READY
function alarm(  $id )
{
	global $redis;
	
	$key = "IT.msgstore.mail.".$id;
	$state = $redis->hget( $key, "state" );
	if ( $state == NULL || $state == "NEW" )
	{
		$redis->sAdd( "IT.alarms.messages", $id );
		$redis->hset( $key, "state", "NAK" );
		return true;
	}
	return false;
}

//NOT_DB_READY
function scan(  $id )
{
	global $redis;
	
	$key = "IT.msgstore.mail.".$id;
	$state = $redis->hget( $key, "scan" );
	if ( $state == NULL || $state == "NEW" )
	{
		$redis->sAdd( "IT.scans.messages", $id );
		$redis->hset( $key, "scan", "NAK" );
		return true;
	}
	return false;
}

//NOT_DB_READY
function ack_scan(  $id )
{
	global $redis;

	$key = "IT.msgstore.mail.".$id;
	$redis->hset( $key, "scan", "ACK" );
	$redis->sRem( "IT.scans.messages", $id );
}

//NOT_DB_READY
function enumerateScans( $id )
{
	return $redis->sMembers( "IT.scans.messages");
}

//NOT_DB_READY
function setVMHost(  $host, $attrib, $state, $id, $when )
{
	global $redis;
	
	$key = "IT.vmhost.".$host;
	$current_when = $redis->hget( $key, $attrib.".when" );
	if ( $current_when == NULL || $current_when < $when )
	{
		$redis->hset( $key, $attrib.".state", $state );
		$redis->hset( $key, $attrib.".id", $id );
		$redis->hset( $key, $attrib.".when", $when );
		return true;
	}
	return false;
}

//NOT_DB_READY
function setVM(  $vm, $attrib, $state, $id, $when )
{
	global $redis;
	
	$key = "IT.vm.".$vm;
	$current_when = $redis->hget( $key, $attrib.".when" );
	if ( $current_when == NULL || $current_when < $when )
	{
		$redis->hset( $key, $attrib.".state", $state );
		$redis->hset( $key, $attrib.".id", $id );
		$redis->hset( $key, $attrib.".when", $when );
		return true;
	}
	return false;
}

//NOT_DB_READY
function getAlarms()
{
	global $redis;
	
	return $redis->sMembers( "IT.alarms.messages" );
}

//NOT_DB_READY
function headerDateSort( $a, $b )
{
	$aDate = $a["headers"]->udate;
	$bDate = $b["headers"]->udate;
	
	if ( $aDate < $bDate )
		return -1;
	if( $aDate > $bDate )
		return 1;
	return 0;
}

//NOT_DB_READY
function sortMsgs( & $list )
{
	usort( $list, "headerDateSort" );
}

//NOT_DB_READY
function retrieveMsgs( $list, $withBody = false )
{
	$result = array();
	foreach ( $list as $msgId )
	{
		$result[] = getMsg( $msgId, $withBody );
	}
	return $result;
}

//NOT_DB_READY
function ack_alarm(  $id, $reason, $who, $sweep )
{
	global $redis;

	if ( isset($sweep) && $sweep == true )
	{
		$msg = getMsg( $id );
		$headers = $msg["headers"];
		$subj = $headers->subject;
		$alarms = getAlarms();
		$msgs = retrieveMsgs( $alarms );
		foreach ($msgs as $newMsg )
		{
			$newId = $newMsg["id"];
			$newHeaders = $newMsg["headers"];
			$newSub = $newHeaders->subject;
			if ( $newSub == $subj )
			{
				ack_alarm( $newId, $reason, $who, false );
			}
		}			
	}
	else
	{
		$key = "IT.msgstore.mail.".$id;
		$redis->hset( $key, "state", "ACK" );
		$redis->hset( $key, "reason", $reason );
		$redis->hset( $key, "who", $who );
		$redis->sRem( "IT.alarms.messages", $id );
	}
}

//NOT_DB_READY
function enumerateAlarms( $id )
{
	return $redis->sMembers( "IT.alarms.messages");
}

//NOT_DB_READY
function getVMHosts()
{
	global $redis;
	
	$results = array();
	$hosts = $redis->keys(  "IT.vmhost.*" );
	foreach ( $hosts as $hostKey )
	{
		$host = substr($hostKey, strrpos( $hostKey, ".",-1)+1 );
		$keys = $redis->hGetAll(  $hostKey );
		$z = array();
		foreach ( $keys as $k => $v )
		{
			$last = strrpos( $k, ".", -1 );
			$key = substr( $k, 0, $last );
			$tag = substr( $k, $last+1);
			if ( !isset($z[$key]) )
			{
				$z[$key] = array();
			}
			$z[$key][$tag] = $v;
		}
		$results[$host] = $z;
	}
	return $results;
}

//NOT_DB_READY
function getVMs()
{
	global $redis;
	
	$results = array();
	$hosts = $redis->keys(  "IT.vm.*" );
	foreach ( $hosts as $hostKey )
	{
		$host = substr($hostKey, strrpos( $hostKey, ".",-1)+1 );
		$keys = $redis->hGetAll(  $hostKey );
		$z = array();
		foreach ( $keys as $k => $v )
		{
			$last = strrpos( $k, ".", -1 );
			$key = substr( $k, 0, $last );
			$tag = substr( $k, $last+1);
			if ( !isset($z[$key]) )
			{
				$z[$key] = array();
			}
			$z[$key][$tag] = $v;
		}
		$results[$host] = $z;
	}
	return $results;
}

//NOT_DB_READY
function getInfrastructureState()
{
	global $redis;
	
	$results = array();
	$keys = $redis->hGetAll(  "IT.tracker.infrastructure" );
	foreach ( $keys as $k => $v )
	{
		$last = strrpos( $k, ".", -1 );
		$key = substr( $k, 0, $last );
		$tag = substr( $k, $last+1);
		
		if ( !isset($results[$key]) )
		{
			$results[$key] = array();
		}
		$results[$key][$tag] = $v;
	}
	$final = array();
	foreach ( $results as $key => $v )
	{
		if ( isset($results[$key]["state"]) )
			$final[$key] = $v;
	}
	return $final;
}

//NOT_DB_READY
function deleteInfrastructure( $key )
{
	global $redis;

	$redis->hdel( "IT.tracker.infrastructure", $key.".state" );
	$redis->hdel( "IT.tracker.infrastructure", $key.".when" );
	$redis->hdel( "IT.tracker.infrastructure", $key.".id" );
}

//NOT_DB_READY
function getHostsState()
{
	global $redis;
	
	$results = array();
	$hosts = $redis->keys(  "IT.tracker.hosts.*" );
	foreach ( $hosts as $hostKey )
	{
		$host = substr($hostKey, strrpos( $hostKey, ".",-1)+1 );
		$keys = $redis->hGetAll(  $hostKey );
		$z = array();
		foreach ( $keys as $k => $v )
		{
			$last = strrpos( $k, ".", -1 );
			$key = substr( $k, 0, $last );
			$tag = substr( $k, $last+1);
			if ( !isset($z[$key]) )
			{
				$z[$key] = array();
			}
			$z[$key][$tag] = $v;
		}
		$results[$host] = $z;
	}
	return $results;
}

//NOT_DB_READY
function getActiveSubnets()
{
	global $redis;
	
	$results = array();
	$net = $redis->keys( "IT.tracker.net.*");
	foreach ( $net as $netKey )
	{
		$parts = explode(".", $netKey );
		$subnet = $parts[3].".".$parts[4].".".$parts[5];
		$results[$subnet] = $subnet;
	}
	return $results;
}
		
//NOT_DB_READY
function getNetState()
{
	global $redis;
	
	$results = array();
	$net = $redis->keys( "IT.tracker.net.*");
	foreach ( $net as $netKey )
	{
		$parts = explode(".", $netKey );
		$ip = $parts[3].".".$parts[4].".".$parts[5].".".$parts[6];
		$keys = $redis->hGetAll(  $netKey );
		$results[$ip] = $keys;
	}
	return $results;
}

//NOT_DB_READY
function updateNetwork( $addr, $udate, $entry )
{
	global $redis;

	$path = "IT.tracker.net.".$addr;
	$val = $redis->hget( $path, "LastUpdateMsg" );
	if ( $val == "" || 1*$val < $udate )
	{
		foreach ( $entry as $k => $v )
		{
			$redis->hset($path, $k, $v );
		}
		$redis->hset( $path, "LastUpdateMsg", $udate );
	}
}

//NOT_DB_READY
function setMac( $mac, $state, $description )
{
	global $redis;
	
	$key = "IT.macregistrar.".$mac;
	$redis->hset( $key, "lastUpdate", date(DATE_RFC2822) );	
	$redis->hset( $key, "state", $state );	
	$redis->hset( $key, "description", $description );	
	//TODO: add session user
}

//NOT_DB_READY
function removeIP( $IP )
{
	global $redis;
	
	$key = "IT.pings.".$IP;
	$redis->hdel( $key, "mac");
	//TODO: add session user
}

//NOT_DB_READY
function dcomp( $a, $b )
{
	$aParts = explode( ".", $a );
	$bParts = explode( ".", $b );
	
	$aDate = $aParts[count($aParts)-1];
	$bDate = $bParts[count($bParts)-1];
	
	if ( $aDate == $bDate )
		return 0;
		
	if ( $aDate < $bDate )
		return -1;
	else
		return 1;
}

//NOT_DB_READY
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

//NOT_DB_READY
function performPurge()
{
	global $redis;

	$keep = 0;
	$remove = 0;
	$msgs = $redis->keys("IT.msgstore.mail.*");
	foreach ( $msgs as $msg )
	{
		$title = substr($msg,17);
		$msg = getMsg( $title, false );
		if ( !isset($msg["headers"]))
			echo "Missing header: ".json_encode($msg)."\n";
		else if (!isset($msg["headers"]->udate))
		{
			echo "Missing udate: ".json_encode($msg)."\n";	
			removeEmail($title);
		}
		else
		{
			$udate = $msg["headers"]->udate;
			$diffDays = (time() - $udate)/3600/24;
			if ( $diffDays > 90 )
			{
				$remove++;
				removeEmail($title);
			}
			else
			{
				$keep++;
			}
		}
	}
	
	echo "Kept = $keep Removed = $remove\n";
/*	$dates = $redis->keys("IT.msgstore.dateMap.*");
	usort($dates, "dcomp" );

	foreach ( $dates as $date )
	{
		$mems = $redis->sMembers( $date );
		echo $date." ".count($mems)."\n";
		foreach ( $mems as $mem )
		{
			$key = "IT.msgstore.mail.".$mem;
			$hdrs = json_decode($redis->hget( $key, "headers"));
	
			if ( isset( $hdrs->subject) )	
				$subj = @iconv_mime_decode($hdrs->subject);
			else
				$subj = "[NO SUBJECT]";
			$udate = $hdrs->udate;
			$host = $hdrs->sender[0]->host;

			if ( preg_match( "/Postmaster/", $subj ) )
			{
				$body = $redis->hget( $key, "body");
				$Parser = new eXorus\PhpMimeMailParser\Parser();
				$Parser->setText($body);
				$text = $Parser->getMessageBody("text");
				if ( preg_match("/X-PHP-Originating-Script: 500:feedback.php/", $text ) )
				{
					echo $subj."\n";
					echo $text."\n";
					echo "============================================\n";
				}
			}
		}
	}*/
}

//NOT_DB_READY
function performDispatch()
{
	global $redis;

	$dates = $redis->keys("IT.msgstore.dateMap.*");
	usort($dates, "dcomp" );
	$sw = array();

	$msgCount = 0;
	$results = getAlarms();
	$map = array();
	foreach ( $results as $result )
	{
		$map[$result] = true;
	}

	foreach ( $dates as $date )
	{
		$mems = $redis->sMembers( $date );
		foreach ( $mems as $mem )
		{
			$msgCount++;
			$key = "IT.msgstore.mail.".$mem;
			$hdrs = json_decode($redis->hget( $key, "headers"));
	
			if ( isset( $hdrs->subject) )	
				$subj = @iconv_mime_decode($hdrs->subject);
			else
				$subj = "[NO SUBJECT]";
			$udate = $hdrs->udate;
			$host = $hdrs->sender[0]->host;
			$mailbox = $hdrs->sender[0]->mailbox;
			$sentto = "";
			if ( isset( $hdrs->to) )
				$sentto = $hdrs->to[0]->mailbox;
			
			if ( preg_match( "/Network Hosts Change/", $subj ) )
			{
				//ignore setInfrastructureFlag( "NETWORKHOSTS", $udate, "PENDING", $mem );
				$body = $redis->hget( $key, "body");
				$Parser = new eXorus\PhpMimeMailParser\Parser();
				$Parser->setText($body);
				$text = $Parser->getMessageBody("text");
				$js = json_decode($text);
				foreach ( $js as $k => $entry )
				{
					updateNetwork( $k, $udate, $entry );
				}
			}
			else if ( preg_match( "/Log file from SonicWALL/", $subj ) )
			{
				alarm( $mem );
			}
			else if ( preg_match( "/Log file from Network Security Appliance \[QuantumSignal\]  Part ([0-9]+)( - End of Log.)?/", $subj, $matches ) )
			{
				$sw[1.0*$matches[1]] = $key;
				$isEnd = (count($matches) == 3);
				if ( $isEnd )
				{
					$hasProcessed = $redis->hget( $key, "sonicwall.processed");
					if ( $hasProcessed == "" )
					{	
						ksort($sw);
						$body_sum = "";
						foreach ( $sw as $sw_k => $sw_v ) 
						{
							$body = $redis->hget( $sw_v, "body");
							$Parser = new eXorus\PhpMimeMailParser\Parser();
							$Parser->setText($body);
							$text = $Parser->getMessageBody("text");
							$body_sum .= $text;
						}
						$newBody = "";
						$lines = explode(PHP_EOL, $body_sum);
						foreach ( $lines as $line )
						{
							$line = preg_replace( "/\r|\n/", "", $line );
							if ( preg_match("/12\.170\.95\.199/",$line ) || $line == " " || $line == "" ||
							     preg_match("/This email was generated by/",$line)||
							     preg_match("/SSL Handshake failure with error/",$line)||
							     preg_match("/Peer IPsec Security Gateway behind a NAT\/NAPT Device/",$line)||
							     preg_match("/Log successfully sent via email/",$line)||
							     preg_match("/PPP message: Processing TERMINATE request/",$line)||
							     preg_match("/PPP message: LCP: unknown NCP code 0xC/",$line)||
							     preg_match("/68\.37\.219\.9/",$line ) )
								continue;
							$newBody .= $line."\n";
						}
						mail("itrobot", "Sonicwall Summary", $newBody );
						$redis->hset( $key, "sonicwall.processed", date(DATE_RFC2822) );
					}
					$sw = array();
				}
			}
			 else if ( preg_match("/Sonicwall Summary/", $subj ) || preg_match("/Log file from Network Security Appliance/", $subj ) )
			{
				$res = setInfrastructureFlag( "GENERAL.SONICWALL", $udate, "PENDING", $mem );
				if ( $res )
				{	
					$body = $redis->hget( $key, "body");
					$lines = explode( PHP_EOL, $body );
					foreach ( $lines as $line )
					{
						if ( preg_match( "![\s]*1080 - Users - Information - [\s]*!", $line ) )
						{
							$parts = explode( " ", $line );
							$when = $parts[0]." ".$parts[1];
							$ip = $parts[9];
							$who = strtoupper($parts[18]);
							$dt = strtotime( $when );
							setInfrastructureFlag( "VPN.".$who, $dt, $ip, $mem );
							//echo ($res?"T":"F")." ".date( DATE_ATOM,$dt)." ".$ip." ".$who."\n";
						}
					}
				}
			}
			else if ( preg_match("/Logwatch for */",$subj ) )
			{
				preg_match_all("/Logwatch for ([A-Za-z0-9-]+)(\..+)*/", $subj, $matches);
				$host = $matches[1][0];
				
				$body = $redis->hget( $key, "body");
				$bGood = true;
				
				$bGood &= preg_match( "!(#+) Logwatch [0-9][.0-9]* \([0-9][0-9]/[0-9][0-9]/[0-9][0-9]\) (#+)\s*(\n+)".
					"(.+)".
					" ##################################################################\s*\n".
					"(.+)".
					"(#+) Logwatch End (#+)!is", $body );
					
				if (preg_match("/- Dovecot Begin -/", $body ))
				{
					$dovecot = preg_match(
						"/(\-+) Dovecot Begin (\-+)\s*\n".
							"[\s\n]*".
							"\s*Dovecot disconnects:\s*\n".
							"\s*Logged out: [0-9]+ Time\(s\)\s*\n".
						" (\-+) Dovecot End (\-+)\s*\n/s", $body );
					//if ( !$dovecot ) echo "BAD: Failed dovecot\n";
					$bGood &= $dovecot;
				}
				
				if (preg_match("/- Kernel Begin -/", $body ))
				{
					//If we have a kernel section it is always flagged
					$bGood = false;
				}
/*
				if (preg_match("/- httpd Begin -/", $body ))
				{
					$httpd = preg_match(
						"/(-+) httpd Begin (-+)\s*\n".
							".+".
						"(-+) httpd End (-+)\s*\n".
						"/"
						
						, $body );
					if ( !$httpd ) echo "BAD: Failed httpd\n";
					$bGood &= $httpd;
				}*/
		/*		
				if (preg_match("/- Postfix Begin -/", $body ))
				{
					$postfix = preg_match(
						"/(.+)*".
						" (\-+) Postfix Begin (-+)\s*\n".
						".*".
					" (\-+) Postfix End (-+)\s*(\n+)/", $body );
					if ( !$postfix ) echo "BAD: Failed postfix\n";
					$bGood &= $postfix;
				}	
*/
				if (preg_match("/- Connections \(secure-log\) Begin -/", $body ))
				{
					$connections = preg_match(
					"/ (\-+) Connections \(secure-log\) Begin (\-+)\s*\n".
						"[\s\n]*".
						"\s*\*\*Unmatched Entries\*\*\s*\n".
						"(\s*(Rootkit H|rkh)unter: Please inspect this machine, because it may be infected\.\: [0-9]+ Time\(s\)\s*\n)*".
						"\s*(Rootkit H|rkh)unter: Rootkit hunter check started \(version [0-9]+(\.[0-9]+)*\)\: [0-9]+ Time\(s\)\s*\n".
						"(\s*(Rootkit H|rkh)unter: Scanning took ([0-9]+ minute(s*) and )?[0-9]+ second(s*)\: [0-9]+ Time\(s\)\s*\n)*".
						".*".
						"[\s\n]*".
					" (\-+) Connections \(secure-log\) End (\-+)\s*\n/s", $body );
					//if ( !$connections ) echo "BAD: Failed connections\n";
					$bGood &= $connections;
				}

				if (preg_match("/- sendmail Begin \(detail=[0-9]\) -/", $body ))
				{
					$sendmail = preg_match(
						"/ (\-+) sendmail Begin \(detail=[0-9]\) (\-+)\s*\n".
						"(\n)*".
						" STATISTICS\s*\n".
						" ----------\s*\n".
						"(\n)*".
						" Messages To Recipients:  [0-9]+\s*\n".
						" Addressed Recipients:    [0-9]+\s*\n".
						" Bytes Transferred:       [0-9]+\s*\n".
						" Messages No Valid Rcpts: [0-9]+\s*\n".
						"(\n)*".
						"(\s*SMTP SESSION, MESSAGE, OR RECIPIENT ERRORS\s*\n".
						"\s*------------------------------------------\s*\n".
						"\s*\n".
						"\s*Authentication warnings:\s*\n".
						"\s*Total:\s+[0-9]+\s*\n".
						"\s*\n".
						"\s*Total SMTP Session, Message, and Recipient Errors handled by Sendmail:\s+[0-9]+\s*\n)?".
						" (\-+) sendmail End (\-+)\s*\n/s", $body );
					//if ( !$sendmail ) echo "BAD: Failed sendmail\n";
					$bGood &= $sendmail;
				}
				
			/*
				SSHD is annoying...
				if (preg_match("/- SSHD Begin -/", $body ))
				{
					//If we have a SSHD section it is always flagged
					$bGood = false;
				}*/
				
				if (preg_match("/- pam_unix Begin -/", $body ))
				{
					//If we have a pam_unix section it is always flagged
					$bGood = false;
				}				

				if (preg_match("/- yum Begin -/", $body ))
				{
					//If we have a yum section it is always flagged
					$bGood = false;
				}
				
				/*if (preg_match("/- XNTPD Begin -/", $body ))
				{
					//DID NOT VERIFY, NO EXAMPLES
					$xntpd = preg_match(
						"/ (\-+) XNTPD Begin (-+)\s*\n".
						"[\s\n]*".
						"(\s*Time Reset [0-9]+ times \(total: [0-9]+\.[0-9]+ s\s+average: [0-9]+\.[0-9]+ s\)\s*\n[\s\n]*)?".
						"[\s\n*]".
						"\s Total synchronizations [0-9]+ \(hosts: [0-9]+\)\s*\n".
						"[\s\n]*".
						" (\-+) XNTPD End (-+)\s*(\n+)/", $body );
					if ( !$xntpd ) echo "BAD: Failed xntpd\n";
					$bGood &= $xntpd;
				}*/

				if (preg_match("/- Disk Space Begin -/", $body ))
				{
					$bDisk = preg_match(
						"! (\-+) Disk Space Begin (\-+)\s*\n".
							"[\s\n]*".
							"\s*Filesystem\s+Size\s+Used\s+Avail\s+Use%\s+Mounted on\s*\n".
							".*".
							"(\s*[/_\-a-zA-Z_0-9]+[\s\n]+[0-9]+(\.[0-9]+)?[MGT]?\s+[0-9]+(\.[0-9]+)?[MGT]?\s+[0-9]+(\.[0-9]+)?[MGT]?\s+[0-9]+%\s+[_/a-zA-Z0-9-]+\s*\n)+".
							"[\s\n]*".
						" (\-+) Disk Space End (-+)\s*\n!s", $body );
						
					//if ( !$bDisk ) echo "BAD: Failed disk\n";
					$bGood &= $bDisk;
				}
				$bForce = false;
				if ( $bGood )				
				{
					setHostFlag( $host, "LOGWATCH", $udate, "GOOD", $mem, $bForce );	
					//echo "In logwatch for ".$host." GOOD ".$udate."\n";
				}
				else
				{
					//echo "In logwatch for ".$host." BAD ".$udate."\n";
					if ( preg_match("/Authentication Failures/", $body ) )
						setHostFlag( $host, "LOGWATCH", $udate, "BAD", $mem, $bForce );	
					else
						setHostFlag( $host, "LOGWATCH", $udate, "PENDING", $mem, $bForce);	
				}
				
			}		
			else if ( preg_match("/CentOS Mirror Update/",$subj ) )
			{
				setInfrastructureFlag( "GENERAL.MIRROR", $udate, "PENDING", $mem );
			}
			else if ( preg_match("/^.*: RKHunter /", $subj ) )
			{
				$array = preg_split("#(:| )#", $subj, NULL, PREG_SPLIT_NO_EMPTY );
				if ( $array[2] == "EMPTY" )
				{
					setHostFlag( $array[0], "RKHUNTER", $udate, "GOOD", $mem );
				}
				else
				{
					if ( $array[2] != "PLEASE")
					{
						setHostFlag( $array[0], "RKHUNTER", $udate, "BAD", $mem );
						alarm( $mem );
					}
					else
					{
						$body = $redis->hget( $key, "body");
						if ( preg_match("/^(Warning\: Download of '(.+)' failed\: Unable to determine the latest version number\.\r\n)*Complete/s", $body ) )
						{
							setHostFlag( $array[0], "RKHUNTER", $udate, "GOOD", $mem );
						}
						else
						{
							setHostFlag( $array[0], "RKHUNTER", $udate, "BAD", $mem );
							alarm( $mem );
						}
						
					}
				}
			}
			else if ( preg_match("![A-Za-z0-9]+:[\s]*WHO Status!", $subj ))
			{
				$parts = explode(":", $subj );
				$host = $parts[0];
				$body = $redis->hget($key, "body");
				$lines = explode(PHP_EOL, $body);
				$status = "GOOD";
				for ( $idx = 0; $idx < count($lines); $idx ++ )
				{
					$aLine = trim(preg_replace( "/\r|\n/", "", $lines[$idx] ));
					if ( $aLine == "Complete" || $aLine == "" ) 
						continue;
					$status = "PENDING";
					break;
				}

				setHostFlag( $host, "WHO", $udate, $status, $mem );
				if ( $status == "BAD" )
					alarm( $mem );
			}
			else if ( preg_match("![A-Za-z0-9]+:[\s]*SUDO Status!", $subj ))
			{
				$parts = explode(":", $subj );
				$host = $parts[0];
				$body = $redis->hget($key, "body");
				$lines = explode(PHP_EOL, $body);
				$status = "BAD";
				if ( count($lines) > 1 )
				{
					$parts = explode(" ", $lines[0] );
					if ( $parts[0] == "5da55a26faf886d0958f6adbae4078b2" //5da...b2 is the good hash for CentOS 6.X
						|| $parts[0] == "e53d2388be272d7fa77b2ea1ebe0d893" //e5...93 after sudo version 1.8.6p3-25 on CentOS6
						|| $parts[0] == "e8e73f16ed73309df7574c12fbcc0af7" //e8...f7 is the good hash for Ubuntu 14.04
						|| $parts[0] == "bd1d2dfa643180176c6ff01b62fec1bf" //bd1..bf is the good hash for Ubuntu 16.04
						|| $parts[0] == "edcf6528783ecffd3f248c8089dc298e" //edc..8e is the good hash for Ubuntu 18.04
						|| $parts[0] == "ef817e657e3ffa6b0a88f59e3fc7241b" //ef...1b is CentOS6 with wheel group enabled for MOT
						|| $parts[0] == "93f8259808fc2832c31d81bbb571ead0" //93...d0 is CentOS7.3
						|| $parts[0] == "ddf084ea6867ae750771b3355ca94855" //dd...55 is CentOS7.4
						|| $parts[0] == "1b134d95a4618029ff962a63b021e1ca" ) //1b...ca is CentOS7.6
					{
						$status = "GOOD";
						for ( $idx = 1; $idx < count($lines); $idx ++ )
						{
							$aLine = trim(preg_replace( "/\r|\n/", "", $lines[$idx] ));
							if ( $aLine == "Complete" || $aLine == "" ) 
								continue;
							if ( $aLine[0] == "#" ) //allow comment lines
								continue;

							if ( ($aLine == "Cmnd_Alias RPMINSTALL=/bin/rpm" || $aLine == "jenkins ALL=(ALL) NOPASSWD:RPMINSTALL" || $aLine == "Defaults:jenkins !requiretty") && ($host == "linux-build64" || $host == "jenkins-centos7") )
								continue;

							if ( preg_match("/drice\s+ALL=\(ALL\:ALL\)\s+ALL/",$aLine) && ($host == "jenkins-ubt-1604" || $host == "anvel-ubt1404" || $host == "anvel-ubt1404-ros" || $host == "anvel-ubt1604-ros") )
								continue;

							if ( preg_match("/drice\s+ALL=\(ALL\)\s+ALL/",$aLine) && ($host == "testrail-dev" || $host == "jenkins-centos7" || $host == "anvel-centos7") )
								continue;

							if ( preg_match("/drice\s+ALL=\(ALL\)\s+ALL/",$aLine) && ($host == "rusty" ) )
								continue;

							if ( preg_match("/jenkins\s+ALL=\(ALL\)\s+ALL/",$aLine) && ($host == "linux-build64" || $host == "jenkins-qs" || $host == "jenkins-centos7" || $host == "interceptorbuild64" ) )
								continue;
							$status = "BAD";
							break;
						}
					}
				}

				setHostFlag( $host, "SUDO", $udate, $status, $mem );
				if ( $status == "BAD" )
					alarm ($mem );
			}
			else if ( preg_match("![A-Za-z0-9]+:[\s]*AIDE DB!", $subj ))
			{
				$parts = explode(":", $subj );
				$host = $parts[0];
				if ( preg_match("/looks OK!/", $subj ))
				{
					setHostFlag( $host, "AIDE", $udate, "GOOD", $mem );
				}
				else
				{
					setHostFlag( $host, "AIDE", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}			
			else if ( preg_match("!/usr/s?bin/aide!", $subj ))
			{
				$parts = split( "[<>@]",$subj );
				if ( count($parts)<3 )
				{
					alarm($mem);
					continue;
				}
				$host = $parts[2];
				$body = $redis->hget( $key, "body");
				if ( preg_match("/### All files match AIDE database. Looks okay!/",$body ) )
				{
					setHostFlag( $host, "AIDE", $udate, "GOOD", $mem );
				}
				else
				{
					setHostFlag( $host, "AIDE", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}
			else if ( preg_match("!AIDE DB!", $subj ))
			{
				$host = $mailbox;
				if ( preg_match("/looks OK!/", $subj ))
				{
					setHostFlag( $host, "AIDE", $udate, "GOOD", $mem );
				}
				else
				{
					setHostFlag( $host, "AIDE", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}			
			else if ( $mailbox == "acronisbackups" || $mailbox=="acronis" || $sentto=="acronis_notify" )
			{
				if ( preg_match("/Backups status/", $subj ) )
				{
					alarm( $mem );
				}
				else
				{
					$parts = split(" ", $subj );
					$host = $parts[0];
					if ( preg_match("/Task completed successfully/i",$subj ) )
					{
						setInfrastructureFlag( "Acronis.".$host , $udate, "GOOD", $mem );
					}
					else
					{
						setInfrastructureFlag( "Acronis.".$host , $udate, "BAD", $mem );
						alarm( $mem );
					}
				}
			}
			else if ( $mailbox == "uraniumbackups" || $sentto=="uranium_notify" )
			{
				$segs = split(":", $subj );
				$host = preg_replace('/\s+/', '_', trim($segs[1]));
				if ( substr($host,0,1)=='_')
				{
					//Showers says ignore the ones which start with _
				}
				else
				{
					if ( preg_match("/Backup completed successfully/i",$subj ) )
					{
						setInfrastructureFlag( "Backup.URANIUM.".$host , $udate, "GOOD", $mem );
					}
					else
					{
						setInfrastructureFlag( "Backup.URANIUM.".$host , $udate, "BAD", $mem );
						alarm( $mem );
					}
				}
			}
			else if ( preg_match("/(Killbot|Fatbot) RAID status/", $subj, $matches ) )
			{
				$host = $matches[1];
				$body = $redis->hget( $key, "body");			
				$bGood = false;
				if ( preg_match( "/".
					".+".
					"\n".
					"\s*Active Devices\s+:\s+([0-9]+)\s*\n".
					"\s*Working Devices\s+:\s+([0-9]+)\s*\n".
					".+".
					"/", $body, $matches ) )
				{
					if ( $matches[1] == $matches[2] )
					{
						setHostFlag( $host, "RAID", $udate, "GOOD", $mem );	
						$bGood = true;
					}
				}
				if ( !$bGood )
				{
					setHostFlag( $host, "RAID", $udate, "BAD", $mem );	
					alarm( $mem );								
				}
			}
			else if ( preg_match( "/([[0-9a-zA-Z_-]+) Backup Status: (FAILED|SUCCESS)/", $subj, $matches ) )
			{
				$host = $matches[1];
				$status = $matches[2];
				if ( $status == "SUCCESS" )
				{
					setInfrastructureFlag( "BACKUP.".$host, $udate, "GOOD", $mem );
				}
				else
				{
					setInfrastructureFlag( "BACKUP.".$host, $udate, "BAD", $mem );
					alarm( $mem );
				}
			}
			else if ( preg_match( "/([A-Za-z0-9-]+)(\..+)* LDAP Backup (FAILURE|SUCCESS)/", $subj, $matches ) )
			{
				$host = $matches[1];
				$status = $matches[3];
				if ( $status == "SUCCESS" )
				{
					setInfrastructureFlag( "BACKUP.LDAP", $udate, "GOOD", $mem );
				}
				else
				{
					setInfrastructureFlag( "BACKUP.LDAP", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}
			else if ( preg_match( "/passMOT DB backup (FAILED!|successful)/", $subj, $matches ) )
			{
				$status = $matches[1];
				if ( $status == "successful" )
				{
					setInfrastructureFlag( "BACKUP.PASS MOT", $udate, "GOOD", $mem );
				}
				else
				{
					setInfrastructureFlag( "BACKUP.PASS MOT", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}			
			else if ( preg_match( "/Veracity Backup status/", $subj ) )
			{
				setInfrastructureFlag( "BACKUP.VERACITY", $udate, "PENDING", $mem );
			}
			else if ( preg_match("/Most recent RAID event log/", $subj ) )
			{
				preg_match_all("/\[(.+)\] .+/", $subj, $matches);
				$host = $matches[1][0];
				setHostFlag( $host, "RAID", $udate, "PENDING", $mem );	
			}
			else if ( preg_match("/LDAP Hosts Change/", $subj ) )
			{
				/*$body = $redis->hget( $key, "body");
				$Parser = new eXorus\PhpMimeMailParser\Parser();
				$Parser->setText($body);
				$text = $Parser->getMessageBody("text");*/
				setInfrastructureFlag("LDAP.HOSTS", $udate, "PENDING", $mem );
				alarm( $mem );
			}
			else if ( preg_match("/Burglar Alarm state changed: /", $subj, $matches ) )
			{
				if ( preg_match("/burglar alarm is NOT going off/", $subj, $matches ) )
					setInfrastructureFlag( "SENSORS.BURGLAR", $udate, "GOOD", $mem );
				else
				{
					setInfrastructureFlag( "SENSORS.BURGLAR", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}
			else if ( preg_match("/Temperature Sensor ([0-9A-Za-z]+) - Notification/", $subj, $matches ) )
			{
				$sensor = $matches[1];
				$body = $redis->hget($key,"body");
				$lines = explode(PHP_EOL, $body);
				$status = "BAD";
				if ( count($lines) > 1 )
				{
					$status = "GOOD";
					for ( $idx = 0; $idx < count($lines); $idx ++ )
					{
						$line = trim(preg_replace( "/\r|\n/", "", $lines[$idx] ));
						if ( strlen($line) == 0 ) continue;
						if ( $line == "Trigger:" ) continue;
						$array = preg_split("/:/", $line, NULL, PREG_SPLIT_NO_EMPTY );
						if ( count($array) == 2 )
						{
							if ( $array[0] == "Trigger" )
							{
								if ( strpos($array[1],'Normal') !== false )
								{
									//OK!
								}
								else if ( strpos($array[1],'Weekly Status') !== false )
                                                                {
                                                                        //OK!
                                                                }
								else
								{
									alarm($mem);
									$status = "BAD";
								}
							}
							else if ( $array[0] == "Battery" )
								setInfrastructureFlag("SENSORS.TEMP.$sensor.BATTERY", $udate, $array[1], $mem );
							else
								setInfrastructureFlag("SENSORS.TEMP.$sensor.".$array[0],$udate,$array[1], $mem );
							continue;
						}
						alarm($mem);
						$status = "PENDING";
						break;
					}
				}
				setInfrastructureFlag( "SENSORS.TEMP.".$sensor, $udate, $status, $mem );
			}
			else if ( preg_match("/Results from scan.php/", $subj ) )
			{
				setInfrastructureFlag( "GENERAL.PRESENTATIONS", $udate, "PENDING", $mem );
			}
			else if ( preg_match("/ZoneMinder camera status EMPTY/", $subj ) )
			{
				setInfrastructureFlag(  "GENERAL.ZONEMINDER", $udate, "GOOD", $mem );
			}
			else if ( preg_match("/ZoneMinder camera status PLEASE READ/", $subj ) )
			{
				setInfrastructureFlag(  "GENERAL.ZONEMINDER", $udate, "BAD", $mem );
			}
			else if ( preg_match("/Samba SID/", $subj ) )
			{
				setInfrastructureFlag( "GENERAL.SAMBASID", $udate, "PENDING", $mem );
				alarm( $mem );
			}
			else if ( preg_match("/([A-Za-z0-9-]+) RAID SUMMARY/", $subj, $matches ) )
			{
			}
			else if ( preg_match("/([A-Za-z0-9-]+) RAID Status/", $subj, $matches ) )
			{
				$host = $matches[1];

				$body = $redis->hget($key, "body");
				$lines = explode(PHP_EOL, $body);
				$lastPatrol = "";
				$lastDate = "";
				$lastConsistency=array();
				$fullStatus = "GOOD";
				foreach ( $lines as $line )
				{
					if ( startsWith( $line, "Time: ") )
						$lastDate = substr($line,6,strlen($line)-8);
					else
					if ( startsWith( $line, "Event Description: ") )		
					{
						$line = substr($line, 19, strlen($line)-20);
						if ( startsWith( $line, "Consistency") )
						{
							$line=str_replace(","," ",$line);
							$array = preg_split("/ on VD /", $line, NULL, PREG_SPLIT_NO_EMPTY );
							if ( count($array) == 1 )
							{
								$array = preg_split("/ VD /", $line, NULL, PREG_SPLIT_NO_EMPTY );
							}
							$parts = explode(" ",$array[1]);
							$key = $parts[0];
							$lastConsistency[$key]=$array[0];
						}
						else
						if ( startsWith( $line, "Patrol" ) )
						{
							if ( $line == "Patrol Read complete" )
								$lastPatrol = "GOOD";
							else
								$lastPatrol = $line;
						}
						else
						if ( startsWith( $line, "Event log cleared" ) ||
							startsWith( $line, "Power state change on " ) ||
							startsWith( $line, "Battery charge complete" ) ||
							startsWith( $line, "CC Schedule properties changed" ) ||
							startsWith( $line, "Battery temperature is normal" ) ||
							startsWith( $line, "Battery relearn " ) ||
							startsWith( $line, "Battery started charging" ) ||
							false )
							continue;
						else
						{
							//echo $line."\n";
							$fullStatus = "PENDING";
						}
					}
				}
				setInfrastructureFlag( "RAID.".$host, $udate, $fullStatus, $mem );
				if ( $lastPatrol != "" )
					setInfrastructureFlag("Patrol Read.".$host, $udate, $lastPatrol, $mem );

				foreach ( $lastConsistency as $k => $v )
				{
					if ( $v == "Consistency Check done" )
						$v = "GOOD";
					setInfrastructureFlag("Consistency Check.".$host.":".$k, $udate, $v, $mem );
				}
			}
			else if ( preg_match("/Chksum Scan/", $subj ) )
			{
				$body = $redis->hget( $key, "body");			
				$bGood = false;
				if ( preg_match( "/YO, ITROBOT, NO TOUCH PERFORMED/", $body ) )
				{
					setInfrastructureFlag( "GENERAL.BoxyChksum", $udate, "GOOD", $mem );
				}
				else
				{
					setInfrastructureFlag( "GENERAL.BoxyChksum", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}
			//else if ( $mailbox == "zabbix_notify" && $host == "mail.internal.quantumsignal.com" )
			//{
			//	alarm( $mem );
			//}
			//else if ( preg_match("/\*\*\*/", $subj ) )
			//{
			//	alarm( $mem );
			//}
			//else if ( preg_match("/Postmaster notify/", $subj )||preg_match("/Returned mail/", $subj) || preg_match("/Warning: could not send message/", $subj) )
			//{
			//	alarm( $mem );
			//}
//			else if ( preg_match( "/passMOT/", $subj, $matches ) )
//			{
			else if ( preg_match( "/([A-Za-z0-9-]+)(\..+)* Daily Backup Status - (Alert|Warning|OK)/", $subj, $matches ) )
			{
				$host = $matches[1];
				$status = $matches[3];
				if ( $status == "OK" )
				{
					setInfrastructureFlag( "BACKUP.".$host, $udate, "GOOD", $mem );
				}
				else
				{
					setInfrastructureFlag( "BACKUP.".$host, $udate, "BAD", $mem );
					alarm( $mem );
				}
			}
			else if ( preg_match("![A-Za-z0-9]+:[\s]*REPO Status!", $subj ))
			{
				$error = false;
				$parts = explode(":", $subj );
				$host = $parts[0];
				$body = $redis->hget( $key, "body");
				$lines = explode("\n", $body);
				//$count = substr_count( $body, "\n");
				$count = count($lines);
				if (substr($lines[0],0,21) === "Reading package lists") //Ubuntu
				{
					$msg = "BAD";
					foreach ($lines as $line)
					{
						if (preg_match("/^\d+ upgraded/i", $line))
						{
							$msg = intval($line)." ENTRIES";
							break;
						}
					}
					setHostFlag( $host, "REPO", $udate, $msg, $mem );
				}
				else //CentOS
				{
					foreach ($lines as $line)
					{
						if (strlen(trim($line))==0) $count--;
						if (startsWith($line,"Loaded plugins")) $count--;
						if (startsWith($line,"Loading mirror speeds")) $count--;
						if (startsWith($line,"Complete")) $count--;
						if (startsWith($line," * ")) $count--; //epel and rpmfusion put in lines like this
						if (startsWith($line,"[Errno 14]")) //TODO identify more error types than 404
						{
							$error = true;
							break;
						}
					}
					if ($error)
						$msg = "BAD";
					else
					{
						$msg = "GOOD";
						if ($count>0) $msg = $count." ENTRIES";
					}
				 	setHostFlag( $host, "REPO", $udate, $msg, $mem );
				}
			}			
			else if ( preg_match( "/Anvelsim DB Dump/", $subj, $matches ) )
			{
				if ( $subj == "Anvelsim DB Dump Success" )
				{
					setInfrastructureFlag( "BACKUP.ANVELSIMDB", $udate, "GOOD", $mem );
				}
				else
				{
					setInfrastructureFlag( "BACKUP.ANVELSIMDB", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}
			else if ( preg_match( "/RockBLOCK/", $subj, $matches ) )
			{
				setInfrastructureFlag( "GENERAL.ROCKBLOCK", $udate, "PENDING", $mem );
			}
			else if ( preg_match( "/PASS escalation rules log/", $subj, $matches ) )
			{
				setInfrastructureFlag( "GENERAL.PASSESCALATION", $udate, "PENDING", $mem );
			}
			else if ( preg_match( "/Global Task Status/", $subj, $matches ) )
			{
				if ( $subj == "Global Task Status - OK" )
				{
					setInfrastructureFlag( "BACKUP.IDERA.GLOBAL.TASK", $udate, "GOOD", $mem );
				}
				else
				{
					setInfrastructureFlag( "BACKUP.IDERA.GLOBAL.TASK", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}
			else if ( preg_match( "/IDERA Global Alert Report/", $subj, $matches ) )
			{
				if ( $subj == "IDERA Global Alert Report - OK" )
				{
					setInfrastructureFlag( "BACKUP.IDERA.GLOBAL.ALERT", $udate, "GOOD", $mem );
				}
				else
				{
					setInfrastructureFlag( "BACKUP.IDERA.GLOBAL.ALERT", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}
			else if ( preg_match( "/Global Idera Server Status/", $subj, $matches ) )
			{
				if ( $subj == "Global Idera Server Status - OK" )
				{
					setInfrastructureFlag( "BACKUP.IDERA.GLOBAL.SERVER", $udate, "GOOD", $mem );
				}
				else
				{
					setInfrastructureFlag( "BACKUP.IDERA.GLOBAL.SERVER", $udate, "BAD", $mem );
					alarm( $mem );
				}
			}
			else if ( preg_match( "/Alert from Network Security Appliance/", $subj, $matches ) )
			{
				if ( preg_match( "/TCP Flood/", $subj, $matches ) )
				{
					//echo "TCP Flood:".$subj."\n";
				}
				else if ( preg_match( "/Port Scan/", $subj, $matches ) )
				{
					//echo "Port Scan:".$subj."\n";
				}
				else if ( preg_match( "/TCP FIN Scan/", $subj, $matches ) )
				{
					//echo "FIN Scan:".$subj."\n";
				}
				else if ( preg_match( "/FIN Flood/", $subj, $matches ) )
				{
					//echo "FIN Flood:".$subj."\n";
				}
				else if ( preg_match( "/RST Flood/", $subj, $matches ) )
				{
					//echo "RST Flood:".$subj."\n";
				}
				else if ( preg_match( "/TCP Xmas Tree Attack/", $subj, $matches ) )
				{
					//echo "Xmas Tree:".$subj."\n";
				}
				else
				{
					$map[$mem] = true;
					alarm( $mem );
				}
			}
			else if ( preg_match("/Alarm VM CPU Usage/", $subj, $matches )
					|| preg_match("/Alarm VM Memory Usage/", $subj, $matches ) )
			{
				if ( preg_match("/\[VMware vCenter \- Alarm VM ([A-Za-z]+) Usage [A-Za-z0-9-]+\] \*\*\*([A-Za-z0-9-]+)[\.A-Za-z0-9-]*\*\*\*.* from ([A-Za-z]+) to ([A-Za-z]+)/", $subj, $matches ) )
				{
					$alarm = $matches[1];
					$vm = $matches[2];
					$endState = $matches[4];
					setVM( $vm, $alarm, $endState, $mem, $udate );
				}
				else
				{
					echo "Failed to parse VM Msg: ".$subj."\n";
				}
			}
			else if ( preg_match("/HostCPUUsageAlarm/", $subj, $matches )
					|| preg_match("/HostMemoryUsageAlarm/", $subj, $matches ) )
			{
				if ( preg_match("/\[VMware vCenter \- Alarm alarm\.Host([A-Za-z]+)UsageAlarm\] \*\*\*([A-Za-z0-9-]+)[\.A-Za-z0-9-]*\*\*\*.* from ([A-Za-z]+) to ([A-Za-z]+)/", $subj, $matches ) )
				{
					$alarm = $matches[1];
					$host = $matches[2];
					$endState = $matches[4];
					setVMHost( $host, $alarm, $endState, $mem, $udate );
				}
				else
				{
					echo "Failed to parse Host Msg\n";
				}
			}
/*			else if ( preg_match("/SysLog Alerts/", $subj, $matches ) )
			{
				$body = trim($redis->hget($key, "body"));
				
				$body =  base64_decode($body);
				$v = json_decode($body);
				if ( $v != null )
				{
					foreach ( $v as $alert )
					{
						$t = $alert->time;
						$dt_part = substr( $t,0,10);
						$redis->lpush("IT.firewall.".$dt_part, json_encode($alert));
						
						if ( $alert->m == 1080 )
						{
							if ( $alert->msg == "SSL VPN zone remote user login allowed" )
							{
								$who = $alert->usr;
								$dt = strtotime($alert->time);
								$ip = $alert->src;
								setInfrastructureFlag( "VPN.".$who, $dt, $ip, $mem );
							}
						}
					}
				}
				alarm($mem);
			}*/
			else
			{
				alarm( $mem );
			}
		}
		
		$parts = explode( ".", $date );	
		$lastPart = $parts[count($parts)-1];
		$diffDays = (time() - $lastPart)/3600/24;
		if ( $diffDays > 30 )
		{
			echo "Retiring ".date(DATE_ATOM,$lastPart)."\n";
			$redis->del( $date );
		}

	}	

	$results = getAlarms();
	$newAlarms = array();
	foreach ( $results as $result )
	{
		if ( isset($map[$result]))
			continue;
		$newAlarms[] = $result;
	}	
	if ( count( $newAlarms ) > 0 )
	{
		$msg = "";
		$msg .= "The following new alarms have been received:\r\n\r\n";
		foreach ( $newAlarms as $k )
		{
			$aMsg = getMsg($k,false);
			$hdrs = $aMsg["headers"];			
			$subj = isset($hdrs->subject) ? @iconv_mime_decode($hdrs->subject) : "<none>";
			$from = isset($hdrs->senderaddress) ? $hdrs->senderaddress : "<unknown>";
			$msg .= "\tSubject: ".$subj."\r\n";
			$msg .= "\tWhen: ".date(DATE_ATOM,$hdrs->udate)."\r\n";
			$msg .= "\tFrom: ".$from."\r\n";
			$msg .= "\r\n";
		}
		if ( count($map) > 0 )
		{
			$msg .= "The following alarms are already present:\r\n\r\n";
			foreach ( $map as $k => $v )
			{
				$aMsg = getMsg($k,false);
				if ( $aMsg["state"] == "ACK" )
					continue;
				
				$hdrs = $aMsg["headers"];
				$subj = isset($hdrs->subject) ? @iconv_mime_decode($hdrs->subject) : "<none>";
				$from = isset($hdrs->senderaddress) ? $hdrs->senderaddress : "<unknown>";
				$msg .= "\tSubject: ".$subj."\r\n";
				$msg .= "\tWhen: ".date(DATE_ATOM,$hdrs->udate)."\r\n";
				$msg .= "\tFrom: ".$from."\r\n";
				$msg .= "\r\n";
			}
		}
		mail("alarm_notify@mail.internal.quantumsignal.com", "IT Robot New Alarms Notification", $msg );
	}
	echo count($newAlarms)." new alarms found\n";
	echo "Processed ".$msgCount." messages\n";
}

?>
