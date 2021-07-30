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

require_once( dirname( __FILE__ )."/common.php" );
require_once( dirname( __FILE__ )."/DB/DBManager.php" );

function username()
{
	return $_SESSION['USER_NAME'];
}

function userid()
{
	return $_SESSION['USERID'];
}

//function hex2bin($hexdata) 
//{
//	$bindata="";
//	for ($i=0;$i<strlen($hexdata);$i+=2) 
//	{
//		$bindata.=chr(hexdec(substr($hexdata,$i,2)));
//	}
//	return $bindata;
//}

function hashPassword( $UserName, $Password )
{
	$ctx = hash_init('md5');
	hash_update( $ctx, $UserName );
	hash_update( $ctx, ":timekeeping:");
	hash_update( $ctx, $Password );
	return hash_final($ctx);
}	

function toEntities( $text )
{
	return mb_convert_encoding($text, "HTML-ENTITIES", "UTF-8");
}	

function get_microtime()
{ 
    list($secs, $micros) = split(" ", microtime()); 
    $mt = $secs + $micros; 
    
    return $mt; 
}
?>
