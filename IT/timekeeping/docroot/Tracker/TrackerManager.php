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
require_once( dirname(__FILE__)."/../common.php" );
require_once( dirname(__FILE__)."/../utils.php" );
require_once( dirname(__FILE__)."/../Settings/UserManager.php" );
require_once( dirname(__FILE__)."/../Project/ProjectManager.php" );
require_once( dirname(__FILE__)."/../DB/DBManager.php" );

class HoursForPeriodVO
{
	public $date;
	public $minEntry;
	public $maxEntry;
	public $numEntries;
}

class ProjectsForPeriodVO
{
	public $date;
	public $projectID;
	public $numBlocks;
}

class ProjectPeriodVO
{
	public $UserID;
	public $ProjectID;
	public $numHours;
}

class AuthorizeeUnit
{
	public $UserID;
	public $FullName;
	public $PartTime;
	public $period;
	public $State;
	public $class;
}

class TrackerUnit
{
	public $Userid;
	public $Username;
	public $Date;
	public $Period;
	public $ProjectCode;
	public $Projectid;
	public $State;
	public $ChangeUserID;
	public $ChangeDate;
				
	public static function createById( $userid, $Date, $Period, $projectid, $State = "open" )
	{
		executeSQL( "REPLACE INTO TimeEntryRecord ( UserID, Date, Period, ProjectID, State, ChangeUserID, ChangeDate ) VALUES ( :userid, :date, :period, :projectid, :state, '".$_SESSION['USERID']."', NOW() )",
			array ('userid' => $userid, 'date' => $Date, 'period' => $Period, 'projectid' => $projectid, 'state' => $State ) );
	}
	
	public static function createByAPI( $userid, $Date, $Period, $projectid, $State = "open" )
	{
		$changeuser = $userid;
		executeSQL( "REPLACE INTO TimeEntryRecord ( UserID, Date, Period, ProjectID, State, ChangeUserID, ChangeDate ) VALUES ( :userid, :date, :period, :projectid, :state, :changeuser, NOW() )",
			array ('userid' => $userid, 'date' => $Date, 'period' => $Period, 'projectid' => $projectid, 'state' => $State, 'changeuser' => $changeuser ) );
	}

	public static function deleteById( $userid, $Date, $Period )
	{
		$sth = executeSQL( "DELETE FROM TimeEntryRecord WHERE UserID = :userid AND Date = :date AND Period = :period AND State IN ('open', 'retracted', 'rejected')",
			array( 'userid' => $userid, 'date' => $Date, 'period' => $Period) );
	}
	
	public static function createRangeById( $UserId, $Date, $StartPeriod, $EndPeriod, $ProjectId, $State = "open" )
	{
		for ($StartPeriod; $StartPeriod <= $EndPeriod; $StartPeriod++)
		{
			TrackerUnit::createById($UserId, $Date, $StartPeriod, $ProjectId, $State);
		}
	}
	
	public static function deleteRangeById( $Userid, $Date, $StartPeriod, $EndPeriod )
	{
		$sth = executeSQL("DELETE FROM TimeEntryRecord WHERE UserID = :userid AND Date = :date AND Period >= :startPeriod AND Period <= :endPeriod AND State IN ('open', 'retracted', 'rejected')",
			array( 'userid' => $Userid, 'date' => $Date, 'startPeriod' => $StartPeriod, 'endPeriod' => $EndPeriod ) );
	}


	public static function getAllTrackerUnitsForDateById($userid, $Date)
	{
		$sth = executeSQL( "SELECT  u.Username, te.Period, pc.ProjectCode, te.ProjectID, te.State FROM Users u, TimeEntryRecord te, ProjectCodes pc WHERE u.UserID=te.UserID AND te.UserID = :userid AND te.Date = :date AND te.ProjectID=pc.ProjectID ORDER BY Period",
			array( 'userid'=>$userid, 'date'=>$Date ) );
		$units = array();
		while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
		{
			$aUnit = new TrackerUnit();
			$aUnit->Userid = $userid;
			$aUnit->Username = $obj->Username;
			$aUnit->Date = $Date;
			$aUnit->Period = $obj->Period;
			$aUnit->ProjectCode = $obj->ProjectCode;
			$aUnit->Projectid = $obj->ProjectID;
			$aUnit->State = $obj->State;
			$units[] = $aUnit;
		}
		return $units;
	}
	
	public static function getAllTrackerUnitsForDateByIdForAudit($userid, $Date)
	{
		$sth = executeSQL( "SELECT  u.Username, te.Period, pc.ProjectCode, te.ProjectID, te.State, te.ChangeUserID, te.ChangeDate FROM Users u, TimeEntryRecord te, ProjectCodes pc WHERE u.UserID=te.UserID AND te.UserID = :userid AND te.Date = :date AND te.ProjectID=pc.ProjectID ORDER BY Period",
			array( 'userid'=>$userid, 'date'=>$Date ) );
		$units = array();
		while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
		{
			$aUnit = new TrackerUnit();
			$aUnit->Userid = $userid;
			$aUnit->Username = $obj->Username;
			$aUnit->Date = $Date;
			$aUnit->Period = $obj->Period;
			$aUnit->ProjectCode = $obj->ProjectCode;
			$aUnit->Projectid = $obj->ProjectID;
			$aUnit->State = $obj->State;
			$aUnit->ChangeUserID = $obj->ChangeUserID;
			$aUnit->ChangeDate = $obj->ChangeDate;
			$units[] = $aUnit;
		}
		return $units;
	}
	
	public static function getAllTrackerUnitsForRangeById($userid, $StartDate, $EndDate)
	{
		$sth = executeSQL( "SELECT  u.Username, te.Period, te.Date, pc.ProjectCode, te.ProjectID, te.State FROM Users u, TimeEntryRecord te, ProjectCodes pc WHERE u.UserID=te.UserID AND te.UserID = :userid AND te.Date >= :StartDate and te.Date <= :EndDate AND te.ProjectID=pc.ProjectID ORDER BY Period",
			array( 'userid'=>$userid, 'StartDate'=>$StartDate, 'EndDate'=>$EndDate ) );
		$units = array();
		while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
		{
			$aUnit = new TrackerUnit();
			$aUnit->Userid = $userid;
			$aUnit->Username = $obj->Username;
			$aUnit->Date = $obj->Date;
			$aUnit->Period = $obj->Period;
			$aUnit->ProjectCode = $obj->ProjectCode;
			$aUnit->Projectid = $obj->ProjectID;
			$aUnit->State = $obj->State;
			$units[] = $aUnit;
		}
		return $units;
	}
	
