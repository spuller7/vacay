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

	require_once( dirname( __FILE__ )."/./authenticate.php" );
	require_once("common.php");
	require_once("utils.php");
	require_once( "./Settings/UserManager.php" );

	emitStdProlog(".");
?>
<script>
var selectedItem = "LogOutItem";

function selectItem( newItem, loc )
{
	if ( selectedItem != null )
	{
		document.getElementById( selectedItem ).className="MenuItem";
	}
	if ( newItem != null )
	{
		document.getElementById( newItem ).className="MenuItemActive";
	}
	selectedItem = newItem;
	if ( loc != null )
	{
		parent.document.getElementById('work').src = loc;
	}
}

</script>
<?php 
emitStdHeader();
	if ( hasRole( Roles::REPORTER ) )
	{
?>	<a onclick="selectItem('StatsItem','./Report/StatsView.php'); return false;" href="#" class="MenuLink"><div id="StatsItem" class='MenuItem'>Statistics</div></a>
	<a onclick="selectItem('FinalizeItem','./Report/ReportFinalize.php'); return false;" href="#" class="MenuLink"><div id="FinalizeItem" class='MenuItem'>Reporter</div></a><?php
	}
	if ( AdminUser::isAuthorizer($_SESSION['USERID']) )
	{
?>	<a onclick="selectItem('AuthorizeItem','./Report/ReportAuthorize.php'); return false;" href="#" class="MenuLink"><div id="AuthorizeItem" class='MenuItem'>Authorize</div></a><?php
	}
?>	<a onclick="selectItem('TrackerItem2','./Tracker/TrackerView2.php'); return false;" href="#" class="MenuLink"><div id="TrackerItem2" class='MenuItem'>Track Time</div></a>
	<a onclick="selectItem('ReportItem','./Report/ReportView.php'); return false;" href="#" class="MenuLink"><div id="ReportItem" class='MenuItem'>Period Summary</div></a><?php
	if ( hasRole( Roles::USER_MANAGER ) )
	{
		?><a onclick="selectItem('ProjectItem','./Project/ProjectView.php'); return false;" href="#" class="MenuLink"><div id="ProjectItem" class='MenuItem'>Projects</div></a>
		<a onclick="selectItem('GroupManagementItem','./Settings/GroupView.php'); return false;" href="#" class="MenuLink"><div id="GroupManagementItem" class='MenuItem'>Groups</div></a><?php
	}	?>
		<a onclick="selectItem('UserManagementItem','./Settings/UserView.php'); return false;" href="#" class="MenuLink"><div id="UserManagementItem" class='MenuItem'>User Management</div></a><?php
	 
?>
	
<a onclick="selectItem('LogOutItem','./logout.php'); return false;" href="#" class="MenuLink"><div id="LogOutItem" class='MenuItem'>Log Out</div></a>

<div class='MenuBarVerticalSeparator'></div>
<div id='statusfooter'></div>
<?php emitStdFooter(); ?>
