<?php
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in and is a hospital admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hospital_admin') {
        header("Location: hospitaladminlogin.php");
        exit();
    }
    
    // Include database connection
    require_once('../config/configdatabase.php');
    
    // Get hospital admin ID from session
    $admin_id = $_SESSION['user_id'];
    
    // Fetch hospital information based on admin ID
    $hospital_query = "SELECT h.* FROM hospital h 
                      JOIN hospitaladmin ha ON h.id = ha.hospitalid 
                      WHERE ha.adminid = ?";
    
    $stmt = $conn->prepare($hospital_query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $hospital = $result->fetch_assoc();
        $hospital_name = $hospital['name'];
        $hospital_location = $hospital['city'] . ', ' . $hospital['district'] . ', ' . $hospital['zone'];
        $hospital_id = $hospital['id']; // Store hospital ID for queries
    } else {
        // If no hospital found, redirect to login
        header("Location: hospitaladminlogin.php");
        exit();
    }
    
    // Fetch admin information
    $admin_query = "SELECT name FROM hospitaladmin WHERE adminid = ?";
    $stmt = $conn->prepare($admin_query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $admin_result = $stmt->get_result();
    
    if ($admin_result->num_rows > 0) {
        $admin = $admin_result->fetch_assoc();
        $admin_name = $admin['name'];
    } else {
        $admin_name = "Admin";
    }
    
    // Initialize variables with default values
    $total_patients = 0;
    $total_appointments = 0;
    $total_doctors = 0;
    $total_reviews = 0;
    $activity_result = null;
    
    // Create reviews table if it doesn't exist
    $create_reviews_table = "CREATE TABLE IF NOT EXISTS reviews (
        review_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        doctor_id VARCHAR(20) NOT NULL,
        patient_id INT(11) NOT NULL,
        rating INT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
        review_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE,
        FOREIGN KEY (patient_id) REFERENCES patients(patientID) ON DELETE CASCADE
    )";
    $conn->query($create_reviews_table);
    
    // 1. Total Patients - Check if appointments table exists first
    $check_table_query = "SHOW TABLES LIKE 'appointments'";
    $table_result = $conn->query($check_table_query);
    
    if ($table_result->num_rows > 0) {
        $patients_query = "SELECT COUNT(DISTINCT a.patient_id) as total_patients 
                          FROM appointments a 
                          WHERE a.hospital_id = ?";
        $stmt = $conn->prepare($patients_query);
        if ($stmt) {
            $stmt->bind_param("i", $hospital_id);
            $stmt->execute();
            $patients_result = $stmt->get_result();
            $patients_data = $patients_result->fetch_assoc();
            $total_patients = $patients_data['total_patients'];
        }
    }
    
    // 2. Total Appointments
    if ($table_result->num_rows > 0) {
        $appointments_query = "SELECT COUNT(*) as total_appointments 
                              FROM appointments 
                              WHERE hospital_id = ?";
        $stmt = $conn->prepare($appointments_query);
        if ($stmt) {
            $stmt->bind_param("i", $hospital_id);
            $stmt->execute();
            $appointments_result = $stmt->get_result();
            $appointments_data = $appointments_result->fetch_assoc();
            $total_appointments = $appointments_data['total_appointments'];
        }
    }
    
    // 3. Total Doctors - Check if doctor table exists
    $check_doctor_table = "SHOW TABLES LIKE 'doctor'";
    $doctor_table_result = $conn->query($check_doctor_table);
    
    if ($doctor_table_result->num_rows > 0) {
        $doctors_query = "SELECT COUNT(*) as total_doctors 
                         FROM doctor 
                         WHERE hospitalid = ?";
        $stmt = $conn->prepare($doctors_query);
        if ($stmt) {
            $stmt->bind_param("i", $hospital_id);
            $stmt->execute();
            $doctors_result = $stmt->get_result();
            $doctors_data = $doctors_result->fetch_assoc();
            $total_doctors = $doctors_data['total_doctors'];
        }
    }
    
    // 4. Total Reviews - Check if reviews table exists
    $check_reviews_table = "SHOW TABLES LIKE 'reviews'";
    $reviews_table_result = $conn->query($check_reviews_table);
    
    if ($reviews_table_result->num_rows > 0 && $doctor_table_result->num_rows > 0) {
        $reviews_query = "SELECT COUNT(*) as total_reviews 
                         FROM reviews r
                         JOIN doctor d ON r.doctor_id = d.doctor_id
                         WHERE d.hospitalid = ?";
        $stmt = $conn->prepare($reviews_query);
        if ($stmt) {
            $stmt->bind_param("i", $hospital_id);
            $stmt->execute();
            $reviews_result = $stmt->get_result();
            $reviews_data = $reviews_result->fetch_assoc();
            $total_reviews = $reviews_data['total_reviews'];
        }
    }
    
    // 5. Recent Activity - Check if all required tables exist
    $check_patients_table = "SHOW TABLES LIKE 'patients'";
    $patients_table_result = $conn->query($check_patients_table);
    
    if ($table_result->num_rows > 0 && $doctor_table_result->num_rows > 0 && $patients_table_result->num_rows > 0) {
        // Modified query to include doctor activities
        $activity_query = "SELECT 
                            'appointment' as activity_type,
                            a.appointment_id as id,
                            a.appointment_date,
                            a.appointment_time,
                            CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                            d.name as doctor_name,
                            a.status,
                            NULL as doctor_id,
                            NULL as specialization
                          FROM appointments a
                          JOIN patients p ON a.patient_id = p.patientID
                          JOIN doctor d ON a.doctor_id = d.doctor_id
                          WHERE a.hospital_id = ?
                          
                          UNION ALL
                          
                          SELECT 
                            'doctor' as activity_type,
                            d.doctor_id as id,
                            d.created_at as appointment_date,
                            NULL as appointment_time,
                            NULL as patient_name,
                            d.name as doctor_name,
                            'added' as status,
                            d.doctor_id,
                            d.specialization
                          FROM doctor d
                          WHERE d.hospitalid = ?
                          
                          ORDER BY appointment_date DESC, appointment_time DESC
                          LIMIT 10";
        
        $stmt = $conn->prepare($activity_query);
        if ($stmt) {
            $stmt->bind_param("ii", $hospital_id, $hospital_id);
            $stmt->execute();
            $activity_result = $stmt->get_result();
        }
    }
    
    include('sidebar.php');
