<?php
session_start();
include_once('../include/header.php');
include_once('../config/configdatabase.php');

// Check if user is logged in
if (!isset($_SESSION['patientID'])) {
    header("Location: patientlogin.php");
    exit();
}

$patient_id = $_SESSION['patientID'];
$success_message = '';
$error_message = '';
$current_action = isset($_GET['action']) ? $_GET['action'] : 'view';

// Fetch hospitals for dropdown
$hospitals_query = "SELECT id, name FROM hospital ORDER BY name";
$hospitals_result = $conn->query($hospitals_query);

// Check if query was successful
if (!$hospitals_result) {
    $error_message = "Error fetching hospitals: " . $conn->error;
} else if ($hospitals_result->num_rows === 0) {
    $error_message = "No hospitals found in the database. Please contact the administrator.";
}

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["report_file"])) {
    $file = $_FILES["report_file"];
    $report_title = trim($_POST["report_title"]);
    $report_date = trim($_POST["report_date"]);
    $report_type = trim($_POST["report_type"]);
    $hospital_id = trim($_POST["hospital_id"]);
    $doctor_id = trim($_POST["doctor_id"]);
    
    // Validate inputs
    if (empty($report_title) || empty($report_date) || empty($report_type) || empty($hospital_id) || empty($doctor_id)) {
        $error_message = "All fields are required";
    } else {
        // Handle file upload
        $target_dir = "../uploads/reports/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check file type
        $allowed_types = array("pdf", "jpg", "jpeg", "png");
        if (!in_array($file_extension, $allowed_types)) {
            $error_message = "Sorry, only PDF, JPG, JPEG & PNG files are allowed.";
        } else {
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                // Insert report into database
                $stmt = $conn->prepare("INSERT INTO patient_reports (patient_id, hospital_id, doctor_id, report_title, report_date, report_type, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiissss", $patient_id, $hospital_id, $doctor_id, $report_title, $report_date, $report_type, $target_file);
                
                if ($stmt->execute()) {
                    $success_message = "Report uploaded successfully!";
                    $current_action = 'view'; // Redirect to view after successful upload
                } else {
                    $error_message = "Error uploading report to database.";
                }
                $stmt->close();
            } else {
                $error_message = "Sorry, there was an error uploading your file.";
            }
        }
    }
}

// Fetch patient's reports with hospital and doctor information
$reports_query = "SELECT pr.*, h.name as hospital_name, d.name as doctor_name, d.specialization 
                 FROM patient_reports pr 
                 JOIN hospital h ON pr.hospital_id = h.id 
                 JOIN doctor d ON pr.doctor_id = d.doctor_id 
                 WHERE pr.patient_id = ? 
                 ORDER BY pr.report_date DESC";
