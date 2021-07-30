<?php
require_once( dirname( __FILE__ )."/./docroot/authenticate.php" );
require_once( dirname( __FILE__ )."/projTrackDB.php" );
$Employee_id = $_SESSION["USERID"];
?>


<html>

<head>
	<title>Project Tracker | Project Summaries</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
	<!--link rel="icon" href="images/favicon.ico" type="image/x-icon"-->
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>

<div id="header">
<?php
include "header.php";
?>
</div>

<div id="wrapper">

<script>

var timeoutId;
function saveText(e)
{
	
	var allEntries = $("textarea");
	
	var eID = $(e).attr('id');
	// console.log("eID: " + eID); 
	var eRow = parseInt(eID.match(/\d+/),10); //grabbed this from stackoverflow it's a regex to get the first int in a string
	// console.log("eRow: " + eRow);
	var entryState = "PARTIAL";
	
	if($('#AccomplishmentsResponse' + eRow).val().length == 0)
		entryState = "NONE"; 
	if($('#IssuesResponse' + eRow).val().length == 0)
		entryState = "NONE";
	if($('#UpcomingGoalsResponse' + eRow).val().length == 0)
		entryState = "NONE";
	
	var eValue = $("#" + e.id).val();
	eValue = eValue.split("\"").join("\\\"");
	
    if (timeoutId) clearTimeout(timeoutId);
    timeoutId = setTimeout(function () {
		console.log("saving...");
		timeoutReset(); //restart the session timeout js timer
    	$.ajax({
            url: 'api.php',
            data: { cmd: "autoSave", projectID: $("#" + e.id).attr('projid'), userID: $("#" + e.id).attr('userID'), text: eValue, id:$("#" + e.id).attr('id'), entryState: entryState, isSummary : 0, color: ""},
			success: function(data){
				console.log(data);
			}
        });
    }, 100);
}

function roundEffort(id){
  var idValue = document.getElementById(id).value;
  value = parseInt(Math.round(idValue/5)*5);
  document.getElementById(id).value = value;
}
</script>

<h1>Projects</h1>
<?php

$currentDate = date("y-m-d");
$currentSunday = date('y-m-d', strtotime("last Sunday", strtotime($currentDate)));

$entryDate = PTGetSundayDateString($_SESSION['DATE']);

$enddate = date('Y-m-d', strtotime("next Saturday", strtotime($entryDate)));
echo "<h3>Viewing Week of: ". $entryDate . " &#8212; " . $enddate ." </h3>";

?>

<!-- table headers -->
	<div class="project-table-headers">
		<div class="project-arrow-div"></div>
		<div class="project-color-div"><h5>Health</h5></div>
		<div class="project-title-div"><h5>Project Title</h5></div>
		<div class="project-warning-div"><h5>Status</h5></div>
		<div class="project-end-div"><h5>End Date</h5></div>
		<div class="project-trash-div"><h5>Delete</h5></div>
		<div class="project-reorder-div"><h5>Reorder</h5></div>
	</div>

<div id="tableDiv">


<table id="projectTable">
    
<?php
$count = 0;
$userId =  $_SESSION["USERID"];
$page = "PROJECTS";

$isReadOnly = "";
$layout = array();

$layout = PTGetProjectsForUserAndDate( $userId, $entryDate, false );

if(strtotime($currentSunday) == strtotime($entryDate)) //Ensure all assigned projects are present
{
	$projData = PTGetAssignedProjectsForUser($userId); 
	foreach ($projData as $projId)
	{
		if ( !in_array($projId, $layout))
		{
			array_push($layout, $projId);
		}
	}
}

if ( !in_array( MISC_PROJECT_ID, $layout ) )
	array_push($layout, MISC_PROJECT_ID );


$totalCount = count($layout);
$count = 1;
foreach ( $layout as $projectId )
{
	$healthState = PTGetHealthForProjectOnOrBeforeDate( $projectId, $entryDate );
	if ( $healthState === false )
	{
		$healthState = "NULL";
	}
	
	$projectInfo = PTGetProjectInfoById( $projectId );
	$projectInfo->HEALTH = $healthState;
	
	include 'createRow.php';
	$count = $count + 1;
}

?>
</table>

