<?php 

if (php_sapi_name() != "cli") 
{
	echo "Not allowed from server";
	exit(0);
}

require('mailmgr.php');

function dosomeshit($shit)
{
$mbox = imap_open($shit, "itrobot", "doofus");
if ( $mbox === FALSE )
{
	echo "Failed to open mailbox\n";
	return;
}
$limit = imap_num_msg($mbox);

echo "There are ".$limit." messages\n";

$now = time();
for ( $i = 1; $i <= $limit; $i ++ )
{
	echo "MESSAGE #".$i;
	$info = imap_headerinfo( $mbox, $i );
	echo " Date = ".date( "M d Y H:i:s", $info->udate).": ".$info->subject."\n";
	$struct = imap_fetchstructure( $mbox, $i );
	$body = imap_body( $mbox, $i );
	$state = hasMsg($info);
	if ( $state == "" )
	{
		echo "==> Added\n";
		createMsg( $info, $body, $struct );
	}
	else
	{
		$age = $now - $info->udate;
		if ( $age > 3600*24*2 )
		{
			echo "Message is ".$age." seconds old, purging\n";
			imap_delete( $mbox, $i );
		}
	}
}

imap_expunge($mbox);
imap_errors();
imap_alerts();
imap_close($mbox);
echo $shit.":DONE!\n";
}

#dosomeshit("{10.1.51.10:143/novalidate-cert}INBOX");
dosomeshit("{10.2.0.250:143/novalidate-cert}INBOX");

?>
