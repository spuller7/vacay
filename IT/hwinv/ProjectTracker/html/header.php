<?php require_once( dirname( __FILE__ )."/./docroot/authenticate.php" ); ?>

<header>

<script>

	var phpTTL = "<?php echo ini_get('session.gc_maxlifetime'); ?>";
	var expireTime = new Date("<?php
$expireTime = strtotime("+".ini_get('session.gc_maxlifetime')." seconds");
$_SESSION['TIMEOUT'] = $expireTime;
echo date("M d  Y H:i:s",$expireTime);
?>");
	var timeoutWatchCount = 0;

	function timeoutReset()
	{
        	expireTime = new Date();
	        expireTime.setTime(expireTime.getTime() + (phpTTL * 1000));
	}

	function timeoutWatch()
	{
		var expires = expireTime - (new Date());
		if ( (timeoutWatchCount % 15) == 0)
			console.log("auto-logout in " + Math.round(expires / 1000) + " seconds");
		timeoutWatchCount++;
		if (expires <= 0) window.location = "docroot/logout.php";
	}

		$(document).ready(function()
		{
			setInterval('timeoutWatch()',2000);

			$(".fa-unlock-alt").click(function(){
				$.post("api.php", 
				{ 
					cmd: "lockEntries"
				},
				function(data){
					location.reload();
					window.alert(data);
				});
			});
		});
		</script>

<div id="header-wrapper">

<div style="">
	<div id="branding">
	</div>
	
	<div id="navigation-wrapper">
		<div id="navigation">
			<ul>
				<li><span class="navigation-title">QSAI PROJECT TRACKER</span></li>
				<li>| <a href="index.php" id="Projects">Projects</a></li>
				<?php 
				if(  hasRole(Roles::ADMINISTRATOR) || hasRole(Roles::MANAGER)){ 
					  echo "<li>| <a href=\"adminOverall.php\" id=\"Overall\">Overall View</a></li>";
				}
				?>
				<li>| <a href="reports.php" id="Reports">Reports</a></li>
				<?php 
				if(  hasRole(Roles::ADMINISTRATOR) || hasRole(Roles::MANAGER)){
					  echo "<li>| <a href=\"newProject.php\" id=\"New\">New Project</a></li>";
				}
				if(  hasRole(Roles::ADMINISTRATOR) )
				{
					echo "<li>| <a href=\"settings.php\" id=\"Settings\">Settings</a> |</li>";
				}
				?>
		  </ul>
		</div>
	</div>
		
	
	<div id="meta-date-wrapper">
		<div id="meta-date">
			<!-- date picker -->
			<input type="text" id="datepicker">
					   
			<script>
				   
			$("#datepicker").datepicker({
				dateFormat: "yy-mm-dd",
				maxDate: 0,
				onSelect: function(dateText, inst){
					// document.cookie = "date="+dateText;
					
					$.ajax({
						url: 'api.php',
						data: { cmd: "updateDate", date: dateText},
						success: function(data){
							console.log(data);
						}
					});
					location.reload();
				}
			});
			
			// function getCookie(name) {
				// var value = "; " + document.cookie;
				// var parts = value.split("; " + name + "=");
				// if (parts.length == 2) return parts.pop().split(";").shift();
			// }
	
			var date = "<?php echo $_SESSION['DATE']; ?>";
			// var date = getCookie("date");
			// console.log(date);
			$("#datepicker").datepicker("setDate", date);
			$( "#datepicker" ).datepicker( "option", "showButtonPanel", true );
		   
			$( function() {
				$("#datepicker").datepicker();
			});
			
			
			function displayDate(jsDate){
				if (jsDate !== null) {
					var jsDate = $('#datepicker').datepicker('getDate');
					var actualMonth = jsDate.getMonth() + 1;
					var formattedDate = jsDate.getFullYear() +"/"+ actualMonth +"/"+ jsDate.getDate();
					// console.log(formattedDate);
					
				}
			}
			
		   </script>
		</div>
			<!-- lock week -->
		<div id="meta-lock">
			<?php
				echo "<span class=\"meta-icons\" id = \"LockIcon\"><a href=\"#\"><i class=\"fas fa-lock\"></i></a></span>";
				echo "<span class=\"meta-icons\" id = \"UnlockIcon\"><a href=\"#\"><i class=\"fas fa-unlock-alt\"></i></a></span>";
				// echo "<script>console.log(\"". basename($_SERVER["REQUEST_URI"]) ."\");</script>";
			?>
			<script>
			
			function changeIcons(lockStatus) {
				if(lockStatus == 1) {//The week is locked
					document.getElementById("LockIcon").style.display = "block";
					document.getElementById("UnlockIcon").style.display = "none";
				}
				else {
					document.getElementById("LockIcon").style.display = "none";
					document.getElementById("UnlockIcon").style.display = "block";
				}
			}
			 
			function isLocked(){
				lockStatus = "";
				page = "<?php echo basename($_SERVER["REQUEST_URI"])?>";
				$.post("api.php",
				  {
					  cmd: "lockStatus",
					  page: page
				  },
				  function(data){
					lockStatus = data;
					changeIcons(lockStatus);

				  });
			}

			document.addEventListener('DOMContentLoaded', isLocked());
			</script>
		</div>
	</div>
	
	
	
	<script>
		$(document).ready(function(){
			$.post("api.php", { cmd: "getName" }, function(data){ 
				$("#welcomeDiv").html("Hello, " + data + "");
			});
		});
	</script>
	
					    
					 
	<div id="welcome-meta-wrapper">
		<div id="meta">
		<span class="meta-icons"><a href="docroot/logout.php"><i class="fas fa-sign-out-alt"></i></a></span>
		</div>
		<div id="welcomeDiv">
		</div>
	</div>
	
	
</div>
</div>
</header>
