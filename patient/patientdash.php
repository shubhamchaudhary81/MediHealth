<?php
// session_start();
include_once('../include/header.php');

// Start session to get user's first name


// Sample first name (Replace this with session or database value)
$firstName = isset($_SESSION['firstName']) ? $_SESSION['firstName'] : 'Guest';

// Get current hour
$hour = date('H');

// Determine greeting message
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

?>

<body>
 

 

  <main>
    <!-- Greeting Section -->
<div class="greeting-container">
    <p class="greeting-text"><?php echo "$greeting, $firstName!"; ?> Welcome to MediHealth.</p>
</div>
    <!-- Hero Section -->
    <section class="hero">
      <div class="container">
        <div class="hero-content">
          <div class="hero-text">
            <div class="badge">Healthcare Made Simple</div>
            <h1>Your Health, Our <span>Priority</span></h1>
            <p>Experience healthcare that's focused on your wellbeing. Book appointments, 
              access your records, and connect with expert doctors all in one place.</p>
            
            <div class="hero-buttons">
              <a href="bookappointment.php" class="btn btn-primary">
                <i class="fa-solid fa-calendar"></i> Book Appointment
              </a>
            
            </div>
            
            <div class="hero-features">
              <div class="hero-feature">
                <div class="feature-icon blue">
                  <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                  <h3>Qualified Doctors</h3>
                  <p>Expert specialists</p>
                </div>
              </div>
              <div class="hero-feature">
                <div class="feature-icon teal">
                  <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                  <h3>24/7 Service</h3>
                  <p>Always available</p>
                </div>
              </div>
            </div>
          </div>
        
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="container">
        <div class="section-header">
          <div class="badge">Our Features</div>
          <h2>Comprehensive Healthcare Management</h2>
          <p>Our platform offers a complete suite of tools to streamline healthcare experiences 
            for patients, doctors, and administrators.</p>
        </div>

        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-calendar-check"></i>
            </div>
            <h3>Easy Appointment Booking</h3>
            <p>Book appointments with your preferred doctors online in just a few clicks, saving time and avoiding phone calls.</p>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <h3>Digital Medical Records</h3>
            <p>Access your complete medical history, prescriptions, and test results securely from anywhere, anytime.</p>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-user-doctor"></i>
            </div>
            <h3>Doctor Management</h3>
            <p>Doctors can efficiently manage their schedules, view patient histories, and provide online consultations.</p>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-message"></i>
            </div>
            <h3>Secure Messaging</h3>
            <p>Communicate directly with your healthcare providers through our secure messaging system.</p>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-shield-check"></i>
            </div>
            <h3>Data Security</h3>
            <p>Your medical data is protected with enterprise-grade security and complies with all healthcare regulations.</p>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-file-prescription"></i>
            </div>
            <h3>Prescription Management</h3>
            <p>Doctors can issue digital prescriptions, and patients can view and download them instantly.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- about section starts -->

    <section class="about" id="about">
      <div class="head">
          <h1 class="heading"> <span>About</span> us </h1>
      </div>
    <div class="row">

        <div class="image">
            <img src="../assets/Screenshot 2025-03-05 235427.png" alt="">
        </div>

        <div class="content">
          <h3>Your health, our priority – committed to excellence in care.</h3>
         
          <p>MediHealth – Your trusted partner in healthcare. We provide expert medical services, reliable health information, and personalized care to support your well-being. With a commitment to excellence and compassion, MediHealth connects you to quality healthcare solutions for a healthier life.</p>
          <a href="#" class="btn"> learn more <span class="fas fa-chevron-right"></span> </a>
      </div>

    </div>

</section>

  <!-- about section ends -->

  </main>


 
  <?php
         include('../include/footer.php')
  ?>
</body>
</html>
