<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: doctorlogin.php");
    exit();
}

require_once('../config/configdatabase.php');

// Get doctor information
$doctor_id = $_SESSION['user_id'];
$query = "SELECT d.*, h.name as hospital_name 
          FROM doctor d 
          LEFT JOIN hospital h ON d.hospitalid = h.id 
          WHERE d.doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

// Get all patients who have appointments with this doctor
$patients_query = "SELECT DISTINCT p.*, 
                  (SELECT COUNT(*) FROM appointments a WHERE a.patient_id = p.patientID AND a.doctor_id = ?) as appointment_count,
                  (SELECT MAX(appointment_date) FROM appointments a WHERE a.patient_id = p.patientID AND a.doctor_id = ?) as last_visit
                  FROM patients p 
                  INNER JOIN appointments a ON p.patientID = a.patient_id 
                  WHERE a.doctor_id = ?
                  ORDER BY last_visit DESC";
$stmt = $conn->prepare($patients_query);
$stmt->bind_param("sss", $doctor_id, $doctor_id, $doctor_id);
$stmt->execute();
$patients = $stmt->get_result();
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
        /* Copy the same CSS from doctordash.php */
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

        /* Patients Section */
        .patients-section {
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

        .search-box {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
        }

        .patient-list {
            display: grid;
            gap: 1rem;
        }

        .patient-card {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 8px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1.5rem;
            align-items: center;
        }

        .patient-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .patient-info h4 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .patient-info p {
            color: var(--gray-color);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .patient-stats {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .stat-item {
            background: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            color: var(--primary-color);
        }

        .patient-actions {
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

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
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
            .patient-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .patient-avatar {
                margin: 0 auto;
            }

            .patient-stats {
                justify-content: center;
            }

            .patient-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="doctordash.php" class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>MediHealth</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item">
                        <a href="doctordash.php" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="appointments.php" class="nav-link">
                            <i class="fas fa-calendar-check"></i>
                            <span>Appointments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="patients.php" class="nav-link active">
                            <i class="fas fa-users"></i>
                            <span>Patients</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="prescriptions.php" class="nav-link">
                            <i class="fas fa-prescription"></i>
                            <span>Prescriptions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="medical_records.php" class="nav-link">
                            <i class="fas fa-file-medical"></i>
                            <span>Medical Records</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="settings.php" class="nav-link">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="welcome-section">
                    <div class="profile-image">
                        <?php echo strtoupper(substr($doctor['name'], 0, 1)); ?>
                    </div>
                    <div class="welcome-text">
                        <h1>Patients</h1>
                        <p><?php echo htmlspecialchars($doctor['hospital_name']); ?></p>
                    </div>
                </div>
            </header>

            <!-- Patients Section -->
            <section class="patients-section">
                <div class="section-header">
                    <h2>Patient List</h2>
                </div>

                <div class="search-box">
                    <input type="text" class="search-input" placeholder="Search patients..." id="searchInput">
                </div>

                <div class="patient-list">
                    <?php if ($patients->num_rows > 0): ?>
                        <?php while ($patient = $patients->fetch_assoc()): ?>
                            <div class="patient-card">
                                <div class="patient-avatar">
                                    <?php echo strtoupper(substr($patient['first_name'], 0, 1)); ?>
                                </div>
                                <div class="patient-info">
                                    <h4><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h4>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($patient['number']); ?></p>
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($patient['email']); ?></p>
                                    <div class="patient-stats">
                                        <span class="stat-item">
                                            <i class="fas fa-calendar-check"></i> <?php echo $patient['appointment_count']; ?> Visits
                                        </span>
                                        <span class="stat-item">
                                            <i class="fas fa-clock"></i> Last Visit: <?php echo date('d M Y', strtotime($patient['last_visit'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="patient-actions">
                                    <button class="btn btn-primary" onclick="viewPatientDetails(<?php echo $patient['patientID']; ?>)">
                                        <i class="fas fa-user"></i>
                                        <span>View Details</span>
                                    </button>
                                    <button class="btn btn-secondary" onclick="viewMedicalHistory(<?php echo $patient['patientID']; ?>)">
                                        <i class="fas fa-file-medical"></i>
                                        <span>Medical History</span>
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No patients found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const patientCards = document.querySelectorAll('.patient-card');
            
            patientCards.forEach(card => {
                const patientName = card.querySelector('h4').textContent.toLowerCase();
                const patientPhone = card.querySelector('.patient-info p:first-child').textContent.toLowerCase();
                const patientEmail = card.querySelector('.patient-info p:nth-child(2)').textContent.toLowerCase();
                
                if (patientName.includes(searchTerm) || patientPhone.includes(searchTerm) || patientEmail.includes(searchTerm)) {
                    card.style.display = 'grid';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        function viewPatientDetails(patientId) {
            fetch(`get_patient_details.php?id=${patientId}`)
                .then(response => response.json())
                .then(data => {
                    // Handle patient details display
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading patient details');
                });
        }

        function viewMedicalHistory(patientId) {
            // Implement medical history view
            window.location.href = `medical_records.php?patient_id=${patientId}`;
        }
    </script>
</body>
</html> 