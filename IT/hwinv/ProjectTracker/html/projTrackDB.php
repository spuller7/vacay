<?php
require_once( dirname( __FILE__ )."/./docroot/authenticate.php" );

define("MISC_PROJECT_ID", 1);
define("GOD_USER_ID", -1);

function PTProjectUpdateIsActive( $projectId, $state )
{
	global $pdo;
	$sql = "UPDATE PROJECTS SET IS_ACTIVE = :state WHERE PROJECT_ID = :projectID";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('state' => $state, 'projectID' => $projectId ));
}

function PTGetManagerForProject( $projectId )
{
	global $pdo;
	$sql = "SELECT PROJECT_MANAGER_ID FROM PROJECTS WHERE PROJECT_ID = :projectID";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('projectID' => $projectId ));
	$result = $sth->fetch( PDO::FETCH_OBJ );
	if ( $result !== false )
	{
		$result->PROJECT_MANAGER_ID |= 0;
		return $result->PROJECT_MANAGER_ID;
	}
	else
		return 0;
}

function PTGetOrderedManagersListForProject( $projectId )
{
	$mgrId = PTGetManagerForProject( $projectId );

	global $pdo;
	$sql = "SELECT if(FullName='',Username,FullName) as Name, Users.UserID, Users.UserID = :mgrId as IsManager from Users, Roles where Users.UserID = Roles.UserID and Roles.Role != 'USER' order by IsManager desc, LOWER(Name)";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('mgrId' => $mgrId ));
	
	$data = array();

	while ( ($result = $sth->fetchObject()) !== false )
	{
		$result->UserID |= 0;
		$result->IsManager = ($result->IsManager != 0);
		array_push($data, $result );
	}
	return $data;
}

function PTFindProjectIdByProjectName( $projectName )
{
	global $pdo;
	
	$sql = "SELECT PROJECT_ID FROM PROJECTS WHERE PROJECT_NAME = :projectName LIMIT 1;";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('projectName' => $projectName ));
	$result = $sth->fetch( PDO::FETCH_OBJ );
	if ( $result !== false )
		return $result->PROJECT_ID;
	else
		return 0;
}

function PTHasProjectEntriesForUserAndDate( $userId, $projectId, $date, $isSummary )
{
	global $pdo;
	$sql = "SELECT COUNT(*) as NUM FROM PROJECT_ENTRIES WHERE ENTRY_DATE = :date AND UserID = :userId AND PROJECT_ID = :projectId AND IS_SUMMARY = :isSummary;";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('userId' => $userId, 'projectId' => $projectId, 'date' => $date, 'isSummary' => $isSummary));
	return $sth->fetch( PDO::FETCH_NUM )[0]|0;
}

function PTAreProjectEntriesLockedForUserAndDate( $userId, $date )
{
	global $pdo;
	$sql = "SELECT COUNT(*) as NUM FROM PROJECT_ENTRIES WHERE ENTRY_DATE = :date AND UserID = :userId AND IS_SUMMARY = FALSE AND ENTRY_STATE='COMPLETE';";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('userId' => $userId, 'date' => $date));
	$count = $sth->fetch( PDO::FETCH_NUM )[0]|0;	
	return $count > 0;
}


function PTCreateDefaultProjectEntryForUserDate( $userId, $projectId, $date, $state, $isSummary)
{
	global $pdo;
	
	$isSummary = $isSummary ? 1 : 0;

	$sql = "REPLACE INTO PROJECT_ENTRIES ".
		"(PROJECT_ID, UserID,  EFFORT, ISSUES, UPCOMING_GOALS, ACCOMPLISHMENTS, ENTRY_STATE, IS_SUMMARY, MISC, HEALTH, ENTRY_DATE ) VALUES ".
		"(:projectId, :userID, 0,      '',     '',             '',              :state,      :isSummary,      '',   NULL,   :date );";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('userID' => $userId, 'projectId' => $projectId, 'date' => $date, 'state' => $state, 'isSummary' => $isSummary ));
	
	return true;
}

