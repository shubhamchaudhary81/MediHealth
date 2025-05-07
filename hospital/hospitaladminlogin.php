<?php
require_once('../config/configdatabase.php');
session_start();

$error = '';

// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     $userid = trim($_POST['userid']);
//     $password = trim($_POST['password']);

//     // Check in usertype table
//     $stmt = $conn->prepare("SELECT * FROM usertype WHERE userid = ? AND user_type = 'hospital_admin'");
//     if ($stmt) {
//         $stmt->bind_param("s", $userid);
//         $stmt->execute();
//         $result = $stmt->get_result();
        
//         if ($result->num_rows > 0) {
//             $user = $result->fetch_assoc();
//             if (password_verify($password, $user['password']) || $password === $user['password']) {
//                 // Store session variables
//                 $_SESSION['user_id'] = $user['reference_id'];
//                 $_SESSION['user_type'] = 'hospital_admin';
                
//                 header("Location: hospitaldash.php");
//                 exit();
//             }
//         }
//         $stmt->close();
//     }
//     $error = 'Invalid User ID or Password';
// }
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $query = "SELECT ha.*, h.status as hospital_status 
              FROM hospitaladmin ha 
              JOIN hospital h ON ha.hospitalid = h.id 
              WHERE ha.email = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Check hospital status
            if ($user['hospital_status'] === 'pending') {
                $_SESSION['error'] = "Your hospital registration is pending approval. Please wait for admin approval.";
            } elseif ($user['hospital_status'] === 'rejected') {
                $_SESSION['error'] = "Your hospital registration has been rejected. Please contact support for more information.";
            } elseif ($user['hospital_status'] === 'approved') {
                // Set session variables
                $_SESSION['user_id'] = $user['adminid'];
                $_SESSION['user_type'] = 'hospital_admin';
                $_SESSION['hospital_id'] = $user['hospitalid'];
                
                // Redirect to dashboard
                header("Location: hospitaldash.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid email or password";
        }
    } else {
        $_SESSION['error'] = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
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
            background: linear-gradient(90deg, #4CAF50, #2196F3);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            width: 120px;
            height: auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
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
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #2196F3;
            outline: none;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #4CAF50, #2196F3);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3);
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .register-links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .register-links a {
            color: #2196F3;
            text-decoration: none;
            margin: 0 10px;
        }

        .register-links a:hover {
            text-decoration: underline;
        }

        .back-to-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-home a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }

        .back-to-home a:hover {
            color: #2196F3;
        }

        @media (max-width: 480px) {
            .login-container {
                width: 95%;
                padding: 30px 20px;
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
            <h1>Hospital Admin Login</h1>
            <p>Please login to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="back-to-home">
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>
</body>
</html> 