<?php
require_once( dirname( __FILE__ )."/./database/config.php" );
require_once( dirname( __FILE__ )."/./database/DBManager.php" );

$activeUsersLDAPGroup = $userLDAPGroup; 

////////////////////////////// CREATE/UPDATE FEEDBACK /////////////////////////////////////////

//DONE
function createSolicitedFeedback($solicitationReason, $solicitorId, $commenterId, $subjectId)
{
	//global $pdo;
	$db = new DBManager();
	$pdo = $db->pdo();
	
	$sql = "INSERT INTO FEEDBACK ".
		"(SOLICITATION_DATE, SOLICITATION_REASON, SOLICITOR_ID, COMMENTER_ID, SUBJECT_ID) VALUES ".
		"(NOW(), :solicitationReason, :solicitorId , :commenterId, :subjectId);";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('solicitationReason' => $solicitationReason, 'solicitorId' => $solicitorId, 'commenterId' => $commenterId, 'subjectId' => $subjectId ));
}

//DONE
function createUnsolicitedFeedback($comment, $disposition, $commenterId, $subjectId)
{
	//global $pdo;
	$db = new DBManager();
	$pdo = $db->pdo();
	
	if(!($disposition == "POSITIVE" || $disposition == "NEUTRAL" || $disposition == "NEGATIVE"))
		return;
	
	$sql = "INSERT INTO FEEDBACK ".
		"(FEEDBACK_DATE, COMMENT, DISPOSITION, COMMENTER_ID, SUBJECT_ID) VALUES ".
		"(NOW(), :comment, :disposition , :commenterId, :subjectId)";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array('comment' => $comment, 'disposition' => $disposition, 'commenterId' => $commenterId, 'subjectId' => $subjectId ));
	return true;
}


//DONE
function setReviewerAssignment($reviewerId, $employeeList)
{
	//global $pdo;
	$db = new DBManager();
	$pdo = $db->pdo();
	
	$sth = $pdo->prepare( "DELETE FROM REVIEWER_ASSIGNMENT WHERE REVIEWER_ID = :reviewerId");
	$sth->execute(array('reviewerId' => $reviewerId));
	
	foreach ( $employeeList as $employeeId )
	{
		//A reviewer can never review themselves
		if ( $reviewerId != $employeeId )
		{
			$sth = $pdo->prepare( "INSERT INTO REVIEWER_ASSIGNMENT (REVIEWER_ID, EMPLOYEE_ID) VALUES (:reviewerId, :employeeId)");
			$sth->execute(array('reviewerId' => $reviewerId, 'employeeId' => $employeeId ));
			
			//A reviewer can never review their own reviewer, thus we will destroy an existing reviewing relationship in favor of this one
			$sth = $pdo->prepare( "DELETE FROM REVIEWER_ASSIGNMENT WHERE REVIEWER_ID = :employeeId AND EMPLOYEE_ID = :reviewerId");
			$sth->execute(array('reviewerId' => $reviewerId, 'employeeId' => $employeeId ));
			
			//NOTE: we don't look for reporting loops
		}
	}
}

//DONE
function getReviewerAssignment($reviewerId)
{
	$db = new DBManager();
	$pdo = $db->pdo();
	$data = array();
	
	$sql = "SELECT EMPLOYEE_ID FROM REVIEWER_ASSIGNMENT WHERE REVIEWER_ID = :reviewerId"; 
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'reviewerId' => $reviewerId));
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result->EMPLOYEE_ID|0 );
	}
	return $data;
}

////////////////////////////// UPDATE FEEDBACK /////////////////////////////////////////

function updateFeedbackStatus($feedbackId, $status, $reviewerId) //Sets status to "UNREAD"
{
	//global $pdo;
	$db = new DBManager();
	$pdo = $db->pdo();
	
	if($status != "UNREAD" && $status != "READ" && $status != "PINNED" && $status != 'IGNORE')
		return false;

	//check to make sure that this feedback corresponds to an subject/employee who is assigned to the reviewer
	$sql = "SELECT COUNT(*) C FROM REVIEWER_ASSIGNMENT RA, FEEDBACK F WHERE F.SUBJECT_ID = RA.EMPLOYEE_ID AND RA.REVIEWER_ID=:reviewerId AND FEEDBACK_ID=:feedbackId";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'reviewerId' => $reviewerId, 'feedbackId' => $feedbackId));
	if ( ($result = $sth->fetchObject()) === false )
		return false;
	
	if ( $result->C == 0 )
		return false;
	
	$sql = "REPLACE INTO FEEDBACK_STATUS VALUES (:reviewerId, :feedbackId, :status )";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'status' => $status, 'reviewerId' => $reviewerId, 'feedbackId' => $feedbackId));
	return true;
}

