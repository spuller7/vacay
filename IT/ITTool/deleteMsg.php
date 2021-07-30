<?php require('chkauth.php');?>
<html>
	<body>
<?php

require('mailmgr.php');

$id = $_GET["id"];
$ref = $_GET["ref"];

removeEmail($id);
echo "Email removed - redispatching<br/><pre>";
//performDispatch();
echo "</pre>";

?>
<script>
	self.opener.doReload();
</script>
<h1>Message Deleted</h1>
</body>
</html>
