<?php
  include_once('./config/database.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHealth</title>
    <link rel="stylesheet" href="css/hello.css">
    <link rel="stylesheet" href="css/footer.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />


    
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->

</head>
<body>
    <div class="container">
        <header>
            <!-- <div class="logo">
                <img src="Medihealth_1-removebg-preview.png" alt="">
            </div> -->
            <div class="item1"> <img src="assets/logo-fotor-20250118225918.png" width="200px"></div>

            <div class="item2">
                <nav>
                    <ul>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#blog">Blog</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#contact">Review</a></li>

                    </ul>
                </nav>
            </div>

            <div class="item3">
                <a href="patient/patientregister.php" class="btn-register">Register Now</a>
                <!-- <a href="#register" class="btn-login">Login</a> -->
            </div>
        </header>
    </div>
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome To, <span>MediHealth </span></h1>
            <p>We are here to connect you to the best doctor & help to get the best medical service and manage you treatment.</p>
            <a href="patient/patientlogin.php" class="btn-services">Login</a>
        </div>
        <div class="hero-image">
            <img src="assets/pngtree-city-hospital-elements-png-image_14500475.png" alt="Doctor">
            <!-- <div class="info-card">
                <p>1520+ Active Clients</p>
            </div> -->
            <!-- <div class="info-card">
                <p>Get 20% off on every first month to expert doctors</p>
            </div> -->
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
          <div class="section-header">
            <div class="badge">Testimonials</div>
            <h2>What Our Patients Say</h2>
            <p>Hear from patients who have experienced our services and care firsthand.</p>
          </div>
          
          <div class="testimonials-grid">
            <div class="testimonial-card">
              <div class="testimonial-header">
                <div class="testimonial-avatar">JD</div>
                <div>
                  <h4>John Doe</h4>
                  <p>Patient</p>
                </div>
              </div>
              <p class="testimonial-text">
                The online appointment system is so convenient. I was able to book my visit in just a few clicks and received timely reminders. The doctors are professional and caring.
              </p>
            </div>
            
            <div class="testimonial-card">
              <div class="testimonial-header">
                <div class="testimonial-avatar">SM</div>
                <div>
                  <h4>Sarah Miller</h4>
                  <p>Patient</p>
                </div>
              </div>
              <p class="testimonial-text">
                I've been using MediPoint for all my family's medical needs. The platform makes it easy to keep track of appointments and medical records. Highly recommended!
              </p>
            </div>
            
            <div class="testimonial-card">
              <div class="testimonial-header">
                <div class="testimonial-avatar">AR</div>
                <div>
                  <h4>Alex Rodriguez</h4>
                  <p>Patient</p>
                </div>
              </div>
              <p class="testimonial-text">
                The care I received was exceptional. I could easily access my test results and the doctor's notes after my visit, which helped me understand my condition better.
              </p>
            </div>
          </div>
        </div>
      </section>

<!-- footer start -->
 <?php
include_once('include/footer.php');
?>



    <!-- footer section end  -->
</body>
</html>

