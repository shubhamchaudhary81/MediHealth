<?php
include_once('../config/configdatabase.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $city = $_POST['city'];
    
    // Debug: Log the received city
    error_log("Received city: " . $city);
    
    // Check if database connection is successful
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }
    
    // Prepare the query to fetch hospitals in the selected city
    $query = "SELECT h.id, h.name 
              FROM hospital h 
              WHERE h.city = ? 
              ORDER BY h.name";
              
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("s", $city);
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
        exit();
    }
    
    $result = $stmt->get_result();
    
    if ($result === false) {
        error_log("Get result failed: " . $stmt->error);
        echo json_encode(['error' => 'Get result failed: ' . $stmt->error]);
        exit();
    }
    
    $hospitals = [];
    
    while ($row = $result->fetch_assoc()) {
        $hospitals[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    // Debug: Log the number of hospitals found
    error_log("Found " . count($hospitals) . " hospitals");
    
    // Return the hospitals as JSON
    header('Content-Type: application/json');
    echo json_encode($hospitals);
    
    $stmt->close();
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?> 