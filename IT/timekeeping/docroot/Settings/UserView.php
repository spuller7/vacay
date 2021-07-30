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

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

emitStdProlog("..");

echo '<script>';

if ( hasRole( Roles::USER_MANAGER ) ) 
{
?>

function deleteUser(userid, username)
{
	//don't let them delete user if user is a primary group authorizer
	if ( confirm( "Are you sure you want to delete "+ username +"?" ) )
	{
		doPostDataMap( './UserOp.php', 
			{
				"action" : "DELETE",
				"Userid" : userid
			} );
		document.location.reload();
	}
}

function UpdateState( userid, dropDown )
{
	var response = doPostDataMap( './UserOp.php',
		{
			"action" : "UPDATE_STATE",
			"Userid" : userid,
			"State" : dropDown.options[dropDown.selectedIndex].value
		} );
		if (response != "OK" ) alert( "Failed to update user's state!");
}

function updatePTStatus(userid, checkbox)
{
	var response = doPostDataMap( './UserOp.php',
	{
		"action" : "UPDATE_PARTTIME",
		"Userid" : userid,
		"pt" : checkbox.checked
	} );
	if (response != "OK" ) alert( "Failed to update user's state!");
}

function resetPassword( userid, username )
{
	var newPassword = prompt('Enter new password','');
	if ( (newPassword == null) || (newPassword == "")  )
	{
		alert("Password must not be blank!");
	}
	else
	{
		
	var response = doPostDataMap( './UserOp.php', 
		{ 
			"action" : "RESET_PASSWORD",
			"Userid" : userid, 
			"Username" : username,
			"newPassword" : newPassword 
		} );
		if ( response != "OK" )
		{
			alert( "Failed to change password: [" + response + "]" + (response=="OK") );
		}
		else
		{
			document.location.reload();
			alert("Password changed");
		}
	}
}

<?php
}
?>
function CancelTextEdit( <?php echo (hasRole( Roles::USER_MANAGER )?'Userid, ':''); ?>Text, identifier )
{
	var divToEdit = document.getElementById( <?php echo (hasRole( Roles::USER_MANAGER )?'Userid':'"'.$_SESSION['USERID'].'"'); ?> + identifier );
	var innerHTML = "<a href='#' title='Edit this text' onclick=\"EditText( <?php echo (hasRole( Roles::USER_MANAGER )?"'\" + Userid + \"', ":''); ?>'" + Text + "', '" + identifier + "' ); return false;\">";
	innerHTML += "<img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'>";
	innerHTML += "</a>" + Text;
	divToEdit.innerHTML = innerHTML;
}

function UpdateTextEdit( <?php echo (hasRole( Roles::USER_MANAGER )?'Userid, ':''); ?>Text, identifier )
{
	var newText = document.getElementById( <?php echo (hasRole( Roles::USER_MANAGER )?"Userid":"\"".$_SESSION['USERID']."\""); ?> + identifier + "Textarea" ).value;
	newText = newText.replace(/\n/g,"").replace(/\s/g,' ');
	if (/[^\w\s.@]/.test(newText))
	{
		alert("Invalid characters entered!");
		return;
	}
	var response = doPostDataMap( './UserOp.php',
		{
			"action" : identifier,
			"Userid" : <?php echo (hasRole( Roles::USER_MANAGER )?"Userid":$_SESSION['USERID']); ?>,
			"Text" : newText
		} );
	if (response != "OK" ) alert( "Failed to update text!");
	CancelTextEdit( <?php echo (hasRole( Roles::USER_MANAGER )?"Userid, ":''); ?>newText, identifier );
}

