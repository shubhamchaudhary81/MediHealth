<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: doctorlogin.php");
    exit();
}

require_once('../config/configdatabase.php');

// Get doctor information
$doctor_id = $_SESSION['user_id'];
$query = "SELECT d.*, h.name as hospital_name 
          FROM doctor d 
          LEFT JOIN hospital h ON d.hospitalid = h.id 
          WHERE d.doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

// Get patient ID from URL parameter
$patient_id = isset($_GET['patient_id']) ? $_GET['patient_id'] : null;

if (!$patient_id) {
    header("Location: patients.php");
    exit();
}

// Get patient information
$patient_query = "SELECT * FROM patients WHERE patientID = ?";
$stmt = $conn->prepare($patient_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient_result = $stmt->get_result();
$patient = $patient_result->fetch_assoc();

if (!$patient) {
    header("Location: patients.php");
    exit();
}

// Get patient's medical history
$medical_records_query = "SELECT 
    d.doctor_id,
    d.name as doctor_name,
    d.specialization,
    h.name as hospital_name,
    GROUP_CONCAT(DISTINCT p.prescription_id) as prescription_ids,
    GROUP_CONCAT(DISTINCT pr.report_id) as report_ids,
    COUNT(DISTINCT p.prescription_id) as prescription_count,
    COUNT(DISTINCT pr.report_id) as report_count,
    MAX(GREATEST(COALESCE(p.created_at, '1970-01-01'), COALESCE(pr.report_date, '1970-01-01'))) as last_updated,
    d.profile_image
    FROM doctor d
    LEFT JOIN prescriptions p ON d.doctor_id = p.doctor_id AND p.patient_id = ?
    LEFT JOIN patient_reports pr ON d.doctor_id = pr.doctor_id AND pr.patient_id = ?
    LEFT JOIN hospital h ON d.hospitalid = h.id
    WHERE (p.prescription_id IS NOT NULL OR pr.report_id IS NOT NULL)
    AND d.doctor_id = ?
    GROUP BY d.doctor_id, d.name, d.specialization, h.name
    ORDER BY last_updated DESC";
$stmt = $conn->prepare($medical_records_query);
$stmt->bind_param("iis", $patient_id, $patient_id, $doctor_id);
$stmt->execute();
$medical_records = $stmt->get_result();

// Function to fetch detailed records for a doctor
function getDoctorRecords($conn, $doctor_id, $patient_id) {
    $records = array();
    
    // Fetch prescriptions
    $prescriptions_query = "SELECT p.*, a.appointment_date
        FROM prescriptions p
        JOIN appointments a ON p.appointment_id = a.appointment_id
        WHERE p.doctor_id = ? AND p.patient_id = ?
        ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($prescriptions_query);
    $stmt->bind_param("si", $doctor_id, $patient_id);
    $stmt->execute();
    $records['prescriptions'] = $stmt->get_result();
    
    // Fetch reports
    $reports_query = "SELECT * FROM patient_reports 
        WHERE doctor_id = ? AND patient_id = ?
        ORDER BY report_date DESC";
    $stmt = $conn->prepare($reports_query);
    $stmt->bind_param("si", $doctor_id, $patient_id);
    $stmt->execute();
    $records['reports'] = $stmt->get_result();
    
    return $records;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Medical History - MediHealth</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #0ea5e9;
            --accent-color: #f43f5e;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --border-color: #e2e8f0;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-primary);
            line-height: 1.5;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 80px;
            }
        }

        .history-container {
            max-width: 1280px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-header {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 8px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        }

        .header-content {
            flex: 1;
        }

        .page-header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
        }

        .page-header p {
            font-size: 1.125rem;
            color: var(--text-secondary);
            max-width: 600px;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.95rem;
            transition: var(--transition);
            background: var(--bg-light);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .filter-btn {
            padding: 0.875rem 1.5rem;
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            color: var(--text-primary);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-btn:hover {
            background: var(--primary-color);
            color: var(--bg-white);
            border-color: var(--primary-color);
        }

        .patient-info {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            position: sticky;
            top: 1rem;
            z-index: 10;
        }

        .patient-info h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .patient-info h2::before {
            content: '\f007';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--primary-color);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: var(--radius);
            transition: var(--transition);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .info-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
            opacity: 0;
            transition: var(--transition);
        }

        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            border-color: var(--primary-color);
        }

        .info-item:hover::before {
            opacity: 1;
        }

        .info-item label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .info-item span {
            font-size: 1.125rem;
            color: var(--text-primary);
            font-weight: 500;
        }

        .records-container {
            display: grid;
            gap: 2rem;
        }

        .doctor-card {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .doctor-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            opacity: 0;
            transition: var(--transition);
        }

        .doctor-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .doctor-card:hover::before {
            opacity: 1;
        }

        .doctor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
        }

        .doctor-info {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .doctor-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--bg-white);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 3px solid var(--bg-white);
            position: relative;
        }

        .doctor-avatar::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            padding: 2px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }

        .doctor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doctor-details h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        .doctor-details p {
            color: var(--text-secondary);
            font-size: 1.125rem;
        }

        .record-stats {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: var(--bg-light);
            border-radius: var(--radius);
            color: var(--text-primary);
            font-weight: 500;
            border: 1px solid var(--border-color);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-item::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            opacity: 0;
            transition: var(--transition);
        }

        .stat-item:hover {
            color: var(--bg-white);
            border-color: var(--primary-color);
        }

        .stat-item:hover::before {
            opacity: 1;
        }

        .stat-item i {
            font-size: 1.25rem;
            color: var(--primary-color);
            position: relative;
            z-index: 1;
        }

        .stat-item span {
            position: relative;
            z-index: 1;
        }

        .stat-item:hover i {
            color: var(--bg-white);
        }

        .btn {
            padding: 0.875rem 1.75rem;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            letter-spacing: 0.025em;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            opacity: 0;
            transition: var(--transition);
        }

        .btn span {
            position: relative;
            z-index: 1;
        }

        .btn i {
            position: relative;
            z-index: 1;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--bg-white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            color: var(--bg-white);
            border-color: transparent;
        }

        .btn-outline:hover::before {
            opacity: 1;
        }

        .records-content {
            display: none;
            padding-top: 1.5rem;
        }

        .records-content.active {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-title {
            font-size: 1.375rem;
            color: var(--text-primary);
            margin: 2rem 0 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .records-grid {
            display: grid;
            gap: 1.5rem;
        }

        .record-item {
            background: var(--bg-light);
            border-radius: var(--radius);
            padding: 2rem;
            border: 1px solid var(--border-color);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .record-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
            opacity: 0;
            transition: var(--transition);
        }

        .record-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            border-color: var(--primary-color);
        }

        .record-item:hover::before {
            opacity: 1;
        }

        .record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .record-title {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1.25rem;
        }

        .record-date {
            color: var(--text-secondary);
            font-size: 0.875rem;
            background: var(--bg-white);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .record-details {
            color: var(--text-primary);
        }

        .record-details p {
            margin-bottom: 1rem;
            line-height: 1.7;
        }

        .record-details strong {
            color: var(--text-primary);
            font-weight: 600;
        }

        .prescription-image {
            max-width: 100%;
            margin-top: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .prescription-image:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }

        .empty-state p {
            color: var(--text-secondary);
            font-size: 1.25rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination-item {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            color: var(--text-primary);
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
        }

        .pagination-item:hover,
        .pagination-item.active {
            background: var(--primary-color);
            color: var(--bg-white);
            border-color: var(--primary-color);
        }

        .pagination-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 1.5rem;
                padding: 2rem;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .search-box {
                width: 100%;
            }

            .doctor-header {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }

            .doctor-info {
                flex-direction: column;
                text-align: center;
            }

            .record-stats {
                flex-wrap: wrap;
                justify-content: center;
            }

            .stat-item {
                width: 100%;
                justify-content: center;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .page-header h1 {
                font-size: 1.875rem;
            }

            .patient-info {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="history-container">
                <div class="page-header">
                    <h1>Patient Medical History</h1>
                    <p>View complete medical records, prescriptions, and reports</p>
                </div>

                <!-- Patient Information -->
                <div class="patient-info">
                    <h2>Patient Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Name</label>
                            <span><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Email</label>
                            <span><?php echo htmlspecialchars($patient['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Phone</label>
                            <span><?php echo htmlspecialchars($patient['number']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Date of Birth</label>
                            <span><?php echo htmlspecialchars($patient['dob']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Gender</label>
                            <span><?php echo htmlspecialchars($patient['gender']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Blood Group</label>
                            <span><?php echo htmlspecialchars($patient['bloodgroup']); ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($medical_records->num_rows > 0): ?>
                    <?php while ($record = $medical_records->fetch_assoc()): ?>
                        <div class="doctor-card">
                            <div class="doctor-header">
                                <div class="doctor-info">
                                    <div class="doctor-avatar">
                                        <?php if (!empty($record['profile_image'])): ?>
                                            <img src="../uploads/doctor_profiles/<?php echo htmlspecialchars($record['profile_image']); ?>" alt="Doctor Profile">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($record['doctor_name'], 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="doctor-details">
                                        <h3>Dr. <?php echo htmlspecialchars($record['doctor_name']); ?></h3>
                                        <p><?php echo htmlspecialchars($record['specialization']); ?> - 
                                           <?php echo htmlspecialchars($record['hospital_name']); ?></p>
                                    </div>
                                </div>
                                <div class="record-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-prescription"></i>
                                        <?php echo $record['prescription_count']; ?> Prescriptions
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-file-medical"></i>
                                        <?php echo $record['report_count']; ?> Reports
                                    </div>
                                    <button class="btn btn-outline" onclick="toggleRecords('<?php echo $record['doctor_id']; ?>')">
                                        <i class="fas fa-chevron-down"></i>
                                        View Records
                                    </button>
                                </div>
                            </div>
                            
                            <div id="records-<?php echo $record['doctor_id']; ?>" class="records-content">
                                <?php 
                                $doctor_records = getDoctorRecords($conn, $record['doctor_id'], $patient_id);
                                
                                // Display Prescriptions
                                if ($doctor_records['prescriptions']->num_rows > 0): ?>
                                    <h3 class="section-title">
                                        <i class="fas fa-prescription icon"></i>
                                        Prescriptions
                                    </h3>
                                    <?php while ($prescription = $doctor_records['prescriptions']->fetch_assoc()): ?>
                                        <div class="record-item">
                                            <div class="record-header">
                                                <div class="record-title">Prescription</div>
                                                <div class="record-date">
                                                    <?php echo date('F j, Y', strtotime($prescription['created_at'])); ?>
                                                </div>
                                            </div>
                                            <div class="record-details">
                                                <p><strong>Diagnosis:</strong> <?php echo nl2br(htmlspecialchars($prescription['diagnosis'])); ?></p>
                                                <p><strong>Medications:</strong> <?php echo nl2br(htmlspecialchars($prescription['medications'])); ?></p>
                                                <p><strong>Appointment Date:</strong> <?php echo date('F j, Y', strtotime($prescription['appointment_date'])); ?></p>
                                                <?php if (!empty($prescription['prescription_image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($prescription['prescription_image']); ?>" 
                                                         alt="Prescription" class="prescription-image">
                                                    <a href="<?php echo htmlspecialchars($prescription['prescription_image']); ?>" 
                                                       download class="btn btn-primary" style="margin-top: 1rem;">
                                                        <i class="fas fa-download"></i> Download Prescription
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile;
                                endif;

                                // Display Reports
                                if ($doctor_records['reports']->num_rows > 0): ?>
                                    <h3 class="section-title">
                                        <i class="fas fa-file-medical icon"></i>
                                        Medical Reports
                                    </h3>
                                    <?php while ($report = $doctor_records['reports']->fetch_assoc()): ?>
                                        <div class="record-item">
                                            <div class="record-header">
                                                <div class="record-title"><?php echo htmlspecialchars($report['report_title']); ?></div>
                                                <div class="record-date">
                                                    <?php echo date('F j, Y', strtotime($report['report_date'])); ?>
                                                </div>
                                            </div>
                                            <div class="record-details">
                                                <p><strong>Report Type:</strong> <?php echo htmlspecialchars($report['report_type']); ?></p>
                                                <a href="<?php echo htmlspecialchars($report['file_path']); ?>" 
                                                   target="_blank" class="btn btn-primary">
                                                    <i class="fas fa-download"></i> Download Report
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile;
                                endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-medical" style="font-size: 3rem; color: #3498db; margin-bottom: 1rem;"></i>
                        <p>No medical records found for this patient.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function toggleRecords(doctorId) {
            const content = document.getElementById(`records-${doctorId}`);
            const button = content.previousElementSibling.querySelector('.btn');
            const icon = button.querySelector('i');
            
            content.classList.toggle('active');
            
            if (content.classList.contains('active')) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
                button.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Records';
            } else {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
                button.innerHTML = '<i class="fas fa-chevron-down"></i> View Records';
            }
        }
    </script>
</body>
</html> 