$stmt = $conn->prepare($reports_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$reports_result = $stmt->get_result();

// Group reports by hospital and doctor
$grouped_reports = array();
while ($report = $reports_result->fetch_assoc()) {
    $key = $report['hospital_id'] . '_' . $report['doctor_id'];
    if (!isset($grouped_reports[$key])) {
        $grouped_reports[$key] = array(
            'hospital_name' => $report['hospital_name'],
            'doctor_name' => $report['doctor_name'],
            'specialization' => $report['specialization'],
            'reports' => array()
        );
    }
    $grouped_reports[$key]['reports'][] = $report;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Reports - MediHealth</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --text-color: #2c3e50;
            --light-bg: #f5f6fa;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--light-bg);
            color: var(--text-color);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            /* margin: 0 auto;
            padding: 2rem; */
        }

        .page-header {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            margin-top: 2.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .upload-section {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .upload-section h2 {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .upload-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .form-group input, 
        .form-group select {
            padding: 0.8rem;
            border: 2px solid #e1e1e1;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: var(--transition);
            width: 100%;
        }

        .form-group input:focus, 
        .form-group select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn-upload {
            background: var(--primary-color);
            color: var(--white);
            padding: 1rem 2rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            width: 100%;
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-upload:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .reports-section {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }

        .reports-section h2 {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .report-card {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid #e1e1e1;
            transition: var(--transition);
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .report-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .report-type {
            background: #e1f0ff;
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .report-date {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .report-actions {
            display: flex;
            gap: 0.8rem;
        }

        .btn-action {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-view {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-download {
            background: var(--secondary-color);
            color: var(--white);
        }

        .btn-delete {
            background: var(--danger-color);
            color: var(--white);
        }

        .btn-action:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .action-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: var(--white);
            padding: 1rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }

        .action-tab {
            padding: 0.8rem 1.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-tab.active {
            background: var(--primary-color);
            color: var(--white);
        }

        .action-tab:not(.active) {
            background: var(--light-bg);
            color: var(--text-color);
        }

        .action-tab:hover:not(.active) {
            background: #e1e1e1;
        }

        .report-group {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .report-group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e1e1e1;
        }

        .hospital-doctor-info {
            flex: 1;
        }

        .hospital-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.3rem;
        }

        .doctor-info {
            color: #666;
            font-size: 0.9rem;
        }

        .doctor-specialization {
            color: var(--secondary-color);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .reports-grid {
                grid-template-columns: 1fr;
            }

            .upload-form {
                grid-template-columns: 1fr;
            }

            .report-actions {
                flex-wrap: wrap;
            }

            .btn-action {
                flex: 1;
                justify-content: center;
            }

            .action-tabs {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Medical Reports</h1>
            <p>View and manage your medical reports</p>
        </div>

        <div class="action-tabs">
            <a href="?action=upload" class="action-tab <?php echo $current_action === 'upload' ? 'active' : ''; ?>">
                <i class="fas fa-upload"></i>
                Upload Report
            </a>
            <a href="?action=view" class="action-tab <?php echo $current_action === 'view' ? 'active' : ''; ?>">
                <i class="fas fa-eye"></i>
                View Reports
            </a>
        </div>

        <?php if ($success_message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($current_action === 'upload'): ?>
            <div class="upload-section">
                <h2>Upload New Report</h2>
                <form class="upload-form" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="hospital_id">Hospital</label>
                        <select id="hospital_id" name="hospital_id" required>
                            <option value="">Select Hospital</option>
                            <?php 
                            if ($hospitals_result && $hospitals_result->num_rows > 0) {
                                while ($hospital = $hospitals_result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($hospital['id']) . '">' . 
                                         htmlspecialchars($hospital['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="doctor_id">Doctor</label>
                        <select id="doctor_id" name="doctor_id" required>
                            <option value="">Select Hospital First</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="report_title">Report Title</label>
                        <input type="text" id="report_title" name="report_title" required placeholder="Enter report title">
                    </div>

                    <div class="form-group">
                        <label for="report_date">Report Date</label>
                        <input type="date" id="report_date" name="report_date" required>
                    </div>

                    <div class="form-group">
                        <label for="report_type">Report Type</label>
                        <select id="report_type" name="report_type" required>
                            <option value="">Select Report Type</option>
                            <option value="Blood Test">Blood Test</option>
                            <option value="X-Ray">X-Ray</option>
                            <option value="MRI">MRI</option>
                            <option value="CT Scan">CT Scan</option>
                            <option value="Prescription">Prescription</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="report_file">Upload File</label>
                        <input type="file" id="report_file" name="report_file" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>

                    <button type="submit" class="btn-upload">
                        <i class="fas fa-upload"></i>
                        Upload Report
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($current_action === 'view'): ?>
            <div class="reports-section">
                <h2>Your Reports</h2>
                <?php if (empty($grouped_reports)): ?>
                    <div class="message">
                        <i class="fas fa-info-circle"></i>
                        No reports found. Please upload a report.
                    </div>
                <?php else: ?>
                    <?php foreach ($grouped_reports as $group): ?>
                        <div class="report-group">
                            <div class="report-group-header">
                                <div class="hospital-doctor-info">
                                    <div class="hospital-name">
                                        <i class="fas fa-hospital"></i>
                                        <?php echo htmlspecialchars($group['hospital_name']); ?>
                                    </div>
                                    <div class="doctor-info">
                                        <i class="fas fa-user-md"></i>
                                        Dr. <?php echo htmlspecialchars($group['doctor_name']); ?>
                                        <span class="doctor-specialization">
                                            (<?php echo htmlspecialchars($group['specialization']); ?>)
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="reports-grid">
                                <?php foreach ($group['reports'] as $report): ?>
                                    <div class="report-card">
                                        <div class="report-header">
                                            <span class="report-title"><?php echo htmlspecialchars($report['report_title']); ?></span>
                                            <span class="report-type"><?php echo htmlspecialchars($report['report_type']); ?></span>
                                        </div>
                                        <div class="report-date">
                                            <i class="far fa-calendar"></i>
                                            <?php echo date('F j, Y', strtotime($report['report_date'])); ?>
                                        </div>
                                        <div class="report-actions">
                                            <a href="<?php echo $report['file_path']; ?>" target="_blank" class="btn-action btn-view">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </a>
                                            <a href="<?php echo $report['file_path']; ?>" download class="btn-action btn-download">
                                                <i class="fas fa-download"></i>
                                                Download
                                            </a>
                                            <button class="btn-action btn-delete" onclick="deleteReport(<?php echo $report['report_id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function deleteReport(reportId) {
            if (confirm('Are you sure you want to delete this report?')) {
                window.location.href = 'delete_report.php?id=' + reportId;
            }
        }

        document.getElementById('hospital_id').addEventListener('change', function() {
            const hospitalId = this.value;
            const doctorSelect = document.getElementById('doctor_id');
            
            // Clear existing options
            doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
            
            if (hospitalId) {
                // Fetch doctors for selected hospital
                fetch(`get_doctors.php?hospital_id=${hospitalId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            data.doctors.forEach(doctor => {
                                const option = document.createElement('option');
                                option.value = doctor.doctor_id;
                                option.textContent = doctor.name + ' (' + doctor.specialization + ')';
                                doctorSelect.appendChild(option);
                            });
                        } else {
                            alert('Error loading doctors: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error loading doctors. Please try again.');
                    });
            }
        });
    </script>
    <?php
    include_once('../include/footer.php');
    ?>
</body>
</html> 