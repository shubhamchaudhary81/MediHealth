<?php
include_once('../include/header.php');
include_once('../config/configdatabase.php');

// Fetch all doctors with their department and hospital information
$query = "SELECT d.*, dep.department_name, h.name as hospital_name, 
          CONCAT(h.city, ', ', h.district, ', ', h.zone) as hospital_location 
          FROM doctor d 
          JOIN department dep ON d.department_id = dep.department_id 
          JOIN hospital h ON d.hospitalid = h.id 
          WHERE d.status = 'active' 
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

        <!-- Filter Section -->
        <div class="filter-section">
          <div class="search-box">
            <input type="text" id="doctorSearch" placeholder="Search doctors by name or specialization...">
            <i class="fas fa-search"></i>
          </div>
          
          <div class="filter-options">
            <select id="departmentFilter">
              <option value="">All Departments</option>
              <?php while($dept = $deptResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($dept['department_name']); ?>">
                  <?php echo htmlspecialchars($dept['department_name']); ?>
                </option>
              <?php endwhile; ?>
            </select>
            
            <select id="experienceFilter">
              <option value="">All Experience Levels</option>
              <option value="0-5">0-5 years</option>
              <option value="5-10">5-10 years</option>
              <option value="10-15">10-15 years</option>
              <option value="15+">15+ years</option>
            </select>
          </div>
        </div>

        <!-- Doctors Grid -->
        <div class="doctors-grid" id="doctorsGrid">
          <?php if($result->num_rows > 0): ?>
            <?php while($doctor = $result->fetch_assoc()): ?>
              <div class="doctor-card" 
                   data-department="<?php echo htmlspecialchars($doctor['department_name']); ?>"
                   data-experience="<?php echo $doctor['experience']; ?>">
                <div class="doctor-info">
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
                    <a href="bookappointment.php?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="btn-book">
                      Book Appointment
                    </a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
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
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('doctorSearch');
      const departmentFilter = document.getElementById('departmentFilter');
      const experienceFilter = document.getElementById('experienceFilter');
      const doctorsGrid = document.getElementById('doctorsGrid');
      const doctorCards = document.querySelectorAll('.doctor-card');
      
      function filterDoctors() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedDept = departmentFilter.value;
        const selectedExp = experienceFilter.value;
        
        doctorCards.forEach(card => {
          const doctorName = card.querySelector('h3').textContent.toLowerCase();
          const doctorSpecialty = card.querySelector('.specialty-tag').textContent.toLowerCase();
          const doctorDept = card.getAttribute('data-department');
          const doctorExp = parseInt(card.getAttribute('data-experience'));
          
          let showCard = true;
          
          // Search filter
          if (searchTerm && !doctorName.includes(searchTerm) && !doctorSpecialty.includes(searchTerm)) {
            showCard = false;
          }
          
          // Department filter
          if (selectedDept && doctorDept !== selectedDept) {
            showCard = false;
          }
          
          // Experience filter
          if (selectedExp) {
            const [min, max] = selectedExp.split('-').map(Number);
            if (max) {
              if (doctorExp < min || doctorExp > max) {
                showCard = false;
              }
            } else {
              // For "15+" option
              if (doctorExp < 15) {
                showCard = false;
              }
            }
          }
          
          card.style.display = showCard ? 'block' : 'none';
        });
        
        // Show no results message if all cards are hidden
        const visibleCards = document.querySelectorAll('.doctor-card[style="display: block"]');
        const noResults = document.querySelector('.no-results');
        
        if (visibleCards.length === 0) {
          if (!noResults) {
            const noResultsDiv = document.createElement('div');
            noResultsDiv.className = 'no-results';
            noResultsDiv.innerHTML = `
              <i class="fas fa-user-md"></i>
              <h3>No doctors found</h3>
              <p>We couldn't find any doctors matching your criteria. Please try different filters.</p>
            `;
            doctorsGrid.appendChild(noResultsDiv);
          }
        } else if (noResults) {
          noResults.remove();
        }
      }
      
      searchInput.addEventListener('input', filterDoctors);
      departmentFilter.addEventListener('change', filterDoctors);
      experienceFilter.addEventListener('change', filterDoctors);
    });
  </script>
</body>
</html> 