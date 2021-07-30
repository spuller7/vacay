<?php require('auth.php');?><html>
	<head>
	<title>IT Net Status</title>
	<meta charset="UTF-8">
	
	<script src='/js/jquery-1.11.1.min.js'></script>
	<script src='/js/itCmds.js'></script>
	<link href='/css/base.css' rel='stylesheet' media='screen'/>	
	<style>
		.WARN { border: 1px solid black; padding: 5px; font-size: 24px; color: red; background:#FFCCCC; }
		TD { border: 1px solid black; padding: 5px }
		.IPINFO { font-size: 12px; }
		.NAMEINFO { font-size: 12px; }
		.MacTitle { font-size: 16px; }
		.DNSCell { border: 1px solid black; padding: 5px; font-size: 12px; }
		.LabelDescription { font-size: 12px; text-decoration: none; }
		.LabelDescription:hover { text-decoration: none; }
		.MacHost { vertical-align:middle; font-size: 12px;  }
		.MacIP { vertical-align:middle; font-size: 16px;  }
	</style>
		</head>
<body>
<div class='BODYAREA'>
<a class='hdrLink' href='viewDNS.php'>DNS</a>&nbsp;&nbsp;&nbsp;
<span class='hdrLink' href='dhcp_fetch.php'>DHCP</span>
<?php

$tab='DNS';
require('header.php');
require('mailmgr.php');
/*
 $conn = ldap_connect("10.1.51.1") or die("Could not connect to server");
$r = ldap_bind($conn, "cn=Manager,dc=ldap,dc=internal,dc=quantumsignal,dc=com", "doofus") or die("Could not bind to server");
import_request_variables("p", "rvar_");

if (isset($rvar_cn) && isset($rvar_ip))
{
    $infoDNS["aRecord"]	= "10.1.66.".$rvar_ip;
    $infoDNS["dNSTTL"] = "86400";
    $infoDNS["objectclass"][0] = "dNSZone";
    $infoDNS["objectclass"][1] = "top";
    $infoDNS["relativeDomainName"] = $rvar_cn;
    $infoDNS["zoneName"] = "corp.quantumsignal.com";

    if (ldap_add($conn,	"relativeDomainName=".$rvar_cn.",dc=corp,dc=quantumsignal,dc=com,ou=dns,dc=ldap,dc=internal,dc=quantumsignal,dc=com", $infoDNS)) 
        echo "<h2 style=\"color:green\">Added LDAP entry for DNS mapping $rvar_cn.corp.quantumsignal.com to 10.1.66.$rvar_ip</h2>";
    else
        echo "<h2 style=\"color:red\">LDAP add failed for DNS!</h2>";

    $inforDNS["dNSTTL"]	= "86400";
    $inforDNS["objectclass"][0] = "dNSZone";
    $inforDNS["objectclass"][1] = "top";
    $inforDNS["pTRRecord"] = $rvar_cn.".corp.quantumsignal.com.";
    $inforDNS["relativeDomainName"] = $rvar_ip;
    $inforDNS["zoneName"] = "66.1.10.in-addr.arpa";

    if (ldap_add($conn,	"relativeDomainName=".$rvar_ip.",dc=66,dc=1,dc=10,dc=in-addr,dc=arpa,ou=dns,dc=ldap,dc=internal,dc=quantumsignal,dc=com", $inforDNS))
        echo "<h2 style=\"color:green\">Added LDAP entry for reverse DNS mapping 10.1.66.$rvar_ip to $rvar_cn.corp.quantumsignal.com</h2>";
    else
        echo "<h2 style=\"color:red\">LDAP add failed for reverse DNS!</h2>";
}
[16:25:20] <cmshowers> where $rvar_cn is the hostname and $rvar_ip is the last octet of the IP
[17:12:52] *** cmshowers is Offline [Logged out]

cn=DHCP Config,ou=dhcp,dc=ldap,dc=internal,dc=quantumsignal,dc=com

dhcpService
dhcpHost

cn	
bqeserver
dhcpHWAddress	
ethernet 00:50:56:b0:cf:a1
dhcpStatements	
fixed-address 10.1.66.22
*/

$conn = ldap_connect("ldaps://ldap.mgmt.quantumsignal.com") or die("Could not connect to server");

$r = ldap_bind($conn, "cn=Manager,dc=ldap,dc=internal,dc=quantumsignal,dc=com", "doofus") or die("Could not bind to server");

$dn = "cn=DHCP Config,ou=dhcp,dc=ldap,dc=internal,dc=quantumsignal,dc=com"; //the object itself instead of the top search level as in ldap_search

$justthese = array("cn", "dhcpHWAddress", "dhcpStatements"); //the attributes to pull, which is much more efficient than pulling all attributes if you don't do this
$result = ldap_search($conn,$dn, "(objectClass=dhcpHost)", $justthese) or die ("Error in search query: ".ldap_error($ldapconn));
$data = ldap_get_entries($conn, $result);
echo "<html><body>";
$lookup = array();
$ipDups = array();
$nameDups = array();
foreach ( $data as $item )
{
	if ( !is_array($item) ) continue;
	$cn = $item["cn"][0];
	$dn = $item["dn"];
	$dhcpHWAddress = $item["dhcphwaddress"][0];
	$parts = explode(" ",$dhcpHWAddress);
	$mac = strtolower($parts[1]);
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
	$obj = new stdclass();
	$obj->cn = $cn;
	$obj->addr = $addr;
	$obj->dn = $dn;
	
	$cns = explode(",",$dn);
	if ( count($cns)>1)
	{
		$ipPart = substr($cns[1],3);
		$ipPart = explode(".",$ipPart);
		$addrPart = explode(".",$addr);
		if ( count($ipPart) == 4 && count($addrPart) == 4 )
		{
			if ( $ipPart[0] != $addrPart[0] || $ipPart[1] != $addrPart[1] || $ipPart[2] != $addrPart[2] )
			{
				echo "<span class='WARN'>Mismatch for ".$obj->dn." ".$obj->addr."</span><br/>";
			}
		}
		else
		{
			echo "<span class='WARN'>Missing parts in ".$obj->dn." ".$obj->addr."</span><br/>";
		}
	}
	if ( !isset($ipDups[$addr]))
		$ipDups[$addr] = array();
	
	if ( !isset($nameDups[$cn]))
	{
		$nameDups[$cn] = $cn;
	}
	else
	{
		echo "<span class='WARN'>Duplicate name $cn</span><br/>";
	}
		

	$ipDups[$addr][] = $cn;
	
	$result = shell_exec('host -W 1 -v '.$obj->addr.' 10.1.51.9');
	$lines = explode("\n", $result );
	$ad = array();
	foreach ( $lines as $line )
	{
		$left = strstr( $line, "PTR", false );
		$rem = explode("PTR", $left);
		if (count($rem) < 2 )
			continue;
		$name = trim($rem[1]);
		if ( $name == "" ) 
			continue;
		$ad[]=$name;
	}
	
	sort($ad);
	$obj->AD = implode("<br/>",$ad);
	if ( !isset($lookup[$mac]) )
		$lookup[$mac] = array();
	$lookup[$mac][] = $obj;
}

foreach ( $ipDups as $name=>$value )
{
	if ( count($value) > 1 )
	{
		echo "<span class='WARN'>";
		echo "Dup IP ".$name."<br/>";
		foreach ($value as $ip )
		{
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$ip."<br/>";
		}
		echo "</span>";
	}
}

echo "<br/>";

$dn = "ou=dns,dc=ldap,dc=internal,dc=quantumsignal,dc=com"; //the object itself instead of the top search level as in ldap_search
$justthese = array("pTRRecord", "relativeDomainName", "zoneName", "aRecord"); //the attributes to pull, which is much more efficient than pulling all attributes if you don't do this
$result = ldap_search($conn,$dn, "(objectClass=dNSZone)", $justthese) or die ("Error in search query: ".ldap_error($ldapconn));
$data = ldap_get_entries($conn, $result);

$ldapDNS = array();
for ( $i = 0; $i < count($data); $i++ )
{
	if ( isset($data[$i]["arecord"]) )
	{
		$d = $data[$i];
		//echo json_encode($d)."<br/>";
		$rdnBase = $data[$i]["relativedomainname"][0];
		$parts = explode("+", $rdnBase);
		$rdn = $parts[0];
		$zone = $data[$i]["zonename"][0];
		$dnsip = $data[$i]["arecord"][0];
		if ( !isset($ldapDNS[$dnsip]))
		{
			$ldapDNS[$dnsip] = array();
		}
		$o = new stdclass;
		$o->rdn = $rdn;
		$o->zone = $zone;
		$ldapDNS[$dnsip][] = $o;
	}
}

ldap_close($conn);

$ipLookup = array();
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
//		if ( $obj->pingAge > 1440*3 )
//			continue;
	

		if ( !isset($lookup[$mac]) )
			$lookup[$mac] = array();

		$ipLookup[$mac][] = $obj;
	}
}

