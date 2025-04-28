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

// Check if doctor_id is provided
if (!isset($_GET['doctor_id'])) {
    header("Location: doctors.php");
    exit();
}

$doctor_id = $_GET['doctor_id'];

// Fetch doctor information
$doctor_query = "SELECT d.*, dept.department_name, h.name as hospital_name 
                FROM doctor d
                JOIN department dept ON d.department_id = dept.department_id
                JOIN hospital h ON d.hospitalid = h.id
                WHERE d.doctor_id = ? AND d.hospitalid IN (
                    SELECT hospitalid FROM hospitaladmin WHERE adminid = ?
                )";

$stmt = $conn->prepare($doctor_query);
$stmt->bind_param("si", $doctor_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: doctors.php");
    exit();
}

$doctor = $result->fetch_assoc();

include('sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile - MediHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .profile-header {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
        }

        .profile-info h1 {
            margin: 0;
            color: var(--text-color);
            font-size: 1.8rem;
            font-weight: 600;
        }

        .profile-info .specialty {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin: 8px 0;
        }

        .profile-info .doctor-id {
            color: #64748b;
            font-size: 0.9rem;
        }

        .profile-details {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .detail-section {
            margin-bottom: 24px;
        }

        .detail-section:last-child {
            margin-bottom: 0;
        }

        .detail-section h2 {
            color: var(--text-color);
            font-size: 1.3rem;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f1f5f9;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .detail-item {
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .detail-item h3 {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-item p {
            font-size: 1rem;
            color: var(--text-color);
            margin: 0;
            font-weight: 500;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 24px;
            transition: all 0.2s ease;
        }

        .back-btn:hover {
            background: #4f6df5;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 16px;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <?php include('header.php'); ?>

        <div class="profile-container">
            <a href="doctors.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Doctors List
            </a>

            <div class="profile-header">
                <img src="https://via.placeholder.com/120" alt="<?php echo htmlspecialchars($doctor['name']); ?>" class="profile-avatar">
                <div class="profile-info">
                    <h1>Dr. <?php echo htmlspecialchars($doctor['name']); ?></h1>
                    <p class="specialty"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                    <p class="doctor-id">ID: <?php echo htmlspecialchars($doctor['doctor_id']); ?></p>
                </div>
            </div>

            <div class="profile-details">
                <div class="detail-section">
                    <h2>Professional Information</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <h3>Department</h3>
                            <p><?php echo htmlspecialchars($doctor['department_name']); ?></p>
                        </div>
                        <div class="detail-item">
                            <h3>Hospital</h3>
                            <p><?php echo htmlspecialchars($doctor['hospital_name']); ?></p>
                        </div>
                        <div class="detail-item">
                            <h3>Experience</h3>
                            <p><?php echo htmlspecialchars($doctor['experience']); ?> years</p>
                        </div>
                        <div class="detail-item">
                            <h3>Qualification</h3>
                            <p><?php echo htmlspecialchars($doctor['qualification']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Contact Information</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <h3>Email</h3>
                            <p><?php echo htmlspecialchars($doctor['email']); ?></p>
                        </div>
                        <div class="detail-item">
                            <h3>Phone</h3>
                            <p><?php echo htmlspecialchars($doctor['phone']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Schedule</h2>
                    <div class="detail-item">
                        <h3>Available Hours</h3>
                        <p style="white-space: pre-line;"><?php echo htmlspecialchars($doctor['schedule']); ?></p>
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