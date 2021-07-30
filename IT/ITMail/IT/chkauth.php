<?php
session_start();
if ( !isset($_SESSION["authenticated"]))
{
echo "nope.";
exit(0);
}
?> 
