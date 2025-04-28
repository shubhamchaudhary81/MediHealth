<?php
include_once('config/configdatabase.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check records in doctor_schedule table
$query = "SELECT ds.*, d.name as doctor_name, d.schedule as doctor_schedule
          FROM doctor_schedule ds 
          JOIN doctor d ON ds.doctor_id = d.doctor_id";
$result = $conn->query($query);

if ($result === false) {
    echo "Error executing query: " . $conn->error . "\n";
} else {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " schedule records:\n\n";
        
        while ($row = $result->fetch_assoc()) {
            echo "Doctor: " . $row['doctor_name'] . " (ID: " . $row['doctor_id'] . ")\n";
            echo "Day: " . $row['day'] . "\n";
            echo "Time slots: " . $row['time_slots'] . "\n";
            echo "Original schedule from doctor table: " . ($row['doctor_schedule'] ? "\n" . $row['doctor_schedule'] : 'No schedule') . "\n";
            echo "-------------------\n";
        }
    } else {
        echo "No records found in doctor_schedule table.\n";
        
        // Check if there are any doctors in the doctor table
        $doctor_query = "SELECT doctor_id, name, schedule FROM doctor";
        $doctor_result = $conn->query($doctor_query);
        
        if ($doctor_result->num_rows > 0) {
            echo "\nFound " . $doctor_result->num_rows . " doctors in doctor table:\n\n";
            
            while ($row = $doctor_result->fetch_assoc()) {
                echo "Doctor: " . $row['name'] . " (ID: " . $row['doctor_id'] . ")\n";
                echo "Schedule: " . ($row['schedule'] ? "\n" . $row['schedule'] : 'No schedule') . "\n";
                echo "-------------------\n";
            }
        } else {
            echo "\nNo doctors found in doctor table.\n";
        }
    }
}

$conn->close();
?> 