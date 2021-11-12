<?php

namespace app\core;

class Mysql
{
    static function query($query)
    {
        $database = $config['database'] ?? '';
        $host = $config['host'] ?? '';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';

        $connection = mysqli_connect($host, $user, $password, $database); 
        mysqli_query($connection, $query);
    }
}
