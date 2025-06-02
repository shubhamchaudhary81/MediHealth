<?php
session_start();

// Check if superadmin is logged in
if (!isset($_SESSION['superadmin_id'])) {
    header("Location: ../superadminlogin.php");
    exit();
}

include_once('../../config/configdatabase.php');

// Handle hospital removal if needed
if (isset($_POST['action']) && isset($_POST['hospital_id'])) {
    $hospital_id = $_POST['hospital_id'];
    $action = $_POST['action'];
    
    if ($action === 'remove') {
        $update_query = "UPDATE hospital SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $hospital_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Hospital successfully removed!";
        } else {
            $_SESSION['error'] = "Error removing hospital";
        }
        header("Location: hospitals.php");
        exit();
    }
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if (!empty($search)) {
    $search = "%$search%";
    $search_condition = "AND (h.name LIKE ? OR h.city LIKE ? OR h.district LIKE ? OR ha.name LIKE ?)";
}

// Fetch approved hospitals
$query = "SELECT h.*, ha.name as admin_name, ha.email as admin_email 
          FROM hospital h 
          JOIN hospitaladmin ha ON h.id = ha.hospitalid 
          WHERE h.status = 'approved' $search_condition
          ORDER BY h.id DESC";

$stmt = $conn->prepare($query);
if (!empty($search)) {
    $stmt->bind_param("ssss", $search, $search, $search, $search);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHealth</title>
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
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .search-input {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-btn {
            padding: 8px 15px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <div class="header">
                    <h1>Approved Hospitals</h1>
                </div>

                <div class="search-box">
                    <form method="GET" action="">
                        <input type="text" name="search" class="search-input" 
                               placeholder="Search hospitals..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button type="submit" class="search-btn">Search</button>
                    </form>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr>
                            <th>Hospital Name</th>
                            <th>Location</th>
                            <th>Contact</th>
                            <th>Admin Name</th>
                            <th>Admin Email</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['city'] . ', ' . 
                                                                 $row['district'] . ', ' . 
                                                                 $row['zone']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($row['admin_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['admin_email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="hospital_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="remove" 
                                                    class="btn btn-danger"
                                                    onclick="return confirm('Are you sure you want to remove this hospital?')">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No approved hospitals found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 