<?php require('auth.php');?><html>
	<head>
	<title>IT Net Status</title>
	<meta charset="UTF-8">
	
	<script src='/js/jquery-1.11.1.min.js'></script>
	<script src='/js/itCmds.js'></script>
	<link href='/css/base.css' rel='stylesheet' media='screen'/>	
	<style>
		.MacTitle { font-size: 16px; }
		.MacDescription { font-size: 12px; text-decoration: none; }
		.MacDescription:hover { text-decoration: none; }
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
	
	var openFooters = [];
	</script>
	</head>
<body>
<div class='BODYAREA'>
<a class='hdrLink' href='viewNet2.php'>Net Info</a>&nbsp;&nbsp;&nbsp;
<span class='hdrLink' href='MacInfo.php'>Mac Info</span>&nbsp;&nbsp;&nbsp;
<a class='hdrLink' href='SwitchView.php'>Switch View</a><br/><br/>
<?php
$tab="NET";
require('header.php');
require('mailmgr.php');

import_request_variables("g", "rvar_");

if ( !isset($rvar_needle) )
	$rvar_needle = "";

echo "<form id='myform'><input type=text name='needle' id='needle' onchange='myform.submit();' value='".$rvar_needle."'/></form>";


$conn = ldap_connect("ldap.mgmt.quantumsignal.com") or die("Could not connect to server");

ldap_start_tls($conn) or die("Could not StartTLS on LDAP server");
$r = ldap_bind($conn, "cn=Manager,dc=ldap,dc=internal,dc=quantumsignal,dc=com", "doofus") or die("Could not bind to server");

$dn = "cn=DHCP Config,ou=dhcp,dc=ldap,dc=internal,dc=quantumsignal,dc=com"; //the object itself instead of the top search level as in ldap_search

$justthese = array("cn", "dhcpHWAddress", "dhcpStatements"); //the attributes to pull, which is much more efficient than pulling all attributes if you don't do this
$result = ldap_search($conn,$dn, "(objectClass=dhcpHost)", $justthese) or die ("Error in search query: ".ldap_error($ldapconn));
$data = ldap_get_entries($conn, $result);
$lookup = array();
foreach ( $data as $item )
{
	if ( !is_array($item) ) continue;
	$cn = $item["cn"][0];
	$dhcpHWAddress = $item["dhcphwaddress"][0];
	$parts = explode(" ",$dhcpHWAddress);
	$mac = strtolower($parts[1]);
	$statements = $item["dhcpstatements"];
	$lookup[$mac] = $cn;
}

ldap_close($conn);

$macMap = array();
$values = $redis->keys("IT.macregistrar.*");
foreach ( $values as $value )
{
	$mac = substr($value,strlen("IT.macregistrar."));
	if ( !isset($macMap[$mac]) )
	{
		$macMap[$mac] = new stdclass();
		$macMap[$mac]->mac = $mac;
		$macMap[$mac]->state = $redis->hget($value, "state");
		$macMap[$mac]->description = $redis->hget($value, "description");
		$macMap[$mac]->hosts = array();
		$macMap[$mac]->port =  $redis->hget($value, "port");
		if ( $macMap[$mac]->port == false )
			$macMap[$mac]->port = "";
		$macMap[$mac]->portID =  $redis->hget($value, "portID");
		$macMap[$mac]->alias = $redis->hget( "IT.portname.".$macMap[$mac]->portID, "name" );
		if ( $macMap[$mac]->alias == false )
			$macMap[$mac]->alias = "";
		$macMap[$mac]->dhcp = (isset($lookup[$mac]) ? $lookup[$mac] : "<b>unspecified</b>");
		
		$lastSeen = $redis->hget($value, "lastSeen");
		if ( $lastSeen == "" )
		{
			$macMap[$mac]->lastSeen = -1;
			$macMap[$mac]->MacTimeString = "???";
		}
		else
		{
			$macMap[$mac]->lastSeen = (int)GetElapsedMinutes($lastSeen);
			$macMap[$mac]->MacTimeString = GetTimeString($macMap[$mac]->lastSeen);
		}
		
		$nmapKey = "IT.nmap.status.".$mac;
		$macMap[$mac]->nmap_status = $redis->hget($nmapKey, "status");
		$macMap[$mac]->nmap_address = $redis->hget($nmapKey, "address");
		$macMap[$mac]->nmap_vendor = $redis->hget($nmapKey, "vendor");
		if ( $macMap[$mac]->nmap_vendor === false )
			$macMap[$mac]->nmap_vendor  = "";
		$macMap[$mac]->nmap_when = $redis->hget($nmapKey, "when");
		$nmapKey = "IT.nmap.ports.".$mac;
		$macMap[$mac]->nmap_ports = $redis->smembers($nmapKey);
		if ( $macMap[$mac]->nmap_ports == false )
			$macMap[$mac]->nmap_ports = array();
	}
}
$values = $redis->keys("IT.pings.*");
foreach ( $values as $value )
{
	$ip = substr($value,strlen("IT.pings."));
	$mac = $redis->hget($value, "mac");
	if ( $mac != "" )
	{
		$obj = new stdclass();
		$hostName = $redis->hget($value, "hostname");
		$obj->hostName = $hostName;
		$obj->ip = $ip;
		$lastAlive = $redis->hget($value, "lastMac");
		if ( $lastAlive == null )
		{
			$obj->age = -1;
			$obj->TimeString = "No Mac";
		}
		else
		{
			$obj->age = (int)GetElapsedMinutes($lastAlive);
			$obj->TimeString = GetTimeString($obj->age);
		}
		$obj->ActiveMac = ( $obj->age >= 0 && $obj->age < 15 );
		
		$lastPing  = $redis->hget($value, "lastAlive");
		if ( $lastPing != null )
		{
			$obj->pingAge = (int)GetElapsedMinutes($lastPing);
		}
		else
		{
			$obj->pingAge = -1;
		}
		$obj->ActivePing = ( $obj->pingAge >= 0 && $obj->pingAge < 30 );
		
/*		if ( $minutes > 30 )
		{
			$ts = GetTimeString($minutes);
			$host = "<span style='color:gray'>".$host."[".$ts."]</span>";
		}		
*/		
		if ( !isset($macMap[$mac]) )
		{
			echo "MISSING MAC:".$mac."<br/>";
		}
		$macMap[$mac]->hosts[] =$obj;
	}
}

