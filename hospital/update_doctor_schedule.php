<?php
// Log start of script execution
error_log("update_doctor_schedule.php: Script execution started.");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Temporary: Enable error reporting for debugging. REMOVE THIS IN PRODUCTION.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is a hospital admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hospital_admin') {
    header("Location: hospitaladminlogin.php");
    exit();
}

// Log after session and login check
error_log("update_doctor_schedule.php: Session and login check passed.");

// Include database connection
require_once('../config/configdatabase.php');

// Log after database connection
error_log("update_doctor_schedule.php: Database connection established.");

// Get hospital admin ID from session
$admin_id = $_SESSION['user_id'];

// Check if doctor_id is provided
if (!isset($_POST['doctor_id'])) {
    header("Location: doctors.php");
    exit();
}

$doctor_id = $_POST['doctor_id'];

// Log after getting doctor_id
error_log("update_doctor_schedule.php: Doctor ID received: " . $doctor_id);

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

// Log after doctor verification
error_log("update_doctor_schedule.php: Doctor verification successful.");

// Process schedule update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schedule'])) {
    // Log at the start of POST processing
    error_log("update_doctor_schedule.php: POST request received, processing schedule.");

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Log before deleting existing schedules
        error_log("update_doctor_schedule.php: Deleting existing schedules.");

        // Delete existing schedules for this doctor
        $delete_query = "DELETE FROM doctor_schedule WHERE doctor_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("s", $doctor_id);
        $delete_stmt->execute();
        
        // Process schedule data (new format)
        $schedule_data = json_decode($_POST['schedule'], true);
       
        // Log the received schedule data for debugging
        error_log("Received schedule data for doctor " . $doctor_id . ": " . print_r($schedule_data, true));

        // Check if JSON decoding was successful and data is in the expected format
        if ($schedule_data === null || !is_array($schedule_data)) {
            error_log("Failed to decode schedule JSON or data format is incorrect for doctor " . $doctor_id);
            throw new Exception("Invalid schedule data received.");
        }

        $formatted_schedule = [];
        
        foreach ($schedule_data as $day => $slots) {
            if (!empty($slots) && is_array($slots)) { // Added is_array check for slots
                $day_slots_formatted = [];
                foreach ($slots as $slot) {
                    // Log before inserting slot
                    error_log("update_doctor_schedule.php: Inserting slot for " . $day);

                    // Check if slot data is in the expected format
                    if (!isset($slot['from'], $slot['to'], $slot['capacity']) || !is_numeric($slot['capacity'])) {
                        error_log("Invalid slot data format for doctor " . $doctor_id . " day " . $day . ": " . print_r($slot, true));
                        throw new Exception("Invalid time slot data format.");
                    }

                    // Insert into doctor_schedule table (new columns)
                    $schedule_insert_query = "INSERT INTO doctor_schedule (doctor_id, day, from_time, to_time, max_patients) VALUES (?, ?, ?, ?, ?)";
                    $schedule_stmt = $conn->prepare($schedule_insert_query);
                    
                    // Ensure times are in HH:MM:SS format for database
                    $from_time = $slot['from'];
                    $to_time = $slot['to'];
                    // Append :00 if seconds are missing
                    if (strlen($from_time) === 5) $from_time .= ':00';
                    if (strlen($to_time) === 5) $to_time .= ':00';

                    $schedule_stmt->bind_param("ssssi", $doctor_id, $day, $from_time, $to_time, $slot['capacity']);
                    
                    if (!$schedule_stmt->execute()) {
                        // Log or handle the error if insertion fails for a slot
                        error_log("Failed to insert schedule slot for doctor " . $doctor_id . " on " . $day . " (From: " . $from_time . ", To: " . $to_time . ", Capacity: " . $slot['capacity'] . "): " . $conn->error);
                        throw new Exception("Database error during schedule insertion.");
                    }
                    
                    // Log after successful slot insertion
                    error_log("update_doctor_schedule.php: Successfully inserted slot for " . $day);

                    // Format for the doctor table schedule column
                    $day_slots_formatted[] = $slot['from'] . ' - ' . $slot['to'] . ' (Max Patients: ' . $slot['capacity'] . ')';
                }
                 $formatted_schedule[] = $day . ': ' . implode(', ', $day_slots_formatted);
            }
        }
        
        // Update the schedule in the doctor table (new format)
        $schedule = implode("\n", $formatted_schedule);
        $update_query = "UPDATE doctor SET schedule = ? WHERE doctor_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $schedule, $doctor_id);
        
        // Log before updating doctor table
        error_log("update_doctor_schedule.php: Updating doctor table schedule string.");

        if (!$update_stmt->execute()) {
             // Log or handle the error if updating doctor table fails
            error_log("Failed to update doctor schedule string for doctor " . $doctor_id . ": " . $conn->error);
            throw new Exception("Database error during doctor schedule string update.");
        }

        // Log before committing transaction
        error_log("update_doctor_schedule.php: Committing transaction.");

        // Commit transaction
        $conn->commit();
        
        // Log after successful commit and before redirect
        error_log("update_doctor_schedule.php: Transaction committed successfully. Redirecting.");

        // Redirect with success message
        header("Location: view-doctor-schedule.php?doctor_id=" . $doctor_id . "&success=1");
        exit();
    } catch (Exception $e) {
        // Log before rollback and error redirect
        error_log("update_doctor_schedule.php: Exception caught. Rolling back transaction.");

        // Rollback transaction on error
        $conn->rollback();
        error_log("Schedule update failed for doctor " . $doctor_id . ": " . $e->getMessage()); // Log the exception message
        // Redirect with error message displayed on the view page
        header("Location: view-doctor-schedule.php?doctor_id=" . $doctor_id . "&error=" . urlencode("Failed to update schedule: " . $e->getMessage()));
        exit();
    }
} else {
    // If not a POST request or schedule not provided
    // Log if not a POST request
    error_log("update_doctor_schedule.php: Not a POST request or schedule not provided.");

    header("Location: view-doctor-schedule.php?doctor_id=" . $doctor_id);
    exit();
}

$conn->close();

// Log end of script execution
error_log("update_doctor_schedule.php: Script execution ended.");

?> 