	public static function getAllTrackerUnitsForRangeInState($startdate, $enddate, $state)
	{
		$dateSQL = "";
		$sqlParams = array();
		$sqlParams['state'] = $state;
		if ($startdate != 0)
		{
			$dateSQL = "te.Date >= :startdate ";
			$sqlParams['startdate'] = $startdate;
		}
		if ($enddate != 0)
		{
			if ($dateSQL != "")
			{
				$dateSQL .= "AND ";
				$sqlParams['enddate'] = $enddate;
			}
			$dateSQL .= "te.Date <= :enddate ";
		}
		if ($dateSQL != "") $dateSQL .= "AND ";
		$sth = executeSQL( "SELECT u.UserID, u.FullName, concat( Month(Date),'/',if(Day(Date)<16,1,16),'/',Year(Date),'-',Month(Date),'/',if(Day(Date)<16,15,Day(LAST_DAY(Date))),'/',Year(Date) ) period, te.State FROM Users u, TimeEntryRecord te WHERE u.UserID=te.UserID AND $dateSQL te.State = :state group by Year(Date),Month(Date),if(Day(Date)<16,1,2), te.UserID order by Date",
			$sqlParams );
		return $sth->fetchALL( PDO::FETCH_CLASS,'AuthorizeeUnit' );
	}

	public static function retrieveTrackerUnitByID($userid, $Date, $Period)
	{
		$sth = executeSQL( "SELECT te.UserID Userid, u.Username, te.Date, te.Period, te.ProjectID Projectid, pc.ProjectCode, te.State FROM TimeEntryRecord te, Users u, ProjectCodes pc WHERE te.ProjectID=pc.ProjectID AND te.UserID = u.UserID AND te.UserID = :userid AND te.Date = :date AND te.Period = :period",
			array( 'userid'=>$userid, 'date'=>$Date, 'period'=>$Period ) );
		
		$unit = new TrackerUnit();
		$sth->setFetchMode( PDO::FETCH_INTO, $unit );
		if ( !$sth->fetch() )
		{
			return null;
		}
		return $unit;
	}
	
	public static function updateStateByID($Userid, $Date, $Period, $State)
	{
		executeSQL( "UPDATE TimeEntryRecord SET State = :state, ChangeUserID = '".$_SESSION['USERID']."'), ChangeDate = NOW() WHERE UserID = :userid AND Date = :date AND Period = :period",
			array ( 'state' => $State, 'userid' => $Userid, 'date' => $Date, 'period' => $Period ) );
	}
		
	public static function getOrderedProjects($userid, $numDays)
	{
		$sqlFavorites = "select pc.ProjectID, pc.ProjectCode, pc.Type, pc.State, count(*) num from ".
			"ProjectCodes pc, TimeEntryRecord te ".
			"where te.Date > date_sub(now(), interval :numDays day) and te.UserID = :userid ".
			"and te.ProjectID = pc.ProjectID ".
			"group by pc.ProjectID, pc.ProjectCode order by count(*) desc, ProjectCode";
		
		$sqlAll = "select ProjectID, ProjectCode, Type, State, count(*) num from ProjectCodes ".
			"group by ProjectID, ProjectCode order by count(*) desc, ProjectCode";
		
		$sth = executeSQL( $sqlFavorites, array( 'userid'=>$userid, 'numDays'=>$numDays ));
			
		$codes = array();
	 	while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
			$codes[]=$obj;
		
		$sth = executeSQL( $sqlAll, array());
		while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
			$codes[]=$obj;
		
		return $codes;
	}
	
	public static function convertPeriodToTime($period)
	{
		$hours = floor(($period*15) / 60);
		$minutes = (((($period*15) / 60) - (floor(($period*15) / 60))) * 60);
		if ($hours >= 12)
		{
			if ($hours == 24) $ampm = "AM";
			else $ampm = "PM";
			$hours -= 12;
		}
		else $ampm = "AM";
		if ($hours == 0) $hours = 12;
		return sprintf("%d:%02d%s", $hours, $minutes, $ampm);
	}
	
	public static function convertTimeToPeriod($time)
	{
		$secondsSoFar = strtotime($time) - strtotime("00:00:00");
		return $secondsSoFar / 900;
	}
	
	public static function convertDateToPayPeriod($Date)
	{
		$period = date("n",strtotime($Date));
		$period = (($period - 1) * 2) + 1;
		if ((date("j",strtotime($Date))) > 15)
			$period++;
		return $period;
	}
	
	public static function convertDateToPeriodRange($Date)
	{
		$dates = array();
		$time = strtotime($Date);
		if (date('d', $time) > 15)
		{
			$dates[] = date('Y-m-16',$time);
			$dates[] = date('Y-m-t',$time);
		}
		else
		{
			$dates[] = date('Y-m-01',$time);
			$dates[] = date('Y-m-15',$time);
		}
		return $dates;
	}
	
	public static function calculateHoursInPayPeriodGivenDate($Date)
	{
		list($start, $end) = convertDateToPeriodRange($Date);
		$hoursPaid = 0;
		$checkdate = $start;
		$endloopdate = date ("Y-m-d", strtotime ("+1 day", strtotime($end)));
		while ($checkdate != $endloopdate)
		{
			if (date('N',strtotime($checkdate)) < 6)
				$hoursPaid += 8;
			$checkdate = date ("Y-m-d", strtotime ("+1 day", strtotime($checkdate)));
		}
		return $hoursPaid;
	}
	
	public static function calculateHoursInPayPeriod($start, $end)
	{
		$hoursPaid = 0;
		$checkdate = $start;
		$endloopdate = date ("Y-m-d", strtotime ("+1 day", strtotime($end)));
		while ($checkdate != $endloopdate)
		{
			if (date('N',strtotime($checkdate)) < 6)
				$hoursPaid += 8;
			$checkdate = date ("Y-m-d", strtotime ("+1 day", strtotime($checkdate)));
		}
		return $hoursPaid;
	}
	
	public static function authorizeDayById($userid, $Date)
	{
		$sth = executeSQL( "UPDATE TimeEntryRecord SET State = 'pending', ChangeUserID = '".$_SESSION['USERID']."' WHERE UserID = :userid AND Date = :date",
			array( 'userid'=>$userid, 'date'=>$Date) );
	}
	
