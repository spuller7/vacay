<?php require('chkauth.php');
	require('config.php');?><html>
	<head>
	<title>IT Host/VM Status</title>
	<meta charset="UTF-8">
	
	<script src='js/jquery-1.11.1.min.js'></script>
	<script src='js/itCmds.js'></script>
	<link href='css/base.css' rel='stylesheet' media='screen'/>	
	<script>
	
	function openMsg(id) {
		embedMsg( id, "MSG_BOX" );
	} 	
	
	function doReload()
	{
		location.reload();
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
	</script>
	</head>
<body>
<div class='BODYAREA'>
<a href='viewBadge2.php'><h2>Table View</h2></a><br/><br/>

<?php

function getBadgeDB()
{
	$db = new PDO($cardreaderDbConnect, $cardreaderDbUser, $cardreaderDbPW);
	return $db;
}

function getData( $db )
{
	$stmt = $db->prepare('select scankey, generation, time, ID, InfoPayload, Status, BadgeID, FirstName, LastName from Scans where Time > date_sub(now(), interval 3 day) order by Time desc');
	$stmt->execute(array());
	return $stmt->fetchAll( PDO::FETCH_CLASS);
}

$tab="BADGE";
require('header.php');

$db = getBadgeDB();	

$results = getData($db);
	
echo "<table id='BadgeHistory' width='100%'>";
echo "<tr><th>Time</th><th>Status</th><th>Badge</th><th>Who</th><th></th></tr>";
$when = "";
$idx = 0;
$lastHr = "";
foreach ( $results as $result )
{
	$idx++;
	$dt = substr($result->time,0,10);
	if ( $when != $dt )
	{
		echo "<tr style='background: cyan;'><td style='text-align: center; border-top: 1px solid #CCCCCC;' colspan=5><big>".$dt."</big></td></tr>";
		$when = $dt;
		$lastHr = "";
	}
	$hr = substr($result->time,11,2);
	$border = "";
	if ( $hr != $lastHr )
	{
		$border = "border-top: 1px solid #CCCCCC;";
		$lastHr = $hr;
	}
	echo "<tr style='background: ".($idx % 2 == 0 ? 'white': '#F0F0F0').";'>";
	$tag = "";
	echo "<td style='text-align:center;".$border."'>".substr($result->time,11)."</td>";
	if ( $result->Status == 70 )
		$tag = "<span style='color: red'>Access Restricted</span>";
	else if ( $result->Status == 64 )
		$tag = "<span style='color: green'>Access Granted</span>";
	else if ( $result->Status == 58 )
		$tag = "<span style='color: blue'><b>Double Tap!</b></span>";
	else if ( $result->Status == 1 )
		$tag = "<span style='color: blue'><b>Device Startup</b></span>";
	else if ( $result->Status == 144 )
		$tag = "<span style='color: blue'><b>Host Connection Start</b></span>";
	else if ( $result->Status == 152 )
		$tag = "<span style='color: blue'><b>HAL Connected</b></span>";
	else if ( $result->Status == 130 )
		$tag = "<span style='color: blue'><b>Network Info Set</b></span>";
	else
		$tag = "Unknown";
	echo "<td style='".$border."'>".$result->Status.($tag != "" ? " - ".$tag : "")."</td>";
	echo "<td style='".$border."'>".$result->BadgeID."</td>";
	echo "<td style='".$border."'>";
	if ($result->LastName != "" )
		echo $result->LastName.", ".$result->FirstName;
	echo "</td>";
	echo "<td style='".$border."'>".$result->generation.":".$result->ID."</td>";
	echo "</tr>";
}
echo "</table>";

?>
</div>
</body></html>

