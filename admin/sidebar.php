<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHealth</title>
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
                <li><a href="pending_hospital.php"><i class="fas fa-clock"></i> Pending Hospitals</a></li>
                <li><a href="doctors.php"><i class="fas fa-user-md"></i> Doctors</a></li>
                <li><a href="patients.php"><i class="fas fa-users"></i> Patients</a></li>
                <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="../../js/script.js"></script> -->
</body>
</html>