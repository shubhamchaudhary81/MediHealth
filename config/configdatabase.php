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

//create hospital table
$hospitalTableSql = "CREATE TABLE IF NOT EXISTS hospital (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    location VARCHAR(150) NOT NULL,
    website VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($hospitalTableSql) === TRUE) {
    // echo "Table 'hospital' created successfully<br>";
} else {
    // echo "Error creating 'hospital' table: " . $conn->error . "<br>";
}

// Create 'hospitaldepartment' table
// This table will link hospitals to their departments
$hospitalDeptTableSql = "CREATE TABLE IF NOT EXISTS hospitaldepartment (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    hospitalid INT(11) NOT NULL,
    department_id INT(11) NOT NULL,
    FOREIGN KEY (hospitalid) REFERENCES hospital(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES department(department_id) ON DELETE CASCADE
)";
if ($conn->query($hospitalDeptTableSql) === TRUE) {
    // echo "Table 'hospitaldepartment' created successfully<br>";
} else {
    // echo "Error creating 'hospitaldepartment' table: " . $conn->error . "<br>";
}

// Create 'hospitaladmin' table
// This table will store hospital admin details
$hospitalAdminTableSql = "CREATE TABLE IF NOT EXISTS hospitaladmin (
    adminid INT(11) AUTO_INCREMENT PRIMARY KEY,
    hospitalid INT(11) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospitalid) REFERENCES hospital(id) ON DELETE CASCADE
)";
if ($conn->query($hospitalAdminTableSql) === TRUE) {
    // echo "Table 'hospitaladmin' created successfully<br>";
} else {
    // echo "Error creating 'hospitaladmin' table: " . $conn->error . "<br>";
}

// $conn->close();
?>
