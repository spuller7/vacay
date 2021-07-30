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

emitStdProlog(".");
emitMD5js();
?>

<script>
<?php insertCornerJS(); ?>

function setFormInfo( )
{
	var username = document.getElementById( "username" ).value.toLowerCase();
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
	if ( NiftyCheck() ) 
	{
		RoundedTop("div#TableTop","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>");
		Rounded("div.FormLabel","<?php echo CSS_BACKGROUND ?>","#FFF","small");
		RoundedBottom("div#TableBottom","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>");
	}
	<?php emitLoadingJS(); ?>
	document.getElementById('username').focus();
}
</script>
<?php emitLoadingHeader(); ?>
<div class="MainHeader">Chronoton Gatherer</div>
<div class='MainVersion'><?php echo buildInfo() ?></div>
<div class="MainSeparator"></div>
<br/><br/><br/><br/>
<center>
		<form>
			<table cellspacing=0 cellpadding=0>
				<tr><td colspan='2'><div id='TableTop'></div></td></tr>
				<tr class='WorkPaneViewListTitle'><td class='WorkPaneFormLabel'><div style='background: #FFF;' class='FormLabel'>&nbsp;&nbsp;User Name&nbsp;&nbsp;</div></td><td class='WorkPaneFormField'><input type=text id="username" name="username"></td></tr>
				<tr class='WorkPaneViewListTitle'><td class='WorkPaneFormLabel'><div style='background: #FFF;' class='FormLabel'>&nbsp;&nbsp;Password&nbsp;&nbsp;</div></td><td class='WorkPaneFormField'><input type=password id="password" name="password"></td></tr>
				<tr class='WorkPaneViewListTitle'><td align=center colspan=2><input type="submit" id="submit" name="submit" value='Login' onclick="setFormInfo(); return false;"></td></tr>
				<tr><td colspan=2><div id='TableBottom'></div></td></tr>
			</table>
		</form>
		<form method="POST" id="submitform">
			<input type=hidden id="hiddenUsername" name="username">
			<input type=hidden id="passwordHash" name="passwordHash"/>
		</form>
</center>
<?php emitLoadingFooter(); ?>		
