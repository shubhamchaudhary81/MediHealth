<?php
session_start();
include_once('../config/configdatabase.php');

header('Content-Type: application/json');

if (!isset($_GET['hospital_id'])) {
    echo json_encode(['success' => false, 'message' => 'Hospital ID is required']);
    exit();
}

$hospital_id = intval($_GET['hospital_id']);

// Fetch doctors for the selected hospital
$query = "SELECT doctor_id, name, specialization 
          FROM doctor 
          WHERE hospital_id = ? 
          ORDER BY name";

$stmt = $conn->prepare($query);
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

echo json_encode([
    'success' => true,
    'doctors' => $doctors
]); 