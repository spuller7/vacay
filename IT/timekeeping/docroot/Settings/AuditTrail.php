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

checkRole( Roles::ADMINISTRATOR );

//import_request_variables('g', 'at_');
extract($_GET, EXTR_PREFIX_ALL, 'at');

$user = AdminUser::retrieve($at_UserID);

emitStdProlog("..");

echo '<script>';

?>

function RedrawTrail(date)
{
	if (isDateAlert(date))
	{
		for(var i=0; i<=96; i++)
		{
			document.getElementById('audit'+i).innerHTML = '';
		}
		var dateObj = new Date(getDateFromFormat(date, "M/d/yyyy"));
		var sqlDate = formatDate(dateObj,"yyyy-MM-dd");
		var result = doPostDataMap( 'UserOp.php', {
			'action' : 'GET_AUDIT_TRAIL',
			'Userid' : <?php echo $user->UserID; ?>,
			'Date' : date
			} );
		if (result != "")
		{
			rows = result.split("\n");
			for(var i in rows)
			{
				if (rows[i] != "")
				{
					values = rows[i].split(",");
					if (values.length == 5)
					{
						var innerHTML = document.getElementById('audit'+values[4]).innerHTML;
						innerHTML += "<td><table style='border:solid 1px #000000;'>";
						innerHTML += "<tr class='AuditTR'><td class='AuditTableHeading'><b>Changed by: "+values[0]+"</b></td></tr>";
						innerHTML += "<tr class='AuditTR'><td class='AuditTableHeading'><b>At: "+values[1]+"</b></td></tr>";
						innerHTML += "<tr class='AuditTR'><td class='AuditTableCell'>Project: "+values[2]+"</td></tr>";
						innerHTML += "<tr class='AuditTR'><td class='AuditTableCell'>State: "+values[3]+"</td></tr>";
						innerHTML += "</table></td>";
						document.getElementById('audit'+values[4]).innerHTML = innerHTML;
					}
					else if (values.length == 1)
					{
						var innerHTML = document.getElementById('audit'+values[0]).innerHTML;
						innerHTML += "<td><table style='border:solid 1px #000000; height: 100%;'>";
						innerHTML += "<tr class='AuditTR'><td class='AuditTableHeadingDeleted'><b>Currently</b></tr>";
						innerHTML += "<tr class='AuditTR'><td class='AuditTableHeadingDeleted'><b>deleted</b></td></tr>";
						innerHTML += "<tr class='AuditTR'><td class='AuditTableCell'><br/></td></tr>";
						innerHTML += "<tr class='AuditTR'><td class='AuditTableCell'><br/></td></tr>";
						innerHTML += "</table></td>";
						document.getElementById('audit'+values[0]).innerHTML = innerHTML;
					}
				}
			}
		}
		for(var i=0; i<=96; i++)
		{
			var innerHTML = document.getElementById('audit'+i).innerHTML;
			innerHTML = "<table><tr><td>"+innerHTML+"</tr></table>";
			document.getElementById('audit'+i).innerHTML = innerHTML;
		}
		//add the current state
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
<?php emitLoadingHeader();
emitCalSetup("..", "RedrawTrail", "auditDate");?>
<div id='TitleBar' class='WorkPaneTitleBar'>Audit Trail for <?php echo $user->FullName; ?></div>
<div id='ErrorMsgArea'></div>

<br/><table>
<tr>
<td>
	<?php emitCalControlAutoDate('auditDate'); ?>
	</td>
</tr>
</table>

<table cellpadding=0 cellspacing=0><tr>
	<th>TIME</th><th>Audit Trail</th></tr>
<?php 

for($i=0; $i<=96; $i++)
{
	$hours = floor(($i*15) / 60);
	$minutes = (((($i*15) / 60) - (floor(($i*15) / 60))) * 60);
	if ($hours == 12) $ampm = "PM";
	elseif ($hours == 24)
	{
		$ampm = "AM";
		$hours = 12;
	}
	elseif ($hours > 12)
	{
		$ampm = "PM";
		$hours -= 12;
	}
	else $ampm = "AM";
	if ($hours == 0) $hours = 12;
	$time = sprintf("%d:%02d%s", $hours, $minutes, $ampm);
	echo "<tr><td>$time</td><td><div id='audit".$i."'>-</div></td></tr>";
}

?>
	
 </table>

 <div id="AuditTrailDiv"></div>
<script>
	 RedrawTrail(getDateForSQL(document.getElementById('auditDate').value));
</script>
<?php emitLoadingFooter(); ?>
