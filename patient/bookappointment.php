<?php
include_once('../include/header.php');
include_once('../config/database.php');
//include_once('../patient/fetch_data.php');

// session_start(); // Ensure session is started

// Ensure the user is logged in and retrieve patient_id from session
if (!isset($_SESSION['userid'])) {
    die("Unauthorized access. Please log in.");
}
$userid = $_SESSION['userid']; // Retrieve patient_id from session

$query = "SELECT hospital_id, name FROM hospital"; // Adjust table and column names
$result = $conn->query($query);

// echo '</select>';


$errors = array();
$response = array();

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // $userid = trim($_POST['userid']);s  // Assuming the user is logged in and ID is passed
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
    if (empty($errors)) {
      // Prepare SQL statement
      $stmt = $conn->prepare("INSERT INTO appointment 
          (userid, hospital_id, department_id, doctor_id, appointment_date, appointment_time, reason, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

      if ($stmt === false) {
          die("Prepare failed: " . $conn->error); // Debugging
      }

      // Bind parameters
      $stmt->bind_param("iiiisss",$userid, $hospital_id, $department_id, $doctor_id, $appointment_date, $appointment_time, $reason);

      // Execute statement
      if ($stmt->execute()) {
          echo "Appointment Booked successfully.";
          header("Location: patientdash.php"); // Redirect on success
          exit();
      } else {
          echo "Error: " . $stmt->error;
      }

      // Close statement
      $stmt->close();
  }
}
    
  

$conn->close();



?>

 <body>
    
 
 <!-- Appointment Section -->
    <section class="appointment" id="appointment">
      <div class="container">
        <div class="section-header">
          <div class="badge">Book Appointment</div>
          <h2>Schedule a Visit With Our Specialists</h2>
          <p>Choose your preferred doctor and time slot, and we'll take care of the rest.
            Our online booking system makes it easy to manage your healthcare needs.</p>
        </div>
        
        <div class="appointment-container">
          <form id="appointmentForm" class="appointment-form" method="POST">
            <h2>Book Your Appointment</h2>
            
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
                <label for="hospital">Hospital</label>
                <select id="hospital" name="hospital_id" class="form-select" required>
                  <option value="" disabled selected>Select Hospital</option>
                  <?php 
                  if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="'.$row["hospital_id"].'">'.$row["name"].'</option>';
                    }
                } else {
                    echo '<option value="">No hospitals available</option>';
                }
                  
                  ?>
                </select>
                <span class="error-message" id="departmentError"></span>
              </div>
              
              <div class="form-group">
                <label for="department">Department</label>
                <select id="department" name="department_id" class="form-select" required>
                  <option value="" disabled selected>Select department</option>

                </select>
                <span class="error-message" id="departmentError"></span>
              </div>
              
              <div class="form-group">
                <label for="doctor">Doctor</label>
                <select id="doctor" name="doctor_id" class="form-select"  required>
                  <option value="" disabled selected>Select department first</option>
                </select>
                <span class="error-message" id="doctorError"></span>
              </div>
              
              <div class="form-group">
                <label for="date">Appointment Date</label>
                <input type="date" id="date" name="appointment_date" class="form-input" required>
                <span class="error-message" id="dateError"></span>
              </div>
              
              <div class="form-group">
                <label for="time">Appointment Time</label>
                <select id="time" name="appointment_time" class="form-select" required>
                  <option value="" disabled selected>Select time slot</option>
                  <option value="09:00 AM">09:00 AM</option>
                  <option value="09:30 AM">09:30 AM</option>
                  <option value="10:00 AM">10:00 AM</option>
                  <option value="10:30 AM">10:30 AM</option>
                  <option value="11:00 AM">11:00 AM</option>
                  <option value="11:30 AM">11:30 AM</option>
                  <option value="12:00 PM">12:00 PM</option>
                  <option value="12:30 PM">12:30 PM</option>
                  <option value="02:00 PM">02:00 PM</option>
                  <option value="02:30 PM">02:30 PM</option>
                  <option value="03:00 PM">03:00 PM</option>
                  <option value="03:30 PM">03:30 PM</option>
                  <option value="04:00 PM">04:00 PM</option>
                  <option value="04:30 PM">04:30 PM</option>
                  <option value="05:00 PM">05:00 PM</option>
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
    $('#hospital').change(function() {
        var hospitalId=$('#hospital').val();
        //alert(hospitalId);
        $.ajax({
                    url: "fetch_department.php",
                    type: "POST",
                    data: { hospital_id: hospitalId },
                    dataType: "json",
                    success: function (data) {
                        debugger;
                        let departmentSelect = $("#department");
                        departmentSelect.html('<option value="" disabled selected>Select Department</option>');

                        $.each(data, function (index, department) {
                            debugger;
                            departmentSelect.append(`<option value="${department.department_id}">${department.name}</option>`);
                        });
                    },
                    error: function () {
                        alert("Error fetching departments");
                    }
                });
});
});

$(document).ready(function() {
    // alert('ok');
    $('#department').change(function() {
        var departmentId=$('#department').val();
        //alert(hospitalId);
        $.ajax({
            // alert('ok');

                    url: "fetch_doctor.php",
                    type: "POST",
                    data: { department_id: departmentId },
                    dataType: "json",
                    success: function (data) {
                        // alert('ok');
                        debugger;
                        let doctorSelect = $("#doctor");
                        doctorSelect.html('<option value="" disabled selected>Select doctor</option>');

                        $.each(data, function (index, doctor) {
                            debugger;
                            doctorSelect.append(`<option value="${doctor.doctor_id}">${doctor.name}</option>`);
                        });
                    },
                    error: function () {
                        alert("Error fetching doctors");
                    }
                });
});
});
</script>

 </body>
 </html>