function PTGetAssignedProjectsForUser( $userId )
{
	global $pdo;
	$sql = "SELECT p.PROJECT_ID FROM PROJECT_ASSIGNMENT pa, PROJECTS p WHERE pa.UserID = :userId AND pa.PROJECT_ID=p.PROJECT_ID and p.IS_ACTIVE = 1 ORDER BY PROJECT_NAME;";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('userId' => $userId ));
	$data = array();
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result->PROJECT_ID|0 );
	}
	return $data;
}	

function PTGetSundayDateString( $strDate )
{
	$d = strtotime($strDate);
	if(date('w', $d) != 0) //check if the selected date is not a Sunday
		$d = strtotime("last Sunday", $d);
		
	return date('Y-m-d',$d);
}
	
function PTGetAssignedUsersForProject( $projectId )
{
	global $pdo;
	$sql = "SELECT if(FullName='',Username,FullName) as Name, Users.UserID from Users, PROJECT_ASSIGNMENT where Users.UserID != -1 AND Users.UserID = PROJECT_ASSIGNMENT.UserID and PROJECT_ASSIGNMENT.PROJECT_ID= :projectId ORDER BY LOWER(Name)";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('projectId' => $projectId ));
	$data = array();

	while ( ($result = $sth->fetchObject()) !== false )
	{
		$result->UserID |= 0;
		array_push($data, $result );
	}
	return $data;
}	

function PTGetAssignableUsersForProject( $projectId )
{
	global $pdo;
	$sql = "SELECT if(FullName='',Username,FullName) as Name, Users.UserID from Users WHERE Users.UserID != -1 AND Users.UserID NOT IN (SELECT Users.UserID from Users, PROJECT_ASSIGNMENT where Users.UserID = PROJECT_ASSIGNMENT.UserID and PROJECT_ASSIGNMENT.PROJECT_ID= :projectId) ORDER BY LOWER(Name)";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('projectId' => $projectId ));
	$data = array();
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
}	

function PTRenameProject( $projectId, $newProjectName )
{
	global $pdo;
	
	$newProjectName = htmlentities($newProjectName);
	
	$existingProjectId = PTFindProjectIdByProjectName( $newProjectName );
	if ( $existingProjectId != 0 )
		return false;
	if ($newProjectName == null)
		return false;
	
	$sql = "UPDATE PROJECTS SET PROJECT_NAME=:newProjectName WHERE PROJECT_ID = :projectId;";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('projectId' => $projectId, 'newProjectName' => $newProjectName ));
	return true;
}

function PTGetNamebyUserID( $userId )
{
	global $pdo;
	
	$sql = "SELECT if(FullName='',Username,FullName) as Name FROM Users WHERE Users.UserID = :userId;";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('userId' => $userId));
	$result = $sth->fetch( PDO::FETCH_OBJ );
	return $result->Name;
}

function PTGetActiveUsersData ()
{
	global $pdo;
	
	$sql = "SELECT if(FullName='',Username,FullName) as Name, Users.UserID from Users WHERE Users.UserID != -1 AND State = \"active\";"; 
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute();
	$data = array();
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
}

function PTGetProjectsbyState ($state) 
{
	global $pdo;
	
	$sql = "SELECT PROJECT_NAME, PROJECT_ID FROM PROJECTS WHERE IS_ACTIVE= :state AND PROJECT_ID != 1;";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('state' => $state));
	$data = array();
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
}

function PTUpdateEntryStatus ($userId, $date) 
{
	global $pdo;
	
	if(PTAreProjectEntriesLockedForUserAndDate( $userId, $date )) //If entries are already locked unlock them
		$state = "PARTIAL";
	else
		$state = "COMPLETE";
	
	$sql = "UPDATE PROJECT_ENTRIES SET ENTRY_STATE = :state WHERE UserID = :userId AND ENTRY_DATE = :date;";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('state' => $state, 'userId' => $userId, 'date' => $date));
}

