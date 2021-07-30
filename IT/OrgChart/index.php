<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once( dirname( __FILE__ )."/./authenticate.php" );

$isAuth = isset($_SESSION['USER_NAME']);

/*

CREATE TABLE
    ORG_REL
    (
	ORG_ID INT PRIMARY KEY AUTO_INCREMENT,
        EMPLOYEE_ID INT,
	TITLE VARCHAR(128),
	SECTION_NAME VARCHAR(128),
	IS_PRIMARY_REPORTING bit DEFAULT 1,
	REPORTS_TO INT
    );
	
CREATE TABLE
   ORG_PROJ_REL
   (
        PROJECT_ID INT PRIMARY KEY,
        ORG_ID INT NOT NULL
   );	
	*/

$db = new DBManager( $DBSERVER, $DBNAME, $DBUSER, $DBPASSWD );

$pdo = $db->pdo();
$projectToLdap = array();

function GetUsers()
{
	global $pdo;
	global $projectToLdap;
	$invalidIds = -1000;
	
	$data = array();
	$lup = array();
	$u = LdapUser::getAllUsers();
	global $LdapUri;
	$conn = ldap_connect($LdapUri);
	ldap_bind($conn) or die("Could not connect to LDAP server");
	foreach ( $u as $uname )
	{
		$ldapInfo = new LdapUser($conn, $uname);
		$ldapInfo->fullName = $ldapInfo->getFullName();
		$data[$ldapInfo->uid] = $ldapInfo;
		$lup[$uname] = $ldapInfo->uid;
	}	
	
	$sql = "SELECT UserID, Username, Fullname FROM Users"; 
	
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute();

	while ( ($result = $sth->fetchObject()) !== false )
	{
		$result->UserID |= 0;
		$uid = 0;
		if (isset($lup[$result->Username]))
		{
			$uid = $lup[$result->Username];
		}
		else
		{
			$uid = $invalidIds--;
			$data[$uid] = new LdapUser($conn, $result->Username);
			$data[$uid]->fullName = "*".$result->Fullname."*";
			$data[$uid]->uid = $uid;
		}
		$data[$uid]->projectTrackerUserId = $result->UserID ;
		$projectToLdap[$result->UserID] = $uid;
	}
	return $data;
}

$users = GetUsers();
function uCmp( $a, $b )
{
	$res = strcasecmp($a->sn, $b->sn );
	if ( $res == 0 )
		$res = strcasecmp($a->givenName, $b->givenName);
	return $res;
}

uasort( $users, "uCmp" );

function GetProjects()
{
	global $pdo;
	global $projectToLdap;
	
	$sql = "SELECT PROJECT_ID, PROJECT_NAME, PROJECT_MANAGER_ID FROM PROJECTS WHERE IS_ACTIVE = 1"; 
	
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute();
	
	$data = array();
	while ( ($result = $sth->fetchObject()) !== false )
	{
		$result->uid = $projectToLdap[$result->PROJECT_MANAGER_ID];
		$data[$result->PROJECT_ID] = $result;
	}
	return $data;
}

function GetProjectAssignments()
{
	global $pdo;
	global $projectToLdap;
	
	$sql = "SELECT PROJECT_ID, UserID FROM PROJECT_ASSIGNMENT"; 
	
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute();
	
	$data = array();
	while ( ($result = $sth->fetchObject()) !== false )
	{
		$projId = $result->PROJECT_ID | 0;
		if ( !isset($data[$projId]))
			$data[$projId]=array();
		$uid = $projectToLdap[$result->UserID];
		$data[$projId][] = $uid;
	}
	return $data;
}

$projects = GetProjects();

$projectAssignmentsTemp = GetProjectAssignments();
$projectAssignments = array();

function paCmp( $a, $b )
{
	global $users;
	
	$au = $users[$a];
	$bu = $users[$b];
	$res = strcasecmp($au->sn, $bu->sn );
	if ( $res == 0 )
		$res = strcasecmp($au->givenName, $bu->givenName);
	return $res;
}

