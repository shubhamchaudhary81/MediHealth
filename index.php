<?php
  include_once('./config/configdatabase.php');
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
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="review.php">Review</a></li>
                        
                    </ul>
                </nav>
            </div>

            <div class="item3">
                <a href="patient/patientregister.php" class="btn-register">Patient Register</a>
                <a href="hospital/hospitalregister.php" class="btn-register">Hospital Register</a>
            </div>
        </header>
    </div>
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome To, <span>MediHealth </span></h1>
            <p>We are here to connect you to the best doctor & help to get the best medical service and manage you treatment.</p>
            <div class="login-dropdown">
                <button class="btn-services" id="loginButton">Login</button>
                <div class="dropdown-content" id="loginDropdown">
                    <a href="patient/patientlogin.php">Patient Login</a>
                    <a href="doctor/doctorlogin.php">Doctor Login</a>
                    <a href="hospital/hospitaladminlogin.php">Hospital Admin Login</a>
                    <a href="admin/superadminlogin.php">Super Admin Login</a>
                </div>
            </div>
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

    <!-- New Features Section -->
    <section class="features-section">
        <div class="features-container">
            <div class="features-header">
                <h2>Why Choose <span>MediHealth</span></h2>
                <p>Experience healthcare that's focused on your wellbeing with our comprehensive platform</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Easy Appointment Booking</h3>
                    <p>Book appointments with your preferred doctors online in just a few clicks, saving time and avoiding phone calls.</p>
                    <div class="feature-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Online scheduling</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Instant confirmation</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Reminder notifications</span>
                        </div>
                    </div>
                </div>
                
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3>Expert Doctors</h3>
                    <p>Access to qualified and experienced healthcare professionals across various specialties.</p>
                    <div class="feature-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Specialized care</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Experienced professionals</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Multiple specialties</span>
                        </div>
                    </div>
                </div>
                
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <h3>Digital Medical Records</h3>
                    <p>Access your complete medical history, prescriptions, and test results securely from anywhere, anytime.</p>
                    <div class="feature-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Medical history</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Test results</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Prescriptions</span>
                        </div>
                    </div>
                </div>
                
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>24/7 Service</h3>
                    <p>Round-the-clock access to healthcare services and support when you need it most.</p>
                    <div class="feature-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Always available</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Emergency support</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Quick response</span>
                        </div>
                    </div>
                </div>
            </div>
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
                <div class="testimonial-avatar">R</div>
                <div>
                  <h4>Raju</h4>
                  <p>Patient</p>
                </div>
              </div>
              <p class="testimonial-text">
                The online appointment system is so convenient. I was able to book my visit in just a few clicks and received timely reminders. The doctors are professional and caring.
              </p>
            </div>
            
            <div class="testimonial-card">
              <div class="testimonial-header">
                <div class="testimonial-avatar">M</div>
                <div>
                  <h4>Mahesh</h4>
                  <p>Patient</p>
                </div>
              </div>
              <p class="testimonial-text">
                I've been using MediHealth for all my family's medical needs. The platform makes it easy to keep track of appointments and medical records. Highly recommended!
              </p>
            </div>
            
            <div class="testimonial-card">
              <div class="testimonial-header">
                <div class="testimonial-avatar">S</div>
                <div>
                  <h4>Shibu</h4>
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

      
        <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginButton = document.getElementById('loginButton');
        const loginDropdown = document.getElementById('loginDropdown');
        
        // Toggle dropdown on button click
        loginButton.addEventListener('click', function(e) {
            e.stopPropagation();
            loginDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!loginButton.contains(e.target) && !loginDropdown.contains(e.target)) {
                loginDropdown.classList.remove('show');
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        loginDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
</script>
      

<!-- footer start -->
 <?php
include_once('include/footer.php');
?>

<style>
/* Features Section Styles */
.features-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    position: relative;
    overflow: hidden;
}

.features-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 120%;
    height: 100%;
    background: url('assets/pattern.png') repeat;
    opacity: 0.05;
    z-index: 0;
}

.features-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 1;
}

.features-header {
    text-align: center;
    margin-bottom: 50px;
}

.features-header h2 {
    font-size: 2.5rem;
    color: #2c3e50;
    margin-bottom: 15px;
}

.features-header h2 span {
    color: #3498db;
}

.features-header p {
    font-size: 1.1rem;
    color: #7f8c8d;
    max-width: 700px;
    margin: 0 auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

.feature-box {
    background: #fff;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.feature-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.feature-box:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
}

.feature-box:hover::before {
    opacity: 1;
}

.feature-icon {
    width: 70px;
    height: 70px;
    background: rgba(52, 152, 219, 0.1);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.feature-box:hover .feature-icon {
    background: #3498db;
    transform: rotate(10deg);
}

.feature-icon i {
    font-size: 1.8rem;
    color: #3498db;
    transition: all 0.3s ease;
}

.feature-box:hover .feature-icon i {
    color: #fff;
}

.feature-box h3 {
    font-size: 1.5rem;
    color: #2c3e50;
    margin-bottom: 15px;
}

.feature-box p {
    color: #7f8c8d;
    margin-bottom: 20px;
    line-height: 1.6;
}

.feature-benefits {
    margin-top: auto;
}

.benefit-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.benefit-item i {
    color: #2ecc71;
    margin-right: 10px;
    font-size: 1rem;
}

.benefit-item span {
    color: #2c3e50;
    font-size: 0.95rem;
}

/* Responsive Design */
@media (max-width: 992px) {
    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .features-section {
        padding: 60px 0;
    }
    
    .features-header h2 {
        font-size: 2rem;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .feature-box {
        padding: 25px;
    }
}

/* Update dropdown styles */
.login-dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #fff;
    min-width: 200px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-radius: 8px;
    z-index: 1000;
    right: 0;
    top: 100%;
    margin-top: 10px;
}

.dropdown-content.show {
    display: block;
    animation: fadeIn 0.3s ease;
}

.dropdown-content a {
    color: #333;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    transition: all 0.3s ease;
}

.dropdown-content a:hover {
    background-color: #f5f5f5;
    color: #2196F3;
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

/* Add a small arrow indicator */
.login-dropdown .btn-services::after {
    content: '▼';
    font-size: 10px;
    margin-left: 5px;
    display: inline-block;
    vertical-align: middle;
}

/* Ensure the dropdown stays above other elements */
.item3 {
    position: relative;
    z-index: 1000;
}

/* Update hero section styles to accommodate the dropdown */
.hero-content {
    position: relative;
    z-index: 2;
}

.hero-content .login-dropdown {
    display: inline-block;
    margin-top: 20px;
}

.hero-content .dropdown-content {
    right: auto;
    left: 0;
}

/* Add styles for the hospital icon next to hospital name */
.hospital-name {
    display: flex;
    align-items: center;
    gap: 10px;
}

.hospital-name i {
    color: #3498db;
    font-size: 1.2rem;
}

/* Update sidebar logo styles */
.sidebar-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
}
</style>

    <!-- footer section end  -->
</body>
</html>

