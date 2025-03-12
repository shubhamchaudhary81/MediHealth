<?php

require_once('../config/database.php');
session_start(); // Start the session

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $patientid = $_POST['patientid'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM patients WHERE patientid='$patientid'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        if($row['password'] == ($password)){
            
             // Store userid and patient_id in session
             $_SESSION['userid'] = $row['userid'];
             $_SESSION['patient_id'] = $row['patient_id'];
            
            header('Location: patientdash.php');
            exit(); // Ensure script stops after redirect
        }else{
            echo '<script>alert("USER ID and PASSWORD NOT MATCHED")</script>';
        }
    } else {
        echo '<script>alert("USER ID NOT FOUND")</script>';
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
  <title>MediHealth - Login</title>
  <link rel="stylesheet" href="../css/patientlogin.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-icons@latest/dist/umd/lucide.min.js">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-content">
        <div class="auth-header">
          <a href="index.html" class="logo">
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

          <form class="auth-form" method="POST">
            <div class="form-group">
              <label for="userid">UserID</label>
              <input type="number" id="userid" name="patientid" class="form-input" placeholder="Enter your UserID" required>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <div class="password-input-wrapper">
                <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                <button type="button" class="password-toggle">
                  <i data-lucide="eye"></i>
                </button>
              </div>
             
              <div class="forgot-password">
                <a href="#" style="text-decoration: none;">Forgot password?</a>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Sign in</button>
            
            <!-- <div class="auth-separator">
              <span>OR</span>
            </div>
            
            <button type="button" class="btn btn-outline btn-full google-btn">
              <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/google/google-original.svg" alt="Google" width="18" height="18">
              Sign in with Google
            </button>
          </form> -->

          <div class="auth-footer">
            <p>Don't have an account? <a href="patientregister.php" style="text-decoration: none;">Register</a></p>
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
    // Initialize Lucide icons
    lucide.createIcons();
    
    // Password visibility toggle
    document.querySelector('.password-toggle').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const icon = this.querySelector('i');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
      } else {
        passwordInput.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
      }
      
      lucide.createIcons();
    });
  </script>
</body>
</html>