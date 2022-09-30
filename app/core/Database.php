<?php

namespace app\core;

use Aws\SecretsManager\SecretsManagerClient; 
use Aws\Exception\AwsException;

// var AWS = require('aws-sdk'),
//     region = "us-east-2",
//     secretName = "Jaunt-Database-Credentials",
//     secret,
//     decodedBinarySecret;


class Database
{
    public \PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = new \PDO($config["dsn"], $config['user'], $config['password']);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }
}