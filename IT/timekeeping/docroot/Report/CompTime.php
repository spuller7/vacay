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

function fillEstimates()
{
	theForm = document.getElementById('CompTime');
	for (i=0; i < theForm.elements.length; i++)
	{
		ele = theForm.elements[i];
		if (ele.type == 'hidden')
		{
			if ((ele.name.substr(0, 4) == 'comp') && (ele.name.substr(ele.name.length-6) == 'hidden'))
			{
				userid = ele.name.substring(4, ele.name.length-6);
				modele = document.getElementById('comp' + userid);
				modele.value = ele.value;
				modele.style.backgroundColor = 'yellow';
			}
		}
	}
}

function SubmitCompTimes(date)
{
	document.getElementById('submit').disabled = true;
	var postData = new Object();
	postData["action"] = "SUBMIT";
	postData["date"] = date;
	for(i=0; i<document.CompTime.elements.length; i++)
	{
	   if (document.CompTime.elements[i].name.substr(0,4) == "comp")
	   {
		   postData[document.CompTime.elements[i].name] = document.CompTime.elements[i].value;
	   }
	}
	var response = doPostDataMap( 'CompTimeOp.php', postData);
	RedrawReport(getDateForSQL(reportDate.value));
	if (response == "OK")
	{
		alert("Comp time has been recorded.");
		window.location='./ReportFinalize.php';
	}
	else
	{
		alert("Error submitting comp time values!");
		document.getElementById('submit').disabled = false;
	}
}

function RedrawReport(date)
{
	if (isDateAlert(date))
	{
		document.getElementById('PrettyReportDiv').innerHTML = doPostDataMap( 'CompTimeOp.php', {
			'action' : 'DRAW_REPORT',
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
<?php emitCalSetup("..", "RedrawReport", "reportDate");?>
<div id='TitleBar' class='WorkPaneTitleBar'>Government Over Hours</div>
<div id='ErrorMsgArea'></div>
<?php 	emitMenuStart();
	emitMenuItem( '&lt;&lt;&nbsp;Back', 'Back to finalize main page', './ReportFinalize.php'.(isset($um_history)?'?history='.$um_history:'') );
	emitMenuEnd();
?><table>
<?php

?>	<tr>
<td>
	<?php emitCalControlAutoDate('reportDate'); ?>
	</td>
</tr>
</table>
</div>
 <div id="PrettyReportDiv"></div>
<script>
	 RedrawReport(getGlobalDateForSQL());
</script>
<?php emitLoadingFooter(); ?>
