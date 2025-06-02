<?php
session_start();
include_once('../config/configdatabase.php');

// Function to store doctor data in session
function storeDoctorData($doctor_id, $conn) {
    $query = "SELECT d.*, h.id as hospital_id, h.name as hospital_name, 
              h.zone, h.district, h.city,
              dep.department_id, dep.department_name
              FROM doctor d 
              JOIN hospital h ON d.hospitalid = h.id 
              JOIN department dep ON d.department_id = dep.department_id 
              WHERE d.doctor_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $doctor_data = $result->fetch_assoc();
        $_SESSION['doctor_booking_data'] = [
            'doctor_id' => $doctor_id,
            'hospital_id' => $doctor_data['hospital_id'],
            'department_id' => $doctor_data['department_id'],
            'zone' => $doctor_data['zone'],
            'district' => $doctor_data['district'],
            'city' => $doctor_data['city'],
            'source' => 'doctor_profile'
        ];
        return true;
    }
    $stmt->close();
    return false;
}

// Handle booking request
if (isset($_GET['book_doctor'])) {
    $doctor_id = $_GET['book_doctor'];
    if (storeDoctorData($doctor_id, $conn)) {
        header("Location: bookappointment.php");
        exit();
    } else {
        $_SESSION['error'] = "Could not find doctor information. Please try again.";
        header("Location: ourdoctors.php");
        exit();
    }
}

// Include header after all potential redirects
include_once('../include/header.php');

// Fetch all doctors with their department and hospital information
$query = "SELECT d.*, dep.department_name, h.name as hospital_name, 
          CONCAT(h.city, ', ', h.district, ', ', h.zone) as hospital_location 
          FROM doctor d 
          JOIN department dep ON d.department_id = dep.department_id 
          JOIN hospital h ON d.hospitalid = h.id 
          WHERE d.status = 'active' AND d.is_specialist = 1
          ORDER BY d.name";
$result = $conn->query($query);

// Fetch all departments for filtering
$deptQuery = "SELECT * FROM department ORDER BY department_name";
$deptResult = $conn->query($deptQuery);
?>

