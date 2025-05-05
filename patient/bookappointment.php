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

<div class="container">
    <div class="appointment">
        <div class="section-header">
            <div class="badge">Book Appointment</div>
            <h2>Schedule a Visit With Our Specialists</h2>
            <p>Choose your preferred location, hospital, department, and doctor, and we'll take care of the rest.</p>
        </div>
        
        <form id="appointmentForm" method="POST">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-grid">
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
                    <span class="error-message" id="provinceError"></span>
                </div>

                <div class="form-group">
                    <label for="district">District</label>
                    <select id="district" name="district" class="form-select" required>
                        <option value="" disabled selected>Select Province first</option>
                    </select>
                    <span class="error-message" id="districtError"></span>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <select id="city" name="city" class="form-select" required>
                        <option value="" disabled selected>Select District first</option>
                    </select>
                    <span class="error-message" id="cityError"></span>
                </div>

                <div class="form-group">
                    <label for="hospital">Hospital</label>
                    <select id="hospital" name="hospital_id" class="form-select" required>
                        <option value="" disabled selected>Select City first</option>
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

                <div class="form-group">
                    <label for="reason">Reason for Visit</label>
                    <textarea id="reason" name="reason" class="form-textarea" placeholder="Please briefly describe your symptoms or reason for the appointment" required></textarea>
                    <span class="error-message" id="reasonError"></span>
                </div>
            </div>

            <button type="submit" class="btn-primary">Book Appointment</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Province, District, and City data
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
        "Pokhara": ["Pokhara", "Lekhnath", "Bagar", "Hemja", "Sarangkot", "Lakeside", "Bindyabasini", "Matepani", "Pumdibhumdi", "Srijana Chowk", "Mahendrapool", "Chipledhunga"],
        "Biratnagar": ["Biratnagar", "Biratchowk", "Letang", "Urlabari", "Rangeli", "Sundarharaincha", "Belbari", "Damak", "Itahari", "Dharan"],
        "Butwal": ["Butwal", "Tilottama", "Devdaha", "Lumbini", "Siddharthanagar", "Manigram", "Sainamaina", "Tansen", "Bhairahawa"],
        "Nepalgunj": ["Nepalgunj", "Kohalpur", "Khajura", "Narainapur", "Rapti Sonari", "Gulariya", "Rajapur"],
        "Dhangadhi": ["Dhangadhi", "Tikapur", "Lamki", "Ghodaghodi", "Attariya", "Gauriganga", "Kailali", "Mahendranagar"],
        "Surkhet": ["Surkhet", "Birendranagar", "Chhinchu", "Gurbhakot", "Panchpuri", "Bheriganga", "Lekbesi"],
        "Jumla": ["Jumla", "Chandannath", "Tatopani", "Patarasi", "Sinja", "Hima"],
        "Dhankuta": ["Dhankuta", "Pakhribas", "Mahalaxmi", "Pakhribas", "Khalsa Chhintang Sahidbhumi"],
        "Ilam": ["Ilam", "Pashupatinagar", "Suryodaya", "Mai", "Mangalbare", "Phakphok"],
        "Jhapa": ["Bhadrapur", "Damak", "Mechinagar", "Birtamod", "Arjundhara", "Kankai", "Gauradaha"],
        "Morang": ["Biratnagar", "Biratchowk", "Letang", "Urlabari", "Rangeli", "Sundarharaincha", "Belbari", "Pathari", "Budhiganga"],
        "Sunsari": ["Itahari", "Dharan", "Inaruwa", "Duhabi", "Ramdhuni", "Barahachhetra", "Dewanganj", "Simariya"],
        "Chitwan": ["Bharatpur", "Ratnanagar", "Kalika", "Khairahani", "Madi", "Rapti", "Ichchhakamana"],
        "Kaski": ["Pokhara", "Lekhnath", "Annapurna", "Machhapuchhre", "Madi", "Rupa"],
        "Rupandehi": ["Butwal", "Tilottama", "Devdaha", "Lumbini", "Siddharthanagar", "Sainamaina", "Marchawari", "Kotahimai"],
        "Kapilvastu": ["Taulihawa", "Buddhabhumi", "Kapilvastu", "Maharajgunj", "Yashodhara", "Shivaraj", "Banganga"],
        "Banke": ["Nepalgunj", "Kohalpur", "Narainapur", "Rapti Sonari", "Khajura", "Janaki", "Duduwa"],
        "Bardiya": ["Gulariya", "Rajapur", "Madhuwan", "Thakurbaba", "Barbardiya", "Bansgadhi"],
        "Dang": ["Ghorahi", "Tulsipur", "Lamahi", "Bangalachuli", "Shantinagar", "Rapti", "Gadhawa"],
        "Kailali": ["Dhangadhi", "Tikapur", "Lamki", "Ghodaghodi", "Attariya", "Gauriganga", "Bhajani"],
        "Kanchanpur": ["Mahendranagar", "Bhimdatta", "Punarbas", "Bedkot", "Shuklaphanta", "Belauri", "Krishnapur"]
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
        $("#date").html('<option value="" disabled selected>Select doctor first</option>');
        $("#time").html('<option value="" disabled selected>Select date first</option>');
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
        $("#date").html('<option value="" disabled selected>Select doctor first</option>');
        $("#time").html('<option value="" disabled selected>Select date first</option>');
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

                    if (data.error) {
                        alert(data.error);
                    } else {
                        $.each(data, function(index, hospital) {
                            hospitalSelect.append(`<option value="${hospital.id}">${hospital.name}</option>`);
                        });
                    }
                    
                    // Reset dependent dropdowns
                    $("#department").html('<option value="" disabled selected>Select hospital first</option>');
                    $("#doctor").html('<option value="" disabled selected>Select department first</option>');
                    $("#date").html('<option value="" disabled selected>Select doctor first</option>');
                    $("#time").html('<option value="" disabled selected>Select date first</option>');
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching hospitals:", error);
                    alert("Error fetching hospitals. Please try again.");
                }
            });
        }
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
<style>
    :root {
        --primary-color: #4f6df5;
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
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: var(--light-bg);
        color: var(--text-color);
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
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
    }

    .form-select:focus, .form-textarea:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 109, 245, 0.1);
    }

    .form-textarea {
        min-height: 100px;
        resize: vertical;
        grid-column: span 3;
    }

    .btn-primary {
        /* background: linear-gradient(135deg, var(--primary-color), #4f6df5); */
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
</style>

</body>
</html>