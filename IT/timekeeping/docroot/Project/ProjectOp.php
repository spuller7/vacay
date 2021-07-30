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
require_once( "./ProjectManager.php" );

checkRole( Roles::USER_MANAGER );

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

if ( !isset( $um_action ) )
{
	checkRole("!!BOGUS!!");
}

switch ( $um_action )
{
	case 'NEW_CAT':
		ProjectCat::create($um_newCat);
		echo "OK";
		break;
	case 'DEL_CAT':
		ProjectCat::delete($um_projCat);
		echo "OK";
		break;
	case 'REN_CAT':
		ProjectCat::updateCatName($um_projCat, $um_Name);
		echo "OK";
		break;
	case 'NEW_CODE':
		Project::create($um_newCode, $um_newCatID, $um_newProjState, $um_newProjType);
		//TODO: check for duplicate code
		echo "OK";
		break;
	case 'DEL_CODE':
		Project::deleteById($um_projCode);
		echo "OK";
		break;
	case 'REN_CODE':
		Project::updateProjectName($um_projCode, $um_Name);
		echo "OK";
		break;
	case 'GET_STATE':
		echo Project::getStateById($um_projCode);
		break;
	case 'UPDATE_STATE':
		Project::updateStateById($um_projCode, $um_projState);
		echo "OK";
		break;
	case 'GET_TYPE':
		echo Project::getTypeById($um_projCode);
		break;
	case 'UPDATE_TYPE':
		Project::updateTypeById($um_projCode, $um_projType);
		echo "OK";
		break;
	case 'MEMBER_ADD':
		Project::addMembershipById($um_projCode, $um_userId);
		echo "OK";
		break;
	case 'MEMBER_DEL':
		Project::removeMembershipById($um_projCode, $um_userId);
		echo "OK";
		break;
	case 'GROUP_ADD':
		Project::addEntireGroupById($um_projCode, $um_groupId);
		echo "OK";
		break;
	case 'GETCODEMEMBERSMULTISEL':
		echo "<select id='memberDelCodeUser' MULTIPLE SIZE=5 STYLE='width: 125px'>";
		$users = Project::listMembershipById($um_projCode);
		if (count($users) > 0)
		{
			foreach ($users as $user)
			{
				echo "<option value=\"".$user->UserID."\">".$user->Username."</option>";
			}
		}
		else
		{
			echo "<option value=-1>PICK A USER</option>";
		}
		echo "</select>";
		break;
	case 'MEMBER_DEL':
		Project::removeMembershipById($um_projCode, $um_userId);
		echo "OK";
		break;
	case 'CLEAN_UP_SORT_ORDER':
		ProjectCat::cleanUpSortOrder();
		echo "OK";
		break;
	case 'PROJECT_SORT_ORDER':
		Project::setSortOrder($um_ProjectID, $um_Pos);
		Project::setSortOrder($um_ProjectID2, $um_Pos2);
		echo "OK";
		break;
	case 'CAT_SORT_ORDER':
		ProjectCat::setSortOrder($um_CatID, $um_Pos);
		ProjectCat::setSortOrder($um_CatID2, $um_Pos2);
		echo "OK";
		break;
	case 'MOVE_PROJ_CAT':
		ProjectCat::updateCat($um_ProjectID, $um_CatID);
		echo "OK";
		break;
	default:
		//don't let them try and hunt out the commands...
		checkRole( "!!BOGUS!!" );
}

?>
