<?php
include_once('../config/configdatabase.php');

if(isset($_POST['hospital_id'])) {
    $hospital_id = $_POST['hospital_id'];
    
    // Get departments for the selected hospital
    $query = "SELECT d.department_id, d.department_name 
              FROM department d 
              INNER JOIN hospitaldepartment hd ON d.department_id = hd.department_id 
              WHERE hd.hospitalid = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $departments = array();
    while($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($departments);
    
    $stmt->close();
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Hospital ID is required']);
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