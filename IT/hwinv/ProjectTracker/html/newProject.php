<?php
require_once( dirname( __FILE__ )."/./docroot/authenticate.php" );
require_once( dirname( __FILE__ )."/projTrackDB.php" );

if( hasRole( Roles::ADMINISTRATOR) || hasRole( Roles::MANAGER ))
{
?>
<html>

<head>
	<title>Project Tracker | New Project</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>
<!-- <div id="datepicker"></div>
 
<script>
$( "#datepicker" ).datepicker();
</script> -->
<!-- This duplicate reference prevents the datepicker from working right-->
<!--script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script-->



<script>
$(function(){
  $("#header").load("header.php"); 
  $("#footer").load("footer.php"); 
});
</script>

<div id="header"></div>

  <div id="wrapper">
    <h1>Create New Project </h1>
    
	<div class="project-wrapper">
		<div class="project-wrapper-left">
			<div class="project-header">
				<h4>Project Title</h4>
			</div>
			<div class="project-fields">
				<input type = "text" tabindex=1 id = "projectTitle"/>
			</div>
		</div>
	</div>
	
	<div class="project-wrapper">
		<div class="project-wrapper-left">
			<div class="project-header">
				<h4>Start Date</h4>
			</div>
			<div class="project-fields">
				<input type="text" tabindex=2 id="startDatePicker"> <!--defaults to today's date -->
			</div>
		</div>
		<div class="project-wrapper-right">
			<div class="project-header">
				<h4>End Date</h4>
				</div>
			<div class="project-fields">
				<input type="text" tabindex=3 id="endDatePicker"> <!-- defaults to today's date -->
			</div>
		</div>
	</div>
	
	<script>
	  
	
	$("#startDatePicker").datepicker({
		dateFormat: "yy-mm-dd",
		minDate: 0
	});
	$("#startDatePicker").datepicker( "setDate" , "-0d" );
	
	$("#endDatePicker").datepicker({
		dateFormat: "yy-mm-dd",
		minDate: 0
	});
	$("#endDatePicker").datepicker( "setDate" , "+1d" );
	  
	  //Move manager to assigned users box
	  $(function () { function moveManager() { 
		manager = $('option:selected', $("#manager")).attr('value'); //Currently selected manager
		console.log(manager);
		
		$('#sbOne').find('option[value=' + manager + ']').appendTo('#sbTwo'); //Move the manager to the assigned users list 
	  } 
	  
	  $('#manager').change(function() {
		  moveManager();
	  }); 
	  });
	  
	  //Moving users between unassigned and assigned boxes
	  $(function () { function moveItems(origin, dest) {
	  $(origin).find(':selected').appendTo(dest);
	  }
	  
	  $('#right').click(function () {
	  moveItems('#sbTwo', '#sbOne');
	  });
 
	  $('#left').on('click', function () {
	  moveItems('#sbOne', '#sbTwo');
	  });
	  });
	  
	  $('#right').click(function () {
	  moveItems('#sbTwo', '#sbOne');
	  });
 
	  $('#left').on('click', function () {
	  moveItems('#sbOne', '#sbTwo');
	  });
	  
	</script>
	
	<div class="project-wrapper">
		<div class="project-wrapper-left">
			<div class="project-header">
				<h4>Lead</h4>
			</div>
			<div class="project-fields">
			  <?php
				$data = PTGetAllManagers();
				
				echo "<select name='managerDropDown' id='manager' tabindex=4>";
				for($i = 0; $i < sizeof($data); $i++)
				{
					echo "<option value='" . $data[$i]->UserID. "'>" . $data[$i]->Name . "</option>";
				}
				echo "</select>";
				
			  ?>
			</div>
		</div>
		<div class="project-wrapper-right">
			<div class="project-header">
			<h4>Assignments</h4>
			</div>
			<div class="project-fields">
				<div class="project-assignments">
					<?php
						$data = PTGetActiveUsersData();

						echo "<select multiple name='staffListBox' id='sbOne' tabindex=5>";
						for($i = 0; $i < sizeof($data); $i++)
						{
							echo "<option value='" . $data[$i]->UserID. "'>" . $data[$i]->Name . "</option>";
						}
						echo "</select>";
					?>
				</div>
				<div class="project-buttons">
				  <button type="button" id="left" tabindex=6>Assign &#187</button><br />
				  <button type="button" id="right">&#171 Remove</button>
				</div>
				<div class="project-assignments">
				  <select multiple name='assignedListBox' id='sbTwo'> 
				  </select>
				</div>
			</div>
		</div>
	</div>
	
	<div class="project-wrapper">
		<div class="project-wrapper-left">
			<div class="project-header">
				<h4>Research & Development</h4>
			</div>
			<div class="project-fields">
				<input type="radio" name = "R&D" id = "trueRadioBtn" value=1 tabindex=7>Yes
				<input type="radio" name = "R&D" id = "falseRadioBtn" value=0 tabindex=8>No
			</div>
		</div>
	</div>
	

			  <button type="button" id="saveBtn" tabindex=9>Save</button>
			  
			  <script>
			  
			  function getOptions()
			  {
				  var arr = new Array();
				  var x = document.getElementById("sbTwo");
				  var i = 0;
				  while (i < x.options.length) {
						arr [i] = x.options[i].value;
						i++;
					}
				
				//Check if the manager ID is in the array already and if it's not add it
				var manager = document.getElementById("manager").value;
				if(!(arr.includes(manager)))
					arr[i] = manager;
				console.log(arr);
				return arr;
			  }
			  
			  function formatDate(jsDate)
			  {
				var actualMonth = jsDate.getMonth() + 1;
				var x = jsDate.getFullYear() +"-"+ actualMonth +"-"+ jsDate.getDate();
				console.log(x);
				return x;
			  }
			  
			  function getRDBtnState() 
			  {
				var selector = document.querySelector('input[name="R&D"]:checked'); 
				var RDstate;
				if(selector) 
				{
					console.log("Selector value: " + selector.value);
					RDstate = selector.value;
				}
				else
					RDstate = 0; //default to false if option isn't selected
				
				return RDstate;
			  }
			  
				$(document).ready(function()
				{
					$("#saveBtn").click(function()
					{
						formatDate( $('#startDatePicker').datepicker('getDate') );

						$.post("api.php",
						{
							cmd: "newProjDatabase",
							name: $('#projectTitle').val().split("\"").join("\\\""),
							startDate: formatDate( $('#startDatePicker').datepicker('getDate') ),
							endDate: formatDate( $('#endDatePicker').datepicker('getDate') ),
							manager: $('option:selected', $("#manager")).attr('value'),
							users: getOptions(),
							is_RD: getRDBtnState()
						},
						function(data){
							window.alert("Project Added");
						});
					});
				});
				
				
			  </script>
		
  </div><!-- wrapper -->

<div id="footer"></div>
</body>


</html>

<?php 
} 
?>