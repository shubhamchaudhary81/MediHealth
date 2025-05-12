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
}

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
        // Get doctor's schedule for the day
        $day_of_week = date('l', strtotime($appointment_date));
        $schedule_query = "SELECT max_patients 
                          FROM doctor_schedule 
                          WHERE doctor_id = ? AND day = ?";
        
        $stmt = $conn->prepare($schedule_query);
        if ($stmt === false) {
            $errors['db_error'] = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $doctor_id, $day_of_week);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $errors['time_error'] = "Doctor is not available on this day.";
            } else {
                $schedule = $result->fetch_assoc();
                $max_patients = $schedule['max_patients'];
                
                // Check current bookings for this time slot
                $check_query = "SELECT COUNT(*) as count 
                               FROM appointments 
                               WHERE doctor_id = ? 
                               AND appointment_date = ? 
                               AND appointment_time = ? 
                               AND status != 'cancelled'";
                
                $stmt = $conn->prepare($check_query);
                $stmt->bind_param("sss", $doctor_id, $appointment_date, $appointment_time);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row['count'] >= $max_patients) {
                    $errors['time_error'] = "This time slot is already full. Please select another time.";
                }
            }
            $stmt->close();
        }
    }

    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert the appointment
            $insertQuery = "INSERT INTO appointments 
                (patient_id, hospital_id, department_id, doctor_id, appointment_date, appointment_time, reason, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled')";

            $stmt = $conn->prepare($insertQuery);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

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

            if (!$stmt->execute()) {
                throw new Exception("Error inserting appointment: " . $stmt->error);
            }

            // Commit transaction
            $conn->commit();
            
            // Clear the session data if it exists
            if (isset($_SESSION['doctor_booking_data'])) {
                unset($_SESSION['doctor_booking_data']);
            }
            
            $_SESSION['success_message'] = "Appointment booked successfully!";
            header("Location: patientdash.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors['db_error'] = "Error: " . $e->getMessage();
        }
    }
}

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
                    <label for="day">Appointment Day</label>
                    <select name="appointment_date" id="day" class="form-select" required>
                        <option value="">Select Day</option>
                        <?php 
                        if ($is_from_doctor_profile) {
                            // Fetch available days for the pre-selected doctor
                            $days_query = "SELECT DISTINCT day FROM doctor_schedule WHERE doctor_id = ? ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
                            $stmt = $conn->prepare($days_query);
                            $stmt->bind_param("s", $doctor_info['doctor_id']);
                            $stmt->execute();
                            $days_result = $stmt->get_result();
                            
                            while ($day = $days_result->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($day['day']) . '">' . htmlspecialchars($day['day']) . '</option>';
                            }
                            $stmt->close();
                        }
                        ?>
                    </select>
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
            </div>

            <button type="submit" class="btn-primary">Book Appointment</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
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
        /* font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; */
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        
    }

    body {
        background-color: var(--light-bg);
        color: var(--text-color);
        line-height: 1.6;
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
</style>

</body>
</html>