<?php
    
    include "connector.php";
    include "functions.php";
    
    $connection = new DBcon;
    $tables     = $connection->getAll();
    $hosts      = $tables["hosts"];
    $macs       = $tables["macaddresses"];
    $newMac     = NULL;
    $macHost    = NULL;
    $msg        = "";
    $warning    = "";
    $hostFound  = "";
    $hostID = NULL;
    $macExists = NULL;

    if(isset($_POST["newMac"]))
    {
        $newMac = $_POST["newMac"];
    }    
    
    if(isset($_POST["macHost"]))
    {
        $macHost = $_POST["macHost"];
    }
    
    if($newMac == NULL)
    {
        $msg = "Please enter a MAC address and corresponding hostname.";
        $connection->close();
        echo json_encode(['msg'=>$msg]);
        exit();
    }    
    
    if(filter_var($newMac, FILTER_VALIDATE_MAC) === false)
    {
        $msg = "'$newMac' is not a MAC address, no actions taken";
        $connection->close();
        echo json_encode(['msg'=>$msg]);
        exit();
    }
    
    foreach($hosts as $i => $row)
    {
        if(strtolower($hosts[$i]["hostname"]) == strtolower($macHost))
        {
            $hostID = $hosts[$i]["hostID"];
            $hostFound = "assigned to $macHost";
        }
    }
    
    foreach($macs as $i => $row)
    {
        if(strtolower($macs[$i]["macAddress"]) == strtolower($newMac))
        {
            $macExists = $macs[$i]["macAddress"];
        }
    }    
    
    if($hostID == NULL)
    {
        $msg = "Provided hostname '$macHost' not found, no actions taken";
        $connection->close();
        echo json_encode(['msg'=>$msg]);
        exit();
    } 
    
    if($macExists === NULL)
    {
        $status = addMac($connection, $newMac, $hostID);
    }
    
    if($macExists !== NULL)
    {
        $warning .= " WARNING: MAC address already exists, updating its host to $macHost";
        $status = updateMacHost($connection, $newMac, $hostID);       
    }
    
    if($status == 1)
    {
        $status = "success";
    }
    else
    {
        $status = "failed";
    }
    
    $msg = "Added $newMac to database, " . $hostFound . $warning . ": " . $status;
    $connection->close();
    echo json_encode(['msg'=>$msg]);
?>
