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
        // Insert hospital
        $stmt = $conn->prepare("INSERT INTO hospital (name, email, phone, zone, district, city, website) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssss", $hospitalName, $email, $phone, $zone, $district, $city, $website);
            if ($stmt->execute()) {
                $hospital_id = $stmt->insert_id;
                $stmt->close();

                // Insert departments into hospitaldepartment
                if (!empty($departments)) {
                    foreach ($departments as $deptName) {
                        $deptName = trim($deptName);

                        // Get department ID
                        $deptStmt = $conn->prepare("SELECT department_id FROM department WHERE department_name = ?");
                        if ($deptStmt) {
                            $deptStmt->bind_param("s", $deptName);
                            $deptStmt->execute();
                            $deptStmt->store_result();

                            if ($deptStmt->num_rows > 0) {
                                $deptStmt->bind_result($deptId);
                                $deptStmt->fetch();
                                $deptStmt->close();

                                // Now insert into hospitaldepartment
                                $insertDeptStmt = $conn->prepare("INSERT INTO hospitaldepartment (hospitalid, department_id) VALUES (?, ?)");
                                if ($insertDeptStmt) {
                                    $insertDeptStmt->bind_param("ii", $hospital_id, $deptId);
                                    if (!$insertDeptStmt->execute()) {
                                        echo "<p style='color:red;'>Failed to insert into hospitaldepartment: " . $insertDeptStmt->error . "</p>";
                                    }
                                    $insertDeptStmt->close();
                                } else {
                                    echo "<p style='color:red;'>Prepare insert hospitaldepartment failed: " . $conn->error . "</p>";
                                }
                            } else {
                                echo "<p style='color:red;'>Department '$deptName' not found in department table.</p>";
                                $deptStmt->close();
                            }
                        } else {
                            echo "<p style='color:red;'>Prepare department lookup failed: " . $conn->error . "</p>";
                        }
                    }
                } else {
                    echo "<p style='color:red;'>No departments selected.</p>";
                }

                // Insert admin
                $adminid = generateAdminID($conn);
                $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
                $adminStmt = $conn->prepare("INSERT INTO hospitaladmin (hospitalid, adminid, name, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
                if ($adminStmt) {
                    $adminStmt->bind_param("iissss", $hospital_id, $adminid, $adminName, $adminEmail, $adminPhone, $hashedPassword);
                    $adminStmt->execute();
                    $adminStmt->close();

                    header("Location: hospitaladminlogin.php"); // Redirect on success
                    exit;
                } else {
                    echo "<p style='color:red;'>Admin insert failed: " . $conn->error . "</p>";
                }
            } else {
                echo "<p style='color:red;'>Hospital insert failed: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p style='color:red;'>Hospital statement failed: " . $conn->error . "</p>";
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
  <title>Hospital Registration</title>
  <style>
    body {
      background-color: #f1f5f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      color: #212529;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      width: 100%;
      max-width: 1000px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    h1 {
      text-align: center;
      color: #0d6efd;
      padding-top: 30px;
      font-size: 2rem;
    }

    h3 {
      font-size: 1.2rem;
      margin: 30px 0 15px;
      font-weight: 600;
      color: #0d6efd;
    }

    form {
      padding: 40px;
    }

    .form-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    label {
      margin-bottom: 6px;
      font-weight: 500;
    }

    input, select {
      width: 100%;
      padding: 10px 1px;
      border-radius: 10px;
      border: 1px solid #ced4da;
      font-size: 16px;
      background: #fff;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    input:focus, select:focus {
      border-color: #0d6efd;
      box-shadow: none;
      outline: none;
    }

    input[type="file"] {
      padding: 6px;
    }

    .full-width {
      grid-column: span 3;
    }

    .departments {
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      margin-top: 10px;
    }

    .departments label {
      background: #e9ecef;
      padding: 8px 14px;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      transition: background-color 0.3s;
    }

    .departments input[type="checkbox"] {
      accent-color: #0d6efd;
    }

    .departments label:hover {
      background-color: #dee2e6;
    }

    .submit-section {
      text-align: center;
      margin-top: 30px;
    }

    button {
      background-color: #0d6efd;
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #0b5ed7;
    }

    .success-message {
      display: none;
      text-align: center;
      margin-top: 20px;
      font-size: 1.1rem;
      color: #198754;
    }

    .checkmark {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      border: 3px solid #198754;
      display: inline-block;
      position: relative;
    }

    .checkmark::after {
      content: "";
      position: absolute;
      left: 14px;
      top: 6px;
      width: 10px;
      height: 20px;
      border: solid #198754;
      border-width: 0 3px 3px 0;
      transform: rotate(45deg);
    }

    .error {
      border-color: #e53935 !important;
      background-color: #ffebee;
    }

    .error-message {
      color: #e53935;
      font-size: 14px;
      margin-top: 4px;
    }

    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Hospital Registration Form</h1>

    <form method="POST" action="">
      <h3>Hospital Details</h3>

      <div class="form-row">
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
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="zone">Zone</label>
          <select id="zone" name="zone" required>
            <option value="">Select Zone</option>
            <option value="Bagmati">Bagmati</option>
            <option value="Gandaki">Gandaki</option>
            <option value="Koshi">Koshi</option>
            <option value="Lumbini">Lumbini</option>
            <option value="Madhesh">Madhesh</option>
            <option value="Karnali">Karnali</option>
            <option value="Sudurpashchim">Sudurpashchim</option>
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
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="website">Website (Optional)</label>
          <input type="url" id="website" name="website">
        </div>
      </div>

      <div class="form-row full-width">
        <div class="form-group full-width">
          <h3>Select Available Departments</h3>
          <div class="departments">
            <?php
                            
                $sql = "SELECT department_name FROM department";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $dept = htmlspecialchars($row['department_name']);
                        echo "<label><input type='checkbox' name='departments[]' value='$dept'> $dept</label> ";
                    }
                } else {
                    echo "No departments found.";
                }
?>
          </div>
        </div>
      </div>

      <h3>Admin Details</h3>

      <div class="form-row">
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
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="adminPassword">Password</label>
          <input type="password" id="adminPassword" name="adminPassword" required>
        </div>

        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <input type="password" id="confirmPassword" name="confirmPassword" required>
        </div>
      </div>

      <div class="submit-section">
        <button type="submit">Register Hospital</button>
      </div> 
    </form>
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
      "Sunsari": [ "Itahari"],
      "Morang": ["Biratnagar"],
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

</body>
</html>
