<?php
// Start session if not already started
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

// Check if doctor_id is provided
if (!isset($_GET['doctor_id'])) {
    header("Location: doctors.php");
    exit();
}

$doctor_id = $_GET['doctor_id'];

// Fetch doctor information
$doctor_query = "SELECT d.*, dept.department_name, h.name as hospital_name 
                FROM doctor d
                JOIN department dept ON d.department_id = dept.department_id
                JOIN hospital h ON d.hospitalid = h.id
                WHERE d.doctor_id = ? AND d.hospitalid IN (
                    SELECT hospitalid FROM hospitaladmin WHERE adminid = ?
                )";

$stmt = $conn->prepare($doctor_query);
$stmt->bind_param("si", $doctor_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: doctors.php");
    exit();
}

$doctor = $result->fetch_assoc();

// Process schedule update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_data = json_decode($_POST['schedule'], true);
    $formatted_schedule = [];
    
    foreach ($schedule_data as $day => $times) {
        if (!empty($times)) {
            $formatted_schedule[] = $day . ': ' . implode(', ', $times);
        }
    }
    
    $schedule = implode("\n", $formatted_schedule);
    
    // Update the schedule in the database
    $update_query = "UPDATE doctor SET schedule = ? WHERE doctor_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ss", $schedule, $doctor_id);
    
    if ($stmt->execute()) {
        $success_message = "Schedule updated successfully!";
        // Refresh doctor data
        $doctor['schedule'] = $schedule;
    } else {
        $error_message = "Error updating schedule: " . $conn->error;
    }
}

include('sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Schedule - MediHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .schedule-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .schedule-header {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .schedule-header h1 {
            margin: 0;
            color: var(--text-color);
            font-size: 1.8rem;
            font-weight: 600;
        }

        .schedule-header .doctor-info {
            margin-top: 8px;
            color: #64748b;
        }

        .schedule-grid {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .schedule-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 16px;
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .schedule-row:last-child {
            border-bottom: none;
        }

        .day-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .time-slot {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .time-slot input[type="checkbox"] {
            margin: 0;
        }

        .time-slot label {
            font-size: 0.9rem;
            color: var(--text-color);
            cursor: pointer;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 24px;
            transition: all 0.2s ease;
        }

        .back-btn:hover {
            background: #4f6df5;
            transform: translateY(-2px);
        }

        .save-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 24px;
            transition: all 0.2s ease;
        }

        .save-btn:hover {
            background: #4f6df5;
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 768px) {
            .schedule-container {
                padding: 16px;
            }

            .schedule-row {
                grid-template-columns: 1fr;
            }

            .day-label {
                margin-bottom: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <?php include('header.php'); ?>

        <div class="schedule-container">
            <a href="doctors.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Doctors List
            </a>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="schedule-header">
                <h1>Doctor Schedule</h1>
                <div class="doctor-info">
                    Dr. <?php echo htmlspecialchars($doctor['name']); ?> - 
                    <?php echo htmlspecialchars($doctor['specialization']); ?>
                </div>
            </div>

            <form id="scheduleForm" method="POST">
                <div class="schedule-grid">
                    <?php
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $time_slots = [
                        '09:00:00' => '09:00 AM',
                        '09:30:00' => '09:30 AM',
                        '10:00:00' => '10:00 AM',
                        '10:30:00' => '10:30 AM',
                        '11:00:00' => '11:00 AM',
                        '11:30:00' => '11:30 AM',
                        '12:00:00' => '12:00 PM',
                        '12:30:00' => '12:30 PM',
                        '14:00:00' => '02:00 PM',
                        '14:30:00' => '02:30 PM',
                        '15:00:00' => '03:00 PM',
                        '15:30:00' => '03:30 PM',
                        '16:00:00' => '04:00 PM',
                        '16:30:00' => '04:30 PM',
                        '17:00:00' => '05:00 PM'
                    ];

                    // Parse existing schedule
                    $existing_schedule = [];
                    if (!empty($doctor['schedule'])) {
                        $schedule_lines = explode("\n", $doctor['schedule']);
                        foreach ($schedule_lines as $line) {
                            if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
                                $day = trim($matches[1]);
                                $times = array_map('trim', explode(',', $matches[2]));
                                $existing_schedule[$day] = $times;
                            }
                        }
                    }

                    foreach ($days as $day): ?>
                        <div class="schedule-row">
                            <div class="day-label">
                                <input type="checkbox" id="day_<?php echo $day; ?>" 
                                       name="schedule[<?php echo $day; ?>][enabled]" 
                                       class="day-checkbox"
                                       <?php echo isset($existing_schedule[$day]) ? 'checked' : ''; ?>>
                                <label for="day_<?php echo $day; ?>"><?php echo $day; ?></label>
                            </div>
                            <div class="time-slots">
                                <?php foreach ($time_slots as $value => $label): ?>
                                    <div class="time-slot">
                                        <input type="checkbox" 
                                               id="time_<?php echo $day; ?>_<?php echo $value; ?>" 
                                               name="schedule[<?php echo $day; ?>][times][]" 
                                               value="<?php echo $value; ?>"
                                               class="time-checkbox"
                                               <?php echo isset($existing_schedule[$day]) && in_array($value, $existing_schedule[$day]) ? 'checked' : ''; ?>
                                               <?php echo !isset($existing_schedule[$day]) ? 'disabled' : ''; ?>>
                                        <label for="time_<?php echo $day; ?>_<?php echo $value; ?>"><?php echo $label; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="save-btn">
                    <i class="fas fa-save"></i>
                    Save Schedule
                </button>
            </form>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Handle day checkbox changes
        document.querySelectorAll('.day-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const day = this.id.split('_')[1];
                const timeCheckboxes = document.querySelectorAll(`input[name="schedule[${day}][times][]"]`);
                timeCheckboxes.forEach(timeCheckbox => {
                    timeCheckbox.disabled = !this.checked;
                });
            });
        });

        // Handle form submission
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Collect schedule data
            const schedule = {};
            document.querySelectorAll('.day-checkbox').forEach(dayCheckbox => {
                const day = dayCheckbox.id.split('_')[1];
                if (dayCheckbox.checked) {
                    const selectedTimes = Array.from(document.querySelectorAll(`input[name="schedule[${day}][times][]"]:checked`))
                        .map(checkbox => checkbox.value);
                    if (selectedTimes.length > 0) {
                        schedule[day] = selectedTimes;
                    }
                }
            });
            
            // Create hidden input for schedule
            const scheduleInput = document.createElement('input');
            scheduleInput.type = 'hidden';
            scheduleInput.name = 'schedule';
            scheduleInput.value = JSON.stringify(schedule);
            this.appendChild(scheduleInput);
            
            // Submit the form
            this.submit();
        });
    </script>
</body>
</html> 