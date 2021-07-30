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
require_once( "../Settings/UserManager.php" );
require_once( "../Project/ProjectManager.php" );
require_once( "../Tracker/TrackerManager.php" );

checkRole( Roles::TRACKER );

//import_request_variables('g', 't_');
extract($_GET, EXTR_PREFIX_ALL, 't');

emitStdProlog("..");

echo '<script>';

if (isset($t_date))
{
	echo "\n\nparent.globalDate = '".date("m/d/Y",$t_date)."';\n";
}

?>
		
		/*********************************************************************
		 * Get an object, this function is cross browser
		 * *** Please do not remove this header. ***
		 * This code is working on my IE7, IE6, FireFox, Opera and Safari
		 * 
		 * Usage: 
		 * var object = get_object(element_id);
		 *
		 * @Author Hamid Alipour Codehead @ webmaster-forums.code-head.com		
		**/
		function get_object(id) {
			var object = null;
			if( document.layers )	{			
				object = document.layers[id];
			} else if( document.all ) {
				object = document.all[id];
			} else if( document.getElementById ) {
				object = document.getElementById(id);
			}
			return object;
		}
		/*********************************************************************/
		
		/*********************************************************************
		 * No onMouseOut event if the mouse pointer hovers a child element 
		 * *** Please do not remove this header. ***
		 * This code is working on my IE7, IE6, FireFox, Opera and Safari
		 * 
		 * Usage: 
		 * <div onMouseOut="fixOnMouseOut(this, event, 'JavaScript Code');"> 
		 *		So many childs 
		 *	</div>
		 *
		 * @Author Hamid Alipour Codehead @ webmaster-forums.code-head.com		
		**/
		function is_child_of(parent, child) {
			if( child != null ) {			
				while( child.parentNode ) {
					if( (child = child.parentNode) == parent ) {
						return true;
					}
				}
			}
			return false;
		}
		function fixOnMouseOut(element, event, JavaScript_code) {
			var current_mouse_target = null;
			if( event.toElement ) {				
				current_mouse_target 			 = event.toElement;
			} else if( event.relatedTarget ) {				
				current_mouse_target 			 = event.relatedTarget;
			}
			if( !is_child_of(element, current_mouse_target) && element != current_mouse_target ) {
				eval(JavaScript_code);
			}
		}
		/*********************************************************************/

function mouseX(evt) {
if (evt.pageX) return evt.pageX;
else if (evt.clientX)
   return evt.clientX + (document.documentElement.scrollLeft ?
   document.documentElement.scrollLeft :
   document.body.scrollLeft);
else return null;
}
function mouseY(evt) {
if (evt.pageY) return evt.pageY;
else if (evt.clientY)
   return evt.clientY + (document.documentElement.scrollTop ?
   document.documentElement.scrollTop :
   document.body.scrollTop);
else return null;
}

var longDays = new Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

function ordinalSuffix(day)
{
	return (day % 10 == 1 && day != 11 ? 'st' : (day % 10 == 2 && day != 12 ? 'nd' : (day % 10 == 3 && day != 13 ? 'rd' : 'th')));
}

var authedPeriod = false;
var projectIDs = new Object();
var projectCodes = new Array();
var projtally = new Array();
<?php 
$codes = Project::listCodes();
foreach ( $codes as $code )
{
	echo "projectIDs['".$code->ProjectCode."'] = ".$code->ProjectID.";\n";
	echo "projectCodes[".$code->ProjectID."] = '".$code->ProjectCode."';\n";
	echo "projtally[".$code->ProjectID."] = 0;\n";
}
?>

var slotProj = new Array();

var projColors = new Array(
'aqua', 'lime','gold','greenyellow',
'hotpink','khaki',
'lightblue','lightgreen','lightpink','lightsalmon',
'lightsteelblue','navajowhite','orange','palegreen','peru' );

var projIndex = 0;

var colors={'':'#DCDCDC'};

function getColor( value )
{
	if ( !colors[value] )
	{
		colors[value] = projColors[projIndex];
		projIndex++;
	}
	return colors[value];
}

function deSelectSlot( i )
{
	var elem = document.getElementById('Period'+i);
	elem.style.border = "1px solid #ffffff";		
}

function deSelectAll()
{
	for (var i=0; i< 96; i++)
	{
		deSelectSlot(i);
	}
}

