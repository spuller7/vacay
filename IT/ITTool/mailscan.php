<?php

if (php_sapi_name() != "cli") 
{
	echo "Not allowed from server";
	exit(0);
}

function getITToolDB()
{
	$db = new PDO($dbConnect, $dbUser, $dbPW);
	return $db;
}

require('mailmgr.php');


$db = getITToolDB();
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
/*$db->prepare("INSERT INTO MESSAGES '50',now(),'qwe','qwe','qwe','qwe','1'")->execute();
*/

global $redis;

//function getMsg( $db, $msgId )
//{
//}


$prefix = "IT.msgstore.mail.";
$msgs = $redis->keys($prefix."*");

$maxID = 0;
$maxState = 0;
$maxBody = 0;
$maxREAD = 0;
$maxHeaders = 0;
$maxStructure = 0;
$maxKey = 0;
$maxValue = 0;
$num = count($msgs);
$imported = 0;
echo $num." messages to process\n";
$keys = array();
foreach ( $msgs as $msg )
{
	$id = substr($msg,strlen($prefix));
	$stuff = $redis->hgetall( $msg );

	$state = $stuff["state"];
	unset($stuff["state"]);

	$body = $stuff["body"];
	unset($stuff["body"]);

	$read = $stuff["READ"];
	unset($stuff["READ"]);
	
	$headers = $stuff["headers"];
	unset($stuff["headers"]);
	
	$structure = $stuff["structure"];
	unset($stuff["structure"]);

	if ( isset( $headers ) )
		$parsed_headers = json_decode($headers);
	else
		$parsed_headers = new stdClass();

	$diffDays = (time() - $parsed_headers->udate)/3600/24;	

	try
	{

		//if ( $diffDays < 15 )
		$stmt = $db->prepare('SELECT COUNT(*) FROM MESSAGES WHERE MSG_ID=? LIMIT 1');
		$stmt->execute( array($id) );
		$count = $stmt->fetch()|0;
	
		if( $count == 0 )
		{
			//echo $parsed_headers->udate." ".$parsed_headers->date." ".$parsed_headers->subject."\n";
				echo "\nInserting ".$id." with size of ".strlen($body)."\n";
				$stmt = $db->prepare('REPLACE INTO MESSAGES (MSG_ID, MSG_DATETIME, HEADERS, STRUCTURE, BODY, STATE, ISREAD) VALUES (?,from_unixtime(?),?,?,?,?,?)');
				$stmt->execute(array($id, $parsed_headers->udate,$headers,$structure,$body,$state,$read));	
				$imported++;
		}
		
		foreach ( $stuff as $key=>$value)
		{
			$stmt = $db->prepare('SELECT PROP_VALUE FROM MSG_PROPS WHERE MSG_ID=? AND PROP_KEY=? LIMIT 1');
			$stmt->execute( array($id, $key) );
			$dbValue = $stmt->fetch();
			if ( $dbValue != $value )
			{
				$stmt = $db->prepare('REPLACE INTO MSG_PROPS (MSG_ID, PROP_KEY, PROP_VALUE) VALUES (?,?,?)');
				$stmt->execute(array($id, $key, $value));
			}
		}				
	} 
	catch (PDOException $e) 
	{
		print "Error!: " . $e->getMessage() . "<br/>";
		die();
	}
	
	if ( $num % 1000 == 0 )
	{
		echo $num." ".$imported."\r";
	}
	$num--;
}
echo "\n\n\n\n";
echo "\n".$num."\n";

		echo "\n".json_encode($keys)."\n";;

//STATE=3 BODY=17874339 READ=1 HEADERS=1424 STRUCT=7888



//      $redis->hset( $key, "headers", json_encode($info) );
//        $redis->hset( $key, "body", $body );
//        $redis->hset( $key, "structure", json_encode($structure) );
//        $redis->hset( $key, "state", "NEW" );
//        setMsgFlag( $info->message_id, "READ", 0 );	

?>