function EditText( <?php echo (hasRole( Roles::USER_MANAGER )?'Userid, ':''); ?>Text, identifier)
{
	var divToEdit = document.getElementById( <?php echo (hasRole( Roles::USER_MANAGER )?"Userid":"\"".$_SESSION['USERID']."\""); ?> + identifier );
	var innerHTML = "<table width=90%><tr><td><textarea style='width: 100%' wrap=on id='" + <?php echo (hasRole( Roles::USER_MANAGER )?"Userid":"\"".$_SESSION['USERID']."\""); ?> + identifier + "Textarea'>";
	innerHTML += Text;
	innerHTML += "</textarea></td></tr><tr><td align='left'>";
	innerHTML += "<a href='#' onclick=\"UpdateTextEdit( <?php echo (hasRole( Roles::USER_MANAGER )?"'\" + Userid + \"', ":''); ?>'" + Text + "', '" + identifier + "' ); return false;\"><img border=0 src='../images/ok_16.gif'><small>accept</small></a>&nbsp;";
	innerHTML += "<a href='#' onclick=\"CancelTextEdit( <?php echo (hasRole( Roles::USER_MANAGER )?"'\" + Userid + \"', ":''); ?>'" + Text + "', '" + identifier + "' ); return false;\"><img border=0 src='../images/cancel_16.gif'><small>cancel</small></a>";
	innerHTML += "</td></tr></table>";
	divToEdit.innerHTML = innerHTML;
	document.getElementById( <?php echo (hasRole( Roles::USER_MANAGER )?"Userid":"\"".$_SESSION['USERID']."\""); ?> + identifier + "Textarea" ).select();
	document.getElementById( <?php echo (hasRole( Roles::USER_MANAGER )?"Userid":$_SESSION['USERID']); ?> + identifier + "Textarea" ).focus();
}

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
<div id='TitleBar' class='WorkPaneTitleBar'>User Management</div>
<div id='ErrorMsgArea'></div>
<?php
	if ( hasRole( Roles::USER_MANAGER ) )
	{
			if (isset($um_Groups) && ($um_Groups != -1)) $users = AdminGroup::listUsersById($um_Groups);
			else $users = AdminUser::listUsers();
		
			emitMenuStart();
			emitMenuItem( 'Create', 'Create new user', 'UserCreate.php' );
			emitMenuEnd();
		?><form method="POST">Show only group: <select name="Groups" onChange="this.form.submit();"><option value=-1>All</option><?php
		$groups = AdminGroup::listGroups();
		if (!isset($um_Groups)) $um_Groups = -1;
		foreach ($groups as $group)
		{
			echo "<option value=\"".$group->GroupID."\"".($group->GroupID==$um_Groups?" SELECTED":"").">".$group->GroupName."</option>";
		}
	}
	else
	{
		$users[] = AdminUser::retrieve($_SESSION['USERID']);
		echo "<br/>";
	}
	?></select></form><?php
	$cols = 5;
	if ( hasRole( Roles::USER_MANAGER ) || (hasRole( Roles::TRACKER_MANAGER )) )
	{
		$cols += 3;
	}
	if ( hasRole( Roles::TRACKER_MANAGER ) )
		$cols += 1;
		
	?><table class='WorkPaneListTable' cellpadding=0 cellspacing=0>
	<tr><td colspan='<?php echo $cols?>'><div id='ViewTable'></div></td></tr>
	<tr class='WorkPaneViewListTitle'><th>User name</th><th>Full name</th><th>Last update</th>
	<th>Groups</th><?php 
	if ( (hasRole( Roles::TRACKER_MANAGER )) || (hasRole( Roles::USER_MANAGER )) )
	{
		echo '<th>State</th><th>Part Time</th><th>&nbsp;</th>';
	}
	if ( hasRole( Roles::TRACKER_MANAGER))
		echo '<th>&nbsp;</th>';
	echo '<th>&nbsp;</th></tr>';
	$iRowCount = 0;
	$adminRoles = roles();
	if (count($users) > 0)
	{
  		$statesEnum = AdminUser::getAvailableStates();
		foreach ( $users as $user)
		{
			?><tr class='WorkPaneListAlt<?php echo 1+($iRowCount % 2) ?>'><td class='WorkPaneTD'>
			<?php 
			if ( hasRole( Roles::USER_MANAGER ) )
			{
				if (username() != $user->Username)
				{
					echo '<a href=\'UserEdit.php?Userid='.$user->UserID.'\'><img border=\'0\'  style=\'vertical-align: bottom\' src=\'../images/zoom_16.gif\'>'.$user->Username.'</a>';
				}
				else
				{
					echo '<img border=\'0\'  style=\'vertical-align: bottom\' src=\'../images/lock_16.gif\'>'.$user->Username;
				}
			}
			else
			{
				echo $user->Username;
			}
			?></td>
			<td class='WorkPaneTD'><div id="<?php echo $user->UserID; ?>UPDATE_FULLNAME"><a href='#' title='Edit this text' onclick="EditText( <?php echo (hasRole( Roles::USER_MANAGER )?"'".$user->UserID."', ":''); ?>'<?php echo $user->FullName; ?>', 'UPDATE_FULLNAME' ); return false;"><img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'></a><?php echo $user->FullName; ?></div></td>
			<td class='WorkPaneTD'><?php echo date('m/d/Y h:i A',strtotime($user->LastUpdate)) ?></td>
			<?php
			$groups = AdminUser::listGroupsById($user->UserID);
			if (count($groups) > 0)
			{
				$groupList = "<table border=1 cellspacing=0>";
				$idx = 0;
				foreach ( $groups as $group )
				{
					if ( $idx % 4 == 0 )
					{
						$groupList .= "<tr>";
					}
					$groupList .= "<td>";
					$groupList .= $group->GroupName;
					$groupList .= "</td>";
					if ( $idx % 4 == 3 )
					{
						$groupList .= "</tr>";
					}
					$idx ++;
				}
				
				if ( $idx > 3 )
				{
					switch ( $idx % 4 )
					{
						case 1:
							$groupList .= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
							break;
						case 2:
							$groupList .= "<td>&nbsp;</td><td>&nbsp;</td>";
							break;
						case 3:
							$groupList .= "<td>&nbsp;</td>";
							break;
					}
				}
				if ( $idx % 4 > 0 )
				{
					$groupList .= "</tr>";
				}
				$groupList .= "</table>";
			}
			else $groupList = "";
			echo '<td class=\'WorkPaneTD\'>'.$groupList.'</td>';
			if ( hasRole( Roles::USER_MANAGER )  )
			{
				?><td class="WorkPaneTD">
				<select onchange="UpdateState('<?php echo $user->UserID; ?>',this);">
				<?php
				foreach($statesEnum as $state)
				{
					echo "<option value=\"".$state."\"";
					if ($state == $user->State) echo " SELECTED";
					echo ">".$state."</option>";
				}
				?>
				</select></td>
				<td class="WorkPaneTD">
				<input type="checkbox" name="pt<?php echo $user->UserID; ?>" <?php echo ($user->PartTime ? "CHECKED" : ""); ?> onclick="updatePTStatus('<?php echo $user->UserID; ?>',this);" />
				</td><?php
			}
			$roles = Roles::getRoles( $user->UserID );
			$canModify = true;
			foreach ( array_keys($roles) as $role )
			{
				if ( !hasRole( $role ) )
				{
					$canModify = false;
					break;
				}
			}
			echo '<td class=\'WorkPaneTD\'>';
			if ( $canModify )
			{
				if ( hasRole( Roles::USER_MANAGER )  )
					echo '<a href=\'#\' onclick=\'resetPassword( "'.$user->UserID.'", "'.$user->Username.'" ); return false;\'><img border=\'0\'  style=\'vertical-align: bottom\' src=\'../images/password_16.gif\'>Reset Password</a>';
				else
					echo '<a href=\'./resetPassword.php\'><img border=\'0\'  style=\'vertical-align: bottom\' src=\'../images/password_16.gif\'>Change Password</a>';
			}
			echo '</td>';
			if ( hasRole( Roles::USER_MANAGER )  )
			{
				echo '<td class=\'WorkPaneTD\'>';
				if ( $canModify )
				{
					if (userid() != $user->UserID)
					{
						echo '<a href=\'#\' onclick=\'deleteUser( "'.$user->UserID.'", "'.$user->Username.'" ); return false;\'><img  style=\'vertical-align: bottom\' border=\'0\' src=\'../images/trash_16.gif\'>Delete</a>';
					}
				}
				echo '</td>';
			}
			if ( hasRole ( Roles::ADMINISTRATOR ) )
			{
				echo '<td class=\'WorkPaneTD\'>';
				echo '<a href=\'./AuditTrail.php?UserID='.$user->UserID.'\'>Audit Trail</a>';
				echo '</td>';
			}
			echo '</tr>';
			$iRowCount++;
		}
	}
?>
<tr class='WorkPaneSectionFooter'>
	<td colspan=<?php echo $cols; ?>>
		<div class='RoundFooter'></div>
	</td>
</tr>
</table>
<?php emitLoadingFooter(); ?>
