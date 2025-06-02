<?php
// session_start();

if(empty($_SESSION['patientID'])){
//     header('location: patientlogin.php');
//     exit;
// }
// {
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
  
  <style>
    .dropdown {
      position: relative;
      display: inline-block;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #fff;
      min-width: 160px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      border-radius: 8px;
      z-index: 1000;
      top: 100%;
      left: 0;
      margin-top: 0.5rem;
    }

    .dropdown:hover .dropdown-content {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    .dropdown-content a {
      color: #2c3e50;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
      transition: all 0.3s ease;
    }

    .dropdown-content a:hover {
      background-color: #f8f9fa;
      color: #3498db;
    }

    .dropdown-content a i {
      margin-right: 8px;
      width: 20px;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .nav-link.dropdown-toggle::after {
      content: '';
      display: inline-block;
      margin-left: 0.5rem;
      vertical-align: middle;
      border-top: 0.3em solid;
      border-right: 0.3em solid transparent;
      border-left: 0.3em solid transparent;
      border-bottom: 0;
    }

    .nav-desktop {
      display: flex;
      gap: 2rem;
      align-items: center;
    }

    .nav-desktop li {
      list-style: none;
    }

    .nav-desktop a {
      text-decoration: none;
      color: #2c3e50;
      font-weight: 500;
      padding: 0.5rem 1rem;
      position: relative;
      transition: color 0.3s ease;
    }

    .nav-desktop a::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 50%;
      background-color: #3498db;
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }

    .nav-desktop a:hover::after,
    .nav-desktop a.active::after {
      width: 100%;
    }

    .nav-desktop a:hover {
      color: #3498db;
    }

    .nav-desktop a.active {
      color: #3498db;
    }

    @keyframes slideIn {
      from {
        width: 0;
        opacity: 0;
      }
      to {
        width: 100%;
        opacity: 1;
      }
    }

    .nav-desktop a.active::after {
      animation: slideIn 0.3s ease forwards;
    }

    /* New responsive styles */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      z-index: 1000;
    }

    .navbar-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
    }

    .mobile-menu-btn {
      display: none;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: #2c3e50;
      cursor: pointer;
      padding: 0.5rem;
    }

    .nav-buttons {
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .btn {
      padding: 0.5rem 1rem;
      border-radius: 6px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-outline {
      border: 2px solid #3498db;
      color: #3498db;
    }

    .btn-outline:hover {
      background: #3498db;
      color: white;
    }

    @media (max-width: 1024px) {
      .nav-desktop {
        gap: 1rem;
      }

      .nav-desktop a {
        padding: 0.5rem;
      }
    }

    @media (max-width: 768px) {
      .mobile-menu-btn {
        display: block;
      }

      .nav-desktop {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        flex-direction: column;
        padding: 1rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        gap: 0;
      }

      .nav-desktop.active {
        display: flex;
        animation: slideDown 0.3s ease;
      }

      .nav-desktop li {
        width: 100%;
      }

      .nav-desktop a {
        display: block;
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
      }

      .nav-desktop a:last-child {
        border-bottom: none;
      }

      .nav-buttons {
        display: none;
      }

      .nav-buttons.mobile {
        display: flex;
        flex-direction: column;
        width: 100%;
        padding: 1rem;
        border-top: 1px solid #f0f0f0;
      }

      .nav-buttons.mobile .btn {
        width: 100%;
        text-align: center;
        margin: 0.5rem 0;
      }
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Add margin to body to account for fixed header */
    body {
      padding-top: 80px;
    }
  </style>
</head>
<body>
    <header class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="item1"> 
                    <img src="../assets/logo-fotor-20250118225918.png" width="100px">
                </div>
                
                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>

                <nav class="nav-desktop">
                    <li><a href="patientdash.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'patientdash.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="ourdoctors.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'ourdoctors.php' ? 'active' : ''; ?>">Our Doctors</a></li>
                    <li><a href="bookappointment.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bookappointment.php' ? 'active' : ''; ?>">Book Appointment</a></li>
                    <li><a href="appointments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">Appointments</a></li>
                    <li><a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">Reports</a></li>
                    <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                </nav>
        
                <div class="nav-buttons">
                    <a href="../patient/logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navDesktop = document.querySelector('.nav-desktop');
            const navButtons = document.querySelector('.nav-buttons');

            mobileMenuBtn.addEventListener('click', function() {
                navDesktop.classList.toggle('active');
                
                // Move logout button to mobile menu when it's active
                if (navDesktop.classList.contains('active')) {
                    navButtons.classList.add('mobile');
                    navDesktop.appendChild(navButtons);
                } else {
                    navButtons.classList.remove('mobile');
                    document.querySelector('.navbar-content').appendChild(navButtons);
                }
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.navbar-content') && navDesktop.classList.contains('active')) {
                    navDesktop.classList.remove('active');
                    navButtons.classList.remove('mobile');
                    document.querySelector('.navbar-content').appendChild(navButtons);
                }
            });
        });
    </script>
</body>
</html>