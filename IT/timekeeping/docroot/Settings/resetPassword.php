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

require_once( dirname( __FILE__ )."/../authenticate.php" );
require_once( "../common.php" );
require_once( "../utils.php" );
require_once( "./UserManager.php" );

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

$user = AdminUser::retrieve($_SESSION['USERID']);

if ( !isset( $um_PasswordHash ) )
{
	emitStdProlog();
	emitMD5js();
	?><script>
	function validateAndSubmit()
	{
		var username = document.createForm.Username.value;
		if (/[^\w\s.@]/.test(username))
		{
			alert("Invalid characters in username!");
			return;
		}
		var OldPassword = document.createForm.OldPassword.value;
		var Password = document.createForm.Password.value;
		var PasswordVerify = document.createForm.PasswordVerify.value;
		if ( (OldPassword == "") || (Password == "") || (PasswordVerify != Password) )
		{
			alert("Password mismatch or blank!");
			return;
		}
		var uniqId = "<?php echo $_SESSION["salt"]; ?>";
		var hashedOldPassword = MD5( uniqId + ":" + MD5("<?php echo $_SESSION['USER_NAME']; ?>:timekeeping:" + OldPassword) + ":" + uniqId );
		var confirmHash = MD5( uniqId + ":<?php echo $user->PasswordHash; ?>:" + uniqId );
		if (hashedOldPassword != confirmHash)
		{
			alert("Incorrect old password!");
			return;
		}
		document.submitForm.Username.value = username;
		document.submitForm.PasswordHash.value = MD5(username + ":timekeeping:" + Password);
		document.submitForm.submit();
	}
	
	window.onload=function(){
		if ( NiftyCheck() ) 
		{
			Rounded("div.WorkPaneTitleBar","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>"); 
			RoundedTop("div#TableTop","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>");
			Rounded("div.FormLabel","<?php echo CSS_BACKGROUND ?>","#FFF","small");
			RoundedBottom("div#TableBottom","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>");
		}
	<?php emitLoadingJS(); ?>
		document.createForm.OldPassword.focus();
	}
	</script>		
	<?php
	emitLoadingHeader();
	?><div id='TitleBar' class='WorkPaneTitleBar'>Update Password for <?php echo $user->FullName; ?></div><br/>
	<?php if (isset($_GET['Error']) ) { ?><br/><table class='ErrorText' cellpadding=0 cellspacing=0><tr><td>There was an error handling your request. Please try again.</td></tr></table><?php } ?>
	<form name='createForm'>
		<table class='WorkPaneListTable' cellpadding=0 cellspacing=0>
		<tr><td colspan=2><div id='TableTop'></div></td></tr>
		<tr class='WorkPaneViewListTitle'>
			<td class='WorkPaneFormLabel'><div class='FormLabel' style='background: #FFF;' id='UsernameLabel'>Username</div></td>
			<td class='WorkPaneFormField'><input type=text name='Username' value='<?php echo $user->Username; ?>'/></td>
		</tr>
		<tr class='WorkPaneViewListTitle'>
			<td class='WorkPaneFormLabel'><div class='FormLabel' style='background: #FFF;' id='OldPasswordLabel'>Old Password</div></td>
			<td class='WorkPaneFormField'><input type=password name='OldPassword'/></td>
		</tr>
		<tr class='WorkPaneViewListTitle'>
			<td class='WorkPaneFormLabel'><div class='FormLabel' style='background: #FFF;' id='PasswordLabel'>New Password</div></td>
			<td class='WorkPaneFormField'><input type=password name='Password'/></td>
		</tr>
		<tr class='WorkPaneViewListTitle'>
			<td class='WorkPaneFormLabel'><div class='FormLabel' style='background: #FFF;' id='PasswordVerifyLabel'>Verify Password</div></td>
			<td class='WorkPaneFormField'><input type=password name='PasswordVerify'/></td>
		</tr>
		<tr class='WorkPaneViewListTitle'>
			<td colspan=2 align='center'>			
				<div id='BtnArea'>
					<input id='CreateBtn' type='submit' value='Change' onclick="validateAndSubmit(); return false;"> <input id='CancelBtn' type=button value='Cancel' onclick='document.location="UserView.php";'/>
				</div>
			</td>
		</tr>	
		<tr><td colspan=2><div id='TableBottom'></div></td></tr>
		</table>
	</form>
	<form name='submitForm' method="POST">
		<input type="hidden" name="PasswordHash"/>
		<input type="hidden" name="Username"/>
	</form>	
	<?php
		emitLoadingFooter();	
		exit();
}
if ( ($um_PasswordHash == ""))
{
	echo "<html><body><script>";
	echo "document.location='UserCreate.php?Error=True';";
	echo "</script></body></html>";
}
else
{
	if ($_SESSION['USER_NAME'] != $um_Username)
	{
		AdminUser::updateUsernameById($_SESSION['USERID'], $um_Username);
		$_SESSION['USER_NAME'] = $um_Username;
	}
	AdminUser::updatePasswordById($_SESSION['USERID'], $um_PasswordHash);
	
	echo "<html><body><script>";
	echo "document.location='UserView.php';";
	echo "</script></body></html>";
}

?>
