<?php
include_once('../config/configdatabase.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    
    // Get doctor's schedule for the selected date
    $day_of_week = date('l', strtotime($appointment_date));
    $schedule_query = "SELECT from_time, to_time, max_patients 
                      FROM doctor_schedule 
                      WHERE doctor_id = ? AND day = ?";
    
    $stmt = $conn->prepare($schedule_query);
    if ($stmt === false) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("ss", $doctor_id, $day_of_week);
    $stmt->execute();
    $schedule_result = $stmt->get_result();
    
    // Get booked appointments for the selected date
    $booked_query = "SELECT appointment_time, COUNT(*) as booked_count 
                     FROM appointments 
                     WHERE doctor_id = ? 
                     AND appointment_date = ? 
                     AND status != 'cancelled'
                     GROUP BY appointment_time";
    
    $stmt = $conn->prepare($booked_query);
    if ($stmt === false) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("ss", $doctor_id, $appointment_date);
    $stmt->execute();
    $booked_result = $stmt->get_result();
    
    // Create an array of booked appointments with their counts
    $booked_slots = [];
    while ($booked = $booked_result->fetch_assoc()) {
        $booked_slots[$booked['appointment_time']] = $booked['booked_count'];
    }
    
    // Generate available time slots
    $available_slots = [];
    while ($schedule = $schedule_result->fetch_assoc()) {
        $from_time = $schedule['from_time'];
        $to_time = $schedule['to_time'];
        $max_patients = $schedule['max_patients'];
        
        // Format times for display
        $display_from_time = date('h:i A', strtotime($from_time));
        $display_to_time = date('h:i A', strtotime($to_time));
        
        // Check booked count for this time slot
        $booked_count = isset($booked_slots[$from_time]) ? $booked_slots[$from_time] : 0;
        $available_count = $max_patients - $booked_count;
        
        if ($available_count > 0) {
            $available_slots[] = [
                'time' => $display_from_time . ' - ' . $display_to_time,
                'value' => $from_time,
                'available' => $available_count,
                'max_patients' => $max_patients,
                'booked_count' => $booked_count
            ];
        }
    }
    
    // Return the available slots as JSON
    header('Content-Type: application/json');
    echo json_encode($available_slots);
    
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?> 