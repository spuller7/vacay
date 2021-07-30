<?php require('auth.php');?><?php
require('mailmgr.php');

$results = getHostsState();

ksort( $results );

?>

<html>
	<head>
	<title>IT Hosts Status</title>
	<meta charset="UTF-8">
	
	<script src='js/jquery-1.11.1.min.js'></script>
	<script src='js/itCmds.js'></script>
	<link href='css/base.css' rel='stylesheet' media='screen'/>		
	
	<script>
	
	function doReload()
	{
		location.reload();
	}
	
	notifications.OnMessageUpdated = function(msgId)
	{
		openMsg(msgId);
	}
	
	notifications.OnMessageDeleted = function(msgId)
	{
		doReload();
	}	
		
	notifications.OnStatusChanged = function( msgId, host, key, state )
	{
		doReload();
	}

	var data = <?php echo json_encode($results); ?>;		
	</script>
	</head>
<body>
<?php
$tab='HOSTS';
require('header.php');
?>
<div class='BODYAREA'>

<table id='HOSTS'>
</table>

<script>

	function doUpdate()
	{
		if ( lastId === undefined )
			executeUpdate();
	}
	
	function AddNote( host, note )
	{
		if ( (n=prompt("Enter note",unescape(note))) != undefined )
		{
			SetHostNote(host,n);
		}
	}
	
	var requiredAttributes = ["LOGWATCH", "RKHUNTER", "AIDE" ];

	var stateToIcon = 
	{
	<?php
		$idx = 0;
		foreach ( $stateToIcon as $k => $v )
		{
			echo "\"$k\": { \"icon\":\"$v\", \"index\":$idx, \"key\":\"$k\"},";
			$idx++;
		}
	?>
	}
	
	var iconLookup = [];

	var minIdx;
	
	function add( aHost, row, bIsRequired )
	{
		var val="<div class='HOST_DIV td_NA'>&nbsp;</div>";
		var click = undefined;

		time = "";
		var extraClass = "";
		if ( aHost !== undefined )
		{
			var total_hours = ( new Date().getTime()/1000 - aHost.when)/3600;
			if ( total_hours > 24 * 2 )
			{
				click = " onclick='if ( event.ctrlKey ) { deleteMessage(\""+aHost.id+"\" ); event.stopPropagation();} else { multiToggleRow(\""+aHost.id+"\"," + row + ");  } return false;'";
			}
			else
			{
				click = " onclick='multiToggleRow(\""+aHost.id+"\"," + row + "); return false;'";
			}
			state = aHost.state;
			
			age = "";
			days = Math.floor(total_hours/24);
			if ( days > 0 )
				age += days+"d";
			var hours = total_hours - days*24;
			age += Math.round(hours)+"h";			
			var msg = "";
			if ( stateToIcon[aHost.state] != undefined )
			{
				msg = "<center><img width=24 height=24 src='/images/" + stateToIcon[aHost.state].icon+"'/></center>";
				if ( stateToIcon[aHost.state].index < minIdx )
					minIdx = stateToIcon[aHost.state].index;
				if ( aHost.state != "GOOD" )
					extraClass="td_"+aHost.state;
			}
			else
			{
				msg = "<img width=24 height=24 src='/images/blank.png'/>"
				msg += aHost.state;
			}
			
			val = msg;
			if ( total_hours > 24 * 2 )
				time = "( <span class='OLD_HOST'>&nbsp;" + age + "&nbsp;</span> )";
			else if ( total_hours > 24 )
			{
				time = "(" + age + ")";
			}
		}
		else if ( bIsRequired )
		{
			val="<div class='HOST_DIV td_MISSING'>???</div>";
		}
		var s = "<td class='HOST_CELL "+extraClass+"'" + (click !== undefined ? click : "") + ">";
		s  += val + time;
		s += "</td>";
		return s;
	}

	$( document ).ready( function() 
	{
		for ( k in stateToIcon )
		{
			iconLookup[stateToIcon[k].index] = stateToIcon[k];
		}
		executeUpdate();
		setInterval(function() { doUpdate(); }, 15000 );
	});

	function executeUpdate()
	{
		UpdateBanner();
		var hosts = $("#HOSTS");

		var allKeys = {};
		for ( hostName in data )
		{
			var aHost = data[hostName];
			for ( keyName in aHost )
			{
				allKeys[keyName] = true;
			}
		}
		delete allKeys["NOTE"];
		
		for ( var k in requiredAttributes )
		{
			delete allKeys[requiredAttributes[k]];
		}
			
		var keyList = [];
		for ( key in allKeys )
		{
			keyList.push(key);
		}
		keyList.sort();
		var s = "<tr><th style='ICONCELL'/><th class='HOST_CELL_HEADER'>Host</th>";
		for ( key in requiredAttributes )
		{
			s += "<th class='HOST_CELL_HEADER'><center>" + requiredAttributes[key] + "</center></th>";
		}
		
		for ( key in keyList )
		{
			s += "<th class='HOST_CELL_HEADER'><center>" + keyList[key] + "</center></th>";
		}
		s += "</tr>\n";
				
		var idx = 0;
		res = s;
		for ( hostName in data )
		{
			var aHost = data[hostName];
			var row = "";
			var obs = false;
			var val = "";
			if ( aHost["NOTE"] != undefined )
				val = escape(aHost["NOTE"].content);
			row += "<a class='HOST_LINK' href='#' onclick='AddNote(\"" + hostName + "\", \"" + val +"\"); return false;'>"+hostName;
//"+(aHost["NOTE"]==undefined?"\"\"":JSON.stringify(aHost["NOTE"].content))+");'>" + hostName;
			if ( aHost["NOTE"] != undefined )
			{
				row += "<img src='/images/tag_red.png' title=\""+aHost["NOTE"].content+"\"/>";
				if ( aHost["NOTE"].content == "OBSOLETE")
				{
					obs = true;
				}
			}
			row += "</a></td>";
			minIdx = iconLookup.length;
			for ( keyIdx in requiredAttributes )
			{
				var keyName = requiredAttributes[keyIdx];
				if ( obs )
					row += "<td></td>";
				else
					row += add( aHost[keyName], idx, true );
			}
			
			for ( keyIdx in keyList )
			{
				var keyName = keyList[keyIdx];
				if ( keyName == "NOTE" ) continue;
				if ( obs )
					row += "<td></td>";
				else
					row += add( aHost[keyName], idx, false );
			}
			var rowClass = "ROW_ALT"+(idx%2);
			var titleClass = "";
			if ( obs )
				rowClass = "td_OBSOLETE";
			var s = "<tr class='HOST_ROW " + rowClass + "' id='ROW_" + idx + "'>";
			s += "<td class='ICONCELL'>";
			if ( iconLookup[minIdx] != undefined )
				s += "<img width=24 height=24 src='/images/" + iconLookup[minIdx].icon+"'/>";
			s += "</td>";
			s += "<td class='HOST_TITLE " + titleClass + "'>";

			s += row + "</tr>";
			s += "<tr class='msgHidden "+rowClass+"' id='MSG_" + idx + "'><td colspan='"+(keyList.length+requiredAttributes.length+2)+"'><div id='MSG_"+idx+"_DIV'></div></td></tr>\n";				
			res += s;
			idx++;
		}
		$("#HOSTS").html(res);
	}
</script>
</div>
</body></html>