foreach ( $projectAssignmentsTemp as $k => $v )
{
	usort( $v, "paCmp" );
	$projectAssignments[$k] = $v;
}

function GetOrgChart()
{
	global $pdo;
	global $projectToLdap;
	
	$sql = "SELECT ORG_ID, EMPLOYEE_ID, TITLE, SECTION_NAME, if(IS_PRIMARY_REPORTING,1,0) as IS_PRIMARY_REPORTING, REPORTS_TO FROM ORG_REL ORDER BY REPORTS_TO"; 
	
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute();
	
	$data = array();
	while ( ($result = $sth->fetchObject()) !== false )
	{
		$result->ORG_ID |= 0;
		$result->EMPLOYEE_ID |= 0;
		$result->IS_PRIMARY_REPORTING = ($result->IS_PRIMARY_REPORTING|0) != 0;
		$result->REPORTS_TO |= 0;
		$data[$result->ORG_ID] = $result;
	}
	return $data;
}

function GetGroupManagers()
{
	global $pdo;

	$sql = "SELECT OPR.PROJECT_ID, OPR.ORG_ID FROM ORG_PROJ_REL OPR, PROJECTS P WHERE OPR.PROJECT_ID=P.PROJECT_ID AND P.IS_ACTIVE=true"; 
	
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute();
	
	$data = array();
	while ( ($result = $sth->fetchObject()) !== false )
	{
		$orgId = $result->ORG_ID | 0;
		if ( !isset($data[$orgId]))
			$data[$orgId]=array();
		$data[$orgId][] = $result->PROJECT_ID|0;
	}
	return $data;
}

$groupTemps = GetGroupManagers();
function prCmp( $a, $b )
{
	global $projects;
	if ( !isset($projects[$a]) )
	{
		echo $a;
		return 0;
	}
	
	$pa = $projects[$a];
	if ( !isset($projects[$b]) )
	{
		echo $b;
		return 1;
	}
	$pb = $projects[$b];
	return strcasecmp($pa->PROJECT_NAME, $pb->PROJECT_NAME );
}

$groups = array();
foreach ( $groupTemps as $k => $v )
{
	usort( $v, "prCmp" );
	$groups[$k] = $v;
}

$sortedOrgs = GetOrgChart();

function orgCmp( $a, $b )
{
	global $users;

	if ( $a->REPORTS_TO < $b->REPORTS_TO )
		return -1;
	if ( $a->REPORTS_TO > $b->REPORTS_TO )
		return 1;
	
	$r = strcasecmp( $a->SECTION_NAME == null ? "" : $a->SECTION_NAME, $b->SECTION_NAME == null ? "" : $b->SECTION_NAME );
	if ( $r != 0 )
		return $r;
	
	if ( $a->EMPLOYEE_ID == 0 )
	{
		if ( $b->EMPLOYEE_ID == 0 )
			return 0;
		else
			return -1;
	}
	if ( $b->EMPLOYEE_ID == 0 )
	{
		return 1;
	}
	$au = $users[$a->EMPLOYEE_ID];
	$bu = $users[$b->EMPLOYEE_ID];
	$res = strcasecmp($au->sn, $bu->sn );
	if ( $res == 0 )
		$res = strcasecmp($au->givenName, $bu->givenName);
	return $res;
}

usort( $sortedOrgs, "orgCmp" );
$orgs = array();
$orgOrder = array();
foreach ( $sortedOrgs as $v )
{
	$orgs[$v->ORG_ID] = $v;
	$orgOrder[]= $v->ORG_ID;
}

$editMode = $isAuth;

function GetUnassignedProjects()
{
	global $pdo;
	global $projectToLdap;
	
	$sql = "SELECT PROJECT_ID FROM PROJECTS WHERE IS_ACTIVE = 1 AND PROJECT_ID <> 1 AND PROJECT_ID NOT IN (SELECT PROJECT_ID FROM ORG_PROJ_REL)"; 
	
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute();
	
	$data = array();
	while ( ($result = $sth->fetchObject()) !== false )
	{
		$data[] = $result;
	}
	return $data;
}