function EvalMac( $a )
{
	$hostCount = count($a->hosts);
	
	$activeMac = false;
	$activePing = false;
	foreach ( $a->hosts as $host )
	{
		$activeMac |= $host->ActiveMac;
		$activePing |= $host->ActivePing;
	}
	
	$score = 0;
	
	if ( $a->state == "APPROVED" )
		$score |= 64;
	else if ( $a->state != "UNAPPROVED" )
		$score |= 32;
	
	if ( $hostCount == 0 && $score != 32 )
		$score |= 256;

	 if ( !$activeMac )
		$score |= 16;

	if ( $activePing )
		$score |= 8;

	return $score;
}


function cmp( $a, $b )
{
	$ea = EvalMac($a);
	$eb = EvalMac($b);
	if ( $ea < $eb )
		return -1;
	else if ( $ea > $eb )
		return 1;
	else
		return strcmp($a->mac, $b->mac);
}

foreach ( $macMap as $map )
{
	$map->ev = EvalMac($map);
}

uasort($macMap, 'cmp');

?>
<div id='macTable'>
</div>
<script>
<?php echo "macMap=".json_encode($macMap).";"; ?>
<?php echo "needle=".json_encode($rvar_needle).";"; ?>

function DoGetPortDescription( mac, port, footer )
{
	if ( openFooters[mac] == 1 )
	{
		$(footer).html("");
		openFooters[mac] = 0;
	}
	else
	{
		info = getPortMapping(port);
		html = "";
		for ( portIdx in info.macs )
		{
			portData = info.macs[portIdx];
			html += portData.mac + " - " + portData.description + "<br/>";
		}
		$(footer).html(html);
		openFooters[mac] = 1;
	}
}

function DoGetPortNMAP( mac, footer )
{
	var data = macMap[mac];
	if ( openFooters[mac] == 2 )
	{
		$(footer).html("");
		openFooters[mac] = 0;
	}
	else
	{
		info = data.nmap_ports
		html = "";
		for ( x in info )
		{
			port = info[x];
			html += port + "<br/>";
		}
		$(footer).html(html);
		openFooters[mac] = 2;
	}
}

notifications.OnMacChanged = function( macID, approvedState, description )
{
	macMap[macID].state = approvedState;
	macMap[macID].description = description;
	updateMac(macID);
}

notifications.OnMacIPRemoved = function( macID, IP )
{
	for ( var i = 0; i < macMap[macID].hosts.length; i ++ )
	{
		if ( macMap[macID].hosts[i].ip == IP )
		{
			macMap[macID].hosts.splice(i,1);
			break;
		}
	}
	updateMac(macID);
}

