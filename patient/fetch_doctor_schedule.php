<?php
include_once('../config/configdatabase.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (isset($_POST['doctor_id'])) {
    $doctor_id = $_POST['doctor_id'];
    $day = isset($_POST['day']) ? $_POST['day'] : null;
    
    error_log("Fetching schedule for doctor_id: " . $doctor_id . ", day: " . $day);
    
    if ($day) {
        // Fetch specific day's schedule
        $stmt = $conn->prepare("SELECT time_slots FROM doctor_schedule WHERE doctor_id = ? AND day = ?");
        $stmt->bind_param("ss", $doctor_id, $day);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $schedule = $result->fetch_assoc();
            error_log("Found schedule for day: " . $day);
            echo json_encode($schedule);
        } else {
            // If no schedule found in doctor_schedule table, check doctor table
            $stmt = $conn->prepare("SELECT schedule FROM doctor WHERE doctor_id = ?");
            $stmt->bind_param("s", $doctor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $doctor = $result->fetch_assoc();
                $schedule_str = $doctor['schedule'];
                
                // Parse the schedule string to find the day's time slots
                $schedule_parts = explode('|', $schedule_str);
                foreach ($schedule_parts as $part) {
                    if (strpos($part, $day) !== false) {
                        $time_slots = explode(':', $part)[1];
                        error_log("Found time slots in doctor table: " . $time_slots);
                        echo json_encode(['time_slots' => $time_slots]);
                        exit;
                    }
                }
            }
            
            error_log("No schedule found for day: " . $day);
            echo json_encode(['time_slots' => '']);
        }
    } else {
        // Fetch all available days
        $stmt = $conn->prepare("SELECT DISTINCT day FROM doctor_schedule WHERE doctor_id = ?");
        $stmt->bind_param("s", $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $days = [];
        while ($row = $result->fetch_assoc()) {
            $days[] = ['day' => $row['day']];
        }
        
        if (empty($days)) {
            // If no days found in doctor_schedule table, check doctor table
            $stmt = $conn->prepare("SELECT schedule FROM doctor WHERE doctor_id = ?");
            $stmt->bind_param("s", $doctor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $doctor = $result->fetch_assoc();
                $schedule_str = $doctor['schedule'];
                
                // Parse the schedule string to get available days
                $schedule_parts = explode('|', $schedule_str);
                foreach ($schedule_parts as $part) {
                    $day = explode(':', $part)[0];
                    $days[] = ['day' => $day];
                }
            }
        }
        
        error_log("Available days: " . json_encode($days));
        echo json_encode($days);
    }
} else {
    error_log("No doctor_id provided");
    echo json_encode(['error' => 'Doctor ID is required']);
}

$conn->close();
?> 