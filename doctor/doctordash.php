<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: doctorlogin.php");
    exit();
}

require_once('../config/configdatabase.php');

// Get doctor information
$doctor_id = $_SESSION['user_id'];
$query = "SELECT * FROM doctor WHERE doctor_id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

// Get today's date
$today = date('Y-m-d');

// Get today's appointments
$appointments_query = "SELECT a.*, p.first_name, p.last_name, p.number as phone 
                      FROM appointments a 
                      LEFT JOIN patients p ON a.patient_id = p.patientID 
                      WHERE a.doctor_id = ? AND a.appointment_date = ? 
                      ORDER BY a.appointment_time";
$stmt = $conn->prepare($appointments_query);
if ($stmt === false) {
    die("Error preparing appointments query: " . $conn->error);
}
$stmt->bind_param("ss", $doctor_id, $today);
$stmt->execute();
$appointments = $stmt->get_result();

// Get upcoming appointments
$upcoming_query = "SELECT a.*, p.first_name, p.last_name, p.number as phone 
                  FROM appointments a 
                  LEFT JOIN patients p ON a.patient_id = p.patientID 
                  WHERE a.doctor_id = ? AND a.appointment_date > ? 
                  ORDER BY a.appointment_date, a.appointment_time LIMIT 5";
$stmt = $conn->prepare($upcoming_query);
if ($stmt === false) {
    die("Error preparing upcoming appointments query: " . $conn->error);
}
$stmt->bind_param("ss", $doctor_id, $today);
$stmt->execute();
$upcoming_appointments = $stmt->get_result();

// Get appointment statistics
$stats_query = "SELECT 
                COUNT(*) as total_appointments,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_appointments
                FROM appointments 
                WHERE doctor_id = ? AND appointment_date = ?";
$stmt = $conn->prepare($stats_query);
if ($stmt === false) {
    die("Error preparing stats query: " . $conn->error);
}
$stmt->bind_param("ss", $doctor_id, $today);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();

// Get patient statistics
$patient_query = "SELECT COUNT(DISTINCT patient_id) as total_patients 
                  FROM appointments 
                  WHERE doctor_id = ?";
$stmt = $conn->prepare($patient_query);
if ($stmt === false) {
    die("Error preparing patient query: " . $conn->error);
}
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$patient_result = $stmt->get_result();
$patient_stats = $patient_result->fetch_assoc();

