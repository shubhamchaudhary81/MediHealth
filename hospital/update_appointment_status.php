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

if (!isset($data['appointment_id']) || !isset($data['status'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$appointment_id = $data['appointment_id'];
$status = $data['status'];

// Validate status
if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Get hospital admin ID from session
$admin_id = $_SESSION['user_id'];

// Verify that the appointment belongs to the admin's hospital
$verify_query = "SELECT a.* FROM appointments a 
                JOIN hospital h ON a.hospital_id = h.id 
                JOIN hospitaladmin ha ON h.id = ha.hospitalid 
                WHERE a.appointment_id = ? AND ha.adminid = ?";

$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $appointment_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Appointment not found or unauthorized']);
    exit();
}

// Update appointment status
$update_query = "UPDATE appointments SET status = ? WHERE appointment_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("si", $status, $appointment_id);

if ($stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?> 