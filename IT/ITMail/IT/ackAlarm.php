<html>
	<body>
<?php
require('chkauth.php');

require('mailmgr.php');

$id = $_GET["id"];

ack_alarm( $id, "WEBUI", "tester" );

?>
<script>
	self.opener.doReload();
	document.location='viewMsg.php?<?php echo "id=".urlencode($id);?>';
</script>
</body>
</html>
