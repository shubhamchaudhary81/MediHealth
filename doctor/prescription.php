<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

require_once('../config/configdatabase.php');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

// Get the JSON data from the request body
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid data format']);
    exit();
}

// Validate required fields
if (!isset($data['appointment_id']) || !isset($data['diagnosis']) || !isset($data['medications'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get appointment details to get patient_id and hospital_id
    $appointment_query = "SELECT patient_id, hospital_id FROM appointments WHERE appointment_id = ? AND doctor_id = ?";
    $stmt = $conn->prepare($appointment_query);
    $stmt->bind_param("is", $data['appointment_id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();

    if (!$appointment) {
        throw new Exception('Appointment not found or unauthorized');
    }

    // Insert prescription
    $insert_query = "INSERT INTO prescriptions (appointment_id, doctor_id, patient_id, hospital_id, diagnosis, medications) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("isssss", 
        $data['appointment_id'],
        $_SESSION['user_id'],
        $appointment['patient_id'],
        $appointment['hospital_id'],
        $data['diagnosis'],
        $data['medications']
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Error saving prescription');
    }

    // Update appointment status to completed
    $update_query = "UPDATE appointments SET status = 'completed' WHERE appointment_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $data['appointment_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Error updating appointment status');
    }

    // Commit transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Prescription saved successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?> 