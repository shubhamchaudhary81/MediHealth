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
    
    // Fetch doctors for this hospital
    $doctors_query = "SELECT d.*, dept.department_name 
                     FROM doctor d
                     JOIN department dept ON d.department_id = dept.department_id
                     WHERE d.hospitalid = ?
                     ORDER BY d.name ASC";
    
    $stmt = $conn->prepare($doctors_query);
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $doctors_result = $stmt->get_result();
    
    include('sidebar.php');
    ?>
    <style>
        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            padding: 20px;
        }

        .doctor-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .doctor-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.1);
        }

        .doctor-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .doctor-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
        }

        .doctor-info h3 {
            margin: 0;
            color: var(--text-color);
            font-size: 1.2rem;
            font-weight: 600;
        }

        .doctor-info .specialty {
            color: var(--primary-color);
            font-size: 0.9rem;
            margin: 4px 0;
        }

        .doctor-id {
            font-size: 0.8rem;
            color: #64748b;
        }

        .doctor-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin: 20px 0;
            background: #f8fafc;
            padding: 12px;
            border-radius: 12px;
        }

        .stat-item {
            text-align: center;
            padding: 8px;
        }

        .stat-item h4 {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-item p {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .doctor-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .action-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .view-profile {
            background: var(--primary-color);
            color: white;
        }

        .view-profile:hover {
            background: #4f6df5;
        }

        .schedule {
            background: #f1f5f9;
            color: var(--text-color);
        }

        .schedule:hover {
            background: #e2e8f0;
        }

        .doctors-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 20px;
            background: white;
            border-radius: 16px;
            margin: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .doctors-header h2 {
            font-size: 1.5rem;
            color: var(--text-color);
            font-weight: 600;
            margin: 0;
        }

        .add-doctor-btn {
            background: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-doctor-btn:hover {
            background: #4f6df5;
            transform: translateY(-2px);
        }

        .no-doctors {
            text-align: center;
            padding: 48px 24px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin: 20px;
        }

        .no-doctors i {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .no-doctors h3 {
            font-size: 1.3rem;
            color: var(--text-color);
            margin-bottom: 12px;
        }

        .no-doctors p {
            color: #64748b;
            margin-bottom: 24px;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .doctors-grid {
                grid-template-columns: 1fr;
                padding: 16px;
            }

            .doctors-header {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }

            .add-doctor-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    
        <!-- Main Content -->
        <div class="main-content">
            <!-- Include the header file -->
            <?php include('header.php'); ?>

            <!-- Doctors Content -->
            <div class="doctors-header">
                <h2>Doctors List</h2>
                <a href="add-doctor.php" class="add-doctor-btn">
                    <i class="fas fa-plus"></i>
                    Add New Doctor
                </a>
            </div>
            
            <?php if ($doctors_result->num_rows > 0): ?>
                <div class="doctors-grid">
                    <?php while ($doctor = $doctors_result->fetch_assoc()): ?>
                        <div class="doctor-card">
                            <div class="doctor-header">
                                <!-- <img src="https://via.placeholder.com/80" alt="<?php echo htmlspecialchars($doctor['name']); ?>" class="doctor-avatar"> -->
                                <div class="doctor-info">
                                    <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                                    <p class="specialty"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                                    <p class="doctor-id">ID: <?php echo htmlspecialchars($doctor['doctor_id']); ?></p>
                                </div>
                            </div>
                            <div class="doctor-stats">
                                <div class="stat-item">
                                    <h4>Department</h4>
                                    <p><?php echo htmlspecialchars($doctor['department_name']); ?></p>
                                </div>
                                <div class="stat-item">
                                    <h4>Experience</h4>
                                    <p><?php echo htmlspecialchars($doctor['experience']); ?> years</p>
                                </div>
                            </div>
                            <div class="doctor-actions">
                                <a href="view-doctor-profile.php?doctor_id=<?php echo urlencode($doctor['doctor_id']); ?>" class="action-btn view-profile">
                                    <i class="fas fa-user-md"></i>
                                    View Profile
                                </a>
                                <a href="edit-doctor-schedule-new.php?doctor_id=<?php echo urlencode($doctor['doctor_id']); ?>" class="action-btn schedule">
                                    <i class="fas fa-calendar-alt"></i>
                                    Schedule
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-doctors">
                    <i class="fas fa-user-md"></i>
                    <h3>No Doctors Found</h3>
                    <p>There are no doctors registered for your hospital yet.</p>
                    <a href="add-doctor.php" class="add-doctor-btn">
                        <i class="fas fa-plus"></i>
                        Add Your First Doctor
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html> 