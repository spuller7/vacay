<?php

require('mailmgr.php');
$now = time();
$count = 0;
snmp_set_valueretrieval(SNMP_VALUE_OBJECT);
$mib = "1.3.6.1.4.1.850.1.1.3.5";
$acIP = "10.1.51.76";
$result = snmprealwalk($acIP, "public", $mib );
$now = time();

foreach ( $result as $key=>$value )
{
	$result[$key] = $value->value|0;
}


class TRIPPLITE_SRXCOOL 
{
	const tlpCoolingReturnAirDegF = "SNMPv2-SMI::enterprises.850.1.1.3.5.3.1.1.1.3.1";
	const tlpCoolingOperatingMode = "SNMPv2-SMI::enterprises.850.1.1.3.5.3.6.1.1.1";
	const tlpCoolingWaterStatus = "SNMPv2-SMI::enterprises.850.1.1.3.5.3.6.1.5.1";
	const tlpCoolingOnOff = "SNMPv2-SMI::enterprises.850.1.1.3.5.4.1.1.1.1";
	const tlpCoolingFanSpeed = "SNMPv2-SMI::enterprises.850.1.1.3.5.5.1.1.2.1";
	const tlpCoolingDisplayUnits = "SNMPv2-SMI::enterprises.850.1.1.3.5.5.1.1.4.1";
	const tlpCoolingDehumidifyingMode = "SNMPv2-SMI::enterprises.850.1.1.3.5.5.1.1.21.1";
	const tlpCoolingQuietMode = "SNMPv2-SMI::enterprises.850.1.1.3.5.5.1.1.23.1";
	const tlpCoolingAutoFanSpeed = "SNMPv2-SMI::enterprises.850.1.1.3.5.5.1.1.25.1";
	const tlpCoolingSetPointDegF = "SNMPv2-SMI::enterprises.850.1.1.3.5.5.2.1.1.1";
}

echo "RETURN AIR TEMP(F)= ".($result[TRIPPLITE_SRXCOOL::tlpCoolingReturnAirDegF]/10.0)."\n";

echo "SET POINT(F) = ".($result[TRIPPLITE_SRXCOOL::tlpCoolingSetPointDegF]/10.0)."\n";


echo "OPERATING MODE = ".$result[TRIPPLITE_SRXCOOL::tlpCoolingOperatingMode]."\n";
echo "Water Status = ".$result[TRIPPLITE_SRXCOOL::tlpCoolingWaterStatus]."\n";
echo "Cooling on/off = ".$result[TRIPPLITE_SRXCOOL::tlpCoolingOnOff]."\n";
echo "Fan Speed = ".$result[TRIPPLITE_SRXCOOL::tlpCoolingFanSpeed]."\n";
echo "Display Units = ".$result[TRIPPLITE_SRXCOOL::tlpCoolingDisplayUnits]."\n";
echo "Dehumidifying = ".$result[TRIPPLITE_SRXCOOL::tlpCoolingDehumidifyingMode]."\n";
echo "Quiet Mode = ".$result[TRIPPLITE_SRXCOOL::tlpCoolingQuietMode]."\n";
echo "Auto Fan Speed = ".$result[TRIPPLITE_SRXCOOL::tlpCoolingAutoFanSpeed]."\n";
?>