ksort($lookup);

echo "<table border=0 style='border-collapse:collapse'>";
echo "<tr><th>Mac</th><th>Description</th><th>Router Info</th><th>DHCP IP</th><th>DHCP Name</th><th>LDAP DNS</th><th>AD DNS</th></tr>";
echo "<tr>";
foreach ( $lookup as $mac => $mappings )
{
	if ( count($mappings) == 0 )
	{
		$o = new stdclass();
		$o->cn = "-";
		$o->addr = "-";
		$mappings[] = $o;
	}
	echo "<td rowspan='".count($mappings)."'>".$mac."</td>";
	$desc = $redis->hget("IT.macregistrar.".$mac,"description");
	$hasMac = ($desc != "");
	$color = "#FFFFFF";
	if ( !$hasMac )
	{
		$desc = "<small>No MAC Record</small>";
		$color = "#CCCCCC";
	}
	echo "<td class='NAMEINFO' style='background: ".$color."' rowspan='".count($mappings)."'>".$desc."</td>";
	
	$activeIP = "";
	$hasInfo = isset($ipLookup[$mac]);
	if ( !$hasInfo )
		$color = "#CCCCCC";
	
	$MAC_DHCP_AGREEMENT = false;
	
	foreach ( $ipLookup[$mac] as $ip )
	{
			if ( $ip->ActiveMac || $activeIP == "" )
			{
				$activeIP = $ip->ip;
			}
	}
	
	foreach ( $mappings as $mapping )
	{
		if ( $mapping->addr == $activeIP )
		{
			$MAC_DHCP_AGREEMENT  = true;
			break;
		}
	}
	
	if ($MAC_DHCP_AGREEMENT)
		$color = "#CCFFCC";
	
	echo "<td class='IPINFO' style='background: ".$color."' rowspan='".count($mappings)."'>";
	if ( $hasInfo )
	{
		foreach ( $ipLookup[$mac] as $ip )
		{
			$desc = $ip->ip." ".($ip->ActiveMac ? "MAPPED" : "<b>".$ip->TimeString."</b>")." ".($ip->ActivePing ? "PING":"<span style='color:red'>NO PING</span>");
			if ( $ip->hostName )
			{
				$desc .= " - ".$ip->hostName;
			}
			echo $desc."<br/>";
			if ( $ip->ActiveMac || $activeIP == "" )
				$activeIP = $ip->ip;
		}
	}
	else
	{
		echo "<small>No Router Info</small>";
	}
	echo "</td>";
	foreach ( $mappings as $mapping )
	{
		if ( $mapping->addr == $activeIP )
			$color = "#CCFFCC";
		else
		{
			if ( $activeIP == "" )
			{
				if ( $hasMac )
					$color= "#FFFFCC";
				else
					$color = "#CCCCCC";
			}
			else
				$color = "#FFCCCC";
		}
		echo "<td style='background:".$color."'>".$mapping->addr."</td>";
		if ( count( $ldapDNS[$mapping->addr] ) == 0 )
			$color = "#CCCCCC";
		else
		{
			$color = "#FFCCCC";
			foreach ( $ldapDNS[$mapping->addr] as $o )
			{
				if ( $o->rdn == $mapping->cn )
				{
					$color = "#CCFFCC";
					break;
				}
			}
		}
		
		echo "<td style='background:".$color."'>".$mapping->cn."</td>";
		
	

		
		echo "<td class='DNSCell' style='background:".$color."'>";
		$ldapChk = array();
		if ( isset($ldapDNS[$mapping->addr]))
		{
			foreach ( $ldapDNS[$mapping->addr] as $o )
			{
				$ldapChk[] = $o->rdn.".".$o->zone.".";
				echo $o->rdn.".".$o->zone."<br/>";
			}
		}
		sort($ldapChk);
		$lc = implode("<br/>",$ldapChk);
		echo "</td>";
		if ( $lc != $mapping->AD )
		{
			echo "<td class='DNSCell' style='background: #FFCCCC'>".$mapping->AD."</td>";
		}
		else
		{
			if ( $mapping->AD == "" )
				echo "<td class='DNSCell' style='background: #CCCCCC'></td>";
			else
				echo "<td class='DNSCell' style='background: #CCFFCC'>OK</td>";
		}

		echo "</tr>";
		echo "<tr>";
	}
}
echo "</tr>";
echo "</table>";
echo "</body></html>";
?>
