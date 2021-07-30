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
require_once( "../Tracker/TrackerManager.php" );
require_once( "../Settings/UserManager.php" );

if (!(AdminUser::isAuthorizer($_SESSION['USERID'])))
{
	header( "HTTP/1.1 403 Forbidden" );
	echo "This account does not have sufficient privileges to perform this operation";
	die();
}

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

if ( !isset( $um_action ) )
{
	checkRole("!!BOGUS!!");
}

if (isset($um_Userid))
{
	$users = AdminUser::listUsersUnderAuthorizer($_SESSION['USERID']);
	$foundmatch = false;
	foreach ($users as $user)
	{
		if ($user->UserID == $um_Userid)
		{
			$foundmatch = true;
			break;
		}
	}
	if (!$foundmatch) checkRole("!!BOGUS!!");
}

switch ( $um_action )
{
	case 'AUTHORIZE':
		TrackerUnit::updateStateByPeriod($um_Userid, $um_StartDate, $um_EndDate, 'authorized');
		echo "OK";
		break;
	case 'REJECT':
		TrackerUnit::updateStateByPeriod($um_Userid, $um_StartDate, $um_EndDate, 'rejected');
		echo "OK";
		break;
	case 'DRAW_REPORT':
		$user = AdminUser::retrieve($um_Userid);
		$time = strtotime($um_date);
		if (date('d', $time) > 15)
		{
			$startdate = date('Y-m-16',$time);
			$enddate = date('Y-m-t',$time);
		}
		else
		{
			$startdate = date('Y-m-01',$time);
			$enddate = date('Y-m-15',$time);
		}
		
		echo "<table width=100%><tr><td><table width=100%><tr><td>";
		echo "<div class='NormalText'><table width=100%><tr><td align=left>".$user->FullName."</td><td align=right>Week starting ".date('j-M-Y',strtotime($startdate))."</td></tr></table></div><br/>";
		
		$totalHours = TrackerUnit::rangeSummaryHTML($um_Userid, array('pending'), $startdate, $enddate);
		if ($totalHours > 0)
		{
			echo "<table width=100% class='donotprint'><tr><td align=right><input type='button' value='Authorize' id='AuthorizeButton' onclick='authorizePeriod(document.getElementById(\"authorizeUser\").value, \"".$startdate."\", \"".$enddate."\");'/>&nbsp;<input type='button' value='Reject' id='RejectButton' onclick='rejectPeriod(document.getElementById(\"authorizeUser\").value, \"".$startdate."\", \"".$enddate."\");'/></td></tr></table>";
		}
		echo "</tr></td></table></td>";
		if (($totalHours > 0) && !$user->PartTime)
		{
			echo "<td align=right>";
			$compTimeString = "GOVT OVERHOURS ".date('Y', strtotime($startdate));
			$compTime = TrackerUnit::getCompTimeTotalForYearV3( $user->UserID, $startdate );
			?><div class='donotprint'><table class='WorkPaneListTable' cellpadding=0 cellspacing=0><tr class='WorkPaneListAlt2'>
			<th class='WorkPaneTD'><span style='white-space: nowrap;'><?php echo $compTimeString; ?></span></th></tr><tr class='WorkPaneListAlt1'><td class='WorkPaneTD' align='right'><?php echo sprintf("%01.2f",$compTime); ?></td></tr></table></div>
			<br /><?php
			$compTimeArray = TrackerUnit::getCompTimeForPeriodOrEstimate($user->UserID, $startdate, $enddate);
			$compTime = $compTimeArray["compTime"];
			$comptimeest = $compTimeArray["comptimeest"];
			if (($compTime != 0) || hasRole(Roles::REPORTER))
			{
				$compTimeString = "";
				if ($comptimeest) $compTimeString .= "OVERHOURS GUESS";
				else $compTimeString .= "PERIOD OVERHOURS";
				?><div class='donotprint'><table class='WorkPaneListTable' cellpadding=0 cellspacing=0><tr class='WorkPaneListAlt2'>
				<th class='WorkPaneTD'><span style='white-space: nowrap;'><?php echo $compTimeString; ?></span></th></tr><tr class='WorkPaneListAlt1'><td class='WorkPaneTD' align='right'><span style='white-space: nowrap;'><?php
				if (hasRole(Roles::REPORTER))
				{
					echo "<div id='COMP_TIME_EDIT'><span style='white-space: nowrap;'>";
					echo "<a href='#' title='Edit this text' onclick=\"EditCompText( '".$user->UserID."', '".$startdate."', '".(sprintf("%01.2f",$compTime))."', 'COMP_TIME_EDIT' ); return false;\"><span class='donotprint'><img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'></span></a>";
					echo sprintf("%01.2f",$compTime);
					echo "</span></div>";
				}
				else echo sprintf("%01.2f",$compTime);
				?></span></td></tr></table></div><?php
			}
			echo "</td>";
		}
		echo "</tr></table>";
		break;
	case 'DRAW_OVERVIEW':
		if (isset($um_history))
		{
			$startdate = 0;
			$enddate = 0;
		}
		else
		{
			$time = strtotime($um_date);
			if (date('d', $time) > 15)
			{
				$startdate = date('Y-m-01',$time);
				$enddate = date('Y-m-t',$time);
			}
			else
			{
				$startdate = date('Y-m-16',strtotime("-1 month",$time));
				$enddate = date('Y-m-15',$time);		
			}
		}
		
		$authorizees = TrackerUnit::getYourListOfAuthorizees($startdate,$enddate);
		if (count($authorizees) < 1)
		{
			echo "There is no time awaiting authorization in the currently selected pay period!";
			break;
		}
		$lastperiod = "";
		$stateTotals = array();
		for($i=0; $i<count($authorizees);$i++)
		{
			if (($authorizees[$i]->period == $lastperiod) && ($authorizees[$i-1]->FullName == $authorizees[$i]->FullName))
			{
				$authorizees[$i-1]->class = "WorkPaneListError";
				$authorizees[$i]->class = "WorkPaneListError";
			}
			else $authorizees[$i]->class = "AuthorizeList".$authorizees[$i]->State;
			if (array_key_exists($authorizees[$i]->State,$stateTotals)) $stateTotals[$authorizees[$i]->State] = ++$stateTotals[$authorizees[$i]->State];
			else $stateTotals[$authorizees[$i]->State] = 1;
			$lastperiod = $authorizees[$i]->period;
		}
		?><table><tr><td>
		<table class='WorkPaneListTable' cellpadding=0 cellspacing=0><tr class='WorkPaneListAlt1'>
		<th class='WorkPaneTD'>DATE</th><th class='WorkPaneTD'>NAME</th><th class='WorkPaneTD'>STATE</th></tr><?php
		$lastperiod = "";
		foreach($authorizees as $authorizee)
		{
			echo "<tr class='".$authorizee->class."'><td class='WorkPaneTD'>".($authorizee->period==$lastperiod?'':$authorizee->period)."</td><td class='WorkPaneTD'>";
			if ($authorizee->State == "pending") echo "<a href='./ReportAuthorize.php?userid=".$authorizee->UserID."&date=".date("m-d-Y",strtotime(substr($authorizee->period,strrpos($authorizee->period,'-')+1))).(isset($um_history)?'&history='.$um_history:'')."'>".$authorizee->FullName."</a>";
			else echo $authorizee->FullName;
			echo "</td><td class='WorkPaneTD'>".$authorizee->State."</td></tr>";
			$lastperiod = $authorizee->period;
		}
		echo "</table>";
		?></td><td valign=top><table class='WorkPaneListTable' cellpadding=0 cellspacing=0><tr class='WorkPaneListAlt1'>
		<th class='WorkPaneTD'>STATE</th><th class='WorkPaneTD'>TOTAL</th></tr><?php
		foreach($stateTotals as $statename => $statetotal)
		{
			echo "<tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>".$statename."</td><td class='WorkPaneTD'>".$statetotal."</td></tr>";
		}
		?></table></td></tr></table><?php
		
		break;
	case 'DRAW_MAINTENANCE':
		$badperiods = TrackerUnit::getAuthorizeeProblemPeriods();
		if (count($badperiods) < 1)
		{
			echo "Congratulations! People whom you authorize have no time before this date which hasn't been authorized.";
			break;
		}
		?><table class='WorkPaneListTable' cellpadding=0 cellspacing=0><tr class='WorkPaneListAlt1'>
		<th class='WorkPaneTD'>DATE</th></tr><?php
		foreach($badperiods as $badperiod)
		{
			echo "<tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>";
			$linkdate = strstr($badperiod,'-');
			echo "<a href='./ReportAuthorize.php?date=".substr($linkdate,1)."'>".$badperiod."</a>";
			echo "</td></tr>";
		}
		echo "</table>";
		break;
	case 'DRAW_COMP':
		$time = strtotime($um_date);
		if (date('d', $time) > 15)
		{
			$startdate = date('Y-m-16',$time);
			$enddate = date('Y-m-t',$time);
		}
		else
		{
			$startdate = date('Y-m-01',$time);
			$enddate = date('Y-m-15',$time);
		}
		$users = TrackerUnit::getYourListOfAuthorizeesSimple();
		echo "<table class='WorkPaneListTable' cellpadding=0 cellspacing=0>";
		echo "<tr class='WorkPaneViewListTitle'><th>User</th><th>Total</th></tr>";
		$iRowCount = 0;
		foreach ( $users as $user )
		{
			if (($user->PartTime) || ($user->State != 'active')) continue;
			echo "<tr class='WorkPaneListAlt".(1+($iRowCount % 2))."'><td class='WorkPaneTD'>".$user->FullName."</td>";
			$yearTotal = TrackerUnit::getCompTimeTotalForYearV3($user->UserID, $startdate);
			echo "<td class='WorkPaneTD'>".$yearTotal."</td></tr>";
			$iRowCount++;
		}
		echo "</table>";
		break;
	default:
		//don't let them try and hunt out the commands...
		checkRole( "!!BOGUS!!" );
}

?>
