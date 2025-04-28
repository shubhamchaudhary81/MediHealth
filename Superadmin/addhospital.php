<?php
    include_once('../config/database.php');

//     $query = "SELECT department_id, name FROM department"; // Adjust table and column names
// $result = $conn->query($query);


// Initialize errors array
$errors = array();

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $name = trim($_POST['name']);
    $district = trim($_POST['district']);
    $city = trim($_POST['city']);
    $number = trim($_POST['number']);

    // Validation
    if (empty($name)) {
        $errors['name_error'] = "Hospital Name is required";
    } elseif (!preg_match("/^[a-zA-Z ]+$/", $name)) {
        $errors['name_error'] = "Name can't contain digits and special characters";
    }

    if (empty($district)) {
        $errors['district_error'] = "District is required";
    }

    if (empty($city)) {
        $errors['city_error'] = "City is required";
    }

    if (empty($number)) {
        $errors['number_error'] = "Contact Number is required.";
    } elseif (!preg_match("/^9[87][0-9]{8}$/", $number)) {
        $errors['number_error'] = "Invalid Contact Number.";
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO hospital (name, district, city, contact_number) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $district, $city, $number);
        
        if ($stmt->execute()) {
            echo "<script>alert('Hospital added successfully!');</script>";
        } else {
            echo "<script>alert('Error adding hospital.');</script>";
        }

        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> Responsive Registration Form | CodingLab </title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <!-- Title section -->
    <div class="title">Add Hospital</div>
    <div class="content">
      <!-- Registration form -->
      <form action="" method="POST">
        <div class="user-details">
          <!-- Input for Full Name -->
          <div class="input-box">
            <span class="details">Hospital Name</span>
            <input type="text" name="name"placeholder="Enter Hospital name" required>
          </div>
          <!-- Input for Username -->
          <div class="input-box">
            <span class="details">District</span>
            <input type="text" placeholder="Enter District" required>
          </div>
          <!-- Input for Email -->
          <div class="input-box">
            <span class="details">City</span>
            <input type="text" name="city" placeholder="Enter your City" required>
          </div>
          <!-- Input for Phone Number -->
          <div class="input-box">
            <span class="details">Contact Number</span>
            <input type="text" name="number" placeholder="Enter Contact number" required>
          </div>
         
        <!-- Submit button -->
        <div class="button">
          <input type="submit" value="Register">
        </div>
      </form>
    </div>
  </div>
</body>
</html>