<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once('../config/configdatabase.php');

if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Appointment ID is required']);
    exit();
}

$appointment_id = $_GET['id'];
$doctor_id = $_SESSION['user_id'];

$query = "SELECT a.*, p.first_name, p.last_name, p.number as phone, p.email
          FROM appointments a 
          LEFT JOIN patients p ON a.patient_id = p.patientID 
          WHERE a.appointment_id = ? AND a.doctor_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $appointment_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $appointment = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($appointment);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Appointment not found']);
}
?> 