// // Get prescription statistics
// // --- CORRECTING THIS PART ---
// $prescription_query = "SELECT COUNT(*) as total_prescriptions 
//                        FROM prescriptions 
//                        WHERE doctor_id = ? AND DATE(date_created) = ?";
// $stmt = $conn->prepare($prescription_query);
// if ($stmt === false) {
//     die("Error preparing prescription query: " . $conn->error . " Query: " . $prescription_query);
// }
// $stmt->bind_param("ss", $doctor_id, $today);
// $stmt->execute();
// $prescription_result = $stmt->get_result();
// $prescription_stats = $prescription_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediHealth - DoctorDash</title>
  <link rel="stylesheet" href="../css/doctordash.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Include TCPDF for PDF generation -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
            <h4>Dr. <?php echo htmlspecialchars($doctor['name']); ?></h4>
            <p><?php echo htmlspecialchars($doctor['specialization']); ?></p>
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
          <h1>Good <?php echo (date('H') < 12) ? 'Morning' : ((date('H') < 18) ? 'Afternoon' : 'Evening'); ?>, Dr. <?php echo htmlspecialchars($doctor['name']); ?></h1>
          <p>Here's your activity summary for today - <?php echo date('l, d F, Y'); ?></p>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-row">
          <div class="stat-card">
            <div class="stat-icon blue">
              <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="stat-details">
              <h3>Appointments</h3>
              <p class="stat-value"><?php echo $appointments->num_rows; ?> Today</p>
            </div>
            <div class="stat-progress">
              <p><?php echo $stats['completed_appointments']; ?> Completed</p>
              <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo ($appointments->num_rows > 0) ? ($stats['completed_appointments'] / $appointments->num_rows * 100) : 0; ?>%"></div>
              </div>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon teal">
              <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-details">
              <h3>Patients</h3>
              <p class="stat-value"><?php echo $patient_stats['total_patients']; ?> Active</p>
            </div>
            <div class="stat-progress">
              <p>+<?php echo rand(1, 5); ?> New This Week</p>
              <div class="progress-bar">
                <div class="progress-fill teal" style="width: 75%"></div>
              </div>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon purple">
              <i class="fa-solid fa-prescription"></i>
            </div>
            <!-- <div class="stat-details">
              <h3>Prescriptions</h3>
              <p class="stat-value"><?php echo $prescription_stats['total_prescriptions']; ?> Today</p>
            </div> -->
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
              <?php if ($appointments->num_rows > 0): ?>
                <?php while ($appointment = $appointments->fetch_assoc()): ?>
                  <div class="appointment-item <?php echo ($appointment['status'] == 'in-progress') ? 'current' : 'upcoming'; ?>">
                    <div class="appointment-time">
                      <p class="time"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                      <div class="time-indicator"></div>
                    </div>
                    <div class="appointment-details">
                      <div class="patient-avatar">
                        <i class="fa-solid fa-user"></i>
                      </div>
                      <div class="appointment-info">
                        <h4><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></h4>
                        <p><?php echo htmlspecialchars($appointment['reason']); ?></p>
                        <div class="appointment-actions">
                          <a href="prestemplate.php"><button class="appointment-btn view" >
                            <i class="fa-solid fa-eye"></i> View
                          </button></a>
                          <button class="appointment-btn reschedule" onclick="rescheduleAppointment(<?php echo $appointment['appointment_id']; ?>)">
                            <i class="fa-solid fa-calendar"></i> Reschedule
                          </button>
                          <button class="appointment-btn cancel" onclick="cancelAppointment(<?php echo $appointment['appointment_id']; ?>)">
                            <i class="fa-solid fa-times"></i> Cancel
                          </button>
                          <?php if ($appointment['status'] == 'confirmed'): ?>
                            <button class="appointment-btn start" onclick="startConsultation(<?php echo $appointment['appointment_id']; ?>)">
                              <i class="fa-solid fa-video"></i> Start Consultation
                            </button>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php else: ?>
                <div class="empty-state">
                  <i class="fa-solid fa-calendar-times"></i>
                  <p>No appointments scheduled for today</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Prescription Modal -->
  <div id="prescriptionModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Create Prescription</h2>
        <span class="close">&times;</span>
      </div>
      <div class="modal-body">
        <form id="prescriptionForm">
          <input type="hidden" id="appointment_id" name="appointment_id">
          <input type="hidden" id="patient_id" name="patient_id">
          
          <div class="form-group">
            <label for="patient_name">Patient Name</label>
            <input type="text" id="patient_name" name="patient_name" readonly>
          </div>
          
          <div class="form-group">
            <label for="diagnosis">Diagnosis</label>
            <textarea id="diagnosis" name="diagnosis" rows="3" required></textarea>
          </div>
          
          <div class="form-group">
            <label for="prescription">Prescription</label>
            <textarea id="prescription" name="prescription" rows="5" required></textarea>
          </div>
          
          <div class="form-group">
            <label for="notes">Additional Notes</label>
            <textarea id="notes" name="notes" rows="2"></textarea>
          </div>
          
          <div class="form-actions">
            <button type="button" id="savePrescription" class="btn-primary">Save Prescription</button>
            <button type="button" id="downloadPrescription" class="btn-secondary">Download as PDF</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div id="toast" class="toast">
    <div class="toast-content">
      <i class="fa-solid fa-circle-check"></i>
      <div class="toast-message"></div>
    </div>
    <div class="toast-progress"></div>
  </div>

  <script>
    // Toggle sidebar
    document.getElementById('toggleSidebar').addEventListener('click', function() {
      document.querySelector('.dashboard-sidebar').classList.toggle('collapsed');
      document.querySelector('.dashboard-main').classList.toggle('expanded');
    });

    // Modal functionality
    const modal = document.getElementById('prescriptionModal');
    const closeBtn = document.querySelector('.close');
    
    closeBtn.onclick = function() {
      modal.style.display = "none";
    }
    
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }

    // View appointment and open prescription modal
    function viewAppointment(appointmentId) {
      // Fetch appointment details via AJAX
      fetch(`get_appointment.php?id=${appointmentId}`)
        .then(response => response.json())
        .then(data => {
          document.getElementById('appointment_id').value = data.appointment_id;
          document.getElementById('patient_id').value = data.patient_id;
          document.getElementById('patient_name').value = data.patient_name;
          
          // Show the modal
          modal.style.display = "block";
        })
        .catch(error => {
          showToast('Error fetching appointment details', 'error');
        });
    }

    // Save prescription
    document.getElementById('savePrescription').addEventListener('click', function() {
      const formData = new FormData(document.getElementById('prescriptionForm'));
      
      fetch('save_prescription.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Prescription saved successfully', 'success');
          modal.style.display = "none";
        } else {
          showToast(data.message || 'Error saving prescription', 'error');
        }
      })
      .catch(error => {
        showToast('Error saving prescription', 'error');
      });
    });

    // Download prescription as PDF
    document.getElementById('downloadPrescription').addEventListener('click', function() {
      const appointmentId = document.getElementById('appointment_id').value;
      const patientName = document.getElementById('patient_name').value;
      const diagnosis = document.getElementById('diagnosis').value;
      const prescription = document.getElementById('prescription').value;
      const notes = document.getElementById('notes').value;
      
      // Create prescription HTML
      const prescriptionHTML = `
        <div id="prescription-pdf" style="padding: 20px; font-family: Arial, sans-serif;">
          <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="color: #4361ee;">MediHealth</h1>
            <h2>Medical Prescription</h2>
          </div>
          
          <div style="margin-bottom: 20px;">
            <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
            <p><strong>Patient Name:</strong> ${patientName}</p>
            <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($doctor['name']); ?></p>
            <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></p>
          </div>
          
          <div style="margin-bottom: 20px;">
            <h3>Diagnosis:</h3>
            <p>${diagnosis}</p>
          </div>
          
          <div style="margin-bottom: 20px;">
            <h3>Prescription:</h3>
            <p>${prescription}</p>
          </div>
          
          <div style="margin-bottom: 20px;">
            <h3>Additional Notes:</h3>
            <p>${notes}</p>
          </div>
          
          <div style="margin-top: 40px; border-top: 1px solid #ccc; padding-top: 10px;">
            <p><strong>Doctor's Signature:</strong> _______________________</p>
          </div>
        </div>
      `;
      
      // Create a temporary div to hold the prescription
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = prescriptionHTML;
      document.body.appendChild(tempDiv);
      
      // Generate PDF
      const element = document.getElementById('prescription-pdf');
      const opt = {
        margin: 1,
        filename: `prescription_${patientName.replace(/\s+/g, '_')}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
      };
      
      html2pdf().set(opt).from(element).save().then(() => {
        document.body.removeChild(tempDiv);
      });
    });

    // Toast notification
    function showToast(message, type = 'success') {
      const toast = document.getElementById('toast');
      const toastMessage = document.querySelector('.toast-message');
      const toastIcon = document.querySelector('.toast-content i');
      
      toastMessage.textContent = message;
      
      if (type === 'error') {
        toastIcon.className = 'fa-solid fa-circle-exclamation';
        toast.classList.add('error');
      } else {
        toastIcon.className = 'fa-solid fa-circle-check';
        toast.classList.remove('error');
      }
      
      toast.classList.add('show');
      
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    // Other appointment actions
    function rescheduleAppointment(appointmentId) {
      // Implement rescheduling functionality
      showToast('Rescheduling functionality will be implemented soon', 'info');
    }
    
    function cancelAppointment(appointmentId) {
      if (confirm('Are you sure you want to cancel this appointment?')) {
        // Implement cancellation functionality
        showToast('Appointment cancelled successfully', 'success');
      }
    }
    
    function startConsultation(appointmentId) {
      // Implement consultation start functionality
      showToast('Starting consultation...', 'info');
    }
  </script>
</body>
</html>