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

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

emitStdProlog("..");

?>
<script>

function jumpBackToTracker(date)
{
	parent.frames[1].document.getElementById( "ReportItem" ).className="MenuItem";
	parent.frames[1].document.getElementById( "TrackerItem2" ).className="MenuItemActive";
	parent.frames[1].selectedItem = "TrackerItem2";
	window.location='../Tracker/TrackerView2.php?date=' + date;
}

function printMe()
{
	//alert("Please print in landscape!");
	print();
}

function CancelTextEdit( Userid, date, Text, identifier )
{
	var divToEdit = document.getElementById( date + identifier );
	var innerHTML = "<a href='#' title='Edit this text' onclick=\"EditText( '" + Userid + "', '" + date + "', '" + escapeQuotes(Text) + "', '" + identifier + "' ); return false;\">";
	innerHTML += "<span class='donotprint'><img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'></span>";
	innerHTML += "</a>" + Text;
	divToEdit.innerHTML = innerHTML;
}

function UpdateTextEdit( Userid, date, Text, identifier )
{
	var newText = document.getElementById( date + identifier + "Textarea" ).value;
	var response = doPostDataMap( './ReportOp.php',
		{
			"action" : identifier,
			"Userid" : Userid,
			"Date" : date,
			"Text" : newText
		} );
	CancelTextEdit( Userid, date, response, identifier );
}

function EditText( Userid, date, Text, identifier)
{
	var divToEdit = document.getElementById( date + identifier );
	var innerHTML = "<table width=90%><tr><td><textarea style='width: 100%' wrap=on id='" + date + identifier + "Textarea'>";
	innerHTML += Text.replace(/<br \/>/g, '\n');
	Text = escapeQuotes(Text);
	innerHTML += "</textarea></td></tr><tr><td align='left'>";
	innerHTML += "<a href='#' onclick=\"UpdateTextEdit( '" + Userid + "', '" + date + "', '" + Text + "', '" + identifier + "' ); return false;\"><img border=0 src='../images/ok_16.gif'><small>accept</small></a>&nbsp;";
	innerHTML += "<a href='#' onclick=\"CancelTextEdit( '" + Userid + "', '" + date + "', '" + Text + "', '" + identifier + "' ); return false;\"><img border=0 src='../images/cancel_16.gif'><small>cancel</small></a>";
	innerHTML += "</td></tr></table>";
	divToEdit.innerHTML = innerHTML;
	document.getElementById(date + identifier + "Textarea").select();
	document.getElementById(date + identifier + "Textarea").focus();
}

<?php if (hasRole(Roles::REPORTER)) { ?>
function CancelCompTextEdit( Userid, date, Text, identifier )
{
	var divToEdit = document.getElementById( identifier );
	var innerHTML = "<a href='#' title='Edit this text' onclick=\"EditCompText( '" + Userid + "', '" + date + "', '" + Text + "', '" + identifier + "' ); return false;\">";
	innerHTML += "<span class='donotprint'><img src='../images/pencil_16.gif' border=0 style='vertical-align:bottom'></span>";
	var numeric = parseFloat(Text);
	innerHTML += "</a>" + numeric.toFixed(2);
	divToEdit.innerHTML = innerHTML;
}

function UpdateCompTextEdit( Userid, date, Text, identifier )
{
	var newText = document.getElementById( identifier + "Textarea" ).value;
	var response = doPostDataMap( './ReportOp.php',
		{
			"action" : identifier,
			"Userid" : Userid,
			"Date" : date,
			"Text" : newText
		} );
	CancelCompTextEdit( Userid, date, response, identifier );
}

function EditCompText( Userid, date, Text, identifier)
{
	var divToEdit = document.getElementById( identifier );
	var innerHTML = "<table width=90%><tr><td><textarea style='width: 100%' wrap=on id='" + identifier + "Textarea'>";
	innerHTML += Text.replace(/<br \/>/g, '\n');
	innerHTML += "</textarea></td></tr><tr><td align='left'>";
	innerHTML += "<a href='#' onclick=\"UpdateCompTextEdit( '" + Userid + "', '" + date + "', '" + Text + "', '" + identifier + "' ); return false;\"><img border=0 src='../images/ok_16.gif'><small>accept</small></a>&nbsp;";
	innerHTML += "<a href='#' onclick=\"CancelCompTextEdit( '" + Userid + "', '" + date + "', '" + Text + "', '" + identifier + "' ); return false;\"><img border=0 src='../images/cancel_16.gif'><small>cancel</small></a>";
	innerHTML += "</td></tr></table>";
	divToEdit.innerHTML = innerHTML;
	document.getElementById(identifier + "Textarea").select();
	document.getElementById(identifier + "Textarea").focus();
}
<?php } ?>
function SubmitTime(userid, startdate, enddate)
{
	document.getElementById('RetractTimeButton').disabled = true;
	document.getElementById('SubmitTimeButton').disabled = true;
	var response = doPostDataMap( 'ReportOp.php', {
		'action' : 'SUBMIT',
		'Userid' : userid,
		'StartDate' : startdate,
		'EndDate' : enddate
		} );
	if (response != "OK") alert("Error authorizing day!");
	//else document.getElementById('RetractTimeButton').disabled = false;
	RedrawReport(startdate, userid);
}

