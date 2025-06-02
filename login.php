<?php

require_once('config/configdatabase.php');
session_start(); // Start the session

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = trim($_POST['userid']);
    $password = trim($_POST['password']);
    $user_type = trim($_POST['user_type']);

    // First check in usertype table
    $stmt = $conn->prepare("SELECT * FROM usertype WHERE userid = ? AND user_type = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $userid, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                // Store only essential session variables
                $_SESSION['user_id'] = $user['reference_id'];
                $_SESSION['user_type'] = $user_type;
                
                // Redirect based on user type
                switch($user_type) {
                    case 'patient':
                        header("Location: patient/patientdash.php");
                        break;
                    case 'doctor':
                        header("Location: doctor/doctordash.php");
                        break;
                    case 'hospital_admin':
                        header("Location: hospital/hospitaldash.php");
                        break;
                    case 'super_admin':
                        header("Location: admin/admindash.php");
                        break;
                }
                exit();
            }
        }
        $stmt->close();
    }
    $error = 'Invalid User ID or Password';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHealth - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --text-color: #333;
            --error-color: #dc3545;
            --success-color: #28a745;
            --background-color: #f8f9fa;
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
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: var(--text-color);
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .login-header p {
            color: #666;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-size: 14px;
            font-weight: 500;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            background-color: white;
        }

        .select-group {
            position: relative;
        }

        .select-group select {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            appearance: none;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .select-group select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            background-color: white;
        }

        .select-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .select-group::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3);
        }

        .error-message {
            background: #fee2e2;
            color: var(--error-color);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .register-links {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }

        .register-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .register-links a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .form-control, .select-group select {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="assets/logo-fotor-20250118225918.png" alt="MediHealth Logo">
        </div>
        
        <div class="login-header">
            <h1>Welcome Back!</h1>
            <p>Please login to access your account</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="userid">User ID</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="userid" name="userid" class="form-control" 
                           placeholder="Enter your user ID" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Enter your password" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="user_type">Login As</label>
                <div class="select-group">
                    <i class="fas fa-user-tag"></i>
                    <select id="user_type" name="user_type" class="form-control" required>
                        <option value="">Select user type</option>
                        <option value="patient">Patient</option>
                        <option value="doctor">Doctor</option>
                        <option value="hospital_admin">Hospital Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>
        </form>
        
        <div class="register-links">
            Don't have an account? 
            <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>