function selectSlot( i )
{
	var elem = document.getElementById('Period'+i);
	elem.style.border = "1px solid #ff0000";
}

function selectArray( selectedPeriods )
{
	for (var i in selectedPeriods)
	{
		selectSlot( selectedPeriods[i] );
	}
}

var previousDate = -1;
var previousUser = -1;
var hourTotal = 0.0;
var periodTotal = 0.0;
var currentClass;
var redrawing = false;

function updateSummaryLine(i, text, value, className)
{
	i++;
	var row = document.getElementById('dailysummarytable').rows[i];
	row.cells[0].innerHTML = "<div id=\"SummaryCell" + i + "Code\" class=\"Slotempty\">" + text + "</div>";
	row.cells[1].innerHTML = "<div id=\"SummaryCell" + i + "Val\" class=\"Slotempty\">" + value + "</div>";
	var code = document.getElementById('SummaryCell' + i + 'Code');
	var val = document.getElementById('SummaryCell' + i + 'Val');
	code.className = className;
	var color = getColor('');
	if (projectIDs[text] !== undefined) color = getColor(projectIDs[text]);
	code.style.backgroundColor = color;
	val.className = className;
	val.style.backgroundColor = colors[''];
}

function updateSummaryTable(date)
{
	var createrows = false;
	if (document.getElementById('dailysummarytable').rows.length == 1)
	{
		createrows = true;
		var tableRef = document.getElementById('dailysummarytable');
		var row = tableRef.insertRow(tableRef.rows.length);
		row.insertCell(0); row.insertCell(1);
		row.cells[0].className = "TrackerLine"; row.cells[1].className = "TrackerLine";
		row = tableRef.insertRow(tableRef.rows.length);
		row.insertCell(0); row.insertCell(1);
		row.cells[0].className = "TrackerLine"; row.cells[1].className = "TrackerLine";
	}
	var j = 0, total = 0, count = 0;
	for (var i in projtally)
	{
		count++;
		if (projtally[i] != 0)
		{
			if (createrows)
			{
				var tableRef = document.getElementById('dailysummarytable');
				var row = tableRef.insertRow(tableRef.rows.length);
				row.insertCell(0); row.insertCell(1);
				row.cells[0].className = "TrackerLine"; row.cells[1].className = "TrackerLine";
			}
			updateSummaryLine(j, projectCodes[i], projtally[i].toFixed(2), "Slotempty");
			total += projtally[i];
			j++;
		}
	}
	updateSummaryLine(j, "", "", "Slotempty");
	j++;
	updateSummaryLine(j, "<b>TOTAL</b>", "<b>" + total.toFixed(2) + "</b>", "Slotempty");
	if (total == 0)
	{
		document.getElementById('runningprojtotals').style.visibility = 'hidden';
		var dateObj = new Date(getDateFromFormat(date, "yyyy-M-d"));
		var day = dateObj.getDate();
		document.getElementById('MiniCal'+day).className = "Dayempty";
	}
	else document.getElementById('runningprojtotals').style.visibility = 'visible';
}

function setSlot( aRow, date )
{
	values = aRow.split(",");
	if ( values.length == 3 )
	{
		if (values[1] == -1)
			slotProj[values[0]] = null;
		else
			slotProj[values[0]] = values[1];
		var elem = document.getElementById('Period'+values[0]);
		elem.innerHTML=projectCodes[values[1]];
		if ( elem.className=='Slotempty')
		{
			if (previousDate != -1)
			{
				var dateObj = new Date(getDateFromFormat(date, "yyyy-M-d"));
				var day = dateObj.getDate();
				document.getElementById('MiniCal'+day).className = currentClass;
			}
			if (!redrawing)
			{
				periodTotal += 0.25;
				document.getElementById('HoursTotalForCurrentPeriod').innerHTML=periodTotal.toFixed(2);
			}
			
		}
		elem.className = "Slot"+values[2];
		elem.style.backgroundColor = getColor(values[1]);
	}
	else if ( values.length == 1 )
	{
		clearPeriod( values[0], date );
		slotProj[values[0]] = null;
	}
}

function clearPeriod( i, date )
{
	var elem = document.getElementById('Period'+i);
	elem.innerHTML = "&nbsp;";
	if ( elem.className != "Slotempty" )
	{
		if (!redrawing)
		{
			periodTotal -= 0.25;
			document.getElementById('HoursTotalForCurrentPeriod').innerHTML=periodTotal.toFixed(2);
		}
	}
	elem.className = "Slotempty";
	elem.style.backgroundColor = getColor('');
}

