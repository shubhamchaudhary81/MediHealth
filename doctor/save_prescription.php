<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once('../config/configdatabase.php');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['appointment_id']) || !isset($data['diagnosis']) || !isset($data['prescription'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required fields']);
    exit();
}

$appointment_id = $data['appointment_id'];
$diagnosis = $data['diagnosis'];
$prescription = $data['prescription'];
$notes = isset($data['notes']) ? $data['notes'] : '';
$doctor_id = $_SESSION['user_id'];

// First, verify that this appointment belongs to the doctor
$verify_query = "SELECT appointment_id FROM appointments WHERE appointment_id = ? AND CAST(doctor_id AS CHAR) = ?";
$verify_stmt = $conn->prepare($verify_query);
if ($verify_stmt === false) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error preparing verification statement: ' . $conn->error]);
    exit();
}

$verify_stmt->bind_param("ss", $appointment_id, $doctor_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid appointment']);
    exit();
}

// Check if prescription already exists
$check_query = "SELECT prescription_id FROM prescriptions WHERE appointment_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $appointment_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing prescription
    $query = "UPDATE prescriptions SET diagnosis = ?, prescription = ?, notes = ?, updated_at = NOW() WHERE appointment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $diagnosis, $prescription, $notes, $appointment_id);
} else {
    // Insert new prescription
    $query = "INSERT INTO prescriptions (appointment_id, doctor_id, diagnosis, prescription, notes, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $appointment_id, $doctor_id, $diagnosis, $prescription, $notes);
}

if ($stmt === false) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error preparing statement: ' . $conn->error]);
    exit();
}

if ($stmt->execute()) {
    // Update appointment status to 'Completed'
    $update_status = "UPDATE appointments SET status = 'completed' WHERE appointment_id = ?";
    $status_stmt = $conn->prepare($update_status);
    $status_stmt->bind_param("s", $appointment_id);
    $status_stmt->execute();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Prescription saved successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error saving prescription: ' . $stmt->error]);
}
?> 