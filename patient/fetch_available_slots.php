<?php
session_start();
include_once('../config/configdatabase.php');

header('Content-Type: application/json');

if (!isset($_POST['doctor_id']) || !isset($_POST['day'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

$doctor_id = $_POST['doctor_id'];
$day = $_POST['day'];

// Fetch schedule from doctor_schedule table
$query = "SELECT from_time, to_time, max_patients 
          FROM doctor_schedule 
          WHERE doctor_id = ? AND day = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $doctor_id, $day);
$stmt->execute();
$result = $stmt->get_result();

$available_slots = array();

if ($result->num_rows > 0) {
    while ($schedule = $result->fetch_assoc()) {
        // Count existing appointments for this time slot
        $count_query = "SELECT COUNT(*) as booked_count 
                       FROM appointments 
                       WHERE doctor_id = ? 
                       AND DAYNAME(appointment_date) = ? 
                       AND appointment_time = ? 
                       AND status != 'cancelled'";
        
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->bind_param("sss", $doctor_id, $day, $schedule['from_time']);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $booked = $count_result->fetch_assoc()['booked_count'];
        
        $available = $schedule['max_patients'] - $booked;
        
        $available_slots[] = array(
            'start_time' => date('h:i A', strtotime($schedule['from_time'])),
            'end_time' => date('h:i A', strtotime($schedule['to_time'])),
            'value' => $schedule['from_time'],
            'available' => $available,
            'max_patients' => $schedule['max_patients']
        );
        
        $count_stmt->close();
    }
}

$stmt->close();
echo json_encode($available_slots);
?> 