function decrementProjectTally(id)
{
	projtally[id] -= 0.25;
	if (projtally[id] == 0)
	{
		var tableRef = document.getElementById('dailysummarytable'); 
		tableRef.deleteRow(tableRef.rows.length-1);
	}
}

function incrementProjectTally(id)
{
	if (projtally[id] == 0)
	{
		var tableRef = document.getElementById('dailysummarytable'); 
		var row = tableRef.insertRow(tableRef.rows.length);
		row.insertCell(0); row.insertCell(1);
		row.cells[0].className = "TrackerLine"; row.cells[1].className = "TrackerLine";
	}
	projtally[id] += 0.25;
}

function setPeriod( selectedPeriods, val, userId, date )
{
	for (var i in selectedPeriods)
	{ 
		//TODO there is no need to get the project code name from the HTML table just to get the project ID from the projectIDs array when it is tracked in the slotProj array
		var oldcode = document.getElementById('Period' + selectedPeriods[i]).innerHTML;
		var code = null;
		if (oldcode != "&nbsp;")
		{
			code = projectIDs[oldcode];
			if ((val != -1) && (code != val)) decrementProjectTally(code);
		}
		if (val == -1)
		{
			if (code != null)
			{
				decrementProjectTally(code);
			}
		}
		else
		{
			if (code != val)
			{
				incrementProjectTally(val);
			}
		}
	}
	var dateObj = new Date(getDateFromFormat(date, "M/d/yyyy"));
	var sqldate = formatDate(dateObj,"yyyy-MM-dd");
	var selectedPeriodsString = "";
	for (var i in selectedPeriods)
	{
		selectedPeriodsString += selectedPeriods[i] + ",";
	}
	var result = doPostDataMap( 'TrackerOp.php', {
		'action' : 'SET_PERIOD',
		'Userid' : userId,
		'date' : sqldate,
		'val' : val,
		'periods': selectedPeriodsString.substr(0, selectedPeriodsString.length - 1)
		} );
	rows = result.split("\n");
	for ( var i in rows)
	{
		if ( rows[i].length > 0 )
			setSlot( rows[i], sqldate );
	}
	updateSummaryTable(sqldate);
}

function RedrawCalendar(date, userId)
{
	if ((userId != -1) && (isDateAlert(date)))
	{
		redrawing = true;
		if ((checkUserChange(userId)) || (checkDateMajorChange(previousDate, date)))
		{
			document.getElementById('PeriodSummaryDiv').innerHTML = doPostDataMap( 'TrackerOp.php', {
				'action' : 'GET_HOURS_FOR_PERIOD_DIV',
				'Userid' : userId,
				'date' : date
			} );
			var authedPeriodStr = doPostDataMap( 'TrackerOp.php', {
				'action' : 'IS_PERIOD_EDITABLE',
				'Userid' : userId,
				'date' : date
			} );
			if (authedPeriodStr == "TRUE") authedPeriod = true;
			else authedPeriod = false;
			periodTotal = parseFloat(document.getElementById('HoursTotalForCurrentPeriod').innerHTML);
			previousDate = -1;
		}
		if (previousDate != -1)
		{
			var oldDateObj = new Date(getDateFromFormat(previousDate, "yyyy-M-d"));
			var oldDay = oldDateObj.getDate();
			document.getElementById('MiniCal'+oldDay).innerHTML = "<b>" + zeroPad(oldDay,2) + "</b>";
		}
		
		previousDate = date;
		
		var dateObj = new Date(getDateFromFormat(date, "yyyy-M-d"));
		var day = dateObj.getDate();
		
		document.getElementById('natedate').innerHTML = "<b>" + longDays[dateObj.getDay()] + " the " + day + "<sup>" + ordinalSuffix(day) + "</sup></b>";
		
		document.getElementById('MiniCal'+day).innerHTML = zeroPad(day,2);
		
		if (document.getElementById('MiniCal'+day).className == "Dayempty")
			currentClass = "Unauthorizedday";
		else
			currentClass = document.getElementById('MiniCal'+day).className;
		
		var result = doPostDataMap( 'TrackerOp.php', {
			'action' : 'RETURN_SCHEDULE',
			'Userid' : userId,
			'date' : date
			} );
			
		for ( var i = 0; i < 96; i ++ )
		{
			clearPeriod(i, date);
			slotProj[i] = null;
		}
		
		rows = result.split("\n");
		
		var note = rows.pop();
		
		CancelDailyNoteTextEdit(userId, date, note, 'DAILY_NOTE_EDIT');
		
		str = "";
		
		for (var i in projtally) projtally[i] = 0;
		
		for ( var i in rows)
		{
			if ( rows[i].length > 0 )
			{
				vals = rows[i].split(",");
				projtally[vals[1]] += 0.25;
				setSlot( rows[i], date );
			}
		}
		
		var tableRef = document.getElementById('dailysummarytable');
		while (tableRef.rows.length > 1) 
			tableRef.deleteRow(tableRef.rows.length-1);
		
		updateSummaryTable(date);
		redrawing = false;
	}
}

