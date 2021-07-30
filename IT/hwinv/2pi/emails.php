<?php

if (php_sapi_name() != "cli") 
{
	echo "Not allowed from server";
	exit(0);
}

require_once( dirname( __FILE__ )."/./database/config.php" );
require_once( dirname( __FILE__ )."/./database/DBManager.php" );
require_once( dirname( __FILE__ )."/./database/LdapLib.php" );
require_once( dirname( __FILE__ )."/DBHelper.php" );

$users = getUsers();

$lookup = array();
foreach ( $users->users as $user )
{
	$lookup[$user["uid"]] = $user;
}

function lookupUser( $uid )
{
	global $lookup;
	if ( !isset($lookup[$uid]) )
	{
		$result = array();
		$result["name"] = "Unknown user(".$uid.")";
        $result["username"] = "Unknown user(".$uid.")";
 		return $result;
	}
	return $lookup[$uid];
}

foreach ( $users->users as $user )
{
	$username = $user["username"];
	$uid = $user["uid"];
	$body = "";
	$data = getTodoFeedbackForUser($uid);
	if ( count($data) > 0 )
	{
		$body.="<h3>TODO</h3>";
		$body.="<p>You have ".count($data)." feedback item".(count($data)>1?"s":"")."  waiting to be completed.</p>";
		$body.="<table>";
		$body.="<tr><th>Request Date</th><th>Solicitor</th><th>Subject</th><th>Topic</th>";
		foreach ( $data as $item )
		{
			$solicitor = lookupUser($item->SOLICITOR_ID);
			$subject = lookupUser($item->SUBJECT_ID);
			$body.="<tr>";
			$body.="<td nowrap>".substr($item->SOLICITATION_DATE,0,10)."</td>";
			$body.="<td nowrap>".$solicitor["name"]."</td>";
			$body.="<td nowrap>".$subject["name"]."</td>";
			$body.="<td>".htmlentities ($item->SOLICITATION_REASON)."</td>";
			$body.="</tr>";
		}
		$body.="</table>";
	}
	
	if ( in_array( $username, $users->reviewers ) )
	{
		$c = getReviewCount($uid);
		if ( $c > 0 )
		{
			if ( $body != "" ) $body .= "<hr/>";
			$data = getOpenFeedbackForReview($uid);
			$body.="<h3>TO REVIEW</h3>";
			$body.="<p>You have ".$c." feedback item".($c>1?"s":"")." waiting to be reviewed.</p>";
			$commenters = array();
			$unreadCount = array();
			$pinnedCount = array();
			foreach ( $data as $item )
			{
				$commenter = lookupUser($item->COMMENTER_ID)["username"];			
				if (!isset( $commenters[$commenter] ) )
					$commenters[$commenter] = true;
				
				if ( $item->STATUS == "UNREAD" )
				{
					if (!isset( $unreadCount[$commenter] ) )
						$unreadCount[$commenter] = 0;
					
					$unreadCount[$commenter]++;
				}
				else if ( $item->STATUS == "PINNED" )
				{
					/*if (!isset( $pinnedCount[$commenter] ) )
						$pinnedCount[$commenter] = 0;
					
					$pinnedCount[$commenter]++;*/
				}
			}
			foreach ( $commenters as $commenter => $value )
			{
				$unread = 0;
				$pinned = 0;
				
				if (isset( $unreadCount[$commenter] ) )
					$unread = $unreadCount[$commenter];
				
				if (isset( $pinnedCount[$commenter] ) )
					$pinned = $pinnedCount[$commenter];
				
				if ( $unread != 0 && $pinned == 0 )
				{
					$body.="&nbsp;&nbsp;&nbsp;&nbsp;".$unread." unread item".($unread>1?"s":"")." from ".$commenter."<br/>";
				} 
				else if ( $unread != 0 && $pinned != 0 )
				{
					$body.="&nbsp;&nbsp;&nbsp;&nbsp;".$unread." unread and ".$pinned." pinned items from ".$commenter."<br/>";
				}
				else if ( $unread == 0 && $pinned != 0 )
				{
					$body.="&nbsp;&nbsp;&nbsp;&nbsp;".$pinned." pinned item".($pinned>1?"s":"")." from ".$commenter."<br/>";
				}
			}
			$body.="<br/>";
		}
	}
	
	if ( in_array( $username, $users->solicitors ) )
	{
		$data = getOpenSolicitedFeedback($uid);
		if ( count($data) > 0 )
		{
			if ( $body != "" ) $body .= "<hr/>";
			$body.="<h3>OUTSTANDING SOLICITED</h3>";
			$body.="<p>You have ".count($data)." solicited item".(count($data)>1?"s":"")." in flight.</p>";
			$commenters = array();
			$waitingFinalize = array();
			$noinput = array();
			foreach ( $data as $item )
			{
				$commenter = lookupUser($item->COMMENTER_ID)["username"];			
				if (!isset( $commenters[$commenter] ) )
					$commenters[$commenter] = true;
				
				if ( $item->FEEDBACK_DATE == null )
				{
					if (!isset( $noinput[$commenter] ) )
						$noinput[$commenter] = 0;
					
					$noinput[$commenter]++;
				}
				else
				{
					if (!isset( $waitingFinalize[$commenter] ) )
						$waitingFinalize[$commenter] = 0;
					
					$waitingFinalize[$commenter]++;
				}
			}
			foreach ( $commenters as $commenter => $value )
			{
				$wf = 0;
				$ni = 0;
				
				if (isset( $waitingFinalize[$commenter] ) )
					$wf = $waitingFinalize[$commenter];
				
				if (isset( $noinput[$commenter] ) )
					$ni = $noinput[$commenter];
				
				if ( $wf != 0 && $ni == 0 )
				{
					$body.="&nbsp;&nbsp;&nbsp;&nbsp;".$wf." awaiting finalization for ".$commenter."<br/>";
				} 
				else if ( $wf != 0 && $ni != 0 )
				{
					$body.="&nbsp;&nbsp;&nbsp;&nbsp;".$wf." awaiting finalization and ".$ni." requiring input from ".$commenter."<br/>";
				}
				else if ( $wf == 0 && $ni != 0 )
				{
					$body.="&nbsp;&nbsp;&nbsp;&nbsp;".$ni." requiring input from ".$commenter."<br/>";
				}
			}
			$body.="<br/>";
		}
	}	
	if ( $body != "" )
	{
		if ( isset( $forceEmailTo ) )
		{
			$to = $forceEmailTo;
		}
		else
		{
			$to = $username;
		}
		$to = $to."@quantumsignalai.com";
		
		echo "Sending email to ".$to."\r\n";
		$subject = "QSAI2Pi Digest for ".date(DATE_RFC2822);

		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: <itrobot@quantumsignalai.com>' . "\r\n";
		
		$final = "<html>";
		$final .= "<style>table, th, td { border: 1px solid black; border-collapse: collapse; padding: 2px; }";
		$final .= "</style>";
		$final .= "<body>";
		$final .= "<h2>QSAI2Pi Digest for ".$username."</h2>";
		$final .= $body;
		$final .= "</body>";
		$final .= "</html>";
		mail($to,$subject,$final,$headers);
		echo $final;
	}
}

?>