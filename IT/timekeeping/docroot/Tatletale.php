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

class DBManager
{
		function __construct()
		{
			$this->m_pdo = new PDO('mysql:host=localhost;dbname=timekeeping','timekeeping','doofus', array( PDO::ATTR_PERSISTENT => true ) );
			$this->m_pdo->exec('SET CHARACTER SET utf8');
		}	
		
		function pdo()
		{
			return $this->m_pdo;
		}	
		
		function exec( $sql )
		{
			return $this->m_pdo->exec( $sql );
		}		
		
		function __destruct()
		{
			$this->m_pdo = null;
		}		

}

?>
<HTML>
<HEAD>
<TITLE>Timekeeping Tatletale</TITLE>
<meta http-equiv="refresh" content="3600" />
</HEAD>
<BODY bgcolor='#FFFFF0'>

<div align=center>
<h2 style='font-family: Arial;'>Number of weekdays since user last entered their time:</h2>

<TABLE cellspacing=0>
<?php

$db = new DBManager();
$pdo = $db->pdo();
$sth = $pdo->prepare( "select u.Username username, DATEDIFF(NOW(), max(te.Date)) daycount from Users u, TimeEntryRecord te where u.UserID = te.UserID AND u.State = 'active' AND te.Date < NOW() group by u.Username order by daycount desc" );
$sth->execute( );
//checkDBError( $sth );
$invalid = array("rohde","mrohde","QSAdmin","root");
$map = array();
$count = 1;
while ( $obj = $sth->fetch( PDO::FETCH_OBJ ) )
{
	if (!(in_array($obj->username,$invalid)))
	{
		$days_difference = $obj->daycount;
		$weeks_difference = floor($days_difference/7);
		$first_day = date("w", strtotime("-".$days_difference." days"));
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder;
		if (($odd_days > 7) && (date("w", strtotime("-".$days_difference." days")) != 0))
			$days_remainder--;
		if ($odd_days > 6 && (date("w", strtotime("-".$days_difference." days")) != 6))
			$days_remainder--;
		$total = ($weeks_difference * 5) + $days_remainder;
		$map[$obj->username] = $total;
		if ($total != 0) $count++;
	}
}
$idx=0;
foreach ($map as $username => $total)
{
	$fontsize = min(12+3*($total),48);
	echo "<tr bgcolor=";
	if ($total == 0)
		echo "#00FF00";
	else
	{
		$red = (($count - $idx) / $count) * 255;
		$green = ($idx / $count) * 255;
		echo "#".str_pad(dechex($red), 2, "0", STR_PAD_LEFT).''.str_pad(dechex($green), 2, "0", STR_PAD_LEFT)."00";
	}
	echo "><td style='border: 1px solid #000; font-size: ".$fontsize.";'>".$username."</td><td style='border: 1px solid #000; font-size: ".$fontsize.";'>".$total."</td></tr>";
	$idx++;
}

?>
</TABLE>
</div>

</BODY>
</HTML>