<html>
<body>

<?php
require('auth.php');

require('mailmgr.php');

import_request_variables("p", "rvar_");

$conn = ldap_connect("10.1.51.1") or die("Could not connect to server");
$r = ldap_bind($conn, "cn=Manager,dc=ldap,dc=internal,dc=quantumsignal,dc=com", "doofus") or die("Could not bind to server");

if (isset($rvar_cn) && isset($rvar_ip)  & isset($rvar_zone))
{
	$hasData = true;
	$aRecord = "";
	$inAddr = "";
	switch ($rvar_zone)
	{
			case "Corp": $aRecord = "10.1.66"; $inAddr="66.1.10"; $dc="dc=66,dc=1,dc=10"; break;
			case "Internal": $aRecord = "10.1.51"; $inAddr = "51.1.10"; $dc="dc=51,dc=1,dc=10"; break;
			case "Mgmt": $aRecord = "10.1.9"; $inAddr = "9.1.10"; $dc="dc=9,dc=1,dc=10"; break;
			case "Pub":$aRecord = "10.4.1"; $inAddr = "1.4.10"; $dc="dc=1,dc=4,dc=10"; break;
			case "QADMZ": $aRecord = "10.1.88"; $inAddr = "88.1.10"; $dc="dc=88,dc=1,dc=10"; break;
			case "VPNNet": $aRecord = "10.2.0"; $inAddr = "0.2.10"; $dc="dc=0,dc=2,dc=10"; break;
			default:
				$hasData = false;
				break;
	}
	
	$zone = strtolower($rvar_zone);
	if( $hasData )
	{
		$infoDNS["aRecord"]	= $aRecord.".".$rvar_ip;
		$infoDNS["dNSTTL"] = "86400";
		$infoDNS["objectclass"][0] = "dNSZone";
		$infoDNS["objectclass"][1] = "top";
		$infoDNS["relativeDomainName"] = $rvar_cn;
		$infoDNS["zoneName"] = $zone.".quantumsignal.com";

		if (ldap_add($conn,	"relativeDomainName=".$rvar_cn.",dc=".$zone.",dc=quantumsignal,dc=com,ou=dns,dc=ldap,dc=internal,dc=quantumsignal,dc=com", $infoDNS)) 
			echo "<h2 style=\"color:green\">Added LDAP entry for DNS mapping $rvar_cn.$zone.quantumsignal.com to $aRecord.$rvar_ip</h2>";
		else
			echo "<h2 style=\"color:red\">LDAP add failed for DNS!</h2>";

		$inforDNS["dNSTTL"]	= "86400";
		$inforDNS["objectclass"][0] = "dNSZone";
		$inforDNS["objectclass"][1] = "top";
		$inforDNS["pTRRecord"] = $rvar_cn.".".$zone.".quantumsignal.com.";
		$inforDNS["relativeDomainName"] = $rvar_ip;
		$inforDNS["zoneName"] = $inAddr.".in-addr.arpa";

		if (ldap_add($conn,	"relativeDomainName=".$rvar_ip.",".$dc.",dc=in-addr,dc=arpa,ou=dns,dc=ldap,dc=internal,dc=quantumsignal,dc=com", $inforDNS))
			echo "<h2 style=\"color:green\">Added LDAP entry for reverse DNS mapping $aRecord.$rvar_ip to $rvar_cn.$zone.quantumsignal.com</h2>";
		else
			echo "<h2 style=\"color:red\">LDAP add failed for reverse DNS!</h2>";
	}
}
	?>
	<form method='post'>
		Name:<input type=text id='cn' name='cn'/><br/>
		IP:<input type=text id='ip' name='ip'/><br/>
		Zone:<select id='zone' name='zone'>
			<option>Corp</option>
			<option>Internal</option>
			<option>Mgmt</option>
			<option>Pub</option>
			<option>QADMZ</option>
			<option>VPNNet</option>
			</select>
		<input type='submit'/>
	</form>
</body>
</html>