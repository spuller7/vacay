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
require_once( "../Project/ProjectManager.php" );
require_once( "../Settings/UserManager.php" );

checkRole( Roles::USER_MANAGER );

emitStdProlog("..");

echo '<script>';

if ( hasRole( Roles::USER_MANAGER ) ) 
{
?>

function createProjCodeCat()
{
	var newCat = document.getElementById('newProjCodeCat').value;
	if ( newCat == "" ) {
		alert( "Please enter a valid project code category name!");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'NEW_CAT',
		'newCat' : newCat
		} );
	if (response == "OK") window.location.reload();
	else alert("Error creating project code category!");
}

function delProjCodeCat()
{
	var deleteCat = document.getElementById('catDelSel').value;
	if (deleteCat == -1)
	{
		alert("Please choose the project category which you wish to delete!");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'DEL_CAT',
		'projCat' : deleteCat
		} );
	if (response == "OK") window.location.reload();
	else alert("Error deleting project cat category!");
}

function renProjCodeCat()
{
	var renCat = document.getElementById('catRenSel').value;
	if (renCat == -1)
	{
		alert("Please choose the project category which you wish to rename!");
		return;
	}
	var renCatName = document.getElementById('catRenName').value;
	if ( renCatName == "" ) {
		alert( "Please enter a valid project code cartegory name!");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'REN_CAT',
		'projCat' : renCat,
		'Name' : renCatName
		} );
	if (response == "OK") window.location.reload();
	else alert("Error renaming project code category!");
}

function moveProjCodeCat()
{
	var moveProj = document.getElementById('moveCatProjSel').value;
	if (moveProj == -1)
	{
		alert("Please choose the project code which you wish to move to a new category!");
		return;
	}
	var moveCat = document.getElementById('moveCatCatSel').value;
	if (moveCat == -1)
	{
		alert("Please choose the project category which you wish to move the project to!");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'MOVE_PROJ_CAT',
		'ProjectID' : moveProj,
		'CatID' : moveCat
		} );
	if (response == "OK") window.location.reload();
	else alert("Error changing the project category!");
}

function createProjCode()
{
	var newCode = document.getElementById('newProjCode').value;
	if ( newCode == "" ) {
		alert( "Please enter a valid project code name!");
		return;
	}
	var newProjState = document.getElementById('newProjState').value;
	if (newProjState == -1)
	{
		alert("Please choose a state for the new project code!");
		return;
	}
	var newProjType = document.getElementById('newProjType').value;
	if (newProjType == -1)
	{
		alert("Please choose a type for the new project code!");
		return;
	}
	var newCat = document.getElementById('newProjCatSel').value;
	if (newCat == -1)
	{
		alert("Please choose a category for the new project code!");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'NEW_CODE',
		'newCode' : newCode,
		'newProjState' : newProjState,
		'newProjType' : newProjType,
		'newCatID' : newCat
		} );
	if (response == "OK") window.location.reload();
	else alert("Error creating project code!");
}

function deleteCode()
{
	var deleteCode = document.getElementById('codeDelSel').value;
	if (deleteCode == -1)
	{
		alert("Please choose the project code which you wish to delete!");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'DEL_CODE',
		'projCode' : deleteCode
		} );
	if (response == "OK") window.location.reload();
	else alert("Error deleting project code!");
}

function renameCode()
{
	var renCode = document.getElementById('codeRenSel').value;
	if (renCode == -1)
	{
		alert("Please choose the project code which you wish to rename!");
		return;
	}
	var renCodeName = document.getElementById('codeRenName').value;
	if ( renCodeName == "" ) {
		alert( "Please enter a valid project code name!");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'REN_CODE',
		'projCode' : renCode,
		'Name' : renCodeName
		} );
	if (response == "OK") window.location.reload();
	else alert("Error deleting project code!");
}

function setCodeState()
{
	var selectedCode = document.getElementById('codeStateCode').value;
	if (selectedCode == -1)
	{
		alert("Please choose the project code which you wish to modify!");
		return;
	}
	var selectedState = document.getElementById('codeStateState').value;
	if (selectedState == -1)
	{
		alert("Please choose the state to update to!");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'UPDATE_STATE',
		'projCode' : selectedCode,
		'projState' : selectedState
		} );
	if (response == "OK") window.location.reload();
	else alert("Error updating project code state!");
}

