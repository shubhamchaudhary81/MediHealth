<?php
include_once('../config/configdatabase.php');

function generateAdminID($conn) {
    $query = "SELECT MAX(adminid) AS last_id FROM hospitaladmin";
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $lastId = $row['last_id'];
        return $lastId ? $lastId + 1 : 10001;
    } else {
        return 10001;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hospitalName = trim($_POST['hospitalName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $zone = trim($_POST['zone']);
    $district = trim($_POST['district']);
    $city = trim($_POST['city']);
    $website = trim($_POST['website']);
    $departments = isset($_POST['departments']) ? $_POST['departments'] : [];

    $adminName = trim($_POST['adminName']);
    $adminEmail = trim($_POST['adminEmail']);
    $adminPhone = trim($_POST['adminPhone']);
    $adminPassword = $_POST['adminPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    $errors = [];

    // Basic Validation
    if (empty($hospitalName) || empty($email) || empty($phone) || empty($zone) || empty($district) || empty($city)) {
        $errors[] = "Please fill all required hospital fields.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid hospital email.";
    }

    if ($adminPassword !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // Insert hospital with pending status
            $hospital_query = "INSERT INTO hospital (name, email, phone, zone, district, city, website, status, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            
            $stmt = $conn->prepare($hospital_query);
            if (!$stmt) {
                throw new Exception("Error preparing hospital query: " . $conn->error);
            }
            
            $stmt->bind_param("sssssss", 
                $hospitalName, 
                $email,
                $phone,
                $zone, 
                $district, 
                $city,
                $website
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error registering hospital: " . $stmt->error);
            }
            
            $hospital_id = $conn->insert_id;
            
            // Insert hospital admin
            $admin_query = "INSERT INTO hospitaladmin (name, email, phone, password, hospitalid, created_at) 
                          VALUES (?, ?, ?, ?, ?, NOW())";
            
            $hashed_password = password_hash($adminPassword, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare($admin_query);
            if (!$stmt) {
                throw new Exception("Error preparing admin query: " . $conn->error);
            }
            
            $stmt->bind_param("ssssi", 
                $adminName, 
                $adminEmail,
                $adminPhone,
                $hashed_password, 
                $hospital_id
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error registering hospital admin: " . $stmt->error);
            }
            
            $conn->commit();
            
            // Send notification email to admin
            $to = $adminEmail;
            $subject = "Hospital Registration Pending Approval";
            $message = "Dear $adminName,\n\n";
            $message .= "Thank you for registering $hospitalName on MediHealth.\n";
            $message .= "Your registration is pending approval from our administrators.\n";
            $message .= "You will receive another email once your registration is approved.\n\n";
            $message .= "Best regards,\nMediHealth Team";
            $headers = "From: medihealth@example.com";
            
            mail($to, $subject, $message, $headers);
            
            $_SESSION['success'] = "Hospital registration submitted successfully. Please wait for admin approval.";
            header("Location: hospitaladminlogin.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    } else {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hospital Registration - MediHealth</title>
  <style>
    :root {
      --primary-color: #2563eb;
      --secondary-color: #1e40af;
      --accent-color: #3b82f6;
      --text-color: #1f2937;
      --light-gray: #f3f4f6;
      --border-color: #e5e7eb;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: var(--light-gray);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      color: var(--text-color);
      line-height: 1.5;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

   /* Header */
   header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    max-width: 1550px;
    background: #fff;
    padding: 2px 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    position: fixed;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
  }
  
  .item1 img {
    max-width: 150px;
  }
  
  .item2 nav ul {
    list-style: none;
    display: flex;
    gap: 25px;
  }
  
  .item2 nav ul li {
    display: inline-block;
  }
  
  .item2 nav ul li a {
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    transition: 0.3s;
  }
  
  .item2 nav ul li a:hover {
    color: rgb(74, 144, 226);
  }
  
  .item3 {
    display: flex;
    gap: 15px;
  }
  
  .btn-register {
    background: rgb(74, 144, 226);
    color: #fff;
    padding: 10px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
  }
  
  .btn-register:hover {
    background: #0056b3;
  }

    .logo {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      margin-top; 50px;
    }

    .logo span {
      color: var(--secondary-color);
    }

    .form-container {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      padding: 2rem;
    }

    .form-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .form-header h1 {
      font-size: 1.875rem;
      font-weight: 700;
      color: var(--text-color);
      margin-bottom: 0.5rem;
    }

    .form-header p {
      color: #6b7280;
    }

    .form-section {
      margin-bottom: 2rem;
    }

    .form-section h2 {
      font-size: 1.25rem;
      font-weight: 600;
      color: var(--text-color);
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid var(--border-color);
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .form-group label {
      font-weight: 500;
      color: var(--text-color);
    }

    .form-group input,
    .form-group select {
      padding: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: 0.5rem;
      font-size: 1rem;
      transition: all 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .departments {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      max-height: 300px;
      overflow-y: auto;
      padding: 1rem;
      border: 1px solid var(--border-color);
      border-radius: 0.5rem;
      background: var(--light-gray);
    }

    .departments label {
      background: white;
      padding: 1rem;
      border-radius: 0.5rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 0.875rem;
      transition: all 0.2s;
      border: 1px solid var(--border-color);
    }

    .departments input[type="checkbox"] {
      width: 1.25rem;
      height: 1.25rem;
      accent-color: var(--primary-color);
    }

    .departments label:hover {
      border-color: var(--primary-color);
      transform: translateY(-2px);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .submit-btn {
      background-color: var(--primary-color);
      color: white;
      padding: 1rem 2rem;
      border: none;
      border-radius: 0.5rem;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      width: 100%;
      max-width: 300px;
      margin: 0 auto;
      display: block;
    }

    .submit-btn:hover {
      background-color: var(--secondary-color);
      transform: translateY(-1px);
    }

    .error {
      color: #dc2626;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }

    @media (max-width: 1024px) {
      .form-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
      
      .departments {
        grid-template-columns: repeat(2, 1fr);
      }
    }
  </style>
</head>
<body>
<div class="container">
        <header>
            <!-- <div class="logo">
                <img src="Medihealth_1-removebg-preview.png" alt="">
            </div> -->
            <div class="item1"> <img src="../assets/logo-fotor-20250118225918.png" width="200px"></div>

            <div class="item2">
                <nav>
                    <ul>
                    <li><a href="../about.php">About Us</a></li>
                        <li><a href="../blog.php">Blog</a></li>
                        <li><a href="../contact.php">Contact</a></li>
                        <li><a href="../contact.php">Review</a></li>
                        
                    </ul>
                </nav>
            </div>

            <div class="item3">
                <a href="hospitaladminlogin.php" class="btn-register">Hospital Login</a>
                <a href="../index.php" class="btn-register">Home Page</a>
            </div>
        </header>
    </div>
  <div class="container">
    <div class="header">
      <div class="logo">
        <!-- <span>MediHealth</span> -->
      </div>
    </div>

    <div class="form-container">
      <div class="form-header">
        <h1>Hospital Registration</h1>
        <p>Please fill in the details to register your hospital</p>
      </div>

      <form method="POST" action="">
        <div class="form-section">
          <h2>Hospital Details</h2>
          <div class="form-grid">
            <div class="form-group">
              <label for="hospitalName">Hospital Name</label>
              <input type="text" id="hospitalName" name="hospitalName" required>
            </div>

            <div class="form-group">
              <label for="email">Hospital Email</label>
              <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
              <label for="phone">Hospital Contact</label>
              <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="form-group">
              <label for="zone">Province</label>
              <select id="zone" name="zone" required>
                <option value="">Select Province</option>
                <option value="Koshi">Province 1</option>
                <option value="Madhesh">Madhesh Province</option>
                <option value="Bagmati">Bagmati Province</option>
                <option value="Gandaki">Gandaki Province</option>
                <option value="Lumbini">Lumbini Province</option>
                <option value="Karnali">Karnali Province</option>
                <option value="Sudurpashchim">Sudurpashchim Province</option>
              </select>
            </div>

            <div class="form-group">
              <label for="district">District</label>
              <select id="district" name="district" required>
                <option value="">Select District</option>
              </select>
            </div>

            <div class="form-group">
              <label for="city">City</label>
              <select id="city" name="city" required>
                <option value="">Select City</option>
              </select>
            </div>

            <div class="form-group">
              <label for="website">Website (Optional)</label>
              <input type="url" id="website" name="website">
            </div>
          </div>
        </div>

        <div class="form-section">
          <h2>Available Departments</h2>
          <div class="departments">
            <?php
                $sql = "SELECT department_name FROM department";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $dept = htmlspecialchars($row['department_name']);
                        echo "<label><input type='checkbox' name='departments[]' value='$dept'> $dept</label>";
                    }
                } else {
                    echo "No departments found.";
                }
            ?>
          </div>
        </div>

        <div class="form-section">
          <h2>Admin Details</h2>
          <div class="form-grid">
            <div class="form-group">
              <label for="adminName">Admin Name</label>
              <input type="text" id="adminName" name="adminName" required>
            </div>

            <div class="form-group">
              <label for="adminEmail">Admin Email</label>
              <input type="email" id="adminEmail" name="adminEmail" required>
            </div>

            <div class="form-group">
              <label for="adminPhone">Admin Phone</label>
              <input type="tel" id="adminPhone" name="adminPhone" required>
            </div>

            <div class="form-group">
              <label for="adminPassword">Password</label>
              <input type="password" id="adminPassword" name="adminPassword" required>
            </div>

            <div class="form-group">
              <label for="confirmPassword">Confirm Password</label>
              <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>
          </div>
        </div>

        <button type="submit" class="submit-btn">Register Hospital</button>
      </form>
    </div>
  </div>

  <script>
    const districtsByProvince = {
      "Koshi": ["Bhojpur", "Dhankuta", "Ilam", "Jhapa", "Khotang", "Morang", "Okhaldhunga", "Panchthar", "Sankhuwasabha", "Solukhumbu", "Sunsari", "Taplejung", "Terhathum", "Udayapur"],
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

    document.getElementById('zone').addEventListener('change', function() {
      const districtSelect = document.getElementById('district');
      const citySelect = document.getElementById('city');
      districtSelect.innerHTML = '<option value="">Select District</option>';
      citySelect.innerHTML = '<option value="">Select City</option>';
      
      const selectedProvince = this.value;
      if (selectedProvince && districtsByProvince[selectedProvince]) {
        districtsByProvince[selectedProvince].forEach(district => {
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
</body>
</html>
