<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once('../config/configdatabase.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Appointment ID is required']);
    exit();
}

$appointment_id = $_GET['id'];
$doctor_id = $_SESSION['user_id'];

// Fetch appointment details
$query = "SELECT a.*, p.first_name, p.last_name, p.number as phone FROM appointments a 
          LEFT JOIN patients p ON a.patient_id = p.patientID 
          WHERE a.appointment_id = ? AND CAST(a.doctor_id AS CHAR) = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error preparing statement: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ss", $appointment_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Appointment not found']);
    exit();
}

$appointment = $result->fetch_assoc();

// Format the response
$response = [
    'appointment_id' => $appointment['appointment_id'],
    'patient_id' => $appointment['patient_id'],
    'patient_name' => $appointment['first_name'] . ' ' . $appointment['last_name'],
    'appointment_date' => $appointment['appointment_date'],
    'appointment_time' => $appointment['appointment_time'],
    'reason' => $appointment['reason'],
    'status' => $appointment['status']
];

header('Content-Type: application/json');
echo json_encode($response);
?> 