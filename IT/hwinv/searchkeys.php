<?php

    $q = "";
    $keyHosts = "";
    $results = array();
    
    if(isset($_POST["search"]))
    {
        $q = $_POST["search"];
    }
    
    if(isset($_POST["keyHosts"]))
    {
        $keyHosts = json_decode(stripslashes($_POST["keyHosts"]), true);
    }
    
    if($q !== "")
    {
        $q = strtolower($q);
        $length = strlen($q);
        
        foreach($keyHosts as $i => $row)
        {
            $key = strtolower($keyHosts[$i]["windowsKey"]);
            if($q == strtolower($keyHosts[$i]["hostname"]) ||
                stristr($q, substr($key, 0, $length)))
            {
                $results[] = $keyHosts[$i]["windowsKey"] . " " . $keyHosts[$i]["hostname"];
            }
        }    
    }

    echo json_encode(['code'=>200, 'q'=>$q, 'results'=>$results]);

?>
