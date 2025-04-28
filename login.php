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

    .select-group {
      position: relative;
    }

    .select-group select {
      width: 100%;
      padding: 12px 15px 12px 45px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
      appearance: none;
      background: white;
      cursor: pointer;
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
      <img src="assets/logo-fotor-20250118225918.png" alt="MediHealth Logo">
    </div>
    
    <div class="login-header">
      <h1>Welcome Back</h1>
      <p>Please login to your account</p>
    </div>

    <?php if ($error): ?>
      <div class="error-message">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="user_type">User Type</label>
        <div class="select-group">
          <i class="fas fa-user-tag"></i>
          <select id="user_type" name="user_type" class="form-control" required>
            <option value="">Select User Type</option>
            <option value="patient">Patient</option>
            <option value="doctor">Doctor</option>
            <option value="hospital_admin">Hospital Admin</option>
            <option value="super_admin">Super Admin</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="userid">User ID</label>
        <div class="input-group">
          <i class="fas fa-user"></i>
          <input type="text" id="userid" name="userid" class="form-control" required>
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

    <div class="register-links">
      <a href="patient/patientregister.php">Register as Patient</a>
      <a href="hospital/hospitalregister.php">Register as Hospital</a>
    </div>
  </div>
</body>
</html>