<?php
// session_start();
include_once('../include/header.php');
include_once('../config/configdatabase.php');

// Get patient's first name from database
$firstName = 'Guest';
if (isset($_SESSION['patientID'])) {
    $patient_id = $_SESSION['patientID'];
    $query = "SELECT first_name FROM patients WHERE patientID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $firstName = $row['first_name'];
    }
}

// Get current hour
// $hour = date('H');

// Determine greeting message
// if ($hour >= 5 && $hour < 12) {
//     $greeting = "Good Morning";
// } elseif ($hour >= 12 && $hour < 17) {
//     $greeting = "Good Afternoon";
// } else {
//     $greeting = "Good Evening";
// }

// Set timezone to Nepal
date_default_timezone_set('Asia/Kathmandu');

// Get current hour in 24-hour format
$hour = date("H");

// Determine greeting based on hour
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

// Fetch hospitals
$hospitals_query = "SELECT id, name, location, phone FROM hospital ORDER BY name LIMIT 5";
$hospitals_result = $conn->query($hospitals_query);

// Fetch doctors with their departments
$doctors_query = "SELECT d.doctor_id, d.name, d.specialization, d.experience, 
                 dep.department_name, h.name as hospital_name 
                 FROM doctor d 
                 JOIN department dep ON d.department_id = dep.department_id 
                 JOIN hospital h ON d.hospitalid = h.id 
                 WHERE d.status = 'active' AND d.is_specialist = 1
                 ORDER BY d.name";
$doctors_result = $conn->query($doctors_query);
?>

<body>
 

 

  <main>
    <!-- Greeting Section -->
    <div class="greeting-container container">
        <div class="greeting-content">
            <div class="greeting-icon">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <div class="greeting-text-container">
                <p class="greeting-text"><?php echo "$greeting, $firstName!"; ?></p>
                <p class="welcome-text">Welcome to MediHealth</p>
            </div>
        </div>
        <div class="profile-link">
            <a href="patientprofile.php" class="btn btn-outline">
                <i class="fa-solid fa-user"></i> My Profile
            </a>
        </div>
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
    <!-- <section class="features">
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
    </section> -->



    <!-- Hospitals Section -->
    <section class="hospitals-section">
      <div class="container">
        <div class="section-header">
          <div class="badge">Our Hospitals</div>
          <h2>Featured Healthcare Facilities</h2>
          <p>Discover our network of trusted hospitals providing quality healthcare services.</p>
        </div>

        <div class="hospitals-slider">
          <?php if ($hospitals_result && $hospitals_result->num_rows > 0): ?>
            <?php while ($hospital = $hospitals_result->fetch_assoc()): ?>
              <div class="hospital-card">
                <div class="hospital-image">
                  <img src="../assets/hospital-placeholder.jpg" alt="<?php echo htmlspecialchars($hospital['name']); ?>">
                </div>
                <div class="hospital-info">
                  <h3><?php echo htmlspecialchars($hospital['name']); ?></h3>
                  <div class="hospital-details">
                    <div class="detail-item">
                      <i class="fas fa-map-marker-alt"></i>
                      <span><?php echo htmlspecialchars($hospital['location']); ?></span>
                    </div>
                    <div class="detail-item">
                      <i class="fas fa-phone"></i>
                      <span><?php echo htmlspecialchars($hospital['phone']); ?></span>
                    </div>
                  </div>
                  <a href="bookappointment.php?hospital_id=<?php echo $hospital['id']; ?>" class="btn-hospital">
                    Book Appointment
                  </a>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="no-data">
              <i class="fas fa-hospital"></i>
              <p>No hospitals available at the moment.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Doctors Section -->
    <section class="doctors-section">
      <div class="container">
        <div class="section-header">
          <div class="badge">Our Doctors</div>
          <h2>Meet Our Specialists</h2>
          <p>Our team of experienced doctors is dedicated to providing you with the best care.</p>
        </div>

        <div class="doctors-slider-container">
          <button class="slider-arrow prev-arrow" onclick="slideDoctors('prev')">
            <i class="fas fa-chevron-left"></i>
          </button>
          
          <div class="doctors-slider">
            <?php if ($doctors_result && $doctors_result->num_rows > 0): ?>
              <?php while ($doctor = $doctors_result->fetch_assoc()): ?>
                <div class="doctor-card">
                  <div class="doctor-image">
                    <img src="../assets/doctor-placeholder.jpg" alt="<?php echo htmlspecialchars($doctor['name']); ?>">
                  </div>
                  <div class="doctor-info">
                    <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                    <div class="doctor-specialty">
                      <span class="specialty-tag"><?php echo htmlspecialchars($doctor['specialization']); ?></span>
                      <span class="department-tag"><?php echo htmlspecialchars($doctor['department_name']); ?></span>
                    </div>
                    <div class="doctor-details">
                      <div class="detail-item">
                        <i class="fas fa-hospital"></i>
                        <span><?php echo htmlspecialchars($doctor['hospital_name']); ?></span>
                      </div>
                      <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo $doctor['experience']; ?> years experience</span>
                      </div>
                    </div>
                    <a href="bookappointment.php?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="btn-doctor">
                      Book Appointment
                    </a>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php endif; ?>
          </div>

          <button class="slider-arrow next-arrow" onclick="slideDoctors('next')">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </section>

        <!-- about section starts -->

        <section class="about" id="about">
          <div class="container">

            <div class="head">
                <h1 class="heading"> <span>About</span> us </h1>
            </div>
            <div class="row" style="display:flex;">
        
                <div class="image">
                    <img src="../assets/Screenshot 2025-03-05 235427.png" alt="">
                </div>
        
                <div class="content">
                  <h3>Your health, our priority – committed to excellence in care.</h3>
                
                  <p>MediHealth – Your trusted partner in healthcare. We provide expert medical services, reliable health information, and personalized care to support your well-being. With a commitment to excellence and compassion, MediHealth connects you to quality healthcare solutions for a healthier life.</p>
                  <a href="#" class="btn"> learn more <span class="fas fa-chevron-right"></span> </a>
              </div>
        
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