function DoToggleMac( mac )
{
	var data = macMap[mac];	
	if ( data.state == "UNAPPROVED" )
		SetMAC( mac, data.description, "APPROVED" );
	else if ( data.state == "APPROVED" )
		SetMAC( mac, data.description, "PENDING" );
	else
		SetMAC( mac, data.description, "UNAPPROVED" );
}

function DoSetDescription( mac )
{
	var data = macMap[mac];	
	
	var reason = prompt( "Enter reason", data.description );
	if ( reason != null )
	{
		SetMAC( mac, reason, data.state );
	}
}

function UpdateSearch()
{
	needle = $("#search").val();
	console.log(needle);
	for ( var mac in macMap )
	{
		console.log(mac);
		var data = macMap[mac];
		var outer = "#" + "MAC_"+mac.replace(/:/g,"_") + "_OUTER";
		bFound = (mac.indexOf(needle) != -1) || (data.alias.indexOf(needle) != -1) || (data.vendor && data.vendor.indexOf(needle) != -1 );
		bFound |= (data.description.indexOf(needle) != -1 );
		if ( data.hosts.length > 0 && !bFound )
		{
			for ( var host in data.hosts )
			{
				hostname = data.hosts[host].hostName;
				if ( hostname )
					bFound |= (hostname.indexOf(needle) != -1);
				ip = data.hosts[host].ip;
				if ( ip )
					bFound |= (ip.indexOf(needle) != -1);
				
				if ( bFound )
					break;
			}
		}
		if ( bFound )
		{
			$(outer).css("display","flex");
			
		}
		else
		{
			$(outer).css("display", "none");
		}
	}	
}

function updateMac(mac)
{
	var data = macMap[mac];
	var outer = "#" + "MAC_"+mac.replace(/:/g,"_") + "_OUTER";
	var title = "#" + "MAC_"+mac.replace(/:/g,"_") + "_TITLE";
	var body = "#" + "MAC_"+mac.replace(/:/g,"_") + "_BODY";
	var footer = "#" + "MAC_"+mac.replace(/:/g,"_") + "_FOOTER";
	var breakbar  = "#" + "MAC_"+mac.replace(/:/g,"_") + "_BREAKBAR";
	
	var title_payload = "";
	var body_payload = "";
	var footer_payload = "";
	var breakbar_payload = "";
	
	var approved = data.state == "APPROVED";
	if ( approved )
	{
		$(title).css("background-color", "#CCFFCC");
	}
	else if ( data.hosts.length == 0 )
		$(title).css("background-color", "#CCCCCC");
	else if ( data.state == "UNAPPROVED" )
		$(title).css("background-color", "#FFCCCC");
	else
		$(title).css("background-color", "#FFFFCC");
	
	title_payload += "<table><tr>";
	title_payload += "<td>";
	title_payload += "<a href=''";
	title_payload += " onclick=\"DoToggleMac('" + mac + "'); return false;\" ";
	title_payload += "><img src='../images/";
	if ( approved )
		title_payload += "<?php echo $stateToIcon["GOOD"];?>";
	else if ( data.state == "UNAPPROVED" )
		title_payload += "<?php echo $stateToIcon["BAD"];?>";
	else
		title_payload += "<?php echo $stateToIcon["PENDING"];?>";		
	title_payload += "'/></a>";
	title_payload += "</td><td>";
	title_payload += "<span class='MacTitle'>"+mac;
	if ( data.lastSeen > 30 || data.lastSeen < 0 )
	{
		title_payload += " ("+data.MacTimeString+")";
	}
	if ( data.port != "" || data.alias != "" || data.dhcp != "" )
	{
		$(breakbar).css("background-color", "#FCFCFC");
		$(breakbar).css("border-bottom", "1px solid black");
		breakbar_payload += "<a class='MacDescription' href='#' onclick=\"DoGetPortDescription( '"+mac+"', " + data.portID + ", '" + footer +"' ); return false;\"><small>"+(data.port != ""? data.alias+" ("+data.port+")":"<b>no traffic</b>") + " - " + data.dhcp + "</small></a>";

		if ( data.nmap_vendor != "" || data.nmap_ports.length > 0 )
		{
			vendor = data.nmap_vendor ;
			if( vendor == "" )
				vendor = "<b>unknown</b>";
			breakbar_payload += "<a class='MacDescription' href='#' onclick=\"DoGetPortNMAP('" + mac + "', '" + footer +"' ); return false;\">";
			breakbar_payload +="<br/><small>"+vendor;
			if ( data.nmap_ports.length > 0 )
				breakbar_payload += " (" + data.nmap_ports.length + " ports)";
			breakbar_payload += "</a></small>";
		}
	}
	
	title_payload += "</span>";
	title_payload += "<br/>";
	title_payload += "<a class='MacDescription' href='#' onclick=\"DoSetDescription('" + mac + "'); return false;\"><small>" + (data.description==""?"?":data.description) + "</small></a>";
	title_payload += "</td></tr></table>";

	body_payload += "<table>";
	hasOld = false;
	if ( data.hosts.length > 0 )
	{
		for ( var host in data.hosts )
		{
			hostname = data.hosts[host].hostName;
			ip = data.hosts[host].ip;
			age = data.hosts[host].age;
			pingAge  = data.hosts[host].pingAge;
			body_payload += "<tr>";
			body_payload += "<td>";
			
			if ( !data.hosts[host].ActiveMac )
			{
				body_payload += "<a href='#' onclick=\"if ( confirm('are you sure?') ) removeIP( '"+mac+"','"+ip+"'); return false;\"><img src='/images/trash.png'/></a>";				
			}
			else
			if ( data.hosts[host].ActivePing )
			{
				body_payload += "<img src='/images/star_100.png'/>";
			}
			else
			{
				body_payload += "<img src='/images/star_00.png'/>";
			}
			body_payload += "</td>";
			
			if ( hostname )
				body_payload += "<td class='MacHost'>"+ip + "<br/>" + hostname;
			else
				body_payload += "<td class='MacIP'>"+ip;
			if ( age < 0 || age > 15 )
			{
				body_payload += " " + data.hosts[host].TimeString;
				if ( age > 24*60 )
					hasOld = true;
			}

			body_payload += "</td";
			body_payload += "</tr>";
		}
	}
	
	if ( hasOld )
		$(outer).css("border-color", "red");
	else
		$(outer).css("border-color", "black");
	
	body_payload += "</table>";
	
	$(title).html(title_payload);
	$(body).html(body_payload);
	$(breakbar).html(breakbar_payload);
	$(footer).html(footer_payload);
}

