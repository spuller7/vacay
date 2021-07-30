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

require_once( dirname(__FILE__)."/./common.php" );
require_once( dirname( __FILE__ )."/../dbConnect.php" );

// setrawcookie("date",date("m/d/Y"));

emitStdProlog(".");
emitMD5js();
?>

<script>
<?php insertCornerJS(); ?>

function setFormInfo( )
{
	var username = document.getElementById( "username" ).value;
	var password = document.getElementById( "password" ).value; 
  	var uniqId = "<?php echo $_SESSION["salt"]; ?>";
	var hashedPassword = MD5( uniqId + ":" + MD5(username + ":timekeeping:" + password) + ":" + uniqId );
	//document.getElementById( "username" ).value = username;
	//document.getElementById( "password" ).value = "novalue";
	document.getElementById( "hiddenUsername" ).value = username;
	//document.getElementById( "passwordHash" ).value = hashedPassword;
	document.getElementById( "passwordHash" ).value = password;
	document.getElementById( "submitform" ).submit();
}

window.onload=function()
{
	document.getElementById('username').focus();
}
</script>

<html>

<head>
	<title>Project Tracker</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
</header>
<body class="body-login">
<div class='MainVersion'><p class="build-info"><?php echo buildInfo() ?></p></div>
<div id="wrapper">
	<div class="login-div">
	<img src="images/logo-login-page.png" class="login-logo" alt="Quantum Signal AI, LLC" />
	<h1>Project Tracker</h1>
		<form>
			<table class="login-table">
			<tr>
			<td><h3>User Name</h3></td>
			<td><input type=text id="username" name="username"></td>
			</tr>
			<tr>
			<td><h3>Password</h3></td>
			<td><input type=password id="password" name="password"></td>
			</tr>
			<tr>
			<td></td>
			<td><input type="submit" id="submit" class="login-button" name="submit" value='Login' onclick="setFormInfo(); return false;">
			</tr>
			</table>
		</form>
		<form method="POST" id="submitform">
			<input type=hidden id="hiddenUsername" name="username">
			<input type=hidden id="passwordHash" name="passwordHash"/>
		</form>
	</div>
</div><!-- wrapper -->
</body>
