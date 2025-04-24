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

// Step 4: Create 'patients' table
$tableSql = "CREATE TABLE IF NOT EXISTS patients (
    patientID INT(11) AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    number VARCHAR(15) NOT NULL,
    dob DATE NOT NULL,
    zone VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    bloodgroup VARCHAR(5) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($tableSql) === TRUE) {
    // echo "Table 'patients' created successfully<br>";
} else {
    // echo "Error creating 'patients' table: " . $conn->error . "<br>";
}

// // Step 5: Create 'department' table
// $deptSql = "CREATE TABLE IF NOT EXISTS department (
//     department_id INT AUTO_INCREMENT PRIMARY KEY,
//     department_name VARCHAR(100) NOT NULL UNIQUE
// )";
// if ($conn->query($deptSql) === TRUE) {
//     echo "Table 'department' created successfully<br>";
// } else {
//     echo "Error creating 'department' table: " . $conn->error . "<br>";
// }

// // Step 6: Insert department data
// $insertDept = "INSERT IGNORE INTO department (department_name) VALUES
// ('Cardiology'),
// ('Neurology'),
// ('Orthopedics'),
// ('Pediatrics'),
// ('General Surgery'),
// ('Dermatology'),
// ('Radiology'),
// ('Psychiatry'),
// ('ENT (Ear, Nose, Throat)'),
// ('Anesthesiology'),
// ('Ophthalmology'),
// ('Urology'),
// ('Gastroenterology'),
// ('Nephrology'),
// ('Oncology'),
// ('Gynecology'),
// ('Emergency'),
// ('Dental'),
// ('Physiotherapy'),
// ('Pathology')";
// if ($conn->query($insertDept) === TRUE) {
//     echo "Department data inserted successfully<br>";
// } else {
//     echo "Error inserting department data: " . $conn->error . "<br>";
// }


// Create 'department' table
$sql = "CREATE TABLE IF NOT EXISTS department (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE
)";
if ($conn->query($sql) === TRUE) {
    // echo "Table 'department' created successfully<br>";
} else {
    // echo "Error creating 'department' table: " . $conn->error . "<br>";
}

// Truncate department table to reset IDs
$conn->query("TRUNCATE TABLE department");

// Insert sample department data
$sql = "INSERT INTO department (department_name) VALUES
('Anesthesiology'),
('Cardiology'),
('Dental'),
('Dermatology'),
('Emergency'),
('ENT (Ear, Nose, Throat)'),
('Gastroenterology'),
('General Surgery'),
('Gynecology'),
('Infectious Diseases'),
('Internal Medicine'),
('Nephrology'),
('Neurology'),
('Oncology'),
('Ophthalmology'),
('Orthopedics'),
('Pathology'),
('Pediatrics'),
('Physiotherapy'),
('Plastic Surgery'),
('Psychiatry'),
('Pulmonology'),
('Radiology'),
('Rheumatology'),
('Urology')";
if ($conn->query($sql) === TRUE) {
    // echo "Department data inserted successfully<br>";
} else {
    // echo "Error inserting department data: " . $conn->error . "<br>";
}

// $conn->close();
?>
