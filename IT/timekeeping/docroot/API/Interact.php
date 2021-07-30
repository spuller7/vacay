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
require_once( "../DB/DBManager.php" );
require_once( "../utils.php" );
require_once( "../Tracker/TrackerManager.php" );
require_once( "../Settings/UserManager.php" );

function createAPIResponse($tag)
{
	$xml = new SimpleXMLElement("<".$tag."/>");
	$xml['xmlns:xsi']="http://www.w3.org/2001/XMLSchema-instance";
	$xml['xmlns:xsd']="http://www.w3.org/2001/XMLSchema";
	$xml['xmlns']="http://www.quantumsignal.com/TimekeepingReqResp.xsd";
	return $xml;
}

function addError( &$xml, $code, $msg )
{
	$xml->error = $msg;
	$xml->error['code'] = $code;
}

if ( isset( $_GET['Validate']) && $_GET['Validate'] == 1 )
{
	echo "OK";
	exit();
}

try
{
	$xmlData = file_get_contents( "php://input" );
	$xmlRequest = simplexml_load_string( $xmlData );
	$xmlResponse = null;
	
	if ( $xmlRequest == null)
	{
		throw new Exception("Missing XML Payload");
	}
	
	switch ( $xmlRequest->getName() )
	{
		case "EnumerateTrackerUnitsRequest":
		{
			$xmlResponse = createAPIResponse("EnumerateTrackerUnitsResponse");
			try
			{
				$units = TrackerUnit::getAllTrackerUnitsForDateById($xmlRequest->userId, $xmlRequest->Date);
				$trackerunits = $xmlResponse->addChild("TrackerUnits");
				foreach ( $units as $unit )
				{
					$trackerunit = $trackerunits->addChild("TrackerUnit");
					$trackerunit->UserID = (string)$xmlRequest->userId;
					$trackerunit->Date = (string)$xmlRequest->Date;
					$trackerunit->Period = $unit->Period;
					$trackerunit->ProjectID = $unit->Projectid;
					$trackerunit->State = $unit->State;
				}
			}
			catch ( Exception $e )
			{
				addError( $xmlResponse, '0002', $e->getMessage() );
			}
			break;			
		}
		case "EnumerateProjectsRequest":
		{
			$xmlResponse = createAPIResponse("EnumerateProjectsResponse");
			try
			{
				$codes = Project::listCodes();
				$projectcodes = $xmlResponse->addChild("ProjectCodes");
				foreach ( $codes as $code )
				{
					$projectcode = $projectcodes->addChild("ProjectCode");
					$projectcode->ProjectID = $code->ProjectID;
					$projectcode->ProjectCode = $code->ProjectCode;
				}
			}
			catch ( Exception $e )
			{
				addError( $xmlResponse, '0002', $e->getMessage() );
			}
			break;			
		}
		case "TrackerUnitSubmitRequest":
		{
			$xmlResponse = createAPIResponse("TrackerUnitSubmitResponse");
			try
			{
				TrackerUnit::createByAPI($xmlRequest->UserID, $xmlRequest->Date, $xmlRequest->Period, $xmlRequest->ProjectID);
			}
			catch ( Exception $e )
			{
				addError( $xmlResponse, '0002', $e->getMessage() );
			}
			break;			
		}
		case "TrackerUnitRangeSubmitRequest":
		{
			$xmlResponse = createAPIResponse("TrackerUnitRangeSubmitResponse");
			try
			{
				$StartPeriod = (int)$xmlRequest->StartPeriod;
				$EndPeriod = (int)$xmlRequest->EndPeriod;
				for ($StartPeriod; $StartPeriod <= $EndPeriod; $StartPeriod++)
					TrackerUnit::createByAPI($xmlRequest->UserID, $xmlRequest->Date, $StartPeriod, $xmlRequest->ProjectID);
			}
			catch ( Exception $e )
			{
				addError( $xmlResponse, '0002', $e->getMessage() );
			}
			break;			
		}
		case "AuthenticateRequest":
		{
			$xmlResponse = createAPIResponse("AuthenticateResponse");
			try
			{
				$username = $xmlRequest->username;
				$password = $xmlRequest->password;
				
				$subj = AdminUser::retrieveByName($username);
				if ( $subj == null || $subj->PasswordHash != $password )
				{
					$xmlResponse->authenticationResult = "Rejected";
				}
				else
				{
					$xmlResponse->authenticationResult = "Authenticated";
				}
			}
			catch ( Exception $e )
			{
					$xmlResponse->authenticationResult = "Error";		
					addError( $xmlResponse, '0002', $e->getMessage() );
			}
			break;
		}
		case "UserIDRequest":
		{
			$xmlResponse = createAPIResponse("UserIDResponse");
			try
			{
				$username = $xmlRequest->username;
				
				$UserID = AdminUser::getUserID($username);
				
				$xmlResponse->userId = $UserID;
			}
			catch ( Exception $e )
			{
					$xmlResponse->authenticationResult = "Error";		
					addError( $xmlResponse, '0002', $e->getMessage() );
			}
			break;
		}
		case "SubmitDayRequest":
		{
			$xmlResponse = createAPIResponse("SubmitDayResponse");
			try
			{
				TrackerUnit::authorizeDayByAPI($xmlRequest->UserID, $xmlRequest->Date);
			}
			catch ( Exception $e )
			{
				$xmlResponse->authenticationResult = "Error";		
				addError( $xmlResponse, '0002', $e->getMessage() );
			}
			break;			
		}
		case "RetractDayRequest":
		{
			$xmlResponse = createAPIResponse("RetractDayResponse");
			try
			{
				TrackerUnit::retractDayByAPI($xmlRequest->UserID, $xmlRequest->Date);
			}
			catch ( Exception $e )
			{
				$xmlResponse->authenticationResult = "Error";		
				addError( $xmlResponse, '0002', $e->getMessage() );
			}
			break;			
		}
		case "IsDaySubmittedRequest":
		{
			$xmlResponse = createAPIResponse("IsDaySubmittedResponse");
			try
			{
				if (TrackerUnit::isDateAuthorized($xmlRequest->UserID, $xmlRequest->Date))
					$xmlResponse->Response = 1;
				else
					$xmlResponse->Response = 0;
			}
			catch ( Exception $e )
			{
				$xmlResponse->authenticationResult = "Error";		
				addError( $xmlResponse, '0002', $e->getMessage() );
			}
			break;			
		}
		case "NOPRequest":
		{
			$xmlResponse = createAPIResponse( 'NOPResponse' );
			break;
		}
		default:
		{
			$xmlResponse = createAPIResponse( 'error' );
			$xmlResponse = "Unimplemented method";
			$xmlResponse['code'] = '0001';
			break;
		}
	}
	if ( $xmlResponse != null )
	{
		echo $xmlResponse->asXML();
	}
	else
	{
		header( "HTTP/1.1 400 Bad Request" );
		echo "The request was invalid or malformed";
	}
}
catch ( Exception $exception )
{
	header( "HTTP/1.1 400 Bad Request");
	echo $exception->Message;
}
?>