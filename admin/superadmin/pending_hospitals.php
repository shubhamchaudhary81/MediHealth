<?php
session_start();

// Check if superadmin is logged in
if (!isset($_SESSION['superadmin_id'])) {
    header("Location: ../superadminlogin.php");
    exit();
}

include_once('../../config/configdatabase.php');

// Handle approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve']) && isset($_POST['hospital_id'])) {
        $hospital_id = $_POST['hospital_id'];
        $update_query = "UPDATE hospital SET status = 'approved' WHERE hospital_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("s", $hospital_id);
        if ($stmt->execute()) {
            $success_message = "Hospital approved successfully!";
        } else {
            $error_message = "Error approving hospital: " . $conn->error;
        }
        $stmt->close();
    } elseif (isset($_POST['reject']) && isset($_POST['hospital_id'])) {
        $hospital_id = $_POST['hospital_id'];
        $update_query = "UPDATE hospital SET status = 'rejected' WHERE hospital_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("s", $hospital_id);
        if ($stmt->execute()) {
            $success_message = "Hospital rejected successfully!";
        } else {
            $error_message = "Error rejecting hospital: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch all pending hospitals
$pending_hospitals_query = "SELECT * FROM hospital WHERE status = 'pending' ORDER BY registration_date";
$pending_hospitals_result = $conn->query($pending_hospitals_query);

$conn->close();
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
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
            border: none;
            cursor: pointer;
        }
        .btn-approve {
            background-color: #4CAF50;
        }
        .btn-reject {
            background-color: #f44336;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
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
        .hospital-details {
            margin-top: 10px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <div class="header">
                    <h1>Pending Hospital Registrations</h1>
                </div>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <table>
                    <thead>
                        <tr>
                            <th>Hospital Name</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pending_hospitals_result && $pending_hospitals_result->num_rows > 0): ?>
                            <?php while ($hospital = $pending_hospitals_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                                    <td><?php echo htmlspecialchars($hospital['address']); ?></td>
                                    <td><?php echo htmlspecialchars($hospital['contact']); ?></td>
                                    <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                                    <td><?php echo htmlspecialchars($hospital['registration_date']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="hospital_id" value="<?php echo $hospital['hospital_id']; ?>">
                                            <button type="submit" name="approve" class="btn btn-approve">Approve</button>
                                            <button type="submit" name="reject" class="btn btn-reject">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No pending hospital registrations found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 