//DONE
function updateFeedback($comment, $disposition, $commenterId, $feedbackId)
{
	//global $pdo;
	$db = new DBManager();
	$pdo = $db->pdo();
	
	if(!($disposition == "POSITIVE" || $disposition == "NEUTRAL" || $disposition == "NEGATIVE"))
		return;
	
	$sql = "UPDATE FEEDBACK SET COMMENT = :comment, DISPOSITION = :disposition WHERE COMMENTER_ID = :commenterId AND FEEDBACK_ID = :feedbackId;";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'comment' => $comment, 'disposition' => $disposition, 'commenterId' => $commenterId, 'feedbackId' => $feedbackId));
}

//DONE
function updateTodoFeedback($comment, $disposition, $commenterId, $feedbackId)
{
	//global $pdo;
	$db = new DBManager();
	$pdo = $db->pdo();
	
	if(!($disposition == "POSITIVE" || $disposition == "NEUTRAL" || $disposition == "NEGATIVE"))
		return;
	
	$sql = "UPDATE FEEDBACK SET COMMENT = :comment, DISPOSITION = :disposition, FEEDBACK_DATE = now() WHERE SOLICITATION_DATE IS NOT NULL AND COMMENTER_ID = :commenterId AND FEEDBACK_ID = :feedbackId;";
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'comment' => $comment, 'disposition' => $disposition, 'commenterId' => $commenterId, 'feedbackId' => $feedbackId));
}


////////////////////////////// REMOVE FEEDBACK /////////////////////////////////////////

//DONE
function removeUnsolicitedOpenFeedback($commenterId, $feedbackId)  
{
	$db = new DBManager();
	$pdo = $db->pdo();

	//Only removed open feedback that did not come from a solicitor that belongs to the specified commenter
	$sql = "DELETE FROM FEEDBACK WHERE COMMENTER_ID = :commenterId AND FEEDBACK_ID = :feedbackId AND CLOSED_DATE IS NULL AND SOLICITOR_ID IS NULL"; 
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'commenterId' => $commenterId, 'feedbackId' => $feedbackId));
}


//DONE
function removeSolicitedOpenFeedback($solicitorId, $feedbackId)  
{
	$db = new DBManager();
	$pdo = $db->pdo();

	$sql = "DELETE FROM FEEDBACK WHERE SOLICITOR_ID = :solicitorId AND FEEDBACK_ID = :feedbackId AND CLOSED_DATE IS NULL"; 
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'solicitorId' => $solicitorId, 'feedbackId' => $feedbackId));
}

//DONE
function finalizeFeedback($feedbackId)  
{
	$db = new DBManager();
	$pdo = $db->pdo();

	$sql = "UPDATE FEEDBACK SET CLOSED_DATE=now() WHERE FEEDBACK_ID = :feedbackId AND CLOSED_DATE IS NULL"; 
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'feedbackId' => $feedbackId));
}

////////////////////////////// REPORTING /////////////////////////////////////////


