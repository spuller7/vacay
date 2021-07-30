<?php
require('mailmgr.php');
$input = file_get_contents("php://input");
$lines = explode("\n",$input);
$now = time();
$count = 0;
snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
foreach ( $lines as $line )
{
	if ( trim($line) == "" )
		continue;
	
	$parts = explode(" ",$line);
	$host = $parts[0];
	if ( $host == "?" )
		$host = "";
	$ip = substr($parts[1],1,strlen($parts[1])-2);
	$mac = $parts[2];
	if ( $mac == "<incomplete>" )
		$mac = "";
	
	$key = "IT.pings.".$ip;
	
	if ( $mac != "" )
	{
		$val = $redis->hset( $key, "lastMac", $now);
		$val = $redis->hset( $key, "mac", $mac );
		
		$mackey = "IT.macregistrar.".$mac;

		$mib = "1.3.6.1.2.1.17.4.3.1.2";
		$parts = explode(":", $mac);
		foreach ( $parts as $p )
		{
			$mib .= ".".hexdec($p);
		}
		
		$port = "";
		$portID = 0;
		$a = snmp2_get($switchIP, "public", $mib); 
		if ( $a != "Unknown value type" )
		{
			$port = "s".(floor($a/50)+1)."/p".($a%50);
			$portID = $a+0;
		}
		
		$val = $redis->hset( $mackey, "port", $port );
		$val = $redis->hset( $mackey, "portID", $portID );
		
		$redis->hset($mackey,"lastSeen", $now);
		if ( $redis->hget($mackey,"state") == "" )
			$redis->hset($mackey, "state", "PENDING");
		if ( $redis->hget($mackey,"description") == "" )
			$redis->hset($mackey, "description", "auto added");
	}
	
	if ( $host != "" )
	{
		$val = $redis->hset( $key, "lastHost", $now);
		$val = $redis->hset( $key, "hostname", $host );
	}
	$count++;
}
echo "OK\nProcessed ".$count." entries\n";
?>
