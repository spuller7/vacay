<?php require('chkauth.php');?><html>
	<head>
	<title>SonicWall Firewall Syslog Viewer</title>
	<meta charset="UTF-8">
	
	<script src='js/jquery-1.11.1.min.js'></script>
	<script src='js/itCmds.js'></script>
	<link href='css/base.css' rel='stylesheet' media='screen'/>	
	
	
	<style>
		.fw_table { 
			border-collapse: collapse; 
		}
		.fw_table, .fw_td, .fw_th, .fw_cell {
			border: 1px solid black; 
		}
		
		.fw_cell {
			padding: 3px;
		}
		
		.fw_td {
			padding: 0px;
		}
		
		.attrTable { 
			padding: 3px;
		}
		.attrTable, .attrRow {
		}	
		
		.attrCell {
			border-left: 1px solid green; 
			border-right: 1px solid green; 
			border-top: 1px solid green; 
			border-bottom: 1px solid green; 
			font-family: courier;
			font-size: 12px;
			vertical-align: top;
			padding-left: 5px;
			padding-right: 5px;
		}
		
		.fw_msg {
			background: #EEEEFF;
			padding: 3px;
		}
		
		.attrHidden 
		{
			display: none;
		}
		
		.SELECTED_KEY
		{
			background: #80FF80;
		}
	</style>
	
	<script>

	
	function openMsg(id) {
		embedMsg( id, "MSG_BOX" );
	} 	
	
	function doReload()
	{
		//location.reload();
	}
	
	notifications.OnMessageUpdated = function(msgId)
	{
		openMsg(msgId);
	}

	notifications.OnInfChanged = function( msgId, label, state )
	{
		doReload();
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
		setInterval(function() { doUpdate(); }, 30000 );	
	});
	
	function toggleInfo(idx)
	{
		$("#attr_table_"+idx).toggle();
	}
	
	</script>
	</head>
<body>
<div class='BODYAREA'>

<?php
	$tab="FIREWALL";
	require('header.php');

require('mailmgr.php');

$keys = $redis->keys("IT.firewall.*");

sort($keys);

$selectedKey = "";
if ( isset($_GET["key"]) )
{
	$selectedKey = $_GET["key"];
}
else
{
	if (count($keys) > 0 )
		$selectedKey = $keys[count($keys)-1];
}

echo "<table><tr>";
foreach ( $keys as $key )
{
	echo "<td class='".($key == $selectedKey?'SELECTED_KEY':'')."'><a href='viewFirewall.php?key=".$key."'>".substr($key,12)."</a></td>";
}
echo "</tr></table>";

echo "<table class='fw_table' id='firewall' width='100%'>";

$idx = 0;
echo "<tr class='fw_tr'><th class='fw_th' colspan=6>".substr($selectedKey,12)."</th></tr>";
$values = $redis->lrange( $selectedKey, 0, -1 );
foreach ( $values as $v )
{
	$idx++;
	$r = json_decode($v );
	echo "<tr class='fw_tr'>";
	echo "<td class='fw_cell' nowrap>".substr($r->time,11)."</td>";
	echo "<td class='fw_cell'><center>";
	
	if ( $r->pri == 0 ) echo "EMERGENCY";
	else if ( $r->pri == 1 ) echo "ALERT";
	else if ( $r->pri == 2 ) echo "CRITICAL";
	else if ( $r->pri == 3 ) echo "ERROR";
	else if ( $r->pri == 4 ) echo "WARNING";
	else if ( $r->pri == 5 ) echo "NOTICE";
	else if ( $r->pri == 6 ) echo "INFORM";
	else if ( $r->pri == 7 ) echo "DEBUG";
	else echo $r->pri;
	
	echo "</center></td>";
	echo "<td class='fw_cell'>".$r->c."</td>";
	echo "<td class='fw_cell'>".$r->m."</td>";
	echo "<td class='fw_td' width='100%'><div onclick='toggleInfo(\"".$idx."\");' class='fw_msg'>".$r->msg."</div>";
	
	echo "<table id='attr_table_".$idx."' class='attrTable attrHidden'>";
	echo "<tr class='attrRow'>";
	$res = array();
	foreach ( $r as $rk => $rv )
	{
		if ( $rv == "" || $rk == "id" || $rk == "sn" || $rk == "time" || 
			$rk == "fw" || $rk == "pri" || $rk == "c" || $rk == "m" || 
			$rk == "msg" || $rk == "fw_action" )
			continue;
			
		$res[$rk] = $rv;
	}
	foreach ( $res as $rk => $rv )
	{
		echo "<td class='attrCell'><u>".$rk."</u><br>".$rv."</td>";
	}
	echo "</tr>";
	echo "</table>";
	
	echo "</td>";
	echo "</tr>";
}
echo "</table>";

?>
</div>
</body></html>