	public static function retractDayById($userid, $Date)
	{
		$sth = executeSQL( "UPDATE TimeEntryRecord SET State = 'retracted', ChangeUserID = '".$_SESSION['USERID']."' WHERE UserID = :userid AND Date = :date",
			array( 'userid'=>$userid, 'date'=>$Date) );
	}
	
	public static function authorizeDayByAPI($userid, $Date)
	{
		$changeuser = $userid;
		$sth = executeSQL( "UPDATE TimeEntryRecord SET State = 'pending', ChangeUserID = :changeuser WHERE UserID = :userid AND Date = :date",
			array( 'changeuser' => $changeuser, 'userid'=>$userid, 'date'=>$Date) );
	}
	
	public static function retractDayByAPI($userid, $Date)
	{
		$changeuser = $userid;
		$sth = executeSQL( "UPDATE TimeEntryRecord SET State = 'retracted', ChangeUserID = :changeuser WHERE UserID = :userid AND Date = :date",
			array( 'changeuser' => $changeuser, 'userid'=>$userid, 'date'=>$Date) );
	}
	
	public static function updateStateByPeriod($userid, $StartDate, $EndDate, $state)
	{
		$sth = executeSQL( "UPDATE TimeEntryRecord SET State = :state, ChangeUserID = '".$_SESSION['USERID']."' WHERE UserID = :userid AND Date >= :StartDate and Date <= :EndDate",
			array( 'userid'=>$userid, 'state'=>$state, 'StartDate'=>$StartDate, 'EndDate'=>$EndDate) );
	}
	
	public static function isDateAuthorized($userid, $Date, $suppressTimeEnteredCheck = false)
	{
		if (!$suppressTimeEnteredCheck)
		{
			if (!self::isTimeEntered($userid, $Date)) return false;
		}
		$sth = executeSQL( "SELECT State FROM TimeEntryRecord WHERE UserID = :userid AND Date = :date",
			array( 'userid'=>$userid, 'date'=>$Date) );
		while ($state = $sth->fetchColumn(0))
		{
			if (($state == "open") || ($state == "retracted") || ($state == "rejected")) return false;
		}
		return true;
	}
	
	public static function isPeriodAuthorized($userid, $startate, $enddate, $suppressTimeEnteredCheck = false)
	{
		if (!$suppressTimeEnteredCheck)
		{
			if (!self::isTimeEntered($userid, $Date)) return false;
		}
		$sth = executeSQL( "SELECT State FROM TimeEntryRecord WHERE UserID = :userid AND Date >= :StartDate and Date <= :EndDate",
			array( 'userid'=>$userid, 'StartDate'=>$startate, 'EndDate'=>$enddate) );
		while ($state = $sth->fetchColumn(0))
		{
			if (($state == "pending") || ($state == "authorized") || ($state == "finalized")) return true;
		}
		return false;
	}
	
	public static function getStatesForPeriod($userid, $start, $end)
	{
		$sth = executeSQL( "SELECT State FROM TimeEntryRecord WHERE UserID = :userid AND Date >= :StartDate and Date <= :EndDate",
			array( 'userid'=>$userid, 'StartDate'=>$start, 'EndDate'=>$end) );
		$states = array();
		while ($state = $sth->fetchColumn(0))
		{
			$states[] = $state;
		}
		return array_unique($states);
	}
	
	public static function getYourListOfAuthorizees($startdate, $enddate)
	{
		$dateSQL = "";
		$sqlParams = array();
		if ($startdate != 0)
		{
			$dateSQL = "ter.Date >= :startdate ";
			$sqlParams['startdate'] = $startdate;
		}
		if ($enddate != 0)
		{
			if ($dateSQL != "")
			{
				$dateSQL .= "AND ";
				$sqlParams['enddate'] = $enddate;
			}
			$dateSQL .= "ter.Date <= :enddate ";
		}
		if ($dateSQL != "") $dateSQL .= "AND ";
		if (hasRole(Roles::ADMINISTRATOR))
			$sth = executeSQL("select u.UserID, u.FullName, u.PartTime, concat( Month(Date),'/',if(Day(Date)<16,1,16),'/',Year(Date),'-',Month(Date),'/',if(Day(Date)<16,15,Day(LAST_DAY(Date))),'/',Year(Date) ) period, ter.State from TimeEntryRecord ter, Users u where u.UserID=ter.UserID AND $dateSQL ter.State NOT IN('authorized','finalized') group by ter.UserID, Year(Date),Month(Date),if(Day(Date)<16,1,2), ter.State order by Date",
				$sqlParams );
		else
			$sth = executeSQL("select u.UserID, u.FullName, u.PartTime, concat( Month(Date),'/',if(Day(Date)<16,1,16),'/',Year(Date),'-',Month(Date),'/',if(Day(Date)<16,15,Day(LAST_DAY(Date))),'/',Year(Date) ) period, ter.State from TimeEntryRecord ter, Users u where u.UserID=ter.UserID AND ter.UserID in (SELECT UserID FROM GroupMembership WHERE GroupID IN (SELECT GroupID FROM GroupAuthorizers WHERE UserID = ".$_SESSION['USERID']." UNION SELECT GroupID FROM Groups WHERE Authorizer = ".$_SESSION['USERID'].")) AND u.UserID <> ".$_SESSION['USERID']." AND $dateSQL ter.State NOT IN('authorized','finalized') group by ter.UserID, Year(Date),Month(Date),if(Day(Date)<16,1,2), ter.State order by Date",
				$sqlParams );
		return $sth->fetchALL(PDO::FETCH_CLASS,'AuthorizeeUnit');
	}
	
	public static function getYourListOfAuthorizeesSimple()
	{
		if (hasRole(Roles::ADMINISTRATOR))
			$sth = executeSQL("select u.UserID, u.FullName, u.State, u.PartTime from Users u order by u.FullName asc",
				array() );
		else
			$sth = executeSQL("select u.UserID, u.FullName, u.State, u.PartTime from Users u where u.UserID in (SELECT UserID FROM GroupMembership WHERE GroupID IN (SELECT GroupID FROM GroupAuthorizers WHERE UserID = ".$_SESSION['USERID']." UNION SELECT GroupID FROM Groups WHERE Authorizer = ".$_SESSION['USERID'].")) AND u.UserID <> ".$_SESSION['USERID'],
				array() );
		return $sth->fetchALL(PDO::FETCH_CLASS,'AuthorizeeUnit');
	}
	