//DONE
function reportReviewerFeedback($reviewerId, $dateStart, $dateEnd) 
{
	$db = new DBManager();
	$pdo = $db->pdo();
	$data = array();
	
//	$sql =  "SELECT F.FEEDBACK_ID,F.SOLICITATION_DATE, F.SOLICITATION_REASON, F.SOLICITOR_ID, F.COMMENTER_ID, F.SUBJECT_ID, F.COMMENT, F.FEEDBACK_DATE,F.DISPOSITION, F.CLOSED_DATE,RA.REVIEWER_ID, IFNULL(FS.STATUS,'UNREAD') AS STATUS FROM FEEDBACK F LEFT JOIN (SELECT * FROM FEEDBACK_STATUS WHERE REVIEWER_ID=:reviewerId) AS FS ON F.FEEDBACK_ID=FS.FEEDBACK_ID, REVIEWER_ASSIGNMENT RA ".
//			"WHERE F.SUBJECT_ID=RA.EMPLOYEE_ID AND RA.REVIEWER_ID=:reviewerId AND CLOSED_DATE IS NOT NULL ORDER BY FEEDBACK_DATE"; 
	$sql =  "SELECT F.FEEDBACK_DATE, F.COMMENTER_ID, F.SUBJECT_ID, F.DISPOSITION, F.COMMENT, F.SOLICITOR_ID, F.SOLICITATION_DATE, F.SOLICITATION_REASON, F.CLOSED_DATE, F.FEEDBACK_ID, RA.REVIEWER_ID, IFNULL(FS.STATUS,'UNREAD') AS STATUS FROM FEEDBACK F LEFT JOIN (SELECT * FROM FEEDBACK_STATUS WHERE REVIEWER_ID=:reviewerId) AS FS ON F.FEEDBACK_ID=FS.FEEDBACK_ID, REVIEWER_ASSIGNMENT RA ".
			"WHERE F.SUBJECT_ID=RA.EMPLOYEE_ID AND RA.REVIEWER_ID=:reviewerId AND CLOSED_DATE IS NOT NULL AND date(FEEDBACK_DATE) BETWEEN CAST(:dateStart AS DATE) AND CAST(:dateEnd AS DATE) ORDER BY FEEDBACK_DATE"; 
			
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'reviewerId' => $reviewerId, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd ));
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
}

function reportUserFeedback($userId, $dateStart, $dateEnd) 
{
	$db = new DBManager();
	$pdo = $db->pdo();
	$data = array();
	
	$sql = "SELECT FEEDBACK_DATE, COMMENTER_ID, SUBJECT_ID, DISPOSITION, COMMENT, SOLICITOR_ID, SOLICITATION_DATE, SOLICITATION_REASON, CLOSED_DATE, FEEDBACK_ID FROM FEEDBACK WHERE COMMENTER_ID = :userId AND date(FEEDBACK_DATE) BETWEEN CAST(:dateStart AS DATE) AND CAST(:dateEnd AS DATE) ORDER BY FEEDBACK_DATE"; 
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'userId' => $userId, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd ));
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
}

////////////////////////////// FEEDBACK PAGES: GET FEEDBACK /////////////////////////////////////////

//DONE
function getOpenFeedbackForUser($userId) //Gets Feedback data for user that has a null CLOSED_DATE AND COMMENT AND DISPOSITION
{
	$db = new DBManager();
	$pdo = $db->pdo();
	$data = array();
	
	$sql = "SELECT * FROM FEEDBACK WHERE COMMENTER_ID = :userId AND CLOSED_DATE IS NULL AND FEEDBACK_DATE IS NOT NULL ORDER BY FEEDBACK_DATE DESC"; 
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'userId' => $userId));
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
}

//DONE
function getTodoFeedbackForUser($userId) //Gets Feedback data for user that has a null CLOSED_DATE AND COMMENT AND DISPOSITION
{
	$db = new DBManager();
	$pdo = $db->pdo();
	$data = array();
	
	$sql = "SELECT * FROM FEEDBACK WHERE COMMENTER_ID = :userId AND CLOSED_DATE IS NULL AND FEEDBACK_DATE IS NULL AND SOLICITATION_DATE IS NOT NULL ORDER BY SOLICITATION_DATE ASC, SOLICITATION_REASON"; 
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'userId' => $userId));
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
}

//DONE
function getTodoCount($userId) //Gets Feedback data for user that has a null CLOSED_DATE AND COMMENT AND DISPOSITION
{
	$db = new DBManager();
	$pdo = $db->pdo();
	$data = array();
	
	$sql = "SELECT COUNT(*) AS C FROM FEEDBACK WHERE COMMENTER_ID = :userId AND CLOSED_DATE IS NULL AND FEEDBACK_DATE IS NULL AND SOLICITATION_DATE IS NOT NULL ORDER BY SOLICITATION_DATE DESC"; 
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'userId' => $userId));
	
	if ( ($result = $sth->fetchObject()) !== false )
		return $result->C|0;
	else
		return 0;
}

