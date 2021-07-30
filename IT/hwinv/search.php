
<?php
    
    $q = "";
    $data = "";
    $results = array();

    if(isset($_POST["search"]))
    {
        $q = $_POST["search"];
    }
    
    if(isset($_POST["hosts"]))
    {
        $data = json_decode(stripslashes($_POST["hosts"]), true);
    }
    
    if($q !== "")
    {
        $q = strtolower($q);
        $length = strlen($q);

        foreach($data as $i => $row)
        {
            $name = $data[$i]["hostname"];
            if(stristr($q, substr($name, 0, $length)))
            {
                $results[] = $name;
            }
        }
    }

    echo json_encode(['code'=>200, 'q'=>$q, 'results'=>$results]);
   
?>