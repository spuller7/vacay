<?php
//////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2008, Quantum Signal, LLC
// 
// This data and information is proprietary to, and a valuable trade 
// secret of, Quantum Signal, LLC.  It is given in confidence by Quantum 
// Signal, LLC. Its use, duplication, or disclosure is subject to the
// restrictions set forth in the License Agreement under which it has 
// been distributed.
//
//////////////////////////////////////////////////////////////////////

require_once( dirname(__FILE__)."/./utils.php" );

setrawcookie("date",date("m/d/Y"));

function emitStdProlog( $relDir = ".", $title = "Org Chart Tracker")
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<link href="<?php echo $relDir ?>/main.css.php" rel="stylesheet" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="<?php echo $relDir ?>/print.css.php" media="print" />
	<script type="text/javascript" src="<?php echo $relDir ?>/query.js"></script>
	<script type="text/javascript" src="<?php echo $relDir ?>/corner.js"></script>
	<script type="text/javascript" src="<?php echo $relDir ?>/utils.js"></script>
	<title>Quantum Signal AI Org Chart Tracker</title>
<?php
}
?>

<script>

window.onload=function()
{
	document.getElementById('username').focus();
}
</script>


<head>
	<title>Org Chart Manager</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="main.css.php">
</header>
<body>
<h1>Org Manager</h1>
<form method="POST" action="index.php">
	<table>
	<tr>
	<td>User Name</td>
	<td><input type=text id="username" name="username"></td>
	</tr>
	<tr>
	<td>Password</td>
	<td><input type=password id="password" name="password"></td>
	</tr>
	<tr>
	<td></td>
	<td><input type="submit" id="submit" class="login-button" name="submit" value='Login' >
	</tr>
	</table>
</form>
</body>
</html>