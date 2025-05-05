<?php
include_once('../config/configdatabase.php');

if (isset($_POST['district'])) {
    $district = $_POST['district'];
    
    // Fetch cities for the selected district
    $query = "SELECT id, name FROM cities WHERE district_id = ? ORDER BY name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $district);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cities = array();
    while ($row = $result->fetch_assoc()) {
        $cities[] = $row;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($cities);
} else {
    // Return error if district is not provided
    http_response_code(400);
    echo json_encode(array('error' => 'District is required'));
}

$conn->close();
?> 