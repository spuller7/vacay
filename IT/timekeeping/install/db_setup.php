<?php

function execCmd( $cmd, $ignoreError )
{
	global $pdo;
	$result = $pdo->exec($cmd);
	$failed = ($result===false);
	if ( $failed )
	{
		if ( $ignoreError )
		{
			echo "x ";
		}
		else
		{
			echo "Failed executing: ".$cmd."\n";
			print_r($pdo->errorInfo());
			echo "\n";
			return false;
		}
	}
	else
	{
		echo ". ";
	}
	return true;
}

$pdo = new PDO('mysql:host=localhost','root','r00t01', array( PDO::ATTR_PERSISTENT => true ) );

$xml = simplexml_load_file("db_setup.xml");
foreach ( $xml->Commands->Command as $command )
{
	$ignoreError = isset( $command['canFail'] ) && $command['canFail'] == 1;
	if ( !execCmd($command, $ignoreError) )
	{
		die("databse setup failure");
	}
}
?>