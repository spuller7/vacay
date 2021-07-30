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

require_once("xmpp/XMPP.php");
//Set a debug user if you want someone to be notified when this script does things
//$debuguser=

class DBManager
{
		function __construct()
		{
			$this->m_pdo = new PDO('mysql:host=10.1.51.10;dbname=timekeeping','timekeeping','doofus', array( PDO::ATTR_PERSISTENT => true ) );
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

function isLastWeekdayOfPayPeriod()
{
	$weekday = date("w");
	//if today is Sunday or Saturday, it is not the last weekday of anything
	if ($weekday == 0 || $weekday == 6)
		return false;
	$nowday = date("j");
	$lastdayofmonth = date("t");
	//if it is the last day of the month or the 15th but not Sunday or Saturday, yes it is the last weekday of the pay period
	if ($nowday == 15 || $nowday == $lastdayofmonth)
		return true;
	//if it is Friday and the last day of the month or the 15th falls on Saturday or Sunday, yes it is the last weekday of the pay period
	if ( $weekday == 5 && ($nowday == 14 || $nowday == 13 || $nowday == $lastdayofmonth - 1 || $nowday == $lastdayofmonth - 2) )
		return true;
	return false;
}

//Set the appropriate network and user info for your jabber server
$xmpp = new XMPPHP_XMPP(
	'jabber.internal.quantumsignal.com', 
	5222, 
	'username', 
	'password', 
	'xmpphp', 
	'jabber.internal.quantumsignal.com', 
	$printlog=False);
$xmpp->useEncryption(false);
$xmpp->connect();
$xmpp->processUntil('session_start');

$db = new DBManager();
$pdo = $db->pdo();
$sth = $pdo->prepare( "select u.Username username, DATEDIFF(NOW(), max(te.Date)) daycount from Users u, TimeEntryRecord te where u.UserID = te.UserID AND u.State = 'active' AND te.Date < NOW() group by u.Username order by daycount desc" );
$sth->execute( );
//checkDBError( $sth );
$invalid = array("rohde","mrohde","QSAdmin","root");
//remap timekeeping usernames to jabber usernames
$remap = array("vperlin" => "victor", "ncushing" => "nicole", "srohde" => "steve");
$map = array();
while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
{
	if (!(in_array($obj->username,$invalid)))
	{
		$days_difference = $obj->daycount;
		$weeks_difference = floor($days_difference/7);
		$first_day = date("w", strtotime("-".$days_difference." days"));
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder;
		if (($odd_days > 7) && (date("w", strtotime("-".$days_difference." days")) != 0))
			$days_remainder--;
		if ($odd_days > 6 && (date("w", strtotime("-".$days_difference." days")) != 6))
			$days_remainder--;
		$total = ($weeks_difference * 5) + $days_remainder;
		$map[$obj->username] = $total;
	}
}

$nowhour = date('H');
$submitnag = false;
if ( isLastWeekdayOfPayPeriod() && $nowhour == 14)
{
	$submitnag = true;
	if (date('j') > 15)
	{
		$startdate = date("Y-m-16");
		$enddate = date("Y-m-t");
	}
	else
	{
		$startdate = date("Y-m-01");
		$enddate = date("Y-m-15");
	}
	$startdate = 
	$sth = $pdo->prepare( "SELECT u.Username username, Count(ter.State) FROM Users u, TimeEntryRecord ter WHERE u.UserID = ter.UserID AND ter.Date >= \"$startdate\" AND ter.Date <= \"$enddate\" AND (ter.State = \"pending\" OR ter.State = \"authorized\" OR ter.State = \"finalized\") group by ter.UserID" );
	$sth->execute( );
	$goodpeople = array();
	while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
	{
		$username = $obj->username;
		if (isset($remap[$username]))
			$username = $remap[$username];
		array_push($goodpeople, $username);
	}
}
$xmpp->presence("Nagging");
$xmpp->getRoster();
//CMS command_result is not the correct thing to process until but the timeout of one works for getting the entire roster
$xmpp->processUntil(array('command_result'),3);
foreach ($map as $username => $total)
{
	$msg = "";
	if ($total > 0 && $nowhour > 16 - $total)
		$msg = "Fill out your timesheet! You are ".$total." day".($total>1?"s":"")." behind.";
	if ($msg != "" || $submitnag)
	{
		if (isset($remap[$username]))
			$username = $remap[$username];
		$status = $xmpp->roster->getPresence($username."@jabber.internal.quantumsignal.com");
		if ($status)
		{
			if ($msg != "")
			{
				$xmpp->message($username."@jabber.internal.quantumsignal.com", $msg);
				if (isset($debuguser))
					$xmpp->message($debuguser, $username." was nagged");
			}
			if ($submitnag)
			{
				if (!(in_array($username,$goodpeople)))
					$xmpp->message($username."@jabber.internal.quantumsignal.com", "It is the last day of the pay period. Please submit your timesheet before leaving.");
			}
		}
		else if (isset($debuguser) && $msg != "")
		{
			$xmpp->message($debuguser, $username." should have been nagged but they were offline");
		}
	}
}

if (isset($debuguser))
	$xmpp->message($debuguser, "timenag script ran".($submitnag?" on the last day of the pay period":""));
$xmpp->disconnect();

?>
