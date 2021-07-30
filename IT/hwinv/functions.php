<?php 
    session_start();
    /*if(!($_SESSION['loggedin']))
    {
        header('Location: login.php');
        exit;
    }*/
    require "connector.php";
    include 'LdapLib.php';
    include 'LdapConfig.php';

    // reconstruct the organization of the spreadsheet
    function buildHosts ($tables, $ht)
    {    
        // reference the input arrays
        $hosts = $tables["hosts"];
        $types = $tables["hosttype"];
        $vendorModels = $ht["vendorModels"];
        $macHostname  = $ht["macHostname"];
        $notes = $tables["note"];
        

        $countH = 0;
        
        if(!empty($hosts))
        {
            $countH = count($hosts);
        } 
        elseif (empty($hosts))
        {
            $hostRow = array(
                "hostname" => "",
                "PIN"      => "",
                "vendor"   => "",
                "model"    => "",
                "type"     => "",
                "tag"      => "",
                "STSN"     => "",
                "MACs"     => array(0 => ""),
                "deployed" => "",
                "user"     => "",
                "notes"    => array(0 => "")
            );
            
            return $hostRow;
        }

        // return array with IT inventory host data reassembled
        $hostData = array();

        for($i = 0; $i < $countH; $i++)
        {
            // initialize row array
            $hostRow = array(
                "hostname" => "",
                "PIN"      => "",
                "vendor"   => "",
                "model"    => "",
                "type"     => "",
                "tag"      => "",
                "STSN"     => "",
                "MACs"     => array(0 => ""),
                "deployed" => "",
                "user"     => "",
                "notes"    => array(0 => "")
            );

            // hostname
            $hostRow["hostname"] = $hosts[$i]["hostname"];
            
            //admin PIN
            if($hosts[$i]["adminPIN"] !== NULL)
            {    
                $hostRow["PIN"] = $hosts[$i]["adminPIN"];
            }

            // vendor and model
            if(!empty($vendorModels))
            {
                foreach ($vendorModels as $j => $row)
                {
                    if($vendorModels[$j]["modelID"] == $hosts[$i]["modelID"])
                    {
                        $hostRow["vendor"] = $vendorModels[$j]["vendor"];
                        $hostRow["model"]  = $vendorModels[$j]["model"];
                    }
                }
            }

            // PC, Laptop, Dock
            if(!empty($types))
            {
                foreach ($types as $x => $row)
                {
                    if($types[$x]["typeID"] == $hosts[$i]["typeID"])
                    {
                        $hostRow["type"] = $types[$x]["hostType"];
                    }
                }
            }

            // property tag (if present)
            if($hosts[$i]["propertyTag"] != NULL)
            {
                //$hostRow["tag"] = "#" . str_pad($hosts[$i]["propertyTag"], 6, "000000", STR_PAD_LEFT);
                if($hosts[$i]["propertyTag"] == 0 || $hosts[$i]["propertyTag"] == NULL)
                {
                    $hostRow["tag"] = "";
                }
                else
                {
                   $hostRow["tag"] = $hosts[$i]["propertyTag"];
                }
            }

            // Dell service tag or manufacturer serial number (if present)
            if($hosts[$i]["serviceTag-serialNumber"] != NULL)
            { 
               $hostRow["STSN"] = $hosts[$i]["serviceTag-serialNumber"];
            }

            // all MACs belonging to the machine
            $macNum = 0;
            if(!empty($macHostname))
            {
                foreach ($macHostname as $k => $row)   
                {
                    if($macHostname[$k]["hostname"] == $hosts[$i]["hostname"])
                    {
                        $hostRow["MACs"][$macNum] = $macHostname[$k]["macAddress"];
                        $macNum++;
                    }
                }
            }
            
            // date the host was deployed
            if($hosts[$i]["deploydate"] != NULL)
            {
                $hostRow["deployed"] = $hosts[$i]["deploydate"];
            }

            // username of user currently using host
            if(!empty($hosts))
            {
                if($hosts[$i]["ldapUID"] != NULL)
                {
                    $hostRow["user"] = $hosts[$i]["ldapUID"];
                }
            }
            
            // all notes currently assigned to host
            $noteNum = 0;
            if(!empty($notes))
            {
                foreach ($notes as $n => $row)
                {
                    if($hosts[$i]["hostID"] == $notes[$n]["hostID"])
                    {
                        $hostRow["notes"][$noteNum] = $notes[$n]["text-note"];
                        $noteNum++;
                    }
                }
            }

            // add row
            $hostData[$i] = $hostRow;
        }

        return $hostData;
    }

    // all the windows keys and pertinent information together
    function buildKeys ($tables, $ht)
    {
        
        $keys         = $tables["windowskeys"];
        $hostKeys     = $ht["hostKeys"];
        $hostStickers = $ht["hostStickers"];
        $winVers      = $ht["winVers"];

        $countK = 0;
        
        if(!empty($keys))
        {
            $countK = count($keys);
        }
        elseif (empty($keys))
        {
            $keyRow = array(
                "winKey"  => "",
                "version" => "",
                "host"    => "",
                "sticker" => "",
                "free"    => "",
                "upgrade" => "",
                "status"  => ""
            );
            
            return $keyRow;
        }

        // return array for Windows key data reassembled
        $keyData = array();

        for($i = 0; $i < $countK; $i++)
        {
            // initialize the row array to prevent undefined indexes
            $keyRow = array(
                "winKey"  => "",
                "version" => "",
                "host"    => "",
                "sticker" => "",
                "free"    => "",
                "upgrade" => "",
                "status"  => ""
            );

            // key
            $keyRow["winKey"] = $keys[$i]["windowsKey"];

            // Windows Version
            if(!empty($winVers))
            {
                foreach ($winVers as $j => $row)
                {
                    if($winVers[$j]["windowsKey"] == $keys[$i]["windowsKey"])
                    {
                        $keyRow["version"] = $winVers[$j]["windowsVersion"];
                    }
                }
            }

            // associated hostname, if any
            if(!empty($hostKeys))
            {
                if($keys[$i]["hostID"] != NULL || $keys[$i]["hostID"] != 0)
                {
                    foreach ($hostKeys as $k => $row)
                    {
                        if($hostKeys[$k]["windowsKey"] == $keys[$i]["windowsKey"])
                        {
                            $keyRow["host"] = $hostKeys[$k]["hostname"];
                        }
                    }
                }
            }

            // sticker host, if any
            if(!empty($hostStickers))
            {
                if($keys[$i]["hostIDSticker"] != NULL || $keys[$i]["hostIDSticker"] != 0)
                {
                    foreach ($hostStickers as $l => $row)
                    {
                        if($hostStickers[$l]["windowsKey"] == $keys[$i]["windowsKey"])
                        {
                            $keyRow["sticker"] = $hostStickers[$l]["hostname"];
                        }
                    }
                }
            }

            // if key isn't assigned to a host, it's free
            if($keys[$i]["hostID"] == NULL || $keys[$i]["hostID"] == 0)
            {
               $keyRow["free"] = "License is available";
            }
            
            if($keys[$i]["winVerID"] == 2 && $keys[$i]["hostID"] != NULL)
            {
                $keyRow["upgrade"] = "Windows 7=>10 Pro";
            }

            // license verification status
            if($keys[$i]["statusID"] == 0)
            {
                $keyRow["status"] = "verified";
            }
            else
            {
                $keyRow["status"] = "failed";
            }

            // add row
            $keyData[$i] = $keyRow;
        }

        return $keyData;
    }    

    // update hostname associated with key
    // should be consistent, i.e. potentially two updates; when host changes, 
    //     it should also be removed from the prior key's hostID if present
    function updateKeyHost ($args)
    {   
        $key        = $args[0];
        $hostID     = $args[1];
        $connection = new DBcon();
        $status     = "";
        $oldKey = getKeyRowHost($connection, $hostID);
        $newKey = getKeyRowKey($connection, $key);
        $host   = getHost($connection, $hostID);
        
        if($hostID === false)
        {
            $status = "Host $host does not exist, no actions taken";
            return $status;
        }
        
        if($newKey === false)
        {
            $status = "Key $key does not exist, no actions taken";
            return $status;
        }
        
        
        if($newKey["hostID"] === $hostID)
        {
            $status = $host . " is already assigned to " . $key . ", no actions taken";
            return $status;
        }    
        
        if($oldKey !== false)
        {    
            if($oldKey["hostID"] !== NULL || $oldKey["hostID"] !== 0)
            {
               $old  = $oldKey["windowsKey"];
               $connection->run("UPDATE `windowskeys` SET `hostID`=NULL WHERE `windowsKey`=(?)", [$old]);
               $status .= "Host $host moved from old key $old, ";
            }
        }
        
        $connection->run("UPDATE `windowskeys` SET `hostID`=(?) WHERE `windowsKey`=(?)", [$hostID, $key]);
        $status .= "Host $host assigned to key $key";
        $connection->close();
        return $status;
    }
    
    function updateKeySticker ($args)
    {
        $key    = $args[0];
        $hostID = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE `windowskeys` SET `hostIDSticker`=(?) WHERE `windowsKey`=(?)", [$hostID, $key]);
        return $status;
    }

    // free up a key by specifying the key to be freed
    function freeKey($args)
    {
        $key = $args[0];
        $connection = new DBCon();
        $status = $connection->run("UPDATE windowskeys SET hostID=NULL WHERE windowsKey=(?)", [$key]);
        $connection->close();
        return $status;
    }

    // free up a key by specifying its former host
    function freeKeyViaHost ($args)
    {
        $hostname   = $args[0];
        $connection = new DBcon();
        $hostID     = getHostID($connection, $hostname);
        $keyRow     = getKeyRowHost($connection, $hostID);
     
        if($hostID === false)
        {
            $status = "Host $hostname doesn't exist, no action taken";
            return $status;
        }    
        
        if($keyRow === false)
        {
            $status = "No key is assigned to $hostname, no action taken";
            return $status;
        }
        
        $key    = $keyRow["windowsKey"];
        $status = $connection->run("UPDATE windowskeys SET hostID=NULL WHERE windowsKey=(?)", [$key]);
        $connection->close();
        return $status;
    }

    // add new key
    function addKey ($args)
    {   
        $key      = $args[0];
        $hostID   = NULL;
        $winVerID = NULL;
        if($args[1])
        {
            if($args[1] !== "none")
            {
                $hostID = $args[1];
            }     
        }
        if($args[2])
        {
            $winVerID = $args[2];
        }
        $connection = new DBcon();
        $status = $connection->run("INSERT INTO `windowskeys` (`windowsKey`, `hostID`, `winVerID`) VALUES (?, ?, ?)", [$key, $hostID, $winVerID]);
        $connection->close();
        return $status;
    }

    // remove key
    function delKey ($args)
    {
        $key = $args[0];
        $connection = new DBcon();
        $status = $connection->run("DELETE FROM `windowskeys` WHERE `windowsKey`=(?)", [$key]);
        $connection->close();
        return $status;
    }
    
    function updateKeyType ($args)
    {
        $key  = $args[0];
        $type = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE windowskeys SET winVerID=(?) WHERE windowsKey=(?)", [$type, $key]);
        $connection->close();
        return $status;
    }

    // update the verification status of a key
    function updateKeyStatus($args)
    {
        $key      = $args[0];
        $statusID = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE windowskeys SET statusID=(?) WHERE windowsKey=(?)", [$statusID, $key]);
        $connection->close();
        return $status;
    }        

    // add a new MAC address to a host
    function addMac ($args)
    {
        if(filter_var($args[0], FILTER_VALIDATE_MAC) !== false )
        {
            $mac      = $args[0];
            $hostID   = NULL;
            if($args[1])
            {
                if($args[1] !== "")
                {
                    $hostID   = $args[1];
                }   
            }
            $connection = new DBcon();
            $status = $connection->run("INSERT INTO `macaddresses` (`macAddress`, `hostID`) VALUES (?, ?)", [$mac, $hostID]);
            $connection->close();
            return $status;
        }
        $status = "$args[0] isn't a MAC address";
        return $status;
    }

    // delete MAC address
    function delMac ($args)
    {
        if(filter_var($args[0], FILTER_VALIDATE_MAC) !== false )
        {
            $mac = $args[0];
            $connection = new DBcon();
            $status = $connection->run("DELETE FROM `macaddresses` WHERE `macaddress`=(?)", [$mac]);
            $connection->close();
            if($status !== false)
            {
                return true;
            }
        }
        $status = "$args[0] isn't a MAC address";
        return $status;
    }
    
    function updateMac($args)
    {
        if(filter_var($args[0], FILTER_VALIDATE_MAC) !== false )
        {
            if(filter_var($args[1], FILTER_VALIDATE_MAC) !== false)
            {
                $old = $args[0];
                $new = $args[1];
                $connection = new DBcon();
                $status = $connection->run("UPDATE `macaddresses` SET macAddress=(?) WHERE `macAddress`=(?)", [$new, $old]);
                $connection->close();
                return $status;
            }
            $status = "$args[1] isn't a valid MAC address";
            return $status;
        }
        $status = "$args[0] isn't a valid MAC address";
        return $status;
    }

    // remove host assignment from MAC
    //     this is for ethernet dongles and added NICs
    function freeMac ($args)
    {        
        if(filter_var($args[0], FILTER_VALIDATE_MAC) !== false ){
            $mac = $args[0];
            $connection = new DBcon();
            $status = $connection->run("UPDATE macaddresses SET hostID=NULL WHERE macAddress=(?)", [$mac]);
            $connection->close();
            if($status !== false)
            {
                return true;
            }
        }
        $status = "$args[0] isn't a MAC address";
        return $status;
    }
    
    function updateHostname($args)
    {
        $host     = $args[0];
        $hostname = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE hosts SET hostname=(?) WHERE hostname=(?)", [$hostname, $host]);
        $connection->close();
        return $status;
    }
    
    function updateKey($args)
    {
        $key = $args[0];
        $new = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE windowskeys SET windowsKey=(?) WHERE windowsKey=(?)", [$new, $key]);
        $connection->close();
        return $status;
    }

    // change the host associated with a MAC
    //     this is for ethernet dongles and added NICs
    function updateMacHost ($args)
    {
        if(filter_var($args[0], FILTER_VALIDATE_MAC) !== false )
        {
            $mac      = $args[0];
            $hostID   = $args[1];
            $connection = new DBcon();
            $status = $connection->run("UPDATE macaddresses SET hostID=(?) WHERE macAddress=(?)", [$hostID, $mac]);
            $connection->close();
            if($status !== false)
            {
                return true;
            }
            return false;
        }
        $status = "$args[0] isn't a MAC address";
        return $status;
    }

    // change the user associated with a host
    function updateUserHost ($args)
    {
        $host = $args[0];
        $ldapUID = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE hosts SET ldapUID=(?) WHERE hostname=(?)", [$ldapUID, $host]);
        $connection->close();
        return $status;
    }

    // remove user from host
    function freeHost ($args)
    {       
        $host = $args[0];
        $connection = new DBcon();
        $status = $connection->run("UPDATE hosts SET ldapUID=NULL WHERE hostname=(?)", [$host]);
        $connection->close();
        return $status;
    }

    // add new hostname
    function addHost ($args)
    {
        $host = $args[0];
        $connection = new DBcon();
        $status = $connection->run("INSERT INTO `hosts` (`hostname`, `modelID`) VALUES (?,?)", [$host,1]);
        $connection->close();
        return $status;
    }

    // remove a host
    function delHost ($args)
    {
        $host = $args[0];
        $connection = new DBcon();
        $status = $connection->run("DELETE FROM `hosts` WHERE `hostname`=(?)", [$host]);
        $connection->close();
        return $status;
    }

    // update host ST/SN
    function updateSTSNHost ($args)
    {
        $host = $args[0];
        $STSN = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE `hosts` SET `serviceTag-serialNumber`=(?) WHERE `hostname`=(?)", [$STSN, $host]);
        $connection->close();
        return $status;
    }

    // update host property tag
    function updatePTHost ($args)
    {
        $host    = $args[0];
        $propTag = $args[1];
        //$propTag = trim($propTag, "#0");
        $connection = new DBcon();
        $status = $connection->run("UPDATE `hosts` SET `propertyTag`=(?) WHERE `hostname`=(?)", [$propTag, $host]);
        return $status;
    }

    // update host type [PC, Laptop, Dock]
    function updateTypeHost ($args)
    {
        $host   = $args[0];
        $typeID = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE `hosts` SET `typeID`=(?) WHERE `hostname`=(?)", [$typeID, $host]);
        $connection->close();
        return $status;
    }

    // add a new user
    function addUser ($args)
    {
        $username = $args[0];
        $ldapUID  = $args[1];
        $connection = new DBcon();
        
        $status = $connection->run("INSERT INTO `users` (`username`, `ldapUID`) VALUES (?, ?)", [$username, $ldapUID]);
        $connection->close();
        return $status;
    }

    // remove a user
    function delUser ($args)
    {
        $username = $args[0];
        $connection = new DBcon();
        
        $status = $connection->run("DELETE FROM `users` WHERE `username`=(?)", [$username]);
        $connection->close();
        return $status;
    }

    // add a new vendor; this will be used if the desired
    //     vendor isn't in the drop down list
    function addVendor ($args)
    {
        $vendor = $args[0];
        $connection = new DBcon();
        
        $status = $connection->run("INSERT INTO `vendors` (`vendor`) VALUES (?)", [$vendor]);
        $connection->close();
        return $status;
    }

    // delete a vendor; for testing, the user will not normally
    //     be presented with the option to delete a vendor
    function delVendor ($args)
    {
        $vendor = $args[0];
        $connection = new DBcon();
        
        $status = $connection->run("DELETE FROM `vendors` WHERE `vendor`=(?)", [$vendor]);
        $connection->close();
        return $status;
    }

    // add a new model, vendor must be provided
    function addModel ($args)
    {
        $vendorID   = $args[0];
        $modelName  = $args[1];
        $connection = new DBcon();

        $status = $connection->run("INSERT INTO `models` (`vendorID`, `model`) VALUES (?, ?)", [$vendorID, $modelName]);
        $connection->close();
        return $status;
    }

    // delete a model; for testing, the user will not normally
    //     be presented with the option to delete a model
    function delModel ($args)
    {
        $modelName = $args[0];
        $connection = new DBcon();
        
        $status = $connection->run("DELETE FROM `models` WHERE `model`=(?)", [$modelName]);
        $connection->close();
        return $status;
    }
    
    function updateModel ($args)
    {
        $oldname = $args[0];
        $newname = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE models SET model=(?) WHERE model=(?)", [$newname,$oldname]);
        $connection->close();
        return $status;
    }
    
    function updateModelVendor ($args)
    {
        $connection = new DBcon();
        $model    = $args[0];
        $vendorID = $args[1];
        $status   = $connection->run("UPDATE models SET vendorID=(?) WHERE model=(?)",[$vendorID, $model]);
        $connection->close();
        return $status;
    }
    
    function updateHostModel($args)
    {
        $hostname   = $args[0];
        $modelID    = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE hosts SET modelID=(?) WHERE hostname=(?)",[$modelID, $hostname]);
        $connection->close();
        return $status;
    }
    
    function updateAdminPIN ($args)
    {
        $hostname = $args[0];
        $adminPIN = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE `hosts` set `adminPIN`=(?) WHERE `hostname`=(?)",[$adminPIN, $hostname]);
        $connection->close();
        return $status;
    }
    
    function updateVendor ($args)
    {
        $oldname = $args[0];
        $newname = $args[1];
        $connection = new DBcon();
        $status = $connection->run("UPDATE vendors SET vendor=(?) WHERE vendor=(?)",[$newname,$oldname]);
        $connection->close();
        return $status;
    }
    
    function addNote($args)
    {
        $connection = new DBcon();
        
        $hostID = getHostID($connection, $args[0]);
        $note   = $args[1];
        $status = $connection->run("INSERT INTO `note` (`hostID`, `text-note`) VALUES (?, ?)", [$hostID, $note]);
        $connection->close();
        return $status;
    }
    
    function updateNote($args)
    {
        $connection = new DBcon();
        $oldnote    = $args[0];
        $newnote    = $args[1];
        $hostID     = getHostID($connection, $args[2]);
        $status     = $connection->run("UPDATE `note` SET `text-note`=(?) WHERE `text-note`=(?) AND `hostID`=(?)",[$newnote,$oldnote,$hostID]);
        $connection->close();
        return $status;
    }
    
    function delNote($args)
    {
        $connection = new DBcon();
        $note     = $args[0];
        $hostname = $args[1];
        $hostID   = getHostID($connection, $hostname);
        $status = $connection->run("DELETE FROM `note` WHERE `text-note`=(?) AND `hostID`=(?)",[$note, $hostID]);
        $connection->close();
        return $status;
    }
    
    function getVendorID($connection, $vendor)
    {
        $search = $connection->run("SELECT `vendorID` FROM `vendors` WHERE `vendor`=(?)",[$vendor])->fetch();
        if($search !== false)
        {
            $result = $search["vendorID"];
            return $result;
        }    
        return $search;
    }        
    
    function getHostID($connection, $hostname)
    {
        $search = $connection->run("SELECT `hostID` FROM `hosts` WHERE `hostname`=(?)",[$hostname])->fetch();
        if($search !== false)
        {
            $result = $search["hostID"];
            return $result;
        }
        return $search;
    }
    
    function getHost($connection, $hostID)
    {
        $search = $connection->run("SELECT `hostname` FROM `hosts` WHERE `hostID`=(?)",[$hostID])->fetch();
        if($search !== false)
        {
            $result = $search["hostname"];
            return $result;
        }
        return $search;
    }
    
    function getModelID($connection, $model)
    {
        $search = $connection->run("SELECT `modelID` FROM `models` WHERE `model`=(?)", [$model])->fetch();
        if($search !== false)
        {
            $result = $search["modelID"];
            return $result;
        }
        return $search;
    }        
     
    function getKeyRowHost ($connection, $hostID)
    {
        $search = $connection->run("SELECT * FROM `windowskeys` WHERE `hostID`=(?)",[$hostID])->fetch();
        return $search;
    }
    
    function getKeyRowKey ($connection, $key)
    {
        $search = $connection->run("SELECT * FROM `windowskeys` WHERE `windowsKey`=(?)",[$key])->fetch();
        return $search;
    }
    
    function upDate($args)
    {
        $connection = new DBcon(); 
        $hostname = $args[0];
        $date     = $args[1];
        
        if($args[1] === "")
        {
            $status = $connection->run("UPDATE hosts SET deploydate=NULL WHERE hostname=(?)", [$hostname]);      
        }
        else
        {
            $status = $connection->run("UPDATE hosts SET deploydate=(?) WHERE hostname=(?)", [$date, $hostname]);
        }
        $connection->close();
        return $status;
    }
    
    function authenticate()
    {
        $username = "";
        $password = "";
          
        if(isset($_POST["username"]))
        {
            $_SESSION['username'] = $_POST["username"];
            $username = $_SESSION['username'];
        }

        if(isset($_POST["password"]))
        {
            $_SESSION['password'] = $_POST["password"];
            $password = $_SESSION['password'];
        }
        
        if($username !== "" && $password !== "")
        {
           if(ldapLogin($username, $password))
           {
               $group = new LdapGroup();
               $user  = new LdapUser($username);
                              
               // cmshowers, drice, kwakevainen, rlupa, ktuttle, adminuser2
               //$admins    = $group->getGroupMembers("qsadmin");
               // jenkins, adminuser
               //$notadmins = $group->getGroupMembers("qsnotadmin");
               
               if($group->isUserInGroup($username, "qsadmin"))
               {
                   $_SESSION['loggedin'] = true;
                   header('Location: index.php');
                   exit;
               }
               else
               {
                   header('Location: login.php');
                   return "authentication succeeded, but you are not authorized";
               }
               
           }
           else
           {
               header('Location: login.php');
               return "incorrect username or password";
           }
        }
    }
    
    function ldapLogin($username, $password)
    {
        // config
        global $LdapUri;
        $ldapconfig['host'] = $LdapUri;
        $ldapconfig['port'] = '389';
        $ldapconfig['basedn'] = 'dc=ldap,dc=internal,dc=quantumsignal,dc=com';
        $ldapconfig['usersdn'] = 'ou=People';
        
        // connect and set protocol
        $conn=ldap_connect($ldapconfig['host'], $ldapconfig['port']);
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        
        // configure dn
        $dn = "uid=".$username.",".$ldapconfig['usersdn'].",".$ldapconfig['basedn'];
        
        if(@ldap_bind($conn, $dn, $password))
        {
            return true;
        }
        
        return false;
    }
    
    function addHardware($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $status = $connection->run("INSERT INTO `hardware` (`hostname`) VALUES (?)", [$hostname]);
        $connection->close();
        return $status;
    }
    
    function delHardware($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $status = $connection->run("DELETE FROM `hardware` WHERE `hostname`=(?)",[$hostname]);
        $connection->close();
        return $status;
    }
    
    function updateMotherboard($args)
    {
        $connection = new DBcon();
        $hostname =  $args[0];
        $motherboard = $args[1];
        $status = $connection->run("UPDATE `hardware` SET `motherboard`=(?) WHERE `hostname`=(?)", [$motherboard,$hostname]);
        $connection->close();
        return $status;
    }
    
    function updateGraphics($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $graphics = $args[1];
        $status = $connection->run("UPDATE `hardware` SET `graphics`=(?) WHERE `hostname`=(?)", [$graphics,$hostname]);
        $connection->close();
        return $status;
    }
    
    function updateCPU($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $CPU = $args[1];
        $status = $connection->run("UPDATE `hardware` SET `CPU`=(?) WHERE `hostname`=(?)", [$CPU,$hostname]);
        $connection->close();
        return $status;
    }
    
    function updateCPU_specs($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $CPU_specs = $args[1];
        $status = $connection->run("UPDATE `hardware` SET `CPU_specs`=(?) WHERE `hostname`=(?)", [$CPU_specs,$hostname]);
        $connection->close();
        return $status;
    }
    
    function updateRAM_total($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $RAM_total = $args[1];
        $status = $connection->run("UPDATE `hardware` SET `RAM_total`=(?) WHERE `hostname`=(?)", [$RAM_total,$hostname]);
        $connection->close();
        return $status;
    }
    
    function updateRAM_specs($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $RAM_specs = $args[1];
        $status = $connection->run("UPDATE `hardware` SET `RAM_specs`=(?) WHERE `hostname`=(?)", [$RAM_specs,$hostname]);
        $connection->close();
        return $status;
    }
    
    function updateStoragePrimary($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $storage = $args[1];
        $status = $connection->run("UPDATE `hardware` SET `storage-primary`=(?) WHERE `hostname`=(?)", [$storage,$hostname]);
        $connection->close();
        return $status;
    }
    
    function updateStorageAdditional($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $storage = $args[1];
        $status = $connection->run("UPDATE `hardware` SET `storage-additional`=(?) WHERE `hostname`=(?)", [$storage,$hostname]);
        $connection->close();
        return $status;
    }
    
    function updateChipset($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $chipset = $args[1];
        $status = $connection->run("UPDATE `hardware` SET `chipset`=(?) WHERE `hostname`=(?)", [$chipset,$hostname]);
        $connection->close();
        return $status;
    }
    
    function updateBIOS_ver($args)
    {
        $connection = new DBcon();
        $hostname = $args[0];
        $BIOS_ver = $args[1];
        $status = $connection->run("UPDATE `hardware` SET `BIOS_ver`=(?) WHERE `hostname`=(?)", [$BIOS_ver,$hostname]);
        $connection->close();
        return $status;
    }    
    
    function tables ()
    {        
        $connection = new DBcon();
        $tables = $connection->getAll();
        $ht     = $connection->getHelperTables();
        $connection->close();
        
        $data = array(
            "tables"   => $tables,
            "ht"       => $ht,
        );
        
        return $data;
    }
    
    function getTable($args)
    {
        $name = $args[0];
        $connection = new DBcon();
        $get = $connection->getTable($name);
        $connection->close();
        return $get;    
    }
    
    function getHelperTable($args)
    {
        $ht = $args[0];
        $connection = new DBcon();
        $get = $connection->getHelperTable($ht);
        $connection->close();
        return $get;
    }
    
    $functionCalled = "";
    $arguments = "";
    $functionExists = false;
    $result = "";
    $username = "";
    $password = "";
    
    $functions = array(
            "tables"          => "tables",
            "addHardware"     => "addHardware",
            "addHost"         => "addHost",
            "addKey"          => "addKey",
            "addMac"          => "addMac",
            "addModel"        => "addModel",
            "addNote"         => "addNote",
            "addUser"         => "addUser",
            "addVendor"       => "addVendor",
            "authenticate"    => "authenticate",
            "buildHosts"      => "buildHosts",
            "buildKeys"       => "buildKeys",
            "delHardware"     => "delHardware",
            "delHost"         => "delHost",
            "delKey"          => "delKey",
            "delMac"          => "delMac",
            "delModel"        => "delModel",
            "delNote"         => "delNote",  
            "delUser"         => "delUser",
            "delVendor"       => "delVendor",
            "freeHost"        => "freeHost",
            "freeKeyViaHost"  => "freeKeyViaHost",
            "freeKey"         => "freeKey",
            "freeMac"         => "freeMac",
            "getTable"        => "getTable",
            "getHelperTable"  => "getHelperTable",
            "upDate"          => "upDate",
            "updateAdminPIN"  => "updateAdminPIN",
            "updateBIOS_ver"  => "updateBIOS_ver",
            "updateCPU"       => "updateCPU",
            "updateCPU_specs" => "updateCPU_specs",
            "updateChipset"   => "updateChipset",
            "updateGraphics"  => "updateGraphics",
            "updateHostModel" => "updateHostModel",
            "updateHostname"  => "updateHostname",
            "updateKey"       => "updateKey",
            "updateKeyHost"   => "updateKeyHost",
            "updateKeyStatus" => "updateKeyStatus",
            "updateKeySticker" => "updateKeySticker",
            "updateKeyType"   => "updateKeyType",
            "updateMac"       => "updateMac",
            "updateMacHost"   => "updateMacHost",
            "updateModel"     => "updateModel",
            "updateModelVendor" => "updateModelVendor",
            "updateMotherboard" => "updateMotherboard",
            "updateNote"      => "updateNote",
            "updatePTHost"    => "updatePTHost",
            "updateRAM_specs" => "updateRAM_specs",
            "updateRAM_total" => "updateRAM_total",
            "updateSTSNHost"  => "updateSTSNHost",
            "updateStorageAdditional" => "updateStorageAdditional",
            "updateStoragePrimary" => "updateStoragePrimary",
            "updateTypeHost"  => "updateTypeHost",
            "updateUserHost"  => "updateUserHost",
            "updateVendor"    => "updateVendor"
        );
    
    if(isset($_POST["function"]))
    {
        $functionCalled = $_POST["function"];
    }
    
    if(isset($_POST["args"]))
    {
        $arguments = $_POST["args"];
    }
    
    foreach($functions as $i => $row)
    {
        if($functions[$i] === $functionCalled)
        {
            $functionExists = true;
        }
    }
    
    if($functionExists)
    {
        if($arguments != "")
        {
            $result = $functionCalled($arguments);
        }
        else
        {
            $result = $functionCalled();
        }
    }
   
    echo json_encode(['result'=>$result, 'function'=>$functionCalled, 'args'=>$arguments]);
?>