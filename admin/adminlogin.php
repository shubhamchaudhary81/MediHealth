<?php
 include_once('../config/database.php');
 session_start();

 if($_SERVER['REQUEST_METHOD'] == 'POST'){
     $username = $_POST['username'];
     $password = $_POST['password'];


    
     $sql = "SELECT * FROM admin WHERE adminusername='$username'";
     $result = mysqli_query($conn, $sql);
 
 if(mysqli_num_rows($result) > 0){
     $row = mysqli_fetch_assoc($result);
     if($row['adminpassword'] == ($password)){
             $_SESSION['username'] = $row['username'];
             $_SESSION['username'] = $username;
             echo $row['username'];
             header('Location: admindash.php ');
             // echo "Login Successfull";
     }else{
        
        echo '<script>alert("Username & Password Not Matched")</script>';

     }
 }
 else{
     echo "USERNAME NOT FOUND";
 }
 }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-style.css">
    <title>adminlogin</title>
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
            <h1>Welcome Admin</h1>
            <p>Please enter your details to sign in</p>
          </div>

          <form class="auth-form" method="POST">
            <div class="form-group">
              <label for="userid">UserName</label>
              <input type="text" id="username" name="username" class="form-input" placeholder="Enter your UserName" required>
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
