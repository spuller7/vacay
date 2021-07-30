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

checkRole( Roles::REPORTER );

//import_request_variables('p', 'um_');
extract($_POST, EXTR_PREFIX_ALL, 'um');

if ( !isset( $um_action ) )
{
	checkRole("!!BOGUS!!");
}

switch ( $um_action )
{
	case 'DRAW_REPORT':
		$users = AdminUser::listUsers(true);

		$projs = ProjectCatCollapsed::buildBaseProjectList();

		$time = strtotime($um_date);
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
		
		foreach($users as $user)
		{
			$projsandstate = ProjectCatCollapsed::collapseProjectHours(
				TrackerUnit::retrieveFullProjectHoursForPeriodWithState($user->UserID, $startdate, $enddate), 
				$projs);
			$output = "";
			$projcounts = array();
			if (count($projsandstate) > 0)
			{
				$finalized = true;
				foreach($projsandstate as $projandstate)
				{
					if (!isset($projcounts[$projandstate->ProjectID])) $projcounts[$projandstate->ProjectID] = 0;
					$projcounts[$projandstate->ProjectID] = $projcounts[$projandstate->ProjectID] + $projandstate->numBlocks;
					if ($projandstate->state != 'finalized') $finalized = false;
				}
			}
			else
				$finalized = false;
			foreach($projs as $ProjectCode => $projObj)
			{
				if (isset($projcounts[$projObj->ProjectId]))
					$output .= $projcounts[$projObj->ProjectId].",";
				else $output .= "0,";
			}
			$output .= ($finalized?1:0);
			echo "$output\n";
		}
		break;
	case 'DRAW_REPORT2':
		$users = AdminUser::listUsers(true);
		//have to do this to get the project codes in the right order:
		$projBlob = ProjectCat::getSortedProjectList();
		$proj2cat = array();
		$cats = array();
		foreach($projBlob as $obj)
		{
			if (get_class($obj) == "Project")
				$proj2cat[$obj->ProjectID] = $obj->CatID;
			if (get_class($obj) == "ProjectCat")
				$cats[] = $obj;
		}
		
		$time = strtotime($um_date);
		if ($um_range == 1)
		{
			$startdate = date('Y-m-01',$time);
			$enddate = date('Y-m-t',$time);
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
		
		$periodHours = 0;
		$checkdate = $startdate;
		$endloopdate = date ("Y-m-d", strtotime ("+1 day", strtotime($enddate)));
		while ($checkdate != $endloopdate)
		{
			if (date('N',strtotime($checkdate)) < 6)
				$periodHours += 8;
			$checkdate = date ("Y-m-d", strtotime ("+1 day", strtotime($checkdate)));
		}
		echo "$periodHours\n";
		
		foreach($users as $user)
		{
			$projsandstate = TrackerUnit::retrieveFullProjectHoursForPeriodWithState($user->UserID, $startdate, $enddate);
			$output = "";
			$projcatcounts = array();
			if (count($projsandstate) > 0)
			{
				$finalized = true;
				foreach($projsandstate as $projandstate)
				{
					if (!isset($projcatcounts[$proj2cat[$projandstate->ProjectID]])) $projcatcounts[$proj2cat[$projandstate->ProjectID]] = 0;
					$projcatcounts[$proj2cat[$projandstate->ProjectID]] = $projcatcounts[$proj2cat[$projandstate->ProjectID]] + $projandstate->numBlocks;
					if ($projandstate->state != 'finalized') $finalized = false;
				}
			}
			else
				$finalized = false;
			foreach($cats as $catObj)
			{
				if (isset($projcatcounts[$catObj->CatID]))
					$output .= $projcatcounts[$catObj->CatID].",";
				else $output .= "0,";
			}
			$output .= ($finalized?1:0);
			echo "$output\n";
		}
		break;
	default:
		//don't let them try and hunt out the commands...
		checkRole( "!!BOGUS!!" );
}

?>
