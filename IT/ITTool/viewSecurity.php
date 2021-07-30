<?php require('auth.php');?><html>
	<head>
	<title>IT Alarms</title>
	<meta charset="UTF-8">
	
	<script src='js/jquery-1.11.1.min.js'></script>
	<script src='js/itCmds.js'></script>
	<script src='CameraList.php'></script>
	<link href='css/base.css' rel='stylesheet' media='screen'/>		
	<style>
		.white_content {
		  display: none;
		  position: absolute;
		  top: 0;
		  left: 0;
		  padding: 16px;
		  border: 16px solid orange;
		  background-color: white;
		  z-index: 1002;
		  overflow: hidden;
		}	
	</style>
	<script>

	var cycleIdx = 1;
	var cycleID = 0;
	var inCycle = false;
	var cycleCount = 0;
	var flop = true;
	
	function CycleFunc()
	{
		cycleCount++;
		if ( cycleCount % 5 == 0 && inCycle )
		{
			cycleIdx = (cycleIdx + 1) % cameraList.length;
			if ( cycleIdx == 0 )
				cycleIdx = 1;
			showCamera( cameraList[cycleIdx] );
		}
		var stride = (cycleCount /(cameraList.length-1))|0;
		if ( (stride % 2) == 1 )
		{
			var idx = cycleCount % (cameraList.length-1);
			updateStill(idx+1);
		}
		else
		{
			var idx = (Math.random()*(cameraList.length-1))|0;
			updateStill(idx+1);
		}
	}
	
	function showCamera( cam )
	{
		$("#CAMERA").attr('src',"https://fatbot.internal.quantumsignal.com:8083/livestream/" + cam );
		$("#CAMERA").css("display","block");
	}
	
	function hideCamera( cam )
	{
		$("#CAMERA").attr('src',"");
		$("#CAMERA").css("display","none");
		inCycle = false;
	}
	
	function setCamera( cam, idx )
	{
		if ( idx == 0 )
		{
			inCycle =true;
		}
		else
		{
			inCycle = false;
			showCamera(cam);
			cycleIdx = idx;
		}
	}
	
	function pad(n, width, z) {
	  z = z || '0';
	  n = n + '';
	  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
	}

	var lastUpdate = -1;
	
	function updateStill( idx )
	{
		if ( idx != 0 )
		{
			$("#camera" + idx ).attr("src", "https://fatbot.internal.quantumsignal.com:8083/snapshot/" + cameraList[idx] + "?resolution=320x240&compression=75&rand="+Date.now() );
			var dt = new Date();
			$("#ts" + idx ).text(pad(dt.getHours(), 2) + ":" + pad(dt.getMinutes(), 2) + ":" + pad(dt.getSeconds(), 2) );
			$("#cell" + lastUpdate).css('background-color', "#CCCCCC");
			$("#cell" + idx).css('background-color', "#8080FF");
			lastUpdate = idx;
		}

	}
	
	$( document ).ready( function() {
		var result = "<table width=100%>";
		cameraList[0] = "Cycle Cameras";
		result += "<tr>";
		for ( var i = 1; i < cameraList.length; i ++ )
		{
			if ( (i-1) != 0 && (i-1) % 8 == 0 ) 
				result += "</tr><tr>";
			
			result += "<td id='cell" + i + "' width=12% style='vertical-align:top;'>";
			result += "<div style='width: 100%;text-align:center;' onclick='setCamera(\"" + cameraList[i] + "\", "+(i)+"); return false;'><small><u>"+cameraList[i]+"</u></small><br/><img width=100% id='camera" + i + "'/><br/><small id='ts" + i + "'>-</small></div>";
			result += "</td>";
		}
		result += "</tr></table>";
		$("#IMAGES").html(result);
		UpdateBanner();
		/*for ( var i = 0; i < cameraList.length; i ++ )
		{
			updateStill(i);
		}*/	
		cycleID = setInterval( CycleFunc, 250 );
	})
		
	</script>	
	</head>
<body>
<?php
	$tab='SECURITY';
	require('header.php');
?>
<br/><br/><br/>
<div id='IMAGES'></div>
<img class="white_content" onclick="hideCamera(); return false;" id='CAMERA'/>
</body></html>