<style>
.greeting-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2rem 3rem;
    /* margin-top:2rem; */
    background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    /* margin: 2.75rem; */
    margin-top: 8rem;
    margin-bottom: 3rem;
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(52, 152, 219, 0.1);
    transition: all 0.3s ease;
}

.greeting-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(52, 152, 219, 0.05) 0%, rgba(52, 152, 219, 0) 100%);
    z-index: 0;
    margin-top:2 rem;
}

.greeting-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.greeting-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    position: relative;
    z-index: 1;
}

.greeting-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border-radius: 50%;
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
    animation: pulse 2s infinite;
}

.greeting-icon i {
    font-size: 1.8rem;
    color: white;
}

.greeting-text-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.greeting-text {
    font-size: 1.8rem;
    color: #2c3e50;
    margin: 0;
    font-weight: 700;
    letter-spacing: -0.5px;
}

.welcome-text {
    font-size: 1.2rem;
    color: #7f8c8d;
    margin: 0;
    font-weight: 500;
}

.profile-link {
    margin-left: 1rem;
    position: relative;
    z-index: 1;
}

.btn-outline {
    background: transparent;
    border: 2px solid #3498db;
    color: #3498db;
    padding: 0.85rem 1.8rem;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-size: 1.05rem;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.1);
}

.btn-outline:hover {
    background: #3498db;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
}

.btn-outline i {
    font-size: 1.2rem;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.4);
    }
    70% {
        box-shadow: 0 0 0 15px rgba(52, 152, 219, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(52, 152, 219, 0);
    }
}

@media (max-width: 768px) {
    .greeting-container {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
        padding: 1.8rem;
        margin: 1.5rem;
    }

    .greeting-content {
        flex-direction: column;
        align-items: center;
    }

    .greeting-icon {
        margin-bottom: 0.5rem;
    }

    .greeting-text {
        font-size: 1.6rem;
    }

    .profile-link {
        margin-left: 0;
        width: 100%;
    }

    .btn-outline {
        width: 100%;
        justify-content: center;
    }
}

/* Hospitals Section Styles */
.hospitals-section, .doctors-section {
  padding: 4rem 0;
  background-color: #f8f9fa;
}

.hospitals-section .section-header, .doctors-section .section-header {
  text-align: center;
  margin-bottom: 3rem;
}

.hospitals-section .badge, .doctors-section .badge {
  display: inline-block;
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
  color: white;
  padding: 0.5rem 1.5rem;
  border-radius: 50px;
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 1rem;
  box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
}

.hospitals-section h2, .doctors-section h2 {
  font-size: 2.2rem;
  color: #2c3e50;
  margin-bottom: 1rem;
  font-weight: 700;
}

.hospitals-section p, .doctors-section p {
  font-size: 1.1rem;
  color: #7f8c8d;
  max-width: 700px;
  margin: 0 auto;
}

.hospitals-slider, .doctors-slider {
  display: flex;
  gap: 2rem;
  overflow-x: auto;
  padding: 1rem 0.5rem;
  scrollbar-width: thin;
  scrollbar-color: #3498db #f0f0f0;
}

.hospitals-slider::-webkit-scrollbar, .doctors-slider::-webkit-scrollbar {
  height: 8px;
}

.hospitals-slider::-webkit-scrollbar-track, .doctors-slider::-webkit-scrollbar-track {
  background: #f0f0f0;
  border-radius: 10px;
}

