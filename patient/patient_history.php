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

// Fetch patient's prescriptions and reports grouped by doctor
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
    WHERE p.prescription_id IS NOT NULL OR pr.report_id IS NOT NULL
    GROUP BY d.doctor_id, d.name, d.specialization, h.name
    ORDER BY last_updated DESC";
$stmt = $conn->prepare($medical_records_query);
$stmt->bind_param("ii", $patient_id, $patient_id);
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
    <title>Medical History - MediHealth</title>
    <style>
        .history-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 15px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .doctor-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .doctor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .doctor-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .doctor-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(52,152,219,0.3);
            overflow: hidden;
        }

        .doctor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doctor-details h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .doctor-details p {
            margin: 0.5rem 0 0;
            color: #666;
            font-size: 1.1rem;
        }

        .record-stats {
            display: flex;
            gap: 1.5rem;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            font-size: 1rem;
            color: #3498db;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-item i {
            font-size: 1.2rem;
        }

        .records-content {
            display: none;
            margin-top: 2rem;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .records-content.active {
            display: block;
        }

        .record-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
            transition: transform 0.3s ease;
        }

        .record-item:hover {
            transform: translateX(5px);
        }

        .record-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .record-title {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.2rem;
        }

        .record-date {
            color: #666;
            font-size: 0.95rem;
        }

        .record-details {
            color: #555;
            font-size: 1rem;
            line-height: 1.6;
        }

        .record-details p {
            margin: 0.5rem 0;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 1rem;
        }

        .btn-primary {
            background: #3498db;
            color: white;
            box-shadow: 0 4px 15px rgba(52,152,219,0.3);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #3498db;
            color: #3498db;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52,152,219,0.4);
        }

        .prescription-image {
            max-width: 100%;
            height: auto;
            margin-top: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
            font-size: 1.2rem;
            background: #f8f9fa;
            border-radius: 12px;
            margin: 2rem 0;
        }

        .section-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .icon {
            font-size: 1.4rem;
        }

        @media (max-width: 768px) {
            .doctor-header {
                flex-direction: column;
                gap: 1rem;
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
        }
    </style>
</head>
<body>
    <div class="history-container">
        <div class="page-header">
            <h1>Medical History</h1>
            <p>View your complete medical records, prescriptions, and reports</p>
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
                <p>No medical records found.</p>
            </div>
        <?php endif; ?>
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