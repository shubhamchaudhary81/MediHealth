<?php
session_start();

// Check if superadmin is logged in
if (!isset($_SESSION['superadmin_id'])) {
    header("Location: ../superadminlogin.php");
    exit();
}

include_once('../../config/configdatabase.php');

// Get counts for dashboard
$hospital_count = 0;
$pending_count = 0;
$doctor_count = 0;
$patient_count = 0;
$appointment_count = 0;

// Hospital count
$hospital_count_query = "SELECT COUNT(*) as count FROM hospital";
$hospital_result = $conn->query($hospital_count_query);
if ($hospital_result) {
    $hospital_count = $hospital_result->fetch_assoc()['count'];
} else {
    // Log error or handle it appropriately
    error_log("Error in hospital count query: " . $conn->error);
}

// Pending hospital count
$pending_hospital_query = "SELECT COUNT(*) as count FROM hospital WHERE status = 'pending'";
$pending_result = $conn->query($pending_hospital_query);
if ($pending_result) {
    $pending_count = $pending_result->fetch_assoc()['count'];
} else {
    error_log("Error in pending hospital count query: " . $conn->error);
}

// Doctor count
$doctor_count_query = "SELECT COUNT(*) as count FROM doctor";
$doctor_result = $conn->query($doctor_count_query);
if ($doctor_result) {
    $doctor_count = $doctor_result->fetch_assoc()['count'];
} else {
    error_log("Error in doctor count query: " . $conn->error);
}

// Patient count
$patient_count_query = "SELECT COUNT(*) as count FROM patients";
$patient_result = $conn->query($patient_count_query);
if ($patient_result) {
    $patient_count = $patient_result->fetch_assoc()['count'];
} else {
    error_log("Error in patient count query: " . $conn->error);
}

// Appointment count
// $appointment_count_query = "SELECT COUNT(*) as count FROM appointments";
// $appointment_result = $conn->query($appointment_count_query);
// if ($appointment_result) {
//     $appointment_count = $appointment_result->fetch_assoc()['count'];
// } else {
//     error_log("Error in appointment count query: " . $conn->error);
// }

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHealth</title>
    <!-- <link rel="stylesheet" href="../../css/style.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: rgb(74, 144, 226);
            color: white;
            padding: 20px 0;
        }
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        .sidebar-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info span {
            margin-right: 15px;
            color: #666;
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: rgb(74, 144, 226);
            margin: 0;
        }
        .card-link {
            display: block;
            margin-top: 15px;
            color: rgb(74, 144, 226);
            text-decoration: none;
            font-size: 0.9rem;
        }
        .card-link:hover {
            text-decoration: underline;
        }
        .recent-activity {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .recent-activity h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: white;
        }
        .activity-text {
            color: #666;
        }
        .activity-time {
            color: #999;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-hospital"></i> MediHealth</h2>
                <p>Superadmin Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="hospitals.php"><i class="fas fa-hospital"></i> Hospitals</a></li>
                <li><a href="pending_hospitals.php"><i class="fas fa-clock"></i> Pending Hospitals</a></li>
                <li><a href="doctors.php"><i class="fas fa-user-md"></i> Doctors</a></li>
                <li><a href="patients.php"><i class="fas fa-users"></i> Patients</a></li>
                <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['superadmin_name']); ?></span>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Hospitals</h3>
                        <div class="card-icon" style="background-color: #4CAF50;">
                            <i class="fas fa-hospital"></i>
                        </div>
                    </div>
                    <p class="card-value"><?php echo $hospital_count; ?></p>
                    <a href="hospitals.php" class="card-link">View all hospitals <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pending Hospitals</h3>
                        <div class="card-icon" style="background-color: #ffc107;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <p class="card-value"><?php echo $pending_count; ?></p>
                    <a href="pending_hospitals.php" class="card-link">Review pending hospitals <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Doctors</h3>
                        <div class="card-icon" style="background-color: #17a2b8;">
                            <i class="fas fa-user-md"></i>
                        </div>
                    </div>
                    <p class="card-value"><?php echo $doctor_count; ?></p>
                    <a href="doctors.php" class="card-link">View all doctors <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Patients</h3>
                        <div class="card-icon" style="background-color: #6f42c1;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <p class="card-value"><?php echo $patient_count; ?></p>
                    <a href="patients.php" class="card-link">View all patients <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Appointments</h3>
                        <div class="card-icon" style="background-color: #fd7e14;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <p class="card-value"><?php echo $appointment_count; ?></p>
                    <a href="appointments.php" class="card-link">View all appointments <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon" style="background-color: #4CAF50;">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <span class="activity-text">New hospital registration: City Hospital</span>
                        <span class="activity-time">2 hours ago</span>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon" style="background-color: #17a2b8;">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <span class="activity-text">New doctor added: Dr. John Smith</span>
                        <span class="activity-time">5 hours ago</span>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon" style="background-color: #6f42c1;">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="activity-text">New patient registration: Jane Doe</span>
                        <span class="activity-time">1 day ago</span>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon" style="background-color: #fd7e14;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <span class="activity-text">New appointment booked: Cardiology</span>
                        <span class="activity-time">2 days ago</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="../../js/script.js"></script> -->
    
</body>
</html> 