<?php
require('mailmgr.php');
$input = file_get_contents("php://input");
$lines = explode("\n",$input);
$now = time();
$count = 0;
foreach ( $lines as $line )
{
	if ( trim($line) == "" )
		continue;
	
	$key = "IT.pings.".$line;
	$val = $redis->hset( $key, "lastAlive", $now);
	$count++;
}
echo "OK\nProcessed ".$count." entries\n";
?>