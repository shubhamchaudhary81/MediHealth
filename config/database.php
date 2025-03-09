<?php
$servername = "localhost"; // Update with your server name
$username = "root"; // Update with your username
$password = ""; // Update with your password

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$dbname = "medihealthdb"; 
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";

if ($conn->query($sql) === TRUE) {
    // echo "Database created successfully or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error); // Stop script if database creation fails
}

// Close the first connection
$conn->close();

// Reconnect with the newly created database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // echo "Connected successfully to database '$dbname'.<br>";
}


// SQL to create the patients table
$sql = "CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userid VARCHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    number VARCHAR(20) NOT NULL UNIQUE,
    dob DATE NOT NULL,
    zone VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    // echo "Table 'patients' created successfully";
} else {
    // echo "Error creating table: " . $conn->error;
}
?>
