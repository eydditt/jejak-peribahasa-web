<?php
$servername = "localhost";
$username = "root"; // Default username for local servers
$password = "";     // Default password for local servers (leave empty)
$dbname = "peribahasa";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
?>
