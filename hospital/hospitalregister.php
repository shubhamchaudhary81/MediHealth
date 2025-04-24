<?php
  include_once('../config/configdatabase.php');
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

    <form>
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
          <label for="location">Location</label>
          <input type="text" id="location" name="location" required>
        </div>

        <!-- <div class="form-group">
          <label for="type">Hospital Type</label>
          <select id="type" name="type">
            <option value="">Select Type</option>
            <option value="Public">Public</option>
            <option value="Private">Private</option>
            <option value="Clinic">Clinic</option>
          </select>
        </div> -->

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
          <label for="adminPhone">Admin Contact</label>
          <input type="tel" id="adminPhone" name="adminPhone" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="adminUsername">Admin Username</label>
          <input type="text" id="adminUsername" name="adminUsername" required>
        </div>

        <div class="form-group">
          <label for="adminPassword">Admin Password</label>
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

</body>
</html>
