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
                  INNER JOIN hospitaladmin ha ON h.id = ha.hospitalid 
                  WHERE ha.adminid = ?";

if (!($stmt = $conn->prepare($hospital_query))) {
    die("Error preparing hospital query: " . $conn->error);
}

if (!$stmt->bind_param("i", $admin_id)) {
    die("Error binding parameters for hospital query: " . $stmt->error);
}

if (!$stmt->execute()) {
    die("Error executing hospital query: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $hospital = $result->fetch_assoc();
    $hospital_id = $hospital['id'];
} else {
    header("Location: hospitaladminlogin.php");
    exit();
}

$stmt->close();

// Handle search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Fetch patients with search and pagination
$patients_query = "SELECT p.*, 
                  (SELECT COUNT(*) FROM appointments a WHERE a.patient_id = p.patientID) as appointment_count
                  FROM patients p 
                  INNER JOIN appointments a ON p.patientID = a.patient_id 
                  WHERE a.hospital_id = ? ";

if (!empty($search)) {
    $patients_query .= "AND (p.first_name LIKE ? OR p.last_name LIKE ? OR p.email LIKE ? OR p.phone LIKE ?) ";
}

$patients_query .= "GROUP BY p.patientID ORDER BY p.patientID DESC LIMIT ? OFFSET ?";

if (!($stmt = $conn->prepare($patients_query))) {
    die("Error preparing patients query: " . $conn->error);
}

if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("issssii", $hospital_id, $search_param, $search_param, $search_param, $search_param, $records_per_page, $offset);
} else {
    $stmt->bind_param("iii", $hospital_id, $records_per_page, $offset);
}

if (!$stmt->execute()) {
    die("Error executing patients query: " . $stmt->error);
}

$patients_result = $stmt->get_result();
$stmt->close();

// Get total number of patients for pagination
$count_query = "SELECT COUNT(DISTINCT p.patientID) as total 
                FROM patients p 
                INNER JOIN appointments a ON p.patientID = a.patient_id 
                WHERE a.hospital_id = ? ";

if (!empty($search)) {
    $count_query .= "AND (p.first_name LIKE ? OR p.last_name LIKE ? OR p.email LIKE ? OR p.phone LIKE ?)";
}

if (!($stmt = $conn->prepare($count_query))) {
    die("Error preparing count query: " . $conn->error);
}

if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("issss", $hospital_id, $search_param, $search_param, $search_param, $search_param);
} else {
    $stmt->bind_param("i", $hospital_id);
}

if (!$stmt->execute()) {
    die("Error executing count query: " . $stmt->error);
}

$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_patients = $total_row['total'];
$total_pages = ceil($total_patients / $records_per_page);

$stmt->close();

include('sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - Hospital Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .patients-container {
            padding: 20px;
        }

        .patients-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-box input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 300px;
        }

        .search-box button {
            padding: 8px 12px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .patients-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .patients-table th,
        .patients-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .patients-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .patients-table tr:hover {
            background: #f8f9fa;
        }

        .patient-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-button {
            padding: 6px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
        }

        .view-button {
            background: #3b82f6;
        }

        .edit-button {
            background: #f59e0b;
        }

        .delete-button {
            background: #ef4444;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }

        .pagination a.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination a:hover:not(.active) {
            background: #f8f9fa;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <?php include('header.php'); ?>
        
        <div class="patients-container">
            <div class="patients-header">
                <h2>Patients</h2>
                <div class="search-box">
                    <form action="" method="GET">
                        <input type="text" name="search" placeholder="Search patients..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
            </div>

            <?php if ($patients_result->num_rows > 0): ?>
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Appointments</th>
                            <!-- <th>Status</th> -->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($patient = $patients_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patient['patientID']); ?></td>
                                <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                <td><?php echo htmlspecialchars($patient['number']); ?></td>
                                <td><?php echo htmlspecialchars($patient['appointment_count']); ?></td>
                                <!-- <td>
                                    <span class="patient-status status-<?php echo $patient['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                        <?php echo ucfirst($patient['status']); ?>
                                    </span>
                                </td> -->
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-button view-button" onclick="viewPatient(<?php echo $patient['patientID']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-button edit-button" onclick="editPatient(<?php echo $patient['patientID']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-button delete-button" onclick="deletePatient(<?php echo $patient['patientID']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">&laquo; Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                               class="<?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>No patients found<?php echo !empty($search) ? ' matching your search' : ''; ?>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function viewPatient(patientId) {
            window.location.href = 'view_patient.php?id=' + patientId;
        }

        function editPatient(patientId) {
            window.location.href = 'edit_patient.php?id=' + patientId;
        }

        function deletePatient(patientId) {
            if (confirm('Are you sure you want to delete this patient? This action cannot be undone.')) {
                fetch('delete_patient.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        patient_id: patientId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete patient: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the patient');
                });
            }
        }
    </script>
</body>
</html> 