?>        
<body>
        <!-- Main Content -->
        <div class="main-content">
            <!-- Include the header file -->
            <?php include('header.php'); ?>

            <!-- Dashboard Content -->
            <div class="dashboard">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon patients">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Total Patients</h3>
                            <p><?php echo $total_patients; ?></p>
                            <a href="patients.php" class="view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon appointments">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Appointments</h3>
                            <p><?php echo $total_appointments; ?></p>
                            <a href="appointments.php" class="view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon doctors">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Doctors</h3>
                            <p><?php echo $total_doctors; ?></p>
                            <a href="doctors.php" class="view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon reviews">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Total Reviews</h3>
                            <p><?php echo $total_reviews; ?></p>
                            <a href="reviews.php" class="view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="recent-activity">
                    <h2>Recent Activity</h2>
                    <div class="activity-list">
                        <?php if ($activity_result && $activity_result->num_rows > 0): ?>
                            <?php while ($activity = $activity_result->fetch_assoc()): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?php echo $activity['activity_type'] === 'doctor' ? 'doctor-icon' : 'appointment-icon'; ?>">
                                        <i class="fas <?php echo $activity['activity_type'] === 'doctor' ? 'fa-user-md' : 'fa-calendar-check'; ?>"></i>
                                    </div>
                                    <div class="activity-details">
                                        <?php if ($activity['activity_type'] === 'appointment'): ?>
                                            <h4>Appointment with <?php echo htmlspecialchars($activity['patient_name']); ?></h4>
                                            <p>Dr. <?php echo htmlspecialchars($activity['doctor_name']); ?> - <?php echo htmlspecialchars($activity['status']); ?></p>
                                            <span class="time"><?php echo date('F j, Y', strtotime($activity['appointment_date'])); ?> at <?php echo date('g:i A', strtotime($activity['appointment_time'])); ?></span>
                                        <?php else: ?>
                                            <h4>New Doctor Added</h4>
                                            <p>Dr. <?php echo htmlspecialchars($activity['doctor_name']); ?> - <?php echo htmlspecialchars($activity['specialization']); ?></p>
                                            <span class="time"><?php echo date('F j, Y', strtotime($activity['appointment_date'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="activity-details">
                                    <h4>No Recent Activity</h4>
                                    <p>There are no recent activities to display.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>

    <style>
        .main-content {
            padding: 20px;
            background-color: #f5f7fa;
        }

        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .patients {
            background: #e3f2fd;
            color: #1976d2;
        }

        .appointments {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .doctors {
            background: #fff3e0;
            color: #f57c00;
        }

        .reviews {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .stat-details h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
            margin-bottom: 10px;
        }

        .stat-details p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 15px;
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #f5f5f5;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .view-btn:hover {
            background: #e0e0e0;
            color: #000;
        }

        .view-btn i {
            margin-right: 8px;
        }

        .recent-activity {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .recent-activity h2 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 20px;
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .activity-icon {
            width: 35px;
            height: 35px;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .activity-details h4 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 16px;
        }

        .activity-details p {
            margin: 0 0 5px 0;
            color: #666;
            font-size: 14px;
        }

        .activity-details .time {
            color: #888;
            font-size: 12px;
        }

        .activity-icon.doctor-icon {
            background: #fff3e0;
            color: #f57c00;
        }

        .activity-icon.appointment-icon {
            background: #e3f2fd;
            color: #1976d2;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 15px;
            }
        }
    </style>
</body>
</html> 