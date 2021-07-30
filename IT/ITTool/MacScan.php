<?php

if (php_sapi_name() != "cli") 
{
	echo "Not allowed from server";
	exit(0);
}

require('mailmgr.php');

$bad = array();
$unknown = array();
$hashList = array();

$pingValues = $redis->keys("IT.pings.*");
$hostList = array();
foreach ( $pingValues as $pingValue )
{
	$pingMac = $redis->hget($pingValue, "mac");
	if ( $pingMac != "" )
	{
		if ( !isset($hostList[$pingMac]))
			$hostList[$pingMac] = array();
		
		$ip = substr($pingValue,strlen("IT.pings."));
		$hostList[$pingMac][] = $ip;
	}
}
	
$values = $redis->keys("IT.macregistrar.*");
foreach ( $values as $value )
{
	$mac = substr($value,strlen("IT.macregistrar."));
	$state = $redis->hget($value, "state");
	$description = $redis->hget($value, "description");
	$lastSeen = $redis->hget("IT.macregistrar.".$mac, "lastSeen");
	if ( $lastSeen == "" )
		continue;
	
	$age = GetElapsedMinutes($lastSeen);
	if ( $state == "APPROVED")
		continue;
	
	$l  = array();
	if (isset($hostList[$mac]))
		$l = $hostList[$mac];
	
	if ( $state == "UNAPPROVED" && count($l) == 0 )
	{
		continue;
	}
	
	$hl = implode(",",$l);
	$line = $mac;
	if ( $description != "" )
		$line .=" (".$description.")";
	
	if ( count($l) > 0 )
		$line .= "[".$hl."]";
	
	if ( $age > 30 )
	{
		$line .= " - ".GetTimeString($age);
	}
	
	if ( $state == "UNAPPROVED" )
		$bad[] = $line;
	else
		$unknown[] = $line;
	
	$hashList[] = $mac;
}

sort($bad);
sort($unknown);
sort($hashList);

$hashList = json_encode($hashList);

$body = "The following restricted MAC addresses have been detected:\n\n";
foreach ( $bad as $line )
{
	$body .= "\t".$line."\n";
}
$body .= "\nThe following rogue MAC addresses have been detected:\n\n";
foreach ( $unknown as $line )
{
	$body .= "\t".$line."\n";
}

$oldBody = $redis->hget("IT.lastMacAlarm","body");
if ( $oldBody != $hashList )
{
	echo "Sending MAIL\n";
	mail("itrobot@mail.internal.quantumsignal.com", "MAC Address Scanner", $body );
	echo $body;
	$redis->hset("IT.lastMacAlarm","body",$hashList);
	echo "MAIL Sent!\n";
}
else
{
	echo "NO MAIL NEEDED\n";
}
$redis->hset("IT.lastMacAlarm","lastCheck",time());

?>