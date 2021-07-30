<?php require('auth.php');?><html>
	<head>
	<title>IT Net Status</title>
	<meta charset="UTF-8">
	
	<script src='js/jquery-1.11.1.min.js'></script>
	<script src='js/itCmds.js'></script>
	<link href='css/base.css' rel='stylesheet' media='screen'/>	
	<script>
	
	function doReload()
	{
		location.reload();
	}
	
	$( document ).ready( function() {
		UpdateBanner();
//		setInterval(function() { doUpdate(); }, 15000 );	
	});
	</script>
	</head>
<body>
<div class='BODYAREA'>

<?php
	$tab="NET";
	require('header.php');
require('mailmgr.php');

$d = file_get_contents("../../eg/net/pingStatus.json");
$vals = json_decode($d);


$results = getActiveSubnets();

echo "<table border=1>";
echo "<tr><th/>";
foreach ( $results as $subnet )
{
	echo "<th>".$subnet.".x</th>";
}
echo "</tr>";

for ( $lastOct = 1; $lastOct < 255; $lastOct++ )
{
	$bFoundOne = false;
	$res = "<tr>";
	$res .= "<th>".$lastOct."</th>";
	foreach ( $results as $subnet )
	{
		$key = $subnet.".".$lastOct;
		$hostName = gethostbyaddr($key);
		$strKey = "";
		$isAlive = false;
		$reallyOld = false;
		if ( isset( $vals->$key ) )
		{
			$val = $vals->$key;
			if ( !isset($val->LastAlive) || $val->LastAlive == 0 )
				$strKey = "";
			else
			{
				$minutes = round((time() - $val->LastAlive/1000)/60);
				$reallyOld = $minutes > (60*24*90); //90 days is really old
				if ( $minutes > 60*24  )
					$lastAlive = round($minutes/(60*24))."d";
				else if ( $minutes > 60 )
					$lastAlive = round($minutes/60)."h";
				else
					$lastAlive = $minutes."m";
				$strKey = ($val->Alive?"Online":"Offline")."(".$lastAlive.")";
				$isAlive = $val->Alive;
			}
		}
		$strHost = "";
		if ( isset( $vals->$key ) )
		{
			$val = $vals->$key;
			$host = isset( $val->Host ) ? $val->Host : "";
			$stem = ".quantumsignal.com";
			if ( substr($host, -strlen($stem) ) == $stem )
			{
				$host = substr($host,0,strlen($host)-strlen($stem));
			}
			$strHost = $host;
		}
		
		$icon = "blank.png";
		$clazz = "TD_HOST_INFO";
		if ( $strKey == "" )
		{
			if ( $strHost != "" )
			{
				$icon = "red_claim.png";
			}
			else
			{
				$clazz = "TD_HOST_NO_INFO";
			}
		}
		else
		{
			if ( $isAlive )
			{
				$icon = "green_check.png";
			}
			else
			{
				if ( $reallyOld )
				{
					$clazz = "TD_HOST_NO_INFO";
				}
				else
				{
					$icon = "orange_exclaim.png";
				}
			}
		}
		$strMAC = "";
		if ( isset( $vals->$key ) )
		{
			$val = $vals->$key;
			$strMAC = isset( $val->MAC ) ? $val->MAC : "";
		}

		if ( $strKey != "" || $strHost != "" )
			$bFoundOne = true;
		$res .= "<td class='".$clazz."'>";
		$res .= "<table><tr><td><img src='/images/$icon'/></td><td>".($strKey?$strKey."<br/>":"").($strHost?$strHost:"").($strMAC?($strHost?"<br/>":"")."MAC: ".$strMAC:"")."</td></tr></table>";
		$res .= "</td>";
	}
	$res .= "</tr>\n";
	if ( $bFoundOne )
		echo $res;
}

echo "</table>";

?>
</div>
</body></html>

