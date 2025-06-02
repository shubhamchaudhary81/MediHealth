<?php
session_start();
require_once '../config/configdatabase.php';

// Check if user is logged in and is a doctor
if (!isset($_SESSION['doctorID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get appointment ID from request
$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;

if (!$appointment_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
    exit;
}

// Verify that the appointment belongs to this doctor
$verify_query = "SELECT doctor_id FROM appointments WHERE appointment_id = ?";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment || $appointment['doctor_id'] != $_SESSION['doctorID']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access to this appointment']);
    exit;
}

// Get attachments for this appointment
$query = "SELECT * FROM appointment_attachments WHERE appointment_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

$attachments = [];
while ($row = $result->fetch_assoc()) {
    $attachments[] = [
        'file_name' => $row['file_name'],
        'file_path' => $row['file_path'],
        'file_type' => $row['file_type'],
        'description' => $row['description'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode([
    'success' => true,
    'attachments' => $attachments
]); 