function PTHasEmptyEntries ( $userId, $date )
{
	global $pdo;

	$sql = "SELECT COUNT(*) as NUM FROM PROJECT_ENTRIES pe, PROJECTS p WHERE pe.ENTRY_DATE = :date AND pe.UserID = :userId AND pe.ENTRY_STATE='NONE' AND pe.PROJECT_ID != 1 AND pe.PROJECT_ID = p.PROJECT_ID AND p.IS_ACTIVE = TRUE;";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('userId' => $userId, 'date' => $date));
	$count = $sth->fetch( PDO::FETCH_NUM )[0]|0;	
	return $count > 0;
}

function PTHasValidEffortPercentage ( $userId, $date )
{
	global $pdo;
	
	$sql = "SELECT SUM(EFFORT) FROM PROJECT_ENTRIES WHERE ENTRY_DATE = :date AND UserID = :userId;";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('userId' => $userId, 'date' => $date));
	$count = $sth->fetch( PDO::FETCH_NUM )[0]|0;	
	return $count == 100;
}

function PTGetAllManagers () //Can consolidate this with GetUsersByRole
{
	global $pdo;
	
	$sql = "SELECT if(FullName='',Username,FullName) as Name, Users.UserID FROM Users, Roles WHERE Users.UserID = Roles.UserID and Roles.Role != 'USER';"; 
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute();
	
	$data = array();

	while ( ($result = $sth->fetchObject()) !== false )
	{
		$result->UserID |= 0;
		array_push($data, $result );
	}
	return $data;
}

function PTCreateProject ( $projectName, $startDate, $endDate, $projectManagerId, $isRD )
{
	global $pdo;
	
	$projectName = htmlentities($projectName);
	
	$sql = "INSERT INTO PROJECTS ".
		"(PROJECT_NAME, START_DATE, END_DATE, PROJECT_MANAGER_ID, IS_ACTIVE, IS_RD) VALUES ".
		"(:projectName, :startDate, :endDate , :projectManagerId, TRUE , CAST( :isRD AS UNSIGNED));";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('projectName' => $projectName, 'startDate' => $startDate, 'endDate' => $endDate, 'projectManagerId' => $projectManagerId, 'isRD' => $isRD ));

	// echo "\nPDO::errorCode(): ", $sth->errorCode();
	
	// echo "\nPDOStatement::errorInfo():\n";
	// $arr = $sth->errorInfo();
	// print_r($arr);
}

function PTAssignUsertoProject ( $userId, $projectId )
{
	global $pdo;
	
	$sql = "REPLACE INTO PROJECT_ASSIGNMENT ".
		"(PROJECT_ID, UserID) VALUES ".
		"(:projectId, :userId);";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('projectId' => $projectId, 'userId' => $userId));
}

function PTUnassignUserfromProject ( $userId, $projectId )
{
	global $pdo;
	
	$sql = "DELETE FROM PROJECT_ASSIGNMENT ".
		"WHERE PROJECT_ID = :projectId AND UserID = :userId;";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('projectId' => $projectId, 'userId' => $userId));
}

function PTGetUsersByRole ($role)
{
	global $pdo;
	
	$sql = "SELECT if(FullName='',Username,FullName) as Name, Users.UserID FROM Users, Roles WHERE Users.UserID = Roles.UserID and Roles.Role = :role;"; 
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('role' => $role));
	$data = array();
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
}

function PTUpdateUserRole ($userId, $role)
{
	global $pdo;
	
	$sql = "UPDATE Roles SET Role = :role WHERE UserID = :userID"; 
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('role' => $role, 'userID' => $userId));
}

function PTGetProjectsForUserAndDate($userId, $date, $isSummary)
{
	global $pdo;
	
	$sql = "SELECT PROJECT_ID FROM PROJECT_ENTRIES WHERE ENTRY_DATE = :date AND UserID = :userId AND IS_SUMMARY = :isSummary ORDER BY PROJECT_ID DESC;"; 
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('userId' => $userId, 'date' => $date, 'isSummary' => $isSummary));
	$data = array();
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result->PROJECT_ID|0 );
	}
	
	return $data;
}

