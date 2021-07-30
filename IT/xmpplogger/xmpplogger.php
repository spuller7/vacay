<?php
//////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2020, Quantum Signal, LLC
// 
// This data and information is proprietary to, and a valuable trade 
// secret of, Quantum Signal, LLC.  It is given in confidence by Quantum 
// Signal, LLC. Its use, duplication, or disclosure is subject to the
// restrictions set forth in the License Agreement under which it has 
// been distributed.
//
//////////////////////////////////////////////////////////////////////

require_once("config.php");
require_once("xmpp/XMPP.php");
//Set a debug user if you want someone to be notified when this script does things
//$debuguser=

class DBManager
{
		function __construct()
		{
			global $DBHOST,$DBNAME,$DBUSER,$DBPASSWD;
			$this->m_pdo = new PDO('mysql:host='.$DBHOST.';dbname='.$DBNAME,$DBUSER,$DBPASSWD, array( PDO::ATTR_PERSISTENT => true ) );
			$this->m_pdo->exec('SET CHARACTER SET utf8');
		}
		
		function pdo()
		{
			return $this->m_pdo;
		}
		
		function exec( $sql )
		{
			return $this->m_pdo->exec( $sql );
		}
		
		function __destruct()
		{
			$this->m_pdo = null;
		}

}

function createStatus($pdo, $user, $status, $changeTime)
{
	$sth = $pdo->prepare( "insert into status(username,status,changeTime) value(?,?,?);" );
	$sth->execute(array($user,$status,$changeTime));
}

$db = new DBManager();
$pdo = $db->pdo();

//Set the appropriate network and user info for your jabber server
$xmpp = new XMPPHP_XMPP(
	'jabber.internal.quantumsignal.com', 
	5222, 
	'jenkins', 
	'test1234', 
	'xmpphp', 
	'jabber.internal.quantumsignal.com', 
	$printlog=False);
$xmpp->useEncryption(false);
$xmpp->connect();
$xmpp->processUntil('session_start');
$xmpp->presence("Logging");
$xmpp->getRoster();
//CMS command_result is not the correct thing to process until but the timeout of one works for getting the entire roster
$xmpp->processUntil(array('command_result'),3);
$rosterTime = time();

$filename = 'laststatus.obj';
$lastStatus = unserialize(file_get_contents($filename));
//print_r($lastStatus);
$lastStatusProcessed = array();
foreach($lastStatus->getRoster() as $user)
{
	$userjid = $user['contact']['jid'];
	$hostCount = 0;
	$statusJSON = "[";
	foreach($user['presence'] as $host => $presence)
	{
		$hostCount++;
		if ($hostCount > 1)
			$statusJSON .= ",";
		//print_r($presence);
		$statusJSON .= "{'".$host."','".$presence['show']."','".$presence['status']."'}";
	}
	$statusJSON .= "]";
	//echo "$userjid,$statusJSON\n";
	$lastStatusProcessed[$userjid] = $statusJSON;
}

$thisStatusProcessed = array();
//print_r($xmpp->roster);
foreach($xmpp->roster->getRoster() as $user)
{
	$userjid = $user['contact']['jid'];
	$hostCount = 0;
	$statusJSON = "[";
	foreach($user['presence'] as $host => $presence)
	{
		$hostCount++;
		if ($hostCount > 1)
			$statusJSON .= ",";
		//print_r($presence);
		$statusJSON .= "{'".$host."','".$presence['show']."','".$presence['status']."'}";
	}
	$statusJSON .= "]";
	//echo "$userjid,$statusJSON,$rosterTime\n";
	$thisStatusProcessed[$userjid] = $statusJSON;
}

foreach ($thisStatusProcessed as $user => $status)
{
	if (array_key_exists($user, $lastStatusProcessed))
	{
		if (strcmp($status, $lastStatusProcessed[$user]) !== 0)
		{
			echo "Status changed for $user!\n\t$status\n";
			createStatus($pdo,$user,$status,$rosterTime);
		}
	}
	else
	{
		echo "New user $user appeared!\n\t$status\n";
		createStatus($pdo,$user,$status,$rosterTime);
	}
}

$fp = fopen($filename, 'w');
//fwrite($fp, print_r($xmpp->roster,true));
fwrite($fp, serialize($xmpp->roster));
fclose($fp);

?>

