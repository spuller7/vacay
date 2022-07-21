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
        $client = new SecretsManagerClient([
            'region' => 'us-east-2',
            'version' => 'latest',
            'credentials' => [
                'key'    => 'AKIAVA5ATUCZ6IXEM4G3',
                'secret' => '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'
            ]
        ]);

        $secretName = 'Jaunt-Database-Credentials';

        try {
            $result = $client->getSecretValue([
                'SecretId' => $secretName,
            ]);
        
        } catch (AwsException $e) {
            $error = $e->getAwsErrorCode();
            if ($error == 'DecryptionFailureException') {
                // Secrets Manager can't decrypt the protected secret text using the provided AWS KMS key.
                // Handle the exception here, and/or rethrow as needed.
                throw $e;
            }
            if ($error == 'InternalServiceErrorException') {
                // An error occurred on the server side.
                // Handle the exception here, and/or rethrow as needed.
                throw $e;
            }
            if ($error == 'InvalidParameterException') {
                // You provided an invalid value for a parameter.
                // Handle the exception here, and/or rethrow as needed.
                throw $e;
            }
            if ($error == 'InvalidRequestException') {
                // You provided a parameter value that is not valid for the current state of the resource.
                // Handle the exception here, and/or rethrow as needed.
                throw $e;
            }
            if ($error == 'ResourceNotFoundException') {
                // We can't find the resource that you asked for.
                // Handle the exception here, and/or rethrow as needed.
                throw $e;
            }
        }

        $data = json_decode($result['SecretString']);
        $dsn = "mysql:host=".$data->host.";port=3306;dbname=application";
        $user = $data->username;
        $password = $data->password;

        $this->pdo = new \PDO($dsn, $user, $password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }
}