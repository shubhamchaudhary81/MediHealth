<?php
include_once('config/configdatabase.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Drop the existing doctor_schedule table
$drop_table = "DROP TABLE IF EXISTS doctor_schedule";
if ($conn->query($drop_table)) {
    echo "Dropped existing doctor_schedule table\n";
} else {
    echo "Error dropping table: " . $conn->error . "\n";
    exit;
}

// Create the doctor_schedule table
$create_table = "CREATE TABLE doctor_schedule (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id VARCHAR(20) NOT NULL,
    day VARCHAR(20) NOT NULL,
    time_slots TEXT NOT NULL,
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE
)";

if ($conn->query($create_table)) {
    echo "Created doctor_schedule table\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
    exit;
}

// Get all doctors and their schedules
$doctor_query = "SELECT doctor_id, name, schedule FROM doctor";
$doctor_result = $conn->query($doctor_query);

if ($doctor_result->num_rows > 0) {
    echo "\nProcessing " . $doctor_result->num_rows . " doctors:\n";
    
    while ($doctor = $doctor_result->fetch_assoc()) {
        echo "\nDoctor: " . $doctor['name'] . " (ID: " . $doctor['doctor_id'] . ")\n";
        
        if (!empty($doctor['schedule'])) {
            // Parse the schedule string
            $schedule_lines = explode("\n", $doctor['schedule']);
            
            foreach ($schedule_lines as $line) {
                if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                    $day = trim($matches[1]);
                    $times = array_map('trim', explode(',', $matches[2]));
                    
                    // Format time slots
                    $formatted_times = array();
                    foreach ($times as $time) {
                        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $time_matches)) {
                            $hours = $time_matches[1];
                            $minutes = $time_matches[2];
                            $formatted_times[] = sprintf("%02d:%02d:00", $hours, $minutes);
                        } else {
                            $formatted_times[] = $time;
                        }
                    }
                    
                    $time_slots = implode(',', $formatted_times);
                    
                    // Insert into doctor_schedule table
                    $insert_query = "INSERT INTO doctor_schedule (doctor_id, day, time_slots) VALUES (?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("sss", $doctor['doctor_id'], $day, $time_slots);
                    
                    if ($insert_stmt->execute()) {
                        echo "Added schedule for $day\n";
                    } else {
                        echo "Error adding schedule for $day: " . $insert_stmt->error . "\n";
                    }
                }
            }
        } else {
            echo "No schedule found, using default schedule\n";
            
            // Use default schedule
            $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
            $default_time_slots = array(
                '09:00:00', '09:30:00', '10:00:00', '10:30:00', 
                '11:00:00', '11:30:00', '12:00:00', '12:30:00', 
                '14:00:00', '14:30:00', '15:00:00', '15:30:00', 
                '16:00:00', '16:30:00', '17:00:00'
            );
            
            foreach ($days as $day) {
                $time_slots = implode(',', $default_time_slots);
                
                $insert_query = "INSERT INTO doctor_schedule (doctor_id, day, time_slots) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param("sss", $doctor['doctor_id'], $day, $time_slots);
                
                if ($insert_stmt->execute()) {
                    echo "Added default schedule for $day\n";
                } else {
                    echo "Error adding default schedule for $day: " . $insert_stmt->error . "\n";
                }
            }
        }
    }
} else {
    echo "No doctors found in the database\n";
}

$conn->close();
?> 