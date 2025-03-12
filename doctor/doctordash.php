<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediHealth - DoctorDash</title>
  <link rel="stylesheet" href="../css/doctordash.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <!-- Font Awesome for icons -->
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
</head>
<body class="dashboard-body">
  <!-- Dashboard Layout -->
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
      <div class="sidebar-header">
        <!-- <a href="index.html" class="logo">
          <div class="logo-icon">
            <i class="fa-solid fa-file-medical"></i>
          </div>
          <span>MediHealth</span>
        </a> -->
        <button id="toggleSidebar" class="toggle-sidebar">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
      
      <nav class="sidebar-nav">
        <ul>
          <li class="nav-item active">
            <a href="#dashboard">
              <i class="fa-solid fa-gauge-high"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#appointments">
              <i class="fa-solid fa-calendar-check"></i>
              <span>Appointments</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#patients">
              <i class="fa-solid fa-users"></i>
              <span>Patients</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#medical-records">
              <i class="fa-solid fa-file-medical"></i>
              <span>Medical Records</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#prescriptions">
              <i class="fa-solid fa-prescription"></i>
              <span>Prescriptions</span>
            </a>
          </li>
        </ul>
      </nav>
      
      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">
            <!-- <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Dr. John Doe"> -->
          </div>
          <div class="user-details">
            <h4>Dr. Balkrishna Shah</h4>
            <p>Pediatricians</p>
          </div>
        </div>
        <a href="../patient/logout.php" class="logout-btn">
          <i class="fa-solid fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      </div>
    </aside>
    
    <!-- Main Content -->
    <main class="dashboard-main">
      <!-- Top Navigation -->
      <header class="dashboard-header">
        
        
        <div class="header-actions">
          <button class="notification-btn">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-dot"></span>
          </button>
        </div>
      </header>
    
      <!-- Dashboard Content -->
      <div class="dashboard-content">
        <div class="dashboard-welcome">
          <h1>Good Morning, Dr. Balkrishna Sah</h1>
          <p>Here's your activity summary for today - Monday, 11 March, 2025</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-row">
          <div class="stat-card">
            <div class="stat-icon blue">
              <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="stat-details">
              <h3>Appointments</h3>
              <p class="stat-value">8 Today</p>
            </div>
            <div class="stat-progress">
              <p>2 Completed</p>
              <div class="progress-bar">
                <div class="progress-fill" style="width: 25%"></div>
              </div>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon teal">
              <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-details">
              <h3>Patients</h3>
              <p class="stat-value">24 Active</p>
            </div>
            <div class="stat-progress">
              <p>+3 New This Week</p>
              <div class="progress-bar">
                <div class="progress-fill teal" style="width: 75%"></div>
              </div>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon purple">
              <i class="fa-solid fa-prescription"></i>
            </div>
            <div class="stat-details">
              <h3>Prescriptions</h3>
              <p class="stat-value">12 Pending</p>
            </div>
            <div class="stat-progress">
              <p>5 Completed Today</p>
              <div class="progress-bar">
                <div class="progress-fill purple" style="width: 42%"></div>
              </div>
            </div>
          </div>
          </div>
        </div>
        
        <!-- Appointment Schedule -->
        <div class="content-row">
          <div class="content-card appointments-card">
            <div class="card-header">
              <h2>Today's Schedule</h2>
              <div class="card-actions">
                <button class="view-all-btn">View All</button>
              </div>
            </div>
            
            <div class="appointments-timeline">
              <div class="appointment-item upcoming">
                <div class="appointment-time">
                  <p class="time">09:00 AM</p>
                  <div class="time-indicator"></div>
                </div>
                <div class="appointment-details">
                  <div class="patient-avatar">
                    
                  </div>
                  <div class="appointment-info">
                    <h4>Komal Sah</h4>
                    <p>Follow-up Consultation - Hypertension</p>
                    <div class="appointment-actions">
                      <button class="appointment-btn view">
                        <i class="fa-solid fa-eye"></i> View
                      </button>
                      <button class="appointment-btn reschedule">
                        <i class="fa-solid fa-calendar"></i> Reschedule
                      </button>
                      <button class="appointment-btn cancel">
                        <i class="fa-solid fa-times"></i> Cancel
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="appointment-item current">
                <div class="appointment-time">
                  <p class="time">10:30 AM</p>
                  <div class="time-indicator"></div>
                </div>
                <div class="appointment-details">
                  <div class="patient-avatar">
                    <!-- <img src="https://randomuser.me/api/portraits/men/34.jpg" alt="Michael Chen"> -->
                  </div>
                  <div class="appointment-info">
                    <h4>Shibu Sharma</h4>
                    <p>Initial Consultation - Chest Pain</p>
                    <div class="appointment-actions">
                      <button class="appointment-btn start">
                        <i class="fa-solid fa-video"></i> Start Consultation
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="appointment-item">
                <div class="appointment-time">
                  <p class="time">01:00 PM</p>
                  <div class="time-indicator"></div>
                </div>
                <div class="appointment-details">
                  <div class="patient-avatar">
                    <!-- <img src="https://randomuser.me/api/portraits/women/63.jpg" alt="Emma Wilson"> -->
                  </div>
                  <div class="appointment-info">
                    <h4>Alina Khatoon</h4>
                    <p>Follow-up Consultation - Post Surgery</p>
                    <div class="appointment-actions">
                      <button class="appointment-btn view">
                        <i class="fa-solid fa-eye"></i> View
                      </button>
                      <button class="appointment-btn reschedule">
                        <i class="fa-solid fa-calendar"></i> Reschedule
                      </button>
                      <button class="appointment-btn cancel">
                        <i class="fa-solid fa-times"></i> Cancel
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="appointment-item">
                <div class="appointment-time">
                  <p class="time">03:30 PM</p>
                  <div class="time-indicator"></div>
                </div>
                <div class="appointment-details">
                  <div class="patient-avatar">
                    <!-- <img src="https://randomuser.me/api/portraits/men/52.jpg" alt="James Miller"> -->
                  </div>
                  <div class="appointment-info">
                    <h4>Mika Singh</h4>
                    <p>Regular Checkup - Cardiac Monitoring</p>
                    <div class="appointment-actions">
                      <button class="appointment-btn view">
                        <i class="fa-solid fa-eye"></i> View
                      </button>
                      <button class="appointment-btn reschedule">
                        <i class="fa-solid fa-calendar"></i> Reschedule
                      </button>
                      <button class="appointment-btn cancel">
                        <i class="fa-solid fa-times"></i> Cancel
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
  
  <!-- Toast Notification -->
  <div id="toast" class="toast">
    <div class="toast-content">
      <i class="fa-solid fa-circle-check"></i>
      <div class="toast-message">
       
      </div>
    </div>
    <div class="toast-progress"></div>
  </div>

  <!-- <script src="script.js"></script> -->
  <!-- <script src="dashboard.js"></script> -->
</body>
</html>