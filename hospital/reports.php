
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
        $hospital_location = $hospital['location'];
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
    include('sidebar.php');
    ?>
    <style>
        .reports-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .report-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .report-title {
            font-size: 18px;
            color: var(--text-color);
        }

        .report-actions {
            display: flex;
            gap: 10px;
        }

        .report-action {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            background: var(--background-color);
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .report-action:hover {
            background: var(--primary-color);
            color: white;
        }

        .report-content {
            margin-bottom: 20px;
        }

        .report-stat {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .stat-label {
            flex: 1;
            font-size: 14px;
            color: #64748b;
        }

        .stat-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
        }

        .progress-bar {
            height: 8px;
            background: var(--background-color);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary-color);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .report-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #64748b;
        }

        .report-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .report-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .status-completed {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .recent-reports {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .report-list {
            margin-top: 20px;
        }

        .report-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .report-item:hover {
            background: var(--background-color);
        }

        .report-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--background-color);
            color: var(--primary-color);
        }

        .report-details {
            flex: 1;
        }

        .report-details h4 {
            margin: 0 0 5px 0;
            color: var(--text-color);
        }

        .report-details p {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }

        .hospital-info {
            padding: 0 20px;
        }

        .hospital-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
        }

        .hospital-address {
            margin: 4px 0 0;
            font-size: 14px;
            color: #64748b;
        }
    </style>
</head>
<body>
   

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation Bar -->
            <!-- <header class="top-nav">
                <div class="nav-left">
                    <button class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="hospital-info">
                        <h2 class="hospital-title">Nobel Medical Hospital</h2>
                        <p class="hospital-address">Biratnagar, Morang</p>
                    </div>
                </div>
                <div class="nav-right">
                    <div class="admin-profile">
                        <img src="https://via.placeholder.com/40" alt="Admin">
                        <span>Dr. Smith</span>
                    </div>
                </div>
            </header> -->
            <?php include('header.php'); ?>

            <!-- Reports Content -->
            <div class="reports-container">
                <!-- Recent Reports -->
                <div class="recent-reports">
                    <h2>Recent Reports</h2>
                    <div class="report-list">
                        <div class="report-item">
                            <div class="report-icon">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <div class="report-details">
                                <h4>Monthly Patient Satisfaction Survey</h4>
                                <p>Overall satisfaction rate increased by 12%</p>
                            </div>
                            <div class="report-date">
                                <i class="far fa-calendar"></i>
                                <span>March 13, 2024</span>
                            </div>
                            <div class="report-actions">
                                <div class="report-action">
                                    <i class="fas fa-download"></i>
                                </div>
                            </div>
                        </div>

                        <div class="report-item">
                            <div class="report-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="report-details">
                                <h4>Resource Utilization Analysis</h4>
                                <p>Equipment efficiency report for Q1 2024</p>
                            </div>
                            <div class="report-date">
                                <i class="far fa-calendar"></i>
                                <span>March 12, 2024</span>
                            </div>
                            <div class="report-actions">
                                <div class="report-action">
                                    <i class="fas fa-download"></i>
                                </div>
                            </div>
                        </div>
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
</body>
</html> 