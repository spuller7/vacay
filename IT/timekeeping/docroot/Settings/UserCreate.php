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

checkRole( Roles::USER_MANAGER );

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

if ( !isset( $um_Username ) )
{
	emitStdProlog();
	emitMD5js();
	?><script>

	function validateAndSubmit()
	{
		if ( (document.createForm.Password.value == "") || (document.createForm.PasswordVerify.value != document.createForm.Password.value) )
		{
			alert("Password mismatch or blank!");
			return false;
		}
		var uniqId = "<?php echo $_SESSION["salt"]; ?>";
		document.submitForm.Username.value = document.createForm.Username.value;
		document.submitForm.FullName.value = document.createForm.Fullname.value;
		document.submitForm.Template.value = document.createForm.Template.value;
		document.submitForm.State.value = document.createForm.State.value;
		document.submitForm.PasswordHash.value = MD5( document.createForm.Username.value + ":timekeeping:" + document.createForm.Password.value );
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
		document.createForm.Username.focus();
	}
	</script>		
	<?php
	emitLoadingHeader();
	?><div id='TitleBar' class='WorkPaneTitleBar'>Create User</div><br/>
	<?php if (isset($_GET['Error']) ) { ?><br/><table class='ErrorText' cellpadding=0 cellspacing=0><tr><td>There was an error handling your request. Please try again.</td></tr></table><?php } ?>
	<form name='createForm' method='post'>
		<table class='WorkPaneListTable' cellpadding=0 cellspacing=0>
		<tr><td colspan=2><div id='TableTop'></div></td></tr>
		<tr class='WorkPaneViewListTitle'>
			<td class='WorkPaneFormLabel'><div class='FormLabel' style='background: #FFF;' id='UserNameLabel'>&nbsp;&nbsp;User name&nbsp;&nbsp;</div></td>
			<td class='WorkPaneFormField'><input type=text name='Username' maxlength=32 /></td>
		</tr>
		<tr class='WorkPaneViewListTitle'>
			<td class='WorkPaneFormLabel'><div class='FormLabel' style='background: #FFF;' id='PasswordLabel'>Password</div></td>
			<td class='WorkPaneFormField'><input type=password name='Password'/></td>
		</tr>
		<tr class='WorkPaneViewListTitle'>
			<td class='WorkPaneFormLabel'><div class='FormLabel' style='background: #FFF;' id='PasswordVerifyLabel'>Verify Password</div></td>
			<td class='WorkPaneFormField'><input type=password name='PasswordVerify'/></td>
		</tr>
		<tr class='WorkPaneViewListTitle'>
			<td class='WorkPaneFormLabel'><div class='FormLabel' style='background: #FFF;' id='FullNameLabel'>&nbsp;&nbsp;Full name&nbsp;&nbsp;</div></td>
			<td class='WorkPaneFormField'><input type=text name='Fullname' maxlength=64 /></td>
		</tr>
		<tr class='WorkPaneViewListTitle'>
			<td class='WorkPaneFormLabel'><div class='FormLabel' style='background: #FFF;' id='UserNameLabel'>&nbsp;&nbsp;User state&nbsp;&nbsp;</div></td>
			<td class='WorkPaneFormField'><select name=State><option value="active" SELECTED>Active</option><option value="terminated">Terminated</option><option value="suspended">Suspended</option></select></td>
		</tr>
		<tr class='WorkPaneViewListTitle'>
			<td class='WorkPaneFormLabel'><div class='FormLabel' style='background: #FFF;' id='TemplateLabel'>&nbsp;&nbsp;Template Administrator&nbsp;&nbsp;</div></td>
			<td class='WorkPaneFormField'><select name=Template><option value=''>- none -</option>
		<?php 
			$users = AdminUser::listUsers();
			foreach ( $users as $user )
			{
				echo "<option value='".$user->UserID."'>".$user->Username."</option>\n";
			}
		?></select></td></tr>
		<tr class='WorkPaneViewListTitle'>
			<td colspan=2 align='center'>			
				<div id='BtnArea'>
					<input id='CreateBtn' type='button' value='Create' onclick='validateAndSubmit();'> <input id='CancelBtn' type=button value='Cancel' onclick='document.location="UserView.php";'/>
				</div>
			</td>
		</tr>	
		<tr><td colspan=2><div id='TableBottom'></div></td></tr>
		</table>	
	</form>	
	<form name='submitForm' method='post'>
		<input type='hidden' name='PasswordHash'/>
		<input type='hidden' name='Username'/>
		<input type='hidden' name='FullName'/>
		<input type='hidden' name='Template'/>
		<input type='hidden' name='State'/>
	</form>
	<?php
		emitLoadingFooter();	
		exit();
}
if ( ($um_Username == "") || ($um_PasswordHash == ""))
{
	echo "<html><body><script>";
	echo "document.location='UserCreate.php?Error=True';";
	echo "</script></body></html>";
}
else
{
	$user = AdminUser::create( $um_Username, $um_PasswordHash, $um_FullName, $um_State );
	if ( isset($um_Template) && $um_Template != "" )
	{
		$templateUser = AdminUser::retrieve( $um_Template );
		
		$adminRoles = roles();
		$templateRoles = $templateUser->roles();
		foreach( array_keys($adminRoles) as $role )
		{
			$user->setRole( $role, isset( $templateRoles[$role] ) );
		}
	}
	
	echo "<html><body><script>";
	echo "document.location='UserEdit.php?Userid=".$user->UserID."';";
	echo "</script></body></html>";
}

?>
