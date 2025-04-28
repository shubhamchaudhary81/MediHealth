<?php
session_start();

// Check if superadmin is logged in
if (!isset($_SESSION['superadmin_id'])) {
    header("Location: ../superadminlogin.php");
    exit();
}

include_once('../../config/configdatabase.php');

// Fetch all approved hospitals
$hospitals_query = "SELECT * FROM hospital WHERE status = 'approved' ORDER BY hospital_name";
$hospitals_result = $conn->query($hospitals_query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospitals - MediHealth</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Approved Hospitals</h1>
            <div>
                <a href="dashboard.php" class="btn back-btn">Back to Dashboard</a>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
        
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
                <?php if ($hospitals_result && $hospitals_result->num_rows > 0): ?>
                    <?php while ($hospital = $hospitals_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                            <td><?php echo htmlspecialchars($hospital['address']); ?></td>
                            <td><?php echo htmlspecialchars($hospital['contact']); ?></td>
                            <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                            <td><?php echo htmlspecialchars($hospital['registration_date']); ?></td>
                            <td>
                                <a href="view_hospital.php?id=<?php echo $hospital['hospital_id']; ?>" class="btn">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No approved hospitals found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 