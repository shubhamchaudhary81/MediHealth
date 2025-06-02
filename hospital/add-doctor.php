<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
include ('sidebar.php');

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

// Fetch hospital information based on admin ID
$hospital_query = "SELECT h.* FROM hospital h 
                  JOIN hospitaladmin ha ON h.id = ha.hospitalid 
                  WHERE ha.adminid = ?";

$stmt = $conn->prepare($hospital_query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $hospital = $result->fetch_assoc();
    $hospital_id = $hospital['id']; // Store hospital ID for queries
} else {
    // If no hospital found, redirect to login
    header("Location: hospitaladminlogin.php");
    exit();
}

// Fetch departments for dropdown
$departments_query = "SELECT * FROM department ORDER BY department_name";
$departments_result = $conn->query($departments_query);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $nmc_number = $_POST['nmc_number'];
    $department_id = $_POST['department'];
    $specialization = $_POST['specialization'];
    $qualification = $_POST['qualification'];
    $experience = $_POST['experience'];
    $is_specialist = isset($_POST['is_specialist']) ? 1 : 0;
    
    // Process schedule data
    $schedule_data = json_decode($_POST['schedule'], true);
    $formatted_schedule = [];
    
    foreach ($schedule_data as $day => $slots) {
        if (!empty($slots)) {
            $day_slots = [];
            foreach ($slots as $slot) {
                $day_slots[] = $slot['from'] . ' - ' . $slot['to'] . ' (Max Patients: ' . $slot['capacity'] . ')';
            }
            $formatted_schedule[] = $day . ': ' . implode(', ', $day_slots);
        }
    }
    
    $schedule = implode("\n", $formatted_schedule);

    // Get the last doctor ID from the database
    $last_id_query = "SELECT doctor_id FROM doctor ORDER BY doctor_id DESC LIMIT 1";
    $last_id_result = $conn->query($last_id_query);
    
    if ($last_id_result->num_rows > 0) {
        $last_row = $last_id_result->fetch_assoc();
        $last_id = $last_row['doctor_id'];
        // Extract the numeric part and increment
        $numeric_part = intval(substr($last_id, 3)) + 1;
    } else {
        // If no doctors exist, start with 1
        $numeric_part = 1;
    }
    
    // Format the new doctor ID with DOC prefix and 4-digit number
    $doctor_id = 'DOC' . str_pad($numeric_part, 4, '0', STR_PAD_LEFT);

    // Generate a random password (8 characters)
    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
   
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert doctor into database
        $insert_query = "INSERT INTO doctor (doctor_id, hospitalid, department_id, name, email, phone, nmc_number, specialization, qualification, experience, password, schedule, is_specialist) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("siisssssssssi", $doctor_id, $hospital_id, $department_id, $name,  $email, $phone, $nmc_number, $specialization, $qualification, $experience, $hashed_password, $schedule, $is_specialist);

        if ($stmt->execute()) {
            // Insert schedule into doctor_schedule table
            foreach ($schedule_data as $day => $slots) {
                if (!empty($slots)) {
                    foreach ($slots as $slot) {
                        $schedule_insert_query = "INSERT INTO doctor_schedule (doctor_id, day, from_time, to_time, max_patients) VALUES (?, ?, ?, ?, ?)";
                        $schedule_stmt = $conn->prepare($schedule_insert_query);
                        $schedule_stmt->bind_param("ssssi", $doctor_id, $day, $slot['from'], $slot['to'], $slot['capacity']);
                        $schedule_stmt->execute();
                    }
                }
            }
            
            // Send email to doctor with PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'medihealth628@gmail.com';
                $mail->Password = 'esme zlrl slig ujcm';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('medihealth628@gmail.com', 'MediHealth');
                $mail->addAddress($email, $name);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = '🎉 Welcome to MediHealth – Your Doctor Account Created!';

                // Format schedule for email
                $schedule_html = '';
                foreach ($formatted_schedule as $day_schedule) {
                    $schedule_html .= "<p><strong>" . htmlspecialchars($day_schedule) . "</strong></p>";
                }

                $mail->Body = "
                    <div style='text-align: center; font-family: Arial, sans-serif;'>
                        <h2 style='color: #4f6df5;'>Welcome, Dr. $name!</h2>
                        <p style='font-size: 16px; color: #555;'>We are pleased to have you join our MediHealth family.</p>

                        <div style='background-color: #f2f2f2; padding: 15px; border-radius: 10px; display: inline-block; margin-top:20px;'>
                            <p><strong>Doctor ID:</strong> <span style='color: #4f6df5;'>$doctor_id</span></p>
                            <p><strong>Password:</strong> <span style='color: #4f6df5;'>$password</span></p>
                            <div style='margin-top: 10px;'>
                                <p><strong>Your Schedule:</strong></p>
                                $schedule_html
                            </div>
                        </div>

                        <p style='margin-top: 20px; color: #777;'>You can log in using the button below:</p>

                        <a href='http://localhost/MediHealth/doctor/doctorlogin.php' style='display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Login Here</a>

                        <p style='margin-top: 30px; color: #999;'>Please change your password after your first login for security.</p>
                        <p><em>Best wishes,<br>MediHealth Team</em></p>
                    </div>
                ";

                $mail->send();
                
                // Commit transaction
                $conn->commit();
                
                // Redirect with success
                $success_message = "Doctor added successfully! Login credentials have been sent to their email.";
                header("Location: doctors.php?success=" . urlencode($success_message));
                exit();
            } catch (Exception $e) {
                // If email fails, rollback transaction
                $conn->rollback();
                $error_message = "Error adding doctor: " . $e->getMessage();
                header("Location: doctors.php?error=" . urlencode($error_message));
                exit();
            }
        } else {
            // If doctor insertion fails, rollback transaction
            $conn->rollback();
            $error_message = "Error adding doctor: " . $conn->error;
            header("Location: doctors.php?error=" . urlencode($error_message));
            exit();
        }
    } catch (Exception $e) {
        // If any error occurs, rollback transaction
        $conn->rollback();
        $error_message = "Error: " . $e->getMessage();
        header("Location: doctors.php?error=" . urlencode($error_message));
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Doctor - MediHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <style>
        /* Your existing CSS styles */
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-title {
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary-color), #4f6df5);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 109, 245, 0.2);
        }

        .error-message {
            color: #ef4444;
            margin-bottom: 20px;
            padding: 10px;
            background: #fee2e2;
            border-radius: 8px;
        }

        .schedule-grid {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .schedule-header {
            display: grid;
            grid-template-columns: 150px 1fr;
            background-color: #f8fafc;
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
        }

        .schedule-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .schedule-row:last-child {
            border-bottom: none;
        }
        
        .day-label {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .time-slot {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .day-checkbox, .time-checkbox {
            margin: 0;
        }

        .time-checkbox:disabled + label {
            color: #94a3b8;
            cursor: not-allowed;
        }

        /* Add these new styles */
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-label span {
            font-size: 15px;
            color: var(--text-color);
        }

        .time-slot-inputs {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 10px;
        }

        .time-slot-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .time-input {
            width: 120px;
        }

        .capacity-input {
            width: 100px;
        }

        .remove-slot {
            padding: 8px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .add-slot {
            padding: 8px 16px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="main-content">
        <?php include('header.php'); ?>
        
    <div class="form-container">
            <h2 class="form-title">Add New Doctor</h2>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="nmc_number">NMC Number</label>
                        <input type="number" id="nmc_number" name="nmc_number" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="number" id="phone" name="phone" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php while ($dept = $departments_result->fetch_assoc()): ?>
                                <option value="<?php echo $dept['department_id']; ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" id="specialization" name="specialization" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="qualification">Qualification</label>
                        <input type="text" id="qualification" name="qualification" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="experience">Years of Experience</label>
                    <input type="number" id="experience" name="experience" class="form-control" min="0" required>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="is_specialist" name="is_specialist">
                        <span>Is Specialist</span>
                    </label>
                </div>

                <div class="form-group">
                    <label>Schedule</label>
                    <div class="schedule-grid">
                        <?php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        ?>
                        <div class="schedule-header">
                            <div class="day-header">Day</div>
                            <div class="time-header">Time Slots</div>
                        </div>
                        <?php foreach ($days as $day): ?>
                            <div class="schedule-row">
                                <div class="day-label">
                                    <input type="checkbox" id="day_<?php echo $day; ?>" name="schedule[<?php echo $day; ?>][enabled]" class="day-checkbox">
                                    <label for="day_<?php echo $day; ?>"><?php echo $day; ?></label>
                                </div>
                                <div class="time-slots" id="time-slots-<?php echo $day; ?>">
                                    <div class="time-slot-inputs">
                                        <div class="time-slot-row">
                                            <input type="time" class="form-control time-input" disabled>
                                            <span>to</span>
                                            <input type="time" class="form-control time-input" disabled>
                                            <input type="number" class="form-control capacity-input" placeholder="Max Patients" min="1" disabled>
                                            <button type="button" class="btn btn-danger remove-slot" disabled>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-secondary add-slot" disabled>
                                        <i class="fas fa-plus"></i> Add Time Slot
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Add Doctor</button>
        </form>
    </div>
</div>

<script>
    // Toggle sidebar on mobile
    document.querySelector('.menu-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Handle day checkbox changes
        document.querySelectorAll('.day-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const day = this.id.split('_')[1];
                const timeSlotsContainer = document.getElementById(`time-slots-${day}`);
                const inputs = timeSlotsContainer.querySelectorAll('input, button');
                inputs.forEach(input => {
                    input.disabled = !this.checked;
                });
            });
        });

        // Add time slot
        document.querySelectorAll('.add-slot').forEach(button => {
            button.addEventListener('click', function() {
                const day = this.closest('.schedule-row').querySelector('.day-checkbox').id.split('_')[1];
                const timeSlotsContainer = this.previousElementSibling;
                const newSlot = document.createElement('div');
                newSlot.className = 'time-slot-row';
                newSlot.innerHTML = `
                    <input type="time" class="form-control time-input" name="schedule[${day}][slots][][from]" required>
                    <span>to</span>
                    <input type="time" class="form-control time-input" name="schedule[${day}][slots][][to]" required>
                    <input type="number" class="form-control capacity-input" name="schedule[${day}][slots][][capacity]" placeholder="Max Patients" min="1" required>
                    <button type="button" class="btn btn-danger remove-slot">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                timeSlotsContainer.appendChild(newSlot);

                // Add remove functionality
                newSlot.querySelector('.remove-slot').addEventListener('click', function() {
                    newSlot.remove();
                });
            });
        });

        // Handle form submission
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Collect schedule data
            const schedule = {};
            document.querySelectorAll('.day-checkbox').forEach(dayCheckbox => {
                const day = dayCheckbox.id.split('_')[1];
                if (dayCheckbox.checked) {
                    const slots = [];
                    const timeSlots = dayCheckbox.closest('.schedule-row').querySelectorAll('.time-slot-row');
                    timeSlots.forEach(slot => {
                        const fromTime = slot.querySelector('input[type="time"]:first-child').value;
                        const toTime = slot.querySelector('input[type="time"]:nth-child(3)').value;
                        const capacity = slot.querySelector('input[type="number"]').value;
                        
                        if (fromTime && toTime && capacity) {
                            slots.push({
                                from: fromTime,
                                to: toTime,
                                capacity: parseInt(capacity)
                            });
                        }
                    });
                    
                    if (slots.length > 0) {
                        schedule[day] = slots;
                    }
                }
            });
            
            // Validate if at least one day has slots
            if (Object.keys(schedule).length === 0) {
                alert('Please add at least one time slot for any day');
                return;
            }
            
            // Create hidden input for schedule
            let scheduleInput = document.querySelector('input[name="schedule"]');
            if (!scheduleInput) {
                scheduleInput = document.createElement('input');
                scheduleInput.type = 'hidden';
                scheduleInput.name = 'schedule';
                form.appendChild(scheduleInput);
            }
            scheduleInput.value = JSON.stringify(schedule);
            
            // Submit the form
            form.submit();
        });
    });
</script>
</body>
</html>
