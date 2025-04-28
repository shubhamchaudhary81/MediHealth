<?php
include_once('config/configdatabase.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get all schedules with doctor information
$query = "SELECT ds.*, d.name as doctor_name 
          FROM doctor_schedule ds 
          JOIN doctor d ON ds.doctor_id = d.doctor_id 
          ORDER BY ds.doctor_id, FIELD(ds.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $current_doctor = '';
    
    while ($row = $result->fetch_assoc()) {
        if ($current_doctor != $row['doctor_id']) {
            if ($current_doctor != '') {
                echo "\n-------------------\n";
            }
            echo "\nDoctor: " . $row['doctor_name'] . " (ID: " . $row['doctor_id'] . ")\n";
            $current_doctor = $row['doctor_id'];
        }
        
        echo "\nDay: " . $row['day'] . "\n";
        
        // Format time slots for display
        $time_slots = explode(',', $row['time_slots']);
        echo "Time slots:\n";
        foreach ($time_slots as $time) {
            // Convert to 12-hour format
            $time_obj = DateTime::createFromFormat('H:i:s', $time);
            if ($time_obj) {
                echo "  " . $time_obj->format('h:i A') . "\n";
            } else {
                echo "  " . $time . "\n";
            }
        }
    }
} else {
    echo "No schedules found in the database\n";
}

$conn->close();
?> 