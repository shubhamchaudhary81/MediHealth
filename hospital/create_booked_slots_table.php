<?php
include_once('../config/configdatabase.php');

// Create booked_slots table
$sql = "CREATE TABLE IF NOT EXISTS booked_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id VARCHAR(20) NOT NULL,
    appointment_date DATE NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    booked_count INT DEFAULT 0,
    max_patients INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table booked_slots created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 