function setCodeType()
{
	var selectedCode = document.getElementById('codeTypeCode').value;
	if (selectedCode == -1)
	{
		alert("Please choose the project code which you wish to modify!");
		return;
	}
	var selectedType = document.getElementById('codeTypeType').value;
	if (selectedType == -1)
	{
		alert("Please choose the type to update to!");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'UPDATE_TYPE',
		'projCode' : selectedCode,
		'projType' : selectedType
		} );
	if (response == "OK") window.location.reload();
	else alert("Error updating project code type!");
}

function addMemberToCode()
{
	var projCode = document.getElementById('addMemberToCode').value;
	if ( projCode == -1 ) {
		alert( "Please select a project code to add the user to.");
		return;
	}
	var userid = document.getElementById('addMemberToCodeMember').value;
	if (userid == -1)
	{
		alert("Please choose a user to add to the project code.");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'MEMBER_ADD',
		'projCode' : projCode,
		'userId' : userid
		} );
	if (response == "OK") window.location.reload();
	else alert("Error adding user to project code!");
}

function addGroupToCode()
{
	var projCode = document.getElementById('addGroupToCode').value;
	if ( projCode == -1 ) {
		alert( "Please select a project code to add the group to.");
		return;
	}
	var groupid = document.getElementById('addGroupToCodeGroup').value;
	if (groupid == -1)
	{
		alert("Please choose a group to add to the project code.");
		return;
	}
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'GROUP_ADD',
		'projCode' : projCode,
		'groupId' : groupid
		} );
	if (response == "OK") window.location.reload();
	else alert("Error adding group to project code!");
}

function updateDelCodeMultiSel()
{
	var projCode = document.getElementById('memberDelCode').value;
	document.getElementById('UsersToDelCodeDiv').innerHTML = doPostDataMap( 'ProjectOp.php', {
		'action' : 'GETCODEMEMBERSMULTISEL',
		'projCode' : projCode
		} );
}

function delMemberFromCode()
{
	var projCode = document.getElementById('memberDelCode').value;
	if ( projCode == -1 ) {
		alert( "Please select a project code to remove the user from.");
		return;
	}
	var userid = document.getElementById('memberDelCodeUser');
	if ((userid.value == "") || (userid.value == -1))
	{
		alert("Please choose a user to remove from the project code.");
		return;
	}
	for (var i = 0; i < userid.options.length; i++)
	{
		if (userid.options[i].selected)
		{ 
			var response = doPostDataMap( 'ProjectOp.php', {
				'action' : 'MEMBER_DEL',
				'projCode' : projCode,
				'userId' : userid.options[i].value
				} );
			if (response != "OK") alert("Error removing user from project code!");
		}
	}
	window.location.reload();
}

function UdateCodeStateStateDropDown()
{
	var projCode = document.getElementById('codeStateCode').value;
	var state = doPostDataMap( 'ProjectOp.php', {
		'action' : 'GET_STATE',
		'projCode' : projCode
		} );

	var codeStateDropDown = document.getElementById('codeStateState');
	for (var i = 0; i < codeStateDropDown.options.length; i++)
	{
		if (codeStateDropDown.options[i].value == state)
		{ 
			codeStateDropDown.selectedIndex = i;
			break;
		}
	}
}

