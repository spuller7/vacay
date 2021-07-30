<?php require('auth.php');?><html>
	<head>
	<title>IT Alarms</title>
	<meta charset="UTF-8">
	
	<script src='js/jquery-1.11.1.min.js'></script>
	<script src='js/itCmds.js'></script>
	<link href='css/base.css' rel='stylesheet' media='screen'/>		
	<style>
	</style>
	<script>
	
	var lastId = undefined;
	
	function showMsgRow( id, rowId )
	{
		if ( lastId != undefined )
		{
			hideMsgRow( lastId );
		}
		
		var t = $("#ROW_"+rowId);
		if ( t.hasClass("tr_UNREAD") )		
		{
			t.removeClass( "tr_UNREAD" );
			t.addClass( "tr_READ" );
		}
		
		var r = $("#MSG_"+rowId);
		r.removeClass("msgHidden");
		r.addClass("msgVisible");
		embedMsg( id, "MSG_" + rowId + "_DIV" );			
		lastId = rowId;
	}
	
	function hideMsgRow( rowId )
	{
		var t = $("#MSG_"+rowId);
		var d = $("#MSG_"+rowId+"_DIV");

		t.removeClass("msgVisible");
		t.addClass("msgHidden");
		d.html("");
		lastId = undefined;
	}
	
	function toggleRow(id, rowId) 
	{
		var t = $("#MSG_"+rowId);
		if ( t.hasClass("msgHidden") )
		{
			showMsgRow( id, rowId );
		}
		else
		{
			hideMsgRow( rowId );
		}
	} 	
	
	function doReload()
	{
		location.reload();
	}
	
	notifications.OnAckAlarm = function( msgId, msg, sweep )
	{
		if ( sweep != undefined && sweep == true )
			doReload();
		else
		{
			$("#ROW_"+lastId).remove();
			hideMsgRow(lastId);
		}
	}
	
	notifications.OnMessageDeleted = function(msgId)
	{
		doReload();
	}	
	
	function doUpdate()
	{
		if ( lastId === undefined )
			doReload();
	}
		
	$( document ).ready( function() {
		UpdateBanner();
		setInterval(function() { doUpdate(); }, 15000 );	
	})
		
	</script>	
	</head>
<body>
<div class='BODYAREA'>

<?php
$tab="ALARMS";
require('header.php');
require('mailmgr.php');

$results = getAlarms();

$msgs = retrieveMsgs( $results );

sortMsgs( $msgs );

echo "<table id='ALARMS'>";
echo "<tr><th>Date</th><th>Subject</th><th>Sender</th></tr>";
$rowId = 0;
foreach ( $msgs as $msg )
{

	$id = $msg["id"];
	$hdrs = $msg["headers"];

	$bRead = (isset( $msg["READ"] ) && $msg["READ"] == 1 );
	echo "<tr id='ROW_".$rowId."' class='ALARM_ROW ".($bRead ? "tr_READ" : "tr_UNREAD")."' onclick='toggleRow(\"".addslashes($id)."\",".$rowId."); return false;'>";
	echo "<td nowrap class='ALARM_CELL'>";
	$age = (time() - $hdrs->udate)/3600/24;
	if ( $age > 1.0 )
	{
		echo date("Y-m-d", $hdrs->udate )." -".floor($age)."d";
	}
	else
	{
		echo date(DATE_ATOM,$hdrs->udate);
	}
	echo "</td>";
	if ( isset( $hdrs->subject ) )
		$subj = $hdrs->subject;
	else
		$subj = "&nbsp;";
	echo "<td class='ALARM_CELL'>".htmlentities(iconv_mime_decode($subj))."</td>";
	if( isset($hdrs->senderaddress) ) 
		$sender = $hdrs->senderaddress;
	else
		$sender = "&nbsp;";
	echo "<td class='ALARM_CELL'>".$sender."</td>";
	echo "</tr>\n";
	echo "<tr class='msgHidden' id='MSG_".$rowId."'><td colspan='4'><div id='MSG_".$rowId."_DIV'></div></td></tr>\n";
	$rowId++;
}
echo "</table>";
?>
</div>
</body></html>