	public static function getAuthorizeeProblemPeriods()
	{
		if (hasRole(Roles::ADMINISTRATOR))
			$sth = executeSQL("select concat( Month(Date),'/',if(Day(Date)<16,1,16),'/',Year(Date),'-',Month(Date),'/',if(Day(Date)<16,15,Day(LAST_DAY(Date))),'/',Year(Date) ) period from TimeEntryRecord ter, Users u where u.UserID=ter.UserID AND ter.State NOT IN('authorized','finalized') group by Year(Date),Month(Date),if(Day(Date)<16,1,2) order by Date",
				array() );
		else
			$sth = executeSQL("select concat( Month(Date),'/',if(Day(Date)<16,1,16),'/',Year(Date),'-',Month(Date),'/',if(Day(Date)<16,15,Day(LAST_DAY(Date))),'/',Year(Date) ) period from TimeEntryRecord ter, Users u where u.UserID=ter.UserID AND ter.UserID in (SELECT UserID FROM GroupMembership WHERE GroupID IN (SELECT GroupID FROM GroupAuthorizers WHERE UserID = ".$_SESSION['USERID']." UNION SELECT GroupID FROM Groups WHERE Authorizer = ".$_SESSION['USERID'].")) AND u.UserID <> ".$_SESSION['USERID']." AND ter.State NOT IN('authorized','finalized') group by Year(Date),Month(Date),if(Day(Date)<16,1,2) order by Date",
				array() );
		return $sth->fetchALL(PDO::FETCH_COLUMN);
	}
	
	public static function getFinalizeProblemPeriods()
	{
		$sth = executeSQL( "SELECT concat( Month(Date),'/',if(Day(Date)<16,1,16),'/',Year(Date),'-',Month(Date),'/',if(Day(Date)<16,15,Day(LAST_DAY(Date))),'/',Year(Date) ) period FROM Users u, TimeEntryRecord te WHERE u.UserID=te.UserID AND te.State = 'authorized' group by Year(Date),Month(Date),if(Day(Date)<16,1,2) order by Date",
			array() );
		return $sth->fetchALL( PDO::FETCH_COLUMN );
	}
	
	public static function getTheProblemUsers($startdate, $enddate)
	{
		$sth = executeSQL("select u.FullName FROM Users u, TimeEntryRecord ter WHERE u.UserId=ter.UserID AND ter.State NOT IN('authorized','finalized') AND ter.Date >= :startdate AND ter.Date <= :enddate group by ter.UserID order by ter.UserID",
			array('startdate'=>$startdate, 'enddate'=>$enddate) );
		return $sth->fetchALL(PDO::FETCH_CLASS);
	}
	
	public static function isTimeEntered($userid, $Date)
	{
		$sth = executeSQL( "SELECT count(*) FROM TimeEntryRecord WHERE UserID = :userid AND Date = :date",
			array( 'userid'=>$userid, 'date'=>$Date) );
		if ($sth->fetchColumn(0) > 0) return true;
		else return false;
	}
	
	public static function retrieveProjectsForPeriod( $start, $end, $states = false )
	{
		if ($states == false)
		{
			$sql = "select UserID, ProjectID, count(*)/4.0 numHours from TimeEntryRecord where Date >= :StartDate and Date <= :EndDate group by UserID, ProjectCode";
			$sth = executeSQL( $sql, array( 'StartDate' => $start, 'EndDate' => $end ) );
			return $sth->fetchALL(PDO::FETCH_CLASS,'ProjectPeriodVO');
		}
		else
		{
			$sql = "select UserID, ProjectID, count(*)/4.0 numHours from TimeEntryRecord where State = :state and Date >= :StartDate and Date <= :EndDate group by UserID, ProjectCode";
			$results = array();
			foreach( $states as $state)
			{
				$sth = executeSQL( $sql, array( 'state' => $state, 'StartDate' => $start, 'EndDate' => $end ) );
				$results= array_merge($results, $sth->fetchALL(PDO::FETCH_CLASS,'ProjectPeriodVO'));
			}
			return $results;
		}
	}
	
	public static function retrieveHoursForPeriodById( $userID, $start, $end, $states = false )
	{
		if ($states == false)
		{
			$sql = "select date, min(period) minEntry,max(period) maxEntry,count(*) numEntries from TimeEntryRecord where UserID = :UserID and Date>= :StartDate and Date <= :EndDate group by Date order by Date";
			$sth = executeSQL( $sql, array( 'UserID' => $userID, 'StartDate' => $start, 'EndDate' => $end ) );
			return $sth->fetchALL(PDO::FETCH_CLASS,'HoursForPeriodVO');
		}
		else
		{
			$sql = "select date, min(period) minEntry,max(period) maxEntry,count(*) numEntries from TimeEntryRecord where UserID = :UserID and State = :state and Date>= :StartDate and Date <= :EndDate group by Date order by Date";
			$results = array();
			foreach( $states as $state)
			{
				$sth = executeSQL( $sql, array( 'UserID' => $userID, 'state' => $state, 'StartDate' => $start, 'EndDate' => $end ) );
				$results = array_merge($results, $sth->fetchALL(PDO::FETCH_CLASS,'HoursForPeriodVO'));
			}
			return $results;
		}
	}
	
	/*public static function calculateCompTimeForPeriodById($userID, $start, $end)
	{
		$sql = "select count(*) from TimeEntryRecord where UserID = :UserID and Date>= :StartDate and Date <= :EndDate";
		$sth = executeSQL( $sql, array( 'UserID' => $userID, 'StartDate' => $start, 'EndDate' => $end ) );
		$totalHours = ($sth->fetchColumn(0)) / 4.0;
		$hoursPaid = 0;
		$checkdate = $start;
		$endloopdate = date ("Y-m-d", strtotime ("+1 day", strtotime($end)));
		while ($checkdate != $endloopdate)
		{
			if (date('N',strtotime($checkdate)) < 6)
				$hoursPaid += 8;
			$checkdate = date ("Y-m-d", strtotime ("+1 day", strtotime($checkdate)));
		}
		return $totalHours - $hoursPaid;
	}*/
	
	/*public static function retrieveCompTimeForUserById( $userID )
	{
		$sql = "select Total from CompTime where UserID = :UserID";
		$sth = executeSQL( $sql, array( 'UserID' => $userID ) );
		return ($sth->fetchColumn(0) / 4.0);
	}*/
	
