<?php
include_once ('../config/database.php');


if (isset($_POST['hospital_id'])) {
    $hospital_id = intval($_POST['hospital_id']);
    $query = "SELECT department_id, name FROM department WHERE hospital_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }

    echo json_encode($departments);
    
    $stmt->close();
}

$conn->close();


// if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['hospital_id'])) {
//     $hospital_id = intval($_POST['hospital_id']); // Sanitize input

//     $query = "SELECT department_id, name FROM department WHERE hospital_id = ?";
//     $stmt = $conn->prepare($query);
//     $stmt->bind_param("i", $hospital_id);
//     $stmt->execute();
//     $result = $stmt->get_result();

    
//     if ($result->num_rows > 0) {
//         $department = $result->fetch_assoc();
//         echo json_encode($department);
//     } else {
//         echo json_encode(['error' => 'No matching department found.']);
//     }
// }

// $stmt->close();
// $conn->close();
?>