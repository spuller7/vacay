<?php 

header('Content-Type: text/plain; charset=UTF-8');
	$db = new PDO($cardreaderDbConnect, $cardreaderDbUser, $cardreaderDbPW);
	
	$stmt = $db->prepare('select distinct BadgeID, FirstName, LastName, Time as Time, unix_timestamp(Time) as utime  from Scans where Date(Time) = date(now()) and BadgeID<>"" group by BadgeID, FirstName, LastName, Time ORDER BY Time DESC LIMIT 10');
	$stmt->execute(array());
	$data = $stmt->fetchAll( PDO::FETCH_CLASS);
	foreach ( $data as $item )
	{
		$name = $item->FirstName;
		if ( strlen($name) > 0 )
			$name .= " ";
		
		$name .= $item->LastName;
		if ( strlen( $name ) == 0 )
			$name = "[noname]";
		
		echo floor(($item->utime-time())/60)."m ".$name."\n";
		// .$item->BadgeID." "
		// ." ".$item->Time
	}
	return;
?>
