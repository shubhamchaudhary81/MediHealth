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

// Get all prescriptions
$prescriptions_query = "SELECT p.*, 
                       pt.first_name, pt.last_name, pt.number as phone,
                       a.appointment_date, a.appointment_time
                       FROM prescriptions p 
                       JOIN patients pt ON p.patient_id = pt.patientID
                       JOIN appointments a ON p.appointment_id = a.appointment_id
                       WHERE p.doctor_id = ?
                       ORDER BY p.created_at DESC";
$stmt = $conn->prepare($prescriptions_query);
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$prescriptions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - MediHealth</title>
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

        /* Prescriptions Section */
        .prescriptions-section {
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

        .prescription-list {
            display: grid;
            gap: 1rem;
        }

        .prescription-card {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 8px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1.5rem;
        }

        .prescription-info h4 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .prescription-info p {
            color: var(--gray-color);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .prescription-details {
            margin-top: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 6px;
        }

        .prescription-details h5 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .prescription-details p {
            white-space: pre-line;
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .prescription-actions {
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
            .prescription-card {
                grid-template-columns: 1fr;
            }

            .prescription-actions {
                justify-content: flex-start;
            }
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
                        <?php echo strtoupper(substr($doctor['name'], 0, 1)); ?>
                    </div>
                    <div class="welcome-text">
                        <h1>Prescriptions</h1>
                        <p><?php echo htmlspecialchars($doctor['hospital_name']); ?></p>
                    </div>
                </div>
            </header>

            <!-- Prescriptions Section -->
            <section class="prescriptions-section">
                <div class="section-header">
                    <h2>All Prescriptions</h2>
                </div>

                <div class="search-box">
                    <input type="text" class="search-input" placeholder="Search prescriptions..." id="searchInput">
                </div>

                <div class="prescription-list">
                    <?php if ($prescriptions->num_rows > 0): ?>
                        <?php while ($prescription = $prescriptions->fetch_assoc()): ?>
                            <div class="prescription-card">
                                <div class="prescription-info">
                                    <h4><?php echo htmlspecialchars($prescription['first_name'] . ' ' . $prescription['last_name']); ?></h4>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($prescription['phone']); ?></p>
                                    <p><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($prescription['appointment_date'])); ?></p>
                                    <p><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($prescription['appointment_time'])); ?></p>
                                    
                                    <div class="prescription-details">
                                        <h5>Diagnosis</h5>
                                        <p><?php echo nl2br(htmlspecialchars($prescription['diagnosis'])); ?></p>
                                        
                                        <h5>Medications</h5>
                                        <p><?php echo nl2br(htmlspecialchars($prescription['medications'])); ?></p>
                                    </div>
                                </div>
                                <div class="prescription-actions">
                                    <button class="btn btn-primary" onclick="printPrescription(<?php echo $prescription['prescription_id']; ?>)">
                                        <i class="fas fa-print"></i>
                                        <span>Print</span>
                                    </button>
                                    <button class="btn btn-secondary" onclick="viewPatientDetails(<?php echo $prescription['patient_id']; ?>)">
                                        <i class="fas fa-user"></i>
                                        <span>View Patient</span>
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No prescriptions found</p>
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
            const prescriptionCards = document.querySelectorAll('.prescription-card');
            
            prescriptionCards.forEach(card => {
                const patientName = card.querySelector('h4').textContent.toLowerCase();
                const diagnosis = card.querySelector('.prescription-details p:first-of-type').textContent.toLowerCase();
                const medications = card.querySelector('.prescription-details p:last-of-type').textContent.toLowerCase();
                
                if (patientName.includes(searchTerm) || diagnosis.includes(searchTerm) || medications.includes(searchTerm)) {
                    card.style.display = 'grid';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        function printPrescription(prescriptionId) {
            window.open(`prestemplate.php?id=${prescriptionId}&print=true`, '_blank');
        }

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
    </script>
</body>
</html> 