function PTGetHealthForProjectOnOrBeforeDate( $projectId, $date )
{
	global $pdo;
	
	$sql = "SELECT HEALTH FROM PROJECT_ENTRIES WHERE PROJECT_ID = :projectId AND IS_SUMMARY = TRUE AND ENTRY_DATE <= :date ORDER BY ENTRY_DATE DESC LIMIT 1";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('projectId' => $projectId, 'date' => $date ));
	$result = $sth->fetch( PDO::FETCH_OBJ );
	if ( $result !== false )
		return $result->HEALTH;
	else
		return false;
}

function PTGetMiscEntryDatabyUserandDate ( $userId, $date) //TODO: I don't think is used anymore
{
	global $pdo;
	$projectId = MISC_PROJECT_ID;
	
	$sql = "SELECT EFFORT, ACCOMPLISHMENTS, ISSUES, UPCOMING_GOALS FROM PROJECT_ENTRIES WHERE PROJECT_ID= :projectId AND UserID= :userId AND ENTRY_DATE = :date;";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('projectId' => $projectId, 'userId' => $userId, 'date' => $date));
	$data = array();
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
	
}

function PTGetUpcomingGoalsData ( $projectId, $userId, $date)
{
	global $pdo;
	
	$sql = "SELECT UPCOMING_GOALS FROM PROJECT_ENTRIES WHERE ENTRY_DATE = :date AND PROJECT_ID = :projectId AND UserID = :userId AND IS_SUMMARY = FALSE;"; 
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('projectId' => $projectId, 'userId' => $userId, 'date' => $date));
	$result = $sth->fetch( PDO::FETCH_OBJ );
	if ( $result !== false )
		return $result->UPCOMING_GOALS;
	else
		return false;
}

function PTGetProjectEntryByUserAndProjectAndDate( $userId, $projectId, $date, $isSummary)
{
       global $pdo;
	   
       $sql = "SELECT ENTRY_DATE, ENTRY_STATE, EFFORT, ".
               "(SELECT COUNT(*) FROM PROJECT_ASSIGNMENT WHERE PROJECT_ID=:projectId AND UserID=:userId) AS ASSIGNED,".
               "(SELECT UPCOMING_GOALS FROM PROJECT_ENTRIES WHERE PROJECT_ID=:projectId AND UserID=:userId ".
               " AND IS_SUMMARY = FALSE AND ENTRY_DATE < :date ORDER BY ENTRY_DATE DESC LIMIT 1) as PREVIOUS_GOALS, ".
               "ACCOMPLISHMENTS, ISSUES, UPCOMING_GOALS FROM PROJECT_ENTRIES WHERE PROJECT_ID=:projectId AND UserID=:userId AND ".
               "IS_SUMMARY = :isSummary AND ENTRY_DATE = :date";

       $sth = $pdo->prepare( $sql );
       $res = $sth->execute(array('projectId' => $projectId, 'userId' => $userId, 'date' => $date, 'isSummary' => $isSummary));
       $result = $sth->fetch( PDO::FETCH_OBJ );
       if ( $result !== false )
       {
		   if( $result->PREVIOUS_GOALS == null )
				   $result->PREVIOUS_GOALS = "";
		   $result->ASSIGNED = $result->ASSIGNED != 0;
       }
       return $result;
}

function PTGetProjectInfoById( $projectId )
{
       global $pdo;
       $sql = "SELECT PROJECT_ID, PROJECT_NAME, END_DATE, IS_ACTIVE FROM PROJECTS WHERE PROJECT_ID= :projectId";
       $sth = $pdo->prepare( $sql );
       $res = $sth->execute(array('projectId' => $projectId));
       $result = $sth->fetch( PDO::FETCH_OBJ );
       return $result;
}


function PTGetAvailableProjectsForUserAndDate( $userId, $date )
{
       global $pdo;
       $sql = "SELECT  PROJECT_ID, PROJECT_NAME FROM PROJECTS  WHERE ".
                       "PROJECT_ID NOT IN (SELECT PROJECT_ID FROM PROJECT_ENTRIES WHERE ".
                       "UserID = :userId AND ENTRY_DATE = :date) ".
                       "AND IS_ACTIVE=TRUE AND PROJECT_ID != 1 ORDER BY LOWER(PROJECT_NAME)";

       $sth = $pdo->prepare( $sql );
       $res = $sth->execute(array('userId' => $userId, 'date' => $date ));

       $data = array();

       while ( ($result = $sth->fetchObject()) !== false )
       {
               $result->PROJECT_ID |= 0;
               array_push($data, $result );
       }
       return $data;
}