$unAssPrjs = GetUnassignedProjects();

function GetUnassignedUsers()
{
	global $pdo;
	global $projectToLdap;
	
	$sql = "SELECT Username, FullName FROM Users WHERE UserID NOT IN (SELECT UserID FROM PROJECT_ASSIGNMENT PA, PROJECTS PRJ WHERE PA.PROJECT_ID=PRJ.PROJECT_ID AND PRJ.IS_ACTIVE=true) AND State='active' AND UserID<>-1";
	
	$sth = $pdo->prepare( $sql );
	$res = $sth->execute();
	
	$data = array();
	while ( ($result = $sth->fetchObject()) !== false )
	{
		$data[] = $result;
	}
	return $data;	
}

if ( $isAuth )
{
	$unassignedUsers = GetUnassignedUsers();
}
else
{
	$unassignedUsers = array(); 
}


?><html>
<head>
<link rel="stylesheet" href="css/org.css">
<script src="js/jquery-3.4.1.min.js"></script>
<script>

var emps = <?php echo json_encode($users); ?>;
var orgs = <?php echo json_encode($orgs); ?>;
var orgOrder = <?php echo json_encode($orgOrder); ?>;
var groups = <?php echo json_encode($groups); ?>;
var projects= <?php echo json_encode($projects); ?>;
var projectAssignments = <?php echo json_encode($projectAssignments); ?>;

<?php
if ( $editMode )
{
?>
function doPost( payload, cb )
{
	var response = {"error":-1,"error_message": "Command failed"};
	$.ajax({
		type: "POST",
		url: 'ajaxCmd.php',
		dataType: 'json',
		data: JSON.stringify(payload),
		cache: false,
		async: true,
		success: function(data){
			cb(data);
		},
		error: function(xhr,txt, error) {
			response.error = -2;
			response.error_message = "Request failure: " + xhr.responseText + " " + error;
			response.error_extra = xhr.status;
			cb(response);
		}
	});
}	

var currModal = undefined;

function closeModal(e)
{
	$(currModal).removeClass("show-modal");
	currModal = undefined;
}

function showModal(e,who)
{
	currModal = who;
	$(who).addClass("show-modal");
}
<?php
}
?>
</script>
</head>
<body>
<?php
if ( $isAuth )
{
	echo "<img src='images\add.png' onclick='document.location=\"logout.php\"; return false'/>";
}
else
{
	echo "<img src='images\lock.png' onclick='document.location=\"login.php\"; return false'/>";
}
?>
<div class="org-chart" id="orgchart">
</div>



<?php

if ( count($unassignedUsers) > 0 )
{
	echo "<hr/>";
	echo "<h1>Users which are not assigned to projects</h1>";
	foreach ( $unassignedUsers as $k => $v )
	{
		echo "<div class='UnassignedUsers'>".$v->FullName." (".$v->Username.")</div>";
	}
}

