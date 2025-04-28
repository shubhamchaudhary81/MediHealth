<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a hospital admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hospital_admin') {
    header("Location: hospitaladminlogin.php");
    exit();
}

// Include database connection
require_once('../config/configdatabase.php');

// Get hospital admin ID from session
$admin_id = $_SESSION['user_id'];

// Check if doctor_id is provided
if (!isset($_POST['doctor_id'])) {
    header("Location: doctors.php");
    exit();
}

$doctor_id = $_POST['doctor_id'];

// Verify that the doctor belongs to the admin's hospital
$verify_query = "SELECT d.doctor_id FROM doctor d 
                JOIN hospitaladmin ha ON d.hospitalid = ha.hospitalid 
                WHERE d.doctor_id = ? AND ha.adminid = ?";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("si", $doctor_id, $admin_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    header("Location: doctors.php");
    exit();
}

// Process schedule update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schedule'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete existing schedules for this doctor
        $delete_query = "DELETE FROM doctor_schedule WHERE doctor_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("s", $doctor_id);
        $delete_stmt->execute();
        
        // Process schedule data
        $schedule_data = json_decode($_POST['schedule'], true);
        $formatted_schedule = [];
        
        foreach ($schedule_data as $day => $times) {
            if (!empty($times)) {
                $formatted_schedule[] = $day . ': ' . implode(', ', $times);
                
                // Format times to HH:MM:SS
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
                $insert_stmt->bind_param("sss", $doctor_id, $day, $time_slots);
                $insert_stmt->execute();
            }
        }
        
        // Update the schedule in the doctor table
        $schedule = implode("\n", $formatted_schedule);
        $update_query = "UPDATE doctor SET schedule = ? WHERE doctor_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $schedule, $doctor_id);
        $update_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect with success message
        header("Location: view-doctor-schedule.php?doctor_id=" . $doctor_id . "&success=1");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        header("Location: view-doctor-schedule.php?doctor_id=" . $doctor_id . "&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // If not a POST request or schedule not provided
    header("Location: view-doctor-schedule.php?doctor_id=" . $doctor_id);
    exit();
}

$conn->close();
?> 