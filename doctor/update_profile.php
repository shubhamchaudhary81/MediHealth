<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: doctorlogin.php");
    exit();
}

require_once('../config/configdatabase.php');

$doctor_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($specialization)) {
        $error_message = "All fields are required";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Check if email is already taken by another doctor
            $email_check = "SELECT doctor_id FROM doctor WHERE email = ? AND doctor_id != ?";
            $stmt = $conn->prepare($email_check);
            $stmt->bind_param("ss", $email, $doctor_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("Email already exists");
            }

            // Handle password update if provided
            $password_update = "";
            $params = array($name, $email, $phone, $specialization);
            $types = "ssss";

            if (!empty($current_password)) {
                // Verify current password
                $verify_query = "SELECT password FROM doctor WHERE doctor_id = ?";
                $stmt = $conn->prepare($verify_query);
                $stmt->bind_param("s", $doctor_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $doctor = $result->fetch_assoc();

                if (!password_verify($current_password, $doctor['password'])) {
                    throw new Exception("Current password is incorrect");
                }

                if (empty($new_password) || empty($confirm_password)) {
                    throw new Exception("New password and confirmation are required");
                }

                if ($new_password !== $confirm_password) {
                    throw new Exception("New passwords do not match");
                }

                if (strlen($new_password) < 8) {
                    throw new Exception("Password must be at least 8 characters long");
                }

                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_update = ", password = ?";
                $params[] = $hashed_password;
                $types .= "s";
            }

            // Handle profile image upload
            $image_update = "";
            if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
                $max_size = 5 * 1024 * 1024; // 5MB

                if (!in_array($_FILES['profileImage']['type'], $allowed_types)) {
                    throw new Exception("Invalid file type. Only JPG, PNG and GIF are allowed");
                }

                if ($_FILES['profileImage']['size'] > $max_size) {
                    throw new Exception("File size too large. Maximum size is 5MB");
                }

                $upload_dir = '../uploads/doctor_profiles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
                $new_filename = $doctor_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $upload_path)) {
                    $image_update = ", profile_image = ?";
                    $params[] = $new_filename;
                    $types .= "s";
                } else {
                    throw new Exception("Failed to upload image");
                }
            }

            // Update doctor information
            $update_query = "UPDATE doctor SET 
                name = ?, 
                email = ?, 
                phone = ?, 
                specialization = ?" . 
                $password_update . 
                $image_update . 
                " WHERE doctor_id = ?";

            $params[] = $doctor_id;
            $types .= "s";

            $stmt = $conn->prepare($update_query);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $conn->commit();
                $success_message = "Profile updated successfully";
                
                // Update session name if it was changed
                $_SESSION['doctor_name'] = $name;
            } else {
                throw new Exception("Error updating profile: " . $conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = $e->getMessage();
        }
    }
}

// Redirect back to dashboard with messages
$_SESSION['success_message'] = $success_message;
$_SESSION['error_message'] = $error_message;
header("Location: doctordash.php");
exit();
?> 