<?php
require('chkauth.php');

header('Content-Type: application/json; charset=UTF-8');

require('mailmgr.php');

$payload = "{}";
if ( isset( $_POST ) )
	$payload = $_POST;
	

$r = new stdClass();
$r->error = 0;
$r->error_message = "";

if ( $payload["Command"] == 'SetHostNote' )
{
	$host = $payload["Host"];
	$note = $payload["Note"];
	setHostNote($host,$note);
}
else if ( $payload["Command"] == 'GetMessage' )
{
	$id = $payload["ID"];
	$msg = getMsg($id);
	setMsgFlag( $id, "READ", 1 );
	$r->result = $msg;

}
else if ( $payload["Command"] == 'AckMessage' )
{
	$id = $payload["ID"];
	$msg = $payload["Message"];
	$sweep = $payload["Sweep"];
	ack_alarm( $id, "System", $msg, $sweep );
}
else if ( $payload["Command"] == 'ResetStatus' )
{
	$host = $payload["Host"];
	$key = $payload["Key"];
	resetStatus( $host, $key );
}
else if ( $payload["Command"] == 'SetAgeLimit' )
{
	$label  = $payload["Label"];
	$limit = $payload["Limit"];
	setAgeLimit( $label, $limit );
}
else if ( $payload["Command"] == 'SetInfState' )
{
	$id = $payload["ID"];
	$label = $payload["Label"];
	$state = $payload["State"];
	$msg = getMsg($id, false );
	setInfrastructureFlag( $label, $msg["headers"]->udate, $state, $id, true );
	//TODO: this should be the real user info, etc. with a reason (maybe only for FIXED and GOOD?)
	ack_alarm( $id, "System", "AutoAck Infrastructure key[".$label."] changed to ".$state, false );
}
else if ( $payload["Command"] == 'SetStatusState' )
{
	$id = $payload["ID"];
	$host = $payload["Host"];
	$key = $payload["Key"];
	$state = $payload["State"];
	$msg = getMsg($id, false );
	setHostFlag( $host, $key, $msg["headers"]->udate, $state, $id, true );
	//TODO: this should be the real user info, etc. with a reason (maybe only for FIXED and GOOD?)
	ack_alarm( $id, "System", "AutoAck key[".$host.".".$key."] changed to ".$state, false );
}
else if ( $payload["Command"] == 'DeleteMsg' )
{
	$id = $payload["ID"];
	removeEmail($id);
	ob_start();
	//performDispatch();
	ob_end_clean();
}
else if ( $payload["Command"] == 'SetMac' )
{
	$Mac = $payload["MAC"];
	$State = $payload["State"];
	$Description = $payload["Description"];
	setMac( $Mac, $State, $Description );
}
else if ( $payload["Command"] == 'RemoveIP' )
{
	$IP = $payload["IP"];
	removeIP( $IP );
}
else if ( $payload["Command"] == 'SwitchInfo' )
{
	snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
	$port = $payload["Port"];
	$r->status = snmp2_get($switchIP, "public", "IF-MIB::ifOperStatus.".$port) + 0;
	$r->vlan = snmp2_get($switchIP, "public", "1.3.6.1.2.1.17.7.1.4.5.1.1.".$port);
	$r->name  = snmp2_get($switchIP, "public", "IF-MIB::ifName.".$port);
	$r->alias = snmp2_get($switchIP, "public", "IF-MIB::ifAlias.".$port);
	$r->speed = snmp2_get($switchIP, "public", "IF-MIB::ifHighSpeed.".$port);
	global $redis;
	$redis->hset( "IT.portname.".$port, "name", $r->alias );
	$redis->hset( "IT.portname.".$port, "status", $r->status );
	$redis->hset( "IT.portname.".$port, "vlan", $r->vlan );
}
else if ( $payload["Command"] == 'GetPortMapping' )
{
	$port = $payload["Port"];
	$r->macs = array();
	$values = $redis->keys("IT.macregistrar.*");
	foreach ( $values as $value )
	{
		$portID = $redis->hget($value, "portID");
		if ( $port != $portID )
			continue;
		
		$mac = substr($value,strlen("IT.macregistrar."));
		$macMap = new stdclass();
		$macMap->mac = $mac;
		$macMap->state = $redis->hget($value, "state");
		$macMap->description = $redis->hget($value, "description");
		$r->macs[] = $macMap;
	}
}
else if ( $payload["Command"] == 'SetPortAlias' )
{
	$port = $payload["Port"];
	$alias = $payload["Alias"];
//	trigger_error($port." ==> ".$alias);
	snmp2_set( $switchIP, "private", "IF-MIB::ifAlias.".$port, "s", $alias);
}
else
{
	$r->error = -1;
	$r->error_message = "Unknown Command: ".$payload["Command"];
}
echo json_encode($r);
return;
?>
