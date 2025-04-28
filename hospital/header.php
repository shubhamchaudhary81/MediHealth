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
?>

<!-- Top Navigation Bar -->
<header class="top-nav">
    <div class="nav-left">
        <button class="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="hospital-info">
            <div class="hospital-title-wrapper">
                <i class="fas fa-hospital-alt"></i>
                <h2 class="hospital-title"><?php echo htmlspecialchars($hospital_name); ?></h2>
            </div>
            <div class="hospital-location">
                <i class="fas fa-map-marker-alt"></i>
                <p class="hospital-address"><?php echo htmlspecialchars($hospital_location); ?></p>
            </div>
        </div>
    </div>
    <div class="nav-right">
        <div class="admin-profile">
            <img src="https://via.placeholder.com/40" alt="Admin">
            <span><?php echo htmlspecialchars($admin_name); ?></span>
        </div>
    </div>
</header>

<style>
    .top-nav {
        background: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .nav-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .menu-toggle {
        background: none;
        border: none;
        font-size: 20px;
        color: #333;
        cursor: pointer;
        padding: 5px;
        display: none;
    }

    .hospital-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .hospital-title-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .hospital-title-wrapper i {
        color: #2d89ef;
        font-size: 20px;
    }

    .hospital-title {
        font-size: 20px;
        color: #333;
        margin: 0;
        font-weight: 600;
    }

    .hospital-location {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hospital-location i {
        color: #666;
        font-size: 14px;
    }

    .hospital-address {
        font-size: 14px;
        color: #666;
        margin: 0;
    }

    .nav-right {
        display: flex;
        align-items: center;
    }

    .admin-profile {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 15px;
        background: #f8f9fa;
        border-radius: 6px;
        transition: background-color 0.3s;
    }

    .admin-profile:hover {
        background: #e9ecef;
    }

    .admin-profile img {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        object-fit: cover;
    }

    .admin-profile span {
        color: #333;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .menu-toggle {
            display: block;
        }

        .hospital-title {
            font-size: 18px;
        }

        .hospital-title-wrapper i {
            font-size: 18px;
        }

        .hospital-address {
            font-size: 12px;
        }

        .hospital-location i {
            font-size: 12px;
        }

        .admin-profile span {
            display: none;
        }
    }
</style>

<script>
    document.querySelector('.menu-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });
</script> 