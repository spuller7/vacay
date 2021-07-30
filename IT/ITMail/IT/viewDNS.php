<?php require('auth.php');?><?php

function gethost ($ip) {
$host = `host $ip`;
$lines = explode("\n",$host);
$result = array();
foreach ( $lines as  $line )
{
	if ( $line != "" )
	{		
		$result[] = end( explode( " ", $line));
	}
}
return $result;
}

?>
<html>
        <head>
        <title>IT Hosts Status</title>
        <meta charset="UTF-8">

        <script src='/js/jquery-1.11.1.min.js'></script>
        <script src='/js/itCmds.js'></script>
        <link href='/css/base.css' rel='stylesheet' media='screen'/>
<style>
	.ROW_GOOD { display: none; }
</style>
<script>
function DoShowAll()
{
	$(".ROW_GOOD").css("display", "table-row");
	$(".ROW_PROBLEM").css("display", "table-row");
}

function DoShowProblems()
{
	$(".ROW_GOOD").css("display", "none");
	$(".ROW_PROBLEM").css("display", "table-row");
}

$( document ).ready( function() {
	UpdateBanner();
});
</script>
	</head>
<body>
<div class='BODYAREA'>
<span class='hdrLink' href='viewDNS.php'>DNS</span>&nbsp;&nbsp;&nbsp;
<a class='hdrLink' href='dhcp_fetch.php'>DHCP</a><br/>
<?php

$tab='DNS';
require('header.php');

echo "<a onclick='DoShowAll(); return false' href='#'>Show All</a>";
echo "&nbsp;&nbsp;";
echo "<a onclick='DoShowProblems(); return false' href='#'>Show Problems</a>";

$conn = ldap_connect("ldaps://ldap.mgmt.quantumsignal.com") or die("Could not connect to server");
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
	$statements = $item["dhcpstatements"];
	$addr = "";
	foreach ( $statements as $statement )
	{
		$parts = explode(" ", $statement);
		if ( $parts[0] == "fixed-address")
		{
			$addr = $parts[1];
			break;
		}
	}
	$ldapDHCP[$addr] = $cn;
}




$dn = "ou=dns,dc=ldap,dc=internal,dc=quantumsignal,dc=com"; //the object itself instead of the top search level as in ldap_search
$justthese = array("pTRRecord", "relativeDomainName", "zoneName", "aRecord"); //the attributes to pull, which is much more efficient than pulling all attributes if you don't do this
$result = ldap_search($conn,$dn, "(objectClass=dNSZone)", $justthese) or die ("Error in search query: ".ldap_error($ldapconn));
$ldapData = ldap_get_entries($conn, $result);

$data = array();
for ( $i = 0; $i < count($ldapData); $i++ )
{
	if ( !isset($ldapData[$i]) )
		continue;
	
	$dt = $ldapData[$i];
	if ( isset($dt["arecord"]) )
	{
		$rdnBase = $dt["relativedomainname"][0];
		$parts = explode("+", $rdnBase);
		$rdn = $parts[0];
		$dnsName = $rdn.".".$dt["zonename"][0];
		$dnsip = $dt["arecord"][0];	
		$obj = new stdClass();
		$obj->ip = $dnsip;
		$obj->host = $dnsName;
		$obj->type = "host";
		$data[] = $obj;
	}
	else if ( isset($dt["ptrrecord"]) ) 
	{
		$ip = $dt["zonename"][0];
		$parts = explode( ".", $ip );
		$ip = $parts[2].".".$parts[1].".".$parts[0].".";
		$rdnParts = explode(".",$dt["relativedomainname"][0]);
		$ip .= $rdnParts[0];
		$hosts = $dt["ptrrecord"];
		$count = $hosts["count"]|0;
		for ( $idx = 0; $idx < $count; $idx++ )
		{
			$host = $hosts[$idx];
			$obj = new stdClass();
			$obj->ip = $ip;
			$obj->host = substr($host, 0, strlen($host)-1);
			$obj->type = "address";
			$data[]= $obj;
		}
	}
}

ldap_close($conn);

echo "<html><body>";
echo "<table class='DNSTable'>";
echo "<tr><th></th><th>Type</th><th>LDAP IP</th><th>LDAP Hostname</th><th>Resolved Host</th><th>Resolved IP</th><th>Host List</th><th>IP List</th><th>DHCP</th></tr>\n";
foreach ( $data as $obj )
{
	$ip = $obj->ip;
	$hostname = $obj->host;
	$type = $obj->type;
	$resolved_hosts = gethost( $ip );
	$resolved_host = "-";
	foreach ( $resolved_hosts as $aHost )
	{
		$resolved_host = $aHost;
		if ( $aHost == $hostname."." )
		{
			$resolved_host = $hostname;
			break;
		}
	}
	$resolved_ips = gethostbynamel( $hostname );
	$resolvedIP = "-";
	if ( $resolved_ips )
	{
		foreach ( $resolved_ips as $anIP )
		{
			$resolvedIP = $anIP;
			if ( $anIP == $ip )
				break;
		}
	}
	$goodIp = ($ip == $resolvedIP);
	$goodHost = ($hostname == $resolved_host);

	$classIP = "DNSCell td_".($goodIp?"NONE":"BAD");
	$classHost = "DNSCell td_".($goodHost?"NONE":"BAD");	
	
	$inDHCP = isset($ldapDHCP[$ip]) ? true : false;

	//TEMP
	$inDHCP = true;

	$dhcpName = $inDHCP ? $ldapDHCP[$ip] : "";

	if ( $type == 'host' )
	{
		$classType = $classHost;
		$inDHCP = true;
	}
	else
	{
		$classType = $classIP;
	}
	
	$classDHCP = "DNSCell td_".($inDHCP?"NONE":"BAD");

	if ( $goodIp && $goodHost && $inDHCP )
	{
		$rowClass = "ROW_GOOD";
		$rowIcon = "<img src='/images/green_check.png'>";
	}
	else
	{
		$rowClass = "ROW_PROBLEM";
		$rowIcon = "<img src='/images/red_claim.png'>";
	}

	echo "<tr class='".$rowClass."'>";
	echo "<td class='DNSCell'><center>".$rowIcon."</center></td>";
	echo "<td class='".$classType."'>".$type."</td>";
	echo "<td class='".$classIP."'>".$ip."</td><td class='".$classHost."'>".$hostname."</td><td class='".$classHost."'>".$resolved_host."</td><td class='".$classIP."'>".$resolvedIP."</td>";
	echo "<td class='".$classHost."'>";
	$bFirst = true;
	foreach ( $resolved_hosts as $host )
	{
		if ( $bFirst ) $bFirst = false; else echo "<br/>";
		echo "&#8226; ".$host;
	}
	echo "</td>";
	echo "<td class='".$classIP."'>";
	$bFirst = true;
	if ( $resolved_ips )
	{
		foreach ( $resolved_ips as $ip )
		{
			if ( $bFirst ) $bFirst = false; else echo "<br/>";
			echo "&#8226; ".$ip;
		}
	}
	echo "</td>";
	echo "<td class='".$classDHCP."'>".$dhcpName."</td>";
	echo "</tr>\n";
}
	echo "</table>";
?>
</div>
</body>
</html>

