<?php

require('mailmgr.php');
$now = time();
$count = 0;
snmp_set_valueretrieval(SNMP_VALUE_OBJECT);
$mib = "1.3.6.1.2.1.17.4.3.1.2";
$result = snmprealwalk($switchIP, "public", $mib );
$now = time();

foreach ( $result as $key=>$value )
{
	$parts = explode( ".", $key );
	$len = count($parts);
	$mac = sprintf("%02x:%02x:%02x:%02x:%02x:%02x",$parts[$len-6],$parts[$len-5],$parts[$len-4],$parts[$len-3],$parts[$len-2],$parts[$len-1]);
	
	$mackey = "IT.macregistrar.".$mac;
	$a = $value->value;
	$port = "s".(floor($a/50)+1)."/p".($a%50);
	$portID = $a+0;
	
	echo $mac." ".$port."\n";

	$val = $redis->hset( $mackey, "port", $port );
	$val = $redis->hset( $mackey, "portID", $portID );
	$redis->hset($mackey,"lastSeen", $now);
	if ( $redis->hget($mackey,"state") == "" )
		$redis->hset($mackey, "state", "PENDING");
	if ( $redis->hget($mackey,"description") == "" )
		$redis->hset($mackey, "description", "auto added");
}
/*
$values = $redis->keys("IT.macregistrar.*");
foreach ( $values as $value )
{
	$mac = substr($value,strlen("IT.macregistrar."));
	$state = $redis->hget($value, "state");
	if ( $state == "PENDING" )
	{
		$redis->del($value);
		echo $value."<br/>";
	}
}*/
echo "DONE!";
?>
