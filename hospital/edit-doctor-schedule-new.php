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

// Check if doctor_id is provided in GET or POST
$doctor_id = null;
if (isset($_GET['doctor_id'])) {
    $doctor_id = $_GET['doctor_id'];
} elseif (isset($_POST['doctor_id'])) {
    $doctor_id = $_POST['doctor_id'];
}

if (!$doctor_id) {
    // Redirect to doctors list if no doctor_id is provided
    header("Location: doctors.php");
    exit();
}

// Verify that the doctor belongs to the admin's hospital
$verify_query = "SELECT d.doctor_id FROM doctor d 
                JOIN hospitaladmin ha ON d.hospitalid = ha.hospitalid 
                WHERE d.doctor_id = ? AND ha.adminid = ?";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("si", $doctor_id, $admin_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    // Redirect if doctor not found or not associated with the admin's hospital
    header("Location: doctors.php");
    exit();
}

// Fetch doctor information
$doctor_query = "SELECT d.*, dept.department_name, h.name as hospital_name 
                FROM doctor d
                JOIN department dept ON d.department_id = dept.department_id
                JOIN hospital h ON d.hospitalid = h.id
                WHERE d.doctor_id = ?";

$stmt = $conn->prepare($doctor_query);
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
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
        'from' => (new DateTime($row['from_time']))->format('H:i'), // Format to HH:MM for time input
        'to' => (new DateTime($row['to_time']))->format('H:i'), // Format to HH:MM for time input
        'capacity' => $row['max_patients']
    ];
}

// Handle form submission for schedule update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule'])) {
    // Let's copy and adapt the logic here:
    $conn->begin_transaction();
    try {
        // Delete existing schedules for this doctor
        $delete_query = "DELETE FROM doctor_schedule WHERE doctor_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("s", $doctor_id);
        $delete_stmt->execute();

        // Process schedule data (new format)
        // Read the JSON string from the hidden input field
        $schedule_json_string = isset($_POST['schedule_json_data']) ? $_POST['schedule_json_data'] : '';

        // Log the received JSON string for debugging
        error_log("Received JSON string for doctor " . $doctor_id . ": " . $schedule_json_string);

        $schedule_data = json_decode($schedule_json_string, true);

        // Check if JSON decoding was successful and data is in the expected format
        if ($schedule_data === null || !is_array($schedule_data)) {
             throw new Exception("Invalid schedule data received or JSON decoding failed.");
        }

        $formatted_schedule = [];

        foreach ($schedule_data as $day => $slots) {
            if (!empty($slots) && is_array($slots)) {
                $day_slots_formatted = [];
                foreach ($slots as $slot) {
                    // Check if slot data is in the expected format
                    if (!isset($slot['from'], $slot['to'], $slot['capacity']) || !is_numeric($slot['capacity'])) {
                         throw new Exception("Invalid time slot data format.");
                    }

                    // Ensure times are in HH:MM:SS format for database
                    $from_time = $slot['from'];
                    $to_time = $slot['to'];
                    if (strlen($from_time) === 5) $from_time .= ':00';
                    if (strlen($to_time) === 5) $to_time .= ':00';

                    // Insert into doctor_schedule table
                    $schedule_insert_query = "INSERT INTO doctor_schedule (doctor_id, day, from_time, to_time, max_patients) VALUES (?, ?, ?, ?, ?)";
                    $schedule_stmt = $conn->prepare($schedule_insert_query);
                    $schedule_stmt->bind_param("ssssi", $doctor_id, $day, $from_time, $to_time, $slot['capacity']);

                    if (!$schedule_stmt->execute()) {
                         throw new Exception("Database error during schedule insertion: " . $conn->error);
                    }

                    // Format for the doctor table schedule column
                    $day_slots_formatted[] = $slot['from'] . ' - ' . $slot['to'] . ' (Max Patients: ' . $slot['capacity'] . ')';
                }
                 $formatted_schedule[] = $day . ': ' . implode(', ', $day_slots_formatted);
            }
        }

        // Update the schedule in the doctor table
        $schedule_string = implode("\n", $formatted_schedule);
        $update_query = "UPDATE doctor SET schedule = ? WHERE doctor_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $schedule_string, $doctor_id);

        if (!$update_stmt->execute()) {
             throw new Exception("Database error during doctor schedule string update: " . $conn->error);
        }

        // Commit transaction
        $conn->commit();

        // Redirect with success message
        $success_message = "Schedule updated successfully!";
        header("Location: edit-doctor-schedule-new.php?doctor_id=" . $doctor_id . "&success=" . urlencode($success_message));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error updating schedule: " . $e->getMessage();
         header("Location: edit-doctor-schedule-new.php?doctor_id=" . $doctor_id . "&error=" . urlencode($error_message));
         exit();
    }
}

