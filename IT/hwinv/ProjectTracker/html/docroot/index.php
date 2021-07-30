<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once( dirname( __FILE__ )."/./authenticate.php" );
?>

<html>

<head>
	<title>Registration Form</title>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

<header>
	<div id="branding">
	</div>
	<div id="pageheader-wrapper">
		<div id="page-header">
			<h1>Project Tracker</h1>
		</div>
		<div id="navigation">
			<ul>
				<li><a href="index.php">Projects</a></li>
				<li>&nbsp;|&nbsp;</li>
				<li><a href="reports.php">Reports</a></li>
				<li>&nbsp;|&nbsp;</li>
				<li><a href="#">Settings</a></li>
			</ul>
		</div>
	</div>
	<div id="meta-wrapper">
		<div id="meta-date">
			<p>date picker</p>
		</div>
		<div id="meta">
			<p>Log Out</p>
		</div>
	</div>
</header>

<div id="wrapper">

<div id="tableDiv">
	<table id="effortTable" style="width:15%; float:left" border="1"></table>
       
<table id="projectTable" style="width:85%; float:left">
<!-- <table id="projectTable"> -->
    
    <?php
			include 'config.php';
			$Employee_id = 1;
			
			$conn = new mysqli($servername, $username, $password, $db);

			if ($conn->connect_error) {
			    die("Connection failed: " . $conn->connect_error);
			} 
			
			$sql = "SELECT PROJECT_ID, PROJECT_NAME FROM PROJECTS WHERE PROJECT_ID IN (SELECT PROJECT_ID FROM PROJECT_ASSIGNMENT WHERE EMPLOYEE_ID = ". $Employee_id. ");";
			$result = $conn->query($sql);
			
			$count = 0;
			
			if ($result->num_rows > 0) {
			    while($projectList = $result->fetch_assoc()) {
			    	$entrySql = "SELECT EFFORT, PREVIOUS_GOALS, ACCOMPLISHMENTS, ISSUES, UPCOMING_GOALS FROM PROJECT_ENTRIES WHERE PROJECT_ID=". $projectList["PROJECT_ID"]. " AND EMPLOYEE_ID=". $Employee_id. " AND ENTRY_STATE='PARTIAL' ORDER BY ENTRY_DATE DESC LIMIT 1;"; //ADD AND FROM THIS WEEK
			    	$entryQuery = $conn->query($entrySql);
			    	$entryData = $entryQuery->fetch_assoc();
			    	echo "<tr>";
			        echo "<th colspan=5><i href=\"#\" class=\"fa fa-caret-square-right\" data-prod-cat=\"". $count. "\"></i> ". $projectList["PROJECT_NAME"]. "</th>";
			        echo "</tr>";
					echo "<tr class=\"cat". $count. "\" style=\"display:none\">";
					echo "<td class=\"project-headers\">Utilization</td>";
					echo "<td class=\"project-headers\">Previous Goals</td>"; 
					echo "<td class=\"project-headers\">Accomplishments</td>";
					echo "<td class=\"project-headers\">Issues</td>";
					echo "<td class=\"project-headers\">Upcoming Goals</td>";
					echo "</tr>";
			        echo "<tr class=\"cat". $count. "\" style=\"display:none\">";
				    echo "<td><textarea id=\"UtilizationResponse". $count. "\" style=\"width:100%\">". $entryData["EFFORT"]. "</textarea></td>";
    				echo "<td><textarea id=\"PreviousGoalsResponse". $count. "\" style=\"width:100%\">". $entryData["PREVIOUS_GOALS"]. "</textarea></td>";
    				echo "<td><textarea id=\"AccomplishmentsResponse". $count. "\" style=\"width:100%\">". $entryData["ACCOMPLISHMENTS"]. "</textarea></td>";
    				echo "<td><textarea id=\"IssuesResponse". $count. "\" style=\"width:100%\">". $entryData["ISSUES"]. "</textarea></td>";
    				echo "<td><textarea id=\"UpcomingGoalsResponse". $count. "\" style=\"width:100%\">". $entryData["UPCOMING_GOALS"]. "</textarea></td>";
  					echo "</tr>";
					$count = $count + 1;
				}
			} else {
			    echo "0 results";
			}
		?>
</table>

<script>
	var table = document.getElementById('projectTable');
	var rows = table.rows.length;
	
	//Slider
	table = document.getElementById('effortTable');
	for(var i=0; i<rows/3; i+=1){
		row = table.insertRow(-1);
		var cell = row.insertCell(0);
		cell.innerHTML = "hello";
	}
</script>

<script>
    var table = document.getElementById('projectTable');

    table.onclick = function(event) {
		event.preventDefault();
      let target = event.target;
      while (target != this) {
        if (target.tagName == 'I') {
          $('.cat'+$(event.target).attr('data-prod-cat')).toggle();
		  $(event.target).toggleClass('fa-caret-square-right fa-caret-square-down');
		  changeTableHeight();
          return;
        }
        target = target.parentNode;
      }
    }
	//Slider
	function changeTableHeight(){
		var numTh = 0;
		$('#projectTable th').each(function() {

			numTh+=1;

		});
		var height = $("#projectTable").height()/numTh;
		$('#effortTable tr').each(function() {

			$(this).css("height", height + "px");

		});
		
	}
	
  </script>
  
  </div>

