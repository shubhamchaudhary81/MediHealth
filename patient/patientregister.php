<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
include_once('../config/configdatabase.php');

// Initialize errors array
$errors = array();

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $dob = trim($_POST['dob']);
    $number = trim($_POST['number']);
    $zone = trim($_POST['zone']);
    $district = trim($_POST['district']);
    $city = trim($_POST['city']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $gender = isset($_POST['gender']) ? $_POST['gender'] : 'Male';
    $bloodgroup = trim($_POST['bloodgroup']);

    // First Name Validation
    // if (empty($firstName) || !preg_match("/^[a-zA-Z]+$/", $firstName)) {
    //     $errors['firstName'] = "Valid first name is required.";
    // }
    
    // Last Name Validation
    // if (empty($lastName) || !preg_match("/^[a-zA-Z]+$/", $lastName)) {
    //     $errors['lastName'] = "Valid last name is required.";
    // }

    // First Name Validation

     // Generate a unique 6-digit user ID
     $patientid = mt_rand(100000, 999999);

      // Name Validation
      $firstName = trim($firstName);
      if (!(strlen($firstName) > 0)) {
          $errors['firstName_error'] = "FirstName is required";
      } else {
          $pattern = "/^[a-zA-Z ]+$/"; // it includes more than one alphabet with space
          if (!preg_match($pattern, $firstName)) {
              $errors['firstName_error'] = "FirstName can't contain digits and special characters";
          }
      }

      // Last Name Validation
        // Name Validation
    $lastName = trim($lastName);
    if (!(strlen($lastName) > 0)) {
        $errors['lastName_error'] = "LastName is required";
    } else {
        $pattern = "/^[a-zA-Z ]+$/"; // it includes more than one alphabet with space
        if (!preg_match($pattern, $lastName)) {
            $errors['lastName_error'] = "LastName can't contain digits and special characters";
        }
    }

  
    
    // Email Validation
    // if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //     $errors['email'] = "Invalid email format.";
    // }

    // Email validation
    $email = trim($email);
    if (!(strlen($email) > 0)) {
        $errors['email_error'] = "Email can't be blank";
    } else {
        $pattern = "/^[a-z0-9\.-_]+@[a-z]+\.[a-z]+(\.[a-z]{2})?$/";
        if (!preg_match($pattern, $email)) {
            $errors['email_error'] = "Email address is not valid";
        }
    }
    
    // Phone Number Validation
    // if (!preg_match("/^9[87][0-9]{8}$/", $phone)) {
    //     $errors['phone'] = "Invalid phone number.";
    // }
    
      // Number validation
      $number = trim($number);
      $number_pattern = "/^9[87][0-9]{8}$/";
      if (!(strlen($number) > 0)) {
          $errors['number_error'] = "Phone number is required.";
      } else if (!preg_match($number_pattern, $number)) {
          $errors['number_error'] = "Phone number is not valid.";
      }

    // Password Validation
    // if (strlen($password) < 8) {
    //     $errors['password'] = "Password must be at least 8 characters.";
    // } elseif ($password !== $confirm_password) {
    //     $errors['confirm_password'] = "Passwords do not match.";
    // }
    
        // Password validation
    $password = trim($password);
    if (!(strlen($password) > 0)) {
        $errors['password_error'] = "Password is required";
    } else if (strlen($password) <= 8) {
        $errors['password_error'] = "Password should be greater than 8 digits";
    } else {
        $pattern = "/^[a-zA-Z0-9@\.#]+$/";
        if (!preg_match($pattern, $password)) {
            $errors['password_error'] = "Password is not valid";
        }
    }

    // Confirm password validation
    $confirm_password = trim($confirm_password);
    if (!(strlen($confirm_password) > 0)) {
        $errors['confirm_password_error'] = "Re-enter your password is required";
    } else if ($confirm_password !== $password) {
        $errors['confirm_password_error'] = "Confirm password and password should be the same";
    }


    // Address Validation
    if (empty($zone) || empty($district) || empty($city)) {
        $errors['zone'] = "Zone is required.";
        $errors['district'] = "district is required.";
        $errors['city'] = "city is required.";
    }
    
    // If no errors, insert data into patients table
//     if (empty($errors)) {
//         // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
//         // $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, email, dob, number, zone, district, city, password, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
//         // $stmt->bind_param("ssssssssss", $firstName, $lastName, $email, $dob, $number, $zone, $district, $city, $hashed_password, $gender);
        
//          $sql = "INSERT INTO patients (userid, first_name, last_name, email, dob, number, zone, district, city, password, gender, created_at) 
//         VALUES ($userid, $firstName, $lastName, $email, $dob, $number, $zone, $district, $city, password, $gender, NOW())";

// if (mysqli_query($conn, $sql)) {
//   // Table created successfully
// } else {
//   echo "Error Creating table: " . mysqli_error($conn);
// }
//         $sql->close();
//     }

if (empty($errors)) {
  // Hash the password before storing
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // Prepare statement
  $stmt = $conn->prepare("INSERT INTO patients 
      (patientid, first_name, last_name, email, dob, number, zone, district, city, password, gender, bloodgroup, created_at) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

  if ($stmt === false) {
      die("SQL Prepare Error: " . $conn->error);
  }

  // Bind parameters
  $stmt->bind_param("ssssssssssss", $patientid, $firstName, $lastName, $email, $dob, $number, $zone, $district, $city, $hashed_password, $gender, $bloodgroup);

  // Execute statement
  if ($stmt->execute()) {

    $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'medihealth628@gmail.com'; // Replace with your email
                $mail->Password = 'esme zlrl slig ujcm'; // Replace with your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
    
                // Recipients
                $mail->setFrom('medihealth628@gmail.com', 'MediHealth');
                $mail->addAddress($email, $name);
    
                // // Content
                // $mail->isHTML(true);
                // $mail->Subject = 'Welcome to MediHealth - Your Registration Details';
                // $mail->Body = "<h3>Dear $name,</h3><p>Thank you for registering at MediHealth.</p>
                //               <p>Your User ID: <b>$user_id</b></p>
                //               <p>Your Password: <b>$password</b></p>
                //               <p>Please keep this information safe.</p>
                //               <p>Best regards,<br>MediHealth Team</p>";

                 // Embed image using cid (Content-ID)
    // $mail->AddEmbeddedImage('/path/to/logo.png', 'logo_cid'); // Replace with actual path

    $mail->isHTML(true);
    $mail->Subject = '🎉 Welcome to MediHealth – Your Healthcare Companion!';

    $mail->Body = "
        <div style='text-align: center; font-family: Arial, sans-serif;'>
            <img src='cid:logo_cid' alt='MediHealth Logo' style='width: 150px; margin-bottom: 20px;'>
            <h2 style='color: #2d89ef;'>Welcome to MediHealth, $firstName!</h2>
            <p style='font-size: 16px; color: #555;'>We are excited to have you on board. Your health is our priority, and we're here to support you on your journey to wellness.</p>
            
            <div style='background-color: #f2f2f2; padding: 15px; border-radius: 10px; display: inline-block;'>
                <p><strong>Your User ID:</strong> <span style='color: #2d89ef;'>$patientid</span></p>
                <p><strong>Your Password:</strong> <span style='color: #2d89ef;'>$password</span></p>
            </div>

            <p style='margin-top: 20px; color: #777;'>If you have any questions, feel free to reach out to our support team.</p>
            <p><strong>Stay healthy, stay safe!</strong></p>
            <p><em>Best regards,<br>The MediHealth Team</em></p>
        </div>
    ";

    
                $mail->send();
                echo "Registration successful! Please check your email for login details.";
            } catch (Exception $e) {
                echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error: " . $stmt->error;
        }

      echo "Patient registered successfully.";
      header("Location: patientlogin.php"); // Redirect on success
      exit();
  } else {
      // echo "Error: " . $stmt->error;
  }

  // Close statement
  // $stmt->close();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediPoint - Patient Registration</title>
  <!-- <link rel="stylesheet" href="../css/patientregister.css"> -->
  <link rel="stylesheet" href="../css/register.css">
   
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-icons@latest/dist/umd/lucide.min.js"> -->
  <!-- <script src="https://unpkg.com/lucide@latest"></script> -->
  <style>
    .form-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .form-group label {
        margin-bottom: 0;
        font-size: 14px;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    
    .form-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }
    
    .form-select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }
    
    .gender-group {
        display: flex;
        gap: 20px;
        align-items: center;
    }
    
    .gender-group label {
        margin-right: 10px;
    }
    
    .blood-group-select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }

    .password-input-wrapper {
        position: relative;
        width: 100%;
    }

    .password-input-wrapper input {
        width: 100%;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 20px 0;
    }

    .form-check label {
        margin-bottom: 0;
    }

    .errormsg {
        color: red;
        font-size: 12px;
        margin-top: 5px;
    }

    .form-group {
        position: relative;
    }

    .auth-form {
        max-width: 100%;
    }
  </style>
