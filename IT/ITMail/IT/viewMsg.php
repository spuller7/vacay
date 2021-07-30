<?php require('chkauth.php');?>	<script>
	function doDelete(id, force)
	{
		if ( force || confirm("Are you sure you want to delete this message?") )
		{
			deleteMessage( id );
		}
	}
	
	function modifyHost(id,host,key)
	{
		if ( confirm("Are you sure you want to reset "+ host + " " + key ) )
		{
			resetStatus(id,host,key);
		}
	}
	
	function modifyInf(id,label,ageLimit)
	{
		var newLimit = prompt("Enter new age limit for " + label, ageLimit );
		if ( newLimit != undefined )
		{
			setAgeLimit(id,label,newLimit);
		}
	}
	</script>
<?php

require('mailmgr.php');

$id = $_GET["id"];

setMsgFlag( $id, "READ", 1 );
$msg = getMsg($id);
$headers = $msg["headers"];
$body = $msg["body"];
$state = $msg["state"];
$struct = $msg["structure"];
if ($struct != null )
{
	$struct = json_decode($struct);
	if ( $struct->encoding == 3 )
		$body = base64_decode($body);
}

function GetStatusLink( $id, $host, $key, $current, $state )
{
	global $stateToIcon;

	if ( $current == $state )
	{
		return "<img class='select_$state' src='/images/".$stateToIcon[$state]."' title='$state'/>";
	}
	else
	{
		return "<a onclick='setStatusState( \"".addSlashes($id)."\", \"".$host."\", \"".$key."\", \"".$state."\"); return false;'><img class='unselect' src='/images/".$stateToIcon[$state]."' title='$state'/></a>";
	}
}

function GetInfLink( $id, $label, $current, $state )
{
	global $stateToIcon;
	
	if ( $current == $state )
	{
		return "<img class='select_$state' src='/images/".$stateToIcon[$state]."' title='$state'/>";
	}
	else
	{
		return "<a onclick='setInfState( \"".addSlashes($id)."\", \"".$label."\", \"".$state."\"); return false;'><img class='unselect' src='/images/".$stateToIcon[$state]."' title='$state'/></a>";
	}
}

echo "<div class='MSG_DEL'><a href='#' onclick='doDelete(\"".addSlashes($id)."\", event.ctrlKey ); event.stopPropagation(); return false;'><img src='/images/trash.png'/></a></div>";
echo "<div class='MSG_SUBJECT'><a name='top'/>";
if ( $state == "NAK" )
{
	echo "<a onclick='AckAlarm(\"".addSlashes($id)."\", \"Alarm Message View\", event); return false;'><img src='/images/tag_red.png'/></a>";
}

echo htmlentities(iconv_mime_decode($headers->subject))."</div>";

echo "<div class='MSG_DATE'>".$headers->date."</div>";
echo "<span class='MSG_FROM'>".iconv_mime_decode($headers->senderaddress)."</span><br/>";
echo "<div class='LINE_SEP'></div>";
echo "<div class='MSG_CONTAINER'>";
echo "<div class='MSG_STATES'>";
$keys = getInfrastructureKeys( $msg );
if  ( count($keys) > 0 )
{
	echo "<div class='INF_TITLE'>Infrastructure</div>";
	foreach ( $keys as $key => $label )
	{
		$v = $msg[$key];
		$ageLimit = getInfAgeLimit( $label );
		echo "<div class='INF_LABEL'><a onclick='modifyInf( \"".addSlashes($id)."\", \"$label\", \"$ageLimit\" ); return false;'>$label</a><br/>";
		
		$bFoundOne = false;
		foreach ( $stateToIcon as $state => $icon )
		{
			echo GetInfLink( $id, $label, $v, $state );
			$bFoundOne |= ( $v == $state );
		}
		if ( !$bFoundOne )
		{
			echo "<br/><span class='select_GOOD'>".$v."</span>";
		}
		echo "</div>";
	}
}

$keys = getHostKeys( $msg );
if ( count( $keys ) > 0 )
{
	echo "<div class='INF_TITLE'>Hosts</div>";

	foreach ( $keys as $key => $o )
	{
		$host = $o->host;
		$key = $o->key;
		$v = $o->value;
	
		echo "<div class='INF_LABEL'><a onclick='modifyHost( \"".addSlashes($id)."\", \"".$host."\", \"".$key."\", \"".$state."\"); return false;'>".$host." ".$key."</a><br/>";
		
		$bFoundOne = false;
		foreach ( $stateToIcon as $state => $icon )
		{
			echo GetStatusLink( $id, $host, $key, $v, $state );
			$bFoundOne |= ( $v == $state );
		}
		if ( !$bFoundOne )
		{
			echo "<br/><span class='select_GOOD'>".$v."</span>";
		}
		echo "</div>";
	}
}
echo "</div>";
echo "<div class='MSG_SEP'></div>";
echo "<div class='MSG_BODY'><a href='#bottom'>jump to bottom</a><pre>".htmlspecialchars(wordwrap($body,120))."</pre><a href='#top'>jump to top</a></div>";
echo "</div><a name='bottom'></a>";
?>
