<?php
session_start();
if (!isset($_SESSION['patientID'])) {
    header('Location: ../index.php');
    exit();
}
include_once('../config/configdatabase.php');
include_once('../include/header.php');

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
$conn->close();

// Separate appointments into myself and others
$myself_appointments = array_filter($appointments, function($app) {
    return $app['appointment_for'] === 'myself';
});

$others_appointments = array_filter($appointments, function($app) {
    return $app['appointment_for'] === 'others';
});
?>

<div class="container appointments-container">
    <div class="section-header">
        <div class="badge">Appointments</div>
        <h2>Your Appointments</h2>
        <p>View and manage all your appointments</p>
    </div>

    <div class="appointments-section">
        <div class="view-toggle">
            <button class="toggle-btn active" data-view="myself">My Appointments</button>
            <button class="toggle-btn" data-view="others">Others' Appointments</button>
            <!-- <button class="toggle-btn" data-view="history">Appointment History</button> -->
        </div>

        <!-- Myself Appointments Table -->
        <div class="appointments-table-container" id="myself-view">
            <?php if (empty($myself_appointments)): ?>
                <div class="empty-message">No appointments found for yourself.</div>
            <?php else: ?>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Hospital</th>
                            <th>Doctor</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myself_appointments as $appointment): ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['hospital_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['department_name']); ?></td>
                                <td><span class="status-badge <?php echo strtolower($appointment['status']); ?>"><?php echo ucfirst($appointment['status']); ?></span></td>
                                <td>
                                    <button class="view-btn" data-appointment='<?php echo json_encode($appointment); ?>' data-patient='<?php echo json_encode($patient_data); ?>'>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Others Appointments Table -->
        <div class="appointments-table-container" id="others-view" style="display: none;">
            <?php if (empty($others_appointments)): ?>
                <div class="empty-message">No appointments found for others.</div>
            <?php else: ?>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Patient Name</th>
                            <th>Hospital</th>
                            <th>Doctor</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($others_appointments as $appointment): ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['other_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['hospital_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                <td><span class="status-badge <?php echo strtolower($appointment['status']); ?>"><?php echo ucfirst($appointment['status']); ?></span></td>
                                <td>
                                    <button class="view-btn" data-appointment='<?php echo json_encode($appointment); ?>' data-patient='<?php echo json_encode($patient_data); ?>'>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Appointment History Table -->
        <div class="appointments-table-container" id="history-view" style="display: none;">
            <?php if (empty($appointments)): ?>
                <div class="empty-message">No appointment history found.</div>
            <?php else: ?>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Hospital</th>
                            <th>Doctor</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                <td>
                                    <?php 
                                    if ($appointment['appointment_for'] === 'others') {
                                        echo htmlspecialchars($appointment['other_name']);
                                    } else {
                                        echo htmlspecialchars($patient_data['name']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($appointment['hospital_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['department_name']); ?></td>
                                <td><span class="status-badge <?php echo strtolower($appointment['status']); ?>"><?php echo ucfirst($appointment['status']); ?></span></td>
                                <td>
                                    <button class="view-btn" data-appointment='<?php echo json_encode($appointment); ?>' data-patient='<?php echo json_encode($patient_data); ?>'>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for Appointment Details -->
<div id="appointmentModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Appointment Details</h2>
        <div id="modalContent"></div>
    </div>
</div>

<style>
     .container {
        /* max-width: 1200px;
        margin: 0 auto;
        padding: 20px; */
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 1.25rem;
    }

.appointments-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.section-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.badge {
    display: inline-block;
    padding: 8px 18px;
    background: #3498db;
    color: white;
    border-radius: 20px;
    font-size: 15px;
    margin-bottom: 12px;
    font-weight: 500;
}

.appointments-section {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    padding: 2.5rem 2rem;
}

.view-toggle {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.toggle-btn {
    padding: 0.8rem 2rem;
    border: 2px solid #3498db;
    background: transparent;
    color: #3498db;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.toggle-btn.active {
    background: #3498db;
    color: white;
}

.toggle-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52,152,219,0.2);
}

.appointments-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.appointments-table th,
.appointments-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.appointments-table th {
    background: #f8fafc;
    font-weight: 600;
    color: #2d3748;
}

.status-badge {
    padding: 0.3rem 1rem;
    border-radius: 1rem;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-badge.scheduled { background: #e3f6fc; color: #3498db; }
.status-badge.completed { background: #e6f4ea; color: #28a745; }
.status-badge.cancelled { background: #fee2e2; color: #ef4444; }

.view-btn {
    padding: 0.5rem 1rem;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.view-btn:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.empty-message {
    text-align: center;
    padding: 2rem;
    color: #666;
    font-size: 1.1rem;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 15px;
    width: 90%;
    max-width: 800px;
    position: relative;
    max-height: 80vh;
    overflow-y: auto;
}

.close {
    position: absolute;
    right: 1.5rem;
    top: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
}

.close:hover {
    color: #000;
}

#modalContent {
    margin-top: 1.5rem;
}

.modal-detail {
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
}

.modal-detail h3 {
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.modal-detail p {
    margin: 0.3rem 0;
    color: #4a5568;
}

@media (max-width: 768px) {
    .appointments-table {
        display: block;
        overflow-x: auto;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}

/* Add these new styles */
.history-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
}

.history-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.history-title {
    font-weight: 600;
    color: #2c3e50;
}

.history-date {
    color: #666;
    font-size: 0.9rem;
}

.history-details {
    color: #555;
    font-size: 0.95rem;
    line-height: 1.5;
}

.history-status {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    display: inline-block;
    margin-top: 0.5rem;
}

.history-status.completed { background: #d4edda; color: #155724; }
.history-status.cancelled { background: #f8d7da; color: #721c24; }
.history-status.scheduled { background: #cce5ff; color: #004085; }

.appointments-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.appointments-header h1 {
    font-size: 2rem;
    color: #2c3e50;
    margin: 0;
}

.appointments-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.tab-button {
    padding: 0.8rem 1.5rem;
    border: none;
    background: #f8f9fa;
    color: #2c3e50;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.tab-button.active {
    background: #3498db;
    color: white;
}

.tab-button:hover:not(.active) {
    background: #e9ecef;
}

.appointments-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.appointments-table th,
.appointments-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.appointments-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.appointments-table tr:hover {
    background: #f8f9fa;
}

.status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-confirmed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.status-completed {
    background: #cce5ff;
    color: #004085;
}

.view-details-btn {
    padding: 0.5rem 1rem;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.view-details-btn:hover {
    background: #2980b9;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal-content {
    position: relative;
    background: white;
    width: 90%;
    max-width: 600px;
    margin: 2rem auto;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.close-modal {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
}

.appointment-details {
    margin-top: 1rem;
}

.detail-item {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.3rem;
}

.detail-value {
    color: #666;
}

@media (max-width: 768px) {
    .appointments-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .appointments-table {
        display: block;
        overflow-x: auto;
    }

    .appointments-table th,
    .appointments-table td {
        min-width: 120px;
    }

    .modal-content {
        width: 95%;
        margin: 1rem auto;
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .appointments-container {
        padding: 1rem;
    }

    .appointments-header h1 {
        font-size: 1.5rem;
    }

    .tab-button {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }

    .status-badge {
        padding: 0.3rem 0.6rem;
        font-size: 0.8rem;
    }

    .view-details-btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle buttons functionality
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    const myselfView = document.getElementById('myself-view');
    const othersView = document.getElementById('others-view');
    const historyView = document.getElementById('history-view');

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Update active button
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide appropriate view
            myselfView.style.display = view === 'myself' ? 'block' : 'none';
            othersView.style.display = view === 'others' ? 'block' : 'none';
            historyView.style.display = view === 'history' ? 'block' : 'none';
        });
    });

    // Modal functionality
    const modal = document.getElementById('appointmentModal');
    const modalContent = document.getElementById('modalContent');
    const closeBtn = document.querySelector('.close');
    const viewBtns = document.querySelectorAll('.view-btn');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const appointment = JSON.parse(this.dataset.appointment);
            const patient = JSON.parse(this.dataset.patient);
            
            let content = '';
            
            if (appointment.appointment_for === 'others') {
                content = `
                    <div class="modal-detail">
                        <h3>Patient Information</h3>
                        <p><strong>Name:</strong> ${appointment.other_name}</p>
                        <p><strong>Age:</strong> ${appointment.other_age}</p>
                        <p><strong>Gender:</strong> ${appointment.other_gender}</p>
                        <p><strong>Blood Group:</strong> ${appointment.other_blood_group}</p>
                        <p><strong>Relation:</strong> ${appointment.other_relation}</p>
                        <p><strong>Address:</strong> ${appointment.other_address}</p>
                        <p><strong>Email:</strong> ${appointment.other_email}</p>
                        <p><strong>Phone:</strong> ${appointment.other_phone}</p>
                    </div>
                `;
            } else {
                content = `
                    <div class="modal-detail">
                        <h3>Patient Information</h3>
                        <p><strong>Name:</strong> ${patient.name}</p>
                    </div>
                `;
            }
            
            content += `
                <div class="modal-detail">
                    <h3>Appointment Details</h3>
                    <p><strong>Hospital:</strong> ${appointment.hospital_name}</p>
                    <p><strong>Doctor:</strong> ${appointment.doctor_name} (${appointment.specialization})</p>
                    <p><strong>Department:</strong> ${appointment.department_name}</p>
                    <p><strong>Date:</strong> ${new Date(appointment.appointment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                    <p><strong>Time:</strong> ${new Date('1970-01-01T' + appointment.appointment_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })}</p>
                    <p><strong>Status:</strong> <span class="status-badge ${appointment.status.toLowerCase()}">${appointment.status}</span></p>
                    <p><strong>Reason:</strong> ${appointment.reason}</p>
                </div>
            `;
            
            modalContent.innerHTML = content;
            modal.style.display = 'block';
        });
    });

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script> 