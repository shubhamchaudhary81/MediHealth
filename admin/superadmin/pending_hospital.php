<?php
session_start();
require_once('../../config/configdatabase.php');

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_id'])) {
    header("Location: ../superadminlogin.php");
    exit();
}

// Handle approval/rejection
if (isset($_POST['action']) && isset($_POST['hospital_id'])) {
    $hospital_id = $_POST['hospital_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        // Update hospital status
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $update_query = "UPDATE hospital SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $status, $hospital_id);
        
        if ($stmt->execute()) {
            if ($action === 'approve') {
                // Fetch hospital admin email
                $email_query = "SELECT ha.email, h.name as hospital_name 
                              FROM hospitaladmin ha 
                              JOIN hospital h ON ha.hospitalid = h.id 
                              WHERE h.id = ?";
                $stmt = $conn->prepare($email_query);
                $stmt->bind_param("i", $hospital_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $admin_data = $result->fetch_assoc();
                
                // Send approval email
                $to = $admin_data['email'];
                $subject = "Hospital Registration Approved";
                $message = "Dear Hospital Administrator,\n\n";
                $message .= "Congratulations! Your hospital " . $admin_data['hospital_name'] . " has been successfully registered and approved on MediHealth.\n";
                $message .= "You can now login to your hospital admin dashboard and manage your hospital profile.\n\n";
                $message .= "Best regards,\nMediHealth Team";
                $headers = "From: medihealth@example.com";
                
                mail($to, $subject, $message, $headers);
            }
            $_SESSION['success'] = "Hospital successfully " . $action . "d!";
        } else {
            $_SESSION['error'] = "Error updating hospital status";
        }
    }
    header("Location: pending_hospital.php");
    exit();
}

// Fetch pending hospitals
$query = "SELECT h.*, ha.name as admin_name, ha.email as admin_email 
          FROM hospital h 
          JOIN hospitaladmin ha ON h.id = ha.hospitalid 
          WHERE h.status = 'pending' 
          ORDER BY h.id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediHealth</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            margin: 20px auto;
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
            border: none;
            cursor: pointer;
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
        .back-btn {
            background-color: #2196F3;
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
    <div class="container">
        <div class="header">
            <h1>Pending Hospital Requests</h1>
            <div>
                <a href="dashboard.php" class="btn back-btn">Back to Dashboard</a>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
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
                            <td><?php echo htmlspecialchars($row['admin_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['admin_email']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="hospital_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="approve" 
                                            class="btn">
                                        Approve
                                    </button>
                                    <button type="submit" name="action" value="reject" 
                                            class="btn btn-danger">
                                        Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No pending hospital requests</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>