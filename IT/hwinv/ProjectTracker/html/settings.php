<?php
require_once( dirname( __FILE__ )."/./docroot/authenticate.php" );
require_once( dirname( __FILE__ )."/projTrackDB.php" );
?>
<html>

<head>
	<title>Project Tracker | Settings</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
	<link rel="icon" href="images/favicon.ico" type="image/x-icon">
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>

<!--script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script-->
<script>
$(function(){
  $("#header").load("header.php"); 
  $("#footer").load("footer.php"); 
});
</script>

<div id="header"></div>
<div id="wrapper">
	<h1>SETTINGS</h1>

<?php if( hasRole( Roles::ADMINISTRATOR))
{ ?>
	
	<div class="project-wrapper">
		<div class="project-header">
			<h4>Archive Projects</h4>
		</div>
		<div class="project-fields">

	<?php
		$data = PTGetProjectsbyState(TRUE);

		echo "<select multiple name='activeProjectDropDown' id='activeProjects'>";
		for($i = 0; $i < sizeof($data); $i++)
		{
			echo "<option value='" . $data[$i]->PROJECT_ID. "'>" . $data[$i]->PROJECT_NAME . "</option>";
		}
		echo "</select>";
		
	?>
	
	<script>
	  $(function () { function moveItems(origin, dest) {
	  $(origin).find(':selected').appendTo(dest);
	  }
	  
	  function callmoveItems(id, swap1, swap2) 
	  {
		$(id).on('click', function () {
			moveItems(swap1, swap2);
		});
	  }
	  
	  //Archive/Activate Projects Buttons
	  callmoveItems('#activate','#archivedProjects','#activeProjects');
	  callmoveItems('#archive','#activeProjects','#archivedProjects');
	  
	  //User/Manager Buttons
	  callmoveItems('#userToManager','#usersList','#managersList');
	  callmoveItems('#managerToUser','#managersList','#usersList');
	  
	  //Manager/Admin Buttons
	  callmoveItems('#managerToAdmin','#managersList','#adminsList');
	  callmoveItems('#adminToManager','#adminsList','#managersList');

	   });
	</script>
		</div><!-- project-assignments -->
		<div class="project-buttons">
			<p><button type="button" id="archive">Archive &#187;</button><br />
			<button type="button" id="activate">&#171; Activate</button></p>
		</div>
		<div class="project-fields">
	  
	<?php  
		$data = PTGetProjectsbyState(FALSE);

		echo "<select multiple name='archivedProjectDropDown' id='archivedProjects'>";
		for($i = 0; $i < sizeof($data); $i++)
		{
			echo "<option value='" . $data[$i]->PROJECT_ID. "'>" . $data[$i]->PROJECT_NAME . "</option>";
		}
		echo "</select>";
	?>
	
	<script>
	  
		function getOptions(id){
			var arr = new Array();
			var x = document.getElementById(id);
			for (var i = 0; i < x.options.length; i++) {
				arr [i] = x.options[i].value;
				console.log(x.options[i]);
			}
			console.log(arr);
			return arr;
		}
	  
		$(document).ready(function(){
			$("#archive").click(function(){
				$.post("api.php", 
				{ 
					cmd: "archiveProjects",
					archivedProjects: getOptions("archivedProjects") 
				},
				function(data){console.log(data)});
			
			});
		});
		
		$(document).ready(function(){
			$("#activate").click(function(){
				$.post("api.php", 
				{ 
					cmd: "activateProjects",
					activeProjects: getOptions("activeProjects")
				},
				function(data){console.log(data)});
			});
		});
	</script>
		</div>
	</div>
	
	<div class="project-wrapper">
		<div class="project-header">
			<h4>Unlock User Content</h4>
		</div>
		<div class="project-fields">
	
    <?php
		$data = PTGetActiveUsersData();
		// echo '<pre>'; print_r($data); echo '</pre>';
		echo "<select name='staffListBox' id='sbOne'>";
	
		for($i = 0; $i < sizeof($data); $i++)
		{
			echo "<option value='" . $data[$i]->UserID . "'>" . $data[$i]->Name . "</option>";
		}
		
		echo "</select>";
	?>
		</div>
		<div class="project-fields">
		<span style="margin-left:20px;">Date: </span><input type="text" id="unlockDatePicker">
		<button type="button" onclick="unlockEntry()">Unlock</button>
		
		 <script>
		
		 $( function() {
			$("#unlockDatePicker").datepicker({
				dateFormat: "yy-mm-dd",
				maxDate: 0
			});
			$("#unlockDatePicker").datepicker( "setDate" , "-0d" );
		});
		
		function unlockEntry(){
			var jsDate = $('#unlockDatePicker').datepicker('getDate');
			if (jsDate != null) {
				var actualMonth = jsDate.getMonth()+1;
				var formattedDate = jsDate.getFullYear() +"/"+ actualMonth +"/"+ jsDate.getDate();
				console.log(formattedDate);
				console.log(document.getElementById("sbOne").value);
				
				$.post("api.php", //unlockEntries
				{ 
					cmd: "unlockEntries",
					UserID: $('option:selected', $("#sbOne")).attr('value')
				},
				function(data){
					window.alert(data);
				});
				
			}
		}
		
		</script>
		</div>
	</div>
	
	<div class="project-wrapper">
		<div class="project-header">
			<h4>Manage Roles</h4>
		</div>
		
		<div class="project-assignments">
		<h5>Users</h5>
		<?php
		$data = PTGetUsersByRole("USER");
		
		echo "<select multiple name='usersDropDown' id='usersList'>";
		for($i = 0; $i < sizeof($data); $i++)
		{
			echo "<option value='" . $data[$i]->UserID. "'>" . $data[$i]->Name . "</option>"; //Need to add User name to function
		}
		echo "</select>";
		?>
		</div>
		<div class="project-buttons">
		  <p><button type="button" id="userToManager">&#187;</button><br />
		  <button type="button" id="managerToUser">&#171;</button></p>
		</div>
		<div class="project-assignments">
		<h5>Leads</h5>
		<?php
		$data = PTGetUsersByRole("MANAGER");
		
		echo "<select multiple name='managersDropDown' id='managersList'>";
		for($i = 0; $i < sizeof($data); $i++)
		{
			echo "<option value='" . $data[$i]->UserID. "'>" . $data[$i]->Name . "</option>";
		}
		echo "</select>";
		?>
		</div>
		<div class="project-buttons">
		  <p><button type="button" id="managerToAdmin">&#187;</button><br />
		  <button type="button" id="adminToManager">&#171;</button></p>
		</div>
		<div class="project-assignments">
		<h5>Administrators</h5>
		<?php
		$data = PTGetUsersByRole("ADMINISTRATOR");
		
		echo "<select multiple name='adminsDropDown' id='adminsList'>";
		for($i = 0; $i < sizeof($data); $i++)
		{
			echo "<option value='" . $data[$i]->UserID. "'>" . $data[$i]->Name . "</option>";
		}
		echo "</select>";
		?>
		</div>
		
		<script>
			$(document).ready(function(){
				$("#userToManager").click(function(){
					$.post("api.php", 
					{ 
						cmd: "changeUserRoles",
						usersList: getOptions("managersList"), 
						role: "MANAGER" 
					},
					function(data){console.log(data)});
				});
			});
			
			$(document).ready(function(){
				$("#managerToUser").click(function(){
					$.post("api.php", //changeUserRoles
					{ 
						cmd: "changeUserRoles",
						usersList: getOptions("usersList"), 
						role: "USER"
					},
					function(data){console.log(data)});
				});
			});
			
			$(document).ready(function(){
				$("#managerToAdmin").click(function(){
					$.post("api.php", //changeUserRoles
					{ 
						cmd: "changeUserRoles",
						usersList: getOptions("adminsList"), 
						role: "ADMINISTRATOR"
					},
					function(data){console.log(data)});
				});
			});
			
			$(document).ready(function(){
				$("#adminToManager").click(function(){
					$.post("api.php", //changeUserRoles
					{ 
						cmd: "changeUserRoles",
						usersList: getOptions("managersList"), 
						role: "MANAGER"
					},
					function(data){console.log(data)});
				});
			});
		</script>
	</div>
	
	<div class="project-wrapper">
		<div class="project-header">
			<h4>Change User Name</h4>
		</div>
		<div class="project-fields">
	
		<?php
			$data = PTGetActiveUsersData();
			echo "<select name='staffListBox' id='changeNameSelect'>";
		
			for($i = 0; $i < sizeof($data); $i++)
			{
				echo "<option value='" . $data[$i]->UserID . "'>" . $data[$i]->Name . "</option>";
			}
			
			echo "</select>";
		?>
		
		<span style="margin-left:20px;">New Name: </span><input type="text" id="changeNameTextBox">
		<button type="button" id = "saveBtn">Save</button>
		
		<script>
		
		$(document).ready(function(){
			
			$("#saveBtn").click(function(){
			var newName = document.getElementById('changeNameTextBox').value;
				console.log(newName);
				if (newName != null) 
				{
					$.post("api.php",
					{ 
						cmd: "changeName",
						UserID: $('option:selected', $("#changeNameSelect")).attr('value'),
						newName: newName
					},
					function(data){
						window.alert(data);
						location.reload();
					});
				}
			});
		});
		</script>
		
		</div>
	</div>
	
<?php }
?>

</div>

<div id="footer"></div>
</body>

</html>