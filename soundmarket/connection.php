<?php
$host = "localhost";
$port = "3306";
$dbname = "soundmarket";
$username = "root";
$password = "";

// Create connection using MySQLi
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Success message
echo "Database connection successful!";
?>