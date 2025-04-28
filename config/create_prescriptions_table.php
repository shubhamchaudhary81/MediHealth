<?php
require_once('configdatabase.php');

// Create 'prescriptions' table
$prescriptionsTableSql = "CREATE TABLE IF NOT EXISTS prescriptions (
    prescription_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT(11) NOT NULL,
    doctor_id VARCHAR(20) NOT NULL,
    diagnosis TEXT NOT NULL,
    prescription TEXT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE
)";

if ($conn->query($prescriptionsTableSql) === TRUE) {
    echo "Table 'prescriptions' created successfully<br>";
} else {
    echo "Error creating 'prescriptions' table: " . $conn->error . "<br>";
}

$conn->close();
?> 