<?php
include_once('config/configdatabase.php');

// Create doctor_schedule table
$create_table_query = "CREATE TABLE IF NOT EXISTS doctor_schedule (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY, 
    doctor_id VARCHAR(20) NOT NULL, 
    day VARCHAR(20) NOT NULL, 
    time_slots TEXT NOT NULL, 
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE
)";

if ($conn->query($create_table_query)) {
    echo "Doctor schedule table created successfully.";
} else {
    echo "Error creating doctor schedule table: " . $conn->error;
}

$conn->close();
?> 