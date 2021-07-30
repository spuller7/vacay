<?php
require_once( dirname( __FILE__ )."/./authenticate.php" );

header('Content-Type: application/json; charset=UTF-8');

$isAuth = isset($_SESSION['USER_NAME']);
if ( !$isAuth )
{
	$r = new stdClass();
	$r->error = -1;
	$r->error_message = "Not authenticated";
	echo json_encode($r);
	return;
}

$db = new DBManager( $DBSERVER, $DBNAME, $DBUSER, $DBPASSWD );
$pdo = $db->pdo();

$json = file_get_contents('php://input');
$payload = json_decode($json);

$r = new stdClass();
$r->error = 0;
$r->error_message = "";

if ( $payload->Command == 'UpdateEmployee' )
{
	$sql = "UPDATE ORG_REL SET EMPLOYEE_ID=:employee_id WHERE ORG_ID=:org_id";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array("employee_id"=>$payload->employee_id,"org_id"=>$payload->org_id ));
	$r->result = $sth;
	$r->payload = $payload;
	checkDBError($sth);
} 
else
if ( $payload->Command == 'UpdateTitle' )
{
	$sql = "UPDATE ORG_REL SET TITLE=:title WHERE ORG_ID=:org_id";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array("title"=>$payload->title,"org_id"=>$payload->org_id ));
	$r->result = $sth;
	$r->payload = $payload;
	checkDBError($sth);
} 
else
if ( $payload->Command == 'UpdateSectionName' )
{
	$sql = "UPDATE ORG_REL SET SECTION_NAME=:sectionName WHERE ORG_ID=:org_id";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array("sectionName"=>$payload->sectionName,"org_id"=>$payload->org_id ));
	$r->result = $sth;
	$r->payload = $payload;
	checkDBError($sth);
} 
else	
if ( $payload->Command == 'UpdateProjectParent' )
{
	$sql = "REPLACE INTO ORG_PROJ_REL (PROJECT_ID, ORG_ID) VALUES (:proj_id, :newOrg)";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array("newOrg"=>$payload->newOrg,"proj_id"=>$payload->proj_id ));
	$r->result = $sth;
	$r->payload = $payload;
	checkDBError($sth);
} 
else
if ( $payload->Command == 'DeleteNode' )
{
	$sql = "DELETE FROM ORG_REL WHERE ORG_ID=:org_id";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array("org_id"=>$payload->org_id ));
	$r->result = $sth;
	$r->payload = $payload;
	checkDBError($sth);
} 
else
if ( $payload->Command == 'AddNode' )
{
	$sql = "INSERT INTO ORG_REL (EMPLOYEE_ID, TITLE, IS_PRIMARY_REPORTING, REPORTS_TO) VALUES (0,:title, 1, :org_id)";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array("title"=>$payload->title,"org_id"=>$payload->org_id ));
	$r->result = $sth;
	$r->payload = $payload;
	checkDBError($sth);
} 
else
if ( $payload->Command == 'UpdateReportsTo' )
{
	$sql = "UPDATE ORG_REL SET REPORTS_TO=:new_parent_id WHERE ORG_ID=:org_id";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute(array("new_parent_id"=>$payload->new_parent_id,"org_id"=>$payload->org_id ));
	$r->result = $sth;
	$r->payload = $payload;
	checkDBError($sth);
}
else
if ( $payload->Command == 'AssignOrphansProjects' )
{
	$sql = "INSERT INTO ORG_PROJ_REL (SELECT PROJECT_ID,-1 FROM PROJECTS WHERE IS_ACTIVE = 1 AND PROJECT_ID<> 1 AND PROJECT_ID NOT IN (SELECT PROJECT_ID FROM ORG_PROJ_REL))";
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute();
	$r->result = $sth;
	$r->payload = $payload;
	checkDBError($sth);
}
else
{
	$r->error = -1;
	$r->error_message = "Unknown Command: ".$payload->Command;
}
echo json_encode($r);
return;
?>
