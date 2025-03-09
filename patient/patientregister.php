<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
include_once('../config/database.php');

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
     $userid = mt_rand(100000, 999999);

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
      (userid, first_name, last_name, email, dob, number, zone, district, city, password, gender, created_at) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

  if ($stmt === false) {
      die("SQL Prepare Error: " . $conn->error);
  }

  // Bind parameters
  $stmt->bind_param("sssssssssss", $userid, $firstName, $lastName, $email, $dob, $number, $zone, $district, $city, $hashed_password, $gender);

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
                <p><strong>Your User ID:</strong> <span style='color: #2d89ef;'>$userid</span></p>
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
      echo "Error: " . $stmt->error;
  }

  // Close statement
  $stmt->close();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediPoint - Patient Registration</title>
  <link rel="stylesheet" href="../css/patientregister.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-icons@latest/dist/umd/lucide.min.js"> -->
  <!-- <script src="https://unpkg.com/lucide@latest"></script> -->
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
                <input type="text" id="firstName" name="firstName" class="form-input" placeholder="Enter your first name" required>
                <span class="errormsg" style="color: red;">
                    <?php
                        if (isset($errors['firstName_error'])) {
                            echo $errors['firstName_error'];
                        }
                        ?>
                    </span>
              </div>
              
              <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="lastName" class="form-input" placeholder="Enter your last name" required>
                <span class="errormsg" style="color: red;">
                    <?php
                        if (isset($errors['lastName_error'])) {
                            echo $errors['lastName_error'];
                        }
                        ?>
                    </span>
              </div>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required>
              <span class="errormsg" style="color: red;">
                    <?php
                        if (isset($errors['email_error'])) {
                            echo $errors['email_error'];
                        }
                        ?>
                    </span>
            </div>

            <div class="form-group">
              <label for="dob">Date of Birth</label>
              <input type="date" id="dob" name="dob" class="form-input" required>
              <span class="errormsg" style="color: red;">
                    <?php
                        if (isset($errors['dob_error'])) {
                            echo $errors['dob_error'];
                        }
                        ?>
                    </span>
            </div>

            <div class="form-group">
              <label for="numer">Phone Number</label>
              <input type="number" id="number" name="number" class="form-input" placeholder="Enter your phone number" required>
              <span class="errormsg" style="color: red;">
                    <?php
                        if (isset($errors['number_error'])) {
                            echo $errors['number_error'];
                        }
                        ?>
                    </span>
            </div>
              <span style="font-weight: bold;">Full Address:</span><br>
            <div class="form-group">
              <label for="zone">Zone</label>
              <select id="zone" name="zone" class="form-input" required>
                <option value="">Select Zone</option>
                <option value="Bagmati">Bagmati</option>
                <option value="Gandaki">Gandaki</option>
                <option value="Koshi">Koshi</option>
                <option value="Lumbini">Lumbini</option>
                <option value="Madhesh">Madhesh</option>
                <option value="Karnali">Karnali</option>
                <option value="Sudurpashchim">Sudurpashchim</option>
              </select>
              <span class="errormsg" style="color: red;">
                    <?php
                        if (isset($errors['zone_error'])) {
                            echo $errors['zone_error'];
                        }
                        ?>
                    </span>
            </div>
            
            <div class="form-group">
              <label for="district">District</label>
              <select id="district" name="district" class="form-input" required>
                <option value="">Select District</option>
              </select>
              <span class="errormsg" style="color: red;">
                    <?php
                        if (isset($errors['district_error'])) {
                            echo $errors['zone_error'];
                        }
                        ?>
                    </span>
            </div>
            
            <div class="form-group">
              <label for="city">City</label>
              <select id="city" name="city" class="form-input" required>
                <option value="">Select City</option>
              </select>
              <span class="errormsg" style="color: red;">
                    <?php
                        if (isset($errors['city_error'])) {
                            echo $errors['city_error'];
                        }
                        ?>
                    </span>
            </div>
            
            <script>
              const districtsByZone = {
                "Bagmati": ["Kathmandu", "Lalitpur", "Bhaktapur", "Sindhupalchowk"],
                "Gandaki": ["Pokhara", "Gorkha", "Lamjung", "Tanahun"],
                "Koshi": ["Biratnagar", "Jhapa", "Morang", "Sunsari"],
                "Lumbini": ["Butwal", "Kapilvastu", "Rupandehi", "Palpa"],
                "Madhesh": ["Janakpur", "Parsa", "Bara", "Dhanusha"],
                "Karnali": ["Surkhet", "Jumla", "Mugu", "Dailekh"],
                "Sudurpashchim": ["Dhangadhi", "Kailali", "Kanchanpur", "Dadeldhura"]
              };
            
              const citiesByDistrict = {
                "Kathmandu": ["Kathmandu", "Kirtipur", "Tokha"],
                "Lalitpur": ["Patan", "Godawari", "Lubhu"],
                "Bhaktapur": ["Bhaktapur", "Thimi", "Suryabinayak"],
                "Pokhara": ["Pokhara", "Lekhnath"],
                "Biratnagar": ["Biratnagar", "Itahari"],
                "Butwal": ["Butwal", "Tilottama"],
                "Janakpur": ["Janakpur", "Mahendranagar"],
                "Surkhet": ["Surkhet", "Birendranagar"],
                "Dhangadhi": ["Dhangadhi", "Tikapur"]
              };
            
              document.getElementById('zone').addEventListener('change', function() {
                const districtSelect = document.getElementById('district');
                const citySelect = document.getElementById('city');
                districtSelect.innerHTML = '<option value="">Select District</option>';
                citySelect.innerHTML = '<option value="">Select City</option>';
                
                const selectedZone = this.value;
                if (selectedZone && districtsByZone[selectedZone]) {
                  districtsByZone[selectedZone].forEach(district => {
                    let option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    districtSelect.appendChild(option);
                  });
                }
              });
            
              document.getElementById('district').addEventListener('change', function() {
                const citySelect = document.getElementById('city');
                citySelect.innerHTML = '<option value="">Select City</option>';
                
                const selectedDistrict = this.value;
                if (selectedDistrict && citiesByDistrict[selectedDistrict]) {
                  citiesByDistrict[selectedDistrict].forEach(city => {
                    let option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                  });
                }
              });
            </script>
            <div class="form-group">
              <label for="password">Password</label>
              <div class="password-input-wrapper">
                <input type="password" id="password" name="password" class="form-input" placeholder="Create a password" required>
                <button type="button" class="password-toggle">
                  <i data-lucide="eye"></i>
                </button>
              </div>
              <span class="errormsg" style="color: red;">
                    <?php
                        if (isset($errors['password_error'])) {
                            echo $errors['password_error'];
                        }
                        ?>
                    </span>
            </div>

            <div class="form-group">
              <label for="confirmPassword">Confirm Password</label>
              <div class="password-input-wrapper">
                <input type="password" id="confirm_Password" name="confirm_password" class="form-input" placeholder="Confirm your password" required>
                <button type="button" class="password-toggle">
                  <i data-lucide="eye"></i>
                </button>
              </div>
              <span class="errormsg" style="color: red;">
                    <?php
                        if (isset($errors['confirm_password_error'])) {
                            echo $errors['confirm_password_error'];
                        }
                        ?>
                    </span>
            </div>
              
            <div class="form-group">
    <label>Gender</label><br>
    <input type="radio" id="male" name="gender" value="Male" checked>
    <label for="male">Male</label>
    <input type="radio" id="female" name="gender" value="Female">
    <label for="female">Female</label>
    <input type="radio" id="other" name="gender" value="Other">
    <label for="other">Other</label>
</div>

            

            <div class="form-check">
              <input type="checkbox" id="terms" required>
              <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Create Account</button>
            
           
          </form>

          <div class="auth-footer">
            <p>Already have an account? <a href="login.html">Sign in</a></p>
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

    
  </script>
  


</body>
</html>