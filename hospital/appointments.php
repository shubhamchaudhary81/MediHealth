<?php
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
    $hospital_name = $hospital['name'];
    // $hospital_location = $hospital['location'];
    $hospital_location = $hospital['city'] . ', ' . $hospital['district'] . ', ' . $hospital['zone'];
    $hospital_id = $hospital['id'];
} else {
    header("Location: hospitaladminlogin.php");
    exit();
}

$stmt->close();

// Get selected date from GET parameter or default to today
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$current_month = date('F Y', strtotime($selected_date));
$first_day = date('Y-m-01', strtotime($selected_date));
$last_day = date('Y-m-t', strtotime($selected_date));

// Fetch appointments for the selected date
$appointments_query = "SELECT a.*, p.first_name as patient_name, d.name as doctor_name, d.specialization 
                      FROM appointments a 
                      INNER JOIN patients p ON a.patient_id = p.patientID 
                      INNER JOIN doctor d ON a.doctor_id = d.doctor_id 
                      WHERE a.hospital_id = ? 
                      AND a.appointment_date = ?
                      ORDER BY a.appointment_time ASC";

if (!($stmt = $conn->prepare($appointments_query))) {
    die("Error preparing appointments query: " . $conn->error);
}

if (!$stmt->bind_param("is", $hospital_id, $selected_date)) {
    die("Error binding parameters for appointments query: " . $stmt->error);
}

if (!$stmt->execute()) {
    die("Error executing appointments query: " . $stmt->error);
}

$appointments_result = $stmt->get_result();
$stmt->close();

// Fetch appointments count for each day of the month
$monthly_appointments_query = "SELECT appointment_date, COUNT(*) as count 
                             FROM appointments 
                             WHERE hospital_id = ? 
                             AND appointment_date BETWEEN ? AND ?
                             GROUP BY appointment_date";

if (!($stmt = $conn->prepare($monthly_appointments_query))) {
    die("Error preparing monthly appointments query: " . $conn->error);
}

if (!$stmt->bind_param("iss", $hospital_id, $first_day, $last_day)) {
    die("Error binding parameters for monthly appointments query: " . $stmt->error);
}

if (!$stmt->execute()) {
    die("Error executing monthly appointments query: " . $stmt->error);
}

$monthly_result = $stmt->get_result();
$stmt->close();

$appointments_by_date = array();
while ($row = $monthly_result->fetch_assoc()) {
    $appointments_by_date[$row['appointment_date']] = $row['count'];
}

include('sidebar.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Hospital Admin Panel</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .appointments-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
        }

        .calendar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-header h2 {
            font-size: 18px;
            color: var(--text-color);
        }

        .calendar-nav {
            display: flex;
            gap: 10px;
        }

        .calendar-nav button {
            background: none;
            border: none;
            color: var(--text-color);
            cursor: pointer;
            font-size: 16px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-day {
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .calendar-day.past {
            opacity: 0.7;
        }
        
        .calendar-day.has-appointments::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background-color: var(--primary-color);
            border-radius: 50%;
        }
        
        .appointment-count {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 12px;
            color: var(--primary-color);
        }
        
        .no-appointments {
            text-align: center;
            padding: 20px;
            color: #64748b;
        }
        
        .appointment-actions button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .appointment-actions button:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .appointments-list {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .appointment-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .appointment-item:hover {
            background: var(--background-color);
        }

        .appointment-time {
            min-width: 100px;
        }

        .appointment-time h4 {
            font-size: 16px;
            color: var(--text-color);
            margin: 0;
        }

        .appointment-time p {
            font-size: 12px;
            color: #64748b;
        }

        .appointment-info {
            flex: 1;
        }

        .appointment-info h3 {
            font-size: 16px;
            color: var(--text-color);
            margin: 0 0 5px 0;
        }

        .appointment-info p {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }

        .appointment-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .status-confirmed {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .hospital-info {
            padding: 0 20px;
        }

        .hospital-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
        }

        .hospital-address {
            margin: 4px 0 0;
            font-size: 14px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <?php include('header.php'); ?>
        
        <div class="appointments-container">
            <div class="calendar">
                <div class="calendar-header">
                    <h2><?php echo htmlspecialchars($current_month); ?></h2>
                    <div class="calendar-nav">
                        <button onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                        <button onclick="changeMonth(1)"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="calendar-grid">
                    <?php
                    // Generate calendar days
                    $first_day_of_month = date('N', strtotime($first_day)) - 1;
                    $days_in_month = date('t', strtotime($selected_date));
                    $today = date('Y-m-d');
                    
                    // Add empty cells for days before the first day of month
                    for ($i = 0; $i < $first_day_of_month; $i++) {
                        echo '<div class="calendar-day empty"></div>';
                    }
                    
                    // Add days of the month
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $date = date('Y-m-d', strtotime("$first_day +".($day-1)." days"));
                        $classes = ['calendar-day'];
                        
                        if ($date < $today) {
                            $classes[] = 'past';
                        }
                        if ($date === $selected_date) {
                            $classes[] = 'active';
                        }
                        if (isset($appointments_by_date[$date])) {
                            $classes[] = 'has-appointments';
                        }
                        
                        echo '<div class="'.implode(' ', $classes).'" onclick="selectDate(\''.$date.'\')">';
                        echo $day;
                        if (isset($appointments_by_date[$date])) {
                            echo '<span class="appointment-count">'.htmlspecialchars($appointments_by_date[$date]).'</span>';
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="appointments-list">
                <h2><?php echo date('F j, Y', strtotime($selected_date)); ?> Appointments</h2>
                <?php if ($appointments_result->num_rows > 0): ?>
                    <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                        <div class="appointment-item">
                            <div class="appointment-time">
                                <h4><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></h4>
                                <p>45 mins</p>
                            </div>
                            <div class="appointment-info">
                                <h3><?php echo htmlspecialchars($appointment['patient_name']); ?></h3>
                                <p><?php echo htmlspecialchars($appointment['reason']); ?> with Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?></p>
                            </div>
                            <span class="appointment-status status-<?php echo strtolower($appointment['status']); ?>">
                                <?php echo ucfirst($appointment['status']); ?>
                            </span>
                            <div class="appointment-actions">
                                <button onclick="updateStatus(<?php echo $appointment['appointment_id']; ?>, 'confirmed')" 
                                        <?php echo $appointment['status'] === 'confirmed' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="updateStatus(<?php echo $appointment['appointment_id']; ?>, 'cancelled')"
                                        <?php echo $appointment['status'] === 'cancelled' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-appointments">
                        <p>No appointments scheduled for this date</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function selectDate(date) {
            window.location.href = 'appointments.php?date=' + date;
        }
        
        function changeMonth(offset) {
            const currentDate = new Date('<?php echo $selected_date; ?>');
            currentDate.setMonth(currentDate.getMonth() + offset);
            const newDate = currentDate.toISOString().split('T')[0];
            window.location.href = 'appointments.php?date=' + newDate;
        }
        
        function updateStatus(appointmentId, status) {
            if (confirm('Are you sure you want to mark this appointment as ' + status + '?')) {
                fetch('update_appointment_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        appointment_id: appointmentId,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to update appointment status: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the appointment status');
                });
            }
        }
    </script>
</body>
</html> 