	public static function setCompTimeForUserById( $userID, $date, $value )
	{
		$period = self::convertDateToPayPeriod($date);
		$year = date('Y', strtotime($date));
		$sql = "replace into CompTime (UserID, PayPeriod, Year, PeriodAccrument) VALUES ( :UserID, :period, :year, :value)";
		$sth = executeSQL( $sql, array( 'UserID' => $userID, 'period' => $period, 'year' => $year, 'value' => $value * 4 ) );
	}
	
	public static function getCompTimeForPeriodOrEstimate( $userId, $startdate, $enddate)
	{
		$cat = ProjectCat::retrieveByName("Govt");
		$realTime = self::getHoursSpentOnProjectsInCategory($userId, $cat->CatID, $startdate, $enddate);
		$totaltimeworked = self::retrieveHoursTotalForPeriodById($userId, $startdate, $enddate);
		$maxTime = TrackerUnit::calculateHoursInPayPeriod($startdate, $enddate);
		$yearTotal = TrackerUnit::getCompTimeTotalForYearV3($userId, $startdate);
		$comptimeest = false;
		$compTime = TrackerUnit::getCompTimeForPeriodFromDB($userId, $startdate);
		if (is_numeric($compTime)) $compTime /= 4.0;
		else
		{
			$comptimeest = true;
			$compTime = max($realTime - $maxTime,0);
			if ($compTime == 0)
			{
				if ($totaltimeworked < $maxTime)
					$compTime = $totaltimeworked - $maxTime;
			}
		}
		$returnArray = array();
		$returnArray["comptimeest"] = $comptimeest;
		$returnArray["compTime"] = $compTime;
		return $returnArray;
	}
	
	public static function getCompTimeForPeriodFromDB( $userId, $date)
	{
		$period = self::convertDateToPayPeriod($date);
		$year = date('Y', strtotime($date));
		$sql = "select PeriodAccrument from CompTime where UserID = :userid and PayPeriod = :period and Year = :year";
		$sth = executeSQL( $sql, array( 'userid' => $userId, 'period' => $period, 'year' => $year) );
		return $sth->fetchColumn(0);
	}
	
	public static function getCompTimeTotalForYear( $userId, $date)
	{
		$year = date('Y', strtotime($date));
		$sql = "select sum(PeriodAccrument) from CompTime where UserID = :userid and Year = :year";
		$sth = executeSQL( $sql, array( 'userid' => $userId, 'year' => $year) );
		return ($sth->fetchColumn(0) / 4.0);
	}
	
	public static function getCompTimeTotalForYearV2( $userId, $date)
	{
		$year = date('Y', strtotime($date));
		$sql = "select sum(PeriodAccrument) from CompTime where UserID = :userid and Year = :year and PeriodAccrument > 0";
		$sth = executeSQL( $sql, array( 'userid' => $userId, 'year' => $year) );
		return ($sth->fetchColumn(0) / 4.0);
	}
	
	public static function getCompTimeTotalForYearV3( $userId, $date)
	{
		$period = self::convertDateToPayPeriod($date);
		$year = date('Y', strtotime($date));
		$sql = "select PeriodAccrument from CompTime where UserID = :userid and Year = :year and PayPeriod <= :period";
		$sth = executeSQL( $sql, array( 'userid' => $userId, 'year' => $year, 'period' => $period) );
		$timetotal = 0;
		while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
		{
			$timetotal += $obj->PeriodAccrument;
			if ($timetotal < 0) $timetotal = 0;
		}
		return ($timetotal / 4.0);
	}
	
	/*public static function updateCompTimeForUserById( $userID, $value )
	{
		$prev = TrackerUnit::retrieveCompTimeForUserById($userID);
		TrackerUnit::setCompTimeForUserById($userID, $prev + $value);
	}*/
	
	public static function getHoursSpentOnProjectsInCategory( $userID, $catID, $start, $end )
	{
		$sql = "select count(*) from TimeEntryRecord t, ProjectCodes p where t.ProjectID=p.ProjectID and t.Date >= :StartDate and t.Date <= :EndDate and p.CatID = :CatID and UserID = :UserID";
		$sth = executeSQL( $sql, array( 'StartDate' => $start, 'EndDate' => $end, "UserID" => $userID, "CatID" => $catID ) );
		return ($sth->fetchColumn(0) / 4.0);
	}
	
	public static function retrieveHoursTotalForDayById( $userID, $date, $states = false )
	{
		if ($states == false)
		{
			$sql = "select count(*) from TimeEntryRecord where UserID = :UserID and Date = :Date";
			$sth = executeSQL( $sql, array( 'UserID' => $userID, 'StartDate' => $start, 'EndDate' => $end ) );
			return (($sth->fetchColumn(0)) / 4.0);
		}
		else
		{
			$sql = "select count(*) from TimeEntryRecord where UserID = :UserID and State = :state and Date = :Date";
			$results = 0;
			foreach( $states as $state)
			{
				$sth = executeSQL( $sql, array( 'UserID' => $userID, 'state' => $state, 'StartDate' => $start, 'EndDate' => $end ) );
				$results += (($sth->fetchColumn(0)) / 4.0);
			}
			return $results;
		}
	}
	
	public static function retrieveHoursTotalForPeriodById( $userID, $start, $end, $states = false )
	{
		if ($states == false)
		{
			$sql = "select count(*) from TimeEntryRecord where UserID = :UserID and Date>= :StartDate and Date <= :EndDate";
			$sth = executeSQL( $sql, array( 'UserID' => $userID, 'StartDate' => $start, 'EndDate' => $end ) );
			return (($sth->fetchColumn(0)) / 4.0);
		}
		else
		{
			$sql = "select count(*) from TimeEntryRecord where UserID = :UserID and Date>= :StartDate and Date <= :EndDate";
			$results = 0;
			foreach( $states as $state)
			{
				$sth = executeSQL( $sql, array( 'UserID' => $userID, 'state' => $state, 'StartDate' => $start, 'EndDate' => $end ) );
				$results += (($sth->fetchColumn(0)) / 4.0);
			}
			return $results;
		}
	}
	