function UdateCodeTypeTypeDropDown()
{
	var projCode = document.getElementById('codeTypeCode').value;
	var state = doPostDataMap( 'ProjectOp.php', {
		'action' : 'GET_TYPE',
		'projCode' : projCode
		} );

	var codeTypeDropDown = document.getElementById('codeTypeType');
	for (var i = 0; i < codeTypeDropDown.options.length; i++)
	{
		if (codeTypeDropDown.options[i].value == state)
		{ 
			codeTypeDropDown.selectedIndex = i;
			break;
		}
	}
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
<div id='TitleBar' class='WorkPaneTitleBar'>Project Control Center</div>
<div id='ErrorMsgArea'></div>
<?php
	$codes = Project::listCodes();
	$states = Project::getAvailableStates();
	$types = Project::getAvailableTypes();
	$groups = AdminGroup::listGroups();
	$users = AdminUser::listUsers();
	$cats = ProjectCat::listCats();

	echo "<br/>";
		
	?><table class='WorkPaneListTable' cellpadding=0 cellspacing=0>
<?php //CREATE PROJECT CAT
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Create Cat', 'Create a new project category', 'createProjCodeCat();' );
?>
<td>
&nbsp;&nbsp;<input type="text" id="newProjCodeCat" value="Project Category" onFocus="if (this.value=='Project Category') this.value='';"/>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END CREATE PROJECT CAT
	//DELETE PROJECT CAT
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Delete Cat', 'Delete a project category', 'delProjCodeCat();' );
?>
<td>
&nbsp;&nbsp;<select id='catDelSel'>
<option id="SELECT" value="-1" SELECTED>SELECT CAT</option>
<?php

foreach ( $cats as $cat )
{
	echo '<option value="'.$cat->CatID.'">'.$cat->CatName.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END DELETE PROJECT CAT
//RENAME PROJECT CAT
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Rename Cat', 'Rename a project category', 'renProjCodeCat();' );
?>
<td>
&nbsp;&nbsp;<select id='catRenSel'>
<option id="SELECT" value="-1" SELECTED>SELECT CAT</option>
<?php

foreach ( $cats as $cat )
{
	echo '<option value="'.$cat->CatID.'">'.$cat->CatName.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<input type="text" id="catRenName" value="Project Category" onFocus="if (this.value=='Project Category') this.value='';"/>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END RENAME PROJECT CAT
	//MOVE PROJECT
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Move Code', 'Move a project into another category', 'moveProjCodeCat();' );
?>
<td>
&nbsp;&nbsp;<select id='moveCatProjSel'>
<option id="SELECT" value="-1" SELECTED>SELECT CODE</option>
<?php

foreach ( $codes as $code )
{
	echo '<option value="'.$code->ProjectID.'">'.$code->ProjectCode.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<select id='moveCatCatSel'>
<option id="SELECT" value="-1" SELECTED>SELECT CAT</option>
<?php

foreach ( $cats as $cat )
{
	echo '<option value="'.$cat->CatID.'">'.$cat->CatName.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END MOVE PROJECT
	//SORT ORDER LINK
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
<a href="./SortOrderEdit.php">Modify Sort Order</a>
</td></tr>
<?php	//END SORT ORDER LINK 
?>
</table><br/>
	<table class='WorkPaneListTable' cellpadding=0 cellspacing=0>
<?php //CREATE PROJECT CODE
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Create Code', 'Create a new project code', 'createProjCode();' );
?>
<td>
&nbsp;&nbsp;<input type="text" id="newProjCode" value="Project Code" onFocus="if (this.value=='Project Code') this.value='';"/>&nbsp;&nbsp;
<select id='newProjState'>
<option id="SELECT" value="-1" SELECTED>SELECT STATE</option>
<?php

foreach ( $states as $state )
{
	echo '<option value="'.$state.'">'.$state.'</option>'."\n";
}
?>
</select>
<select id='newProjType'>
<option id="SELECT" value="-1" SELECTED>SELECT TYPE</option>
<?php

foreach ( $types as $type )
{
	echo '<option value="'.$type.'">'.$type.'</option>'."\n";
}
?>
</select>
</select>
<select id='newProjCatSel'>
<option id="SELECT" value="-1" SELECTED>SELECT CAT</option>
<?php

foreach ( $cats as $cat )
{
	echo '<option value="'.$cat->CatID.'">'.$cat->CatName.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END CREATE PROJECT CODE
//DELETE CODE
/* ?>	<tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Delete', 'Delete project code', 'deleteCode();' );
?>
<td>
&nbsp;&nbsp;<select id='codeDelSel'>
<option id="SELECT" value="-1" SELECTED>SELECT CODE</option>
<?php

foreach ( $codes as $code )
{
	echo '<option value="'.$code->ProjectID.'">'.$code->ProjectCode.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php*/ //END DELETE CODE
//RENAME CODE
 ?>	<tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Rename', 'Rename project code', 'renameCode();' );
?>
<td>
&nbsp;&nbsp;<select id='codeRenSel'>
<option id="SELECT" value="-1" SELECTED>SELECT CODE</option>
<?php

foreach ( $codes as $code )
{
	echo '<option value="'.$code->ProjectID.'">'.$code->ProjectCode.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<input type="text" id="codeRenName" value="Project Code" onFocus="if (this.value=='Project Code') this.value='';"/>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END RENAME CODE
//SET CODE STATE
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Code State', 'Change the state of project code', 'setCodeState();' );
?>
<td>
&nbsp;&nbsp;<select id='codeStateCode' onchange='UdateCodeStateStateDropDown();'>
<option id="SELECT" value="-1" SELECTED>SELECT CODE</option>
<?php

foreach ( $codes as $code )
{
	echo '<option value="'.$code->ProjectID.'">'.$code->ProjectCode.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<select id='codeStateState'>
<option id="SELECT" value="-1" SELECTED>SELECT STATE</option>
<?php

foreach ( $states as $state )
{
	echo '<option value="'.$state.'">'.$state.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END CODE STATE
//SET CODE TYPE
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Code Type', 'Change the type of project code', 'setCodeType();' );
?>
<td>
&nbsp;&nbsp;<select id='codeTypeCode' onchange='UdateCodeTypeTypeDropDown();'>
<option id="SELECT" value="-1" SELECTED>SELECT CODE</option>
<?php

foreach ( $codes as $code )
{
	echo '<option value="'.$code->ProjectID.'">'.$code->ProjectCode.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<select id='codeTypeType'>
<option id="SELECT" value="-1" SELECTED>SELECT TYPE</option>
<?php

foreach ( $types as $type )
{
	echo '<option value="'.$type.'">'.$type.'</option>'."\n";
}
?>
</select>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END SET CODE TYPE
//ADD MEMBER
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Add Member', 'Add a user to the project code', 'addMemberToCode();' );
?>
<td>
&nbsp;&nbsp;<select id='addMemberToCode'>
<option id="SELECT" value="-1" SELECTED>SELECT CODE</option>
<?php

foreach ( $codes as $code )
{
	echo '<option value="'.$code->ProjectID.'">'.$code->ProjectCode.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<select id='addMemberToCodeMember'>
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
//ADD GROUP
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Add Group', 'Add an entire group to the project code', 'addGroupToCode();' );
?>
<td>
&nbsp;&nbsp;<select id='addGroupToCode'>
<option id="SELECT" value="-1" SELECTED>SELECT CODE</option>
<?php

foreach ( $codes as $code )
{
	echo '<option value="'.$code->ProjectID.'">'.$code->ProjectCode.'</option>'."\n";
}
?>
</select>
&nbsp;&nbsp;<select id='addGroupToCodeGroup'>
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
<?php //END ADD GROUP
//REMOVE MEMBER
 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
	<?php if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItemScript( 'Remove Member', 'Remove member of project code', 'delMemberFromCode();' );
?>
<td><table><tr><td valign="center">
&nbsp;&nbsp;<select id='memberDelCode' onChange="updateDelCodeMultiSel();">
<option id="SELECT" value="-1" SELECTED>SELECT CODE</option>
<?php

foreach ( $codes as $code )
{
	echo '<option value="'.$code->ProjectID.'">'.$code->ProjectCode.'</option>'."\n";
}
?>
</select></td><td>
<div id="UsersToDelCodeDiv"><select id='memberDelCodeUser' MULTIPLE SIZE=5 STYLE="width: 125px">
<option value=-1>PICK A USER</option>
</select></div></td></tr></table>
</td>
<?php
		emitMenuEnd();
	}?>
	</td></tr>
<?php //END REMOVE MEMBER
?>	</table>
<?php emitLoadingFooter(); ?>
