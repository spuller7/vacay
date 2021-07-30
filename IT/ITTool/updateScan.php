<?php

//curl --request POST --include --header "Content-type: text/xml" --data-binary @- --no-buffer https://amy.internal.quantumsignal.com/IT/updateScan.php
/*

 nmap -oX - -Pn -O -sT 10.1.75.0/24| curl --request POST --include --header "Content-type: text/xml" --data-binary @- --no-buffer https://amy.internal.quantumsignal.com/IT/updateScan.php
 nmap -oX - -Pn -O -sT 10.1.66.0/24| curl --request POST --include --header "Content-type: text/xml" --data-binary @- --no-buffer https://amy.internal.quantumsignal.com/IT/updateScan.php
 nmap -oX - -Pn -O -sT 10.1.51.0/24| curl --request POST --include --header "Content-type: text/xml" --data-binary @- --no-buffer https://amy.internal.quantumsignal.com/IT/updateScan.php



*/
require('mailmgr.php');

global $redis;
/*
$values = $redis->keys("IT.nmap.ports.*");
foreach ( $values as $value )
{
	echo $value;
	$redis->del($value);
}*/

$input = file_get_contents("php://input");
$xml=simplexml_load_string($input) or die("Error: Cannot create object");
$when = time();
foreach ( $xml->host as $key=>$value)
{
	$mac = strtolower($value->address[1]["addr"]);
	if ( $mac == "" )
		continue;
	$status = "".$value->status["state"];
	$address = "".$value->address[0]["addr"];
	$vendor = "".$value->address[1]["vendor"];
	echo $address." ".$status." ".$mac." ".$vendor."\n";
	$key = "IT.nmap.status.".$mac;
	$redis->hset( $key, "status", $status );
	$redis->hset( $key, "mac", $mac );
	$redis->hset( $key, "address", $address );
	$redis->hset( $key, "vendor", $vendor );
	$redis->hset( $key, "when", $when );
	
	$key = "IT.nmap.ports.".$mac;
	$currentMembers = $redis->smembers($key);
	$redis->del( $key );
	foreach ( $value->ports->port as $portK=>$portV)
	{
		$state = $portV->state[0]["state"];
		if ( $state == 'closed' )
			continue;
		
		$protocol = $portV[0]["protocol"];
		$portid = $portV[0]["portid"];
		$reason = $portV->state[0]["reason"];
		$service = $portV->service[0]["name"];
		$redis->sadd( $key, $protocol.".".$portid.".".$service );
	}
}

echo "Total=".$xml->runstats->finished[0]["elapsed"]."\n";
?>