	public static function retrieveProjectCountsForPeriodById( $userID, $start, $end, $states = false )
	{
		if ($states == false)
		{
			$sql = "select date, projectID, count(*) numBlocks from TimeEntryRecord where UserID = :UserID and Date>= :StartDate and Date <= :EndDate group by Date, ProjectID order by Date";
			$sth = executeSQL( $sql, array( 'UserID' => $userID, 'StartDate' => $start, 'EndDate' => $end ) );
			return $sth->fetchALL(PDO::FETCH_CLASS,'ProjectsForPeriodVO');
		}
		else
		{
			$sql = "select date, projectID, count(*) numBlocks from TimeEntryRecord where UserID = :UserID and State = :state and Date>= :StartDate and Date <= :EndDate group by Date, ProjectID order by Date";
			$results = array();
			foreach( $states as $state)
			{
				$sth = executeSQL( $sql, array( 'UserID' => $userID, 'state' => $state, 'StartDate' => $start, 'EndDate' => $end ) );
				$results = array_merge($results, $sth->fetchALL(PDO::FETCH_CLASS,'ProjectsForPeriodVO'));
			}
			return $results;
		}
	}
	
	public static function retrieveFullProjectHoursForPeriodWithState( $userID, $start, $end )
	{
		$sql = "select ProjectID, count(*) numBlocks, state from TimeEntryRecord where UserID = :UserID and Date>= :StartDate and Date <= :EndDate group by ProjectID, state";
		$sth = executeSQL( $sql, array( 'UserID' => $userID, 'StartDate' => $start, 'EndDate' => $end ) );
		return $sth->fetchALL(PDO::FETCH_OBJ);
	}
	
	public static function retrieveFullProjectHoursForPeriodById( $userID, $start, $end, $states = false )
	{
		if ($states == false)
		{
			$sql = "select ProjectID, count(*) numBlocks from TimeEntryRecord where UserID = :UserID and Date>= :StartDate and Date <= :EndDate group by ProjectID";
			$sth = executeSQL( $sql, array( 'UserID' => $userID, 'StartDate' => $start, 'EndDate' => $end ) );
			$hours = array();
		 	while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
			{
				$hours[$obj->ProjectID]=$obj->numBlocks;
			}
			return $hours;
		}
		else
		{
			$sql = "select ProjectID, count(*) numBlocks from TimeEntryRecord where UserID = :UserID and State = :state and Date>= :StartDate and Date <= :EndDate group by ProjectID";
			$results = array();
			foreach( $states as $state)
			{
				$sth = executeSQL( $sql, array( 'UserID' => $userID, 'state' => $state, 'StartDate' => $start, 'EndDate' => $end ) );
				$hours = array();
			 	while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
				{
					$hours[$obj->ProjectID]=$obj->numBlocks;
				}
				$results = array_merge($results, $hours);
			}
			return $results;
		}
	}
	
	public static function checkExistsById($userid, $Date, $Period)
	{
		$sth = executeSQL( "SELECT Count(*) FROM TimeEntryRecord WHERE UserID = :userid AND Date = :date AND Period = :period",
			array( 'userid'=>$userid, 'date'=>$Date, 'period'=>$Period) );
		return $sth->fetchColumn(0);
	}
	
	public static function addNote($userid, $Date, $note)
	{
		if ($note != '')
		{
			executeSQL( "REPLACE INTO TimeEntryNote ( UserID, Date, Note ) VALUES ( :userid, :date, :note )",
				array ('userid' => $userid, 'date' => $Date, 'note' => $note ) );
		}
		else
		{
			executeSQL( "DELETE FROM TimeEntryNote WHERE UserID = :userid AND Date = :date",
				array ('userid' => $userid, 'date' => $Date ) );
		}
	}
	
	public static function getNote($userid, $Date)
	{
		$sth = executeSQL( "SELECT Note FROM TimeEntryNote WHERE UserID = :userid AND Date = :date",
			array( 'userid'=>$userid, 'date'=>$Date ) );
		return $sth->fetchColumn(0);
	}
	
	public static function addPeriodNote($userid, $Date, $note)
	{
		$period = self::convertDateToPayPeriod($Date);
		$year = date('Y', strtotime($Date));
		if ($note != '')
		{
			executeSQL( "REPLACE INTO PeriodEntryNote ( UserID, PayPeriod, Year, Note ) VALUES ( :userid, :period, :year, :note )",
				array ('userid' => $userid, 'period' => $period, 'year' => $year, 'note' => $note ) );
		}
		else
		{
			executeSQL( "DELETE FROM PeriodEntryNote WHERE UserID = :userid AND PayPeriod = :period AND Year = :year",
				array ('userid' => $userid, 'period' => $period, 'year' => $year));
		}
	}
	
	public static function getPeriodNote($userid, $Date)
	{
		$period = self::convertDateToPayPeriod($Date);
		$year = date('Y', strtotime($Date));
		$sth = executeSQL( "SELECT Note FROM PeriodEntryNote WHERE UserID = :userid AND PayPeriod = :period AND Year = :year",
			array( 'userid'=>$userid, 'period'=>$period, 'year'=>$year ) );
		return $sth->fetchColumn(0);
	}
	
	/*public static function calculateCompTimeThisYear($userid, $Date)
	{
		$year = date('Y',strtotime($Date));
		$startdate = $year.'-01-01';
		$checkdate = $startdate;
		$time = strtotime($Date);
		if (date('d', $time) > 15)
			$enddate = date('Y-m-01',strtotime(date('Y-m-15',$time)." +1 month"));
		else
			$enddate = date('Y-m-16',$time);
		$comptime = 0;
		while ($checkdate != $enddate)
		{
			$time = strtotime($checkdate);
			if (date('d', $time) > 15)
			{
				$periodstartdate = date('Y-m-16',$time);
				$periodenddate = date('Y-m-t',$time);
			}
			else
			{
				$periodstartdate = date('Y-m-01',$time);
				$periodenddate = date('Y-m-15',$time);
			}
			$sth = executeSQL( "select count(*) from TimeEntryRecord where UserID = :UserID and State = 'finalized' and Date>= :StartDate and Date <= :EndDate",
				array( 'UserID' => $userid, 'StartDate' => $periodstartdate, 'EndDate' => $periodenddate ) );
			$hoursworked = $sth->fetchColumn(0) / 4;
			error_log($periodstartdate." thru ".$periodenddate.": ".$hoursworked);
			if ($hoursworked > 0)
			{
				$hourspaid = 0;
				$checkdate = $periodstartdate;
				$endloopdate = date ("Y-m-d", strtotime ("+1 day", strtotime($periodenddate)));
				while ($checkdate != $endloopdate)
				{
					if (date('N',strtotime($checkdate)) < 6)
						$hourspaid += 8;
					$checkdate = date ("Y-m-d", strtotime ("+1 day", strtotime($checkdate)));
				}
				if ($hourspaid < $hoursworked) $comptime += $hoursworked - $hourspaid;
			}
			else
				$checkdate = date ("Y-m-d", strtotime ("+1 day", strtotime($periodenddate)));
		}
		return $comptime;
	}*/
	
