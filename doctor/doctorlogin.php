<?php

require_once('../config/database.php');
session_start(); // Start the session

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $doctorid = $_POST['doctor_id'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM doctor WHERE doctor_id='$doctorid'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        if($row['password'] == ($password)){
            
             // Store userid and patient_id in session
            //  $_SESSION['userid'] = $row['userid'];
            //  $_SESSION['patient_id'] = $row['patient_id'];
            
            header('Location: doctordash.php');
            exit(); // Ensure script stops after redirect
        }else{
            echo '<script>alert("USER ID and PASSWORD NOT MATCHED")</script>';
        }
    } else {
        echo '<script>alert("USER ID NOT FOUND")</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="doctor-style.css">
    <title>doctorlogin</title>
</head>
<body>
    
</body>
</html>
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
            <h1>Welcome Doctor</h1>
            <p>Please enter your details to sign in</p>
          </div>

          <form class="auth-form" method="POST">
            <div class="form-group">
              <label for="userid">UserID</label>
              <input type="number" id="userid" name="doctor_id" class="form-input" placeholder="Enter your UserID" required>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <div class="password-input-wrapper">
                <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                <button type="button" class="password-toggle">
                  <i data-lucide="eye"></i>
                </button>
              </div>
            </div>

            <div class="forgot-password">
              <a href="#" style="text-decoration: none;">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Sign in</button>
          </form>
        </div>
      </div>
    </div>
</div>
