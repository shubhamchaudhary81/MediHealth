<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once('../config/configdatabase.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_id = $_POST['doctor_id'];
    $password = $_POST['password'];
    
    // Prepare SQL statement to prevent SQL injection
    $query = "SELECT * FROM doctor WHERE doctor_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $doctor = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $doctor['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $doctor['doctor_id'];
            $_SESSION['user_type'] = 'doctor';
            $_SESSION['doctor_name'] = $doctor['name'];
            $_SESSION['hospital_id'] = $doctor['hospitalid'];
            
            // Redirect to doctor dashboard
            header("Location: doctordash.php");
            exit();
        } else {
            $error_message = "Invalid password. Please try again.";
        }
    } else {
        $error_message = "Doctor ID not found. Please check your credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Login - MediHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #3b82f6;
            --text-color: #1e293b;
            --background-color: #f8fafc;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 32px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-header h1 {
            color: var(--text-color);
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: #64748b;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-btn:hover {
            background-color: #2563eb;
        }
        
        .error-message {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .back-to-home {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-home a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .back-to-home a:hover {
            color: #2563eb;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Doctor Login</h1>
            <p>Welcome back! Please login to your account.</p>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="doctor_id">Doctor ID</label>
                <input type="text" id="doctor_id" name="doctor_id" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
        </form>
        
        <div class="back-to-home">
            <a href="../index.php">Back to Home</a>
        </div>
    </div>
</body>
</html> 