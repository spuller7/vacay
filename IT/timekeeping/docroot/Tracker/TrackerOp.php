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
require_once( "./TrackerManager.php" );

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
	case 'SET_PERIOD':
		$periods = explode(',', $um_periods);
		if ( $um_val == -1 )
		{
			foreach ($periods as $period)
				TrackerUnit::deleteById( $um_Userid, $um_date, $period );
		}
		else
		{
			foreach ($periods as $period)
				TrackerUnit::createById($um_Userid, $um_date, $period, $um_val );
		}
		foreach ($periods as $period)
		{
			$unit = TrackerUnit::retrieveTrackerUnitByID($um_Userid, $um_date, $period);
			if ( $unit == null )
			{
				echo $period."\n";
			}
			else
			{
				echo $unit->Period.",".$unit->Projectid.",".$unit->State."\n";
			}
		}
		break;
	case 'RETURN_SCHEDULE':
		$units = TrackerUnit::getAllTrackerUnitsForDateByID($um_Userid,$um_date);
		foreach ( $units as $unit )
		{
			echo $unit->Period.",".$unit->Projectid.",".$unit->State."\n";
		}
		$note = TrackerUnit::getNote($um_Userid,$um_date);
		echo $note;
		break;
	case 'GET_HOURS_FOR_DAY':
		echo sprintf("%01.2f", TrackerUnit::retrieveHoursTotalForDayById($um_Userid,$um_date));
		break;
	case 'GET_HOURS_FOR_PERIOD_DIV':
		$time = strtotime($um_date);
		if (date('d', $time) > 15)
		{
			$startdate = date('Y-m-16',$time);
			$enddate = date('Y-m-t',$time);
			$offset = date("w", mktime(0, 0, 0, date("n", $time), 16, date("Y", $time)));
		}
		else
		{
			$startdate = date('Y-m-01',$time);
			$enddate = date('Y-m-15',$time);
			$offset = date("w", mktime(0, 0, 0, date("n", $time), 1, date("Y", $time)));
		}
		echo "<br/><table width=100%><tr><td colspan=2 align=center class='TrackerText'><b>Summary of this period</b></td></tr>";
		echo "<tr><td><table width=100%><tr><td align=center><table>\n\t<tr>\n";
		echo "<th class='TrackerText'>S</th><th class='TrackerText'>M</th><th class='TrackerText'>T</th><th class='TrackerText'>W</th><th class='TrackerText'>T</th><th class='TrackerText'>F</th><th class='TrackerText'>S</th></tr><tr>";
		$year = date('Y', strtotime($startdate));
		$month = date('m', strtotime($startdate));
		$endday = date('d', strtotime($enddate));
		$positioninweek = 0;
		for($i = 0; $i < $offset; $i++)
    	{
    		echo "\t\t<td class='Daynotinperiod'></td>\n";
    		$positioninweek++;
    	}
    	$startday = (int)date('d', strtotime($startdate));
		for ($d = $startday; $d <= $endday; $d++)
		{
			$todaytime = mktime(0,0,0,$month,$d,$year);
			if (TrackerUnit::isTimeEntered($um_Userid, date('Y-m-d', $todaytime)))
			{
				if (TrackerUnit::isDateAuthorized($um_Userid, date('Y-m-d', $todaytime), true)) $class="Authorizedday";
				else $class = "Unauthorizedday";
			}
			else $class = "Dayempty";
			echo "\t\t<td class='MiniCalTD'><div id='MiniCal".$d."' class='".$class."' onclick='onDateChange($year, $month, $d);'><b>".str_pad($d,2,"0",STR_PAD_LEFT)."</b></div></td>\n";
			if (++$positioninweek == 7)
			{
				echo "\t</tr>\n";
				$positioninweek = 0;
				if ($d != $endday) echo "\t<tr>\n";
			}
		}
		if ($positioninweek != 0)
		{
			for($positioninweek; $positioninweek < 7; $positioninweek++)
	    	{
	    		echo "\t\t<td class='Daynotinperiod'></td>\n";
	    	}
	    	echo "\t</tr>\n";
		}
		echo "</table>";
		echo "</td></tr>";
		echo "<tr><td align=right class='TrackerText'>Period total: <span id='HoursTotalForCurrentPeriod'>".sprintf("%01.2f", TrackerUnit::retrieveHoursTotalForPeriodById($um_Userid, $startdate, $enddate))."</span>";
		echo "</td></tr>";
		echo "<tr><td align=center class='TrackerText'>";
		echo "<u>Legend</u><br/>";
		echo "<table><tr><td class='Authorizedday'>&nbsp;</td><td class='TrackerText'>Submitted time</td></tr>";
		echo "<tr><td class='Unauthorizedday'>&nbsp;</td><td class='TrackerText'>Time entered</td></tr>";
		echo "<tr><td class='Dayempty'>&nbsp;</td><td class='TrackerText'>No time</td></tr>";
		echo "</table></td></tr>";
		echo "</table>";
		break;
	case 'IS_PERIOD_EDITABLE':
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
		if (TrackerUnit::isPeriodAuthorized($um_Userid, $startdate, $enddate, true)) echo "TRUE";
		else echo "FALSE";
		break;
	case 'DRAW_SCHEDULE':
		$ranges = TrackerRange::recognizeRange(TrackerUnit::getAllTrackerUnitsForDateById($um_Userid,$um_date));
		$codes = Project::listCodes();
		  ?><table class='WorkPaneListTable' cellpadding=0 cellspacing=0><tr class='WorkPaneListAlt1'>
		  <th class='WorkPaneTD'>StartPeriod</th><th class='WorkPaneTD'>EndPeriod</th><th class='WorkPaneTD'>ProjectCode</th><th class='WorkPaneTD'>State</th><th class='WorkPaneTD'>Action</th><th class='WorkPaneTD'>Delete</th></tr>
		  <tr class='WorkPaneListAlt1'><td class='WorkPaneTD'><?php
		  if (count($ranges)>0)
		  {
		  	if (is_int(($ranges[0]->StartPeriod) / 4))
		  		$period = (floor(($ranges[0]->StartPeriod) / 4) * 4) - 4;
		  	else
		  		$period = (floor(($ranges[0]->StartPeriod) / 4) * 4);
		  }
		  else
		  	$period = date("G") * 4;
		  if ($period < 0) $period=0;
		  emitPeriodList('firstNewStartPeriod', $period);
		  ?></td>
		  <td class='WorkPaneTD'><?php
		  if (count($ranges)>0)
		  	$period = $ranges[0]->StartPeriod;
		  else
		  	$period = (date("G") * 4) + 4;
		  emitPeriodList('firstNewEndPeriod', $period);
		  ?></td>
		  <td class='WorkPaneTD'><select id='firstNewProjectCode'>
		<option id="SELECT" value="-1" SELECTED>SELECT CODE</option>
		<?php
		
		foreach ( $codes as $code )
		{
			echo '<option value="'.$code->ProjectID.'">'.$code->ProjectCode.'</option>'."\n";
		}
		?>
		</select></td><td class='WorkPaneTD'></td>
		<td class='WorkPaneTD' align='center'><input type='button' value='Submit' onclick='SubmitNewUnit(document.getElementById("trackerUser").value, document.getElementById("trackerDate").value, document.getElementById("firstNewStartPeriod").value, document.getElementById("firstNewStartPeriod").value, document.getElementById("firstNewEndPeriod").value, document.getElementById("firstNewEndPeriod").value, document.getElementById("firstNewProjectCode").value);'/></td><td class='WorkPaneTD'></td></tr>
		 <?php
		 if (count($ranges)>0)
		{
			$rangeCount = 0;
			foreach ($ranges as $unit)
			{
				echo "<tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>";
				emitPeriodList('StartPeriod'.$rangeCount, $unit->StartPeriod);
				//echo $unit->StartPeriod;
				echo "</td><td class='WorkPaneTD'>";
				emitPeriodList('EndPeriod'.$rangeCount, $unit->EndPeriod+1);
				//echo $unit->EndPeriod;
				echo "</td><td class='WorkPaneTD'>";
				echo "<select id='ProjectCode".$rangeCount."'>";
				foreach ( $codes as $code )
				{
					echo '<option value="'.$code->ProjectID.'"';
					if ($code->ProjectID == $unit->Projectid) echo " SELECTED";
					echo '>'.$code->ProjectCode.'</option>'."\n";
				}
				echo "</select>";
				//echo $unit->ProjectCode;
				echo "</td><td class='WorkPaneTD'>".$unit->State."</td>";
				if (($unit->State == 'open') || ($unit->State == 'retracted') || ($unit->State == 'rejected'))
				{
					echo "<td class='WorkPaneTD' align='center'><input type='button' value='Submit' onclick='SubmitNewUnit(document.getElementById(\"trackerUser\").value, document.getElementById(\"trackerDate\").value, ".$unit->StartPeriod.", document.getElementById(\"StartPeriod".$rangeCount."\").value, ".($unit->EndPeriod+1).", document.getElementById(\"EndPeriod".$rangeCount."\").value, document.getElementById(\"ProjectCode".$rangeCount."\").value);'></td>";
					echo "<td class='WorkPaneTD'><a href='#' onclick='DeleteUnit(document.getElementById(\"trackerUser\").value, document.getElementById(\"trackerDate\").value, document.getElementById(\"StartPeriod".$rangeCount."\").value, document.getElementById(\"EndPeriod".$rangeCount."\").value ); return false;'><img  style='vertical-align: bottom' border='0' src='../images/trash_16.gif'>Delete</a></tr>";
				}
				else
					echo "<td class='WorkPaneTD'></td><td class='WorkPaneTD'></td></tr>";
				$rangeCount++;
			}
		 ?><tr class='WorkPaneListAlt1'><td class='WorkPaneTD'><?php
		 emitPeriodList('lastNewStartPeriod', $unit->EndPeriod+1);
		 ?></td>
		  <td class='WorkPaneTD'><?php
		  if (is_int(($unit->EndPeriod+1)/4))
		  	$period = (ceil(($unit->EndPeriod+1)/4) * 4)+4;
		  else
		  	$period = (ceil(($unit->EndPeriod+1)/4) * 4);
		  if ($period>96) $period = 96;
		  emitPeriodList('lastNewEndPeriod', $period);
		  ?></td>
		  <td class='WorkPaneTD'><select id='lastNewProjectCode'>
		<option id="SELECT" value="-1" SELECTED>SELECT CODE</option>
		<?php
		
		foreach ( $codes as $code )
		{
			echo '<option value="'.$code->ProjectID.'">'.$code->ProjectCode.'</option>'."\n";
		}
		?>
		</select></td><td class='WorkPaneTD'></td>
		<td class='WorkPaneTD' align='center'><input type='button' value='Submit' onclick='SubmitNewUnit(document.getElementById("trackerUser").value, document.getElementById("trackerDate").value, document.getElementById("lastNewStartPeriod").value, document.getElementById("lastNewStartPeriod").value, document.getElementById("lastNewEndPeriod").value, document.getElementById("lastNewEndPeriod").value, document.getElementById("lastNewProjectCode").value);'/></td><td class='WorkPaneTD'></td></tr>
		<?php
		}
		?>
		 </table><table><tr><td align='right'><input type='button' value='Authorize' onclick='Authorize(document.getElementById("trackerDate").value, document.getElementById("trackerUser").value);'/>
		 &nbsp;&nbsp;<input type='button' value='Retract' onclick='Retract(document.getElementById("trackerDate").value, document.getElementById("trackerUser").value);'/></td></tr></table><?php
		break;
	case "GETPOPUPDIVCONTENT":
		$canViewPrivate = hasRole(Roles::VIEW_PRIVATE);
		$commonList = TrackerUnit::getOrderedProjects($um_Userid, 5);
		echo "<div class='PopupRemove' onMouseOver='this.className=\"PopupSelect\";' onMouseOut='this.className=\"PopupRemove\";' onclick='selectProject( -1 ); return false;'>[remove]</div>\n";
		foreach ( $commonList as $commonItem )
		{
			if (($commonItem->Type == "private") && (!$canViewPrivate)) continue;
			if ($commonItem->State != "active") continue;
			$className = "Popup".($commonItem->num > 1 ? "Popular" : "Rest");
			echo "<div class='".$className."' onMouseOver='this.className=\"PopupSelect\";' onMouseOut='this.className=\"".$className."\";' onclick='selectProject( ".$commonItem->ProjectID." ); return false;'>".$commonItem->ProjectCode."</div>\n";
		}
		break;
	case 'DAILY_NOTE_EDIT':
		$text = htmlentities($um_Text,ENT_NOQUOTES,"UTF-8");
		$text = str_replace( "\r\n", "<br />", $text );
		$text = str_replace( "\n", "<br />", $text );
		$text = str_replace( "\r", "<br />", $text );
		TrackerUnit::addNote($um_Userid,$um_Date,$text);
		echo $text;
		break;
	default:
		//don't let them try and hunt out the commands...
		checkRole( "!!BOGUS!!" );
}

?>
