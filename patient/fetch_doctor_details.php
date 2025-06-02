<?php
require_once('../config/configdatabase.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['doctor_id'])) {
    $doctor_id = $_POST['doctor_id'];
    
    // Prepare the query to fetch doctor details
    $query = "SELECT d.*, h.name as hospital_name 
              FROM doctor d 
              JOIN hospital h ON d.hospitalid = h.id 
              WHERE d.doctor_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $doctor = $result->fetch_assoc();
        
        // Prepare the response data
        $response = array(
            'name' => $doctor['name'],
            'specialization' => $doctor['specialization'],
            'qualification' => $doctor['qualification'],
            'experience' => $doctor['experience'],
            'hospital' => $doctor['hospital_name'],
            'profile_image' => $doctor['profile_image']
        );
        
        // Send JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // Send error response
        header('HTTP/1.1 404 Not Found');
        echo json_encode(array('error' => 'Doctor not found'));
    }
    
    $stmt->close();
} else {
    // Send error response for invalid request
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array('error' => 'Invalid request'));
}

$conn->close();
?> 