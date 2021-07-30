<?php  
    $q = "";
    $macHostname = "";
    $results = array();
    
    if(isset($_POST["search"]))
    {
        $q = $_POST["search"];
        $q = strtolower($q);
    }
    
    if(isset($_POST["macHostname"]))
    {
        $macHostname = json_decode(stripslashes($_POST["macHostname"]), true);
    }
    
    
    if($q !== "")
    {
        $length = strlen($q);
        foreach($macHostname as $i => $row)
        {
            $mac = $macHostname[$i]["macAddress"];
            if($q == strtolower($macHostname[$i]["hostname"]) ||
                stristr($q, substr($mac, 0, $length)))
            {
                $results[] = $macHostname[$i]["macAddress"] . " ". $macHostname[$i]["hostname"];
            }
        }    
    }
     
    echo json_encode(['code'=>200, 'q'=>$q, 'results'=>$results]);
?>