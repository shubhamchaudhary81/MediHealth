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

// Get all appointments with date filter
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : date('Y-m-d');
$appointments_query = "SELECT a.*, p.first_name, p.last_name, p.number as phone, p.patientID
                      FROM appointments a 
                      LEFT JOIN patients p ON a.patient_id = p.patientID 
                      WHERE a.doctor_id = ? 
                      AND a.appointment_date = ?
                      ORDER BY a.appointment_time DESC";
$stmt = $conn->prepare($appointments_query);
$stmt->bind_param("ss", $doctor_id, $date_filter);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> MediHealth</title>
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

        .filter-section {
            margin-bottom: 1.5rem;
        }

        .filter-form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .date-filter {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .date-filter label {
            font-weight: 500;
            color: var(--dark-color);
        }

        .date-filter input[type="date"] {
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
        }

        .table-responsive {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .appointments-table th,
        .appointments-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .appointments-table th {
            background: var(--light-color);
            font-weight: 600;
            color: var(--dark-color);
        }

        .appointments-table tr:hover {
            background: var(--light-color);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .appointment-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-scheduled { background: #e3f2fd; color: #1976d2; }
        .status-completed { background: #e8f5e9; color: #2e7d32; }
        .status-cancelled { background: #ffebee; color: #c62828; }

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
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.25rem;
            color: var(--dark-color);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .detail-item {
            margin-bottom: 1.5rem;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-item h4 {
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .detail-item p {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .detail-item p:last-child {
            margin-bottom: 0;
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

            .appointments-container {
                grid-template-columns: 1fr;
            }

            .appointment-details-panel {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .appointment-card {
                grid-template-columns: 1fr;
            }

            .appointment-actions {
                flex-wrap: wrap;
            }

            .action-buttons {
                flex-direction: column;
            }
            
            .btn-sm {
                width: 100%;
            }
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            background: var(--light-color);
            border-radius: 8px;
            color: var(--gray-color);
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
                        <a href="appointments.php" class="nav-link active">
                            <i class="fas fa-calendar-check"></i>
                            <span>Appointments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="patients.php" class="nav-link">
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
                        <h1>Appointments</h1>
                        <p><?php echo htmlspecialchars($doctor['hospital_name']); ?></p>
                    </div>
                </div>
            </header>

            <!-- Appointments Section -->
            <section class="appointments-section">
                <div class="section-header">
                    <h2>All Appointments</h2>
                </div>

                <div class="filter-section">
                    <form method="GET" class="filter-form">
                        <div class="date-filter">
                            <label for="date_filter">Select Date:</label>
                            <input type="date" id="date_filter" name="date_filter" value="<?php echo $date_filter; ?>" class="form-control">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                <span>Search</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient Name</th>
                                <th>Phone</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($appointments->num_rows > 0): ?>
                                <?php while ($appointment = $appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['reason']); ?></td>
                                        <td>
                                            <span class="appointment-status status-<?php echo strtolower($appointment['status']); ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                        <td class="action-buttons">
                                            <button class="btn btn-primary btn-sm" onclick="viewAppointmentDetails(<?php echo $appointment['appointment_id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                                <span>View</span>
                                            </button>
                                            <?php if ($appointment['status'] === 'scheduled'): ?>
                                                <button class="btn btn-primary btn-sm" onclick="viewPatientDetails(<?php echo $appointment['patientID']; ?>)">
                                                    <i class="fas fa-user"></i>
                                                    <span>Patient</span>
                                                </button>
                                                <a href="prestemplate.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-prescription"></i>
                                                    <span>Prescription</span>
                                                </a>
                                                <button class="btn btn-danger btn-sm" onclick="cancelAppointment(<?php echo $appointment['appointment_id']; ?>)">
                                                    <i class="fas fa-times"></i>
                                                    <span>Cancel</span>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <p>No appointments found for the selected date</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Appointment Details Modal -->
            <div id="appointmentModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Appointment Details</h2>
                        <button class="close" onclick="closeAppointmentModal()">&times;</button>
                    </div>
                    <div id="appointmentDetailsContent" class="modal-body">
                        <!-- Appointment details will be loaded here -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Copy the same JavaScript functions from doctordash.php
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

        function viewAppointmentDetails(appointmentId) {
            fetch(`get_appointment_details.php?id=${appointmentId}`)
                .then(response => response.json())
                .then(data => {
                    const detailsContent = document.getElementById('appointmentDetailsContent');
                    detailsContent.innerHTML = `
                        <div class="detail-item">
                            <h4>Patient Information</h4>
                            <p><strong>Name:</strong> ${data.first_name} ${data.last_name}</p>
                            <p><strong>Phone:</strong> ${data.phone}</p>
                            <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                        </div>
                        <div class="detail-item">
                            <h4>Appointment Information</h4>
                            <p><strong>Date:</strong> ${new Date(data.appointment_date).toLocaleDateString()}</p>
                            <p><strong>Time:</strong> ${new Date(data.appointment_time).toLocaleTimeString()}</p>
                            <p><strong>Status:</strong> ${data.status}</p>
                            <p><strong>Reason:</strong> ${data.reason}</p>
                        </div>
                        ${data.notes ? `
                        <div class="detail-item">
                            <h4>Notes</h4>
                            <p>${data.notes}</p>
                        </div>
                        ` : ''}
                    `;
                    
                    document.getElementById('appointmentModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading appointment details');
                });
        }

        function closeAppointmentModal() {
            document.getElementById('appointmentModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html> 