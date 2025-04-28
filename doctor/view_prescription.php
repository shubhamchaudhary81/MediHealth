<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once('../config/configdatabase.php');

if (!isset($_GET['appointment_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Appointment ID is required']);
    exit();
}

$appointment_id = $_GET['appointment_id'];
$doctor_id = $_SESSION['user_id'];

// Verify that this appointment belongs to the doctor
$verify_query = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, 
                        p.first_name, p.last_name, p.number as phone, p.dob as age, p.gender
                 FROM appointments a 
                 LEFT JOIN patients p ON a.patient_id = p.patientID 
                 WHERE a.appointment_id = ? AND CAST(a.doctor_id AS CHAR) = ?";
$verify_stmt = $conn->prepare($verify_query);

if ($verify_stmt === false) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error preparing verification statement: ' . $conn->error]);
    exit();
}

$verify_stmt->bind_param("ss", $appointment_id, $doctor_id);
$verify_stmt->execute();
$appointment_result = $verify_stmt->get_result();

if ($appointment_result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid appointment']);
    exit();
}

$appointment_data = $appointment_result->fetch_assoc();

// Get prescription if it exists
$prescription_query = "SELECT diagnosis, prescription, notes, created_at, updated_at 
                      FROM prescriptions 
                      WHERE appointment_id = ?";
$prescription_stmt = $conn->prepare($prescription_query);
$prescription_stmt->bind_param("s", $appointment_id);
$prescription_stmt->execute();
$prescription_result = $prescription_stmt->get_result();

$response = [
    'appointment' => $appointment_data,
    'prescription' => null
];

if ($prescription_result->num_rows > 0) {
    $response['prescription'] = $prescription_result->fetch_assoc();
}

header('Content-Type: application/json');
echo json_encode($response);
?> 