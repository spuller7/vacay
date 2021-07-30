<?php require('auth.php');?><html>
	<head>
	<title>IT Net Status</title>
	<meta charset="UTF-8">
	
	<script src='js/jquery-1.11.1.min.js'></script>
	<script src='js/itCmds.js'></script>
	<link href='css/base.css' rel='stylesheet' media='screen'/>	
	<style>
		.MacTitle { font-size: 16px; }
		.LabelDescription { font-size: 12px; text-decoration: none; }
		.LabelDescription:hover { text-decoration: none; }
		.MacHost { vertical-align:middle; font-size: 12px;  }
		.MacIP { vertical-align:middle; font-size: 16px;  }
		.hdrLink { font-size: 16px; }
	</style>
	
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
<a class='hdrLink' href='viewNet2.php'>Net Info</a>&nbsp;&nbsp;&nbsp;
<a class='hdrLink' href='MacInfo.php'>Mac Info</a>&nbsp;&nbsp;&nbsp;
<span class='hdrLink' href='SwitchView.php'>Switch View</span>
<br/>
<input type='button' onclick='doRefresh();' id='DoRefresh' name='DoRefresh' value='Refresh'/>
<br/>
<?php
	$tab="NET";
	require('header.php');
require('mailmgr.php');

echo "<table border=0 style='border-collapse:collapse'>";
$start = microtime(true);
for ( $j = 0; $j < 4; $j ++ )
{
	$row1 = "";
	$row1a = "";
	$row2 = "";
	$row2a = "";
	for ( $t = 1; $t <= 48; $t++ )
	{
		$i = $j*50+$t;
		$html = "<td width='4%' style='background: #0000FF; padding: 2px;'><div id='port".$i."'  style='background: grey; overflow: hidden;'><small><center>&nbsp;&nbsp;&nbsp;???&nbsp;&nbsp;&nbsp;<br/>???</center></small></div></td>";
		$html2 = "<td id='PortLabel".($i)."' style='background: #0000FF; color: white;'><center onclick='getPortInfo(".$i.",".$j.");' ><b>".$t."</b></center></td>";
		
		if( $t % 2 == 1 )
		{
			$row1 .= $html;
			$row1a .= $html2;
		}
		else
		{
			$row2 .= $html;
			$row2a .= $html2;
		}
	}
	echo "<tr style='margin: 0px; background: blue'><td rowspan=4 style='padding: 5px; vertical-align:middle; color:#00FF00'><center><big>".($j+1)."</big></center></td>".$row1."</tr>";
	echo "<tr>".$row1a."</tr>";
	echo "<tr>".$row2."</tr>";
	echo "<tr>".$row2a."</tr>";
	echo "<tr><td colspan=49 id='space".$j."'>&nbsp;</td></tr>";
}
echo "</table>";
?>
</div>
<script>

function updateAllPorts()
{
	for ( j = 0; j < 4; j ++ )
	{
		for ( i = 1; i <= 48; i ++ )
		{
			var port = j*50+i;
			$("#port"+port).html("&nbsp;");
			
			switchInfo(port);
		}
	}
}

function doRefresh()
{
	$.ajax({
		type: "POST",
		url: 'switch_mac.php',
		dataType: 'text',
		data: "{}",
		cache: false,
		async: false,
		success: function(data){
			updateAllPorts();
		},
		error: function(xhr,txt, error) {
			updateAllPorts();
		}
	});
}
function EditAlias( port, alias )
{
	alias = decodeURIComponent(alias);
	if ( (alias=prompt("New Alias for port " + port, alias )) != null )
	{
		setPortAlias( port, alias );
		switchInfo(port);
	}
}
function updatePort( port, payload )
{
	var elem = "#port" + port;
	var portLabel = "#PortLabel" + port;
	var html = "<small><center>";

	var color = "#F0F0F0";
	var portLabelColor = "#FFFFFF";
	if ( payload.status == 1 )
	{
		if ( payload.speed == 100 )
			color = "#FFBF8E";
		else
			color = "#CCFFCC";
		if ( payload.alias == "" )
			portLabelColor = "#FF0000";
	}
	else if ( payload.status == 2 )
	{
		color = "#CCCCCC";
	}
	$(portLabel).css("color", portLabelColor);
	
	$(elem).css("background", color );
	
	html += "<a class='LabelDescription' href='#' onclick='EditAlias("+port+", \"" + encodeURIComponent(payload.alias)+"\"); return false;'>";
	if ( payload.alias != "" )
		html += payload.alias;
	else
		html += "-";
	html += "</a>";
	
	html += "<br/>";
	html += ""+payload.vlan + "";
		
	html += "</small></center>";
	$(elem).html(html);
}

var portInfo= [0,0,0,0];

function getPortInfo( port, space )
{
	if( portInfo[space] == port )
	{
		$("#space"+space).html("&nbsp;");
		portInfo[space] = 0;
		return;
	}
	portInfo[space] = port;
	
	info = getPortMapping(port);
	html = "<h3>Port " + (port%50) + " on switch " + (space+1)+"</h3>";
	for ( portIdx in info.macs )
	{
		portData = info.macs[portIdx];
		html += portData.mac + " - " + portData.description + "<br/>";
	}
	$("#space"+space).html(html);
}


var portUpdateList = [];

notifications.OnSwitchInfo = function( port, payload )
{
	updatePort(port, payload );
	if ( portUpdateList.length > 0 )
		switchInfo(portUpdateList.pop());
}

updateAllPorts();
//switchInfo(portUpdateList.pop());

</script>

</body></html>
