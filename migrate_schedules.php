<?php
include_once('config/configdatabase.php');

// Get all doctors with schedules
$query = "SELECT doctor_id, schedule FROM doctor WHERE schedule IS NOT NULL AND schedule != ''";
$result = $conn->query($query);

$migrated_count = 0;
$error_count = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctor_id = $row['doctor_id'];
        $schedule = $row['schedule'];
        
        // Parse the schedule string
        $schedule_lines = explode("\n", $schedule);
        
        foreach ($schedule_lines as $line) {
            if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                $day = trim($matches[1]);
                $times = array_map('trim', explode(',', $matches[2]));
                
                // Ensure time slots are in the correct format (HH:MM:SS)
                $formatted_times = array();
                foreach ($times as $time) {
                    // If time is in HH:MM format, convert to HH:MM:SS
                    if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $time_matches)) {
                        $hours = $time_matches[1];
                        $minutes = $time_matches[2];
                        $formatted_times[] = sprintf("%02d:%02d:00", $hours, $minutes);
                    } else {
                        $formatted_times[] = $time;
                    }
                }
                
                $time_slots = implode(',', $formatted_times);
                
                // Check if this schedule already exists
                $check_query = "SELECT schedule_id FROM doctor_schedule WHERE doctor_id = ? AND day = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("ss", $doctor_id, $day);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows == 0) {
                    // Insert the schedule
                    $insert_query = "INSERT INTO doctor_schedule (doctor_id, day, time_slots) VALUES (?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("sss", $doctor_id, $day, $time_slots);
                    
                    if ($insert_stmt->execute()) {
                        $migrated_count++;
                    } else {
                        $error_count++;
                        echo "Error migrating schedule for doctor $doctor_id, day $day: " . $insert_stmt->error . "<br>";
                    }
                }
            }
        }
    }
    
    echo "Migration completed. Successfully migrated $migrated_count schedules. Errors: $error_count";
} else {
    echo "No schedules found to migrate.";
}

$conn->close();
?> 