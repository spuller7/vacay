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

if (!(AdminUser::isAuthorizer($_SESSION['USERID'])))
{
	header( "HTTP/1.1 403 Forbidden" );
	echo "This account does not have sufficient privileges to perform this operation";
	die();
}

//import_request_variables('pg', 'um_');
extract($_GET, EXTR_PREFIX_ALL, 'um');
extract($_POST, EXTR_PREFIX_ALL, 'um');

emitStdProlog("..");

?>
<script>

function authorizePeriod(userid, startdate, enddate)
{
	document.getElementById('RejectButton').disabled = true;
	document.getElementById('AuthorizeButton').disabled = true;
	var response = doPostDataMap( 'AuthorizeOp.php', {
		'action' : 'AUTHORIZE',
		'Userid' : userid,
		'StartDate' : startdate,
		'EndDate' : enddate
		} );
	if (response != "OK") alert("Error authorizing day!");
	<?php if (isset($um_history)) echo "window.location='./ReportAuthorize.php?history=true';";
	else echo "window.location='./ReportAuthorize.php';";
	?>
}

function rejectPeriod(userid, startdate, enddate)
{
	document.getElementById('RejectButton').disabled = true;
	document.getElementById('AuthorizeButton').disabled = true;
	var response = doPostDataMap( 'AuthorizeOp.php', {
		'action' : 'REJECT',
		'Userid' : userid,
		'StartDate' : startdate,
		'EndDate' : enddate
		} );
	if (response != "OK") alert("Error rejecting day!");
	<?php if (isset($um_history)) echo "window.location='./ReportAuthorize.php?history=true';";
	else echo "window.location='./ReportAuthorize.php';";
	?>
}

function RedrawReport(date, userid)
{
	if ((userid != -1) && (isDateAlert(date)))
	{
		document.getElementById('PrettyReportDiv').innerHTML = doPostDataMap( 'AuthorizeOp.php', {
			'action' : 'DRAW_REPORT',
			'Userid' : userid,
			'date' : date
			} );
	}
}

function RedrawOverview(date)
{
	if (isDateAlert(date))
	{
		document.getElementById('PrettyOverviewDiv').innerHTML = doPostDataMap( 'AuthorizeOp.php', {
			'action' : 'DRAW_OVERVIEW',
			<?php if (isset($um_history)) echo "\n\t\t\t'history' : true\n";
			else echo "\n\t\t\t'date' : date\n"; ?>
			} );
	}
}

<?php if ((isset($um_mode)) && ($um_mode == 'maintenance')) { ?>
function RedrawMaintenance()
{
	document.getElementById('PrettyMaintenanceDiv').innerHTML = doPostDataMap( 'AuthorizeOp.php', {
		'action' : 'DRAW_MAINTENANCE'
		} );
}
<?php }
if ((isset($um_mode)) && ($um_mode == 'comp'))
{
?>
function RedrawComp(date)
{
	if (isDateAlert(date))
	{
		document.getElementById('PrettyCompDiv').innerHTML = doPostDataMap( 'AuthorizeOp.php', {
			'action' : 'DRAW_COMP',
			'date': date
			} );
	}
}
<?php } ?>

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
<div id='TitleBar' class='WorkPaneTitleBar'>Authorize!</div>
<div id='ErrorMsgArea'></div>
<?php if ((isset($um_userid)) && (isset($um_date)))
{
emitCalSetup("..", "RedrawReport", "authorizeDate", "authorizeUser");
emitMenuStart();
emitMenuItem( '&lt;&lt;&nbsp;Back', 'Back to authorization overview', './ReportAuthorize.php'.(isset($um_history)?'?history='.$um_history:'') );
emitMenuEnd();
?>
<input type='hidden' id='authorizeUser' value='<?php echo $um_userid; ?>' />
<input type='hidden' id='authorizeDate' value='<?php echo $um_date; ?>' />
</div>

 <div id="PrettyReportDiv"></div>
  <script>
 RedrawReport(getDateForSQLFromFormat(document.getElementById('authorizeDate').value, "MM-dd-yyyy"), document.getElementById('authorizeUser').value);
 updateGlobalDate(document.getElementById('authorizeDate').value, "MM-dd-yyyy");
 </script>
 <?php
}
else if ((isset($um_mode)) && ($um_mode == 'maintenance'))
{
	emitMenuStart();
	emitMenuItem( '&lt;&lt;&nbsp;Back', 'Back to authorize main page', './ReportAuthorize.php'.(isset($um_history)?'?history='.$um_history:'') );
	emitMenuEnd();
	?><br/><div id="PrettyMaintenanceDiv"></div>
	  <script>RedrawMaintenance();</script>
	<?php
}
else if ((isset($um_mode)) && ($um_mode == 'comp'))
{
	emitCalSetup("..", "RedrawComp", "authorizeDate");
	emitMenuStart();
	emitMenuItem( '&lt;&lt;&nbsp;Back', 'Back to authorize main page', './ReportAuthorize.php'.(isset($um_history)?'?history='.$um_history:'') );
	emitMenuEnd();
	?><table>
<?php

?>	<tr>
<td>
	<?php emitCalControlAutoDate('authorizeDate'); ?>
	</td>
</tr>
</table>
</div><br />
 <div id="PrettyCompDiv"></div>
<script>
	 RedrawComp(getGlobalDateForSQL());
</script>
	<?php
}
else
{
	emitMenuStart();
	if (!(isset($um_history)))
		emitMenuItem( 'Show All', 'Check for proplems remaining in previous pay periods', './ReportAuthorize.php?&history=true');
	else
		emitMenuItem( 'Current Period', 'See only the selected pay period', './ReportAuthorize.php');
	emitMenuItem( 'Problem Periods', 'View a list of periods with outstanding issues', './ReportAuthorize.php?mode=maintenance'.(isset($um_history)?'&history='.$um_history:''));
	emitMenuItem( 'Govt Time Summary', 'View a summary of your authorizees govt overhours', './ReportAuthorize.php?mode=comp'.(isset($um_history)?'&history='.$um_history:''));
	emitMenuEnd();
	emitCalSetup("..", "RedrawOverview", "authorizeDate");
	
	if (!(isset($um_history)))
	{
		?><table><tr><td><?php
		if (!(isset($um_date)))
			emitCalControlAutoDate('authorizeDate');
		else
		{
			emitCalControl('authorizeDate',$um_date);
			echo "<script>window.onunload=function(){ parent.globalDate = document.getElementById('authorizeDate').value; }</script>";
		}
		?></td></tr></table><br/>
	<?php } else echo "<br/>"; ?>
	<div id="PrettyOverviewDiv"></div>
	  <script>
	  <?php
	if (!(isset($um_date)))
		echo "RedrawOverview(getGlobalDateForSQL());";
	else
	{
		$sqlDate = date("Y-m-d",strtotime($um_date));
		echo "RedrawOverview('$sqlDate');";
	}
	?>
	 </script>
	<?php
}
?>
<?php emitLoadingFooter(); ?>
