<?php
session_start();

// Check if user is logged in and is a hospital admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hospital_admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include database connection
require_once('../config/configdatabase.php');

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['patient_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing patient ID']);
    exit();
}

$patient_id = $data['patient_id'];

// Get hospital admin ID from session
$admin_id = $_SESSION['user_id'];

// Verify that the patient has appointments with the admin's hospital
$verify_query = "SELECT DISTINCT p.* FROM patients p 
                INNER JOIN appointments a ON p.patientID = a.patient_id 
                INNER JOIN hospital h ON a.hospital_id = h.id 
                INNER JOIN hospitaladmin ha ON h.id = ha.hospitalid 
                WHERE p.patientID = ? AND ha.adminid = ?";

if (!($stmt = $conn->prepare($verify_query))) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

if (!$stmt->bind_param("ii", $patient_id, $admin_id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    exit();
}

if (!$stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    exit();
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Patient not found or unauthorized']);
    exit();
}

$stmt->close();

// Start transaction
$conn->begin_transaction();

try {
    // Delete appointments for this patient in this hospital
    $delete_appointments = "DELETE a FROM appointments a 
                          INNER JOIN hospital h ON a.hospital_id = h.id 
                          INNER JOIN hospitaladmin ha ON h.id = ha.hospitalid 
                          WHERE a.patient_id = ? AND ha.adminid = ?";
    
    if (!($stmt = $conn->prepare($delete_appointments))) {
        throw new Exception('Error preparing appointments deletion: ' . $conn->error);
    }
    
    if (!$stmt->bind_param("ii", $patient_id, $admin_id)) {
        throw new Exception('Error binding parameters for appointments deletion: ' . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Error executing appointments deletion: ' . $stmt->error);
    }
    
    $stmt->close();
    
    // Check if patient has any remaining appointments in other hospitals
    $check_remaining = "SELECT COUNT(*) as count FROM appointments WHERE patient_id = ?";
    
    if (!($stmt = $conn->prepare($check_remaining))) {
        throw new Exception('Error preparing remaining check: ' . $conn->error);
    }
    
    if (!$stmt->bind_param("i", $patient_id)) {
        throw new Exception('Error binding parameters for remaining check: ' . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Error executing remaining check: ' . $stmt->error);
    }
    
    $remaining_result = $stmt->get_result();
    $remaining_row = $remaining_result->fetch_assoc();
    
    $stmt->close();
    
    // If patient has no remaining appointments, delete the patient record
    if ($remaining_row['count'] == 0) {
        $delete_patient = "DELETE FROM patients WHERE patientID = ?";
        
        if (!($stmt = $conn->prepare($delete_patient))) {
            throw new Exception('Error preparing patient deletion: ' . $conn->error);
        }
        
        if (!$stmt->bind_param("i", $patient_id)) {
            throw new Exception('Error binding parameters for patient deletion: ' . $stmt->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Error executing patient deletion: ' . $stmt->error);
        }
        
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 