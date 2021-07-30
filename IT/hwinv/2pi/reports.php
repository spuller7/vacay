<?php

require_once( dirname( __FILE__ )."/./database/DBManager.php" );
require_once( dirname( __FILE__ )."/DBHelper.php" );
require 'Spreadsheet/Excel/Writer.php';

session_start();
if ( session_id() == "" || !isset($_SESSION) || !isset($_SESSION['USERID']) )
{
	return;
}

if ( !isset($_GET["MIN_DATE"] ) || !isset($_GET["MAX_DATE"]) || !isset($_GET["ISUSER"]) )
{
	return;
}

$userId = $_SESSION['USERID'];

$minDate = $_GET["MIN_DATE"];
$maxDate = $_GET["MAX_DATE"];
$isUser = $_GET["ISUSER"];

$isUser = ($isUser == "true" );

if ( $isUser )
{
	$data = reportUserFeedback( $userId, $minDate, $maxDate);
}
else
{
	$data = reportReviewerFeedback( $userId, $minDate, $maxDate);
}

if ( count($data) == 0 )
	return;

$titles = array();
$titles["FEEDBACK_DATE"] = "Date";
$titles["COMMENTER_ID"] = "Commenter";
$titles["SUBJECT_ID"] = "Subject";
$titles["DISPOSITION"] = "Disposition";
$titles["COMMENT"] = "Comment";
$titles["SOLICITOR_ID"] = "Solicitor";
$titles["SOLICITATION_DATE"] = "Solicitation Date";
$titles["SOLICITATION_REASON"] = "Solicitation Reason";
$titles["CLOSED_DATE"] = "Closed";
$titles["FEEDBACK_ID"] = "ID";
$titles["REVIEWER_ID"] = "Reviewer";
$titles["STATUS"] = "Review Status";


$uidMap = $_SESSION['USERIDMAP'];

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
$workbook->send( ($isUser ? "user" : "reviewer")."_".$uidMap[$userId|0]."_report".$minDate."_".$maxDate.".xls");
$workbook->setVersion(8);

// Creating a worksheet
$worksheet = $workbook->addWorksheet( $minDate." to ".$maxDate);

$worksheet->hideGridLines();

$format_general = $workbook->addFormat();
$format_general->setBorder (1);
$format_general->setBorderColor('black');

$format_wrap = $workbook->addFormat();
$format_wrap->setTextWrap();
$format_wrap->setBorder (1);
$format_wrap->setBorderColor('black');

$format_title = $workbook->addFormat();
$format_title->setBold();
$format_title->setColor('yellow');
$format_title->setPattern(1);
$format_title->setFgColor('blue');
$format_title->setBorder (1);
$format_title->setBorderColor('black');
$format_title->setTextWrap();

$format_date = $workbook->addFormat();
$format_date->setNumFormat( "M/D/YYYY"); // h:mm:ss
$format_date->setBorder (1);
$format_date->setBorderColor('black');

$worksheet->write( 0, 0, ($isUser ? "User" : "Reviewer"). " Report Generated ".date(DATE_RFC2822)." from ".$minDate." to ".$maxDate." for ".$uidMap[$userId|0] );

$col  = 0;
$row = 2;
foreach ( $data[0] as $hdr => $value )
{
	$fmt = $format_title;
	
	if( $hdr == 'SOLICITOR_ID' || $hdr == 'COMMENTER_ID' || $hdr == 'SUBJECT_ID' || $hdr == 'REVIEWER_ID' )
	{
		$worksheet->setColumn( $col, $col, 16 );
	} 
	else if ( $hdr == 'DISPOSITION' || $hdr == 'STATUS' )
	{
		$worksheet->setColumn( $col, $col, 12 );
	}	
	else if ( $hdr == 'FEEDBACK_ID' )
	{
		$worksheet->setColumn( $col, $col, 5 );
	}	
	else if ( $hdr == 'SOLICITATION_DATE' || $hdr == 'FEEDBACK_DATE' || $hdr == 'CLOSED_DATE' )
	{
		$worksheet->setColumn( $col, $col, 12 );
	}
	else if ( $hdr == 'SOLICITATION_REASON' || $hdr == 'COMMENT' )
	{
		$worksheet->setColumn( $col, $col, 50 );
	}	
	else 
	{
		$worksheet->setColumn( $col, $col, strlen($hdr)*1.5 );
	}
	
	$title = isset( $titles[$hdr] ) ? $titles[$hdr] : $hdr;
	$worksheet->write($row, $col++, $title, $format_title);
}
$row++;

$seconds_in_a_day = 86400;
// Unix timestamp to Excel date difference in seconds
$ut_to_ed_diff = $seconds_in_a_day * 25569;

foreach ( $data as $line )
{
	$col = 0;
	foreach ( $line as $hdr => $value )
	{
		$fmt = $format_general;
		if( $hdr == 'SOLICITOR_ID' || $hdr == 'COMMENTER_ID' || $hdr == 'SUBJECT_ID' || $hdr == 'REVIEWER_ID' )
		{
			if ( $value == null )
			{
				$value = "";
			}
			else if ( isset( $uidMap[$value|0] ) )
				$value = $uidMap[$value|0];
			else
				$value = "##UNKNOWN##";
		}
		
		if ( $hdr == 'FEEDBACK_DATE' || $hdr == 'CLOSED_DATE' || $hdr == 'SOLICITATION_DATE' )
		{
			if ( $value != null )
			{
				$value = strtotime($value);
				$value = ($value + $ut_to_ed_diff)/$seconds_in_a_day;
			}
			else
			{
				$value = "";
			}
			$fmt = $format_date;
		}
		
		if ( $hdr == 'SOLICITATION_REASON' || $hdr == 'COMMENT' )
		{
			$fmt = $format_wrap;
		}	
		
		if ( $hdr == 'FEEDBACK_ID' )
			$value = $value | 0;
		
		if ( mb_strlen($value) > 32767 )
			$value = substr($value,0,32767);
		
		$worksheet->write($row, $col++, $value, $fmt );
	}
	$row++;
}

// Let's send the file
$workbook->close();
return;

?>