if ( $isAuth )
{
?>
<div id='selectEmployee' class='modal'>
	<div class='modal-content'>
		<input type=hidden  id='selectEmployeeOrgId'/>
		<div class='modal-title'>Select Employee</div>
		Employee:<br/>
		<select id='selectEmployee'>
			<option value='0'>#vacant#</option>
<?php
		foreach ( $users as $uid => $user )
		{
			echo "<option value='".$user->uid."'>".$user->fullName."</option>\n";
		}
?>		
		</select>
		<button onclick='UpdateOrg();'>Update</button><button id='closeSelectEmployee' class='close-button'>Cancel</button>

	</div>
</div>


<div id='reparentDialog' class='modal'>
	<div class='modal-content'>
		<input type=hidden  id='reparentOrg'/>
		<div class='modal-title'>Select New Parent</div>
		Position:<br/>
		<select id='reparentOrgs'>
		</select>
		<button onclick='doReparentOrg();'>Update</button><button id='closeReparentDialog' class='close-button'>Cancel</button>

	</div>
</div>


<div id='projectOwnerDialog' class='modal'>
	<div class='modal-content'>
		<input type=hidden  id='projectOwnerProjId'/>
		<div class='modal-title'>Select New Owner</div>
		Position:<br/>
		<select id='projectOwners'>
		</select>
		<button onclick='doUpdateProjectOwnwer();'>Update</button><button id='closeReparentDialog' class='close-button'>Cancel</button>

	</div>
</div>
<?php
}
?>
<script>

	function getEmployeeName( employeeId )
	{
		if ( employeeId == 0 )
		{
			return "#vacant#";
		}
		else
		{
			if ( emps[employeeId] !== undefined )
				return emps[employeeId].fullName;
			else	
				return "%UNKNOWN%("+employeeId+")";
		}
	}

	function RenderNode( orgIdx )
	{
		var org = orgs[orgIdx];
		var res = "<li><div class='user'>";
<?php
if ( $isAuth )
{
?>
		
		if ( org.ORG_ID != -1 )
		{
			//res += "<div class='move' onclick='ReparentNode("+org.ORG_ID+"); return false;'>*</div>";
			res += "<img src='images/swap.png' class='move' onclick='ReparentNode("+org.ORG_ID+"); return false;'/>";
		}	
		
		res += "<img src='images/add.png' class='edit' onclick='editSection("+org.ORG_ID+", "+JSON.stringify(org.SECTION_NAME==null?"":org.SECTION_NAME)+"); return false;'/>";
<?php
}
?>
		
		if ( org.SECTION_NAME != null )
		{
<?php
if ( $isAuth )
{
?>
			res += "<div class='sectionTitle' onclick='editSection("+org.ORG_ID+", "+JSON.stringify(org.SECTION_NAME==null?"":org.SECTION_NAME)+"); return false;'>";
<?php
} else {
?>
			res += "<div class='sectionTitle'>";
<?php
}
?>
			res += org.SECTION_NAME;
			res += "</div>";
		}
		
		if ( emps[org.EMPLOYEE_ID] !== undefined )
		{
			u = emps[org.EMPLOYEE_ID];
			if ( u.photo )
			{
				res += "<img class='portrait' src='data:image/jpeg;base64,"+u.photo+"'/>";
			}
			else
			{
				res += "<img class='portrait' src='images/person_blue.png'/>";
			}
		}
		else
		{
				res += "<img class='portrait' src='images/person_blue.png'/>";
		}			
		
		if ( org.ORG_ID != -1 )
		{
<?php
if ( $isAuth )
{
?>
			res += "<div class='name' onclick='editEmployee("+org.ORG_ID+","+org.EMPLOYEE_ID+"); return false;'>";
<?php
} else {
?>
			res += "<div class='name'>";
<?php
}
?>
			res += getEmployeeName(org.EMPLOYEE_ID);
			res += "</div>";
		}
<?php
if ( $isAuth )
{
?>
		res += "<div class='role' onclick='editTitle("+org.ORG_ID+", "+JSON.stringify(org.TITLE)+"); return false;'>";
<?php
} else {
?>
		res += "<div class='role'>";
<?php
}
?>
		res += org.TITLE;
		res += "</div>";
		if ( org.IS_PRIMARY_REPORTING == 0 )
			res += "<div class='role'>Matrixed</div>";
		var bFoundOne = false;
		
		var kids = "";
		var childCount = 0;

		for ( var rptIdx in orgOrder )
		{
			var rpt = orgs[orgOrder[rptIdx]];
			if ( rpt.REPORTS_TO == org.ORG_ID )
			{
				if ( !bFoundOne )
				{
					kids += "<ul>";
					bFoundOne = true;
				}
				kids += RenderNode(rpt.ORG_ID);
				childCount++;
			}
		}
		
		if ( groups[org.ORG_ID] !== undefined )
		{
			if ( !bFoundOne )
			{
				kids += "<ul>";
				bFoundOne = true;
			}
			kids += "<li>";
			for ( gIdx in groups[org.ORG_ID] )
			{
				var group = groups[org.ORG_ID][gIdx];
				kids += "<div class='project'>";
<?php
if ( $isAuth )
{
?>	
				kids += "<img src='images/swap.png' class='swap' onclick='swapProject("+group +", "+org.ORG_ID+"); return false;'/>";
<?php
}
?>				
				var proj = projects[group];
				kids += "<div class='projectTitle'>"+proj.PROJECT_NAME + "</div>";
				
				var mgr = emps[proj.uid];
				kids += "<div class='lead'>";
				if ( mgr.photo !== undefined )
					kids += "<img class='miniportrait' src='data:image/jpeg;base64,"+mgr.photo+"'/>";				
				else
					kids += "<img class='miniportrait' src='images/person_blue.png'/>";
				kids += getEmployeeName(mgr.uid) + " (lead)";
				kids += "</div>";
				var assignments = projectAssignments[group];
				if ( assignments !== undefined )
				{
					for ( assIdx in assignments )
					{
						var ass = assignments[assIdx];
						if ( ass == proj.uid )
							continue;
						
						var emp = emps[ass];
						kids += "<div class='member'>";
						if ( emp.photo !== undefined )
							kids += "<img class='miniportrait' src='data:image/jpeg;base64,"+emp.photo+"'/>";				
						else
							kids += "<img class='miniportrait' src='images/person_blue.png'/>";
						kids += getEmployeeName(emp.uid);
						kids += "</div>";
					}
				}
				kids += "</div><br/><br/>";
				childCount++;
			}
			kids += "</li>";
		}

		if ( bFoundOne )
			kids += "</ul>";
		
<?php
if ( $isAuth )
{
?>
		if ( childCount == 0 && org.ORG_ID != -1 )
		{
			res += "<img src='images/trash.png' class='trash' onclick='TrashNode("+org.ORG_ID+"); return false;'/>";
		}
		res += "<img src='images/green_plus.png' class='add' onclick='AddNode("+org.ORG_ID+"); return false;'/>";
<?php
}
?>
		
		res += "</div>";
		res += kids;
		res += "</li>\n";
		return res;
	}

	function renderOrgChart()
	{
		var body = "<ul>";
		for ( var orgIdx in orgs )
		{
			var org = orgs[orgIdx];
			if ( org.REPORTS_TO == 0 )
			{
				body += RenderNode(orgIdx);
			}
		}
		body += "</ul>";
		$("#orgchart").html(body);
	}


