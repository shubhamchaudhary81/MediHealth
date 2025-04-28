<?php
include_once('config/configdatabase.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if doctor_schedule table exists
$check_table = "SHOW TABLES LIKE 'doctor_schedule'";
$result = $conn->query($check_table);

if ($result->num_rows == 0) {
    echo "Creating doctor_schedule table...\n";
    
    // Create the table
    $create_table = "CREATE TABLE doctor_schedule (
        schedule_id INT AUTO_INCREMENT PRIMARY KEY,
        doctor_id VARCHAR(20) NOT NULL,
        day VARCHAR(20) NOT NULL,
        time_slots TEXT NOT NULL,
        FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE
    )";
    
    if ($conn->query($create_table)) {
        echo "Table created successfully\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
} else {
    echo "Table exists, checking structure...\n";
    
    // Get table structure
    $describe = "DESCRIBE doctor_schedule";
    $result = $conn->query($describe);
    
    echo "Current table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . $row['Key'] . "\n";
    }
}

$conn->close();
?> 