var s = "";
var idx = 0;
var row1 = "";
var row2 = "";
var idx = 0;
needle = needle.toLowerCase();
for ( var mac in macMap )
{
	var data = macMap[mac];
	if ( needle.length > 0 )
	{
		bFound = (mac.toLowerCase().indexOf(needle) != -1) || (data.alias.toLowerCase().indexOf(needle) != -1) || (data.vendor && data.vendor.toLowerCase().indexOf(needle) != -1 );
		bFound |= (data.description.toLowerCase().indexOf(needle) != -1 );
		if ( data.hosts.length > 0 && !bFound )
		{
			for ( var host in data.hosts )
			{
				hostname = data.hosts[host].hostName;
				if ( hostname )
					bFound |= (hostname.toLowerCase().indexOf(needle) != -1);
				ip = data.hosts[host].ip;
				if ( ip )
					bFound |= (ip.toLowerCase().indexOf(needle) != -1);
				
				if ( bFound )
					break;
			}
		}
		if ( !bFound )
			continue;
	}
	
	if ( idx % 5 == 0 )
		s += "<div style='display: flex; flex:1;'>";
	s += "<div style='display: flex; flex-direction: column; border: 1px solid black; width:15%; position: relative;' id='MAC_"+mac.replace(/:/g,"_") + "_OUTER'>";
	
	s += "<div style='text-align:center; background-color: blue; border-bottom: 1px solid black;' id='MAC_"+mac.replace(/:/g,"_") + "_TITLE'>";
	s += "</div>";
	s += "<div style='padding:0px 0px; overflow: hidden;' id='MAC_"+mac.replace(/:/g,"_") + "_BREAKBAR'>";
	s += "</div>";
	s += "<div style='padding:2px 0px; overflow: hidden;' id='MAC_"+mac.replace(/:/g,"_") + "_BODY'>";
	s += "</div>";
	s += "<div style='padding:2px 0px; overflow: hidden;' id='MAC_"+mac.replace(/:/g,"_") + "_FOOTER'>";
	s += "</div>";
	s += "</div>";
	if ( idx % 5 == 4 )
		s += "</div><br/>";
	else
		s += "<div style='width: 15px;'>&nbsp;</div>";
	idx++;
}
//if ( row1 != "" )
//	s = s + "<tr>" + row1 + "</tr><tr>" + row2 + "</tr>";

$("#macTable").html(s);
for ( var mac in macMap )
{
	updateMac(mac);
}
</script>
<?php
if ( isset($rvar_delpreto) )
{
	echo "Mac,State,Description,Address<br/>";
	foreach ( $macMap as $mac => $inf )
	{
		echo $inf->mac.",".$inf->state.",".$inf->description.",".$inf->nmap_address."<br/>";
	}
}
?>
</div>
</body></html>
