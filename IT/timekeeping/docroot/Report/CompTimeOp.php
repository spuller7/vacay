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

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

checkRole( Roles::REPORTER );

if ( !isset( $um_action ) )
{
	checkRole("!!BOGUS!!");
}

switch ( $um_action )
{
	case "SUBMIT":
		foreach ($_POST as $name => $value)
		{
			if (is_numeric($value))
			{
				if ((substr($name, 0, 4) == "comp") && (substr($name, -6) != "hidden"))
				{
					$userid = substr($name, 4);
					TrackerUnit::setCompTimeForUserById($userid, $um_date, $value);
				}
			}
		}
		echo "OK";
		break;
	case 'DRAW_REPORT':
		$users = AdminUser::listUsers(true);
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
		if (date('d') > 15) $estimatedate = date('Y-m-01');
		else $estimatedate = date('Y-m-16',strtotime("-1 month"));
		
		echo "<div class='TrackerText'>Pay period is <b>".TrackerUnit::calculateHoursInPayPeriod($startdate, $enddate)."</b> hours total</div>";
		echo "<form action='./CompTimeOp.php' method='POST' name='CompTime' id='CompTime'><input type='hidden' name='action' value='SUBMIT'>";
		if ($startdate < $estimatedate) echo "<input type=button value='Fill Estimates' onclick='fillEstimates();' />";
		echo "<table><tr><td>";
		echo "<table class='WorkPaneListTable' cellpadding=0 cellspacing=0>";
		//echo "<tr><td colspan=4><div id='ViewTable'></div></td></tr>";
		echo "<tr class='WorkPaneViewListTitle'><th>User</th><th>Accrued</th><th>Govt</th><th>Total</th><th>Year</th></tr>";
		$iRowCount = 0;
		foreach ( $users as $user )
		{
			if (($user->PartTime) || ($user->State != 'active')) continue;
			echo "<tr class='WorkPaneListAlt".(1+($iRowCount % 2))."'><td class='WorkPaneTD'>".$user->FullName."</td>";
			//TODO: This project category shouldn't be hard coded...
			$cat = ProjectCat::retrieveByName("Govt");
			$realTime = TrackerUnit::getHoursSpentOnProjectsInCategory($user->UserID, $cat->CatID, $startdate, $enddate);
			$totaltimeworked = TrackerUnit::retrieveHoursTotalForPeriodById($user->UserID, $startdate, $enddate);
			$maxTime = TrackerUnit::calculateHoursInPayPeriod($startdate, $enddate);
			$yearTotal = TrackerUnit::getCompTimeTotalForYearV3($user->UserID, $startdate);
			$comptimeest = false;
			$compTime = TrackerUnit::getCompTimeForPeriodFromDB($user->UserID, $startdate);
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
				$estimateTime = $compTime;
				if ($startdate < $estimatedate) $compTime = "-";
			}
			if ($comptimeest) echo "<input type=hidden value='".$estimateTime."' id='comp".$user->UserID."hidden' name='comp".$user->UserID."hidden' />";
			echo "<td class='WorkPaneTD'><input type=text name='comp".$user->UserID."' id='comp".$user->UserID."' value='".$compTime."' size=6".($comptimeest ? " style='background-color: yellow;'" : "")." /></td>";
			echo "<td class='WorkPaneTD'>".$realTime."</td>";
			echo "<td class='WorkPaneTD'>".$totaltimeworked."</td>";
			echo "<td class='WorkPaneTD'>".$yearTotal."</td></tr>";
			$iRowCount++;
		}
		//echo "<tr class='WorkPaneSectionFooter'><td colspan=4><div class='RoundFooter'></div></td></tr>";
		echo "</table>";
		echo "</td></tr><tr><td align=right>";
		echo "<input type=button value='Submit' id='submit' onclick='javascript:SubmitCompTimes(\"".$startdate."\");'/>";
		echo "</td></tr></table>";
		echo "</form>";
		break;
	default:
		//don't let them try and hunt out the commands...
		checkRole( "!!BOGUS!!" );
}

?>
