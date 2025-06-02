<?php
session_start();
require_once('../config/configdatabase.php');

if (!isset($_SESSION['patientID'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

$patient_id = $_SESSION['patientID'];
$appointment_id = $_POST['appointment_id'] ?? null;
$doctor_id = $_POST['doctor_id'] ?? null;

if (!$appointment_id || !$doctor_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

// Create upload directories if they don't exist
$upload_dir = '../uploads/medical_records/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$uploaded_files = [];
$errors = [];

// Handle prescription files
if (isset($_FILES['prescription_files'])) {
    foreach ($_FILES['prescription_files']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['prescription_files']['error'][$key] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['prescription_files']['name'][$key];
            $file_type = $_FILES['prescription_files']['type'][$key];
            $file_tmp = $_FILES['prescription_files']['tmp_name'][$key];
            
            // Generate unique filename
            $unique_filename = uniqid() . '_' . $file_name;
            $file_path = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Save to database
                $query = "INSERT INTO appointment_attachments (appointment_id, patient_id, doctor_id, file_type, file_path, file_name) 
                         VALUES (?, ?, ?, 'prescription', ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iisss", $appointment_id, $patient_id, $doctor_id, $file_path, $file_name);
                
                if ($stmt->execute()) {
                    $uploaded_files[] = [
                        'name' => $file_name,
                        'type' => 'prescription',
                        'path' => $file_path
                    ];
                } else {
                    $errors[] = "Error saving prescription file: " . $file_name;
                }
            } else {
                $errors[] = "Error uploading prescription file: " . $file_name;
            }
        }
    }
}

// Handle report files
if (isset($_FILES['report_files'])) {
    foreach ($_FILES['report_files']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['report_files']['error'][$key] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['report_files']['name'][$key];
            $file_type = $_FILES['report_files']['type'][$key];
            $file_tmp = $_FILES['report_files']['tmp_name'][$key];
            
            // Generate unique filename
            $unique_filename = uniqid() . '_' . $file_name;
            $file_path = $upload_dir . $unique_filename;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Save to database
                $query = "INSERT INTO appointment_attachments (appointment_id, patient_id, doctor_id, file_type, file_path, file_name) 
                         VALUES (?, ?, ?, 'report', ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iisss", $appointment_id, $patient_id, $doctor_id, $file_path, $file_name);
                
                if ($stmt->execute()) {
                    $uploaded_files[] = [
                        'name' => $file_name,
                        'type' => 'report',
                        'path' => $file_path
                    ];
                } else {
                    $errors[] = "Error saving report file: " . $file_name;
                }
            } else {
                $errors[] = "Error uploading report file: " . $file_name;
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'success' => empty($errors),
    'uploaded_files' => $uploaded_files,
    'errors' => $errors
]); 