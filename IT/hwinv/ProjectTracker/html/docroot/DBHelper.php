<?php
//Require authentification
require_once( dirname( __FILE__ )."/./authenticate.php" );

function activateProjects( $projectList )
{
	checkRole(Roles::ADMINISTRATOR);

	foreach ($projectList as $x) 
	{
		PTProjectUpdateIsActive( $x, true ); 
	}
}

function addRow ( $projectID )
{
	$entryDate = PTGetSundayDateString($_SESSION['DATE']);
	$userId = $_SESSION["USERID"];

	$healthState = PTGetHealthForProjectOnOrBeforeDate( $projectID, $entryDate );
	if ( $healthState === false )
	{
		$healthState = "NULL";
	}

	$projectInfo = PTGetProjectInfoById( $projectID );

	if(PTHasProjectEntriesForUserAndDate( $userId, $projectID, $entryDate, false) == 0)
	{
		PTCreateDefaultProjectEntryForUserDate( $userId, $projectID, $entryDate, 'NONE', false ); 
	}

	$entryData = PTGetProjectEntryByUserAndProjectAndDate( $userId, $projectID, $entryDate, false );
	if ( $entryData->PREVIOUS_GOALS == "" )
		$entryData->PREVIOUS_GOALS = "No Previous Goals";


	$data = new stdClass;
	$data->projectName= $projectInfo->PROJECT_NAME; 
	$data->end_date= $projectInfo->END_DATE;
	$data->projectID= $projectID;

	$data->effort= $entryData->EFFORT;
	$data->previousGoals= $entryData->PREVIOUS_GOALS;
	$data->accomplishments= $entryData->ACCOMPLISHMENTS;
	$data->issues= $entryData->ISSUES;
	$data->upcomingGoals= $entryData->UPCOMING_GOALS;

	$data->health=$healthState;

	return json_encode($data);
}

function archiveProjects( $projectList )
{
	checkRole(Roles::ADMINISTRATOR);

	foreach ($projectList as $x) 
	{
		PTProjectUpdateIsActive( $x, 0 ); //If you pass it false rather than 0 it gives the error  HY000 Incorrect integer value: '' for column 'IS_ACTIVE' at row 1 
	}
}

function autoSave ( $projectID, $color, $text, $entryState, $isSummary, $id) //TODO: check what entry state is
{
	$entryDate = PTGetSundayDateString($_SESSION['DATE']); //Date from date picker in header.php
	$userId = $_SESSION["USERID"];
	$projId = $projectID;

	if($color != NULL) //isset//array_key_exists("color", $_GET))
	{
		$flag = $color;
		if( $flag == "status-green")
			$color = "GREEN";
		elseif($flag == "status-yellow")
			$color = "YELLOW";
		else
			$color = "RED";
		
		PTUpdateProjectHealth( $projectID, $entryDate, $color );
	} 
	else
	{
		$isSummary = ($isSummary == 1); 
		$flag = $id;

		if (strpos($flag, "AccomplishmentsResponse") !== false)
			$id = "ACCOMPLISHMENTS";
		elseif (strpos($flag, "IssuesResponse") !== false)
			$id = "ISSUES";
		elseif (strpos($flag, "UpcomingGoalsResponse") !== false)
			$id = "UPCOMING_GOALS";
		else
		{
			$id = "EFFORT";
		}

		PTUpdateProjectEntryField($userId, $projectID, $entryDate, $id, $text, $isSummary );
	}
}

function changeUserRoles ( $userList, $role )
{
	checkRole(Roles::ADMINISTRATOR);

	foreach ($userList as $x) 
	{
		PTUpdateUserRole( $x, $role );
	}
}

function deleteProject ( $projectID ) //TODO: Rewrite this so entry deletion is not required
{
	$entryDate = PTGetSundayDateString($_SESSION['DATE']); //Get Sunday function
	$userID = $_SESSION["USERID"];

	if(PTIsUserAssignedToProject($userID, $projectID) === true)
		PTUnassignUserfromProject($userID, $projectID);

	PTRemoveProjectEntrybyUserandDate($userID, $entryDate, $projectID); //TODO: Could avoid deleting entry if there was a way to designate that it didn't need to be complete to lock
}

