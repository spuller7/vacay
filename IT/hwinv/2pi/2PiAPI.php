<?php
require_once( dirname( __FILE__ )."/./database/config.php" );
require_once( dirname( __FILE__ )."/./database/DBManager.php" );
require_once( dirname( __FILE__ )."/./database/LdapLib.php" );
require_once( dirname( __FILE__ )."/DBHelper.php" );

header('Content-Type: application/json; charset=UTF-8');
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

$json = file_get_contents('php://input');
$payload = json_decode($json);

$r = new stdClass();
$r->error = 0;
$r->error_message = "";

session_start();

if ( $payload->Command == "logout" )
{
	session_destroy();	
	$r->result = "logged out";
} 
else if ( $payload->Command == "authenticate" )
{
	$login_username = strtolower($payload->username);
	$login_password = $payload->password;
	if ( LdapUser::authenticateUser( $login_username, $login_password ) )
	{
		$user = LdapUser::FetchOne( $login_username );
		$r->result = true;
		$_SESSION['USER_NAME'] = $login_username;
		$_SESSION['USERID'] = $user->uid;
		$_SESSION['USERIDMAP'] = LdapUser::getUserMap();
		$_SESSION['USERNAMEMAP'] = array_flip( $_SESSION['USERIDMAP'] );
		$_SESSION['USER'] = $user;
	}
	else
	{
		$r->result = false;
		$r->error_message = "Authentication Failed";
	}
} 
else if ( session_id() == "" || !isset($_SESSION) || !isset($_SESSION['USERID']) )
{
	$r->error = -5;
	$r->error_message = "Not authenticated";
}
else
{
	$userId = $_SESSION['USERID'];

	try
	{
		switch($payload->Command) 
		{
			case 'getAuthData':
			{
				$r->result = new stdClass();
				$r->result->username  = $_SESSION['USER_NAME'];
				$r->result->givenName = $_SESSION['USER']->givenName;
				$r->result->sn = $_SESSION['USER']->sn;
				$r->result->submit_cooldown = $submit_cooldown;
				break;
			}
			case 'createSolicitedFeedback': //DONE
			{
				if ( isSolicitor( $_SESSION['USER_NAME'] ) )
				{
					$r->result = createSolicitedFeedback($payload->solicitationReason, $userId, $payload->commenterId, $payload->subjectId );
				}
				else
				{
					$r->error = -6;
					$r->error_message = "Not a solicitor";
				}
				break; 
			}
			case 'createSolicitedFeedbackBatch': //DONE
			{
				if ( !isSolicitor( $_SESSION['USER_NAME'] ) )
				{
					$r->error = -6;
					$r->error_message = "Not a solicitor";
				}
				else
				{
					$reason = $payload->solicitationReason;
					$cids = $payload->commenterIds;
					$sids = $payload->subjectIds;
					foreach ( $cids as $cid )
					{
						foreach ($sids as $sid )
						{
							createSolicitedFeedback($reason, $userId, $cid|0, $sid|0 );
						}
					}
					$r->result = true;
				}
				break; 
			}
			case 'createUnsolicitedFeedback':  //DONE
				$r->result = createUnsolicitedFeedback($payload->comment, $payload->disposition, $userId, $payload->subjectId|0);
				break;
			case 'getOpenFeedback': //DONE
				$r->result = getOpenFeedbackForUser($userId);
				break;
			case 'getTodoFeedback': //DONE
				$r->result = getTodoFeedbackForUser($userId);
				break;			
			case 'getOpenSolicitedFeedback': //DONE
				$r->result = getOpenSolicitedFeedback($userId);
				break;	
			case 'getOpenFeedbackForReview': //DONE
				$r->result = getOpenFeedbackForReview($userId);
				break;	
			case 'getUsers': //DONE
			{
				if ( isset($_SESSION["getUsers"]))
				{
					$r->result = $_SESSION["getUsers"];
				}
				else
				{
					$_SESSION["getUsers"] = $r->result = getUsers();
				}
				break;
			}	
			case 'setReviewerAssignment': //DONE
			{
				if ( !isAdmin( $_SESSION['USER_NAME'] ) )
				{
					$r->error = -7;
					$r->error_message = "Not an admin";
				}
				else
				{
					$r->result = setReviewerAssignment($payload->reviewerId, $payload->employeeList);
				}
				break;
			}
			case 'getReviewerAssignment': //DONE
			{
				if ( !isAdmin( $_SESSION['USER_NAME'] ) )
				{
					$r->error = -7;
					$r->error_message = "Not an admin";
				}
				else
				{		
					$r->result = getReviewerAssignment($payload->reviewerId);
				}
				break;
			}
			case 'removeUnsolicitedOpenFeedback': //DONE
				$r->result = removeUnsolicitedOpenFeedback($userId, $payload->feedbackId);
				break;
			case 'removeSolicitedOpenFeedback': //DONE
				$r->result = removeSolicitedOpenFeedback($userId, $payload->feedbackId);
				break;			
			case 'updateFeedback':  //DONE
				$r->result = updateFeedback($payload->comment, $payload->disposition, $userId, $payload->feedbackId);
				break;
			case 'updateTodoFeedback':  //DONE
				$r->result = updateTodoFeedback($payload->comment, $payload->disposition, $userId, $payload->feedbackId);
				break;	
			case 'updateFeedbackStatus': //DONE
				$r->result = updateFeedbackStatus($payload->feedbackId, $payload->status, $userId );
				break;
//REMOVED POST ALPHA				
//			case 'forceFinalize': //DONE
//				$r->result = finalizeFeedback($payload->feedbackId);
//				break;	
			case 'reportReviewerFeedback': //DONE
				$r->result = reportReviewerFeedback($userId, $payload->dateFrom, $payload->dateTo);
				break;	
			case 'reportUserFeedback': //DONE
				$r->result = reportUserFeedback($userId, $payload->dateFrom, $payload->dateTo);
				break;	
			case 'notificationCounts': //DONE
			{
				$r->result = new stdClass();
				$r->result->reviews = getReviewCount($userId);
				$r->result->todo = getTodoCount($userId);
				break;
			}
			default:
			{
					$r->error = -1;
					$r->error_message = "Unknown Command: ".$payload->Command;
					break;
			}
		}
	}
	catch (Exception $e)
	{
		$r->error = -4;
		$r->error_message = $e->getMessage();
	}
}
echo json_encode($r);
return;
	
?>