function PTUpdateProjectEndDate( $projectId, $date )
{
       global $pdo;

       $sql = "UPDATE PROJECTS SET END_DATE=:date WHERE PROJECT_ID = :projectId;";
		$sth = $pdo->prepare( $sql );
       $res = $sth->execute(array('projectId' => $projectId, 'date' => $date ));
       return true;
}

function PTUpdateProjectHealth( $projectId, $date, $health )
{
       global $pdo;

       $sql = "UPDATE PROJECT_ENTRIES SET HEALTH = :health WHERE PROJECT_ID = :projectId AND IS_SUMMARY = TRUE AND ENTRY_DATE = :date";
       $sth = $pdo->prepare( $sql );
       $res = $sth->execute(array('projectId' => $projectId, 'date' => $date, 'health' => $health ));
       return true;
}

function PTUpdateProjectEntryField( $userId, $projectId, $date, $field, $value, $isSummary )
{
	   global $pdo;
	   if ( $field != "ACCOMPLISHMENTS" && $field != "ISSUES" && $field != "UPCOMING_GOALS" && $field != "EFFORT" )
		   return;
	   
	   $value = htmlentities($value);

	   $entryState = (strlen($value) == 0 ? "NONE" : "PARTIAL");
	   if ( $isSummary == true )
		   $userId = -1;

	   $sql = "UPDATE PROJECT_ENTRIES SET ". $field ." = :value, ENTRY_STATE = :entryState WHERE PROJECT_ID= :projectId AND UserID= :userId AND ENTRY_STATE != 'COMPLETE' AND IS_SUMMARY = :isSummary AND ENTRY_DATE = :date";
	   $sth = $pdo->prepare( $sql );
	   $res = $sth->execute(array(
		   'userId' => $userId,
		   'projectId' => $projectId,
		   'date' => $date,
		   'value' => $value,
		   'isSummary' => $isSummary,
		   'entryState' => $entryState
		   ));
	   return true;
}

function PTUpdateProjectManager( $projectId, $newManagerId )
{
       global $pdo;

       $sql = "UPDATE PROJECTS SET PROJECT_MANAGER_ID = :newManagerId WHERE PROJECT_ID = :projectId";
       $sth = $pdo->prepare( $sql );
       $res = $sth->execute(array('projectId' => $projectId, 'newManagerId' => $newManagerId ));

       $sql = "REPLACE INTO PROJECT_ASSIGNMENT (PROJECT_ID, UserID) VALUES ( :projectId, :newManagerId )";
       $sth = $pdo->prepare( $sql );
       $res = $sth->execute(array('projectId' => $projectId, 'newManagerId' => $newManagerId ));

       return true;
}

function PTGetEndDateByProject ( $projectId)
{
	global $pdo;
	
	$sql = "SELECT END_DATE FROM PROJECTS WHERE IS_ACTIVE = TRUE AND PROJECT_ID = :projectId;"; 
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('projectId' => $projectId));
	$result = $sth->fetch( PDO::FETCH_OBJ );
    return $result->END_DATE;
}

function PTGetUtilizationReportData ( $startDate, $endDate )
{
   global $pdo;
   $miscId = MISC_PROJECT_ID;
   
   $sql = "SELECT   if(FullName = '', Username, FullName) as Name, PROJECT_NAME, EFFORT ".
			"FROM Users u, PROJECTS p, PROJECT_ENTRIES pe WHERE u.UserID = pe.UserID ".
			"AND p.PROJECT_ID = pe.PROJECT_ID AND pe.ENTRY_DATE BETWEEN :startDate AND :endDate ".
			"AND p.PROJECT_ID != :miscId ORDER BY Lower(Name);";

   $sth = $pdo->prepare( $sql );
   $res = $sth->execute(array('startDate' => $startDate, 'endDate' => $endDate, 'miscId' => $miscId));

   $data = array();

   while ( ($result = $sth->fetchObject()) !== false )
   {
	   $result->EFFORT |= 0;
	   array_push($data, $result );
   }
   return $data;
}

