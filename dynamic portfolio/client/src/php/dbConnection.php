<?php
$servername = "127.0.0.1";   // match phpMyAdmin
$username   = "root";
$password   = "";             // empty, as config shows
$dbname     = "smart_portfolio";
$port       = 4306;           // important: match your config

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