</head>
<body>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-content">
        <div class="auth-header">
          <a href="index.html" class="logo">
            <div class="logo-icon">
              <i data-lucide="file-text"></i>
            </div>
            <span>MediHealth</span>
          </a>
        </div>

        <div class="auth-form-container">
          <div class="auth-welcome">
            <h1>Create an account</h1>
            <p>Please enter your details to register</p>
          </div>

          <form class="auth-form" method="POST">
            <div class="form-grid">
              <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="firstName" class="form-input" placeholder="Enter your first name" value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>" required>
                <span class="errormsg">
                    <?php
                        if (isset($errors['firstName_error'])) {
                            echo $errors['firstName_error'];
                        }
                        ?>
                    </span>
              </div>
              
              <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" class="form-input" placeholder="Enter your last name" value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>" required>
                <span class="errormsg">
                    <?php
                        if (isset($errors['lastName_error'])) {
                            echo $errors['lastName_error'];
                        }
                        ?>
                    </span>
              </div>

              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <span class="errormsg">
                    <?php
                        if (isset($errors['email_error'])) {
                            echo $errors['email_error'];
                        }
                        ?>
                    </span>
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" class="form-input" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>" required>
                <span class="errormsg">
                    <?php
                        if (isset($errors['dob_error'])) {
                            echo $errors['dob_error'];
                        }
                        ?>
                    </span>
              </div>

              <div class="form-group">
                <label for="number">Phone Number</label>
                <input type="tel" id="number" name="number" class="form-input" placeholder="Enter your phone number" value="<?php echo isset($_POST['number']) ? htmlspecialchars($_POST['number']) : ''; ?>" required>
                <span class="errormsg">
                    <?php
                        if (isset($errors['number_error'])) {
                            echo $errors['number_error'];
                        }
                        ?>
                    </span>
              </div>

              <div class="form-group">
                <label for="bloodgroup">Blood Group</label>
                <select id="bloodgroup" name="bloodgroup" class="blood-group-select" required>
                  <option value="">Select Blood Group</option>
                  <option value="A+" <?php echo (isset($_POST['bloodgroup']) && $_POST['bloodgroup'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                  <option value="A-" <?php echo (isset($_POST['bloodgroup']) && $_POST['bloodgroup'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                  <option value="B+" <?php echo (isset($_POST['bloodgroup']) && $_POST['bloodgroup'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                  <option value="B-" <?php echo (isset($_POST['bloodgroup']) && $_POST['bloodgroup'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                  <option value="AB+" <?php echo (isset($_POST['bloodgroup']) && $_POST['bloodgroup'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                  <option value="AB-" <?php echo (isset($_POST['bloodgroup']) && $_POST['bloodgroup'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                  <option value="O+" <?php echo (isset($_POST['bloodgroup']) && $_POST['bloodgroup'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                  <option value="O-" <?php echo (isset($_POST['bloodgroup']) && $_POST['bloodgroup'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                </select>
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label for="province">Province</label>
                <select id="province" name="province" class="form-select" required>
                  <option value="">Select Province</option>
                  <option value="Province 1" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Province 1') ? 'selected' : ''; ?>>Province 1</option>
                  <option value="Madhesh" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Madhesh') ? 'selected' : ''; ?>>Madhesh</option>
                  <option value="Bagmati" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Bagmati') ? 'selected' : ''; ?>>Bagmati</option>
                  <option value="Gandaki" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Gandaki') ? 'selected' : ''; ?>>Gandaki</option>
                  <option value="Lumbini" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Lumbini') ? 'selected' : ''; ?>>Lumbini</option>
                  <option value="Karnali" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Karnali') ? 'selected' : ''; ?>>Karnali</option>
                  <option value="Sudurpashchim" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Sudurpashchim') ? 'selected' : ''; ?>>Sudurpashchim</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="district">District</label>
                <select id="district" name="district" class="form-select" required>
                  <option value="">Select District</option>
                  <?php
                  if (isset($_POST['district'])) {
                      echo '<option value="' . htmlspecialchars($_POST['district']) . '" selected>' . htmlspecialchars($_POST['district']) . '</option>';
                  }
                  ?>
                </select>
              </div>
              
              <div class="form-group">
                <label for="city">City</label>
                <select id="city" name="city" class="form-select" required>
                  <option value="">Select City</option>
                  <?php
                  if (isset($_POST['city'])) {
                      echo '<option value="' . htmlspecialchars($_POST['city']) . '" selected>' . htmlspecialchars($_POST['city']) . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-wrapper">
                  <input type="password" id="password" name="password" class="form-input" placeholder="Create a password" required>
                  <button type="button" class="password-toggle">
                    <i data-lucide="eye"></i>
                  </button>
                </div>
                <span class="errormsg">
                    <?php
                        if (isset($errors['password_error'])) {
                            echo $errors['password_error'];
                        }
                        ?>
                    </span>
              </div>

              <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="password-input-wrapper">
                  <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Confirm your password" required>
                  <button type="button" class="password-toggle">
                    <i data-lucide="eye"></i>
                  </button>
                </div>
                <span class="errormsg">
                    <?php
                        if (isset($errors['confirm_password_error'])) {
                            echo $errors['confirm_password_error'];
                        }
                        ?>
                    </span>
              </div>
            </div>

            <div class="form-group">
              <label>Gender</label>
              <div class="gender-group">
                <input type="radio" id="male" name="gender" value="Male" <?php echo (!isset($_POST['gender']) || $_POST['gender'] == 'Male') ? 'checked' : ''; ?>>
                <label for="male">Male</label>
                <input type="radio" id="female" name="gender" value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'checked' : ''; ?>>
                <label for="female">Female</label>
                <input type="radio" id="other" name="gender" value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'checked' : ''; ?>>
                <label for="other">Other</label>
              </div>
            </div>

            <div class="form-check">
              <input type="checkbox" id="terms" required>
              <label for="terms">I agree to the <a href="terms_and_conditions.pdf" target="_blank">Terms of Service</a> and <a href="privacy_policy.pdf" target="_blank">Privacy Policy</a></label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Create Account</button>
            
           
          </form>

          <div class="auth-footer">
            <p>Already have an account? <a href="patientlogin.php">Sign in</a></p>
          </div>
        </div>
      </div>
      
      <div class="auth-image">
        <div class="image-overlay"></div>
        <div class="auth-quote">
          <blockquote>
            "The good physician treats the disease; the great physician treats the patient who has the disease."
          </blockquote>
          <cite>— William Osler</cite>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Initialize Lucide icons
    lucide.createIcons();
    
    // Password visibility toggle for both password fields
    document.querySelectorAll('.password-toggle').forEach(function(button) {
      button.addEventListener('click', function() {
        const passwordInput = this.parentElement.querySelector('input');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          icon.setAttribute('data-lucide', 'eye-off');
        } else {
          passwordInput.type = 'password';
          icon.setAttribute('data-lucide', 'eye');
        }
        
        lucide.createIcons();
      });
    });

    // Province, District, and City Selection
    const districtsByProvince = {
      "Province 1": ["Bhojpur", "Dhankuta", "Ilam", "Jhapa", "Khotang", "Morang", "Okhaldhunga", "Panchthar", "Sankhuwasabha", "Solukhumbu", "Sunsari", "Taplejung", "Terhathum", "Udayapur"],
      "Madhesh": ["Bara", "Dhanusha", "Mahottari", "Parsa", "Rautahat", "Saptari", "Sarlahi", "Siraha"],
      "Bagmati": ["Bhaktapur", "Chitwan", "Dhading", "Dolakha", "Kathmandu", "Kavrepalanchok", "Lalitpur", "Makwanpur", "Nuwakot", "Ramechhap", "Rasuwa", "Sindhuli", "Sindhupalchok"],
      "Gandaki": ["Baglung", "Gorkha", "Kaski", "Lamjung", "Manang", "Mustang", "Myagdi", "Nawalpur", "Parbat", "Syangja", "Tanahu"],
      "Lumbini": ["Arghakhanchi", "Banke", "Bardiya", "Dang", "Eastern Rukum", "Gulmi", "Kapilvastu", "Palpa", "Parasi", "Pyuthan", "Rolpa", "Rupandehi"],
      "Karnali": ["Dailekh", "Dolpa", "Humla", "Jajarkot", "Jumla", "Kalikot", "Mugu", "Salyan", "Surkhet", "Western Rukum"],
      "Sudurpashchim": ["Achham", "Baitadi", "Bajhang", "Bajura", "Dadeldhura", "Darchula", "Doti", "Kailali", "Kanchanpur"]
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

    // Function to update district options
    function updateDistricts() {
      const provinceSelect = document.getElementById('province');
      const districtSelect = document.getElementById('district');
      const citySelect = document.getElementById('city');
      
      if (!provinceSelect || !districtSelect || !citySelect) {
        console.error('Required select elements not found');
        return;
      }

      // Clear previous options
      districtSelect.innerHTML = '<option value="">Select District</option>';
      citySelect.innerHTML = '<option value="">Select City</option>';
      
      const selectedProvince = provinceSelect.value;
      if (selectedProvince && districtsByProvince[selectedProvince]) {
        districtsByProvince[selectedProvince].forEach(district => {
          const option = document.createElement('option');
          option.value = district;
          option.textContent = district;
          districtSelect.appendChild(option);
        });
      }
    }

    // Function to update city options
    function updateCities() {
      const districtSelect = document.getElementById('district');
      const citySelect = document.getElementById('city');
      
      if (!districtSelect || !citySelect) {
        console.error('Required select elements not found');
        return;
      }

      // Clear previous options
      citySelect.innerHTML = '<option value="">Select City</option>';
      
      const selectedDistrict = districtSelect.value;
      if (selectedDistrict && citiesByDistrict[selectedDistrict]) {
        citiesByDistrict[selectedDistrict].forEach(city => {
          const option = document.createElement('option');
          option.value = city;
          option.textContent = city;
          citySelect.appendChild(option);
        });
      }
    }

    // Wait for the DOM to be fully loaded
    window.addEventListener('load', function() {
      // Get the select elements
      const provinceSelect = document.getElementById('province');
      const districtSelect = document.getElementById('district');
      
      if (!provinceSelect || !districtSelect) {
        console.error('Required select elements not found');
        return;
      }

      // Add event listeners
      provinceSelect.addEventListener('change', updateDistricts);
      districtSelect.addEventListener('change', updateCities);

      // Initialize selections if values are already selected
      if (provinceSelect.value) {
        updateDistricts();
        if (districtSelect.value) {
          updateCities();
        }
      }
    });
  </script>
  


</body>
</html>