<p>
<?php
$pListSql = "SELECT PROJECT_ID, PROJECT_NAME FROM PROJECTS WHERE PROJECT_ID NOT IN (SELECT PROJECT_ID FROM PROJECT_ASSIGNMENT WHERE EMPLOYEE_ID = ". $Employee_id. ") AND IS_ACTIVE=TRUE;";
$pListResults = $conn->query($pListSql);
echo "<select id=\"addProjectsList\">";
echo "<option selected=\"selected\" disabled>--select one--</option>";
while($pListData = $pListResults->fetch_assoc()){
        echo "<option value=\"". $pListData["PROJECT_ID"]. "\">". $pListData["PROJECT_NAME"]. "</option>";
}
echo "</select>";
?>
<button id="addProjectButton">Select</button>
<script>
$(document).ready(function(){
var count = <?php echo $count; ?>;
$("#addProjectButton").click(function() {
console.log("click");

$.post("addRow.php",
  {
    project: $('#addProjectsList').val(),
    employee: <?php echo $Employee_id; ?>
  },
  function(data, status){
  var j = JSON.parse(data);

    var table = document.getElementById("projectTable");
    var row = table.insertRow(-1);
    row.innerHTML = "<th colspan=5 ><i href=\"#\" class=\"fa fa-caret-square-right\" data-prod-cat=\"" + count + "\"></i> "+j["projectName"]+"</th>";
	
	var row = table.insertRow(-1);
	var cell1 = row.insertCell(0);
	var cell2 = row.insertCell(1);
	var cell3 = row.insertCell(2);
	var cell4 = row.insertCell(3);
	var cell5 = row.insertCell(4);
	
	//row.innerHTML = "<tr class=\"cat"+ count + "\" style=\"display:none\">";
	cell1.outerHTML = "<td class=\"project-headers\">Utilization</td>";
	cell2.outerHTML = "<td class=\"project-headers\">Previous Goals</td>"; 
	cell3.outerHTML = "<td class=\"project-headers\">Accomplishments</td>";
	cell4.outerHTML = "<td class=\"project-headers\">Issues</td>";
	cell5.outerHTML = "<td class=\"project-headers\">Upcoming Goals</td>";
	
	row.outerHTML = "<tr class=\"cat"+ count + "\" style=\"display:none\">" + row.outerHTML.substring(4);
	
	var row = table.insertRow(-1);
	var cell1 = row.insertCell(0);
	var cell2 = row.insertCell(1);
	var cell3 = row.insertCell(2);
	var cell4 = row.insertCell(3);
	var cell5 = row.insertCell(4);
	
	if(!(j["effort"] == null)){
		cell1.innerHTML = "<textarea style=\"width:100%\">" + j["effort"] + "</textarea>";
	}else{
		cell1.innerHTML = "<textarea style=\"width:100%\"></textarea>";
	}
	if(!(j["previousGoals"] == null)){
		cell2.innerHTML = "<textarea style=\"width:100%\">" + j["previousGoals"] + "</textarea>";
	}else{
		cell2.innerHTML = "<textarea style=\"width:100%\"></textarea>";
	}
	if(!(j["accomplishments"] == null)){
	cell3.innerHTML = "<textarea style=\"width:100%\">" + j["accomplishments"] + "</textarea>";
	}else{
		cell3.innerHTML = "<textarea style=\"width:100%\"></textarea>";
	}
	if(!(j["issues"] == null)){
	cell4.innerHTML = "<textarea style=\"width:100%\">" + j["issues"] + "</textarea>";
	}else{
		cell4.innerHTML = "<textarea style=\"width:100%\"></textarea>";
	}
	if(!(j["upcomingGoals"] == null)){
	cell5.innerHTML = "<textarea style=\"width:100%\">" + j["upcomingGoals"] + "</textarea>";
	}else{
		cell5.innerHTML = "<textarea style=\"width:100%\"></textarea>";
	}

    row.outerHTML = "<tr class=\"cat"+ count + "\" style=\"display:none\">" + row.outerHTML.substring(4);
    count++;
  });

  $("#addProjectsList option[value=\"" + $('#addProjectsList').val() + "\"]").remove();
  
  document.getElementById("effortTable").insertRow(-1).insertCell(0).innerHTML = "hello";
});
});
</script>
</p>
<button>Submit</button>

</div><!-- wrapper -->

<footer class="footer">
	<div class="footer-column">
		<h3>Message from the CEO</h3>
		<h4>Our greatest weakness lies in giving up. The most certain way to succeed is always to try just one more time.</h4>
		<p>-Thomas Edison</p>
	</div>
	<div class="footer-column">
		<h3>Helpful Links</h3>
		<h5><a href="https://wiki.internal.quantumsignal.com/index.php/Human_Resources">Human Resources</a></h5>
		<h5><a href="https://wiki.internal.quantumsignal.com/index.php/Software_Resources">Software Resources</a></h5>
		<h5><a href="https://wiki.internal.quantumsignal.com/index.php/Mechanical_and_Electrical_Resources">Mechanical and Electrical Resources</a></h5>
		<h5><a href="https://wiki.internal.quantumsignal.com/index.php/Phone_List">Employee Phone List</a></h5>
	</div>
	<div class="footer-column">
		<h3>More Helpful Info</h3>
		<h5>Coming soon!</h5>
	</div>	
</footer>

</body>
</html>