<?php
if ( $isAuth )
{
?>
	function isAncestor( parentId, thisId )
	{
		if ( thisId == parentId )
			return true;
		var anOrg = orgs[thisId];
		if ( anOrg.REPORTS_TO == 0 )
			return false
		return isAncestor( parentId, anOrg.REPORTS_TO );
	}

	function ReparentNode( orgId )
	{
		var options = "";
		var currParent = orgs[orgId].REPORTS_TO;
		for ( orgIdx in orgs )
		{
			var org = orgs[orgIdx];
			if ( !isAncestor( orgId, org.ORG_ID ) )
			{
				options += "<option "+((org.ORG_ID == currParent)?"SELECTED":"")+" value='" + org.ORG_ID + "'>" + org.TITLE + "(" + getEmployeeName(org.EMPLOYEE_ID) + ")</option>\n";
			}
		}
		$("#reparentOrgs").html(options);
		$("#reparentOrg").val(orgId);
		showModal({},"#reparentDialog");
	}
	
	function doReparentOrg()
	{
		closeModal({});
		var org_id = $("#reparentOrg").val() | 0;
		var newReportsTo = $("#reparentOrgs option:selected").val() | 0;
		doPost( 
		{
			"Command":"UpdateReportsTo",
			"org_id" : org_id,
			"new_parent_id" : newReportsTo
		}, function(r) {
			orgs[org_id].REPORTS_TO = newReportsTo;
			renderOrgChart();
			}
		);
	}
	
	
	function doUpdateProjectOwnwer()
	{
		closeModal({});
		var projId = $("#projectOwnerProjId").val() | 0;
		var newOrg = $("#projectOwners option:selected").val() | 0;
		doPost( 
		{
			"Command":"UpdateProjectParent",
			"proj_id" : projId,
			"newOrg" : newOrg
		}, function(r) {
			document.location.reload();
			}
		);
	}
	
	function swapProject( projectId, parentOrg )
	{
		var options = "";
		for ( orgIdx in orgs )
		{
			var org = orgs[orgIdx];
			options += "<option "+((org.ORG_ID == parentOrg)?"SELECTED":"")+" value='" + org.ORG_ID + "'>" + org.TITLE + "(" + getEmployeeName(org.EMPLOYEE_ID) + ")</option>\n";
		}
		$("#projectOwners").html(options);
		$("#projectOwnerProjId").val(projectId);
		showModal({},"#projectOwnerDialog");
	}	
	
	function UpdateOrg()
	{
		closeModal({});
		var org_id = $("#selectEmployeeOrgId").val() | 0;
		var employee_id = $("#selectEmployee option:selected").val() | 0;
		doPost( 
		{
			"Command":"UpdateEmployee",
			"org_id" : org_id,
			"employee_id" : employee_id
		}, function(r) {
			document.location.reload();
			}
		);
	}
	
	function editTitle( orgId, title )
	{
		if ( (newTitle = prompt("Enter new title", title )) != null )
		{
			if ( $.trim(newTitle) == "" )
			{
				alert("Title cannot be empty");
				return;
			}
			doPost( 
			{
				"Command":"UpdateTitle",
				"org_id" : orgId,
				"title" : newTitle
			}, function(r) {
				orgs[orgId].TITLE = newTitle;
				renderOrgChart();
				}
			);			
		}
	}
	
	function editSection( orgId, sectionName )
	{
		if ( (newSectionName = prompt("Enter new section name", sectionName )) != null )
		{
			doPost( 
			{
				"Command":"UpdateSectionName",
				"org_id" : orgId,
				"sectionName" : newSectionName
			}, function(r) {
				document.location.reload();
				}
			);			
		}
	}	

	function TrashNode( orgId )
	{
		if ( confirm("Are you sure you want to delete this node?") )
		{
			doPost( 
			{
				"Command":"DeleteNode",
				"org_id" : orgId
			}, function(r) {
				document.location.reload();
				}
			);			
		}
	}
	
	function AddNode( orgId )
	{
		if ( (newTitle = prompt("Enter new title", "New Hire" )) != null )
		{
			doPost( 
			{
				"Command":"AddNode",
				"org_id" : orgId,
				"title" : newTitle
			}, function(r) {
				document.location.reload();
				}
			);			
		};
	}
		
	
	function editEmployee( orgId, employeeId )
	{
		$("#selectEmployeeOrgId").val(orgId);
		$("#selectEmployee option[value='"+employeeId+"']").attr("selected","selected");
		showModal({},"#selectEmployee");
	}
<?php
}
?>


	$( document ).ready( 
		function() 
		{
<?php
if ( $isAuth )
{
?>
			var btns = document.querySelectorAll(".close-button");
			[].forEach.call(btns,function (btn) { btn.addEventListener("click", closeModal); } );	
<?php
	if ( count($unAssPrjs) > 0 )
	{
?>
		doPost( 
		{
			"Command":"AssignOrphansProjects"
		}, function(r) {
			}
		);	
<?php	
	}
}
?>
			
			renderOrgChart();
		}
	);
</script>
</body>
</html>
