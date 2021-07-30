	<?php require('chkauth.php');
	require('config.php');
	?><html>
	<head>
	<title>IT Host/VM Status</title>
	<meta charset="UTF-8">
	
	<script src='js/jquery-1.11.1.min.js'></script>
	<script src='js/itCmds.js'></script>
	<link href='css/base.css' rel='stylesheet' media='screen'/>	
	<style>
		.BadgeList {
				font-weight: bold;
				font-size: 20px;
				padding: 5px 10px 5px 10px;
				text-align:center;
		}
		
		.BadgeBlink {
				font-weight: bold;
				font-size: 20px;
				padding: 5px 10px 5px 10px;
				text-align:center;
				animation:blinkingText 1.0s infinite;				
		}	
		
		@keyframes blinkingText{
			0%{     color: #000;    }
			49%{    color: #000; }
			50%{    color: transparent; }
			99%{    color:transparent;  }
			100%{   color: #000;    }
		}
		
		.EmptyBadge {
			background: lightgrey;
		}
	</style>
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

<?php

function getBadgeDB()
{
	$db = new PDO($cardreaderDbConnect, $cardreaderDbUser, $cardreaderDbPW);
	return $db;
}

function getData( $db )
{
	$stmt = $db->prepare('select distinct BadgeID, FirstName, LastName, min(Time) as Time from Scans where Date(Time) = date(now()) group by BadgeID, FirstName, LastName');
	$stmt->execute(array());
	return $stmt->fetchAll( PDO::FETCH_CLASS);
}

function getRecentUsers( $db )
{
	$stmt = $db->prepare('select distinct BadgeID, FirstName, LastName from Scans where Time > date_sub(now(), interval 30 day) and BadgeID<>"" and Status=64 order by LastName, FirstName, BadgeID');
	$stmt->execute(array());
	return $stmt->fetchAll( PDO::FETCH_CLASS);
}

$tab="BADGE";
require('header.php');

$db = getBadgeDB();

$users = getRecentUsers($db);

$todayUsers = getData($db);
$activeUsers = array();
foreach ( $todayUsers as $user )
{
	$activeUsers[$user->BadgeID] = $user;
}

$lastFirst = "";
$idx = 0;
$maxRow = 0;
foreach ( $users as $user  )
{
	$s = $user->LastName[0];
	if ( $lastFirst != $s )
	{
		$maxRow = max($maxRow, $idx);
		$lastFirst = $s;
		$idx = 0;
	}	
	$idx++;
}

$lastFirst = "";
$idx = 0;
echo "<table>";
$hour = date("H");
$min = date("m");
$id = 0;
foreach ( $users as $user  )
{
	$s = $user->LastName[0];
	if ( $lastFirst != $s )
	{
		if ( $lastFirst != "" )
		{
			for ( $i = $idx; $i < $maxRow; $i++ )
			{
				echo "<td class='EmptyBadge'>&nbsp;</td>";
			}
			echo "</tr>";
		}
		$lastFirst = $s;
		echo "<tr><th><big>".$s.":</big>&nbsp;&nbsp;&nbsp;&nbsp;</th>";
		$idx = 0;
	}
	
	$c = "#BBBBBB; color: #444444";
/*	if ( $hour > 11 )
		$c = "#AAFFFF";
	else if ($hour >= 10 )
	{
		if ( $min >= 15 )
			$c = "#FF0000";
		else
			$c = "#FFCCCC";
	}*/
	$label = $user->LastName.", ".$user->FirstName;	
	$clz = "BadgeList";
	$t = "";
	$alt = "No Data";
	if ( isset($activeUsers[$user->BadgeID]))
	{
		$c = "#BBBBBB";
		$when = $activeUsers[$user->BadgeID]->Time;
		$ft = strtotime($when);
		$min = date("i", $ft)*1.0;
		$min = floor(($min+14)/15)*15 - $min;
		$ft += $min*60;
		$dt  = substr($when,11,5);
		$alt = $dt;
		if ( $dt <= "10:00" )
		{
			$c = "#00FF00";
		}
		else if ( $dt <= "10:15" )
		{
			$c = "#FFCCCC";
		}
		else if ( $dt <= "11:00")
			$c = "#FF0000; color:white";
		else
			$c = "#00FFFF";
		
		$label .= " ~".date("h:i", $ft);
		if ( $ft > time() - 60*5 )
		{
			$label = "<big>".$label."</big>";
			$c = "#FF00FF";
		}
		if ( $ft > time() - 60 * 15 )
		{
			$clz = "BadgeBlink";
		}
	}
	
	
	echo "<td class='".$clz."' style='background: ".$c."' onmouseenter='$(this).html(\"$alt\");' onmouseleave='$(this).html(\"$label\");'>".$label."</td>";
	$id++;
	$idx++;
}
for ( $i = $idx; $i < $maxRow; $i++ )
{
	echo "<td class='EmptyBadge'>&nbsp;</td>";
}
echo "</tr>";

echo "</table>";
?>
<a href='viewBadge.php'><h2>List View</h2></a>
</div>
</body></html>

