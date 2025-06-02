<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['patientID'])) {
    header("Location: ../index.php");
    exit();
}

// Clear session data on page refresh/reload
if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') {
    // This indicates a page refresh - just clear the session data without redirecting
    if (isset($_SESSION['doctor_booking_data'])) {
        unset($_SESSION['doctor_booking_data']);
        $is_from_doctor_profile = false; // Reset this flag
    }
}

include_once('../config/configdatabase.php');

$patient_id = $_SESSION['patientID'];
$errors = array();

// Check if we have doctor booking data from doctor profile
$is_from_doctor_profile = false;
if (isset($_SESSION['doctor_booking_data']) && isset($_SESSION['doctor_booking_data']['source']) && $_SESSION['doctor_booking_data']['source'] === 'doctor_profile') {
    $is_from_doctor_profile = true;
    $doctor_data = $_SESSION['doctor_booking_data'];

    // Fetch complete doctor information
    $query = "SELECT d.*, h.id as hospital_id, h.name as hospital_name, 
              h.zone, h.district, h.city,
              dep.department_id, dep.department_name
              FROM doctor d 
              JOIN hospital h ON d.hospitalid = h.id 
              JOIN department dep ON d.department_id = dep.department_id 
              WHERE d.doctor_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $doctor_data['doctor_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Doctor information not found.";
        header("Location: ourdoctors.php");
        exit();
    }

    $doctor_info = $result->fetch_assoc();
    
    // Add this new code to show doctor info box immediately
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update doctor info box
            $('#doctorName').text('Dr. " . addslashes($doctor_info['name']) . "');
            $('#doctorSpecialization').text('" . addslashes($doctor_info['specialization']) . "');
            $('#doctorQualification').text('" . addslashes($doctor_info['qualification']) . "');
            $('#doctorExperience').text('" . addslashes($doctor_info['experience']) . " years');
            
            // Show the doctor info box
            $('#doctorInfoBox').show();
        });
    </script>";
}

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $hospital_id = trim($_POST['hospital_id']);
    $department_id = trim($_POST['department_id']);
    $doctor_id = trim($_POST['doctor_id']);
    $appointment_date = trim($_POST['appointment_date']);
    $appointment_time = trim($_POST['appointment_time']);
    $reason = trim($_POST['reason']);

    // New: Get appointment type and other patient fields
    $appointment_for = isset($_POST['appointment_type']) ? $_POST['appointment_type'] : 'myself';
    $other_patient_id = null;
    $status = 'scheduled';
    if ($appointment_for === 'others') {
        $other_patient_name = trim($_POST['patient_name']);
        $other_patient_age = trim($_POST['patient_age']);
        $other_patient_gender = trim($_POST['patient_gender']);
        $other_patient_blood_group = trim($_POST['patient_blood_group']);
        $other_patient_relation = trim($_POST['relation']);
        $other_patient_address = trim($_POST['patient_address']);
        $other_patient_email = trim($_POST['patient_email']);
        $other_patient_phone = trim($_POST['patient_phone']);
    }

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

    // Prevent double booking for the same day for the same patient and doctor (myself)
    if (empty($errors) && $appointment_for === 'myself') {
        // Check if patient has any appointment for this day with any doctor
        $check_day_query = "SELECT * FROM appointments 
                           WHERE patient_id = ? 
                           AND appointment_date = ? 
                           AND status != 'cancelled'";
        $stmt = $conn->prepare($check_day_query);
        if (!$stmt) {
            $errors['db_error'] = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $patient_id, $appointment_date);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $errors['date_error'] = "You already have an appointment scheduled for this day.";
            }
            $stmt->close();
        }

        // Check if the specific time slot is available for this doctor
        if (empty($errors)) {
            $check_time_query = "SELECT COUNT(*) as count FROM appointments 
                                WHERE doctor_id = ? 
                                AND appointment_date = ? 
                                AND appointment_time = ? 
                                AND status != 'cancelled'";
            $stmt = $conn->prepare($check_time_query);
            if (!$stmt) {
                $errors['db_error'] = "Database error: " . $conn->error;
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
    }

    // Prevent double booking for the same day for the same 'other patient'
    if (empty($errors) && $appointment_for === 'others') {
        // Check if other patient has any appointment for this day
        $check_other_day_query = "SELECT a.* FROM appointments a 
                                 INNER JOIN other_patients op ON a.other_patient_id = op.id 
                                 WHERE op.phone = ? 
                                 AND a.appointment_date = ? 
                                 AND a.status != 'cancelled'";
        $stmt = $conn->prepare($check_other_day_query);
        if (!$stmt) {
            $errors['db_error'] = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $other_patient_phone, $appointment_date);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $errors['date_error'] = "This person already has an appointment scheduled for this day.";
            }
            $stmt->close();
        }

        // Check if the specific time slot is available for this doctor
        if (empty($errors)) {
            $check_other_time_query = "SELECT COUNT(*) as count FROM appointments 
                                      WHERE doctor_id = ? 
                                      AND appointment_date = ? 
                                      AND appointment_time = ? 
                                      AND status != 'cancelled'";
            $stmt = $conn->prepare($check_other_time_query);
            if (!$stmt) {
                $errors['db_error'] = "Database error: " . $conn->error;
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
    }

    // Always format the date before using it in queries
    $formatted_date = date('Y-m-d', strtotime($appointment_date));
    $formatted_time = date('H:i:s', strtotime($appointment_time));

    // Check if appointment already exists for this patient, doctor, and date
    if (empty($errors)) {
        $check_query = "SELECT * FROM appointments 
                       WHERE patient_id = '$patient_id' 
                       AND doctor_id = '$doctor_id' 
                       AND appointment_date = '$formatted_date' 
                       AND status != 'cancelled'";
        $result = $conn->query($check_query);
        if ($result && $result->num_rows > 0) {
            echo "<script>
                alert('You have already booked an appointment with this doctor for this day. Please select another day or doctor.');
                window.location.href = 'bookappointment.php';
            </script>";
            exit();
        }
    }

    // If there are no errors, proceed with the booking
    if (empty($errors)) {
        try {
            // Store appointment data in session for payment
            $_SESSION['pending_appointment'] = [
                'hospital_id' => $hospital_id,
                'department_id' => $department_id,
                'doctor_id' => $doctor_id,
                'appointment_date' => $formatted_date,
                'appointment_time' => $formatted_time,
                'reason' => $reason,
                'appointment_for' => $appointment_for,
                'status' => 'pending_payment'
            ];

            // If booking for others, store other patient data
            if ($appointment_for === 'others') {
                $_SESSION['pending_appointment']['other_patient'] = [
                    'name' => $other_patient_name,
                    'age' => $other_patient_age,
                    'gender' => $other_patient_gender,
                    'blood_group' => $other_patient_blood_group,
                    'relation' => $other_patient_relation,
                    'address' => $other_patient_address,
                    'email' => $other_patient_email,
                    'phone' => $other_patient_phone
                ];
            }

            // Clear doctor booking data if exists
            if (isset($_SESSION['doctor_booking_data'])) {
                unset($_SESSION['doctor_booking_data']);
            }

            // Redirect to payment page
            header("Location: payment.php");
            exit();
        } catch (Exception $e) {
            echo "<script>
                alert('Error preparing appointment: " . addslashes($e->getMessage()) . "');
                window.location.href = 'bookappointment.php';
            </script>";
            exit();
        }
    }
}

