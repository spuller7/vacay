<?php

include 'config.php';
class DBcon
{
    // array of hardcoded selects
    // equivalent to SELECT * from $table, but faster
    // public so it can be used by multiple functions
    public $dbcon;
    public $stmts = array(
            "hardware"        => "SELECT hostname, motherboard, graphics, CPU, `CPU_specs`, `RAM_total`, `RAM_specs`, `storage-primary`, `storage-additional`, chipset, `BIOS_ver` FROM hardware",
            "hosts"           => "SELECT hostID, hostname, adminPIN, `serviceTag-serialNumber`, modelID, typeID, propertyTag, deploydate, ldapUID FROM hosts",
            "hosttype"        => "SELECT typeID, hostType FROM hosttype",
            "macaddresses"    => "SELECT macAddress, hostID FROM macaddresses",
            "models"          => "SELECT modelID, vendorID, model FROM models",
            "note"            => "SELECT noteID, ldapUID, `date-note`, hostID, `text-note` FROM note",
            "status"          => "SELECT statusID, status FROM status",
            "vendors"         => "SELECT vendorID, vendor FROM vendors",
            "windowskeys"     => "SELECT windowsKey, hostID, hostIDSticker, winVerID, statusID FROM windowskeys",
            "windowsversions" => "SELECT winVerID, windowsVersion FROM windowsversions",
        );
    // array of hardcoded joins
    // public so it can be used by multiple functions
    public $helpers = array(
            "macHostname"      => "SELECT macaddresses.macAddress, hosts.hostname FROM macaddresses LEFT OUTER JOIN hosts ON macaddresses.hostID=hosts.hostID",
            "vendorModels"     => "SELECT models.modelID, vendors.vendor, models.model FROM models INNER JOIN vendors ON models.vendorID=vendors.vendorID",
            "hostKeys"         => "SELECT windowskeys.windowsKey, hosts.hostname, hosts.hostID FROM windowskeys INNER JOIN hosts ON windowskeys.hostID=hosts.hostID",
            "hostStickers"     => "SELECT windowskeys.windowsKey, hosts.hostname FROM windowskeys INNER JOIN hosts ON windowskeys.hostIDSticker=hosts.hostID",
            "winVers"          => "SELECT windowskeys.windowsKey, windowsversions.windowsVersion FROM windowskeys INNER JOIN windowsversions ON windowskeys.winVerID=windowsversions.winVerID",
            "VMmod"            => "SELECT models.model, vendors.vendor FROM vendors INNER JOIN models on vendors.vendorID=models.vendorID"
        );
    
    public function __construct()
    {   
        // defined in config.php
        $host     = HOST;
        $username = USERNAME;
        $password = PASSWORD;
        $db       = DB;
        $port     = PORT;
        $opt      = [];
        
        // set default fetch mode and set exceptions as errormode
        $options = [
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        
        // use options array
        $opt = array_replace($options, $opt);
        // prepare db connect sql
        $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8";
        
        try
        {
            $this->dbcon = new PDO($dsn, $username, $password, $opt);    
        } 
        catch (PDOException $ex) 
        {
            throw new PDOException($ex->getMessage(), $ex->getCode());
        }
    }
    
    public function getAll()
    {        
        // all tables array init
        $tables = array (
            "hardware"        => NULL,
            "hosts"           => NULL, 
            "hosttype"        => NULL, 
            "macaddresses"    => NULL, 
            "models"          => NULL, 
            "note"            => NULL, 
            "status"          => NULL,
            "vendors"         => NULL, 
            "windowskeys"     => NULL, 
            "windowsversions" => NULL
        );
        
        // put the table returned by each statement into an array of tables
        foreach ($this->stmts as $i => $row)
        {
            // execute hardcoded SELECT statement
            $stmt = $this->dbcon->prepare($this->stmts[$i]);
            $stmt->execute();
            $table = $stmt->fetchAll();
            
            // if table was retrieved, add it to tables array
            if($table)
            {
                $tables[$i] = $table;
            }
        }
        
        return $tables;
    }
    
    public function getHelperTables()
    {    
        // joined table array init
        $helperTables = array (
            "macHostname"      => NULL, 
            "vendorModels"     => NULL,
            "hostKeys"         => NULL, 
            "hostStickers"     => NULL, 
            "winVers"          => NULL,
            "VMmod"            => NULL 
        );
        
        foreach($this->helpers as $i => $row)
        {
            // execute hardcoded SELECT with JOIN statement
            $stmt = $this->dbcon->prepare($this->helpers[$i]);
            $stmt->execute();
            $join = $stmt->fetchAll();
            
            // if table was retrieved, add it to tables array
            if($join)
            {
                $helperTables[$i] = $join;
            }
        }    

        return $helperTables;
    }
    
    public function getHelperTable($ht)
    {
        $stmt = "";
        foreach($this->helpers as $key => $value)
        {
            if($key === $ht)
            {
                $stmt = $value;
            }
        }
        $query = $this->dbcon->prepare($stmt);
        $query->execute();
        $get = $query->fetchAll();
        return $get;
    }
    
    public function getTable($table)
    {
        // pre-format the prepared statement
        // PDO prevents variable table and field names
        // this string is now "prepared"
        $stmt = "SELECT * FROM $table";
        $query = $this->dbcon->prepare($stmt);
        $query->execute();
        $get = $query->fetchAll();
        return $get;
    }
    
    public function getRows ($table, $fields, $where)
    {
        // pre-format the prepared statement
        // sneakily subvert PDO via programmer magic
        $stmt = "SELECT $fields FROM $table WHERE $where";
        $query = $this->dbcon->prepare($stmt);
        $query->execute();
        $get = $query->fetchAll();
        return $get;
    }
    
    public function run ($sql, $args = NULL )
    {
        // execute string without variables
        if($args == NULL)
        {
            $stmt = $this->dbcon->prepare($sql);
            return $stmt->execute();
        }
        
        // prepare statement, execute with args
        $stmt = $this->dbcon->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
    
    public function close()
    {
        $this->dbcon = NULL;
    }        
}

?>