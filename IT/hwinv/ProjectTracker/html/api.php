<?php
require_once( dirname( __FILE__ )."/./docroot/authenticate.php" );
require_once( dirname( __FILE__ )."/./docroot/DBHelper.php" );
require_once( dirname( __FILE__ )."/projTrackDB.php" );

 $cmd = "";
if(isset($_POST["cmd"]))
	$cmd = $_POST["cmd"];
if(isset($_GET["cmd"]))
	$cmd = $_GET["cmd"];

switch($cmd) {
	case 'activateProjects':
		activateProjects($_POST["activeProjects"]);
		break;
	case 'archiveProjects':
		archiveProjects($_POST["archivedProjects"]);
		break;
	case 'addRow':
		$json = addRow($_POST["projectID"]);
		echo $json;
		break;
	case 'autoSave': 
		autoSave( $_GET["projectID"], $_GET["color"], $_GET["text"], $_GET["entryState"], $_GET["isSummary"], $_GET["id"]);
		break;
	case 'changeName':
		PTChangeUserFullName( $_POST["UserID"], $_POST["newName"]);
		echo "User Name Updated.";
		break;
	case 'changeUserRoles':
		changeUserRoles( $_POST["usersList"], $_POST["role"]);
		break;
	case 'deleteProject':
		deleteProject($_POST["projectID"]);
		break;
	case 'exportReport':
		$data = exportReport($_POST['reportType'], $_POST['startDate'], $_POST['endDate']);
		echo $data;
		break;
	case 'getEndDate':
		$data = PTGetEndDateByProject( $_POST["projectID"]);
		echo $data;
		break;
	case 'getManagers':
		checkRole(Roles::ADMINISTRATOR);
		$data = PTGetOrderedManagersListForProject( $_POST["projectID"]  );
		echo json_encode($data);
		break;
	case 'getName':
		$name = PTGetNamebyUserID($_SESSION["USERID"]);
		echo $name;
		break;
	case 'getRDstate': 
		$RDstate = PTGetRDstate($_POST["projID"]); 
		if($RDstate == TRUE || $RDstate == FALSE)
			echo $RDstate;
		break;
	case 'getUsers':
		$usersList = getUsers($_POST["projectID"]);
		echo $usersList;
		break;
	case 'lockEntries':
		lockEntries();
		break;
	case 'lockStatus':
		$lockStatusData = lockStatus($_POST["page"]);
		echo $lockStatusData;
		break;
	case 'moveUsers':
		moveUsers($_POST["projectID"], $_POST["users"], $_POST["unassignedUsers"]);
		break;
	case 'newProjDatabase':
		newProjDatabase( $_POST['name'], $_POST['startDate'], $_POST['endDate'], $_POST['manager'], $_POST['is_RD'], $_POST['users']);
		break;
	case 'setRDstate':
		PTSetRDstate($_POST["projectID"], $_POST["RDstate"]);
		echo "called setRDstate";
		break;
	case 'statusIcons':
		$statusIconsData = statusIcons();
		echo $statusIconsData;
		break;
	case 'unlockEntries':
		$data = unlockEntries($_POST["UserID"]);
		echo $data;
		break;
	case 'updateDate':
		$data = updateDate($_GET["date"]);
		echo $data;
		break;
	case 'updateProjectInfo':
		PTSetRDstate($_POST["projectID"], $_POST["RDstate"]);
		PTUpdateProjectManager( $_POST["projectID"], $_POST["projectManagerID"] );
		PTRenameProject( $_POST['projectID'], $_POST['PROJECT_NAME'] );
		PTUpdateProjectEndDate( $_POST['projectID'], $_POST['endDate'] );
		break;
	default: echo "<script> console.log(\"The command didn't match anything\"); </script>";
}

?>