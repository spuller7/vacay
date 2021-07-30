<?php
//////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2008, Quantum Signal, LLC
// 
// This data and information is proprietary to, and a valuable trade 
// secret of, Quantum Signal, LLC.  It is given in confidence by Quantum 
// Signal, LLC. Its use, duplication, or disclosure is subject to the
// restrictions set forth in the License Agreement under which it has 
// been distributed.
//
//////////////////////////////////////////////////////////////////////

require_once( dirname( __FILE__ )."/./authenticate.php" );
require_once( dirname( __FILE__ )."/./Tracker/TrackerManager.php" );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd"> 
<html>
<head>
<script type="text/javascript" src="utils.js"></script>
<script>
var globalDate = '<?php echo date("m/d/Y"); ?>';

var phpTTL = "<?php echo ini_get('session.gc_maxlifetime'); ?>";
var expireTime = new Date("<?php
$expireTime = strtotime("+".ini_get('session.gc_maxlifetime')." seconds");
$_SESSION['TIMEOUT'] = $expireTime;
echo date("M d  Y H:i:s",$expireTime); 
?>");

function timeoutReset()
{
	expireTime = new Date();
	expireTime.setTime(expireTime.getTime() + (phpTTL * 1000));
}

function timeoutWatch()
{
	var currTime = new Date();
	var expires = expireTime - currTime;
	var finishHour = expireTime.getHours();
	var finishMinute = expireTime.getMinutes();
	var ampm = "AM";
	if (finishHour > 11)
	{
		ampm = "PM";
		if (finishHour > 12) finishHour -= 12;
	}
	if (finishHour == 0) finishHour = 12;
	var finishTime = parseInt(finishHour) + ":" + zeroPad(finishMinute,2) + ampm;
	//you can't reference frames by name in Chrome??
	if(frames[1].name == 'menubar')
	{
		frames[1].document.getElementById('statusfooter').innerHTML = "Session expires at " + finishTime + "<br/>(in " + Math.round(expires / 1000) + " seconds)";
	}
	if (expires <= 0) window.location = "timeout.php";
}

window.onload=function()
{
	setInterval('timeoutWatch()',2000);
}

</script>
</head>
<frameset framespacing=0 frameborder=0 rows="50,*">
	<frame noresize scrolling=no id='title' name='title' src="titleBar.php"/>
	<frameset framespacing=0 frameborder=0 cols="225,*">
		<frame noresize scrolling=no id='menubar' name='menubar' src="menuBar.php"/>
		<frame scrolling=auto id='work' name='work' src="./Tracker/TrackerView2.php"/>
	</frameset>
</frameset>
</html>
