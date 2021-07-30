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
require_once( "../Tracker/TrackerManager.php" );

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

if ( !isset( $um_action ) )
{
	checkRole("!!BOGUS!!");
}

$checkedEm = false;

if ( isset($um_Username ) )
{
	if ($um_Username != $_SESSION['USER_NAME'])
	{
		checkRole( Roles::USER_MANAGER );
	}
	$checkedEm = true;
}

if ( isset( $um_Userid ) )
{
	if ($um_Userid != $_SESSION['USERID'])
	{
		checkRole( Roles::USER_MANAGER );
	}
	$checkedEm = true;
}

if (!$checkedEm) checkRole( Roles::USER_MANAGER );

switch ( $um_action )
{
	case 'DELETE':
		AdminUser::deleteById( $um_Userid );
		break;
	case 'RESET_PASSWORD':
		//should hash password before you send it here to prevent network sniffing
		AdminUser::updatePasswordById( $um_Userid, hashPassword( $um_Username, $um_newPassword ) );
		echo "OK";
		break;
	case 'NEW_GROUP':
		AdminGroup::createById($um_newGroupName, $um_newGroupAuthId);
		echo "OK";
		break;
	case 'DEL_GROUP':
		AdminGroup::deleteById($um_groupId);
		echo "OK";
		break;
	case 'REN_GROUP':
		AdminGroup::updateGroupName($um_groupId, $um_Name);
		echo "OK";
		break;
	case 'MEMBER_ADD':
		AdminGroup::addMembershipById($um_userId, $um_groupId);
		echo "OK";
		break;
	case 'GETGROUPMEMBERSMULTISEL':
		$users = AdminGroup::listUsersById($um_groupId);
		echo "<select id='memberDelUser' MULTIPLE SIZE=5 STYLE='width: 125px'>";
		if (count($users)>0)
		{
			foreach ($users as $user)
			{
				echo "<option value=\"".$user->UserID."\">".$user->Username."</option>";
			}
		}
		else
		{
			echo "<option value=-1>PICK A GROUP</option>";
		}
		echo "</select>";
		break;
	case 'MEMBER_DEL':
		AdminGroup::removeMembershipById($um_userId, $um_groupId);
		echo "OK";
		break;
	case 'AUTH_ADD':
		AdminGroup::addAuthorizerById($um_userId, $um_groupId);
		echo "OK";
		break;
	case 'GETGROUPAUTHSMULTISEL':
		echo "<select id='authDelUser' MULTIPLE SIZE=5 STYLE='width: 125px'>";
		$primaryAuth = AdminGroup::getPrimaryAuthorizerById($um_groupId);
		if ($primaryAuth != "") echo "<option value=\"-1\">*".$primaryAuth->Username."*</option>";
		$users = AdminGroup::getAuthorizersById($um_groupId);
		if (count($users) > 0)
		{
			foreach ($users as $user)
			{
				echo "<option value=\"".$user->UserID."\">".$user->Username."</option>";
			}
		}
		echo "</select>";
		break;
	case 'AUTH_DEL':
		AdminGroup::removeAuthorizerById($um_userId, $um_groupId);
		echo "OK";
		break;
	case 'PRIM_AUTH_UPDATE':
		AdminGroup::setPrimaryAuthorizerById($um_userId, $um_groupId);
		echo "OK";
		break;
	case 'UPDATE_STATE':
		AdminUser::updateStateById($um_Userid, $um_State);
		echo "OK";
		break;
	case 'UPDATE_PARTTIME':
		if ($um_pt=='false') $um_pt=false;
		else $um_pt=true;
		AdminUser::updatePartTimeStatusById($um_Userid, $um_pt);
		echo "OK";
		break;
	case 'UPDATE_FULLNAME':
		$text = htmlentities($um_Text,ENT_NOQUOTES,"UTF-8");
		AdminUser::updateFullNameById($um_Userid, $text);
		echo "OK";
		break;
	case 'UPDATE_USER_NAME':
		$text = htmlentities($um_Text,ENT_NOQUOTES,"UTF-8");
		AdminUser::updateUsernameById($um_Userid, $text);
		echo "OK";
		break;
	case 'GET_AUDIT_TRAIL':
		$audits = AuditRecord::retrieveAuditRecordByDay($um_Userid, $um_Date);
		$periodmap = Array();
		if ($audits != null)
		{
			foreach ($audits as $audit)
			{
				$user = AdminUser::getUserName($audit->ChangeUserID);
				$project = Project::getProjectCodeFromID($audit->ProjectID);
				echo $user.",".$audit->ChangeDate.",".$project.",".$audit->State.",".$audit->Period."\n";
				$periodmap[$audit->Period] = true;
			}
		}
		$units = TrackerUnit::getAllTrackerUnitsForDateByIdForAudit($um_Userid, $um_Date);
		if ($units != null)
		{
			foreach ($units as $unit)
			{
				$user = AdminUser::getUserName($unit->ChangeUserID);
				$project = Project::getProjectCodeFromID($unit->Projectid);
				echo $user.",".$unit->ChangeDate.",".$project.",".$unit->State.",".$unit->Period."\n";
				if ($periodmap[$unit->Period]) $periodmap[$unit->Period] = false; 
			}
		}
		foreach ($periodmap as $idx => $period)
			if ($period) echo $idx."\n";
		break;
	default:
		//don't let them try and hunt out the commands...
		checkRole( "!!BOGUS!!" );
}

?>
