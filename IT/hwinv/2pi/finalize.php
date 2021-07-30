<?php

require_once( dirname( __FILE__ )."/./database/config.php" );
require_once( dirname( __FILE__ )."/./database/DBManager.php" );
require_once( dirname( __FILE__ )."/DBHelper.php" );
//require 'Spreadsheet/Excel/Writer.php';

//$data = reportReviewerFeedback( 1001, "2020-01-01", "2021-01-01");
//$data = reportUserFeedback( 1001, "2020-01-01", "2021-01-01");

$dt = new DateTime();
$dt->setTime(0,0);

$c = $submit_cooldown-1;

while ( $c > 0 )
{
	$dt->modify("-1 day");
	if ( $dt->format("N") > 0 && $dt->format("N") < 6 )
		$c--;
}

$db = new DBManager();
$pdo = $db->pdo();

$sql = "UPDATE FEEDBACK SET CLOSED_DATE=now() WHERE FEEDBACK_DATE <= :when AND CLOSED_DATE IS NULL"; 
$sth = $pdo->prepare( $sql );
$sth->execute(array( 'when' => $dt->format("Y-m-d")));

echo "Finalized all requests earlier than ".$dt->format("Y-m-d");
?>