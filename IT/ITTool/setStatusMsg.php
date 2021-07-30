<?php require('chkauth.php');?><html>
	<body>
<?php

require('mailmgr.php');

$id = $_GET["id"];
$host = $_GET["host"];
$key = $_GET["key"];
$state = $_GET["state"];
$ref = $_GET["ref"];
$msg = getMsg($id, false );

setHostFlag( $host, $key, $msg["headers"]->udate, $state, $id, true );
ack_alarm( $id, "System", "AutoAck key[".$host.".".$key."] changed to ".$state );

?>
<script>
	self.opener.doReload();
	document.location='viewMsg.php?<?php echo "id=".urlencode($id)."&ref=".$ref;?>';
</script>
</body>
</html>
