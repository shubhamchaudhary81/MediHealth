<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create appointments table if it doesn't exist
$createAppointmentsTable = "CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    patient_id INT(11) NOT NULL,
    hospital_id INT(11) NOT NULL,
    department_id INT(11) NOT NULL,
    doctor_id VARCHAR(50) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patientID),
    FOREIGN KEY (hospital_id) REFERENCES hospital(id),
    FOREIGN KEY (department_id) REFERENCES department(department_id),
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id)
)";

if (!$conn->query($createAppointmentsTable)) {
    error_log("Error creating appointments table: " . $conn->error);
}
?> 