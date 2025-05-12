<?php
session_start();
include_once('../config/configdatabase.php');

// Check if user is logged in
if (!isset($_SESSION['patientID'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Check if hospital_id is provided
if (!isset($_GET['hospital_id']) || empty($_GET['hospital_id'])) {
    echo json_encode(['success' => false, 'message' => 'Hospital ID is required']);
    exit();
}

$hospital_id = $_GET['hospital_id'];

// Prepare and execute query to get doctors for the selected hospital
$query = "SELECT doctor_id, name, specialization 
          FROM doctor 
          WHERE hospitalid = ? 
          ORDER BY name";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = [
        'doctor_id' => $row['doctor_id'],
        'name' => $row['name'],
        'specialization' => $row['specialization']
    ];
}

$stmt->close();

// Return the doctors as JSON
echo json_encode([
    'success' => true,
    'doctors' => $doctors
]); 