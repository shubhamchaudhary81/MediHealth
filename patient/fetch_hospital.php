<?php
include_once('../config/configdatabase.php');

if (isset($_POST['city'])) {
    $city = $_POST['city'];
    
    // Fetch hospitals for the selected city
    $query = "SELECT id, name FROM hospital WHERE city = ? ORDER BY name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $city);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hospitals = array();
    while ($row = $result->fetch_assoc()) {
        $hospitals[] = $row;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($hospitals);
} else {
    // Return error if city is not provided
    http_response_code(400);
    echo json_encode(array('error' => 'City is required'));
}

$conn->close();
?> 