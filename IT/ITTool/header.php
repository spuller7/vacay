<?php
?>
<div class='TOPAREA'>
<table class='HEADER'>
        <tr class='HEADER'>
                <td class='<?php echo ($tab=='ALARMS'?"":"NO_");?>SELECT' onclick='document.location="viewAlarms.php"; return false;'>Alarms</td>
                <td class='<?php echo ($tab=='HOSTS'?"":"NO_");?>SELECT' onclick='document.location="viewHosts.php"; return false;'>Hosts</td>
                <td class='<?php echo ($tab=='INF'?"":"NO_");?>SELECT' onclick='document.location="viewInf.php"; return false;'>Infrastructure</td>
                <td class='<?php echo ($tab=='BADGE'?"":"NO_");?>SELECT' onclick='document.location="viewBadge.php"; return false;'>Badge</td>
                <td class='<?php echo ($tab=='FIREWALL'?"":"NO_");?>SELECT' onclick='document.location="viewFirewall.php"; return false;'>Firewall</td>
                <td class='<?php echo ($tab=='VM'?"":"NO_");?>SELECT' onclick='document.location="viewVM.php"; return false;'>VM</td>
                <td class='<?php echo ($tab=='NET'?"":"NO_");?>SELECT' onclick='document.location="MacInfo.php"; return false;'>Net</td>
                <td class='<?php echo ($tab=='DNS'?"":"NO_");?>SELECT' onclick='document.location="viewDNS.php"; return false;'>DHCP/DNS</td>
                <td class='<?php echo ($tab=='SECURITY'?"":"NO_");?>SELECT' onclick='document.location="viewSecurity.php"; return false;'>Security</td>
        </tr>
</table>
</div>
<script>
function addZero(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

function UpdateBanner()
{
	var d = new Date();
	$("#BANNER").html( "Loaded @ "+addZero(d.getHours()) + ":" + addZero(d.getMinutes()) + ":" + addZero(d.getSeconds()));
}
</script>
<div id="watermark">
<img src='/images/qs_logo_small.png'/><br/>
<div id='BANNER'></div>

</div>
<?php

?>