function PTCalculateUtilization ( $userId, $startdate, $enddate )
{
	global $pdo;
	$miscId = MISC_PROJECT_ID;
	
	$sql = "SELECT SUM(EFFORT) FROM PROJECT_ENTRIES WHERE ENTRY_DATE BETWEEN :startdate AND :enddate AND UserID = :userId AND IS_SUMMARY = FALSE;";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('userId' => $userId, 'startdate' => $startdate, 'enddate' => $enddate));
	$totalEffortCount = $sth->fetch( PDO::FETCH_NUM )[0]|0;	
	
	$sql = "SELECT SUM(EFFORT) FROM PROJECT_ENTRIES WHERE ENTRY_DATE BETWEEN :startdate AND :enddate AND UserID = :userId AND IS_SUMMARY = FALSE AND PROJECT_ID = :projectID;";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('userId' => $userId, 'startdate' => $startdate, 'enddate' => $enddate, 'projectID' => $miscId));
	$miscEffortCount = $sth->fetch( PDO::FETCH_NUM )[0]|0;	
	
	$totalHours = $totalEffortCount * 0.4;
	$miscHours = $miscEffortCount * 0.4;
	
	if($totalHours == 0)
		return 0;
	else
		return ($totalHours - $miscHours)/$totalHours;
}

function PTIsUserAssignedToProject ($userId, $projectId)
{
	global $pdo;
	
	$sql = "SELECT COUNT(*) as NUM FROM PROJECT_ASSIGNMENT WHERE UserID = :userId AND PROJECT_ID = :projectId;";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('userId' => $userId, 'projectId' => $projectId));
	$count = $sth->fetch( PDO::FETCH_NUM )[0]|0;	
	
	return $count > 0;
}

function PTGetRDstate ( $projectId ) 
{
	global $pdo;
	
	$sql = "SELECT IS_RD FROM PROJECTS WHERE PROJECT_ID = :projectId;"; 
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('projectId' => $projectId));
	$result = $sth->fetch( PDO::FETCH_OBJ );
    return $result->IS_RD;
}

function PTSetRDstate ( $projectId, $RDstate ) 
{
	global $pdo;

	$sql = "UPDATE PROJECTS SET IS_RD = CAST( :RDstate AS UNSIGNED) WHERE PROJECT_ID = :projectId;";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('projectId' => $projectId, 'RDstate' => $RDstate));
}

function PTRemoveProjectEntrybyUserandDate ( $userId, $date, $projectId )
{
	global $pdo;
	
	$sql = "DELETE FROM PROJECT_ENTRIES WHERE PROJECT_ID = :projectId AND UserID = :userId AND ENTRY_DATE = :date;";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('projectId' => $projectId, 'date' => $date, 'userId' => $userId));
}

function PTChangeUserFullName( $userId, $name)
{
	global $pdo;
	
	$name = htmlentities($name);
	
	$sql = "UPDATE Users SET FullName = :name WHERE UserID = :userId;";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('userId' => $userId, 'name' => $name));
}

function PTGetUsersWithEntriesbyProjectandDate ( $projectID, $entryDate )
{
	global $pdo;
	   
	$sql = "SELECT if(FullName = '', Username, FullName) as Name, Users.UserID ".
		"FROM Users, PROJECT_ENTRIES WHERE Users.UserID = PROJECT_ENTRIES.UserID ".
		"AND PROJECT_ENTRIES.IS_SUMMARY = FALSE AND PROJECT_ENTRIES.PROJECT_ID = :projectID AND PROJECT_ENTRIES.ENTRY_DATE = :entryDate ORDER BY Lower(Name);";

	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array('projectID' => $projectID, 'entryDate' => $entryDate));

	$data = array();
	   
	while ( ($result = $sth->fetchObject()) !== false )
	{
		$result->UserID |= 0;
		array_push($data, $result );
	}
	return $data;
}



?>
