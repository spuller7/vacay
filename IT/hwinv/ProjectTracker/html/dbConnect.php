<?php

if($DEV == 1) 
{
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

$conn = new mysqli($servername, $username, $password, $db);

if ($conn->connect_error) 
{
	die("Connection failed: " . $conn->connect_error);
}

$db = new DBManager();
$pdo = $db->pdo();
?>