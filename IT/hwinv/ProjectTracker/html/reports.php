<?php
require_once( dirname( __FILE__ )."/./docroot/authenticate.php" );
require_once( dirname( __FILE__ )."/projTrackDB.php" );
?>
<html>

<head>
	<title>Project Tracker | Reports</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="styles.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
  <script src="//code.jquery.com/jquery-1.12.4.js"></script>
  <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>
<body>

<script>
$(function(){
  $("#header").load("header.php"); 
  $("#footer").load("footer.php"); 
});
</script>

<div id="header"></div>

<div id="wrapper">

<h1>Reports</h1>

	<div class="project-wrapper">
		<div class="project-wrapper-left">
			<div class="project-header">
				<h4>Download Reports</h4>
			</div>
			<div class="project-fields-vertical">
				<div class="project-fields-vertical">
					<select name="generate-reports" id="generateReportSelect">
						<option value="utilization-report">Utilization Report</option>
						<option value="hours-report">Hours Report</option>
					</select>
				</div>
				<div class="project-fields-vertical">
					Start Date<br />
					<input type="text" id="startDatePicker">
					<script>
			
					 $( function() {
						$("#startDatePicker").datepicker();
					});
					</script>
				</div>
				<div class="project-fields-vertical">
					End Date<br />
					<input type="text" id="endDatePicker">
					<script> //Can I make it so the end date has to be later than the start date?
			
					 $( function() {
						$("#endDatePicker").datepicker();
					});
			
					</script>
				</div>
				<div class="project-fields-vertical">
					<button type="button" id="generateReportBtn">Generate Report</button>
				</div>
			</div>
		</div>
		
		<script>
		
		$("#startDatePicker").datepicker({
			dateFormat: "yy-mm-dd",
			maxDate: 0
		});
		$("#startDatePicker").datepicker( "setDate" , "-0d" );
		
		$("#endDatePicker").datepicker({
			dateFormat: "yy-mm-dd",
			maxDate: 0
		});
		$("#endDatePicker").datepicker( "setDate" , "-0d" );

		function formatDate(jsDate)
		  {
			var actualMonth = jsDate.getMonth() + 1;
			var x = jsDate.getFullYear() +"-"+ actualMonth +"-"+ jsDate.getDate();
			return x;
		  }
		
		$(document).ready(function()
		{
			$("#generateReportBtn").click(function()
			{
				startDate = formatDate( $('#startDatePicker').datepicker('getDate') );
				endDate = formatDate( $('#endDatePicker').datepicker('getDate') );
				reportType = $('option:selected', $("#generateReportSelect")).attr('value');
				
				$.post("api.php",
				{
					cmd: "exportReport",
					startDate: formatDate( $('#startDatePicker').datepicker('getDate') ),
					endDate: formatDate( $('#endDatePicker').datepicker('getDate') ),
					reportType: $('option:selected', $("#generateReportSelect")).attr('value'),
				},
				function(data)
				{
					var blob = new Blob([data], { type: 'text/csv' });
					var link = window.document.createElement('a');
					link.href = window.URL.createObjectURL(blob);
					link.download =  reportType + startDate + '--' + endDate +'.csv'; 
					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);
				});
			
			});
		});
		</script>


		<div class="project-wrapper-right">
			<div class="project-header" style="width:100%;">
				<h4 style="margin-bottom:10px;"><span class="project-warning"><i class="fas fa-exclamation-triangle"></i></span>Unlocked Users</h4>
			</div>
			
			<?php
			$date = PTGetSundayDateString($_SESSION['DATE']);
			$userData = PTGetActiveUsersData();
			
			foreach ($userData as $user)
			{
				if(!(PTAreProjectEntriesLockedForUserAndDate($user->UserID, $date)))
				{
					echo "<div class=\"unlocked-users-names\">".$user->Name."</div>";
				}
			}
			?>
		</div>
	</div>
</div>


<div id="footer"></div>

</body>

</html>
