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

// Get recent activities
$activity_query = "(
    SELECT 
        'hospital' as type,
        h.name as name,
        h.created_at as date,
        h.status as status,
        ha.name as admin_name,
        NULL as specialization
    FROM hospital h
    JOIN hospitaladmin ha ON h.id = ha.hospitalid
    WHERE h.status = 'pending'
    ORDER BY h.created_at DESC
    LIMIT 5
)
UNION ALL
(
    SELECT 
        'doctor' as type,
        d.name as name,
        d.created_at as date,
        'added' as status,
        h.name as admin_name,
        d.specialization
    FROM doctor d
    JOIN hospital h ON d.hospitalid = h.id
    ORDER BY d.created_at DESC
    LIMIT 5
)
ORDER BY date DESC
LIMIT 5";

$activity_result = $conn->query($activity_query);

// Function to get time ago
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d > 7) {
        return date('M d, Y', strtotime($datetime));
    }
    
    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    }
    if ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    if ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
    return 'Just now';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Dashboard - MediHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .logout-btn {
            color: #dc3545;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        .activity-content {
            flex: 1;
        }
        .activity-text {
            color: #333;
            margin-bottom: 5px;
        }
        .activity-time {
            color: #666;
            font-size: 0.9rem;
        }
        .activity-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-added {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
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
                        <div class="card-icon" style="background-color: #4a90e2;">
                            <i class="fas fa-hospital"></i>
                        </div>
                    </div>
                    <p class="card-value"><?php echo $hospital_count; ?></p>
                    <a href="hospitals.php" class="card-link">View all hospitals →</a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pending Hospitals</h3>
                        <div class="card-icon" style="background-color: #ffc107;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <p class="card-value"><?php echo $pending_count; ?></p>
                    <a href="pending_hospital.php" class="card-link">View pending hospitals →</a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Doctors</h3>
                        <div class="card-icon" style="background-color: #28a745;">
                            <i class="fas fa-user-md"></i>
                        </div>
                    </div>
                    <p class="card-value"><?php echo $doctor_count; ?></p>
                    <a href="doctors.php" class="card-link">View all doctors →</a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Patients</h3>
                        <div class="card-icon" style="background-color: #17a2b8;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <p class="card-value"><?php echo $patient_count; ?></p>
                    <a href="patients.php" class="card-link">View all patients →</a>
                </div>
            </div>
            
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <ul class="activity-list">
                    <?php if ($activity_result && $activity_result->num_rows > 0): ?>
                        <?php while ($activity = $activity_result->fetch_assoc()): ?>
                            <li class="activity-item">
                                <div class="activity-icon" style="background-color: <?php echo $activity['type'] === 'hospital' ? '#4a90e2' : '#28a745'; ?>">
                                    <i class="fas <?php echo $activity['type'] === 'hospital' ? 'fa-hospital' : 'fa-user-md'; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text">
                                        <?php if ($activity['type'] === 'hospital'): ?>
                                            New hospital registration: <?php echo htmlspecialchars($activity['name']); ?>
                                        <?php else: ?>
                                            New doctor added: Dr. <?php echo htmlspecialchars($activity['name']); ?> 
                                            (<?php echo htmlspecialchars($activity['specialization']); ?>)
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo time_elapsed_string($activity['date']); ?>
                                        <?php if ($activity['type'] === 'hospital'): ?>
                                            <span class="activity-status status-pending">Pending</span>
                                        <?php else: ?>
                                            <span class="activity-status status-added">Added</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="activity-item">
                            <div class="activity-icon" style="background-color: #6c757d;">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">No recent activities</div>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html> 