.hospitals-slider::-webkit-scrollbar-thumb, .doctors-slider::-webkit-scrollbar-thumb {
  background: #3498db;
  border-radius: 10px;
}

.hospital-card, .doctor-card {
  min-width: 320px;
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,0.05);
  flex: 1;
}

.hospital-card:hover, .doctor-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.hospital-image, .doctor-image {
  height: 180px;
  overflow: hidden;
  position: relative;
}

.hospital-image img, .doctor-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.hospital-card:hover .hospital-image img, .doctor-card:hover .doctor-image img {
  transform: scale(1.1);
}

.hospital-info, .doctor-info {
  padding: 1.5rem;
}

.hospital-info h3, .doctor-info h3 {
  font-size: 1.3rem;
  color: #2c3e50;
  margin-bottom: 1rem;
  font-weight: 700;
}

.hospital-details, .doctor-details {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  margin-bottom: 1.5rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  color: #7f8c8d;
  font-size: 0.95rem;
}

.detail-item i {
  color: #3498db;
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.doctor-specialty {
  display: flex;
  gap: 0.8rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.specialty-tag, .department-tag {
  padding: 0.4rem 0.8rem;
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 600;
}

.specialty-tag {
  background: rgba(52, 152, 219, 0.1);
  color: #3498db;
}

.department-tag {
  background: rgba(46, 204, 113, 0.1);
  color: #2ecc71;
}

.btn-hospital, .btn-doctor {
  display: inline-block;
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
  color: white;
  padding: 0.8rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  text-align: center;
  width: 100%;
  box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
}

.btn-hospital:hover, .btn-doctor:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
}

.no-data {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  background: white;
  border-radius: 16px;
  min-width: 320px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.no-data i {
  font-size: 3rem;
  color: #bdc3c7;
  margin-bottom: 1rem;
}

.no-data p {
  color: #7f8c8d;
  font-size: 1.1rem;
}

@media (max-width: 768px) {
  .hospitals-section, .doctors-section {
    padding: 3rem 0;
  }
  
  .hospitals-section h2, .doctors-section h2 {
    font-size: 1.8rem;
  }
  
  .hospital-card, .doctor-card {
    min-width: 280px;
  }
  
  .hospital-image, .doctor-image {
    height: 160px;
  }
}

/* Doctors Section Styles */
.doctors-section {
  padding: 4rem 0;
  background: #f8f9fa;
}

.doctors-slider-container {
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 40px;
}

.doctors-slider {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.doctor-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  overflow: hidden;
  transition: transform 0.3s ease;
}

.doctor-card:hover {
  transform: translateY(-5px);
}

.doctor-image {
  width: 100%;
  height: 200px;
  overflow: hidden;
}

.doctor-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.doctor-info {
  padding: 1.5rem;
}

.doctor-info h3 {
  color: #2c3e50;
  font-size: 1.2rem;
  margin-bottom: 1rem;
}

.doctor-specialty {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.specialty-tag, .department-tag {
  padding: 0.3rem 0.6rem;
  border-radius: 4px;
  font-size: 0.8rem;
}

.specialty-tag {
  background: #e3f2fd;
  color: #1976d2;
}

.department-tag {
  background: #e8f5e9;
  color: #2e7d32;
}

.doctor-details {
  margin-bottom: 1.5rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  color: #666;
  font-size: 0.9rem;
}

.detail-item i {
  color: #3498db;
}

.btn-doctor {
  display: block;
  width: 100%;
  padding: 0.8rem;
  background: #3498db;
  color: white;
  text-align: center;
  border-radius: 4px;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.btn-doctor:hover {
  background: #2980b9;
}

.slider-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  background: white;
  border: none;
  border-radius: 50%;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  cursor: pointer;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
}

.slider-arrow:hover {
  background: #f8f9fa;
}

.prev-arrow {
  left: 0;
}

.next-arrow {
  right: 0;
}

@media (max-width: 1200px) {
  .doctors-slider {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 992px) {
  .doctors-slider {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 576px) {
  .doctors-slider {
    grid-template-columns: 1fr;
  }
}
</style>

<script>
  let currentSlide = 0;
  const doctorsSlider = document.querySelector('.doctors-slider');
  const doctorCards = document.querySelectorAll('.doctor-card');
  const cardsPerView = 4;
  const totalSlides = Math.ceil(doctorCards.length / cardsPerView);

  function slideDoctors(direction) {
    if (direction === 'next' && currentSlide < totalSlides - 1) {
      currentSlide++;
    } else if (direction === 'prev' && currentSlide > 0) {
      currentSlide--;
    }

    const offset = currentSlide * -100;
    doctorsSlider.style.transform = `translateX(${offset}%)`;
  }
</script>
