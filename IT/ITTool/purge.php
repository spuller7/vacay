<?php

if (php_sapi_name() != "cli") 
{
	echo "Not allowed from server";
	exit(0);
}

require("mailmgr.php");

performPurge();
?>
