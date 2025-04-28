<?php

require_once('../config/configdatabase.php');
session_start(); // Start the session

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patientid = trim($_POST['patientID']);
    $password = trim($_POST['password']);

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM patients WHERE patientID = ?");
    if ($stmt) {
        $stmt->bind_param("s", $patientid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $patient = $result->fetch_assoc();
            if (password_verify($password, $patient['password'])) {
                // Store session variables
                $_SESSION['patientID'] = $patient['patientID'];
                $_SESSION['user_type'] = 'patient';
                $_SESSION['patient_name'] = $patient['first_name'];
                
                header("Location: patientdash.php");
                exit();
            } else {
                $error = "Invalid password. Please try again.";
            }
        } else {
            $error = "Patient ID not found. Please check your credentials.";
        }
        $stmt->close();
    } else {
        $error = "Database error. Please try again later.";
    }
}

// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//   $userid = $_POST['userid'];
//   $password = $_POST['password'];
//   $usertype = $_POST['usertype']; // Identify user type (patient, doctor, admin)

//   // Define table name based on user type
//   if ($usertype == 'patient') {
//       $table = 'patients';
//       $id_column = 'patientid';
//       $redirect = 'patientdash.php';
//   } elseif ($usertype == 'doctor') {
//       $table = 'doctors';
//       $id_column = 'doctorid';
//       $redirect = 'doctordash.php';
//   } elseif ($usertype == 'admin') {
//       $table = 'admins';
//       $id_column = 'adminid';
//       $redirect = 'admindash.php';
//   } else {
//       echo '<script>alert("Invalid user type.");</script>';
//       exit();
//   }

//   $sql = "SELECT * FROM $table WHERE $id_column='$userid'";
//   $result = mysqli_query($conn, $sql);

//   if (mysqli_num_rows($result) > 0) {
//       $row = mysqli_fetch_assoc($result);
//       if ($row['password'] == $password) {
//           // Store user session data
//           $_SESSION['userid'] = $row['userid'];
//           $_SESSION['user_type'] = $usertype;
//           header("Location: $redirect");
//           exit();
//       } else {
//           echo '<script>alert("USER ID and PASSWORD NOT MATCHED");</script>';
//       }
//   } else {
//       echo '<script>alert("USER ID NOT FOUND");</script>';
//   }
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediHealth - Patient Login</title>
  <link rel="stylesheet" href="../css/register.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <style>
    .auth-form {
      max-width: 400px;
      margin: 0 auto;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 14px;
      transition: border-color 0.3s;
    }
    
    .form-input:focus {
      border-color: #2d89ef;
      outline: none;
    }
    
    .password-input-wrapper {
      position: relative;
    }
    
    .password-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: #666;
    }
    
    .error-message {
      color: #dc3545;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      border-radius: 5px;
      padding: 10px;
      margin-bottom: 20px;
      font-size: 14px;
    }
    
    .btn-primary {
      background-color: #2d89ef;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
      font-weight: 500;
      transition: background-color 0.3s;
    }
    
    .btn-primary:hover {
      background-color: #1a75d1;
    }
    
    .auth-footer {
      text-align: center;
      margin-top: 20px;
      color: #666;
    }
    
    .auth-footer a {
      color: #2d89ef;
      text-decoration: none;
    }
    
    .auth-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-content">
        <div class="auth-header">
          <a href="../index.php" class="logo">
            <div class="logo-icon">
              <i data-lucide="file-text"></i>
            </div>
            <span>MediHealth</span>
          </a>
        </div>

        <div class="auth-form-container">
          <div class="auth-welcome">
            <h1>Welcome back</h1>
            <p>Please enter your details to sign in</p>
          </div>

          <?php if ($error): ?>
            <div class="error-message">
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>

          <form class="auth-form" method="POST">
            <div class="form-group">
              <label for="patientID">Patient ID</label>
              <input type="text" id="patientID" name="patientID" class="form-input" placeholder="Enter your Patient ID" required>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <div class="password-input-wrapper">
                <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                <button type="button" class="password-toggle" onclick="togglePassword()">
                  <i data-lucide="eye"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-primary">Sign in</button>
          </form>

          <div class="auth-footer">
            <p>Don't have an account? <a href="patientregister.php">Register</a></p>
          </div>
        </div>
      </div>
      
      <div class="auth-image">
        <div class="image-overlay"></div>
        <div class="auth-quote">
          <blockquote>
            "The art of medicine consists of amusing the patient while nature cures the disease."
          </blockquote>
          <cite>— Voltaire</cite>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const icon = document.querySelector('.password-toggle i');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
      } else {
        passwordInput.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
      }
      
      // Refresh Lucide icons
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    }
  </script>
</body>
</html>