function checkDateMajorChange(olddate, newdate)
{
	if (olddate == -1) return true;
	var dateObj = new Date(getDateFromFormat(olddate, "yyyy-M-d"));
	var oldday = dateObj.getDate();
	var oldmonth = dateObj.getMonth();
	dateObj = new Date(getDateFromFormat(newdate, "yyyy-M-d"));
	var newday = dateObj.getDate();
	var newmonth = dateObj.getMonth();
	if (oldmonth != newmonth) return true;
	if ((oldday < 16) && (newday > 15)) return true;
	if ((oldday > 15) && (newday < 16)) return true;
	return false;
}

function checkUserChange(userId)
{
	if (previousUser != userId)
	{
		previousUser = userId;
		document.getElementById('popupcontent').innerHTML = doPostDataMap( 'TrackerOp.php', {
			'action' : 'GETPOPUPDIVCONTENT',
			'Userid' : userId
		} );
		return true;
	}
	return false;
}

function CancelDailyNoteTextEdit( Userid, date, Text, identifier )
{
	var divToEdit = document.getElementById( identifier );
	var innerHTML = "<a href='#' title='Edit this text' onclick=\"EditDailyNoteText( '" + Userid + "', '" + date + "', '" + escapeQuotes(Text) + "', '" + identifier + "' ); return false;\">";
	innerHTML += "<span class='donotprint'><img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'></span>";
	innerHTML += "</a>" + Text;
	divToEdit.innerHTML = innerHTML;
}

function UpdateDailyNoteTextEdit( Userid, date, Text, identifier )
{
	var newText = document.getElementById( identifier + "Textarea" ).value;
	var response = doPostDataMap( './TrackerOp.php',
		{
			"action" : identifier,
			"Userid" : Userid,
			"Date" : date,
			"Text" : newText
		} );
	CancelDailyNoteTextEdit( Userid, date, response, identifier );
}

function EditDailyNoteText( Userid, date, Text, identifier)
{
	var divToEdit = document.getElementById( identifier );
	var innerHTML = "<table width=90%><tr><td><textarea style='width: 100%' wrap=on id='" + identifier + "Textarea'>";
	innerHTML += Text.replace(/<br \/>/g, '\n');
	Text = escapeQuotes(Text);
	innerHTML += "</textarea></td></tr><tr><td align='left'>";
	innerHTML += "<a href='#' onclick=\"UpdateDailyNoteTextEdit( '" + Userid + "', '" + date + "', '" + Text + "', '" + identifier + "' ); return false;\"><img border=0 src='../images/ok_16.gif'><small>accept</small></a>&nbsp;";
	innerHTML += "<a href='#' onclick=\"CancelDailyNoteTextEdit( '" + Userid + "', '" + date + "', '" + Text + "', '" + identifier + "' ); return false;\"><img border=0 src='../images/cancel_16.gif'><small>cancel</small></a>";
	innerHTML += "</td></tr></table>";
	divToEdit.innerHTML = innerHTML;
	document.getElementById(identifier + "Textarea").select();
	document.getElementById(identifier + "Textarea").focus();
}

window.onload=function(){
if ( NiftyCheck() )
{ 
	Rounded("div.WorkPaneTitleBar","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>"); 
	RoundedTop("div#ViewTable","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>", "small" );
	RoundedBottom("div.RoundFooter","<?php echo CSS_BODYCOLOR ?>","<?php echo CSS_BACKGROUND ?>");
}
<?php emitLoadingJS(); ?>
}
</script>
<?php emitLoadingHeader();
emitCalSetup("..", "RedrawCalendar", "trackerDate", "trackerUser");?>
<div id='TitleBar' class='WorkPaneTitleBar'>Time Tracking Main Page</div>
<div id='ErrorMsgArea'></div>
<?php	
	$users = AdminUser::listUsers(true);

	echo "<br/>";
		
	?><table>
