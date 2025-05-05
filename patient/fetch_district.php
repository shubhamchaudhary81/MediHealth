<?php
include_once('../config/configdatabase.php');

if (isset($_POST['province'])) {
    $province = $_POST['province'];
    
    // Fetch districts for the selected province
    $query = "SELECT id, name FROM districts WHERE province_id = ? ORDER BY name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $province);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $districts = array();
    while ($row = $result->fetch_assoc()) {
        $districts[] = $row;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($districts);
} else {
    // Return error if province is not provided
    http_response_code(400);
    echo json_encode(array('error' => 'Province is required'));
}

$conn->close();
?> 