	public static function rangeSummaryHTML($userid, $states, $startdate, $enddate, $editdisabled = true, $nohours = false, $links = false)
	{		
		$projects = array();
		$projCounts = TrackerUnit::retrieveProjectCountsForPeriodById( $userid, $startdate, $enddate);
		$projLookup = array();
		$numOfProjs = 0;
		
		$projBlob = ProjectCat::getSortedProjectList();
		$cats = array();
		$projs = array();
		$lastcount=0;
		
		foreach($projBlob as $obj)
		{
			if (get_class($obj) == "Project")
			{
				$projs[$lastcount++] = $obj->ProjectID;
			}
		}
		
		foreach ( $projCounts as $entry )
		{
			$date = $entry->date;
			$code = $entry->projectID;
			if ( !isset( $projLookup[$date] ) )
			{
				$projLookup[$date] = array();
			}
			$projLookup[$date][$code] = $entry->numBlocks;
			if ( !isset( $projects[$code] ) )
			{
				$numOfProjs++;
				$projects[$code] = 0;
			}
			$projects[$code] += $entry->numBlocks;
		}
		
		$dates = TrackerUnit::retrieveHoursForPeriodById( $userid, $startdate, $enddate);
		
		$noNotes = false;
		for ($checkdate = $startdate; strtotime($checkdate) < strtotime($enddate); $checkdate = date("Y-m-d", strtotime("$checkdate +1 day")))
		{
			$note = TrackerUnit::getNote($userid, $checkdate);
			if (strlen($note) > 0)
			{
				$noNotes = true;
				break;
			}
		}
		if ($nohours) $editdisabled = false;
		if ($editdisabled && !$noNotes && !$nohours) $hideNotes = true;
		else $hideNotes = false;
		
		echo "<table class='WorkPaneListTable' cellpadding=0 cellspacing=0><tr class='WorkPaneListAlt2'>";
		echo "<th class='WorkPaneTD'>DAY</th><th class='WorkPaneTD'><div style='width:75px;'>DATE</div></th><th class='WorkPaneTD'>START</th><th class='WorkPaneTD'>END</th><th class='WorkPaneTD'>HOURS</th>";
		foreach ( $projs as $projid)
		{
				if ( isset( $projects[$projid] ) )
				{
					echo "<th class='WorkPaneTD'><div style='width:65px;'>".Project::getProjectCodeFromID($projid)."</div></th>";
				}
		}
		if (!$hideNotes)
			echo "<th class='WorkPaneTD'><div style='width:150px;'>DAILY NOTES</div></th>";
		echo "</tr>";
		$totalHours = 0;
		$nextDate = $startdate;
		foreach ( $dates as $aDate )
		{
			while ($aDate->date != $nextDate)
			{
				echo "<tr class='WorkPaneListAlt1'>";
				echo "<td class='WorkPaneTDHead'";
				if ($links) echo " onclick=\"javascript:jumpBackToTracker('".date("U",strtotime($nextDate))."');\"";
				echo "><b>".(date('l', strtotime($nextDate)))."</b></td><td class='WorkPaneTDHead'";
				if ($links) echo " onclick=\"javascript:jumpBackToTracker('".date("U",strtotime($nextDate))."');\"";
				echo ">".$nextDate."</td>";
				echo "<td class='WorkPaneTD'></td><td class='WorkPaneTD'></td><td class='WorkPaneTD'></td>";
				foreach($projs as $projid) if (isset($projects[$projid])) echo "<td class='WorkPaneTD'></td>";
				if (!$hideNotes)
				{
					$note = TrackerUnit::getNote($userid, $nextDate);
					echo "<td class='WorkPaneTD'><div id='".$nextDate."UPDATE_DAILY_NOTE'>";
					if (!$editdisabled)
						echo "<a href='#' title='Edit this text' onclick=\"EditText( '".$userid."', '".$nextDate."', '".escapeQuotes($note)."', 'UPDATE_DAILY_NOTE' ); return false;\"><span class='donotprint'><img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'></span></a>";
					echo $note."</div></td>";
				}
				echo "</tr>";
				$nextDate = date('Y-m-d', strtotime("$nextDate +1 day"));
			}
			$nextDate = date('Y-m-d', strtotime("$nextDate +1 day"));
			echo "<tr class='WorkPaneListAlt1'>";
			$hours = $aDate->numEntries/4.0;
			$totalHours += $hours;
			echo "<td class='WorkPaneTDHead'";
			if ($links) echo " onclick=\"javascript:jumpBackToTracker('".date("U",strtotime($aDate->date))."');\"";
			echo "><b>".(date('l', strtotime($aDate->date)))."</b></td><td class='WorkPaneTDHead'";
			if ($links) echo " onclick=\"javascript:jumpBackToTracker('".date("U",strtotime($aDate->date))."');\"";
			echo ">".$aDate->date."</td><td class='WorkPaneTD'>".TrackerUnit::convertPeriodToTime($aDate->minEntry)."</td><td class='WorkPaneTD'>".TrackerUnit::convertPeriodToTime(($aDate->maxEntry) + 1)."</td><td class='WorkPaneTD' align=right>".(sprintf("%01.2f", ($hours)))."</td>";
			foreach($projs as $projid)
			{
				if ( isset( $projects[$projid] ) )
				{
					$proj = $projid;
					$blocks = $projects[$proj];
					if ( isset( $projLookup[$aDate->date] ) && isset($projLookup[$aDate->date][$proj]) )
					{
						echo "<td class='WorkPaneTD' align=right>".(sprintf("%01.2f", ($projLookup[$aDate->date][$proj]/4.0)))."</td>";	
					}
					else
					{
						echo "<td class='WorkPaneTD'></td>";
					}
				}
			}
			if (!$hideNotes)
			{
				$note = TrackerUnit::getNote($userid, $aDate->date);
				echo "<td class='WorkPaneTD'><div id='".$aDate->date."UPDATE_DAILY_NOTE'>";
				if (!$editdisabled)
					echo "<a href='#' title='Edit this text' onclick=\"EditText( '".$userid."', '".$aDate->date."', '".escapeQuotes($note)."', 'UPDATE_DAILY_NOTE' ); return false;\"><span class='donotprint'><img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'></span></a>";
				echo $note."</div></td>";
			}
			echo "</tr>";
		}
		while (strtotime($nextDate) <= strtotime($enddate))
		{
			echo "<tr class='WorkPaneListAlt1'>";
			echo "<td class='WorkPaneTDHead'";
			if ($links) echo " onclick=\"javascript:jumpBackToTracker('".date("U",strtotime($nextDate))."');\"";
			echo "><b>".(date('l', strtotime($nextDate)))."</b></td><td class='WorkPaneTDHead'";
			if ($links) echo " onclick=\"javascript:jumpBackToTracker('".date("U",strtotime($nextDate))."');\"";
			echo ">".$nextDate."</td>";
			echo "<td class='WorkPaneTD'></td><td class='WorkPaneTD'></td><td class='WorkPaneTD'></td>";
			foreach($projs as $projid) if (isset($projects[$projid])) echo "<td class='WorkPaneTD'></td>";
			if (!$hideNotes)
			{
				$note = TrackerUnit::getNote($userid, $nextDate);
				echo "<td class='WorkPaneTD'><div id='".$nextDate."UPDATE_DAILY_NOTE' style='width:125px;'>";
				if (!$editdisabled)
					echo "<a href='#' title='Edit this text' onclick=\"EditText( '".$userid."', '".$nextDate."', '".escapeQuotes($note)."', 'UPDATE_DAILY_NOTE' ); return false;\"><span class='donotprint'><img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'></span></a>";
				echo $note."</div></td>";
			}
			echo "</tr>";
			$nextDate = date('Y-m-d', strtotime("$nextDate +1 day"));
		}
		echo "<tr class='WorkPaneListAlt2'><td colspan=4 class='WorkPaneTD'></td>";
		echo "<td class='WorkPaneTD' align=right><b>".(sprintf("%01.2f", $totalHours))."/".TrackerUnit::calculateHoursInPayPeriod($startdate,$enddate)."</b></td>";
		foreach($projs as $projid)
		{
			if ( isset( $projects[$projid] ) )
			{
				$proj = $projid;
				$blocks = $projects[$proj];
				echo "<td class='WorkPaneTD' align=right><b>".(sprintf("%01.2f", ($blocks/4.0)))."</b><br/>";
				echo (sprintf("%01.2f", (100*$blocks/4.0)/$totalHours))."%</td>";
			}
		}
		if (!$hideNotes) echo "<td class='WorkPaneTD'></td>";
		echo "</tr></table><br/>";
		$note = TrackerUnit::getPeriodNote($userid, $startdate);
		if (!$editdisabled || (strlen($note) != 0))
		{
			echo "<table class='WorkPaneListTable' cellpadding=0 cellspacing=0 width=100%><tr class='WorkPaneListAlt2'><th class='WorkPaneTD'>PERIOD NOTES</th></tr>";
			echo "<tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>";
			echo "<div id='".$startdate."UPDATE_PERIOD_NOTE'>";
			if (!$editdisabled)
				echo "<a href='#' title='Edit this text' onclick=\"EditText( '".$userid."', '".$startdate."', '".escapeQuotes($note)."', 'UPDATE_PERIOD_NOTE' ); return false;\"><span class='donotprint'><img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'></span></a>";
			echo $note."</div></td>";
			echo "</td></tr></table>";
		}
		return $totalHours;
	}

}

