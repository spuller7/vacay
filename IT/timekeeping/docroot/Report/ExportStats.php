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
require_once( "../Tracker/TrackerManager.php" );
require_once( "../Project/ProjectManager.php" );
require_once( "../Settings/UserManager.php" );

checkRole( Roles::REPORTER );

//import_request_variables('gp', 'um_');
extract($_GET, EXTR_PREFIX_ALL, 'um');
extract($_POST, EXTR_PREFIX_ALL, 'um');

$time = strtotime($um_date);

header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public"); 
header('Content-type: text/plain');
header('Content-disposition: attachment; filename=StatsExport_'.date("Ymd", $time).'.csv');

$users = AdminUser::listUsers(true);
$projs = ProjectCatCollapsed::buildBaseProjectList();

if ($um_range == 1)
{
	$startdate = date('Y-m-01',$time);
	$enddate = date('Y-m-t',$time);
}
else if ($um_range == 2)
{
	$biweeklyrange = get_biweekly_range($um_date);
	$startdate = $biweeklyrange[0];
	$enddate = $biweeklyrange[1];
}
else
{
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

$headerString="";
$headerString.="name,";
foreach($projs as $ProjectCode => $projObj)
{
	$headerString .= $ProjectCode.",";
}
echo substr($headerString, 0, -1);
echo "\r\n";
foreach($users as $user)
{
	echo $user->Username.",";
	$projcounts = ProjectCatCollapsed::collapseProjectHours(
		TrackerUnit::retrieveFullProjectHoursForPeriodById($user->UserID, $startdate, $enddate), 
		$projs, true);
	$output = "";
	foreach($projs as $ProjectCode => $projObj)
	{
		if (isset($projcounts[$projObj->ProjectId]))
			$output .= ($projcounts[$projObj->ProjectId] / 4.0).",";
		else $output .= "0,";
	}
	$output = substr($output, 0, -1);
	echo "$output\r\n";
}
?>
