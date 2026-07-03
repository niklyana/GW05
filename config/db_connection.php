<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$username = 'GW05';
$password = '123';
$database = 'GW05';  // ← Guna huruf kecil: md_gw05

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Test connection - optional
// echo "Connected to database: " . $database;
?>
