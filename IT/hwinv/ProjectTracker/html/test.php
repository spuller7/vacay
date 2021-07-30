<?php
require_once( dirname( __FILE__ )."/./docroot/authenticate.php" );
require_once( dirname( __FILE__ )."/projTrackDB.php" );
require_once( dirname( __FILE__ )."/./docroot/DBHelper.php" );

$data = PTGetAssignedUsersForProject(101);
echo gettype($data);

echo "<pre>".json_encode($data,JSON_PRETTY_PRINT)."</pre>";

$newData = PTGetUsersWithEntriesbyProjectandDate(101, '2019-12-15');

// function searchForId($id, $array, $field) {
   // foreach ($array as $key => $val) {
	   // echo $val->$field . ' '. gettype($val->$field) . '<br>';
	   // echo $id . ' '. gettype($id) .'<br>';
       // if ($val->$field == $id) {
           // return true;
       // }
   // }
   // return false;
// }


// for($i = 0; $i < sizeof($newData); $i++)
// {
	// if ( !searchForId( $newData[$i]->UserID, $data, 'UserID')) //!(array_search($newData[$i]->UserID, array_column($data, 'UserID')))//!array_search($data, $newData[$i]->UserID))
	// {
		// echo "not in array ". $newData[$i]->UserID . "<br>";
		// array_push($data, $newData[$i]);
	// }
// }


echo "<pre>".json_encode($newData,JSON_PRETTY_PRINT)."</pre>";
echo "<pre>".json_encode($data,JSON_PRETTY_PRINT)."</pre>";
// echo implode(',', $data);
// if ($data !== false)
	// echo  "true";
// else
	// echo "false";
// echo $data;
// $str = implode(',', $data);
// echo $str;

echo "MISC PROJECT ID = ".MISC_PROJECT_ID;

// $data = PTGetActiveUsersData();
// echo "<pre>".json_encode($data,JSON_PRETTY_PRINT)."</pre>";

// $result = PTGetOrderedManagersListForProject( 1 );

// echo "<pre>".json_encode($result,JSON_PRETTY_PRINT)."</pre>";

/*
echo "====<br/>";
$layouts = PTGetLayoutsForUser( 3 );
echo count($layouts);
echo "<br/>";
foreach ($layouts as $obj )
{
	echo "Page: ".$obj->PAGE."<br/>";
	echo "Layout: ".$obj->LAYOUT."<br/>";
	echo "---<br/>";
}


var_dump(PTHasProjectEntriesForUserAndDate( 3, 13, '2019-08-04'));
var_dump(PTAreProjectEntriesLockedForUserAndDate( 3, '2019-08-11'));

echo "<br/>======<br/>";

echo "11/9/19 = ".PTGetSundayDateString("11/9/2019")."<br/>";
echo "11/10/19 = ".PTGetSundayDateString("11/10/2019")."<br/>";
echo "11/11/19 = ".PTGetSundayDateString("11/11/2019")."<br/>";

echo "2019-11-09 = ".PTGetSundayDateString("2019-11-09")."<br/>";
echo "2019-11-10 = ".PTGetSundayDateString("2019-11-10")."<br/>";
echo "2019-11-11 = ".PTGetSundayDateString("2019-11-11")."<br/>";



var_dump(PTGetAssignedUsersForProject(11));
echo "<br/>";
var_dump(PTGetAssignableUsersForProject(11));
echo "<br/>";
var_dump(PTRenameProject( '14', 'QWE TEST RENAME' ));
*/

?>