class AuditRecord
{
	public $UserID;
	public $Date;
	public $Period;
	public $ProjectID;
	public $State;
	public $ChangeDate;
	public $ChangeUserID;
	
	public static function retrieveAuditRecord($userid, $date, $period)
	{
		$sth = executeSQL( "SELECT UserID, Date, Period, ProjectID, State, ChangeDate, ChangeUserID FROM AuditRecord WHERE UserID = :userid AND Date = :date AND Period = :period",
			array( 'userid'=>$userid, 'date'=>$date, 'period'=>$period ) );
		return $sth->fetchALL( PDO::FETCH_CLASS,'AuditRecord');
	}
	
	public static function retrieveAuditRecordByDay($userid, $date)
	{
		$sth = executeSQL( "SELECT UserID, Date, Period, ProjectID, State, ChangeDate, ChangeUserID FROM AuditRecord WHERE UserID = :userid AND Date = :date",
			array( 'userid'=>$userid, 'date'=>$date ) );
		return $sth->fetchALL( PDO::FETCH_CLASS,'AuditRecord' );
	}
	
	public static function retrieveAuditRecordRange($userid, $startdate, $enddate)
	{
		$sth = executeSQL( "SELECT UserID, Date, Period, ProjectID, State, ChangeDate, ChangeUserID FROM AuditRecord WHERE UserID = :userid AND Date >= :StartDate and Date <= :EndDate",
			array( 'userid'=>$userid, 'StartDate'=>$startdate, 'EndDate'=>$enddate ) );
		return $sth->fetchALL( PDO::FETCH_CLASS,'AuditRecord' );
	}
}

class TrackerRange
{
	public $Userid;
	public $Date;
	public $StartPeriod;
	public $EndPeriod;
	public $Projectid;
	public $State;
	
	public static function recognizeRange( $units )
	{
		if (count($units)>0)
		{
			$ranges = array();
			$startPeriod = $units[0]->Period;
			$lastPeriod = $units[0]->Period;
			$lastCode = $units[0]->Projectid;
			$lastState = $units[0]->State;
			for($i=1; $i<count($units);$i++)
			{
				if (($units[$i]->Period != ($lastPeriod + 1)) || ($units[$i]->Projectid != $lastCode) || ($units[$i]->State != $lastState))
				{
					$range = new TrackerRange();
					$range->Userid = $units[$i-1]->Userid;
					$range->Date = $units[$i-1]->Date;
					$range->StartPeriod = $startPeriod;
					$range->EndPeriod = $units[$i-1]->Period;
					$range->Projectid = $units[$i-1]->Projectid;
					$range->State = $units[$i-1]->State;
					$ranges[] = $range;
					$startPeriod = $units[$i]->Period;
					$lastCode = $units[$i]->Projectid;
					$lastState = $units[$i]->State;
				}
				$lastPeriod = $units[$i]->Period;
			}
			$range = new TrackerRange();
			$range->Userid = $units[$i-1]->Userid;
			$range->Date = $units[$i-1]->Date;
			$range->StartPeriod = $startPeriod;
			$range->EndPeriod = $units[$i-1]->Period;
			$range->Projectid = $units[$i-1]->Projectid;
			$range->State = $units[$i-1]->State;
			$ranges[] = $range;
			return $ranges;
		}
	}
}

?>
