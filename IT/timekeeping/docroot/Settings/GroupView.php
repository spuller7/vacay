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
require_once( "./UserManager.php" );

checkRole( Roles::USER_MANAGER );

emitStdProlog("..");

echo '<script>';

if ( hasRole( Roles::USER_MANAGER ) ) 
{
?>

function createGroup()
{
	var newGroupName = document.getElementById('newGroupName').value;
	if ( newGroupName == "" ) {
		alert( "Please enter a valid new group name!");
		return;
	}
	var newGroupAuthID = document.getElementById('newGroupAuthSelect').value;
	if (newGroupAuthID == -1)
	{
		alert("Please choose an Authorizer for the new group " + newGroupName + "!");
		return;
	}
	var response = doPostDataMap( 'UserOp.php', {
		'action' : 'NEW_GROUP',
		'newGroupName' : newGroupName,
		'newGroupAuthId' : newGroupAuthID
		} );
	if (response == "OK") window.location.reload();
	else alert("Error creating group!");
}

function deleteGroup()
{
	var groupid = document.getElementById('groupDelSel').value;
	if ( groupid == -1 ) {
		alert( "Please select the group from the drop down which you wish to delete!");
		return;
	}
	var response = doPostDataMap( 'UserOp.php', {
		'action' : 'DEL_GROUP',
		'groupId' : groupid
		} );
	if (response == "OK")
	{
		alert ("Group deleted successfully!");
		window.location.reload();
	}
	else alert("Error deleting group! Are there authorizers for the group?");
}

function renameGroup()
{
	var groupid = document.getElementById('groupRenSel').value;
	if ( groupid == -1 ) {
		alert( "Please select the group from the drop down which you wish to delete!");
		return;
	}
	var newGroupName = document.getElementById('groupRenName').value;
	if ( newGroupName == "" ) {
		alert( "Please enter a valid new group name!");
		return;
	}
	var response = doPostDataMap( 'UserOp.php', {
		'action' : 'REN_GROUP',
		'groupId' : groupid,
		'Name' : newGroupName
		} );
	if (response == "OK")
	{
		alert ("Group renamed successfully!");
		window.location.reload();
	}
	else alert("Error renaming group!");
}

function memberAdd()
{
	var groupid = document.getElementById('memberAddGroup').value;
	if ( groupid == -1 ) {
		alert( "Please select a group to add the user to.");
		return;
	}
	var userid = document.getElementById('memberAddUser').value;
	if (userid == -1)
	{
		alert("Please choose a user to add to the group.");
		return;
	}
	var response = doPostDataMap( 'UserOp.php', {
		'action' : 'MEMBER_ADD',
		'groupId' : groupid,
		'userId' : userid
		} );
	if (response == "OK") window.location.reload();
	else alert("Error adding user to group!");
}

function updateDelGroupMultiSel()
{
	var groupid = document.getElementById('memberDelGroup').value;
	document.getElementById('UsersToDelDiv').innerHTML = doPostDataMap( 'UserOp.php', {
		'action' : 'GETGROUPMEMBERSMULTISEL',
		'groupId' : groupid
		} );
}

function memberDel()
{
	var groupid = document.getElementById('memberDelGroup').value;
	if ( groupid == -1 ) {
		alert( "Please select a group to remove the user from.");
		return;
	}
	var userid = document.getElementById('memberDelUser');
	if ((userid.value == "") || (userid.value == -1))
	{
		alert("Please choose a user to remove from the group.");
		return;
	}
	for (var i = 0; i < userid.options.length; i++)
	{
		if (userid.options[i].selected)
		{ 
			var response = doPostDataMap( 'UserOp.php', {
				'action' : 'MEMBER_DEL',
				'groupId' : groupid,
				'userId' : userid.options[i].value
				} );
			if (response != "OK") alert("Error removing user from group!");
		}
	}
	window.location.reload();
}

function addAuth()
{
	var groupid = document.getElementById('addAuthGroup').value;
	if ( groupid == -1 ) {
		alert( "Please select a group to add authorizer to.");
		return;
	}
	var userid = document.getElementById('addAuthUser').value;
	if (userid == -1)
	{
		alert("Please choose a user to add as an authorizer of group.");
		return;
	}
	var response = doPostDataMap( 'UserOp.php', {
		'action' : 'AUTH_ADD',
		'groupId' : groupid,
		'userId' : userid
		} );
	if (response == "OK") window.location.reload();
	else alert("Error adding group authorizer!");
}

function updateDelAuthMultiSel()
{
	var groupid = document.getElementById('authDelGroup').value;
	document.getElementById('AuthsToDelDiv').innerHTML = doPostDataMap( 'UserOp.php', {
		'action' : 'GETGROUPAUTHSMULTISEL',
		'groupId' : groupid
		} );
}

function authDel()
{
	var groupid = document.getElementById('authDelGroup').value;
	if ( groupid == -1 ) {
		alert( "Please select a group to remove an authorizer from.");
		return;
	}
	var userid = document.getElementById('authDelUser');
	if (userid.value == "")
	{
		alert("Please choose a user to remove as an authorizer of the group.");
		return;
	}
	if (userid.value == -1)
	{
		alert("You cannot remove the primary authorizer!");
		return;
	}
	for (var i = 0; i < userid.options.length; i++)
	{
		if (userid.options[i].selected)
		{
			if (userid.options[i].value == -1)
			{
				alert("Can not delete the primary authorizer of a group!");
				return;
			}
			else
			{
				var response = doPostDataMap( 'UserOp.php', {
					'action' : 'AUTH_DEL',
					'groupId' : groupid,
					'userId' : userid.options[i].value
					} );
				if (response != "OK") alert("Error removing user from group!");
			}
		}
	}
	window.location.reload();
}

function setPrimAuth()
{
	var groupid = document.getElementById('setPrimAuthGroup').value;
	if ( groupid == -1 ) {
		alert( "Please select the group you want to change the primary authorizer of.");
		return;
	}
	var userid = document.getElementById('setPrimAuthUser').value;
	if (userid == -1)
	{
		alert("Please select the user you wish to make the primary authorizer of group.");
		return;
	}
	var response = doPostDataMap( 'UserOp.php', {
		'action' : 'PRIM_AUTH_UPDATE',
		'groupId' : groupid,
		'userId' : userid
		} );
	if (response == "OK") window.location.reload();
	else alert("Error updating primary authorizer!");
}

<?php
}
?>
window.onload=function(){
if ( NiftyCheck() )
{ 
	Rounded("div.WorkPaneTitleBar","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>"); 
	RoundedTop("div#ViewTable","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>", "small" );
	RoundedBottom("div.RoundFooter","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>");
}
<?php emitLoadingJS(); ?>
}
</script>
<?php emitLoadingHeader(); ?>
<div id='TitleBar' class='WorkPaneTitleBar'>Group Management</div>
<div id='ErrorMsgArea'></div>
<?php	
	$groups = AdminGroup::listGroups();
	$users = AdminUser::listUsers();

	echo "<br/>";
		
	?><table class='WorkPaneListTable' cellpadding=0 cellspacing=0>
