<?php


if (php_sapi_name() != "cli")
{
        echo "Not allowed from server";
        exit(0);
}

require('mailmgr.php');

error_reporting(~E_WARNING);

if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
	$errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    
    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

echo "Socket created \n";

if( !socket_bind($sock, "0.0.0.0" , 5514) )
{
	$errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    
    die("Could not bind socket : [$errorcode] $errormsg \n");
}

echo "Socket bind OK \n";
while(1)
{
	$r = socket_recvfrom($sock, $buf, 9000, 0, $remote_ip, $remote_port);

	if ( $r === false )
		continue;
	$parts = array();
	$inQuote = false;
	$inKey = true;
	$key = "";
	$value = "";
	$part = "";
	for ( $i = 0; $i < strlen($buf); $i++ )
	{
		$ch = $buf[$i];
		if ( $ch == '"' )
		{
			$inQuote = !$inQuote;
			continue;
		}
		if ( !$inQuote )
		{
			if ( $ch == '=' && $inKey )
			{
				$inKey = false;
				continue;
			}
			if ( $ch == ' ' )
			{
				if ( strlen($key)>0)
				{
					$parts[$key] = $part;
				}
				$key = "";
				$part = "";
				$inKey = true;
				continue;
			}
		}
		if ( $inKey )
			$key .= $ch;
		else
			$part .= $ch;
	}
	if ( strlen($key)>0)
	{
		$parts[$key] = $part;
	}


	if ( $parts["m"] == 1079 && startswith($parts["msg"], "destination" ) )
	{
		continue;
	}

	$t = $parts["time"];
	$dt_part = substr( $t,0,10);
	$redis->lpush("IT.firewall.".$dt_part, json_encode($parts));
	
	if ( $parts["m"] == 1080 )
	{
		if ( $parts["msg"] == "SSL VPN zone remote user login allowed" )
		{
			$who = $parts["usr"];
			$dt = strtotime($t);
			$ip = $parts["src"];
			setInfrastructureFlag( "VPN.".$who, $dt, $ip, "syslogger" );
		}
	}
	echo json_encode($r)." - ".$buf."\r\n";
}

socket_close($sock);

?>
