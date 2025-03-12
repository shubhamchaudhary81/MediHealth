<?php
session_start();

if(empty($_SESSION['userid']))
{
    header('location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediHealth</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/header.css">
  <link rel="stylesheet" href="../css/footer.css">
  <link rel="stylesheet" href="../css/patientdash.css">
  <link rel="stylesheet" href="../css/bookappointment.css">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  

  
</head>
<body>
    <header class="navbar">
        <div class="container">
          <div class="navbar-content">
            <div class="item1"> 
              <img src="../assets/logo-fotor-20250118225918.png" width="100px">
            </div>
            
            <nav class="nav-desktop">
              <a style="text-decoration: none;" href="../patient/patientdash.php" class="nav-link active">Home</a>
              <!-- <a style="text-decoration: none;" href="#" class="nav-link">Services</a> -->
              <a href="#" class="nav-link">Our Doctors</a>
              <a href="../patient/bookappointment.php" class="nav-link">Book Appointment</a>
              <a href="#" class="nav-link">Contact</a>
            </nav>
    
            <div class="nav-buttons">
              <a href="../patient/logout.php" class="btn btn-outline">Logout</a>
            </div>
          </div>
        </div>
    </header>
</body>
</html>