<?php //CREATE GROUP

?>	<tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Create', 'Create new group', 'createGroup();' );
?>
<td>
&nbsp;&nbsp;<input type="text" id="newGroupName" value="Group Name" onFocus="if (this.value=='Group Name') this.value='';"/>&nbsp;&nbsp;
<select id='newGroupAuthSelect'>
<option id="SELECT" value="-1" SELECTED>SELECT AUTHORIZER</option>
<?php

foreach ( $users as $user )
{
	echo '<option value="'.$user->UserID.'">'.$user->Username.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END CREATE GROUP
//DELETE GROUP
 ?>	<tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Delete', 'Delete group', 'deleteGroup();' );
?>
<td>
&nbsp;&nbsp;<select id='groupDelSel'>
<option id="SELECT" value="-1" SELECTED>SELECT GROUP</option>
<?php

foreach ( $groups as $group )
{
	echo '<option value="'.$group->GroupID.'">'.$group->GroupName.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END DELETE GROUP
//RENAME GROUP

?>	<tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Rename', 'Rename group', 'renameGroup();' );
?>
<td>
&nbsp;&nbsp;<select id='groupRenSel'>
<option id="SELECT" value="-1" SELECTED>SELECT GROUP</option>
<?php

foreach ( $groups as $group )
{
	echo '<option value="'.$group->GroupID.'">'.$group->GroupName.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<input type="text" id="groupRenName" value="New Group Name" onFocus="if (this.value=='New Group Name') this.value='';"/>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END RENAME GROUP
//ADD MEMBER
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Add Member', 'Add member to group', 'memberAdd();' );
?>
<td>
&nbsp;&nbsp;<select id='memberAddGroup'>
<option id="SELECT" value="-1" SELECTED>SELECT GROUP</option>
<?php

foreach ( $groups as $group )
{
	echo '<option value="'.$group->GroupID.'">'.$group->GroupName.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<select id='memberAddUser'>
<option id="SELECT" value="-1" SELECTED>SELECT USER</option>
<?php

foreach ( $users as $user )
{
	echo '<option value="'.$user->UserID.'">'.$user->Username.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END ADD MEMBER
//REMOVE MEMBER
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Remove Member', 'Remove member of group', 'memberDel();' );
?>
<td><table><tr><td valign="center">
&nbsp;&nbsp;<select id='memberDelGroup' onChange="updateDelGroupMultiSel();">
<option id="SELECT" value="-1" SELECTED>SELECT GROUP</option>
<?php

foreach ( $groups as $group )
{
	echo '<option value="'.$group->GroupID.'">'.$group->GroupName.'</option>'."\n";
}
?>
</select></td><td>
<div id="UsersToDelDiv"><select id='memberDelUser' MULTIPLE SIZE=5 STYLE="width: 125px">
<option value=-1>PICK A GROUP</option>
</select></div></td></tr></table>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END REMOVE MEMBER
//ADD AUTHORIZER
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Add Auth', 'Add group authorizer', 'addAuth();' );
?>
<td>
&nbsp;&nbsp;<select id='addAuthGroup'>
<option id="SELECT" value="-1" SELECTED>SELECT GROUP</option>
<?php

foreach ( $groups as $group )
{
	echo '<option value="'.$group->GroupID.'">'.$group->GroupName.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<select id='addAuthUser'>
<option id="SELECT" value="-1" SELECTED>SELECT USER</option>
<?php

foreach ( $users as $user )
{
	echo '<option value="'.$user->UserID.'">'.$user->Username.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END ADD AUTHORIZER
//REMOVE AUTHORIZER
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Remove Auth', 'Remove authorizer of group', 'authDel();' );
?>
<td><table><tr><td valign="center">
&nbsp;&nbsp;<select id='authDelGroup' onChange="updateDelAuthMultiSel();">
<option id="SELECT" value="-1" SELECTED>SELECT GROUP</option>
<?php

foreach ( $groups as $group )
{
	echo '<option value="'.$group->GroupID.'">'.$group->GroupName.'</option>'."\n";
}
?>
</select></td><td>
<div id="AuthsToDelDiv"><select id='authDelUser' MULTIPLE SIZE=5 STYLE="width: 125px">
<option value=-1>PICK A GROUP</option>
</select></div></td></tr></table>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END REMOVE AUTHORIZER
//SET PRIMARY AUTHORIZER
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Set Prim Auth', 'Set primary authorizer of group', 'setPrimAuth();' );
?>
<td>
&nbsp;&nbsp;<select id='setPrimAuthGroup'>
<option id="SELECT" value="-1" SELECTED>SELECT GROUP</option>
<?php

foreach ( $groups as $group )
{
	echo '<option value="'.$group->GroupID.'">'.$group->GroupName.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<select id='setPrimAuthUser'>
<option id="SELECT" value="-1" SELECTED>SELECT USER</option>
<?php

foreach ( $users as $user )
{
	echo '<option value="'.$user->UserID.'">'.$user->Username.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END SET PRIMARY AUTHORIZER
 ?>	</table>
<?php emitLoadingFooter(); ?>