<?php

?>	<tr>
<?php
if (hasRole(Roles::USER_MANAGER))
{
?><td>
<select id='trackerUser' onChange='onInputChange();'>
<?php

$options = "";
$founduser=false;
foreach ( $users as $user )
{
	$options .= '<option value="'.$user->UserID.'"';
	if (!$founduser)
	{
		if ($user->Username == $_SESSION['USER_NAME'])
		{
			$options .= " SELECTED";
			$founduser = true;
		}
	}
	$options .= '>'.$user->Username.'</option>'."\n";
}

if (!$founduser) $options = '<option value="-1" SELECTED>SELECT USER</option>'.$options;

echo $options;

?>
</select>
</td><?php
}
else
{
	?><input type="hidden" id="trackerUser" value="<?php echo $_SESSION['USERID']; ?>"/>
<?php
}
?>
<td>
	<?php emitCalControlAutoDate('trackerDate'); ?>
	</td>
</tr>
</table>

<div style="position:relative;">
<table><tr><td>
<table>
<tr><td><div id="natedate" class="TrackerText" align="center"></div></td></tr>
<tr><td>
 <div id="PrettySceduleDiv" onmousedown="return false;">
 		
 		<table style="border-collapse: collapse">
 			<tr>
 					<th>Hour</th>
 					<th style='width:60px'>:00</th>
 					<th style='width:60px'>:15</th>
 					<th style='width:60px'>:30</th>
 					<th style='width:60px'>:45</th>
 			</tr>
 	<?php
 	
 		for ( $i = 0; $i < 24; $i ++ )
 		{
 			$hour = $i % 12;
 			$ampm=($i < 12 ? "am" : "pm" );
 			echo "<tr><td class='TrackerLine'>".str_pad("".($hour==0?12:$hour),2,"0",STR_PAD_LEFT).$ampm."</td>";
 			$period = $i*4;
 			$end = $period+4;
 			while ($period < $end)
 			{
	 			echo "<td class='TrackerLine'><div id='Period$period' oncontextmenu='editPeriod(\"$period\", event); return false;' onclick='selectRange($period, event); return eat(event);' ondblclick='setSameAsPrev($period);' class='Slotempty' >-</div></td>";
	 			$period++;
 			}
 			echo "<td class='TrackerLine'>&nbsp;</td>";
 			echo "</tr>\n";
 		}
 		
 	?></table>

 	</div>
 </td></tr>
 <tr><td>&nbsp;</td></tr>
 <tr><td><table width=100%>
 <tr><th class='TrackerLine'><div class='Slotempty' style='background-color:<?php echo CSS_BACKGROUND ?>; color: white;'>NOTES</div></th></tr>
 <tr><td class='TrackerLine'>
<div id='DAILY_NOTE_EDIT' class='Slotempty' style='background-color:#DCDCDC;'><span style='white-space: nowrap;'>
<a href='#' title='Edit this text' onclick="EditDailyNoteText( document.getElementById('trackerUser').value, getGlobalDateForSQL(), 'hi', 'DAILY_NOTE_EDIT' ); return false;"><span class='donotprint'><img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'></span></a>
</span></div>
 </td></tr>
 </table></td></tr>
 </table>
 </td><td><div id="runningprojtotals" style="position: absolute; top: 0;">
 <table style="border-collapse: collapse; width:180px;" id="dailysummarytable">
 	<tr><th colspan=2 class='TrackerLine'><div class='Slotempty' style='background-color:<?php echo CSS_BACKGROUND ?>; color: white;'>Daily Summary</div></th></tr>
 </table>
 		</div><div id='PeriodSummaryDiv' style='position: absolute; bottom: 0px; width: 180px;'></div></td></tr>
 </table></div>
 
<script>

var lastClicked = -1;
var selectedPeriods = new Array();

function eat(event)
{
	event.cancelBubble = true;
	if (event.stopPropagation) event.stopPropagation();
	return true;	
}