<script>
	function changeStatus(num){
		// console.log("status checked " + num);
		
		if($('#PreviousGoalsResponse' + num).val() != "" && $('#AccomplishmentsResponse' + num).val() != "" && $('#IssuesResponse' + num).val() != "" && $('#UpcomingGoalsResponse' + num).val() != "" && $('#projectStar' + num).css('display') == "none"){
			$('#projectWarning' + num).toggle();
			$('#projectStar' + num).toggle();
		}else if($('#projectWarning' + num).css('display') == "none" && ($('#PreviousGoalsResponse' + num).val() == "" || $('#AccomplishmentsResponse' + num).val() == "" || $('#IssuesResponse' + num).val() == "" || $('#UpcomingGoalsResponse' + num).val() == "")){
			$('#projectWarning' + num).toggle();
			$('#projectStar' + num).toggle();
		}
	}
	for(var i = 0; i < <?php echo $count?>; i+=1){
		changeStatus(i);
	}


    var table = document.getElementById('projectTable');

    table.onclick = function(event) {
		event.preventDefault();
      let target = event.target;
      while (target != this) {
        if (target.tagName == 'I' && ($(event.target).attr('class') == 'fa fa-caret-square-right' || $(event.target).attr('class') == 'fa fa-caret-square-down')) {
			console.log(target);
			$('.cat'+$(event.target).attr('data-prod-cat')).toggle();//actually toggles the drop down text
			$(event.target).toggleClass('fa-caret-square-right fa-caret-square-down');// changes the arrow icon
          return;
        }
        target = target.parentNode;
      }
    }
	
  </script>
		<div class="project-wrapper" id = "addTempProjectDiv">
			<div class="project-header">
				<h4>Add Temp Project</h4>
			</div>
			<div class="project-fields">
			<?php
				$pListResults = PTGetAvailableProjectsForUserAndDate($Employee_id,$entryDate );
				echo "<select id=\"addProjectsList\">";
				echo "<option value=\"start\" selected=\"selected\" disabled>--Add Project--</option>";
				foreach ($pListResults as $result )
				{
					echo "<option value=\"". $result->PROJECT_ID. "\">". $result->PROJECT_NAME. "</option>";
				}
				echo "</select>";
			?>
			</div>
			<div class="project-fields">
			<button id="addProjectButton">Add</button>
		</div>
	</div>
  </div>