// Check for success or error messages in URL parameters
$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';


include('sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor Schedule - MediHealth</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Basic styles for layout and form elements */
        .container {
            max-width: 100%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        h2 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .doctor-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }

        .doctor-info p {
            margin: 5px 0;
            color: #555;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .schedule-grid {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .schedule-header-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            background-color: #f2f2f2;
            padding: 10px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }

        .schedule-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            padding: 10px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .schedule-row:last-child {
            border-bottom: none;
        }

        .day-label {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .time-slots-container {
             /* This is the container for time-slot-inputs and add-slot button */
        }

        .time-slot-inputs {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 10px; /* Space between slots and add button */
        }

        .time-slot-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .time-input {
            width: 100px; /* Adjust width as needed */
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .capacity-input {
            width: 80px; /* Adjust width as needed */
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .remove-slot {
            padding: 8px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }

        .add-slot {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }

        .save-btn {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 20px;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

         /* Responsive adjustments */
        @media (max-width: 600px) {
            .schedule-header-row, .schedule-row {
                grid-template-columns: 1fr; /* Stack day and slots on small screens */
            }
            .day-label {
                 margin-bottom: 10px;
            }
            .time-slot-row {
                 flex-direction: column; /* Stack time inputs and capacity vertically */
                 align-items: flex-start;
            }
            .time-input, .capacity-input {
                 width: 100%;
            }
        }

    </style>
</head>
<body>
    <div class="main-content">
        <?php include('header.php'); ?>

        <div class="container">
            <h2>Edit Doctor Schedule</h2>

             <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="doctor-info">
                <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($doctor['name']); ?></p>
                <p><strong>Specialization:</strong> <?php echo htmlspecialchars($doctor['specialization']); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($doctor['department_name']); ?></p>
            </div>

            <form id="scheduleForm" method="POST" action="edit-doctor-schedule-new.php">
                 <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($doctor_id); ?>">
                 <input type="hidden" name="schedule_json_data" id="schedule-data">

                <div class="form-group">
                    <label>Schedule</label>
                    <div class="schedule-grid">
                        <div class="schedule-header-row">
                            <div class="day-header">Day</div>
                            <div class="time-header">Time Slots</div>
                        </div>
                        <?php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        foreach ($days as $day):
                        ?>
                        <div class="schedule-row">
                            <div class="day-label">
                                <input type="checkbox" id="day_<?php echo $day; ?>" class="day-checkbox" <?php echo isset($doctor_schedule_data[$day]) ? 'checked' : ''; ?>>
                                <label for="day_<?php echo $day; ?>"><?php echo $day; ?></label>
                            </div>
                            <div class="time-slots-container">
                                <div class="time-slot-inputs" id="time-slots-<?php echo $day; ?>">
                                    <?php if (isset($doctor_schedule_data[$day])): ?>
                                        <?php foreach ($doctor_schedule_data[$day] as $slot): ?>
                                            <div class="time-slot-row">
                                                <input type="time" class="form-control time-input" value="<?php echo htmlspecialchars($slot['from']); ?>" <?php echo isset($doctor_schedule_data[$day]) ? '' : 'disabled'; ?> required>
                                                <span>to</span>
                                                <input type="time" class="form-control time-input" value="<?php echo htmlspecialchars($slot['to']); ?>" <?php echo isset($doctor_schedule_data[$day]) ? '' : 'disabled'; ?> required>
                                                <input type="number" class="form-control capacity-input" placeholder="Max Patients" value="<?php echo htmlspecialchars($slot['capacity']); ?>" min="1" <?php echo isset($doctor_schedule_data[$day]) ? '' : 'disabled'; ?> required>
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
            // Function to enable/disable inputs in a time slot row
            function toggleSlotInputs(slotRow, enable) {
                slotRow.querySelectorAll('input, button').forEach(input => {
                    input.disabled = !enable;
                    if (enable && (input.type === 'time' || input.type === 'number')) {
                        input.setAttribute('required', 'required');
                    } else {
                        input.removeAttribute('required');
                    }
                });
            }

            // Function to add a time slot dynamically
            function addTimeSlot(day) {
                const timeSlotsContainer = document.getElementById(`time-slots-${day}`);
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
                     // If the last slot is removed from an enabled day, re-add an empty one to maintain structure/validation
                     const dayCheckbox = document.getElementById(`day_${day}`);
                     if(dayCheckbox.checked && timeSlotsContainer.querySelectorAll('.time-slot-row').length === 0) {
                         addEmptySlot(day);
                     }
                });
            }

             // Function to add an empty, disabled time slot (for unchecked days)
            function addEmptySlot(day) {
                 const timeSlotsContainer = document.getElementById(`time-slots-${day}`);
                 const emptySlot = document.createElement('div');
                 emptySlot.className = 'time-slot-row';
                 emptySlot.innerHTML = `
                     <input type="time" class="form-control time-input" disabled>
                     <span>to</span>
                     <input type="time" class="form-control time-input" disabled>
                     <input type="number" class="form-control capacity-input" placeholder="Max Patients" min="1" disabled>
                     <button type="button" class="btn btn-danger remove-slot" disabled>
                         <i class="fas fa-times"></i>
                     </button>
                 `;
                 timeSlotsContainer.appendChild(emptySlot);

                 // Add remove functionality to the empty slot (it can still be removed if day is checked and re-unchecked)
                  emptySlot.querySelector('.remove-slot').addEventListener('click', function() {
                     emptySlot.remove();
                  });
            }

            // Handle day checkbox changes
            document.querySelectorAll('.day-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const day = this.id.split('_')[1];
                    const timeSlotsContainer = document.getElementById(`time-slots-${day}`);
                    const addSlotButton = this.closest('.schedule-row').querySelector('.add-slot');

                    // Enable/disable add slot button
                    addSlotButton.disabled = !this.checked;

                    // Handle existing time slot rows
                    const existingSlotRows = timeSlotsContainer.querySelectorAll('.time-slot-row');

                    if (this.checked) {
                        // Day is checked
                         if (existingSlotRows.length === 1) {
                             const firstSlotInputs = existingSlotRows[0].querySelectorAll('input[type="time"], input[type="number"]');
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
                                  // If existing slots have data, enable their inputs
                                   existingSlotRows.forEach(row => toggleSlotInputs(row, true));
                             }
                         } else if (existingSlotRows.length === 0) {
                              // Should not happen with the initial empty row logic, but as fallback
                              addTimeSlot(day);
                         } else {
                              // Day was checked, just ensure inputs are enabled
                              existingSlotRows.forEach(row => toggleSlotInputs(row, true));
                         }
                    } else {
                        // Day is unchecked
                        // Disable inputs in all time slot rows for this day
                        existingSlotRows.forEach(row => toggleSlotInputs(row, false));
                        // If all slots are removed when unchecked, add an empty one back
                        if(timeSlotsContainer.querySelectorAll('.time-slot-row').length === 0) {
                            addEmptySlot(day);
                        }
                    }
                });
            });

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
                     // If the last slot is removed from an enabled day, re-add an empty one to maintain structure/validation
                     const dayCheckbox = document.getElementById(`day_${day}`);
                     const timeSlotsContainer = document.getElementById(`time-slots-${day}`);
                     if(dayCheckbox.checked && timeSlotsContainer.querySelectorAll('.time-slot-row').length === 0) {
                         addEmptySlot(day);
                     }
                 });
             });

            // Handle form submission
            const form = document.getElementById('scheduleForm');
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
                    const timeSlotsContainer = document.getElementById(`time-slots-${day}`);
                    const timeSlotRows = timeSlotsContainer.querySelectorAll('.time-slot-row');

                    // Check if any slots are added for this enabled day
                    if (timeSlotRows.length === 0 || (timeSlotRows.length === 1 && timeSlotsContainer.querySelector('.time-slot-row input:not(:disabled)') === null)) {
                         validationErrorsDiv.innerHTML += `<p>Please add at least one time slot for ${day}</p>`;
                         hasErrors = true;
                         return; // Skip to next day
                    }

                    timeSlotRows.forEach(slotRow => {
                         const fromInput = slotRow.querySelector('input[type="time"]');
                         const toInput = slotRow.querySelectorAll('input[type="time"]')[1];
                         const capacityInput = slotRow.querySelector('input[type="number"]');

                         // Only validate and collect data from enabled inputs
                         if (!fromInput.disabled) {
                              // Basic validation for each slot
                             if (!fromInput.value || !toInput.value || !capacityInput.value || parseInt(capacityInput.value) < 1) {
                                  validationErrorsDiv.innerHTML += `<p>Please fill in all time and capacity fields correctly for ${day}</p>`;
                                  hasErrors = true;
                                  return; // Skip to next slot in this day
                             }

                             slots.push({
                                 from: fromInput.value,
                                 to: toInput.value,
                                 capacity: parseInt(capacityInput.value)
                             });
                         }
                    });

                     // Only add the day to the schedule if there are slots collected and no errors for this day
                     if (slots.length > 0 && !hasErrors) {
                         schedule[day] = slots;
                    }
                });

                // Validate if at least one day is checked
                 if (document.querySelectorAll('.day-checkbox:checked').length === 0) {
                     validationErrorsDiv.innerHTML += `<p>Please select at least one day.</p>`;
                     hasErrors = true;
                 }

                // Prevent submission if there are errors
                if (hasErrors) {
                    // Scroll to validation errors if any
                    if(validationErrorsDiv.innerHTML !== '') {
                        validationErrorsDiv.scrollIntoView({ behavior: 'smooth' });
                    }
                    return;
                }

                // Put the schedule JSON into the hidden input
                document.getElementById('schedule-data').value = JSON.stringify(schedule);

                console.log('JSON data being submitted:', document.getElementById('schedule-data').value);

                // Submit the form
                form.submit();
            });

            // Initial state setup based on fetched data
            document.querySelectorAll('.day-checkbox').forEach(checkbox => {
                 const day = checkbox.id.split('_')[1];
                 const timeSlotsContainer = document.getElementById(`time-slots-${day}`).parentNode; // Get the container for slots and add button
                 const isDayEnabled = checkbox.checked;
                 const addSlotButton = timeSlotsContainer.querySelector('.add-slot');
                 const existingSlotInputsContainer = timeSlotsContainer.querySelector('.time-slot-inputs');
                 const existingSlotRows = existingSlotInputsContainer.querySelectorAll('.time-slot-row');

                 // Enable/disable add slot button initially
                 addSlotButton.disabled = !isDayEnabled;

                 // Set initial state for inputs in existing rows
                 existingSlotRows.forEach(row => toggleSlotInputs(row, isDayEnabled));

                 // If a day is checked and has no *functional* time slot rows (only the initial empty one),
                 // remove the empty one and add a new functional one on load.
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
                      // Fallback for an unlikely scenario where no row was initially rendered
                      addTimeSlot(day);
                  }
            });
        });
    </script>
</body>
</html> 