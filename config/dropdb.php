<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health";

// Step 1: Create connection to MySQL server (no DB selected yet)
$conn = new mysqli($servername, $username, $password);

// Check server connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // echo "Database '$dbname' created successfully<br>";
} else {
    // echo "Error creating database: " . $conn->error . "<br>";
}
$conn->close(); // Close the server connection

// Step 3: Reconnect using the created database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Step 2: Create database if not exists
$sql = "DROP DATABASE $dbname";
if ($conn->query($sql) === TRUE) {
    // echo "Database '$dbname' created successfully<br>";
} else {
    // echo "Error creating database: " . $conn->error . "<br>";
}
$conn->close(); // Close the server connection

// Step 3: Reconnect using the created database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);

    }