<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['patientID'])) {
    header("Location: ../index.php");
    exit();
}

include_once('../config/configdatabase.php');

$patient_id = $_SESSION['patientID'];
$errors = array();

// Fetch patient details
$patient_query = "SELECT * FROM patients WHERE patientID = ?";
$stmt = $conn->prepare($patient_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient_result = $stmt->get_result();
$patient_data = $patient_result->fetch_assoc();

// Fetch all appointments for the patient
$appointments_query = "SELECT a.*, 
    h.name as hospital_name,
    d.department_name,
    doc.name as doctor_name,
    doc.specialization,
    op.name as other_name, op.age as other_age, op.gender as other_gender, op.blood_group as other_blood_group, op.relation as other_relation, op.address as other_address, op.email as other_email, op.phone as other_phone
    FROM appointments a
    JOIN hospital h ON a.hospital_id = h.id
    JOIN department d ON a.department_id = d.department_id
    JOIN doctor doc ON a.doctor_id = doc.doctor_id
    LEFT JOIN other_patients op ON a.other_patient_id = op.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $conn->prepare($appointments_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments_result = $stmt->get_result();
$appointments = array();
while ($row = $appointments_result->fetch_assoc()) {
    $appointments[] = $row;
}

// Fetch all hospitals
$hospitals_query = "SELECT * FROM hospital ORDER BY name";
$hospitals_result = $conn->query($hospitals_query);
$hospitals = array();
while ($row = $hospitals_result->fetch_assoc()) {
    $hospitals[] = $row;
}

// Fetch all departments
$departments_query = "SELECT * FROM department ORDER BY department_name";
$departments_result = $conn->query($departments_query);
$departments = array();
while ($row = $departments_result->fetch_assoc()) {
    $departments[] = $row;
}

// Fetch all doctors
$doctors_query = "SELECT d.*, h.name as hospital_name, dept.department_name 
    FROM doctor d
    JOIN hospital h ON d.hospitalid = h.id
    JOIN department dept ON d.department_id = dept.department_id
    ORDER BY d.name";
$doctors_result = $conn->query($doctors_query);
$doctors = array();
while ($row = $doctors_result->fetch_assoc()) {
    $doctors[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($patient_data['name']); ?></h1>
        
        <!-- Patient Information -->
        <section class="patient-info">
            <h2>Your Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Name:</label>
                    <span><?php echo htmlspecialchars($patient_data['name']); ?></span>
                </div>
                <div class="info-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($patient_data['email']); ?></span>
                </div>
                <div class="info-item">
                    <label>Phone:</label>
                    <span><?php echo htmlspecialchars($patient_data['phone']); ?></span>
                </div>
                <div class="info-item">
                    <label>Address:</label>
                    <span><?php echo htmlspecialchars($patient_data['address']); ?></span>
                </div>
            </div>
        </section>

        <!-- Appointments -->
        <section class="appointments">
            <h2>Your Appointments</h2>
            <?php if (empty($appointments)): ?>
                <p>No appointments found.</p>
            <?php else: ?>
                <div class="appointments-grid">
                    <?php foreach ($appointments as $appointment): ?>
                        <div class="appointment-card">
                            <div class="appointment-header">
                                <h3><?php echo htmlspecialchars($appointment['hospital_name']); ?></h3>
                                <span class="status <?php echo $appointment['status']; ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </div>
                            <div class="appointment-details">
                                <?php if ($appointment['appointment_for'] === 'others'): ?>
                                    <p><strong>Patient Name:</strong> <?php echo htmlspecialchars($appointment['other_name']); ?></p>
                                    <p><strong>Age:</strong> <?php echo htmlspecialchars($appointment['other_age']); ?></p>
                                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($appointment['other_gender']); ?></p>
                                    <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($appointment['other_blood_group']); ?></p>
                                    <p><strong>Relation:</strong> <?php echo htmlspecialchars($appointment['other_relation']); ?></p>
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($appointment['other_address']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($appointment['other_email']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($appointment['other_phone']); ?></p>
                                <?php else: ?>
                                    <p><strong>Patient Name:</strong> <?php echo htmlspecialchars($patient_data['name']); ?></p>
                                    <!-- You can add more registered patient info here if needed -->
                                <?php endif; ?>
                                <p><strong>Doctor:</strong> <?php echo htmlspecialchars($appointment['doctor_name']); ?></p>
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($appointment['department_name']); ?></p>
                                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                                <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                                <p><strong>Reason:</strong> <?php echo htmlspecialchars($appointment['reason']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Hospitals -->
        <section class="hospitals">
            <h2>Available Hospitals</h2>
            <div class="hospitals-grid">
                <?php foreach ($hospitals as $hospital): ?>
                    <div class="hospital-card">
                        <h3><?php echo htmlspecialchars($hospital['name']); ?></h3>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($hospital['city']); ?></p>
                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($hospital['phone']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Departments -->
        <section class="departments">
            <h2>Available Departments</h2>
            <div class="departments-grid">
                <?php foreach ($departments as $department): ?>
                    <div class="department-card">
                        <h3><?php echo htmlspecialchars($department['department_name']); ?></h3>
                        <p><?php echo htmlspecialchars($department['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Doctors -->
        <section class="doctors">
            <h2>Our Doctors</h2>
            <div class="doctors-grid">
                <?php foreach ($doctors as $doctor): ?>
                    <div class="doctor-card">
                        <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                        <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></p>
                        <p><strong>Hospital:</strong> <?php echo htmlspecialchars($doctor['hospital_name']); ?></p>
                        <p><strong>Department:</strong> <?php echo htmlspecialchars($doctor['department_name']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <script src="../js/script.js"></script>
</body>
</html> 