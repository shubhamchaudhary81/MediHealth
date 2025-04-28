<?php
session_start();
include_once('../config/configdatabase.php');

// Check if user is logged in
if (!isset($_SESSION['patientID'])) {
    header("Location: patientlogin.php");
    exit();
}

$patient_id = $_SESSION['patientID'];

if (isset($_GET['id'])) {
    $report_id = $_GET['id'];
    
    // First, get the file path
    $stmt = $conn->prepare("SELECT file_path FROM patient_reports WHERE report_id = ? AND patient_id = ?");
    $stmt->bind_param("ii", $report_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($report = $result->fetch_assoc()) {
        // Delete the file
        if (file_exists($report['file_path'])) {
            unlink($report['file_path']);
        }
        
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM patient_reports WHERE report_id = ? AND patient_id = ?");
        $delete_stmt->bind_param("ii", $report_id, $patient_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Report deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting report.";
        }
        $delete_stmt->close();
    }
}

// Redirect back to reports page
header("Location: reports.php");
exit();
?> 