include_once('../include/header.php');
?>

<head>
    <!-- ... other head content ... -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <!-- ... your other scripts ... -->
</head>

<div class="container">
    <div class="appointment">
        <div class="section-header">
            <div class="badge">Book Appointment</div>
            <h2>Schedule a Visit With Our Specialists</h2>
            <p>Choose your preferred location, hospital, department, and doctor, and we'll take care of the rest.</p>
        </div>
        
        <!-- Add Doctor Info Box -->
        <div id="doctorInfoBox" class="doctor-info-box" style="display: none;">
            <div class="doctor-info-content">
                <div class="doctor-avatar">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="doctor-details">
                    <h3 id="doctorName">Dr. Name</h3>
                    <p id="doctorSpecialization">Specialization</p>
                    <div class="doctor-meta">
                        <span><i class="fas fa-graduation-cap"></i> <span id="doctorQualification">Qualification</span></span>
                        <span><i class="fas fa-briefcase"></i> <span id="doctorExperience">Experience</span></span>
                    </div>
                </div>
            </div>
        </div>
        
        <form id="appointmentForm" method="POST">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="appointment-type">
                <div class="radio-group">
                    <input type="radio" id="for_myself" name="appointment_type" value="myself" checked><label for="for_myself">For Myself</label>
                    <input type="radio" id="for_others" name="appointment_type" value="others"><label for="for_others">For Others</label>
                </div>
            </div>

            <div id="other_patient_details" style="display: none;">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="patient_name">Patient Name</label>
                        <input type="text" id="patient_name" name="patient_name" class="form-select">
                    </div>

                    <div class="form-group">
                        <label for="patient_age">Age</label>
                        <input type="number" id="patient_age" name="patient_age" class="form-select" min="0" max="120">
                    </div>

                    <div class="form-group">
                        <label for="patient_gender">Gender</label>
                        <select id="patient_gender" name="patient_gender" class="form-select">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="patient_blood_group">Blood Group</label>
                        <select id="patient_blood_group" name="patient_blood_group" class="form-select">
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="relation">Relation with Patient</label>
                        <select id="relation" name="relation" class="form-select">
                            <option value="">Select Relation</option>
                            <option value="Father">Father</option>
                            <option value="Mother">Mother</option>
                            <option value="Brother">Brother</option>
                            <option value="Sister">Sister</option>
                            <option value="Spouse">Spouse</option>
                            <option value="Son">Son</option>
                            <option value="Daughter">Daughter</option>
                            <option value="Grandfather">Grandfather</option>
                            <option value="Grandmother">Grandmother</option>
                            <option value="Uncle">Uncle</option>
                            <option value="Aunt">Aunt</option>
                            <option value="Cousin">Cousin</option>
                            <option value="Friend">Friend</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="patient_address">Address</label>
                        <textarea id="patient_address" name="patient_address" class="form-select" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="patient_email">Email</label>
                        <input type="email" id="patient_email" name="patient_email" class="form-select">
                    </div>

                    <div class="form-group">
                        <label for="patient_phone">Phone Number</label>
                        <input type="tel" id="patient_phone" name="patient_phone" class="form-select">
                    </div>
                </div>
            </div>

            <div class="form-grid">
                <?php if ($is_from_doctor_profile): ?>
                    <!-- Pre-populated form for doctor profile booking -->
                    <div class="form-group">
                        <label for="province">Province</label>
                        <select id="province" name="province" class="form-select" required>
                            <option value="" disabled>Select Province</option>
                            <option value="1" <?php echo ($doctor_info['zone'] == '1') ? 'selected' : ''; ?>>Province 1</option>
                            <option value="2" <?php echo ($doctor_info['zone'] == '2') ? 'selected' : ''; ?>>Madhesh</option>
                            <option value="3" <?php echo ($doctor_info['zone'] == '3') ? 'selected' : ''; ?>>Bagmati</option>
                            <option value="4" <?php echo ($doctor_info['zone'] == '4') ? 'selected' : ''; ?>>Gandaki</option>
                            <option value="5" <?php echo ($doctor_info['zone'] == '5') ? 'selected' : ''; ?>>Lumbini</option>
                            <option value="6" <?php echo ($doctor_info['zone'] == '6') ? 'selected' : ''; ?>>Karnali</option>
                            <option value="7" <?php echo ($doctor_info['zone'] == '7') ? 'selected' : ''; ?>>Sudurpashchim</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="district">District</label>
                        <select id="district" name="district" class="form-select" required>
                            <option value="" disabled>Select District</option>
                            <option value="<?php echo htmlspecialchars($doctor_info['district']); ?>" selected>
                                <?php echo htmlspecialchars($doctor_info['district']); ?>
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="city">City</label>
                        <select id="city" name="city" class="form-select" required>
                            <option value="" disabled>Select City</option>
                            <option value="<?php echo htmlspecialchars($doctor_info['city']); ?>" selected>
                                <?php echo htmlspecialchars($doctor_info['city']); ?>
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="hospital">Hospital</label>
                        <select id="hospital" name="hospital_id" class="form-select" required>
                            <option value="" disabled>Select Hospital</option>
                            <option value="<?php echo $doctor_info['hospital_id']; ?>" selected>
                                <?php echo htmlspecialchars($doctor_info['hospital_name']); ?>
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department_id" class="form-select" required>
                            <option value="" disabled>Select Department</option>
                            <option value="<?php echo $doctor_info['department_id']; ?>" selected>
                                <?php echo htmlspecialchars($doctor_info['department_name']); ?>
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="doctor">Doctor</label>
                        <select id="doctor" name="doctor_id" class="form-select" required>
                            <option value="" disabled>Select Doctor</option>
                            <option value="<?php echo $doctor_info['doctor_id']; ?>" selected>
                                <?php echo htmlspecialchars($doctor_info['name']); ?> - <?php echo htmlspecialchars($doctor_info['specialization']); ?>
                            </option>
                        </select>
                    </div>
                <?php else: ?>
                    <!-- Normal booking form -->
                    <div class="form-group">
                        <label for="province">Province</label>
                        <select id="province" name="province" class="form-select" required>
                            <option value="" disabled selected>Select Province</option>
                            <option value="1">Province 1</option>
                            <option value="2">Madhesh</option>
                            <option value="3">Bagmati</option>
                            <option value="4">Gandaki</option>
                            <option value="5">Lumbini</option>
                            <option value="6">Karnali</option>
                            <option value="7">Sudurpashchim</option>
                        </select>
                    </div>

                <div class="form-group">
                    <label for="district">District</label>
                    <select id="district" name="district" class="form-select" required>
                        <option value="" disabled selected>Select Province first</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <select id="city" name="city" class="form-select" required>
                        <option value="" disabled selected>Select District first</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="hospital">Hospital</label>
                    <select id="hospital" name="hospital_id" class="form-select" required>
                        <option value="" disabled selected>Select City first</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department_id" class="form-select" required>
                        <option value="" disabled selected>Select hospital first</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="doctor">Doctor</label>
                    <select id="doctor" name="doctor_id" class="form-select" required>
                        <option value="" disabled selected>Select department first</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="appointment_date">Appointment Date</label>
                    <input type="text" id="appointment_date" name="appointment_date" class="form-select" autocomplete="off" required readonly>
                    <input type="hidden" id="selected_day" name="selected_day">
                    <div id="datepicker_info" class="datepicker-info"></div>
                </div>

                <div class="form-group">
                    <label for="appointment_time">Select Time Slot</label>
                    <select name="appointment_time" id="appointment_time" class="form-select" required>
                        <option value="">Select Day first</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reason">Reason for Visit</label>
                    <textarea id="reason" name="reason" class="form-textarea" 
                              placeholder="Please briefly describe your symptoms or reason for the appointment" required></textarea>
                </div>

                <div class="form-group">
                    <label>Attach Previous Medical Records (Optional)</label>
                    <div class="attachments-container">
                        <div class="attachment-item">
                            <label class="attachment-label">
                                <i class="fas fa-file-medical"></i>
                                <span>Add Prescription</span>
                                <input type="file" name="prescription_files[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                            </label>
                            <div class="file-list" id="prescription-list"></div>
                        </div>
                        <div class="attachment-item">
                            <label class="attachment-label">
                                <i class="fas fa-file-medical-alt"></i>
                                <span>Add Medical Report</span>
                                <input type="file" name="report_files[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                            </label>
                            <div class="file-list" id="report-list"></div>
                        </div>
                    </div>
                    <p class="help-text">You can attach multiple files. Supported formats: PDF, JPG, PNG</p>
                </div>
            </div>

            <div class="form-group" style="display:none;">
                <select id="day" name="day"></select>
            </div>

            <button type="submit" class="btn-primary">Proceed to Payment</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Form validation before submission
    $('#appointmentForm').on('submit', function(e) {
        let isValid = true;
        const requiredFields = [
            'hospital_id', 'department_id', 'doctor_id', 
            'appointment_date', 'appointment_time', 'reason'
        ];

        // Check all required fields
        requiredFields.forEach(field => {
            const value = $(`[name="${field}"]`).val();
            if (!value) {
                isValid = false;
                $(`[name="${field}"]`).addClass('error');
            } else {
                $(`[name="${field}"]`).removeClass('error');
            }
        });

        // If appointment is for others, check other patient fields
        if ($('#for_others').is(':checked')) {
            const otherFields = [
                'patient_name', 'patient_age', 'patient_gender',
                'patient_blood_group', 'relation', 'patient_address',
                'patient_email', 'patient_phone'
            ];

            otherFields.forEach(field => {
                const value = $(`[name="${field}"]`).val();
                if (!value) {
                    isValid = false;
                    $(`[name="${field}"]`).addClass('error');
                } else {
                    $(`[name="${field}"]`).removeClass('error');
                }
            });
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields before proceeding to payment.');
            return false;
        }
    });

    // Remove error class on input
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('error');
    });

    <?php if (!$is_from_doctor_profile): ?>
    // Only include the dynamic form handling for normal booking
    const districtsByProvince = {
        "1": ["Bhojpur", "Dhankuta", "Ilam", "Jhapa", "Khotang", "Morang", "Okhaldhunga", "Panchthar", "Sankhuwasabha", "Solukhumbu", "Sunsari", "Taplejung", "Terhathum", "Udayapur"],
        "2": ["Bara", "Dhanusha", "Mahottari", "Parsa", "Rautahat", "Saptari", "Sarlahi", "Siraha"],
        "3": ["Bhaktapur", "Chitwan", "Dhading", "Dolakha", "Kathmandu", "Kavrepalanchok", "Lalitpur", "Makwanpur", "Nuwakot", "Ramechhap", "Rasuwa", "Sindhuli", "Sindhupalchok"],
        "4": ["Baglung", "Gorkha", "Kaski", "Lamjung", "Manang", "Mustang", "Myagdi", "Nawalpur", "Parbat", "Syangja", "Tanahu"],
        "5": ["Arghakhanchi", "Banke", "Bardiya", "Dang", "Eastern Rukum", "Gulmi", "Kapilvastu", "Palpa", "Parasi", "Pyuthan", "Rolpa", "Rupandehi"],
        "6": ["Dailekh", "Dolpa", "Humla", "Jajarkot", "Jumla", "Kalikot", "Mugu", "Salyan", "Surkhet", "Western Rukum"],
        "7": ["Achham", "Baitadi", "Bajhang", "Bajura", "Dadeldhura", "Darchula", "Doti", "Kailali", "Kanchanpur"]
    };

    const citiesByDistrict = {
        "Kathmandu": ["Kathmandu", "Kirtipur", "Tokha", "Budhanilkantha", "Gokarneshwar", "Chandragiri", "Tarakeshwar", "Dakshinkali", "Nagarkot", "Sankhu", "Koteshwor", "Boudha", "Patan", "Thamel", "New Baneshwor"],
        "Lalitpur": ["Patan", "Godawari", "Lubhu", "Imadol", "Harisiddhi", "Thaiba", "Chapagaun", "Bungamati", "Karyabinayak", "Jawalakhel", "Kupondole", "Pulchowk", "Kumaripati"],
        "Bhaktapur": ["Bhaktapur", "Thimi", "Suryabinayak", "Changunarayan", "Madhyapur Thimi", "Nagarkot", "Suryamati", "Nangkhel", "Duwakot"],
        "Bhojpur": ["Bhojpur", "Shadanand", "Hatuwagadhi", "Ramprasad Rai", "Aamchok", "Arun", "Pauwadungma", "Salpasilichho"],
        "Dhankuta": ["Dhankuta", "Pakhribas", "Mahalaxmi", "Sangurigadhi", "Chaubise", "Shahidbhumi", "Chhathar Jorpati"],
        "Ilam": ["Ilam", "Deumai", "Mai", "Suryodaya", "Sandakpur", "Mangsebung", "Rong", "Chulachuli"],
        "Jhapa": ["Birtamod", "Damak", "Mechinagar", "Bhadrapur", "Kankai", "Gauradaha", "Arjundhara", "Shivasatakshi"],
        "Khotang": ["Diktel", "Halesi Tuwachung", "Rupakot Majhuwagadhi", "Aiselukharka", "Jantedhunga", "Lamidanda", "Sakela"],
        "Morang": ["Biratnagar", "Sundar Haraincha", "Belbari", "Pathari", "Rangeli", "Urlabari", "Letang", "Budhiganga"],
        "Okhaldhunga": ["Siddhicharan", "Champadevi", "Sunkoshi", "Molung", "Likhu", "Chisankhugadhi", "Manebhanjyang"],
        "Panchthar": ["Phidim", "Hilihang", "Tumbewa", "Miklajung", "Falelung", "Yangwarak", "Kummayak"],
        "Sankhuwasabha": ["Khandbari", "Chainpur", "Madi", "Dharmadevi", "Makalu", "Silichong", "Sabhapokhari"],
        "Solukhumbu": ["Salleri", "Necha Salyan", "Dudhkoshi", "Dudhkunda", "Khumbu Pasang Lhamu", "Likhupike", "Sotang"],
        "Sunsari": ["Inaruwa", "Itahari", "Dharan", "Barahachhetra", "Duhabi", "Ramdhuni", "Koshi", "Harinagar"],
        "Taplejung": ["Phungling", "Aathrai Tribeni", "Sidingwa", "Meringden", "Maiwakhola", "Mikwakhola", "Phaktanglung"],
        "Terhathum": ["Myanglung", "Aathrai", "Phedap", "Laligurans", "Chhathar", "Menchayayem", "Jaljale"],
        "Udayapur": ["Triyuga", "Katari", "Chaudandigadhi", "Udayapurgadhi", "Rautamai", "Tapli", "Limchungbung"],
        "Bara": ["Kalaiya", "Jitpur Simara", "Kolhabi", "Nijgadh", "Mahagadhimai", "Pacharauta", "Pheta", "Simraungadh"],
        "Dhanusha": ["Janakpur", "Dhanusadham", "Mithila", "Bateshwor", "Mukhiyapatti", "Lakshminiya", "Ganeshman Charnath"],
        "Mahottari": ["Jaleshwor", "Bardibas", "Gaushala", "Manara", "Balwa", "Ramgopalpur", "Matihani"],
        "Parsa": ["Birgunj", "Pokhariya", "Bahudaramai", "Jagarnathpur", "Pakahamainpur", "Sakhuwa Prasauni", "Thori"],
        "Rautahat": ["Gaur", "Chandrapur", "Garuda", "Brindaban", "Gujara", "Ishnath", "Katahariya", "Madhav Narayan"],
        "Saptari": ["Rajbiraj", "Hanumannagar", "Khadak", "Tirahut", "Bode Barsain", "Rupani", "Tilathi Koiladi"],
        "Sarlahi": ["Malangwa", "Haripur", "Ishwarpur", "Kabilasi", "Lalbandi", "Dhankaul", "Chandranagar"],
        "Siraha": ["Siraha", "Lahan", "Dhangadhi", "Mirchaiya", "Golbazar", "Bhagwanpur", "Karjanha", "Sukhipur"],
        "Bhaktapur": ["Bhaktapur", "Thimi", "Suryabinayak", "Changunarayan", "Madhyapur Thimi", "Nagarkot", "Suryamati", "Nangkhel", "Duwakot"],
        "Chitwan": ["Bharatpur", "Ratnanagar", "Kalika", "Khairahani", "Madi", "Rapti", "Ichchhakamana"],
        "Dhading": ["Dhunibesi", "Nilkantha", "Khaniyabas", "Gajuri", "Galchhi", "Thakre", "Benighat Rorang"],
        "Dolakha": ["Bhimeshwor", "Jiri", "Kalinchok", "Melung", "Bigu", "Gaurishankar", "Baiteshwor"],
        "Kavrepalanchok": ["Dhulikhel", "Banepa", "Panauti", "Panchkhal", "Mandandeupur", "Namobuddha", "Temal"],
        "Makwanpur": ["Hetauda", "Thaha", "Bhimphedi", "Manahari", "Raksirang", "Bakaiya", "Kailash"],
        "Nuwakot": ["Bidur", "Kakani", "Tadi", "Likhu", "Meghang", "Panchakanya", "Suryagadhi"],
        "Ramechhap": ["Manthali", "Ramechhap", "Umakunda", "Doramba", "Gokulganga", "Likhu Tamakoshi", "Sunapati"],
        "Rasuwa": ["Dhunche", "Gosaikunda", "Kalika", "Naukunda", "Uttargaya"],
        "Sindhuli": ["Kamalamai", "Dudhauli", "Golanjor", "Marin", "Sunkoshi", "Ghyanglekh", "Phikkal"],
        "Sindhupalchok": ["Chautara", "Melamchi", "Bahrabise", "Jugal", "Lisankhu", "Helambu", "Bhotekoshi"],
        "Baglung": ["Baglung", "Galkot", "Jaimuni", "Kathekhola", "Nisikhola", "Bareng", "Tara Khola"],
        "Gorkha": ["Gorkha", "Palungtar", "Sulikot", "Ajirkot", "Arughat", "Chum Nubri", "Dharche"],
        "Kaski": ["Pokhara", "Annapurna", "Machhapuchchhre", "Madi", "Rupa", "Lekhnath"],
        "Lamjung": ["Besisahar", "Sundarbazar", "Rainas", "Dordi", "Dudhpokhari", "Kwholasothar"],
        "Manang": ["Chame", "Narpa Bhumi", "Nashong", "Neshyang"],
        "Mustang": ["Lo Manthang", "Lomanthang", "Thasang", "Gharapjhong", "Waragung Muktichhetra"],
        "Myagdi": ["Beni", "Annapurna", "Dhaulagiri", "Mangala", "Malika", "Raghuganga"],
        "Nawalpur": ["Kawasoti", "Devchuli", "Gaindakot", "Madhyabindu", "Baudikali"],
        "Parbat": ["Kusma", "Phalebas", "Jaljala", "Mahashila", "Paiyun", "Modi"],
        "Syangja": ["Putalibazar", "Waling", "Chapakot", "Galyang", "Arjun Chaupari", "Biruwa"],
        "Tanahu": ["Damauli", "Byas", "Shuklagandaki", "Rhishing", "Devghat", "Bandipur"],
        "Arghakhanchi": ["Sandhikharka", "Sitganga", "Bhumekasthan", "Chhatradev", "Panini"],
        "Banke": ["Nepalgunj", "Kohalpur", "Rapti Sonari", "Narainapur", "Duduwa", "Janaki"],
        "Bardiya": ["Gulariya", "Madhuwan", "Rajapur", "Bansgadhi", "Barbardiya", "Geruwa"],
        "Dang": ["Ghorahi", "Tulsipur", "Lamahi", "Gadhawa", "Rapti", "Shantinagar"],
        "Eastern Rukum": ["Rukumkot", "Bhume", "Putha Uttarganga", "Sisne"],
        "Gulmi": ["Tamghas", "Resunga", "Musikot", "Chandrakot", "Gulmi Darbar", "Satyawati"],
        "Kapilvastu": ["Kapilvastu", "Buddhabhumi", "Shivaraj", "Maharajgunj", "Krishnanagar", "Yashodhara"],
        "Palpa": ["Tansen", "Rampur", "Rambha", "Nisdi", "Mathagadhi", "Bagnaskali"],
        "Parasi": ["Ramgram", "Sunwal", "Pratappur", "Sarawal", "Palhi Nandan"],
        "Pyuthan": ["Pyuthan", "Swargadwari", "Mandavi", "Naubahini", "Jhimruk", "Gaumukhi"],
        "Rolpa": ["Liwang", "Runtigadhi", "Sunchhahari", "Triveni", "Thawang", "Madi"],
        "Rupandehi": ["Butwal", "Siddharthanagar", "Lumbini Sanskritik", "Devdaha", "Sainamaina", "Tilottama"],
        "Dailekh": ["Narayan", "Dullu", "Aathabis", "Bhagawatimai", "Dungeshwar", "Naumule"],
        "Dolpa": ["Dunai", "Shey Phoksundo", "Jagadulla", "Kaike", "Mudkechula"],
        "Humla": ["Simkot", "Namkha", "Kharpunath", "Sarkegad", "Chankheli", "Tajakot"],
        "Jajarkot": ["Khalanga", "Bheri", "Nalgad", "Kushe", "Barekot", "Junichande"],
        "Jumla": ["Chandannath", "Tila", "Guthichaur", "Hima", "Sinja", "Tatopani"],
        "Kalikot": ["Manma", "Pachaljharana", "Sanni Triveni", "Naraharinath", "Shubha Kalika"],
        "Mugu": ["Gamgadhi", "Soru", "Khatyad", "Mugum Karmarong"],
        "Salyan": ["Khalanga", "Sharada", "Darma", "Kapurkot", "Tribeni", "Kalimati"],
        "Surkhet": ["Birendranagar", "Bheriganga", "Gurbhakot", "Panchpuri", "Lekbeshi", "Barahatal"],
        "Western Rukum": ["Musikot", "Chaurjahari", "Aathbiskot", "Banfikot"],
        "Achham": ["Mangalsen", "Kamalbazar", "Sanfebagar", "Panchadewal Binayak", "Mellekh", "Ramaroshan"],
        "Baitadi": ["Dasharathchand", "Patan", "Purchaudi", "Sigas", "Surnaya", "Shivanath"],
        "Bajhang": ["Chainpur", "Jayaprithvi", "Bungal", "Talkot", "Surma", "Thalara"],
        "Bajura": ["Martadi", "Budhiganga", "Gaumul", "Himali", "Pandav Gupha", "Swami Kartik"],
        "Dadeldhura": ["Amargadhi", "Parashuram", "Aalital", "Ganyapadhura", "Nawadurga", "Ajaymeru"],
        "Darchula": ["Darchula", "Shailyashikhar", "Marma", "Lekam", "Naugad", "Duhun"],
        "Doti": ["Dipayal Silgadhi", "Shikhar", "Purbichauki", "Sayal", "Jorayal", "K.I. Singh"],
        "Kailali": ["Dhangadhi", "Tikapur", "Lamki Chuha", "Godawari", "Gauriganga", "Bhajani"],
        "Kanchanpur": ["Bhimdatta", "Krishnapur", "Belauri", "Punarbas", "Laljhadi", "Shuklaphanta"]
    };

    // Province change handler
    $('#province').change(function() {
        var province = $(this).val();
        let districtSelect = $("#district");
        districtSelect.html('<option value="" disabled selected>Select District</option>');

        if (province && districtsByProvince[province]) {
            districtsByProvince[province].forEach(district => {
                districtSelect.append(`<option value="${district}">${district}</option>`);
            });
        }
        
        // Reset dependent dropdowns
        $("#city").html('<option value="" disabled selected>Select district first</option>');
        $("#hospital").html('<option value="" disabled selected>Select city first</option>');
        $("#department").html('<option value="" disabled selected>Select hospital first</option>');
        $("#doctor").html('<option value="" disabled selected>Select department first</option>');
    });

    // District change handler
    $('#district').change(function() {
        var district = $(this).val();
        let citySelect = $("#city");
        citySelect.html('<option value="" disabled selected>Select City</option>');

        if (district && citiesByDistrict[district]) {
            citiesByDistrict[district].forEach(city => {
                citySelect.append(`<option value="${city}">${city}</option>`);
            });
        }
        
        // Reset dependent dropdowns
        $("#hospital").html('<option value="" disabled selected>Select city first</option>');
        $("#department").html('<option value="" disabled selected>Select hospital first</option>');
        $("#doctor").html('<option value="" disabled selected>Select department first</option>');
    });

    // City change handler
    $('#city').change(function() {
        var city = $(this).val();
        if (city) {
            $.ajax({
                url: "fetch_hospital.php",
                type: "POST",
                data: { city: city },
                dataType: "json",
                success: function(data) {
                    let hospitalSelect = $("#hospital");
                    hospitalSelect.html('<option value="" disabled selected>Select Hospital</option>');
                    
                    if (data && data.length > 0) {
                        data.forEach(function(hospital) {
                            hospitalSelect.append(`<option value="${hospital.id}">${hospital.name}</option>`);
                        });
                    }
                    
                    // Reset dependent dropdowns
                    $("#department").html('<option value="" disabled selected>Select hospital first</option>');
                    $("#doctor").html('<option value="" disabled selected>Select department first</option>');
                },
                error: function() {
                    alert("Error fetching hospitals");
                }
            });
        }
    });

    // Hospital change handler
    $('#hospital').change(function() {
        var hospitalId = $(this).val();
        if (hospitalId) {
            $.ajax({
                url: "fetch_department.php",
                type: "POST",
                data: { hospital_id: hospitalId },
                dataType: "json",
                success: function(data) {
                    let departmentSelect = $("#department");
                    departmentSelect.html('<option value="" disabled selected>Select Department</option>');
                    
                    data.forEach(function(department) {
                        departmentSelect.append(`<option value="${department.department_id}">${department.department_name}</option>`);
                    });
                    
                    // Reset doctor dropdown
                    $("#doctor").html('<option value="" disabled selected>Select department first</option>');
                },
                error: function() {
                    alert("Error fetching departments");
                }
            });
        }
    });

    // Department change handler
    $('#department').change(function() {
        var departmentId = $(this).val();
        var hospitalId = $('#hospital').val();
        if (departmentId && hospitalId) {
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
                    
                    data.forEach(function(doctor) {
                        doctorSelect.append(`<option value="${doctor.doctor_id}">${doctor.name} - ${doctor.specialization}</option>`);
                    });
                },
                error: function() {
                    alert("Error fetching doctors");
                }
            });
        }
    });

    // Add doctor change handler to fetch available days
    $('#doctor').change(function() {
        var doctorId = $(this).val();
        if (doctorId) {
            // Fetch doctor details
            $.ajax({
                url: 'fetch_doctor_details.php',
                type: 'POST',
                data: { doctor_id: doctorId },
                dataType: 'json',
                success: function(response) {
                    if (response) {
                        // Update doctor info box
                        $('#doctorName').text('Dr. ' + response.name);
                        $('#doctorSpecialization').text(response.specialization);
                        $('#doctorQualification').text(response.qualification);
                        $('#doctorExperience').text(response.experience + ' years');
                        
                        // Show the doctor info box
                        $('#doctorInfoBox').slideDown(300);
                    }
                },
                error: function() {
                    console.error('Error fetching doctor details');
                }
            });
            
            // Clear time slot
            $('#appointment_time').empty();
            $('#appointment_time').append('<option value="">Select Day first</option>');
            
            // Fetch available days
            $.ajax({
                url: 'fetch_doctor_days.php',
                type: 'POST',
                data: { doctor_id: doctorId },
                dataType: 'json',
                success: function(response) {
                    let daySelect = $("#day");
                    daySelect.html('<option value="">Select Day</option>');
                    
                    if (response && response.length > 0) {
                        response.forEach(function(day) {
                            daySelect.append(`<option value="${day}">${day}</option>`);
                        });
                    } else {
                        daySelect.append('<option value="" disabled>No schedule available</option>');
                    }
                },
                error: function() {
                    alert("Error fetching available days");
                }
            });
        } else {
            // Hide the doctor info box
            $('#doctorInfoBox').slideUp(300);
            
            // Reset day and time dropdowns
            $("#day").html('<option value="">Select Doctor first</option>');
            $("#appointment_time").html('<option value="">Select Day first</option>');
        }
    });
    <?php endif; ?>

    // Time slot fetching for both scenarios
    $('#day').on('change', function() {
        var doctorId = $('#doctor').val();
        var selectedDay = $(this).val();
        
        if (doctorId && selectedDay) {
            $.ajax({
                url: 'fetch_available_slots.php',
                type: 'POST',
                data: { 
                    doctor_id: doctorId,
                    day: selectedDay
                },
                dataType: 'json',
                success: function(response) {
                    $('#appointment_time').empty();
                    $('#appointment_time').append('<option value="">Select Time Slot</option>');
                    
                    if (response && response.length > 0) {
                        response.forEach(function(slot) {
                            var status = slot.available > 0 ? 
                                `${slot.available} slots available` : 
                                'Slot is full';
                            
                            var optionClass = slot.available <= 0 ? 'full-slot' : 'available-slot';
                            
                            $('#appointment_time').append(
                                `<option value="${slot.value}" 
                                 class="${optionClass}"
                                 ${slot.available <= 0 ? 'disabled' : ''}>
                                    ${slot.start_time} - ${slot.end_time} (${status})
                                </option>`
                            );
                        });
                    } else {
                        $('#appointment_time').append('<option value="" disabled>No schedule available for this day</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching schedule:', error);
                    $('#appointment_time').empty();
                    $('#appointment_time').append('<option value="">Error loading schedule</option>');
                }
            });
        } else {
            $('#appointment_time').empty();
            $('#appointment_time').append('<option value="">Select Doctor and Day first</option>');
        }
    });

    // Toggle other patient details section
    const appointmentTypeRadios = document.querySelectorAll('input[name="appointment_type"]');
    const otherPatientDetails = document.getElementById('other_patient_details');

    function toggleOtherDetails() {
        if (document.getElementById('for_others').checked) {
            otherPatientDetails.style.display = 'block';
            // Make other patient fields required
            const otherFields = otherPatientDetails.querySelectorAll('input, select, textarea');
            otherFields.forEach(field => field.setAttribute('required', 'required'));
        } else {
            otherPatientDetails.style.display = 'none';
            // Remove required attribute from other patient fields
            const otherFields = otherPatientDetails.querySelectorAll('input, select, textarea');
            otherFields.forEach(field => field.removeAttribute('required'));
        }
    }

    appointmentTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleOtherDetails);
    });

    // Run on page load in case the default is not 'myself'
    toggleOtherDetails();

    let doctorAvailableDays = [];

    // Helper: Map day names to numbers for Datepicker (0=Sunday, 1=Monday, ...)
    function getDayNumbers(days) {
        const map = {
            'Sunday': 0, 'Monday': 1, 'Tuesday': 2, 'Wednesday': 3,
            'Thursday': 4, 'Friday': 5, 'Saturday': 6
        };
        return days.map(day => map[day]);
    }

    // Update the datepicker with available days
    function updateDatepicker() {
        $('#appointment_date').val('');
        if ($.fn.datepicker) {
            $('#appointment_date').datepicker('destroy');
        }
        if (!doctorAvailableDays.length) {
            $('#datepicker_info').text('No available days for this doctor.');
            return;
        }
        $('#datepicker_info').text('Available days: ' + doctorAvailableDays.join(', '));
        const allowedDays = getDayNumbers(doctorAvailableDays);
        $('#appointment_date').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0,
            maxDate: '+1M',
            beforeShowDay: function(date) {
                return [allowedDays.includes(date.getDay()), ''];
            },
            onSelect: function(dateText, inst) {
                const dateObj = $(this).datepicker('getDate');
                const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'long' });
                $('#selected_day').val(dayName);
                // Set the day dropdown value and trigger change to fetch time slots
                $('#day').val(dayName).trigger('change');
            }
        });
    }

    // Fetch available days when doctor changes
    $('#doctor').change(function() {
        var doctorId = $(this).val();
        if (doctorId) {
            $.ajax({
                url: 'fetch_doctor_days.php',
                type: 'POST',
                data: { doctor_id: doctorId },
                dataType: 'json',
                success: function(response) {
                    doctorAvailableDays = response;
                    updateDatepicker();
                },
                error: function() {
                    doctorAvailableDays = [];
                    updateDatepicker();
                }
            });
        } else {
            doctorAvailableDays = [];
            updateDatepicker();
        }
    });

    // If doctor is pre-selected (from profile), trigger change on page load
    if ($('#doctor').val()) {
        $('#doctor').trigger('change');
        // Add a small delay to ensure the day dropdown is populated
        setTimeout(function() {
            if ($('#day').val()) {
                $('#day').trigger('change');
            }
        }, 500);
    }

    // Handle file uploads
    function handleFileSelect(input, listId) {
        const fileList = document.getElementById(listId);
        const files = input.files;

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <i class="fas ${file.type.includes('pdf') ? 'fa-file-pdf' : 'fa-file-image'}"></i>
                <span>${file.name}</span>
                <i class="fas fa-times remove-file" onclick="removeFile(this)"></i>
            `;
            fileList.appendChild(fileItem);
        }
    }

    function removeFile(element) {
        element.parentElement.remove();
    }

    // Add event listeners for file inputs
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            handleFileSelect(this, this.name === 'prescription_files[]' ? 'prescription-list' : 'report-list');
        });
    });
});
</script>

<style>
    :root {
        --primary-color: #3498db;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --text-color: #2d3748;
        --light-bg: #f8fafc;
        --border-color: #e2e8f0;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        /* font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; */
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        
    }

    body {
        background-color: var(--light-bg);
        color: var(--text-color);
        line-height: 1.5;
    }

    .container {
        /* max-width: 1200px;
        margin: 0 auto;
        padding: 20px; */
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 1.25rem;
    }

    .appointment {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-top: 3rem;
    }

    .section-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .badge {
        display: inline-block;
        padding: 8px 16px;
        background: var(--primary-color);
        color: white;
        border-radius: 20px;
        font-size: 14px;
        margin-bottom: 15px;
    }
    .btn-outline {
    background: transparent;
    border: 2px solid #3498db;
    color: #3498db;
    /* padding: 0.85rem 1.8rem;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-size: 1.05rem;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.1); */
}

.btn-outline:hover {
    background: #3498db;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
}

.btn-outline i {
    font-size: 1.2rem;
}

    .section-header h2 {
        font-size: 32px;
        margin-bottom: 15px;
        font-weight: 600;
        color: var(--text-color);
    }

    .section-header p {
        font-size: 16px;
        color: var(--secondary-color);
        max-width: 600px;
        margin: 0 auto;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-color);
        font-weight: 500;
        font-size: 14px;
    }

    .form-select, .form-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
        background-color: white;
        color: var(--text-color);
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236B7280' viewBox='0 0 16 16'%3E%3Cpath d='M8 11.5l-5-5h10l-5 5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        cursor: pointer;
    }

    .form-select:focus, .form-textarea:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 109, 245, 0.1);
    }

    .form-select option {
        padding: 12px;
        font-size: 15px;
    }

    .form-select option:disabled {
        color: var(--secondary-color);
        background-color: #f3f4f6;
    }

    .form-select option[value=""] {
        color: var(--secondary-color);
    }

    .form-textarea {
        min-height: 100px;
        resize: vertical;
        grid-column: span 3;
        background-image: none;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
        padding: 14px 28px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
        display: block;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 109, 245, 0.2);
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-danger {
        background: #fee2e2;
        color: #ef4444;
        border-left: 4px solid #ef4444;
    }

    .error-message {
        color: #ef4444;
        font-size: 14px;
        margin-top: 5px;
    }

    @media (max-width: 992px) {
        .form-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .form-textarea {
            grid-column: span 2;
        }
    }

    @media (max-width: 576px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .form-textarea {
            grid-column: span 1;
        }
        
        .appointment {
            padding: 20px;
        }
    }

    /* Time slot specific styles */
    #appointment_time {
        background-color: white;
    }

    #appointment_time option {
        padding: 10px 15px;
        border-bottom: 1px solid var(--border-color);
    }

    #appointment_time option:last-child {
        border-bottom: none;
    }

    #appointment_time option:disabled {
        background-color: #f3f4f6;
        color: var(--secondary-color);
        font-style: italic;
    }

    /* Hover effect for options */
    #appointment_time option:not(:disabled):hover {
        background-color: #f8fafc;
    }

    /* Selected option style */
    #appointment_time option:checked {
        background-color: var(--primary-color);
        color: white;
    }

    .appointment-type {
        margin-bottom: 2rem;
        text-align: center;
    }

    .radio-group {
        display: flex;
        gap: 2rem;
        justify-content: center;
        margin-top: 1rem;
    }

    .radio-group input[type="radio"] {
        display: none;
    }

    .radio-group label {
        padding: 0.8rem 2rem;
        border: 2px solid var(--primary-color);
        border-radius: 2rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        color: var(--primary-color);
    }

    .radio-group input[type="radio"]:checked + label {
        background: var(--primary-color);
        color: white;
    }

    .radio-group label:hover {
        background: rgba(79, 109, 245, 0.1);
    }

    #other_patient_details {
        background: #f8fafc;
        padding: 2rem;
        border-radius: 1rem;
        margin-bottom: 2rem;
        border: 1px solid #e2e8f0;
    }

    #other_patient_details .form-grid {
        margin-bottom: 0;
    }

    /* Doctor Info Box Styles */
    .doctor-info-box {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 30px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        animation: slideDown 0.3s ease-out;
    }

    .doctor-info-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .doctor-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 32px;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
    }

    .doctor-details {
        flex: 1;
    }

    .doctor-details h3 {
        color: var(--text-color);
        font-size: 20px;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .doctor-details p {
        color: var(--primary-color);
        font-size: 16px;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .doctor-meta {
        display: flex;
        gap: 20px;
        color: #666;
        font-size: 14px;
    }

    .doctor-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .doctor-meta i {
        color: var(--primary-color);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 576px) {
        .doctor-info-content {
            flex-direction: column;
            text-align: center;
        }
        
        .doctor-meta {
            justify-content: center;
        }
    }

    .form-select.error, .form-textarea.error {
        border-color: #dc2626;
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);
    }

    .form-select.error:focus, .form-textarea.error:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);
    }

    .error-message {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .attachments-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .attachment-item {
        background: var(--bg-light);
        border: 2px dashed var(--border-color);
        border-radius: var(--radius);
        padding: 1rem;
        transition: var(--transition);
    }

    .attachment-item:hover {
        border-color: var(--primary-color);
        background: rgba(37, 99, 235, 0.05);
    }

    .attachment-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        color: var(--text-secondary);
        transition: var(--transition);
    }

    .attachment-label:hover {
        color: var(--primary-color);
    }

    .attachment-label i {
        font-size: 1.5rem;
    }

    .attachment-label input[type="file"] {
        display: none;
    }

    .file-list {
        margin-top: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .file-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        background: var(--bg-white);
        border-radius: var(--radius-sm);
        font-size: 0.875rem;
    }

    .file-item i {
        color: var(--primary-color);
    }

    .file-item .remove-file {
        margin-left: auto;
        color: var(--accent-color);
        cursor: pointer;
        padding: 0.25rem;
        border-radius: var(--radius-sm);
        transition: var(--transition);
    }

    .file-item .remove-file:hover {
        background: rgba(244, 63, 94, 0.1);
    }

    .help-text {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-top: 0.5rem;
    }
</style>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
$(function() {
    // Remove test datepicker initialization
    // $('#appointment_date').datepicker();

    let doctorAvailableDays = [];

    // Helper: Map day names to numbers for Datepicker (0=Sunday, 1=Monday, ...)
    function getDayNumbers(days) {
        const map = {
            'Sunday': 0, 'Monday': 1, 'Tuesday': 2, 'Wednesday': 3,
            'Thursday': 4, 'Friday': 5, 'Saturday': 6
        };
        return days.map(day => map[day]);
    }

    // Update the datepicker with available days
    function updateDatepicker() {
        $('#appointment_date').val('');
        if ($.fn.datepicker) {
            $('#appointment_date').datepicker('destroy');
        }
        if (!doctorAvailableDays.length) {
            $('#datepicker_info').text('No available days for this doctor.');
            return;
        }
        $('#datepicker_info').text('Available days: ' + doctorAvailableDays.join(', '));
        const allowedDays = getDayNumbers(doctorAvailableDays);
        $('#appointment_date').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0,
            maxDate: '+1M',
            beforeShowDay: function(date) {
                return [allowedDays.includes(date.getDay()), ''];
            },
            onSelect: function(dateText, inst) {
                const dateObj = $(this).datepicker('getDate');
                const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'long' });
                $('#selected_day').val(dayName);
                // Set the day dropdown value and trigger change to fetch time slots
                $('#day').val(dayName).trigger('change');
            }
        });
    }

    // Fetch available days when doctor changes
    $('#doctor').change(function() {
        var doctorId = $(this).val();
        if (doctorId) {
            $.ajax({
                url: 'fetch_doctor_days.php',
                type: 'POST',
                data: { doctor_id: doctorId },
                dataType: 'json',
                success: function(response) {
                    doctorAvailableDays = response;
                    updateDatepicker();
                },
                error: function() {
                    doctorAvailableDays = [];
                    updateDatepicker();
                }
            });
        } else {
            doctorAvailableDays = [];
            updateDatepicker();
        }
    });

    // If doctor is pre-selected (from profile), trigger change on page load
    if ($('#doctor').val()) {
        $('#doctor').trigger('change');
        // Add a small delay to ensure the day dropdown is populated
        setTimeout(function() {
            if ($('#day').val()) {
                $('#day').trigger('change');
            }
        }, 500);
    }
});
</script>

</body>
</html>