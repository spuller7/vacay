<?php require('auth.php');?><html>
	<head>
	<title>IT Infrastructure Status</title>
	<meta charset="UTF-8">
	
	<script src='/js/jquery-1.11.1.min.js'></script>
	<script src='/js/itCmds.js'></script>
	<link href='/css/base.css' rel='stylesheet' media='screen'/>	
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
		setInterval(function() { doUpdate(); }, 15000 );	
	});
	</script>
	</head>
<body>
<div class='BODYAREA'>

<?php
	$tab="INF";
	require('header.php');

require('mailmgr.php');

$results = getInfrastructureState();

ksort( $results );

echo "<table id='INFRA'>";

$cols = 1; floor(sqrt( count($results) ));
$rowId = 0;
$prevTitle = "";

foreach ( $results as $k => $v )
{
	$parts = explode( ".", $k );

	if ( $prevTitle != $parts[0] )
	{
		$prevTitle = $parts[0];
		echo "<tr><td class='INF_SECTION' colspan='3'>".$prevTitle."</td></tr>";
	}
	array_shift($parts);

	$title = implode(".", $parts );

	$total_hours = (time() - $v["when"])/3600;
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
		$age.=round($hours)." hours";	
	}
	$effState = $v["state"];
	$ageLimit = getInfAgeLimit( $k );
	$ageState = "";
	
	$ageIcon = "";
	
	$ageIcon = "<img src='/images/blank.png'/>&nbsp;";
	if ( $ageLimit != -1 && $total_hours > $ageLimit )
	{
		$ageIcon = "<img src='/images/red_claim.png' title='$ageLimit hour limit'/>&nbsp;";
	}
		
	echo "<tr id='ROW_".$rowId."' class='ROW_ALT".($rowId%2)."'  onclick='toggleRow(\"".addSlashes($v["id"])."\", ".$rowId."); return false;'>";	
	echo "<td class='INF_AGE ".$ageState."'>".$ageIcon.$age."</td>";
	$st = $v["state"];
	if ( isset( $stateToIcon[$st]))
		$res = "<img src='/images/".$stateToIcon[$st]."' title='$st'/>";
	else
	{
		$res = "<img src='/images/orange_exclaim.png' title='$st'/>";
		$title .= " -- <i>".$st."</i>";
	}
	//td_".$effState."
	echo "<td nowrap class='INF_NAME'>".$res."&nbsp;".$title."</td>";
	echo "</tr>";
	echo "<tr class='msgHidden ROW_ALT".($rowId%2)."' id='MSG_".$rowId."'><td class=' INF_MSG' colspan='2'><div id='MSG_".$rowId."_DIV'></div></td></tr>\n";	
	$rowId++;
}
echo "</table>";

?>
</div>
</body></html>

