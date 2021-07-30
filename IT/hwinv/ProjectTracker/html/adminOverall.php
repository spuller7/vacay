<?php
require_once( dirname( __FILE__ )."/./docroot/authenticate.php" );
require_once( dirname( __FILE__ )."/projTrackDB.php" );

if(  hasRole(Roles::ADMINISTRATOR) || hasRole(Roles::MANAGER))
{
?>
<html>

<head>
	<title>Project Tracker | Overall View</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
<!--script>
$(function(){
  
  $("#header").load("header.php"); 
  $("#footer").load("footer.php"); 
});
</script-->

<body>

<div id="header">
<?php
include "header.php";
?>
</div>
<label for "saved" id="successmessage"></label>
<div id="wrapper">

<h1>Overall View</h1>
<?php
	$selectedDate = PTGetSundayDateString($_SESSION['DATE']);
	
	$entryDate = PTGetSundayDateString($selectedDate); 
	$enddate = date('Y-m-d', strtotime("next Saturday", strtotime($selectedDate)));
	
	$prevStartDate = date('y-m-d', strtotime( "-7 days", strtotime($entryDate)));
	
	$currentDate = date("y-m-d");
	$currentSunday = date('y-m-d', strtotime("last Sunday", strtotime($currentDate)));
	
	echo "<h3>Viewing Week of: ". $entryDate . " &#8212; " . $enddate ." </h3>";
?>

<div id="myModal" class="modal">
  <div class="modal-content">
    <h2>Edit Project</h2>
	 
	<script>
	var timeoutId;
	function saveText(e)
	{
		var allEntries = $("textarea");
		
		var eID = $(e).attr('id');
		var eRow = parseInt(eID.match(/\d+/),10); //grabbed this from stackoverflow it's a regex to get the first int in a string
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
		timeoutId = setTimeout(function () 
		{
			// console.log("saving...");
			timeoutReset(); //restart the session timeout js timer
			$.ajax({
				url: 'api.php',
				data: { cmd: "autoSave", projectID: $("#" + e.id).attr('projid'), text: eValue, id: $("#" + e.id).attr('id'), entryState: entryState, isSummary: 1, color: ""},
				success: function(data){
					// console.log(data);
					// console.log("AUTOSAVE SUCCESS");
				}
			});
		}, 100);
	}
	
	function saveColor(e)
	{
		projBar = e.parentElement.parentElement.parentElement.parentElement;
		
		$.ajax({
			url: 'api.php',
			data: { cmd: "autoSave", projectID: $(projBar).attr('projid'), text: "", id: "", entryState: "", isSummary: 1, color: e.firstChild.className },
			success: function(data){
				// console.log(data);
			}
		});
		
	}
	 
	  $(function () { function moveItems(origin, dest) 
	  {
		$(origin).find(':selected').appendTo(dest);
	  }
	  
	  $('#right').click(function () 
	  {
		moveItems('#sbTwo', '#sbOne');
	  });
 
	  $('#left').on('click', function () 
	  {
		moveItems('#sbOne', '#sbTwo');
	  });
	  });
	  
	  $('#right').click(function () 
	  {
		moveItems('#sbTwo', '#sbOne');
	  });
 
	  $('#left').on('click', function () 
	  {
		moveItems('#sbOne', '#sbTwo');
	  });
	</script>

	<div class="project-wrapper">
		<div class="project-header">
			<h4>Edit Project Name</h4>
		</div>
		<div class="project-fields">	
			<input type="text" id="changeProjectNameInput">
		</div>
	</div>
	
	<div class="project-wrapper">
		<div class="project-header">
		  <?php if(hasRole(Roles::ADMINISTRATOR)){ ?>
			<h4>Project Lead</h4>
		</div>
		<div class="project-fields">
			<select id='sbThree'></select>
			<?php  }  ?>
		</div>
	</div>

	<div class="project-wrapper">
		<div class="project-header">
			<h4>Assignments</h4>
		</div>
		<div class="project-fields">
			<select multiple name='staffListBox' id='sbOne'></select>
		</div>
		<div class="project-fields">
			<p><button type="button" id="left">Assign  &#187;</button><br />
			<button type="button" id="right">&#171; Remove</button></p>
		</div>
		<div class="project-fields">
			<select multiple name='assignedListBox' id='sbTwo'></select>
		</div>
	</div>

	<div class="project-wrapper">
		<div class="project-header">
			<h4>End Date</h4>
		</div>
		<div class="project-fields">
			<input type="text" id="endDatePickerInput">
		</div>
	</div>

	<div class="project-wrapper">
		<div class="project-header">
			<h4>Research & Development</h4>
		</div>
		<div class="project-fields">
			<input type="radio" name = "R&D" id = "trueRadioBtn" value=1>Yes
			<input type="radio" name = "R&D" id = "falseRadioBtn" value=0>No
		</div>
	</div>

	    <button type="button" id="cancelBtn">Cancel</button>
	    <button type="button" id="saveBtn">Save</button>
	</div>
  </div>
  
<!-- Edit Project modal section ends -->

<!-- table headers -->
	<div class="overall-table-headers">
		<div class="overall-arrow-div"></div>
		<div class="overall-color-div"><h5>Health</h5></div>
		<div class="overall-title-div"><h5>Project Title</h5></div>
		<div class="overall-warning-div"><h5>Status</h5></div>
		<div class="overall-edit-div"><h5>Edit</h5></div>
		<div class="overall-lead-div"><h5>Project Lead</h5></div>
		<div class="overall-end-div"><h5>End Date</h5></div>
		<div class="overall-reorder-div"></div>
	</div>
  
<table id="projectTable">
    
    <?php
		$userId = GOD_USER_ID;
		$page = "OVERALL";
		
		$layout = array();

		if(hasRole(Roles::MANAGER))
		{
			$entryData = PTGetProjectsForUserAndDate( $_SESSION["USERID"], $entryDate, true);
		}
		else
		{
			$entryData = PTGetProjectsForUserAndDate( $userId, $entryDate, true); 
		}
		
		foreach ($entryData as $project)
		{
			if ( !in_array($project, $layout))
			{
				array_push($layout, $project);
			}
		}
		
		if(strtotime($currentSunday) == strtotime($entryDate) && hasRole(Roles::ADMINISTRATOR)) //Ensure all projects are displayed for Admins
		{
			$data = PTGetProjectsbyState(true);
		
			foreach ($data as $project)
			{
				if ( !in_array($project->PROJECT_ID, $layout))
				{
					array_push($layout, $project->PROJECT_ID);
				}
			}
		}
			
		if ( !in_array( MISC_PROJECT_ID, $layout ) )
			array_push($layout, MISC_PROJECT_ID );
		
		function searchForId($id, $array, $field) { //Used in createRowOverall
		   foreach ($array as $key => $val) {
			   // echo $val->$field . ' '. gettype($val->$field) . '<br>';
			   // echo $id . ' '. gettype($id) .'<br>';
			   if ($val->$field == $id) {
				   return true;
			   }
		   }
		   return false;
		}
		
		$rowCount = 0;
		foreach ( $layout as $projectId )
		{
			$healthState = PTGetHealthForProjectOnOrBeforeDate( $projectId, $entryDate );
			if ( $healthState === false )
			{
				$healthState = "NULL";
			}
			// echo "<script> console.log(\"project id: ". $projectId ."\"); </script>"; 
			$projectInfo = PTGetProjectInfoById( $projectId );
			if($projectInfo == false)
				continue;
			$projectInfo->HEALTH = $healthState;
			
			// if($projectInfo->PROJECT_ID != MISC_PROJECT_ID ){
				include 'createRowOverall.php';
			// }
			
			$rowCount++;
		}
		
		echo "<tr id=\"test\"></tr>";
	?>
</table>

<script>

	function toggleStatusIcons(index, item) 
	{
		// console.log("status index: " + index);
		if(!document.getElementById('projectWarning' + index) || !document.getElementById('projectStar' + index))
		{
			return;
		}
		if(item == 1) 
		{ //All entries for all users are locked so it's a star!
			document.getElementById('projectWarning' + index).style.display = "none"; //Note ID of project status icons are the project ID rather than the row count like it is on projects page
			document.getElementById('projectStar' + index).style.display = "block";
		}
		else 
		{ //Not all entries are locked so it's a warning sign
			document.getElementById('projectWarning' + index).style.display = "block";
			document.getElementById('projectStar' + index).style.display = "none";
		}
	}
	
	function checkStatusIcons()
	{
		
		// console.log("updating status...");
		$.ajax({
			url: 'api.php', 
			data: {cmd: "statusIcons"}, 
			success: function(data)
			{
				var lockStatusArr = JSON.parse(data);
				
				for (var key in lockStatusArr) 
				{
					var value = lockStatusArr[key];
					toggleStatusIcons(key, value);
				}
			}
		});
		
	}
	document.addEventListener('DOMContentLoaded', checkStatusIcons());	
	setInterval(checkStatusIcons, 5000);


	  // Get the modal
	var modal = document.getElementById("myModal");

	 function getOptions(id)
	 {
		var arr = new Array();
		var x = document.getElementById(id);
		for (var i = 0; i < x.options.length; i++) 
		{
			arr [i] = x.options[i].value;
		}
		return arr;
	}

	// When the user clicks on <span> (x), close the modal
	document.getElementById("cancelBtn").onclick = function() 
	{
	  modal.style.display = "none";
	  $("#sbOne").empty();
	  $("#sbTwo").empty();
	  $("#sbThree").empty();
	}

	document.getElementById("saveBtn").onclick = function() 
	{
		//Returns array of userIDs of specified select box
		function getOptions(id)
		{
			var arr = new Array();
			var x = document.getElementById(id);
			for (var i = 0; i < x.options.length; i++) {
				arr [i] = $(x.options[i]).attr("userid");
			}
			
			// console.log(arr);
			return arr;
		}
		
		var text =  $('#changeProjectNameInput').val();
		text = text.split("\"").join("\\\"");
		
		var endDate = $('#endDatePickerInput').val();
		
		var selector = document.querySelector('input[name="R&D"]:checked'); 
		var RDstate;
		if(selector) 
		{
			// console.log("Selector value: " + selector.value);
			RDstate = selector.value;
		}
		else
		{
			RDstate = 0; //Default R&D state to false if it's not set
			// console.log("no selector");
		}
		 
		$.post('api.php',
		{ 
			cmd: "moveUsers",
			projectID: currentProj, 
			users: getOptions("sbTwo"), 
			unassignedUsers: getOptions("sbOne")
		}, function(data)
		{
			// console.log(data);
			$.post("api.php", 
				{
					cmd: "updateProjectInfo", 
					projectID: currentProj, 
					RDstate: RDstate,
					projectManagerID: $("#sbThree option:selected").attr("userID"),
					PROJECT_NAME: text, 
					endDate: endDate 
				}, function(data) 
				{
					// console.log(data);
					document.getElementById("project-managerName"+currentProj).innerHTML = $("#sbThree option:selected").val();
					
					$("#sbOne").empty();
					$("#sbTwo").empty();
					$("#sbThree").empty();
					modal.style.display = "none";
					location.reload();							
				});		

		}); 
	}

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
	  if (event.target == modal) {
		modal.style.display = "none";
		$("#sbOne").empty();
		$("#sbTwo").empty();
		$("#sbThree").empty();
	  }
	}

	var table = document.getElementById('projectTable');

    table.onclick = function(event) {
		event.preventDefault();
      let target = event.target;
      while (target != this) {
        if (target.tagName == 'I' && ($(event.target).attr('class') == 'fa fa-caret-square-right' || $(event.target).attr('class') == 'fa fa-caret-square-down')) {
          $('.cat'+$(event.target).attr('data-prod-cat')).toggle();
		  $(event.target).toggleClass('fa-caret-square-right fa-caret-square-down');
		  //changeTableHeight();
          return;
        }else if(target.tagName == 'I' && ($(event.target).attr('class') == 'fas fa-edit editIcon')){
			$.post('api.php', { cmd: "getUsers", projectID:  $(event.target).attr('projid'), active: false}, function(data){ //getUsers
				j = JSON.parse(data);

				currentProj = $(event.target).attr('projid');
				var x = document.getElementById("sbOne");
				for(var i = 0; i < j.length; i++){
						var option = document.createElement("option");
						option.text = j[i]["Name"] + ""; 
						$(option).attr("UserID", j[i]["UserID"]);
						x.add(option);
				}
			});
			
			$.post('api.php', { cmd: "getUsers", projectID:  $(event.target).attr('projid'), active: true}, function(data){
				j = JSON.parse(data);

				var x = document.getElementById("sbTwo");
				for(var i = 0; i < j.length; i++){
					var option = document.createElement("option");
					option.text = j[i]["Name"] + "";
					$(option).attr("UserID", j[i]["UserID"]); 
					x.add(option);
				}
			});
			
			//populate change manager box
			var thisProjID = $(event.target).attr('projid');
			$.post('api.php', { cmd: "getManagers", projectID:  $(event.target).attr('projid') }, function(data){ //getManagers
				j = JSON.parse(data);
				// console.log(j);
				
				var x = document.getElementById("sbThree");
				for(var i = 0; i < j.length; i++){
					var option = document.createElement("option");
					option.text = j[i]["Name"] + "";
					$(option).attr("UserID", j[i]["UserID"]);
					x.add(option);
				}
			});
			 
			$("#endDatePickerInput").datepicker({ 
				dateFormat: 'yy-mm-dd'
			});
			
			$.post("api.php",
			  {
				cmd: "getEndDate",
				projectID: thisProjID
			  },
			  function(data) {
				// console.log("" + data);
				  
				$("#endDatePickerInput").datepicker( "setDate" , new Date(data));
			  });
			
			$.post("api.php",
			  {
				cmd: "getRDstate",
				projID: thisProjID
			  },
			  function(data) {
				  // console.log("R&D status: " + data);
				  
				  if(data == 1)
					  $('#trueRadioBtn').prop('checked', true);
				  else if(data == 0)
					  $('#falseRadioBtn').prop('checked', true);
			  });
			
			var modal = document.getElementById("myModal");
			modal.style.display = "block";
			
			$(function () { 
				function moveManager() { 
				manager = $('option:selected', $("#sbThree")).attr('UserID'); //Currently selected manager
				// console.log(manager);
				
				$('#sbOne').find('option[UserID=' + manager + ']').appendTo('#sbTwo'); //Move the manager to the assigned users list 
			  } 
			  
			  $('#sbThree').change(function() {
				  // console.log("manager changed!");
				  moveManager();
			  }); 
			});
		}
        target = target.parentNode;
      }
    }
</script>

</div>

<div id="footer">
<?php
include "footer.php";
?>
</div>
</body>

</html>
<?php  }  ?>