function selectRange( period, event )
{
	hidePopup();
	if ((lastClicked != -1) && (event.shiftKey == true))
	{
		selectedPeriods = new Array();
		deSelectAll();
		var start = lastClicked;
		var end = period;
		if (lastClicked > period)
		{
			start = period;
			end = lastClicked;
		}
		for (start; start <= end; start++)
		{
			selectedPeriods.push(start);
			selectSlot(start);
		}
	}	
	else if (event.ctrlKey == true)
	{
		var found = false;
		var idx = 0;
		for (var i in selectedPeriods)
		{
			if (period == selectedPeriods[i])
			{
				found = true;
				break;
			}
			idx++;
		}
		if (found)
		{
			selectedPeriods.splice(idx, 1);
			deSelectSlot(period);
		}
		else
		{
			selectedPeriods.push(period);
			selectSlot(period);
		}
		lastClicked = period;
	}
	else
	{
		selectedPeriods = new Array();
		deSelectAll();
		selectedPeriods.push(period);
		selectSlot(period);
		lastClicked = period;
	}
}

function editPeriod( period, event )
{
	if (authedPeriod)
	{
		alert("You cannot edit this time. The pay period has already been submitted.");
		return;
	}
	if (selectedPeriods.length == 0)
	{
		deSelectAll();
		selectedPeriods = new Array();
		selectedPeriods.push(period);
		selectSlot(period);
		lastClicked = period;
	}
	else
	{
		var found = false;
		for (var i in selectedPeriods)
		{
			if (period == selectedPeriods[i])
			{
				found = true;
				break;
			}
		}
		if (!found)
		{
			deSelectAll();
			selectedPeriods = new Array();
			selectedPeriods.push(period);
			selectSlot(period);
			lastClicked = period;
		}
	}
	showPopup(200,200,event);
}

var hidePopupHandle;

function hidePopupDelayed()
{
	hidePopupHandle = setTimeout('hidePopup()',1250);
}

function cancelHideEvent()
{
	clearTimeout(hidePopupHandle);
}

function hidePopup()
{   
	var popUp = document.getElementById("popupcontent");   
	popUp.style.visibility = "hidden";
} 	
 	
function showPopup(w,h, event)
{   
	cancelHideEvent();
	if ( selectedPeriods.length == 0 ) return;
	
	var popUp = document.getElementById("popupcontent");    
	popUp.style.left = (mouseX(event)-10)+"px";   
	popUp.style.top = (mouseY(event)-10)+"px";   
	popUp.style.width = w + "px";   
	popUp.style.height = h + "px";
	popUp.scrollTop = 0;
	popUp.style.visibility = "visible";
}

function selectProject( projId )
{
	if ((projId == -1) && authedPeriod)
	{
		alert("Can't delete time that has been submitted!");
	}
	else
	{
		setPeriod( selectedPeriods, projId, document.getElementById("trackerUser").value, document.getElementById("trackerDate").value );
		selectedPeriods = new Array();
		deSelectAll();
	}
	hidePopup();
}

function setSameAsPrev( period )
{
	lastClicked = period;
	if (authedPeriod)
	{
		alert("You cannot edit this time. The pay period has already been submitted.");
		return;
	}
	var projId = slotProj[period];
	if (projId != null) return;
	for (var i = period-1; i >= 0; i--)
	{
		if (slotProj[i] != null)
		{
			projId = slotProj[i];
			break;
		}
	}
	if (projId == null)
	{
		for (i = period+1; i < 96; i++)
		{
			if (slotProj[i] != null)
			{
				projId = slotProj[i];
				break;
			}
		}
	}
	if (projId == null)
	{
		alert("I can't find any recent time to clone!");
		return;
	}
	if (projId != null)
	{
		selectedPeriods = new Array();
		selectedPeriods.push(period);
		setPeriod( selectedPeriods, projId, document.getElementById("trackerUser").value, document.getElementById("trackerDate").value );
		selectedPeriods = new Array();
		deSelectAll();
	}
	else
		alert("error");
}

</script>
<div class="popupcontentStyle" id="popupcontent" onmouseout="fixOnMouseOut(this, event, 'hidePopupDelayed()');" onmouseover="cancelHideEvent();">Projects should be listed here!</div>
 <script>
 RedrawCalendar(getGlobalDateForSQL(), document.getElementById('trackerUser').value);
 </script>
<?php emitLoadingFooter(); ?>