//DONE
function getOpenSolicitedFeedback($userId) 
{
	$db = new DBManager();
	$pdo = $db->pdo();
	$data = array();
	
	$sql = "SELECT * FROM FEEDBACK WHERE SOLICITOR_ID = :userId AND CLOSED_DATE IS NULL ORDER BY SOLICITATION_DATE DESC"; 
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'userId' => $userId));
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
}

//DONE
function getOpenFeedbackForReview($reviewerId) 
{
	$db = new DBManager();
	$pdo = $db->pdo();
	$data = array();
	
	$sql =  "SELECT F.FEEDBACK_ID,F.SOLICITATION_DATE, F.SOLICITATION_REASON, F.SOLICITOR_ID, F.COMMENTER_ID, F.SUBJECT_ID, F.COMMENT, F.FEEDBACK_DATE,F.DISPOSITION, F.CLOSED_DATE,RA.REVIEWER_ID, IFNULL(FS.STATUS,'UNREAD') AS STATUS FROM FEEDBACK F LEFT JOIN (SELECT * FROM FEEDBACK_STATUS WHERE REVIEWER_ID=:reviewerId) AS FS ON F.FEEDBACK_ID=FS.FEEDBACK_ID, REVIEWER_ASSIGNMENT RA ".
			"WHERE F.SUBJECT_ID=RA.EMPLOYEE_ID AND RA.REVIEWER_ID=:reviewerId AND CLOSED_DATE IS NOT NULL AND (FS.STATUS IS NULL OR FS.STATUS = 'UNREAD' OR FS.STATUS = 'PINNED') ORDER BY FEEDBACK_DATE"; 
			
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'reviewerId' => $reviewerId));
	
	while ( ($result = $sth->fetchObject()) !== false )
	{
		array_push($data, $result );
	}
	return $data;
}

//DONE
function getReviewCount($reviewerId) 
{
	$db = new DBManager();
	$pdo = $db->pdo();
	$data = array();
	
	$sql =  "SELECT COUNT(*) as C FROM FEEDBACK F LEFT JOIN (SELECT * FROM FEEDBACK_STATUS WHERE REVIEWER_ID=:reviewerId) AS FS ON F.FEEDBACK_ID=FS.FEEDBACK_ID, REVIEWER_ASSIGNMENT RA WHERE F.SUBJECT_ID=RA.EMPLOYEE_ID AND RA.REVIEWER_ID=:reviewerId AND CLOSED_DATE IS NOT NULL AND (FS.STATUS IS NULL || FS.STATUS = 'UNREAD')"; 
			
	$sth = $pdo->prepare( $sql );
	$sth->execute(array( 'reviewerId' => $reviewerId));
	
	if ( ($result = $sth->fetchObject()) !== false )
		return $result->C|0;
	else
		return 0;
}

////////////////////////////// GET USERS BY SPECIFICATIONS /////////////////////////////////////////

//DONE
function getUsers()
{
	global $activeUsersLDAPGroup;
	
	$info = new stdclass();
	$data = array();
	$result = array();
	
	//TODO: Check that user has role of reviewer, solicitor, or admin using stored session username
	global $LdapUri;
	$conn = ldap_connect($LdapUri);

	foreach(LdapGroup::getGroupMembers($activeUsersLDAPGroup) as $user) //TODO: Switch this out with var for group name
	{
		$thisuser = new LdapUser($conn, $user);
		$result["uid"] = $thisuser->uid;
		$result["name"] = $thisuser->getFullName();
		$result["sn"] = $thisuser->sn;
		$result["givenName"] = $thisuser->givenName;
		$result["collateName"] = $thisuser->sn.", ".$thisuser->givenName;
		$result["username"] = $thisuser->username;
		array_push($data, $result );
	}
	$info->users = $data;
	$info->reviewers = LdapGroup::getGroupMembers("TwoPI_REVIEWER");
	$info->admins = LdapGroup::getGroupMembers("TwoPI_ADMIN");
	$info->peopleops = LdapGroup::getGroupMembers("TwoPI_PEOPLEOPS");
	$info->solicitors = LdapGroup::getGroupMembers("TwoPI_SOLICITOR");
	return $info;
}

//DONE
function isSolicitor( $id )
{
	return LdapGroup::isUserInGroup($id, "TwoPI_SOLICITOR");
}

//DONE
function isAdmin( $id )
{
	return LdapGroup::isUserInGroup($id, "TwoPI_ADMIN");
}


?>