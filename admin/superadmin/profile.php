<?php
session_start();

// Check if superadmin is logged in
if (!isset($_SESSION['superadmin_id'])) {
    header("Location: ../superadminlogin.php");
    exit();
}

include_once('../../config/configdatabase.php');

$superadmin_id = $_SESSION['superadmin_id'];
$success_message = '';
$error_message = '';

// Fetch superadmin details
$superadmin_query = "SELECT * FROM superadmin WHERE super_id = ?";
$stmt = $conn->prepare($superadmin_query);
$stmt->bind_param("i", $superadmin_id);
$stmt->execute();
$result = $stmt->get_result();
$superadmin = $result->fetch_assoc();
$stmt->close();

// Handle form submission for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate current password
    if (!empty($current_password)) {
        if (password_verify($current_password, $superadmin['password'])) {
            // Update profile with new password
            if (!empty($new_password) && $new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE superadmin SET name = ?, email = ?, password = ? WHERE superadmin_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ssss", $name, $email, $hashed_password, $superadmin_id);
            } else {
                $error_message = "New passwords do not match.";
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    } else {
        // Update profile without changing password
        $update_query = "UPDATE superadmin SET name = ?, email = ? WHERE superadmin_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sss", $name, $email, $superadmin_id);
    }
    
    if (empty($error_message) && isset($stmt)) {
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh superadmin data
            $superadmin_query = "SELECT * FROM superadmin WHERE superadmin_id = ?";
            $stmt = $conn->prepare($superadmin_query);
            $stmt->bind_param("s", $superadmin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $superadmin = $result->fetch_assoc();
            $stmt->close();
            
            // Update session variables
            $_SESSION['superadmin_name'] = $superadmin['name'];
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .password-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <div class="header">
                    <h1>Superadmin Profile</h1>
                </div>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="superadmin_id">Superadmin ID</label>
                        <input type="text" id="superadmin_id" value="<?php echo htmlspecialchars($superadmin['super_id']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($superadmin['super_name']); ?>" required>
                    </div>
                    
                    <!-- <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($superadmin['email']); ?>" required>
                    </div> -->
                    
                    <div class="password-section">
                        <h3>Change Password</h3>
                        <p>Leave these fields blank if you don't want to change your password.</p>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 