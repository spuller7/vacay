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

checkRole( Roles::REPORTER );

//import_request_variables('gp', 'um_');
extract($_GET, EXTR_PREFIX_ALL, 'um');
extract($_POST, EXTR_PREFIX_ALL, 'um');

if(!(isset($um_action))) $um_action = 'finalize';

emitStdProlog("..");

?>
<script>

function finalizePeriod(userid, startdate, enddate)
{
	document.getElementById('RejectButton').disabled = true;
	document.getElementById('FinalizeButton').disabled = true;
	var response = doPostDataMap( 'FinalizeOp.php', {
		'action' : 'FINALIZE',
		'Userid' : userid,
		'StartDate' : startdate,
		'EndDate' : enddate
		} );
	if (response != "OK") alert("Error authorizing day!");
	<?php if (isset($um_history)) echo "window.location='./ReportFinalize.php?history=true';";
	else echo "window.location='./ReportFinalize.php';";
	?>
}

function rejectPeriod(userid, startdate, enddate)
{
	document.getElementById('RejectButton').disabled = true;
	document.getElementById('FinalizeButton').disabled = true;
	var response = doPostDataMap( 'FinalizeOp.php', {
		'action' : 'REJECT',
		'Userid' : userid,
		'StartDate' : startdate,
		'EndDate' : enddate
		} );
	if (response != "OK") alert("Error rejecting day!");
	<?php if (isset($um_history)) echo "window.location='./ReportFinalize.php?history=true';";
	else echo "window.location='./ReportFinalize.php';";
	?>
}

<?php
if (hasRole( Roles::ADMINISTRATOR ) )
{ ?>
function unlockPeriod(userid, startdate, enddate)
{
	document.getElementById('UnlockButton').disabled = true;
	var response = doPostDataMap( 'FinalizeOp.php', {
		'action' : 'UNLOCK',
		'Userid' : userid,
		'StartDate' : startdate,
		'EndDate' : enddate
		} );
	if (response != "OK") alert("Error unlocking day!");
	window.location='./ReportFinalize.php';
}
<?php } ?>

function RedrawReport(date, userid, action)
{
	if ((userid != -1) && (isDateAlert(date)))
	{
		document.getElementById('PrettyReportDiv').innerHTML = doPostDataMap( 'FinalizeOp.php', {
			'action' : 'DRAW_REPORT',
			'Userid' : userid,
			'date' : date,
			'mode' : '<?php echo $um_action; ?>'
			} );
	}
}

function RedrawOverview(date)
{
	if (isDateAlert(date))
	{
		document.getElementById('PrettyOverviewDiv').innerHTML = doPostDataMap( 'FinalizeOp.php', {
			'action' : 'DRAW_OVERVIEW',
			mode: '<?php echo $um_action; ?>',
			<?php if (isset($um_history) && ($um_action != 'unlock')) echo "'history' : true\n";
			else echo "'date' : date\n"; ?>
			} );
	}
}

function RedrawMaintenance()
{
	document.getElementById('PrettyMaintenanceDiv').innerHTML = doPostDataMap( 'FinalizeOp.php', {
		'action' : 'DRAW_MAINTENANCE'
		} );
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
<div id='TitleBar' class='WorkPaneTitleBar'>Finalize!</div>
<div id='ErrorMsgArea'></div>
<?php if ((isset($um_userid)) && (isset($um_date)))
{
emitCalSetup("..", "RedrawReport", "finalizeDate", "finalizeUser");
emitMenuStart();
emitMenuItem( '&lt;&lt;&nbsp;Back', 'Back to finalize overview', './ReportFinalize.php?action='.$um_action.(isset($um_history)?'&history='.$um_history:'') );
emitMenuEnd();
?>
<input type='hidden' id='finalizeUser' value='<?php echo $um_userid; ?>' />
<input type='hidden' id='finalizeDate' value='<?php echo $um_date; ?>' />
</div>

 <div id="PrettyReportDiv"></div>
  <script>
 RedrawReport(getDateForSQLFromFormat(document.getElementById('finalizeDate').value, "MM-dd-yyyy"), document.getElementById('finalizeUser').value);
 updateGlobalDate(document.getElementById('finalizeDate').value, "MM-dd-yyyy");
 </script>
 <?php
}
else if ((isset($um_mode)) && ($um_mode == 'maintenance'))
{
	emitMenuStart();
	emitMenuItem( '&lt;&lt;&nbsp;Back', 'Back to finalize main page', './ReportFinalize.php'.(isset($um_history)?'?history='.$um_history:'') );
	emitMenuEnd();
	?><br/><div id="PrettyMaintenanceDiv"></div>
	  <script>RedrawMaintenance();</script>
	<?php
}
else
{
	emitMenuStart();
	emitMenuItem( 'Govt Time', 'Update the government over hours data', './CompTime.php' );
	if ($um_action=='finalize')
	{
		if (hasRole(Roles::ADMINISTRATOR))
			emitMenuItem( 'Unlock', 'Unlock finalized time', './ReportFinalize.php?action=unlock'.(isset($um_history)?'&history='.$um_history:'') );
		if (!(isset($um_history)))
			emitMenuItem( 'Show All', 'Check for proplems remaining in previous pay periods', './ReportFinalize.php?action='.$um_action.'&history=true');
		else
			emitMenuItem( 'Current Period', 'See only the selected pay period', './ReportFinalize.php?action='.$um_action);
		emitMenuItem( 'Period Summary', 'View a list of periods with outstanding issues', './ReportFinalize.php?mode=maintenance'.(isset($um_history)?'&history='.$um_history:''));
	}
	else
		emitMenuItem( 'Finalize', 'Finalize time', './ReportFinalize.php'.(isset($um_history)?'?history='.$um_history:'') );
	emitMenuEnd();
	emitCalSetup("..", "RedrawOverview", "finalizeDate");
	
	if (($um_action == 'unlock') || !(isset($um_history)))
	{
		?><table><tr><td><?php
		if (!(isset($um_date)))
			emitCalControlAutoDate('finalizeDate');
		else
		{
			emitCalControl('finalizeDate',$um_date);
			echo "<script>window.onunload=function(){ parent.globalDate = document.getElementById('finalizeDate').value; }</script>";
		}
		?></td></tr></table><br/>
	<?php } else echo "<br/>"; ?>
	<div id="PrettyOverviewDiv"></div>
	  <script>
	  <?php
	if (!(isset($um_history)))
		echo "RedrawOverview(getDateForSQL(document.getElementById('finalizeDate').value));";
	else
	{
		echo "RedrawOverview(getGlobalDateForSQL());";
	}
	?>
	 </script>
	<?php
}
?>
<?php emitLoadingFooter(); ?>
