<?php
session_start();
include_once('../config/configdatabase.php');

header('Content-Type: application/json');

if (!isset($_POST['doctor_id'])) {
    echo json_encode(['error' => 'Missing doctor_id parameter']);
    exit();
}

$doctor_id = $_POST['doctor_id'];

// Fetch available days for the selected doctor
$query = "SELECT DISTINCT day 
          FROM doctor_schedule 
          WHERE doctor_id = ? 
          ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

$available_days = array();
while ($row = $result->fetch_assoc()) {
    $available_days[] = $row['day'];
}

$stmt->close();
echo json_encode($available_days);
?> 