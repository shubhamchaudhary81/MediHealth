<?php
include_once('../config/configdatabase.php');

if (isset($_POST['zone'])) {
    $zone = $_POST['zone'];
    
    // Fetch hospitals for the selected zone
    $query = "SELECT DISTINCT h.id, h.name, h.district, h.city 
              FROM hospital h 
              WHERE h.district IN (
                  SELECT district 
                  FROM patients 
                  WHERE zone = ?
              )
              ORDER BY h.name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $zone);
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
    // Return error if zone is not provided
    http_response_code(400);
    echo json_encode(array('error' => 'Zone is required'));
}

$conn->close();
?> 