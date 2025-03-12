<?php
include_once ('../config/database.php');


if (isset($_POST['department_id'])) {
    $department_id = intval($_POST['department_id']);
    $query = "SELECT doctor_id, name FROM doctor WHERE department_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $doctor = [];
    while ($row = $result->fetch_assoc()) {
        $doctor[] = $row;
    }

    echo json_encode($doctor);
    
    $stmt->close();
}

$conn->close();
?>