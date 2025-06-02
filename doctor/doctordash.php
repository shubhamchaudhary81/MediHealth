<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: doctorlogin.php");
    exit();
}

require_once('../config/configdatabase.php');

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

// Get doctor information
$doctor_id = $_SESSION['user_id'];
$query = "SELECT d.*, h.name as hospital_name 
          FROM doctor d 
          LEFT JOIN hospital h ON d.hospitalid = h.id 
          WHERE d.doctor_id = ?";
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
$appointments_query = "SELECT a.*, p.first_name, p.last_name, p.number as phone, p.patientID, op.name as other_name, op.phone as other_phone FROM appointments a LEFT JOIN patients p ON a.patient_id = p.patientID LEFT JOIN other_patients op ON a.other_patient_id = op.id WHERE a.doctor_id = ? AND a.appointment_date = ? AND a.status != 'completed' ORDER BY a.appointment_time";
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediHealth</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --border-color: #dee2e6;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: var(--dark-color);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.25rem;
        }

        .logo i {
            font-size: 1.5rem;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-item {
            list-style: none;
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: var(--gray-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--light-color);
            color: var(--primary-color);
        }

        .nav-link i {
            font-size: 1.25rem;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        .header {
            background: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            position: relative;
            overflow: hidden;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-image:hover .profile-overlay {
            opacity: 1;
        }

        .profile-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .profile-overlay i {
            color: white;
            font-size: 1.25rem;
        }

        .welcome-text h1 {
            font-size: 1.5rem;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .welcome-text p {
            color: var(--gray-color);
            font-size: 0.875rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.blue { background: var(--primary-color); }
        .stat-icon.teal { background: var(--success-color); }
        .stat-icon.purple { background: var(--warning-color); }

        .stat-info h3 {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: var(--gray-color);
            font-size: 0.875rem;
        }

        /* Appointments Section */
        .appointments-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            font-size: 1.25rem;
            color: var(--dark-color);
        }

        .appointment-list {
            display: grid;
            gap: 1rem;
        }

        .appointment-card {
            background: var(--light-color);
            padding: 1rem;
            border-radius: 8px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1rem;
            align-items: center;
        }

        .appointment-time {
            text-align: center;
            padding: 0.5rem;
            background: white;
            border-radius: 6px;
            min-width: 100px;
        }

        .appointment-time .time {
            font-weight: 600;
            color: var(--primary-color);
        }

        .appointment-details h4 {
            margin-bottom: 0.25rem;
        }

        .appointment-details p {
            color: var(--gray-color);
            font-size: 0.875rem;
        }

        .appointment-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background: var(--light-color);
            color: var(--dark-color);
        }

        .btn-danger {
            background: var(--warning-color);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            color: var(--dark-color);
        }

        .close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-color);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }

            .sidebar .logo span,
            .sidebar .nav-link span {
                display: none;
            }

            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .appointment-card {
                grid-template-columns: 1fr;
            }

            .appointment-actions {
                flex-wrap: wrap;
            }
        }

        .profile-image-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 20px;
            position: relative;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4a90e2;
            color: white;
            font-size: 4rem;
            font-weight: bold;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="welcome-section">
                    <div class="profile-image">
                        <?php if (!empty($doctor['profile_image'])): ?>
                            <img src="../uploads/doctor_profiles/<?php echo htmlspecialchars($doctor['profile_image']); ?>" alt="Profile Image">
                        <?php else: ?>
                            <?php echo strtoupper(substr($doctor['name'], 0, 1)); ?>
                        <?php endif; ?>
                        <div class="profile-overlay" onclick="showProfileModal()">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <div class="welcome-text">
                        <h1><?php echo $greeting; ?>, Dr. <?php echo htmlspecialchars($doctor['name']); ?></h1>
                        <p><?php echo htmlspecialchars($doctor['hospital_name']); ?></p>
                    </div>
                </div>
        <div class="header-actions">
                    <button class="btn btn-secondary">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
          </button>
        </div>
      </header>
    
            <!-- Stats Grid -->
            <div class="stats-grid">
          <div class="stat-card">
                    <div class="stat-header">
            <div class="stat-icon blue">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Today's Appointments</h3>
                            <p><?php echo $appointments->num_rows; ?> Total</p>
            </div>
            </div>
            <div class="stat-progress">
              <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo ($appointments->num_rows > 0) ? ($stats['completed_appointments'] / $appointments->num_rows * 100) : 0; ?>%"></div>
              </div>
                        <p><?php echo $stats['completed_appointments']; ?> Completed</p>
            </div>
          </div>
          
          <div class="stat-card">
                    <div class="stat-header">
            <div class="stat-icon teal">
                            <i class="fas fa-users"></i>
            </div>
                        <div class="stat-info">
                            <h3>Total Patients</h3>
                            <p><?php echo $patient_stats['total_patients']; ?> Active</p>
              </div>
            </div>
          </div>
          
          <div class="stat-card">
                    <div class="stat-header">
            <div class="stat-icon purple">
                            <i class="fas fa-prescription"></i>
            </div>
                        <div class="stat-info">
              <h3>Prescriptions</h3>
                            <p>Manage patient prescriptions</p>
            </div>
          </div>
          </div>
        </div>
        
            <!-- Today's Appointments -->
            <section class="appointments-section">
                <div class="section-header">
              <h2>Today's Schedule</h2>
                    <!-- <button class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <span>New Appointment</span>
                    </button> -->
            </div>
            
                <div class="appointment-list">
              <?php if ($appointments->num_rows > 0): ?>
                <?php while ($appointment = $appointments->fetch_assoc()): ?>
                            <div class="appointment-card">
                    <div class="appointment-time">
                      <p class="time"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
                    </div>
                    <div class="appointment-details">
                        <h4>
                            <?php if ($appointment['appointment_for'] === 'others'): ?>
                                <?php echo htmlspecialchars($appointment['other_name']); ?>
                            <?php else: ?>
                                <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                            <?php endif; ?>
                        </h4>
                        <p><?php echo htmlspecialchars($appointment['reason']); ?></p>
                        <div class="patient-info">
                            <small>Phone: 
                                <?php if ($appointment['appointment_for'] === 'others'): ?>
                                    <?php echo htmlspecialchars($appointment['other_phone']); ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($appointment['phone']); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                      </div>
                                <div class="appointment-actions">
                                    <button class="btn btn-primary" onclick="viewPatientDetails(<?php echo $appointment['patientID']; ?>)">
                                        <i class="fas fa-user"></i>
                                        <span>View Details</span>
                                    </button>
                                    <a href="prestemplate.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-secondary">
                                        <i class="fas fa-prescription"></i>
                                        <span>Prescription</span>
                                    </a>
                                    <button class="btn btn-danger" onclick="cancelAppointment(<?php echo $appointment['appointment_id']; ?>)">
                                        <i class="fas fa-times"></i>
                                        <span>Cancel</span>
                                    </button>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php else: ?>
                <div class="empty-state">
                  <p>No appointments scheduled for today</p>
                </div>
              <?php endif; ?>
            </div>
            </section>
    </main>
  </div>

    <!-- Profile Modal -->
    <div id="profileModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
                <h2>Update Profile</h2>
                <button class="close" onclick="closeProfileModal()">&times;</button>
            </div>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>
            <form id="profileForm" method="POST" action="update_profile.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profileImage">Profile Image</label>
                    <div class="profile-image-preview">
                        <?php if (!empty($doctor['profile_image'])): ?>
                            <img src="../uploads/doctor_profiles/<?php echo htmlspecialchars($doctor['profile_image']); ?>" alt="Profile Image" id="imagePreview">
                        <?php else: ?>
                            <div class="profile-image-placeholder" id="imagePreview">
                                <?php echo strtoupper(substr($doctor['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" id="profileImage" name="profileImage" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <small>Max file size: 5MB. Allowed formats: JPG, PNG, GIF</small>
                </div>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="specialization">Specialization</label>
                    <input type="text" id="specialization" name="specialization" class="form-control" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control">
                    <small>Leave blank if you don't want to change password</small>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
      </div>
    </div>

    <!-- Patient Details Modal -->
    <div id="patientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Patient Details</h2>
                <button class="close" onclick="closePatientModal()">&times;</button>
  </div>
            <div id="patientDetails">
                <!-- Patient details will be loaded here -->
    </div>
  </div>
    </div>


  <script>
        // Profile Modal Functions
        function showProfileModal() {
            document.getElementById('profileModal').style.display = 'flex';
        }

        function closeProfileModal() {
            document.getElementById('profileModal').style.display = 'none';
        }

        // Patient Modal Functions
        function viewPatientDetails(patientId) {
            fetch(`get_patient_details.php?id=${patientId}`)
        .then(response => response.json())
        .then(data => {
                    document.getElementById('patientDetails').innerHTML = `
                        <div class="patient-info">
                            <h3>${data.first_name} ${data.last_name}</h3>
                            <p><strong>Phone:</strong> ${data.number}</p>
                            <p><strong>Email:</strong> ${data.email}</p>
                            <p><strong>Medical History:</strong> ${data.medical_history || 'None'}</p>
                            <p><strong>Allergies:</strong> ${data.allergies || 'None'}</p>
                            <p><strong>Current Medications:</strong> ${data.current_medications || 'None'}</p>
                        </div>
                    `;
                    document.getElementById('patientModal').style.display = 'flex';
        })
        .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading patient details');
                });
        }

        function closePatientModal() {
            document.getElementById('patientModal').style.display = 'none';
        }

        // Prescription Modal Functions
        function viewPrescription(appointmentId) {
            document.getElementById('appointment_id').value = appointmentId;
            document.getElementById('prescriptionModal').style.display = 'flex';
        }

        function closePrescriptionModal() {
            document.getElementById('prescriptionModal').style.display = 'none';
            document.getElementById('prescriptionForm').reset();
        }

        // Handle prescription form submission
        document.getElementById('prescriptionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                appointment_id: document.getElementById('appointment_id').value,
                diagnosis: document.getElementById('diagnosis').value,
                prescription: document.getElementById('prescription').value,
                notes: document.getElementById('notes').value
            };

            fetch('prescription.php', {
        method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
                    // Create a new window for printing
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(data.prescription_html);
                    printWindow.document.close();
                    
                    // Wait for content to load then print
                    printWindow.onload = function() {
                        printWindow.print();
                        printWindow.close();
                    };
                    
                    closePrescriptionModal();
                    location.reload(); // Refresh to show updated appointment status
        } else {
                    alert(data.error || 'Error saving prescription');
        }
      })
      .catch(error => {
                console.error('Error:', error);
                alert('Error saving prescription');
      });
    });

        // Add prescription template button
        document.getElementById('prescription').addEventListener('focus', function() {
            const templateButton = document.createElement('button');
            templateButton.type = 'button';
            templateButton.className = 'btn btn-secondary';
            templateButton.innerHTML = '<i class="fas fa-file-medical"></i> Use Template';
            templateButton.style.marginTop = '10px';
            
            templateButton.onclick = function() {
                const template = `1. [Medication Name] [Dosage]
   - Take [X] tablet(s) [X] times daily
   - [Before/After] meals
   - Duration: [X] days

2. [Medication Name] [Dosage]
   - Take [X] tablet(s) [X] times daily
   - [Before/After] meals
   - Duration: [X] days

3. [Medication Name] [Dosage]
   - Take [X] tablet(s) [X] times daily
   - [Before/After] meals
   - Duration: [X] days`;
                
                document.getElementById('prescription').value = template;
            };
            
            if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('btn')) {
                this.parentNode.insertBefore(templateButton, this.nextSibling);
            }
        });

        // Appointment Functions
    function cancelAppointment(appointmentId) {
      if (confirm('Are you sure you want to cancel this appointment?')) {
                fetch('cancel_appointment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ appointment_id: appointmentId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error canceling appointment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error canceling appointment');
                });
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
    }

    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview.tagName === 'IMG') {
                    preview.src = e.target.result;
                } else {
                    // Replace placeholder with image
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    preview.parentNode.replaceChild(img, preview);
                    img.id = 'imagePreview';
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
  </script>
</body>
</html>