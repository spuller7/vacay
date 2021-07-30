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

if ( !isset( $um_action ) )
{
	checkRole("!!BOGUS!!");
}

if ( isset($um_Username ) )
{
	if ($um_Username != $_SESSION['USER_NAME'])
	{
		checkRole( Roles::USER_MANAGER );
	}
}

if ( isset( $um_Userid ) )
{
	if ($um_Userid != $_SESSION['USERID'])
	{
		checkRole( Roles::USER_MANAGER );
	}
}

switch ( $um_action )
{
	case "SUBMIT":
		TrackerUnit::updateStateByPeriod($um_Userid, $um_StartDate, $um_EndDate, 'pending');
		echo "OK";
		break;
	case "RETRACT":
		TrackerUnit::updateStateByPeriod($um_Userid, $um_StartDate, $um_EndDate, 'retracted');
		echo "OK";
		break;
	case 'DRAW_REPORT':
		$notbi = true;
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
		
	case 'DRAW_REPORT_BI':
		if (!isset($notbi))
		{
			$user = AdminUser::retrieve($um_Userid);
	                $time = strtotime($um_date);
			$biweeklyrange = get_biweekly_range($um_date);
                        $startdate = $biweeklyrange[0];
                        $enddate = $biweeklyrange[1];
		}
		
		echo "<table width=100%><tr><td><table width=100%><tr><td>";
		$printName = $user->FullName;
		if ($printName == "") $printName = $user->Username;
		echo "<div class='donotdisplay'><table width=100%><tr><td align=left>".$printName."</td><td align=right>Week starting ".date('j-M-Y',strtotime($startdate))."</td></tr></table><br/><br/><br/></div>";
		
		$states = TrackerUnit::getStatesForPeriod($um_Userid, $startdate, $enddate);
		$submitdisabled = true;
		$retractdisabled = true;
		$nohours = false;
		if (count($states) > 0)
		{
			foreach ($states as $state)
			{
				switch($state)
				{
					case 'open':
					case 'retracted':
					case 'rejected':
						$submitdisabled = false;
						break;
					case 'pending':
						$retractdisabled = false;
						break;
				}
			}
		}
		else $nohours = true;
		
		$totalHours = TrackerUnit::rangeSummaryHTML($um_Userid, $states, $startdate, $enddate, $submitdisabled, $nohours, true);
		if ($totalHours > 0)
		{
			echo "<table width=100% class='donotprint'><tr><td align=left><input type='button' value='Submit' id='SubmitTimeButton'".($submitdisabled? " disabled":"")." onclick='SubmitTime(document.getElementById(\"reportUser\").value, \"".$startdate."\", \"".$enddate."\");'/>&nbsp;<input type='button' value='Retract'".($retractdisabled?" disabled":"")." id='RetractTimeButton' onclick='RetractTime(document.getElementById(\"reportUser\").value, \"".$startdate."\", \"".$enddate."\");'/></td>";
			echo "<td align=right><input type=button value='Print' onclick='printMe();'/></td></tr></table>";
		}
		echo "</td></tr></table>";
		/*if ($submitdisabled && ($totalHours > 0) && !$user->PartTime)
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
		}*/
		echo "</tr></table>";
		break;
	case 'UPDATE_DAILY_NOTE':
		$text = htmlentities($um_Text,ENT_NOQUOTES,"UTF-8");
		$text = str_replace( "\r\n", "<br />", $text );
		$text = str_replace( "\n", "<br />", $text );
		$text = str_replace( "\r", "<br />", $text );
		TrackerUnit::addNote($um_Userid,$um_Date,$text);
		echo $text;
		break;
	case 'UPDATE_PERIOD_NOTE':
		$text = htmlentities($um_Text,ENT_NOQUOTES,"UTF-8");
		$text = str_replace( "\r\n", "<br />", $text );
		$text = str_replace( "\n", "<br />", $text );
		$text = str_replace( "\r", "<br />", $text );
		TrackerUnit::addPeriodNote($um_Userid,$um_Date,$text);
		echo $text;
		break;
	case 'COMP_TIME_EDIT':
		$text = htmlentities($um_Text,ENT_NOQUOTES,"UTF-8");
		$text = str_replace( "\r\n", "<br />", $text );
		$text = str_replace( "\n", "<br />", $text );
		$text = str_replace( "\r", "<br />", $text );
		TrackerUnit::setCompTimeForUserById($um_Userid,$um_Date,$text);
		echo $text;
		break;
	default:
		//don't let them try and hunt out the commands...
		checkRole( "!!BOGUS!!" );
}

?>