function RetractTime(userid, startdate, enddate)
{
	document.getElementById('SubmitTimeButton').disabled = true;
	document.getElementById('RetractTimeButton').disabled = true;
	var response = doPostDataMap( 'ReportOp.php', {
		'action' : 'RETRACT',
		'Userid' : userid,
		'StartDate' : startdate,
		'EndDate' : enddate
		} );
	if (response != "OK") alert("Error retracting day!");
	//else document.getElementById('SubmitTimeButton').disabled = false;
	RedrawReport(startdate, userid);
}

function RedrawReport(date, userid)
{
	if ((userid != -1) && (isDateAlert(date)))
	{
		var rangeId = document.getElementsByName("range");
		var selectedRange = 0;

		for(var i = 0; i < rangeId.length; i++)
		{
			if(rangeId[i].checked)
				selectedRange = rangeId[i].value;
		}

		var action = "DRAW_REPORT_BI";
		switch (parseInt(selectedRange)) {
			case 0:
				action = "DRAW_REPORT";
				break;
			default:
				action = "DRAW_REPORT_BI";
		}

		document.getElementById('PrettyReportDiv').innerHTML = doPostDataMap( 'ReportOp.php', {
			'action' : action,
			'Userid' : userid,
			'date' : date
			} );
	}
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
<?php emitLoadingHeader(); ?>
<div id="dontprint">
<?php emitCalSetup("..", "RedrawReport", "reportDate", "reportUser");?>
<div id='TitleBar' class='WorkPaneTitleBar'>Report Generator</div>
<div id='ErrorMsgArea'></div>
<?php	
	$users = AdminUser::listUsers(true);
	$codes = Project::listCodes();

	echo "<br/>";
		
	?><table>
<?php

?>	<tr>
<?php
if (hasRole(Roles::USER_MANAGER))
{
?><td>
<select id='reportUser' onChange='onInputChange();'>
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
?><input type="hidden" id="reportUser" value="<?php echo $_SESSION['USERID'] ?>"/>
<?php
}
?>
<td>
	<?php emitCalControlAutoDate('reportDate'); ?>
	<label class="TrackerText"><input type="radio" name="range" id="rangePeriod" value=0 onclick='RedrawReport(getDateForSQL(document.getElementById("reportDate").value),document.getElementById("reportUser").value);' /> Period</label> 
	<label class="TrackerText"><input type="radio" name="range" id="rangeBimonthly" value=2 checked onclick='RedrawReport(getDateForSQL(document.getElementById("reportDate").value),document.getElementById("reportUser").value);' /> Biweekly</label>
	</td>
</tr>
</table>
</div>
 <div id="PrettyReportDiv"></div>
<script>
	 RedrawReport(getGlobalDateForSQL(), document.getElementById('reportUser').value);
</script>
<?php emitLoadingFooter(); ?>
