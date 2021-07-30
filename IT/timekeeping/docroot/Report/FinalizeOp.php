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

checkRole( Roles::REPORTER );

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

if ( !isset( $um_action ) )
{
	checkRole("!!BOGUS!!");
}

switch ( $um_action )
{
	case 'FINALIZE':
		TrackerUnit::updateStateByPeriod($um_Userid, $um_StartDate, $um_EndDate, 'finalized');
		echo "OK";
		break;
	case 'REJECT':
		TrackerUnit::updateStateByPeriod($um_Userid, $um_StartDate, $um_EndDate, 'rejected');
		echo "OK";
		break;
	case 'UNLOCK':
		checkRole( Roles::ADMINISTRATOR );
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
		
		switch($um_mode)
		{
		case 'finalize':
			$states[] = 'authorized';
			break;
		case 'unlock':
			$states[] = 'finalized';
			break;
		}
		
		$totalHours = TrackerUnit::rangeSummaryHTML($um_Userid, $states, $startdate, $enddate);
		if ($totalHours > 0)
		{
			echo "<table width=100% class='donotprint'><tr><td align=right>";
			switch($um_mode)
			{
			case 'finalize':
				echo "<input type='button' value='Finalize' id='FinalizeButton' onclick='finalizePeriod(document.getElementById(\"finalizeUser\").value, \"".$startdate."\", \"".$enddate."\");'/>&nbsp;<input type='button' value='Reject' id='RejectButton' onclick='rejectPeriod(document.getElementById(\"finalizeUser\").value, \"".$startdate."\", \"".$enddate."\");'/>";
				break;
			case 'unlock':
				if ( hasRole( Roles::ADMINISTRATOR ) )
					echo "<input type='button' value='Unlock' id='UnlockButton' onclick='unlockPeriod(document.getElementById(\"finalizeUser\").value, \"".$startdate."\", \"".$enddate."\");'/>";
				break;
			}
			echo "</td></tr></table>";
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
				$startdate = date('Y-m-16',$time);
				$enddate = date('Y-m-t',$time);
			}
			else
			{
				$startdate = date('Y-m-01',$time);
				$enddate = date('Y-m-15',$time);
			}
		}
		
		switch($um_mode)
		{
		case 'finalize':
			$state = 'authorized';
			break;
		case 'unlock':
			$state = 'finalized';
			break;
		}
		$finalizees = TrackerUnit::getAllTrackerUnitsForRangeInState($startdate,$enddate,$state);
		if (count($finalizees) < 1)
		{
			echo "There is no time that can be ";
			if ($um_mode=='unlock') echo 'unlocked';
			elseif ($um_mode=='finalize') echo 'finalized';
			echo " in the currently selected pay period!";
			break;
		}
		$lastperiod = "";
		for($i=0; $i<count($finalizees);$i++)
		{
			if (($finalizees[$i]->period == $lastperiod) && ($finalizees[$i-1]->FullName == $finalizees[$i]->FullName))
			{
				$finalizees[$i-1]->class = "WorkPaneListError";
				$finalizees[$i]->class = "WorkPaneListError";
			}
			//else $finalizees[$i]->class = "AuthorizeList".$finalizees[$i]->State;
			else $finalizees[$i]->class = "WorkPaneListAlt1";
			$lastperiod = $finalizees[$i]->period;
		}
		?><table class='WorkPaneListTable' cellpadding=0 cellspacing=0><tr class='WorkPaneListAlt1'>
		<th class='WorkPaneTD'>DATE</th><th class='WorkPaneTD'>NAME</th><th class='WorkPaneTD'>STATE</th></tr><?php
		$lastperiod = "";
		foreach($finalizees as $finalizee)
		{
			echo "<tr class='".$finalizee->class."'><td class='WorkPaneTD'>".($finalizee->period==$lastperiod?'':$finalizee->period)."</td>";
			echo "<td class='WorkPaneTD'><a href='./ReportFinalize.php?userid=".$finalizee->UserID."&date=".date("m-d-Y",strtotime(substr($finalizee->period,strrpos($finalizee->period,'-')+1)))."&action=".$um_mode.(isset($um_history)?'&history='.$um_history:'')."'>".$finalizee->FullName."</a>";
			echo "</td><td class='WorkPaneTD'>".$finalizee->State."</td></tr>";
			$lastperiod = $finalizee->period;
		}
		echo "</table>";
		
		/*if (($startdate != 0) && ($um_mode=='finalize'))
		{
			$userlist = TrackerUnit::getTheProblemUsers($startdate,$enddate);
			if (count($userlist) > 0)
			{
				$output = "<p>The following users are problems: ";
				foreach($userlist as $user)
					$output .= $user->FullName.", ";
				$output = substr($output,0,-2);
				$output .= ".</p>";
				echo $output;
			}
		}*/
		
		break;
	case 'DRAW_MAINTENANCE':
		$badperiods = TrackerUnit::getFinalizeProblemPeriods();
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
			echo "<a href='./ReportFinalize.php?date=".substr($linkdate,1)."'>".$badperiod."</a>";
			echo "</td></tr>";
		}
		echo "</table>";
		break;
	default:
		//don't let them try and hunt out the commands...
		checkRole( "!!BOGUS!!" );
}

?>
