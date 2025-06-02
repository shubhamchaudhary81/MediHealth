<?php
require_once('../config/configdatabase.php');
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $superid = $_POST['super_id'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM superadmin WHERE super_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $superid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['superadmin_id'] = $user['super_id'];
            $_SESSION['superadmin_name'] = $user['super_name'];
            $_SESSION['user_type'] = 'super_admin';
            
            // Redirect to dashboard
            header("Location: superadmin/dashboard.php");
            exit();
        } else {
            $error = "Invalid ID or password";
        }
    } else {
        $error = "Invalid ID or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login - MediHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #673AB7;
            --secondary-color: #512DA8;
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
            background: linear-gradient(135deg, #ede7f6 0%, #d1c4e9 100%);
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
            box-shadow: 0 0 0 3px rgba(103, 58, 183, 0.1);
            background-color: white;
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
            box-shadow: 0 5px 15px rgba(103, 58, 183, 0.3);
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

        .back-to-home {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .back-to-home a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-to-home a:hover {
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

            .form-control {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../assets/logo-fotor-20250118225918.png" alt="MediHealth Logo">
        </div>
        
        <div class="login-header">
            <h1>Super Admin Login</h1>
            <p>Welcome back! Please login to access your account</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Super ID</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="text" id="super_id" name="super_id" class="form-control" 
                           placeholder="Enter your ID" required>
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
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>
        </form>
        
        <div class="back-to-home">
            <a href="../index.php">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </div>
</body>
</html> 