<body>
  <main>
    <!-- Doctors Section -->
    <section class="doctors-section">
      <div class="container">
        <div class="section-header">
          <h1>Our Doctors</h1>
          <p>Meet our team of experienced and qualified healthcare professionals.</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
          <form method="GET" action="" class="filter-form">
            <div class="search-box">
              <input type="text" name="search" placeholder="Search doctors by name or specialization..." 
                     value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </div>
            
            <div class="filter-options">
              <select name="department" id="departmentFilter">
                <option value="">All Departments</option>
                <?php 
                $deptResult->data_seek(0);
                while($dept = $deptResult->fetch_assoc()): 
                    $selected = (isset($_GET['department']) && $_GET['department'] == $dept['department_name']) ? 'selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars($dept['department_name']); ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($dept['department_name']); ?>
                    </option>
                <?php endwhile; ?>
              </select>
              
              <select name="experience" id="experienceFilter">
                <option value="">All Experience Levels</option>
                <?php
                $exp_ranges = [
                    '0-5' => '0-5 years',
                    '5-10' => '5-10 years',
                    '10-15' => '10-15 years',
                    '15+' => '15+ years'
                ];
                foreach($exp_ranges as $value => $label):
                    $selected = (isset($_GET['experience']) && $_GET['experience'] == $value) ? 'selected' : '';
                ?>
                    <option value="<?php echo $value; ?>" <?php echo $selected; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
              </select>
              
              <button type="submit" class="filter-btn">Apply Filters</button>
            </div>
          </form>
        </div>

        <!-- Doctors Grid -->
        <div class="doctors-grid">
          <?php 
          if($result->num_rows > 0):
              while($doctor = $result->fetch_assoc()):
                  // Apply filters
                  $show_doctor = true;
                  
                  if(isset($_GET['search']) && !empty($_GET['search'])) {
                      $search = strtolower($_GET['search']);
                      if(strpos(strtolower($doctor['name']), $search) === false && 
                         strpos(strtolower($doctor['specialization']), $search) === false) {
                          $show_doctor = false;
                      }
                  }
                  
                  if(isset($_GET['department']) && !empty($_GET['department'])) {
                      if($doctor['department_name'] != $_GET['department']) {
                          $show_doctor = false;
                      }
                  }
                  
                  if(isset($_GET['experience']) && !empty($_GET['experience'])) {
                      $exp = $doctor['experience'];
                      list($min, $max) = explode('-', $_GET['experience']);
                      if($max) {
                          if($exp < $min || $exp > $max) {
                              $show_doctor = false;
                          }
                      } else {
                          if($exp < 15) {
                              $show_doctor = false;
                          }
                      }
                  }
                  
                  if($show_doctor):
          ?>
              <div class="doctor-card">
                <div class="doctor-info">
                  <div class="doctor-avatar">
                    <?php if (!empty($doctor['profile_image'])): ?>
                        <img src="../uploads/doctor_profiles/<?php echo htmlspecialchars($doctor['profile_image']); ?>" alt="Doctor Profile">
                    <?php else: ?>
                        <?php echo strtoupper(substr($doctor['name'], 0, 1)); ?>
                    <?php endif; ?>
                  </div>
                  <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                  <div class="doctor-tags">
                    <span class="specialty-tag"><?php echo htmlspecialchars($doctor['specialization']); ?></span>
                    <span class="department-tag"><?php echo htmlspecialchars($doctor['department_name']); ?></span>
                  </div>
                  <div class="doctor-details">
                    <div class="detail-item">
                      <i class="fas fa-hospital"></i>
                      <span><?php echo htmlspecialchars($doctor['hospital_name']); ?></span>
                    </div>
                    <div class="detail-item">
                      <i class="fas fa-map-marker-alt"></i>
                      <span><?php echo htmlspecialchars($doctor['hospital_location']); ?></span>
                    </div>
                    <div class="detail-item">
                      <i class="fas fa-graduation-cap"></i>
                      <span><?php echo htmlspecialchars($doctor['qualification']); ?></span>
                    </div>
                    <div class="detail-item">
                      <i class="fas fa-clock"></i>
                      <span><?php echo $doctor['experience']; ?> years experience</span>
                    </div>
                  </div>
                  <div class="doctor-actions">
                    <a href="ourdoctors.php?book_doctor=<?php echo $doctor['doctor_id']; ?>" class="btn-book">
                      <i class="fas fa-calendar-check"></i> Book Appointment
                    </a>
                  </div>
                </div>
              </div>
          <?php 
                  endif;
              endwhile;
          else: 
          ?>
            <div class="no-results">
              <i class="fas fa-user-md"></i>
              <h3>No doctors found</h3>
              <p>We couldn't find any doctors matching your criteria. Please try different filters.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <?php include('../include/footer.php'); ?>

  <style>
    .btn-outline {
    background: transparent;
    border: 2px solid #3498db;
    color: #3498db;
    /* padding: 0.85rem 1.8rem;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-size: 1.05rem;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.1); */
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
    /* Doctors Page Styles */
    .doctors-section {
      padding: 4rem 0;
      background: #f8f9fa;
    }

    .section-header {
      text-align: center;
      margin-bottom: 3rem;
    }

    .section-header h1 {
      color: #2c3e50;
      font-size: 2.5rem;
      margin-bottom: 1rem;
      margin-top: 1.5rem;
      font-weight: 600;
    }

    .section-header p {
      color: #7f8c8d;
      font-size: 1.1rem;
      max-width: 600px;
      margin: 0 auto;
    }

    /* Filter Section */
    .filter-section {
      max-width: 1200px;
      margin: 0 auto 2rem;
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .search-box {
      flex: 1;
      min-width: 300px;
      position: relative;
    }

    .search-box input {
      width: 100%;
      padding: 0.8rem 1rem 0.8rem 2.5rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }

    .search-box i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #95a5a6;
    }

    .filter-options {
      display: flex;
      gap: 1rem;
    }

    .filter-options select {
      padding: 0.8rem 1rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
      background-color: white;
      min-width: 150px;
    }

    /* Doctors Grid */
    .doctors-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .doctor-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      padding: 1.5rem;
    }

    .doctor-info h3 {
      color: #2c3e50;
      font-size: 1.3rem;
      margin-bottom: 1rem;
      font-weight: 600;
    }

    .doctor-tags {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }

    .specialty-tag, .department-tag {
      padding: 0.4rem 0.8rem;
      border-radius: 4px;
      font-size: 0.9rem;
      font-weight: 500;
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
      width: 20px;
      text-align: center;
    }

    .doctor-actions {
      margin-top: 1rem;
    }

    .btn-book {
      display: block;
      width: 100%;
      padding: 0.8rem;
      background: #3498db;
      color: white;
      text-align: center;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 500;
      transition: background-color 0.3s ease;
    }

    .btn-book:hover {
      background: #2980b9;
    }

    .no-results {
      grid-column: 1 / -1;
      text-align: center;
      padding: 3rem;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .no-results i {
      font-size: 3rem;
      color: #bdc3c7;
      margin-bottom: 1rem;
    }

    .no-results h3 {
      color: #2c3e50;
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }

    .no-results p {
      color: #7f8c8d;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
      .section-header h1 {
        font-size: 2rem;
      }

      .filter-section {
        flex-direction: column;
      }

      .search-box {
        width: 100%;
      }

      .filter-options {
        width: 100%;
      }

      .filter-options select {
        flex: 1;
      }

      .doctors-grid {
        grid-template-columns: 1fr;
      }
    }

    .filter-form {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      width: 100%;
    }
    
    .filter-btn {
      padding: 0.8rem 1.5rem;
      background: #3498db;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
    }
    
    .filter-btn:hover {
      background: #2980b9;
    }
    
    .alert {
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 4px;
    }
    
    .alert-danger {
      background: #fee2e2;
      color: #ef4444;
      border: 1px solid #ef4444;
    }

    .doctor-avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: linear-gradient(135deg, #3498db, #2980b9);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 2.5rem;
      font-weight: bold;
      margin: 0 auto 1rem;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(52,152,219,0.3);
    }

    .doctor-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  </style>
</body>
</html> 