<?php


if (php_sapi_name() != "cli")
{
        echo "Not allowed from server";
        exit(0);
}

require('mailmgr.php');


$keys = $redis->keys("IT.firewall.*");

sort($keys);

$c = count($keys);
if ( $c == 0 )
	return;

if ($c == 1 )
	$selectedKey = $keys[count($keys)-1];
else
	$selectedKey = $keys[count($keys)-2];
	
$values = $redis->lrange( $selectedKey, 0, -1 );
$map = array();
foreach ( $values as $v )
{
	$r = json_decode($v );
	if ( isset($r->msg) )
		$msg = $r->msg;
	else
		$msg = $v;
	if ( !isset($map[$msg]) )
	{
		$map[$msg] = 0;
	}
	$map[$msg]++;
}


$lines = array();

foreach ( $map as $k => $v )
{
	$lines[] = str_pad( $v, 4, " ", STR_PAD_LEFT )."  ".wordwrap($k, 64, "\n      ");
}

rsort ($lines);
$str = str_pad( "", 70, "=")."\n"."Firewall report for ".substr($selectedKey,12)."\n".str_pad( "", 70, "=")."\n";
foreach ( $lines as $line )
{
	$str .=  $line."\n";
}

mail("itrobot@mail.internal.quantumsignal.com", "Firewall Summary for ".substr($selectedKey,12), $str );
