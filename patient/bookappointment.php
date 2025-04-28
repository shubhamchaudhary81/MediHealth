<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['patientID'])) {
    header("Location: ../index.php");
    exit();
}

// include_once('../include/header.php');
include_once('../config/configdatabase.php');
//include_once('../patient/fetch_data.php');

$patient_id = $_SESSION['patientID'];
$errors = array();

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $hospital_id = trim($_POST['hospital_id']);
    $department_id = trim($_POST['department_id']);
    $doctor_id = trim($_POST['doctor_id']);
    $appointment_date = trim($_POST['appointment_date']);
    $appointment_time = trim($_POST['appointment_time']);
    $reason = trim($_POST['reason']);

    // Validation
    if (empty($hospital_id)) {
        $errors['hospital_error'] = "Please select a hospital.";
    }
    if (empty($department_id)) {
        $errors['department_error'] = "Please select a department.";
    }
    if (empty($doctor_id)) {
        $errors['doctor_error'] = "Please select a doctor.";
    }
    if (empty($appointment_date)) {
        $errors['date_error'] = "Please select an appointment date.";
    }
    if (empty($appointment_time)) {
        $errors['time_error'] = "Please select an appointment time.";
    }
    if (empty($reason)) {
        $errors['reason_error'] = "Please enter a reason for your visit.";
    }

    // Check if the selected time slot is available
    if (empty($errors)) {
        $checkAvailability = "SELECT COUNT(*) as count FROM appointments 
                            WHERE doctor_id = ? 
                            AND appointment_date = ? 
                            AND appointment_time = ? 
                            AND status != 'cancelled'";
        
        $stmt = $conn->prepare($checkAvailability);
        if ($stmt === false) {
            $errors['db_error'] = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("sss", $doctor_id, $appointment_date, $appointment_time);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                $errors['time_error'] = "This time slot is already booked. Please select another time.";
            }
            $stmt->close();
        }
    }

    if (empty($errors)) {
        // Insert the appointment
        $insertQuery = "INSERT INTO appointments 
            (patient_id, hospital_id, department_id, doctor_id, appointment_date, appointment_time, reason) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insertQuery);
        if ($stmt === false) {
            $errors['db_error'] = "Prepare failed: " . $conn->error;
        } else {
            // Convert date and time to proper format
            $formatted_date = date('Y-m-d', strtotime($appointment_date));
            $formatted_time = date('H:i:s', strtotime($appointment_time));
            
            $stmt->bind_param("iiissss", 
                $patient_id, 
                $hospital_id, 
                $department_id, 
                $doctor_id, 
                $formatted_date, 
                $formatted_time, 
                $reason
            );

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Appointment booked successfully!";
                header("Location: patientdash.php");
                exit();
            } else {
                $errors['db_error'] = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();

include_once('../include/header.php');



?>

 <body>
    
 
 <!-- Appointment Section -->
    <section class="appointment" id="appointment">
      <div class="container">
        <div class="section-header">
          <div class="badge">Book Appointment</div>
          <h2>Schedule a Visit With Our Specialists</h2>
          <p>Choose your preferred location, hospital, department, and doctor, and we'll take care of the rest.
            Our online booking system makes it easy to manage your healthcare needs.</p>
        </div>
        
        <div class="appointment-container">
          <form id="appointmentForm" class="appointment-form" method="POST">
            <h2>Book Your Appointment</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-grid">
            <!-- <div class="form-group">
                <label for="name">UserID</label>
                <input type="number" id="userid" name="userid" class="form-input" placeholder="Enter your UserID" required>
                <span class="error-message" id="nameError"></span>
              </div> -->
              <!-- <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" class="form-input" placeholder="Enter your full name" required>
                <span class="error-message" id="nameError"></span>
              </div>
              
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" class="form-input" placeholder="Enter your email" required>
                <span class="error-message" id="emailError"></span>
              </div>
              
              <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" class="form-input" placeholder="Enter your phone number" required>
                <span class="error-message" id="phoneError"></span>
              </div>  -->

              <div class="form-group">
                <label for="zone">Zone</label>
                <select id="zone" name="zone" class="form-select" required>
                    <option value="" disabled selected>Select Zone</option>
                    <option value="Bagmati">Bagmati</option>
                    <option value="Gandaki">Gandaki</option>
                    <option value="Koshi">Koshi</option>
                    <option value="Lumbini">Lumbini</option>
                    <option value="Madhesh">Madhesh</option>
                    <option value="Karnali">Karnali</option>
                    <option value="Sudurpashchim">Sudurpashchim</option>
                </select>
                <span class="error-message" id="zoneError"></span>
              </div>

              <div class="form-group">
                <label for="hospital">Hospital</label>
                <select id="hospital" name="hospital_id" class="form-select" required>
                    <option value="" disabled selected>Select Zone first</option>
                </select>
                <span class="error-message" id="hospitalError"></span>
              </div>
              
              <div class="form-group">
                <label for="department">Department</label>
                <select id="department" name="department_id" class="form-select" required>
                    <option value="" disabled selected>Select hospital first</option>
                </select>
                <span class="error-message" id="departmentError"></span>
              </div>
              
              <div class="form-group">
                <label for="doctor">Doctor</label>
                <select id="doctor" name="doctor_id" class="form-select" required>
                  <option value="" disabled selected>Select department first</option>
                </select>
                <span class="error-message" id="doctorError"></span>
              </div>
              
              <div class="form-group">
                <label for="date">Appointment Date</label>
                <select id="date" name="appointment_date" class="form-select" required>
                    <option value="" disabled selected>Select doctor first</option>
                </select>
                <span class="error-message" id="dateError"></span>
              </div>
              
              <div class="form-group">
                <label for="time">Appointment Time</label>
                <select id="time" name="appointment_time" class="form-select" required>
                    <option value="" disabled selected>Select date first</option>
                </select>
                <span class="error-message" id="timeError"></span>
              </div>
            </div>
            
            <div class="form-group">
              <label for="reason">Reason for Visit</label>
              <textarea id="reason" name="reason" class="form-textarea" placeholder="Please briefly describe your symptoms or reason for the appointment" required></textarea>
              <span class="error-message" id="reasonError"></span>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">Book Appointment</button>
          </form>
        </div>
      </div>
    </section>

    <script>
$(document).ready(function() {
    //alert('ok');
    $('#zone').change(function() {
        var zone = $(this).val();
        $.ajax({
            url: "fetch_location.php",
            type: "POST",
            data: { zone: zone },
            dataType: "json",
            success: function(data) {
                let hospitalSelect = $("#hospital");
                hospitalSelect.html('<option value="" disabled selected>Select Hospital</option>');

                $.each(data, function(index, hospital) {
                    hospitalSelect.append(`<option value="${hospital.id}">${hospital.name} - ${hospital.city}</option>`);
                });
                
                // Reset dependent dropdowns
                $("#department").html('<option value="" disabled selected>Select hospital first</option>');
                $("#doctor").html('<option value="" disabled selected>Select department first</option>');
                $("#date").html('<option value="" disabled selected>Select doctor first</option>');
                $("#time").html('<option value="" disabled selected>Select date first</option>');
            },
            error: function() {
                alert("Error fetching hospitals");
            }
        });
    });

    $('#hospital').change(function() {
        var hospitalId = $(this).val();
        $.ajax({
                    url: "fetch_department.php",
                    type: "POST",
                    data: { hospital_id: hospitalId },
                    dataType: "json",
            success: function(data) {
                        let departmentSelect = $("#department");
                        departmentSelect.html('<option value="" disabled selected>Select Department</option>');

                $.each(data, function(index, department) {
                    departmentSelect.append(`<option value="${department.department_id}">${department.department_name}</option>`);
                        });
                
                // Reset doctor dropdown
                $("#doctor").html('<option value="" disabled selected>Select department first</option>');
                $("#date").html('<option value="" disabled selected>Select doctor first</option>');
                $("#time").html('<option value="" disabled selected>Select date first</option>');
                    },
            error: function() {
                        alert("Error fetching departments");
                    }
});
});

    $('#department').change(function() {
        var departmentId = $(this).val();
        var hospitalId = $('#hospital').val();
        $.ajax({
                    url: "fetch_doctor.php",
                    type: "POST",
            data: { 
                department_id: departmentId,
                hospital_id: hospitalId
            },
                    dataType: "json",
            success: function(data) {
                        let doctorSelect = $("#doctor");
                doctorSelect.html('<option value="" disabled selected>Select Doctor</option>');

                $.each(data, function(index, doctor) {
                    doctorSelect.append(`<option value="${doctor.doctor_id}">${doctor.name} - ${doctor.specialization}</option>`);
                });
                
                // Reset date and time dropdowns
                $("#date").html('<option value="" disabled selected>Select doctor first</option>');
                $("#time").html('<option value="" disabled selected>Select date first</option>');
            },
            error: function() {
                alert("Error fetching doctors");
            }
        });
    });
    
    $('#doctor').change(function() {
        var doctorId = $(this).val();
        if (doctorId) {
            $.ajax({
                url: "fetch_doctor_schedule.php",
                type: "POST",
                data: { doctor_id: doctorId },
                dataType: "json",
                success: function(data) {
                    let dateSelect = $("#date");
                    dateSelect.html('<option value="" disabled selected>Select Day</option>');
                    
                    if (data.length > 0) {
                        // Get available days from the schedule
                        let availableDays = [];
                        data.forEach(function(schedule) {
                            if (schedule.day && !availableDays.includes(schedule.day)) {
                                availableDays.push(schedule.day);
                            }
                        });
                        
                        // Add available days to the dropdown
                        availableDays.forEach(function(day) {
                            dateSelect.append(`<option value="${day}">${day}</option>`);
                        });
                    } else {
                        dateSelect.append('<option value="" disabled>No available days</option>');
                    }
                    
                    // Reset time dropdown
                    $("#time").html('<option value="" disabled selected>Select day first</option>');
                },
                error: function() {
                    alert("Error fetching doctor schedule");
                }
            });
        } else {
            $("#date").html('<option value="" disabled selected>Select doctor first</option>');
            $("#time").html('<option value="" disabled selected>Select day first</option>');
        }
    });
    
    $('#date').on('change', function() {
        var doctorId = $('#doctor').val();
        var selectedDay = $(this).val();
        
        console.log('Selected day:', selectedDay);
        console.log('Doctor ID:', doctorId);
        
        if (doctorId && selectedDay) {
            // Fetch doctor's schedule
            $.ajax({
                url: 'fetch_doctor_schedule.php',
                type: 'POST',
                data: { 
                    doctor_id: doctorId,
                    day: selectedDay
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Schedule data:', response);
                    
                    // Clear existing options
                    $('#time').empty();
                    $('#time').append('<option value="">Select Time</option>');
                    
                    if (response && response.time_slots) {
                        // Split the time slots string into an array
                        var timeSlots = response.time_slots.split(',');
                        console.log('Time slots:', timeSlots);
                        
                        // Add time slots to the dropdown
                        timeSlots.forEach(function(time) {
                            // Skip empty time slots
                            if (!time || time.trim() === '') {
                                return;
                            }
                            
                            // Convert 24-hour format to 12-hour format for display
                            var timeParts = time.split(':');
                            var hours = parseInt(timeParts[0]);
                            var minutes = timeParts[1];
                            var ampm = hours >= 12 ? 'PM' : 'AM';
                            hours = hours % 12;
                            hours = hours ? hours : 12; // Convert 0 to 12
                            var displayTime = hours + ':' + minutes + ' ' + ampm;
                            
                            $('#time').append('<option value="' + time + '">' + displayTime + '</option>');
                        });
                    } else {
                        $('#time').append('<option value="" disabled>No available times</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching doctor schedule:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    $('#time').empty();
                    $('#time').append('<option value="">Error loading times</option>');
                }
            });
        } else {
            $('#time').empty();
            $('#time').append('<option value="">Select Doctor and Day first</option>');
        }
    });
});
</script>

 </body>
 </html>