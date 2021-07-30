<?php
require('mailmgr.php');

$results = getAlarms();

//$msgs = retrieveMsgs( $results );

//sortMsgs( $msgs );

//foreach ( $msgs as $msg )
//{
//	echo $msg['id']."\n";
//}

unset($results[2] );

$map = array();

foreach ( $results as $result )
{
	$map[$result] = true;
}
var_dump($map);
$results = getAlarms();
foreach ( $results as $result )
{
	if ( isset($map[$result]))
		continue;
	echo "FOUND: ".$result."\n";
}
?>
