<?php
include_once('../config/configdatabase.php');

if (isset($_POST['hospital_id']) && isset($_POST['department_id'])) {
    $hospital_id = $_POST['hospital_id'];
    $department_id = $_POST['department_id'];
    
    // Fetch doctors for the selected hospital and department
    $query = "SELECT doctor_id, name, specialization 
              FROM doctor 
              WHERE hospitalid = ? AND department_id = ?
              ORDER BY name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $hospital_id, $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $doctors = array();
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($doctors);
} else {
    // Return error if required parameters are not provided
    http_response_code(400);
    echo json_encode(array('error' => 'Hospital ID and Department ID are required'));
}

$conn->close();
?>