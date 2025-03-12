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
$sql = "CREATE TABLE IF NOT EXISTS patients (
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

// -- Create Hospital Table
$sql = "CREATE TABLE IF NOT EXISTS hospital (
   id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    district VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    contact_number VARCHAR(15) NOT NULL
)";
if (mysqli_query($conn, $sql)) {
    // echo "Table 'hospital' created successfully.";
} else {
    echo "Error creating 'hospital' table: " . mysqli_error($conn);
}

// Insert Sample Data into Hospital Table
// $sql = "INSERT IGNORE INTO hospital (hospital_id, name, location) VALUES
// (1, 'City Hospital', 'New York'),
// (2, 'Green Valley Hospital', 'Los Angeles'),
// (3, 'Sunrise Medical Center', 'Chicago')";
// if (mysqli_query($conn, $sql)) {
//     // echo "Hospital data inserted successfully.";
// } else {
//     echo "Error inserting hospital data: " . mysqli_error($conn);
// }

//    Create Department Table
$sql = "CREATE TABLE IF NOT EXISTS department (
    department_id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    hospital_id INT,
    FOREIGN KEY (hospital_id) REFERENCES hospital(hospital_id)
)";
if (mysqli_query($conn, $sql)) {
    // echo "Table 'department' created successfully.";
} else {
    echo "Error creating 'department' table: " . mysqli_error($conn);
}

// Insert Sample Data into Department Table
$sql = "INSERT IGNORE INTO department (department_id, name, hospital_id) VALUES
(1, 'Cardiology', 1),
(2, 'Neurology', 2),
(3, 'Orthopedics', 3),
(4, 'Pediatrics', 1),
(5, 'General Surgery', 2)";
if (mysqli_query($conn, $sql)) {
    // echo "Department data inserted successfully.";
} else {
    echo "Error inserting department data: " . mysqli_error($conn);
}

// -- Create Doctor Table
$sql = "CREATE TABLE IF NOT EXISTS doctor (
    doctor_id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    specialization VARCHAR(255) NOT NULL,
    hospital_id INT,
    department_id INT,
    FOREIGN KEY (hospital_id) REFERENCES hospital(hospital_id),
    FOREIGN KEY (department_id) REFERENCES department(department_id)
)";
if (mysqli_query($conn, $sql)) {
    // echo "Table 'doctor' created successfully.";
} else {
    echo "Error creating 'doctor' table: " . mysqli_error($conn);
}

// Insert Sample Data into Doctor Table
$sql = "INSERT IGNORE INTO doctor (doctor_id, name, specialization, hospital_id, department_id) VALUES
(1, 'Dr. John Smith', 'Cardiologist', 1, 1),
(2, 'Dr. Alice Brown', 'Neurologist', 2, 2),
(3, 'Dr. Michael Johnson', 'Orthopedic Surgeon', 3, 3),
(4, 'Dr. Emma Wilson', 'Pediatrician', 1, 4),
(5, 'Dr. Robert Davis', 'General Surgeon', 2, 5)";
if (mysqli_query($conn, $sql)) {
    // echo "Doctor data inserted successfully.";
} else {
    echo "Error inserting doctor data: " . mysqli_error($conn);
}


// $sql = "CREATE TABLE IF NOT EXISTS doctor (
//     doctor_id INT PRIMARY KEY,
//     doctoruserid INT UNIQUE NOT NULL,
//     password VARCHAR(255) NOT NULL,
//     name VARCHAR(255) NOT NULL,
//     specialization VARCHAR(255) NOT NULL,
//     hospital_id INT,
//     department_id INT,
//     FOREIGN KEY (hospital_id) REFERENCES hospital(hospital_id),
//     FOREIGN KEY (department_id) REFERENCES department(department_id)
// )";
// if (mysqli_query($conn, $sql)) {
//     // echo "Table 'doctor' created successfully.";
// } else {
//     echo "Error creating 'doctor' table: " . mysqli_error($conn);
// }

// // Insert Sample Data into Doctor Table
// $sql = "INSERT IGNORE INTO doctor (doctor_id, doctoruserid, password, name, specialization, hospital_id, department_id) VALUES
// (1, 1001, '123456789', 'Dr. John Smith', 'Cardiologist', 1, 1),
// (2, 1002, '987456321', 'Dr. Alice Brown', 'Neurologist', 2, 2),
// (3, 1003, '456789123', 'Dr. Michael Johnson', 'Orthopedic Surgeon', 3, 3)";
// // -- (4, 1004, 'hashed_password_4', 'Dr. Emma Wilson', 'Pediatrician', 1, 4),
// // -- (5, 1005, 'hashed_password_5', 'Dr. Robert Davis', 'General Surgeon', 2, 5)
// if (mysqli_query($conn, $sql)) {
//     // echo "Doctor data inserted successfully.";
// } else {
//     echo "Error inserting doctor data: " . mysqli_error($conn);
// }

// Creating Appointment Table

$sql = "CREATE TABLE IF NOT EXISTS appointment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userid INT NOT NULL,
    hospital_id INT NOT NULL,
    department_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time VARCHAR(10) NOT NULL,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userid) REFERENCES patients(userid), 
    FOREIGN KEY (hospital_id) REFERENCES hospital(hospital_id), 
    FOREIGN KEY (department_id) REFERENCES department(department_id), 
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id)
)";

if (mysqli_query($conn, $sql)) {
    // echo "Table 'appointment' created successfully.";
} else {
    echo "Error creating 'appointment' table: " . mysqli_error($conn);
}

// Create table for Admin
$sql = "CREATE TABLE IF NOT EXISTS admin(
    aid INT PRIMARY KEY AUTO_INCREMENT,
    adminusername VARCHAR(30) NOT NULL,
    adminpassword VARCHAR(255) NOT NULL
)";
if (mysqli_query($conn, $sql)) {
    // echo "Table 'admin' created successfully.";
} else {
    echo "Error creating 'admin' table: " . mysqli_error($conn);
}

// Insert default admin user (INSERT IGNORE ensures no duplicate entry if it already exists)
$sql = "INSERT IGNORE INTO admin (aid, adminusername, adminpassword) VALUES (101, 'sumit', 'sumit123')";
if (mysqli_query($conn, $sql)) {
    // echo "Admin user inserted successfully.";
} else {
    echo "Error inserting admin user: " . mysqli_error($conn);
}
// Insert default admin user (INSERT IGNORE ensures no duplicate entry if it already exists)
$sql = "INSERT IGNORE INTO admin (aid, adminusername, adminpassword) VALUES (102, 'kushal', 'kushal123')";
if (mysqli_query($conn, $sql)) {
    // echo "Admin user inserted successfully.";
} else {
    echo "Error inserting admin user: " . mysqli_error($conn);
}

?>
