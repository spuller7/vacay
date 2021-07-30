<?php require('chkauth.php');?><html>
	<head>
	<title>IT Badge Viewer</title>
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

<?php
	$tab="Badge";
	require('header.php');

require('mailmgr.php');

$results = getVMHosts();
ksort( $results );

function getAge( $when )
{
	$total_hours = (time() - $when)/3600;
	$hours = $total_hours;
	$age = "";
	if ( $hours >= 24 )
	{
		$days = floor($hours/24);
		$hours = round($hours - floor($hours/24)*24);
		if ( $days > 0 )
		{
			if ( $hours > 4 )
				$age = $days."+ days";
			else
				$age = $days." days";
		}
		else
		{
			$age = $days."d";
			if ( $hours > 0 )
				$age .= " ".round($hours)."h";	
		}
	}
	else
	{
		$age.=round($hours)."h";	
	}
	return $age;
}

echo "<table id='VMHOST' width='100%'>";
echo "<tr><td colspan=3><h1>Hosts</h1></td></tr>";
echo "<tr><th>Host</th><th>CPU</th><th>Memory</th></tr>";
$rowId = 0;
foreach ( $results as $k => $v )
{
	echo "<tr class='ROW_ALT".($rowId%2)."'>";
	
	echo "<td>".$k."</td>";

	$cpu = isset( $v["CPU"]) ? $v["CPU"] : null;
	if ( $cpu == null )
	{
		echo "<td>";
	 	echo "N/A";
		echo "</td>";
	}
	else
	{
		echo "<td style='text-align: center; background-color:".$cpu["state"]."' onclick='toggleRow(\"".addSlashes($cpu["id"])."\", ".$rowId."); return false;'>";
		echo getAge($cpu["when"]);
		echo "</td>";
	}
	$memory = isset( $v["Memory"]) ? $v["Memory"] : null;
	if ( $memory == null )
	{
		echo "<td>";
		echo "N/A";
		echo "</td>";
	}
	else
	{
		echo "<td style='text-align: center; background-color:".$memory["state"]."' onclick='toggleRow(\"".addSlashes($memory["id"])."\", ".$rowId."); return false;'>";
		echo getAge($memory["when"]);
		echo "</td>";
	}
	echo "<tr class='msgHidden ROW_ALT".($rowId%2)."' id='MSG_".$rowId."'><td class=' INF_MSG' colspan='3'><div id='MSG_".$rowId."_DIV'></div></td></tr>\n";	
	$rowId++;
}

$results = getVMs();
ksort( $results );

echo "<tr><td colspan=3><h1>VMs</h1></td></tr>";
echo "<tr><th>VM</th><th>CPU</th><th>Memory</th></tr>";
foreach ( $results as $k => $v )
{
	echo "<tr class='ROW_ALT".($rowId%2)."'>";
	
	echo "<td>".$k."</td>";

	$cpu = isset( $v["CPU"]) ? $v["CPU"] : null;
	if ( $cpu == null )
	{
		echo "<td>";
	 	echo "N/A";
		echo "</td>";
	}
	else
	{
		echo "<td style='text-align: center; background-color:".$cpu["state"]."' onclick='toggleRow(\"".addSlashes($cpu["id"])."\", ".$rowId."); return false;'>";
		echo getAge($cpu["when"]);
		echo "</td>";
	}
	$memory = isset( $v["Memory"]) ? $v["Memory"] : null;
	if ( $memory == null )
	{
		echo "<td>";
		echo "N/A";
		echo "</td>";
	}
	else
	{
		echo "<td style='text-align: center; background-color:".$memory["state"]."' onclick='toggleRow(\"".addSlashes($memory["id"])."\", ".$rowId."); return false;'>";
		echo getAge($memory["when"]);
		echo "</td>";
	}
	echo "<tr class='msgHidden ROW_ALT".($rowId%2)."' id='MSG_".$rowId."'><td class=' INF_MSG' colspan='3'><div id='MSG_".$rowId."_DIV'></div></td></tr>\n";	
	$rowId++;
}
echo "</table>";

?>
</div>
</body></html>

