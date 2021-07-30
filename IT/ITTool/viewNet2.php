<?php require('auth.php');?><html>
	<head>
	<title>IT Net Status</title>
	<meta charset="UTF-8">
	<style>
		.hdrLink { font-size: 16px; }
	</style>
	
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
<span class='hdrLink' href='viewNet2.php'>Net Info</span>&nbsp;&nbsp;&nbsp;
<a class='hdrLink' href='MacInfo.php'>Mac Info</a>&nbsp;&nbsp;&nbsp;
<a class='hdrLink' href='SwitchView.php'>Switch View</a><br/><br/>
<?php
	$tab="NET";
	require('header.php');
require('mailmgr.php');


/////////////////////////////////////////////////////////////////

$conn = ldap_connect("ldaps://ldap.mgmt.quantumsignal.com") or die("Could not connect to server");
$r = ldap_bind($conn, "cn=Manager,dc=ldap,dc=internal,dc=quantumsignal,dc=com", "doofus") or die("Could not bind to server");

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
		if ( !isset($data[$dnsip]))
			$data[$dnsip] = array();
		$data[$dnsip][$dnsName] = $dnsName;
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
			$host = substr($host, 0, strlen($host)-1);
			if ( !isset($data[$dnsip]))
				$data[$ip] = array();
			$data[$ip][$host] = $host;
		}
	}
}

ldap_close($conn);

/////////////////////////////////////////////////////////////////

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
	$stem = ".quantumsignal.com";
	foreach ( $results as $subnet )
	{
		$key = $subnet.".".$lastOct;
		$hostName="";
		
		$strKey = "";
		$isAlive = false;
		$reallyOld = false;
		$pingsKey = "IT.pings.".$key;
		$pingsData = $redis->hgetall( $pingsKey);
		$hasData = (isset($pingsData["mac"]) && $pingsData["mac"] != "") || isset($pingsData["hostname"]) || isset($pingsData["lastAlive"]);
		$bFoundOne |= $hasData;
		
		$strHost = "";
		if ( isset($pingsData["lastHost"]) )
		{
			$hostName = $pingsData["hostname"];
			$host = $hostName;
			if ( substr($host, -strlen($stem) ) == $stem )
			{
				$host = substr($host,0,strlen($host)-strlen($stem));
			}
			$strHost = $host;
			$min = GetElapsedMinutes( $pingsData["lastHost"] );
			if ( $min > 30 )
				$strHost .= " (".GetTimeString($min).")";
		}

		if ( !isset($pingsData["lastAlive"]) || $pingsData["lastAlive"] == 0 )
			$strKey = $hasData ? "<b>No Ping</b>" : "";
		else
		{
			$lastPingTime = $pingsData["lastAlive"];
			$lastAlive = $pingsData["lastAlive"];
			$minutes = GetElapsedMinutes($lastAlive);
			$reallyOld = $minutes > (60*24*90); //90 days is really old
			if ( $minutes < 30 )
			{
				$isAlive = true;
			}
			else
			{
				$isAlive = false;
				$ts = GetTimeString($minutes);
				$strKey = "Last Seen(".$ts.")";
			}
		}
		
		if ( isset( $data[$key] ) )
		{
			$strHosts = $data[$key];
			$bFound = false;
			$saveHost = $strHost;
			$strHost = "";
			foreach ( $strHosts as $key )
			{
				if ( $strHost != "" ) $strHost .= "<br/>";
				if ( substr($key, -strlen($stem) ) == $stem )
				{
					$key = substr($key,0,strlen($key)-strlen($stem));
				}
				if ( $key == $saveHost )
				{
					$bFound = true;
					$strHost .= "<b>".$key."</b>";
				}
				else
					$strHost .= $key;
			}
			if ( !$bFound )
			{
				if ( $strHost != "" ) $strHost .= "<br/>";
				$strHost .= "<i>".$saveHost."</i>";
			}
		}
		else
		{
			if ( $strHost != "" )
				$strHost = "<b>NO DNS</b><br/><i>".$strHost."</i>";
			else
			{
				if ( $hasData )
					$strHost = $key;
			}
		}
			
	
		$icon = "blank.png";
		$clazz = "TD_HOST_INFO";
		if ( $hasData )
		{
			if ( $isAlive )
			{
				$icon = "green_check.png";
			}
			else
			{
				if ( $hostName != "" && (!isset($pingsData["mac"]) || $pingsData["mac"]==""))
				{
					$icon = "red_claim.png";
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
		}
		else
		{
			$clazz = "TD_HOST_NO_INFO";
		}
		$strMAC = "";
		if ( isset($pingsData["mac"]) && isset($pingsData["lastMac"]))
		{
			$pingTime = 0;
			if ( isset($pingsData["lastAlive"]))
				$pingTime = $pingsData["lastAlive"];
			$macTime = $pingsData["lastMac"];
			$strMAC = "<a href='https://hwaddress.com/?q=".$pingsData["mac"]."'>".$pingsData["mac"]."</a>";
			if ( $macTime - $pingTime > 60*60*24 && $pingsData["mac"] != "" )
			{
				$clazz = "TD_STEALTH_HOST";
				$minutes = GetElapsedMinutes($macTime);
				if ( $minutes > 30 )
					$strMAC.=" (".GetTimeString($minutes).")";
				//$strMAC.= "[".GetTimeString($macTime - $pingTime)."]";
				$icon = "red_claim.png";
			}
			if ( $strMAC == "" && $hasData )
				$strMAC = "<b>No MAC</b>";
		}

		$res .= "<td class='".$clazz."'>";
		$res .= "<table><tr><td><img src='/images/$icon'/></td><td>".($strKey?$strKey."<br/>":"").($strHost?$strHost:"").($strMAC?($strHost?"<br/>":"")."".$strMAC:"")."</td></tr></table>";
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

