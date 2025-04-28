
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
        .reviews-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .reviews-list {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .review-item {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reviewer-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .reviewer-details h4 {
            margin: 0;
            color: var(--text-color);
            font-size: 16px;
        }

        .review-date {
            color: #64748b;
            font-size: 14px;
        }

        .review-content {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .review-actions {
            display: flex;
            gap: 15px;
        }

        .action-button {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .view-btn {
            background: var(--primary-color);
            color: white;
        }

        .view-btn:hover {
            opacity: 0.9;
        }

        /* Statistics Panel */
        .statistics-panel {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .stat-card {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            background: var(--background-color);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .stat-title {
            font-size: 14px;
            color: #64748b;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-color);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        .trend-up {
            color: #16a34a;
        }

        .trend-down {
            color: #dc2626;
        }

        .rating-breakdown {
            margin-top: 30px;
        }

        .rating-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .rating-label {
            min-width: 60px;
            font-size: 14px;
            color: #64748b;
        }

        .rating-bar {
            flex: 1;
            height: 8px;
            background: var(--background-color);
            border-radius: 4px;
            overflow: hidden;
        }

        .rating-fill {
            height: 100%;
            background: var(--primary-color);
            border-radius: 4px;
        }

        .rating-percent {
            min-width: 40px;
            font-size: 14px;
            color: #64748b;
            text-align: right;
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
            <?php
             include ('header.php');
            ?>

            <!-- Reviews Content -->
            <div class="reviews-container">
                <!-- Reviews List -->
                <div class="reviews-list">
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <img src="https://via.placeholder.com/48" alt="Patient" class="reviewer-avatar">
                                <div class="reviewer-details">
                                    <h4>Anita Maharjan</h4>
                                    <span class="review-date">March 15, 2024</span>
                                </div>
                            </div>
                        </div>
                        <p class="review-content">
                            Excellent care and attention from Dr. Arun Poudel. The staff was very professional and friendly. 
                            The facility is clean and well-maintained. Would highly recommend!
                        </p>
                        <div class="review-actions">
                            <button class="action-button view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </div>
                    </div>

                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <img src="https://via.placeholder.com/48" alt="Patient" class="reviewer-avatar">
                                <div class="reviewer-details">
                                    <h4>Binod Tamang</h4>
                                    <span class="review-date">March 14, 2024</span>
                                </div>
                            </div>
                        </div>
                        <p class="review-content">
                            Good experience overall. Wait time was a bit longer than expected, but the medical care was excellent.
                            Dr. Priya Adhikari was very thorough in her examination.
                        </p>
                        <div class="review-actions">
                            <button class="action-button view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Statistics Panel -->
                <div class="statistics-panel">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Total Reviews</span>
                        </div>
                        <div class="stat-value">1,284</div>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i>
                            <span>+48 this month</span>
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