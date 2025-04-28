<?php
include_once('config/configdatabase.php');

// Function to update doctor schedule
function updateDoctorSchedule($conn, $doctor_id, $schedule) {
    // First, delete existing schedules for this doctor
    $delete_query = "DELETE FROM doctor_schedule WHERE doctor_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("s", $doctor_id);
    $delete_stmt->execute();
    
    if (empty($schedule)) {
        return true; // No schedule to process
    }
    
    // Parse the schedule string
    $schedule_lines = explode("\n", $schedule);
    $success = true;
    
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
            
            // Insert the schedule
            $insert_query = "INSERT INTO doctor_schedule (doctor_id, day, time_slots) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sss", $doctor_id, $day, $time_slots);
            
            if (!$insert_stmt->execute()) {
                $success = false;
                echo "Error inserting schedule for doctor $doctor_id, day $day: " . $insert_stmt->error . "<br>";
            }
        }
    }
    
    return $success;
}

// Example usage:
// updateDoctorSchedule($conn, "DOC001", "Monday: 9:00, 9:30, 10:00\nTuesday: 14:00, 14:30, 15:00");

$conn->close();
?> 