<script>

	 function addTempDisplay(lockStatus) {
		if(lockStatus == 1) {//The week is locked
			document.getElementById("addTempProjectDiv").style.display = "none";
			document.getElementById("trashIcon").style.display = "none";
		}
		else {
			document.getElementById("addTempProjectDiv").style.display = "block";
		}
	 }
	 
	function lockStatus(){
		lockStatus = "";

		$.post("api.php",
		{
			cmd: "lockStatus",
			page: "PROJECTS"
		},
		function(data){
			lockStatus = data;
			// console.log("Lock status: " + lockStatus);
			addTempDisplay(lockStatus);
		});
	}

	document.addEventListener('DOMContentLoaded', lockStatus());

	var select = $('#addProjectsList');
	  select.html(select.find('option').sort(function(x, y) {
		// to change to descending order switch "<" for ">"
		return $(x).text() > $(y).text() ? 1 : -1;
	  }));
	  
	  $("#addProjectsList")[0].selectedIndex = 0;

	function removeProj(num)
	{
		if($('#addProjectsList').css("display") == "none")
		{
			$('#addProjectsList').show();
			$('#addProjectButton').show();
		}
		
		$.post("api.php",
		{
			cmd: "deleteProject",
			projectID: $("#rowCount" + num).attr("projID")
		},
		function(data, status){});
		
		$('#addProjectsList').append($('<option>', {
			value: $("#rowCount" + num).attr("projID"),
			text: $("#rowCount" + num).attr("projName")
		}));
		
		var select = $('#addProjectsList');
		select.html(select.find('option').sort(function(x, y) {
			// to change to descending order switch "<" for ">"
			return $(x).text() > $(y).text() ? 1 : -1;
		}));
	  
		$("#addProjectsList")[0].selectedIndex = 0;
			
		$("#rowCount" + num).remove();
		if($('.cat' + num).css("display") != "none")
			$('.cat' + num).toggle();
	}

	$(document).ready(function()
	{
		var count = <?php echo $count; ?>;
		var Employee_id = <?php echo $Employee_id; ?>;
		$("#addProjectButton").click(function() {
			if($('#addProjectsList').val() != null){
			console.log("addProjectList val: " + $('#addProjectsList').val()); 
			console.log("addProjectList employee val: " + <?php echo $Employee_id; ?>);
			
			$.post("api.php", 
			  {
				cmd: "addRow",
				projectID: $('#addProjectsList').val()
			  },
			  function(data, status){
			  console.log("Type: " + typeof(data));
			  var j = JSON.parse(data);
			  console.log(j);
			  
			  if(j["health"] == "GREEN")
				dropClass = "dropbtnGreen";
			  else if(j["health"] == "YELLOW")
				dropClass = "dropbtnYellow";
			  else if(j["health"] == "RED")
				dropClass = "dropbtnRed";
			  else
				dropClass = "dropbtnNull";

				var table = document.getElementById("projectTable");
				var row = table.insertRow(document.getElementById("projectTable").rows.length - 2);
				row.outerHTML = "<tr id=\"rowCount" + count + "\" projID=\"" + j["projectID"] + "\" projName=\"" + j["projectName"] + "\"> <th class=\"project-arrow\"><i href=\"#\" class=\"fa fa-caret-square-right\" data-prod-cat=\"" + count + "\"></i></th><th class=\"project-color\"><button id=\"dropbtn" + count + "\" class=\"" + dropClass + "\"></button></th><th class=\"project-title\">"+j["projectName"]+"</th><th class=\"project-status\"><div id=\"projectStatus\"><span class=\"project-warning\"><i id=\"projectWarning" + count + "\" class=\"fas fa-exclamation-triangle\"></i></span><span class=\"project-star\"><i id=\"projectStar" + count + "\" class=\"fas fa-star\" style=\"display:none;\"></i></span></div></th><th class=\"project-end\">" + j["end_date"].substring(0,j["end_date"].indexOf(' ')) + "</th><th class=\"project-delete\"><span class=\"project-trash-no-delete\" onclick=\"removeProj(" + count + ")\"><i class=\"fas fa-trash-alt\"></i></span></th><th></th></tr>";
				
				var row = table.insertRow(document.getElementById("projectTable").rows.length - 2);
				row.outerHTML = "<tr class=\"cat"+ count + "\" style=\"display:none\"><td colspan=\"7\"><table id=\"innerTable" + count + "\"></table></td></tr>";
				table = document.getElementById("innerTable" + count);
				row = table.insertRow(-1);
				var cell0 = row.insertCell(0);
				var cell1 = row.insertCell(1);
				var cell2 = row.insertCell(2);
				var cell3 = row.insertCell(3);
				var cell4 = row.insertCell(4);
				
				cell0.outerHTML = "<td class=\"project-headers\">Effort</td>"; 
				cell1.outerHTML = "<td class=\"project-headers\">Previous Goals</td>"; 
				cell2.outerHTML = "<td class=\"project-headers\">Accomplishments</td>";
				cell3.outerHTML = "<td class=\"project-headers\">Issues</td>";
				cell4.outerHTML = "<td class=\"project-headers\">Upcoming Goals</td>";
				
				var row = table.insertRow(-1);
				var cell1 = row.insertCell(0);
				var cell2 = row.insertCell(1);
				var cell3 = row.insertCell(2);
				var cell4 = row.insertCell(3);
				var cell5 = row.insertCell(4);
				
				if(!(j["effort"] == null)){
					console.log("HIT IF");
					cell1.innerHTML = "<td class=\"effort-percentage-td\"><h4 class=\"effort-percent-sign\"><textarea class=\"effort\" maxlength=\"3\" id=\"EffortResponse" + count + "\" onkeyup=\"changeStatus(" + count + "); saveText(this);\" projName=\"" + j["projectName"] + "\" projid=\"" + j["projectID"] + "\" userID=\"" + Employee_id + "\">" + j["effort"] + "</textarea>&nbsp;&#37;</h4></td>";
				}else{
					console.log("HIT ELSE");
					cell1.innerHTML = "<td class=\"effort-percentage-td\"><h4 class=\"effort-percent-sign\"><textarea class=\"effort\" maxlength=\"3\" id=\"EffortResponse" + count + "\" onkeyup=\"changeStatus(" + count + "); saveText(this);\" projName=\"" + j["projectName"] + "\" projid=\"" + j["projectID"] + "\" userID=\"" + Employee_id + "\"></textarea>&nbsp;&#37;</h4></td>";
				}
				
				if(!(j["previousGoals"] == null)){
					console.log("HIT IF");
					cell2.innerHTML = "<textarea class=\"previous-goals\" readonly textarea id=\"PreviousGoalsResponse" + count + "\" onkeyup=\"changeStatus(" + count + "); saveText(this);\" projName=\"" + j["projectName"] + "\" projid=\"" + j["projectID"] + "\" userID=\"" + Employee_id + "\">No Previous Goals</textarea>"; //" + j["previousGoals"] + "
				}else{
					console.log("HIT ELSE");
					cell2.innerHTML = "<textarea id=\"PreviousGoalsResponse" + count + "\" onkeyup=\"changeStatus(" + count + "); saveText(this);\" projName=\"" + j["projectName"] + "\" projid=\"" + j["projectID"] + "\" userID=\"" + Employee_id + "\">No Previous Goals</textarea>";
				}
				if(!(j["accomplishments"] == null)){
					console.log("HIT IF");
				cell3.innerHTML = "<textarea maxlength=\"242\" id=\"AccomplishmentsResponse" + count + "\" onkeyup=\"changeStatus(" + count + "); saveText(this);\" projName=\"" + j["projectName"] + "\" projid=\"" + j["projectID"] + "\" userID=\"" + Employee_id + "\">" + j["accomplishments"] + "</textarea>";
				}else{
					console.log("HIT ELSE");
					cell3.innerHTML = "<textarea maxlength=\"242\" id=\"AccomplishmentsResponse" + count + "\" onkeyup=\"changeStatus(" + count + "); saveText(this);\" projName=\"" + j["projectName"] + "\" projid=\"" + j["projectID"] + "\" userID=\"" + Employee_id + "\"></textarea>";
				}
				if(!(j["issues"] == null)){
					console.log("HIT IF");
				cell4.innerHTML = "<textarea maxlength=\"242\" id=\"IssuesResponse" + count + "\" onkeyup=\"changeStatus(" + count + "); saveText(this);\" projName=\"" + j["projectName"] + "\" projid=\"" + j["projectID"] + "\" userID=\"" + Employee_id + "\">" + j["issues"] + "</textarea>";
				}else{
					console.log("HIT ELSE");
					cell4.innerHTML = "<textarea maxlength=\"242\" id=\"IssuesResponse" + count + "\" onkeyup=\"changeStatus(" + count + "); saveText(this);\" projName=\"" + j["projectName"] + "\" projid=\"" + j["projectID"] + "\" userID=\"" + Employee_id + "\"></textarea>";
				}
				if(!(j["upcomingGoals"] == null)){
					console.log("HIT IF");
				cell5.innerHTML = "<textarea maxlength=\"242\" id=\"UpcomingGoalsResponse" + count + "\" onkeyup=\"changeStatus(" + count + "); saveText(this);\" projName=\"" + j["projectName"] + "\" projid=\"" + j["projectID"] + "\" userID=\"" + Employee_id + "\">" + j["upcomingGoals"] + "</textarea>";
				}else{
					console.log("HIT ELSE");
					cell5.innerHTML = "<textarea maxlength=\"242\" id=\"UpcomingGoalsResponse" + count + "\" onkeyup=\"changeStatus(" + count + "); saveText(this);\" projName=\"" + j["projectName"] + "\" projid=\"" + j["projectID"] + "\" userID=\"" + Employee_id + "\"></textarea>";
				}

				row.outerHTML = "<tr class=\"cat"+ count + "\" style=\"display:none\">" + row.outerHTML.substring(4);
				
				changeStatus(count);
				count++;
			  });

			  $("#addProjectsList option[value=\"" + $('#addProjectsList').val() + "\"]").remove();
			  
			  var select = $('#addProjectsList');
			  select.html(select.find('option').sort(function(x, y) {
				// to change to descending order switch "<" for ">"
				return $(x).text() > $(y).text() ? 1 : -1;
			  }));
			  
			  $("#addProjectsList")[0].selectedIndex = 0;
			  
			  if($("#addProjectsList > option").length == 1){
				  $("#addProjectsList").hide();
				  $('#addProjectButton').hide();
			  }
			}
		});
	});
</script>

</div><!-- wrapper -->

<div id="footer">
<?php
include "footer.php";
?>
</div>


</body>
</html>