function exportReport ( $reportType, $startDate, $endDate )
{
	$startdate = PTGetSundayDateString($startDate); 
	$enddate = PTGetSundayDateString($endDate);

	// header('Content-Type: text/csv; charset=utf-8');
	// header('Content-Disposition: attachment; filename=UtilizationReport.csv');
	$output = fopen("php://output", "w");

	if($reportType == "hours-report")
	{
		fputcsv($output, array('Name', 'Project', 'Effort', 'Hours'));
		
		$data = PTGetUtilizationReportData($startdate, $enddate);
		foreach ($data as $row) {
			$row->HOURS = $row->EFFORT * 0.4; 
			$rowData = array();
			
			array_push($rowData, $row->Name);
			array_push($rowData, $row->PROJECT_NAME);
			array_push($rowData, $row->EFFORT);
			array_push($rowData, $row->HOURS);
			
			fputcsv($output, $rowData); 
			
		}
	}
	elseif ($reportType == "utilization-report")
	{
		fputcsv($output, array('Name', 'Utilization'));
		
		$usersData = PTGetActiveUsersData();
		
		foreach($usersData as $user)
		{
			$rowData = array();
			$utilization = PTCalculateUtilization($user->UserID, $startdate, $enddate);
			
			array_push($rowData, $user->Name);
			array_push($rowData, $utilization);
			
			fputcsv($output, $rowData); 
		}
	}
	fclose($output);
}

function getUsers ( $projectID )
{
	checkRole(Roles::ADMINISTRATOR);
		
	if($_POST["active"] == "true")
		$data = PTGetAssignedUsersForProject( $projectID );
	else
		$data = PTGetAssignableUsersForProject( $projectID );

	return json_encode($data);
}

function lockEntries ()
{
	$entryDate = PTGetSundayDateString($_SESSION['DATE']);
	$userID = $_SESSION["USERID"];

	if(!PTHasEmptyEntries($userID, $entryDate))
	{
		if(PTHasValidEffortPercentage($userID, $entryDate))
		{
			PTUpdateEntryStatus ($userID, $entryDate);
			echo "Entries Locked Successfully.";
		}
		else
			echo "FAILED TO SUBMIT ENTRIES\n\nREASON: Effort does not add up to 100.";
	}
	else
		echo "FAILED TO SUBMIT ENTRIES\n\nREASON: The entry state for one or more of your entries is marked as none.";
}

function lockStatus ( $page )
{
	$entryDate = PTGetSundayDateString($_SESSION['DATE']);

	$isSummary = ($page == "OVERALL") ? 1 : 0;

	$data = PTAreProjectEntriesLockedForUserAndDate($_SESSION["USERID"], $entryDate);

	if($data == true)
		return 1;
	else
		return 0;
}

function moveUsers ( $projectID, $usersList, $unassignedUsersList )
{
	if(hasRole(Roles::ADMINISTRATOR) || hasRole(Roles::MANAGER) )
	{
		foreach ($usersList as $x)
		{
			if(PTIsUserAssignedToProject($x, $projectID) === false)
				PTAssignUsertoProject($x, $projectID);
		}
		
		foreach ($unassignedUsersList as $x) 
		{ 
			if(PTIsUserAssignedToProject($x, $projectID) === true)
				PTUnassignUserfromProject($x, $projectID);
		}
	}
}

function newProjDatabase ( $name, $startDate, $endDate, $manager, $is_RD, $users)
{
	if(hasRole(Roles::ADMINISTRATOR) || hasRole( Roles::MANAGER))
	{
		PTCreateProject( $name, $startDate, $endDate, $manager, $is_RD);
		
		$projectId = PTFindProjectIdByProjectName($name);
		
		foreach ($users as $x) 
		{
			PTAssignUsertoProject($x, $projectId);
		}
	}
}

function statusIcons ()
{
	if( hasRole( Roles::ADMINISTRATOR ) || hasRole( Roles::MANAGER ) )
	{ 
		$entryDate = PTGetSundayDateString($_SESSION['DATE']);
		$userID = GOD_USER_ID;
		$statusArr = array(); //associative array which will store project id as the index and the status of the project (true or false) as the value
		
		$projectList = PTGetProjectsForUserAndDate($userID, $entryDate, true);

		foreach ($projectList as $projectID)
		{
			$assignedUsers = PTGetAssignedUsersForProject($projectID);
			$isLocked = 0;
			foreach ($assignedUsers as $user)
			{
				$userID = $user->UserID;
				
				$isLocked = PTAreProjectEntriesLockedForUserAndDate($userID, $entryDate);

				if ($isLocked === false)
				{
					break;
				}
			}
			$statusArr[$projectID] = $isLocked ? 1 : 0;
		}

		return json_encode($statusArr, true); //previously an echo
		
	}
}

function unlockEntries ( $userId )
{
	checkRole( Roles::ADMINISTRATOR );
	
	$entryDate = PTGetSundayDateString($_SESSION['DATE']);

	PTUpdateEntryStatus($userId, $entryDate);

	echo "Updated Entries";
}

function updateDate ( $date )
{
	//TODO: confirm that passed in date is valid
	
	$_SESSION['DATE'] = $date;

	return $date;
}
?>