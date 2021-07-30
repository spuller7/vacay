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
require_once( "../Tracker/TrackerManager.php" );

checkRole( Roles::REPORTER );

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

emitStdProlog("..");

?>
<script>

var catColors = new Array(
'aqua', 'lime','gold','greenyellow',
'hotpink','khaki',
'lightblue','lightgreen','lightpink','lightsalmon',
'lightsteelblue','navajowhite','orange','palegreen','peru' );

var catIndex = 0;
var rowColors = new Array();

var colors={'':'#DCDCDC'};

function getColor( value )
{
	if ( !colors[value] )
	{
		colors[value] = catColors[catIndex];
		catIndex++;
	}
	return colors[value];
}

function highlightRow(rowid)
{
	var colcount = catIndex;
	for (var i = 0; i < colcount; i++)
	{
		var cellid = i + (colcount*rowid);
		rowColors[i] = document.getElementById('cell'+cellid).style.backgroundColor;
		document.getElementById('cell'+cellid).style.backgroundColor = 'gray';
	}
}

function unhighlightRow(rowid)
{
	var colcount = catIndex;
	for (var i = 0; i < colcount; i++)
	{
		var cellid = i + (colcount*rowid);
		document.getElementById('cell'+cellid).style.backgroundColor = rowColors[i];
	}
}

function RedrawReport(date, range)
{
	if (isDateAlert(date))
	{
		var dateObj = new Date(getDateFromFormat(document.getElementById("reportDate").value, "M/d/yyyy"));
		var sqlDate = formatDate(dateObj,"yyyy-MM-dd");
		var result = doPostDataMap( 'StatsOp.php', {
			'action' : 'DRAW_REPORT2',
			'date' : sqlDate,
			'range' : range
			} );
		//clear column totals
		var colcount = catIndex;
		for (var i = 0; i < colcount; i++)
		{
			document.getElementById('col'+i).innerHTML = "0.00";
		}
		var rows = result.split("\n");
		var hoursInPeriod = rows[0];
		rows.splice(0,1);
		var cellcount = 0;
		var rowcount = 0;
		var allemployees = 0;
		for ( var i in rows)
		{
			if ( rows[i].length > 0 )
			{
				values = rows[i].split(",");
				var finalized = values[values.length-1];
				values.splice(values.length-1, 1);
				var rowTotal = 0;
				var currCol = 0;
				for (var j in values)
				{
					var hours = values[j] / 4.0;
					rowTotal += hours;
					if (hours != 0)
					{
						var hoursDelta = hoursInPeriod - hours;
						var hoursDeltaSign = "+";
						if (hoursDelta > 0) hoursDeltaSign = "-";
						document.getElementById('cell'+cellcount).innerHTML = parseFloat(hours).toFixed(2) + " (" + hoursDeltaSign + parseFloat(Math.abs(hoursDelta)).toFixed(2) + ")";
					}
					else
						document.getElementById('cell'+cellcount).innerHTML = '&nbsp;';
					if (hours > hoursInPeriod)
						document.getElementById('cell'+cellcount).style.background = 'orange';
					else
						document.getElementById('cell'+cellcount).style.background = 'silver';
					var currColTotal = parseFloat(document.getElementById('col'+currCol).innerHTML);
					currColTotal += hours;
					document.getElementById('col'+currCol).innerHTML = parseFloat(currColTotal).toFixed(2);
					cellcount++;
					currCol++;
				}
				document.getElementById('rowTotal'+rowcount).innerHTML = parseFloat(rowTotal).toFixed(2);
				if (finalized == 1)
					document.getElementById('userRow'+rowcount).style.background = 'red';
				else
					document.getElementById('userRow'+rowcount).style.background = 'gray';
				allemployees += rowTotal;
				rowcount++;
			}
		}
		document.getElementById('TotalTotal').innerHTML = parseFloat(allemployees).toFixed(2);
	}
}

function moveFloatingDiv()
{
	document.getElementById('floatkey').style.left = parent.frames['work'].document.documentElement.scrollLeft + "px";
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
</head>
<body onscroll='moveFloatingDiv();'>
	<div id='Loading' class='LoadingTag'>Loading...</div>
	<div id='Pane' class='TopPane'>
<?php emitCalSetup("..", "RedrawReport", "reportDate");?>
<div id='TitleBar' class='WorkPaneTitleBar'>Report Generator</div>
<div id='ErrorMsgArea'></div>
<table>
<?php

?>	<tr>
<td>
	<?php emitCalControlAutoDate('reportDate'); ?>
	<label class="TrackerText"><input type="radio" name="range" id="rangePeriod" value=0 checked onclick='RedrawReport(getDateForSQL(document.getElementById("reportDate").value),this.value);' /> Period</label> <label class="TrackerText"><input type="radio" name="range" id="rangeMonth" value=1 onclick='RedrawReport(getDateForSQL(document.getElementById("reportDate").value),this.value);' /> Month</label>
	&nbsp;<a href='./StatsView.php' class='TrackerText'>Expand categories</a>  
	</td>
</tr>
</table>
<?php
 $users = AdminUser::listUsers(true);
$projBlob = ProjectCat::getSortedProjectList();
$cats = array();
foreach($projBlob as $obj)
{
	if (get_class($obj) == "ProjectCat")
		$cats[$obj->CatName] = $obj;
}

echo "<table style='position: absolute; left: 0px;' cellspacing=0 cellpadding=0><tr><td><div id='floatkey' style='position: relative; background-color: ".CSS_BODYCOLOR."; border: 1px solid;'>";
echo "<table id='keyTable'><tr class='TrackerLine'><th>&nbsp;</th></tr>";
$row=0;
foreach($users as $user)
{
	echo "<tr class='TrackerLine'>";
	echo "<td id='userRow".$row."' style='background:gray;'>".$user->Username."</td>";
	echo "</tr>";
	$row++;
}
echo "<tr class='TrackerLine'><td>&nbsp;</td></tr>";
echo "</table>";
echo "</div></td><td>";
echo "<table id='dataTable'><tr class='TrackerLine' id='categoryRow'>";
foreach($cats as $cat => $catObj)
{
	echo "<th><div id='cat".$catObj->CatID."'>".$cat."</div><script>document.getElementById('cat".$catObj->CatID."').style.backgroundColor = getColor(".$catObj->CatID.");</script></th>";
}
echo "<th></th></tr>";
$cell=0;
for ($row=0; $row<count($users); $row++)
{
	echo "<tr class='TrackerLine' onmouseover='highlightRow(".$row.");' onmouseout='unhighlightRow(".$row.");'>";
	foreach($cats as $cat => $catObj)
	{
		echo "<td align='right'><div id='cell".$cell."'>&nbsp;</div></td>";
		$cell++;
	}
	echo "<td align='right'><div id='rowTotal".$row."'></div></td>";
	echo "</tr>";
}
echo "<tr class='TrackerLine'>";
for ($i=0; $i<count($cats); $i++)
{
	echo "<td align='right'><div id='col".$i."'>0.00</div></td>";
}
echo "<td align='right' style='border:1px solid;'><b><div id='TotalTotal'>0.00</div></b></td>";
echo "</tr></table></td></tr></table>";
?>
<script>
RedrawReport(getDateForSQL(document.getElementById("reportDate").value),0);
</script>
<?php emitLoadingFooter(); ?>
