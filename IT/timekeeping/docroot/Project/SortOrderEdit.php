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
require_once( "../Project/ProjectManager.php" );
require_once( "../Settings/UserManager.php" );

checkRole( Roles::USER_MANAGER );

emitStdProlog("..");

echo '<script>';

if (hasRole( Roles::USER_MANAGER))
{
?>

function changeProjectSortOrder(projId, newPos, otherProjId, otherNewPos)
{
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'PROJECT_SORT_ORDER',
		'ProjectID' : projId,
		'Pos' : newPos,
		'ProjectID2' : otherProjId,
		'Pos2' : otherNewPos 
		} );
	if (response != "OK") alert("Error changing project sort order!");
	window.location.reload();
}

function changeCatSortOrder(catId, newPos, otherCatId, otherNewPos)
{
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'CAT_SORT_ORDER',
		'CatID' : catId,
		'Pos' : newPos,
		'CatID2' : otherCatId,
		'Pos2' : otherNewPos
		} );
	if (response != "OK") alert("Error changing category sort order!");
	window.location.reload();
}

function cleanUpSortOrder()
{
	var response = doPostDataMap( 'ProjectOp.php', {
		'action' : 'CLEAN_UP_SORT_ORDER'
		} );
	if (response != "OK") alert("Error cleaning sort order!");
	window.location.reload();
}
<?php
}
?>
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
<div id='TitleBar' class='WorkPaneTitleBar'>Project Control Center</div>
<div id='ErrorMsgArea'></div>
<?php
	//ProjectCat::cleanUpSortOrder();
	$projBlob = ProjectCat::getSortedProjectList();
	if ( hasRole( Roles::USER_MANAGER ) )
	{
		emitMenuStart();
		emitMenuItem( '&lt;&lt;&nbsp;Back', 'Back to project control main page', './ProjectView.php' );
		emitMenuItemScript( 'Clean', 'Clean up sort order', 'cleanUpSortOrder()' );
		emitMenuEnd();
	}
?>
	<br/>
	<table class='WorkPaneListTable' cellpadding=0 cellspacing=0>
<tr class='WorkPaneListAlt1'><td class='WorkPaneTD'>
<table>
<?php
$cats = array();
$projs = array();
foreach($projBlob as $obj)
{
	if (get_class($obj) == "ProjectCat")
		$cats[] = $obj;
	else if (get_class($obj) == "Project")
		$projs[] = $obj;
}
$maxCat = count($cats)-1;
$catidx=0;
$catcount=0;
$projidx=0;
$firstcat = true;
foreach($projBlob as $obj)
{
	if (get_class($obj) == "ProjectCat")
	{
		echo "<tr style='background:gray;'><td><b>".$obj->CatName."</b></td><td></td><td>".$obj->SortOrder."</td><td>";
		if ($catcount != $maxCat)
			echo "<a href='#' onclick='changeCatSortOrder(".$obj->CatID.", ".($obj->SortOrder+1).", ".$cats[$catidx+1]->CatID.", ".$obj->SortOrder.");'><img border=0 src='../images/arrow_dn_16.gif'></a>\n";
		echo "</td><td>\n";
		if (!$firstcat)
			echo "<a href='#' onclick='changeCatSortOrder(".$obj->CatID.", ".($obj->SortOrder-1).", ".$cats[$catidx-1]->CatID.", ".$obj->SortOrder.");'><img border=0 src='../images/arrow_up_16.gif'></a>\n";
		echo "</td></tr>\n";
		$firstcat = false;
		$catcount++;
		$catidx++;
	}
	else if (get_class($obj) == "Project")
	{
		echo "<tr><td></td><td>".$obj->ProjectCode."</td><td></td><td></td><td></td></tr>\n";
	}
}
?>
</table>
</td>
</tr>
</table>
<?php emitLoadingFooter(); ?>
