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

// Fetch doctor's schedule from doctor_schedule table
$current_schedule_query = "SELECT day, from_time, to_time, max_patients FROM doctor_schedule WHERE doctor_id = ? ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), from_time";
$current_schedule_stmt = $conn->prepare($current_schedule_query);
$current_schedule_stmt->bind_param("s", $doctor_id);
$current_schedule_stmt->execute();
$current_schedule_result = $current_schedule_stmt->get_result();

$doctor_schedule_data = [];
while ($row = $current_schedule_result->fetch_assoc()) {
    $doctor_schedule_data[$row['day']][] = [
        'from' => $row['from_time'],
        'to' => $row['to_time'],
        'capacity' => $row['max_patients']
    ];
}

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
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .schedule-grid .schedule-header-row {
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
            flex-direction: column;
            gap: 10px;
        }

        .time-slot-inputs {
             display: flex;
             flex-direction: column;
             gap: 10px;
             /* margin-bottom: 10px; Remove this if you want slots right below each other */
        }

        .time-slot-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .time-input {
            width: 120px;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 1rem;
        }

        .capacity-input {
            width: 100px;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 1rem;
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
            margin-top: 10px; /* Add some space above the add slot button */
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

            <form method="POST" action="update_doctor_schedule.php">
                <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($doctor_id); ?>">
                
                <div class="form-group">
                    <label>Edit Schedule</label>
                    <div class="schedule-grid">
                        <?php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        ?>
                        <div class="schedule-header-row">
                            <div class="day-header">Day</div>
                            <div class="time-header">Time Slots</div>
                        </div>
                        <?php foreach ($days as $day): ?>
                            <div class="schedule-row">
                                <div class="day-label">
                                    <input type="checkbox" id="day_<?php echo $day; ?>" name="schedule[<?php echo $day; ?>][enabled]" class="day-checkbox" <?php echo isset($doctor_schedule_data[$day]) ? 'checked' : ''; ?>>
                                    <label for="day_<?php echo $day; ?>"><?php echo $day; ?></label>
                                </div>
                                <div class="time-slots" id="time-slots-<?php echo $day; ?>">
                                    <div class="time-slot-inputs">
                                        <?php if (isset($doctor_schedule_data[$day])): ?>
                                            <?php foreach ($doctor_schedule_data[$day] as $slot): ?>
                                                <div class="time-slot-row">
                                                    <input type="time" class="form-control time-input" name="schedule[<?php echo $day; ?>][slots][][from]" value="<?php echo htmlspecialchars($slot['from']); ?>" <?php echo isset($doctor_schedule_data[$day]) ? '' : 'disabled'; ?> required>
                                                    <span>to</span>
                                                    <input type="time" class="form-control time-input" name="schedule[<?php echo $day; ?>][slots][][to]" value="<?php echo htmlspecialchars($slot['to']); ?>" <?php echo isset($doctor_schedule_data[$day]) ? '' : 'disabled'; ?> required>
                                                    <input type="number" class="form-control capacity-input" name="schedule[<?php echo $day; ?>][slots][][capacity]" placeholder="Max Patients" value="<?php echo htmlspecialchars($slot['capacity']); ?>" min="1" <?php echo isset($doctor_schedule_data[$day]) ? '' : 'disabled'; ?> required>
                                                    <button type="button" class="btn btn-danger remove-slot" <?php echo isset($doctor_schedule_data[$day]) ? '' : 'disabled'; ?>><i class="fas fa-times"></i></button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                             <div class="time-slot-row">
                                                <input type="time" class="form-control time-input" disabled>
                                                <span>to</span>
                                                <input type="time" class="form-control time-input" disabled>
                                                <input type="number" class="form-control capacity-input" placeholder="Max Patients" min="1" disabled>
                                                <button type="button" class="btn btn-danger remove-slot" disabled>
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn btn-secondary add-slot" <?php echo isset($doctor_schedule_data[$day]) ? '' : 'disabled'; ?>>
                                        <i class="fas fa-plus"></i> Add Time Slot
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="save-btn"><i class="fas fa-save"></i> Save Schedule</button>
            </form>
            <div id="validation-errors" style="color: red; margin-top: 10px;"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle day checkbox changes
            document.querySelectorAll('.day-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const day = this.id.split('_')[1];
                    const timeSlotsContainer = document.getElementById(`time-slots-${day}`);
                    const inputs = timeSlotsContainer.querySelectorAll('input, button');
                    inputs.forEach(input => {
                        input.disabled = !this.checked;
                        // If enabling, set required attribute
                        if (this.checked && (input.type === 'time' || input.type === 'number')) {
                             input.setAttribute('required', 'required');
                        } else {
                             input.removeAttribute('required');
                        }
                    });
                    // If enabling a day and no slots are present (except the initial empty one), add a slot
                    const existingSlotRows = timeSlotsContainer.querySelectorAll('.time-slot-row');
                    if (this.checked && existingSlotRows.length === 1) {
                         const firstSlotInputs = existingSlotRows[0].querySelectorAll('input[type="time"], input[type="number"]');
                         // Check if the existing slot is the initial empty one (all inputs disabled/empty)
                         let isEmptySlot = true;
                         firstSlotInputs.forEach(input => {
                             if (!input.disabled || input.value !== '') {
                                 isEmptySlot = false;
                             }
                         });
                         
                         if (isEmptySlot) {
                             existingSlotRows[0].remove(); // Remove the initial empty slot
                             addTimeSlot(day); // Add a new functional slot
                         } else {
                              // If the existing slot is not empty (i.e., it's pre-filled data), just enable inputs
                              existingSlotRows.forEach(row => {
                                  row.querySelectorAll('input, button').forEach(input => {
                                      input.disabled = false;
                                       if (input.type === 'time' || input.type === 'number') {
                                            input.setAttribute('required', 'required');
                                       }
                                  });
                              });
                         }
                    } else if (this.checked && existingSlotRows.length === 0) {
                         // Should not happen with the initial row, but as a fallback
                         addTimeSlot(day);
                    }
                });
            });

             // Function to add a time slot dynamically
            function addTimeSlot(day) {
                const timeSlotsContainer = document.getElementById(`time-slots-${day}`).querySelector('.time-slot-inputs');
                const newSlot = document.createElement('div');
                newSlot.className = 'time-slot-row';
                newSlot.innerHTML = `
                    <input type="time" class="form-control time-input" name="schedule[${day}][slots][][from]" required>
                    <span>to</span>
                    <input type="time" class="form-control time-input" name="schedule[${day}][slots][][to]" required>
                    <input type="number" class="form-control capacity-input" name="schedule[${day}][slots][][capacity]" placeholder="Max Patients" min="1" required>
                    <button type="button" class="btn btn-danger remove-slot"><i class="fas fa-times"></i></button>
                `;
                timeSlotsContainer.appendChild(newSlot);

                // Add remove functionality to the new slot
                newSlot.querySelector('.remove-slot').addEventListener('click', function() {
                    newSlot.remove();
                });
            }

            // Add time slot button listener
             document.querySelectorAll('.add-slot').forEach(button => {
                 button.addEventListener('click', function() {
                    const day = this.closest('.schedule-row').querySelector('.day-checkbox').id.split('_')[1];
                    addTimeSlot(day);
                 });
             });

            // Add remove functionality to existing slots on load
             document.querySelectorAll('.remove-slot').forEach(button => {
                 button.addEventListener('click', function() {
                    button.closest('.time-slot-row').remove();
                 });
             });

            // Handle form submission
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                const validationErrorsDiv = document.getElementById('validation-errors');
                validationErrorsDiv.innerHTML = '';

                // Collect schedule data
                const schedule = {};
                let hasErrors = false;
                // Iterate over checked day checkboxes
                document.querySelectorAll('.day-checkbox:checked').forEach(dayCheckbox => {
                    const day = dayCheckbox.id.split('_')[1];
                    const slots = [];
                    const timeSlotInputsContainer = document.getElementById(`time-slots-${day}`).querySelector('.time-slot-inputs');
                    const timeSlotRows = timeSlotInputsContainer.querySelectorAll('.time-slot-row');

                    // Check if any slots are added for this enabled day
                    if (timeSlotRows.length === 0) {
                        validationErrorsDiv.innerHTML += `<p>Please add at least one time slot for ${day}</p>`;
                        hasErrors = true;
                        // Continue to the next day to collect all errors
                        return; 
                    }

                    timeSlotRows.forEach(slotRow => {
                        const fromInput = slotRow.querySelector('input[type="time"]');
                        const toInput = slotRow.querySelectorAll('input[type="time"]')[1]; // Select the second time input
                        const capacityInput = slotRow.querySelector('input[type="number"]');

                        // Basic validation for each slot
                        console.log(`Day: ${day}, Slot: From=${fromInput ? fromInput.value : 'null'}, To=${toInput ? toInput.value : 'null'}, Capacity=${capacityInput ? capacityInput.value : 'null'}`);
                        if (!fromInput || !toInput || !capacityInput || !fromInput.value || !toInput.value || !capacityInput.value || parseInt(capacityInput.value) < 1) {
                            validationErrorsDiv.innerHTML += `<p>Please fill in all time and capacity fields correctly for ${day}</p>`;
                            hasErrors = true;
                            // Continue to the next slot to collect all errors for this day
                            return; 
                        }

                        slots.push({
                            from: fromInput.value,
                            to: toInput.value,
                            capacity: parseInt(capacityInput.value)
                        });
                    });

                    // Only add the day to the schedule if there are slots collected and no errors for this day
                    if (slots.length > 0 && !hasErrors) {
                         schedule[day] = slots;
                    }
                });

                // Validate if at least one day has slots in total for submission
                if (Object.keys(schedule).length === 0 && !hasErrors && document.querySelectorAll('.day-checkbox:checked').length > 0) {
                     // This case should ideally be caught by the per-day check, but as a fallback:
                      validationErrorsDiv.innerHTML += `<p>No valid time slots added for any selected day.</p>`;
                      hasErrors = true;
                } else if (document.querySelectorAll('.day-checkbox:checked').length === 0) {
                     validationErrorsDiv.innerHTML += `<p>Please select at least one day.</p>`;
                     hasErrors = true;
                }

                 // Prevent submission if there are errors
                if (hasErrors) {
                    e.preventDefault(); // Prevent form submission if errors occurred
                    return; // Stop the function execution
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

                // Submit the form if validation passes
                if (!e.defaultPrevented) {
                    form.submit();
                }
            });

             // Initial state setup based on fetched data
            document.querySelectorAll('.day-checkbox').forEach(checkbox => {
                 const day = checkbox.id.split('_')[1];
                 const timeSlotsContainer = document.getElementById(`time-slots-${day}`);
                 const isDayEnabled = checkbox.checked;

                 // Enable/disable inputs based on initial checked state
                 const inputs = timeSlotsContainer.querySelectorAll('input, button');
                 inputs.forEach(input => {
                      input.disabled = !isDayEnabled;
                       if (isDayEnabled && (input.type === 'time' || input.type === 'number')) {
                             input.setAttribute('required', 'required');
                        } else {
                             input.removeAttribute('required');
                        }
                 });

                 // If the day is checked and has no actual schedule data (only the initial empty row exists),
                 // remove the empty row and add a new functional one on load.
                 const existingSlotRows = timeSlotsContainer.querySelectorAll('.time-slot-row');
                  if (isDayEnabled && existingSlotRows.length === 1) {
                       const firstSlotInputs = existingSlotRows[0].querySelectorAll('input[type="time"], input[type="number"]');
                       let isEmptySlot = true;
                        firstSlotInputs.forEach(input => {
                            if (!input.disabled || input.value !== '') {
                                isEmptySlot = false;
                            }
                        });

                       if (isEmptySlot) {
                           existingSlotRows[0].remove();
                           addTimeSlot(day);
                       }
                  } else if (isDayEnabled && existingSlotRows.length === 0) {
                      // Fallback for an unlikely scenario
                      addTimeSlot(day);
                  }

             });
        });
    </script>
</body>
</html> 