<?php
include_once('../include/header.php');
include_once('../config/configdatabase.php');

// Ensure user is logged in
if (!isset($_SESSION['patientID'])) {
    // Redirect to login page if not logged in
    header("Location: patientlogin.php");
    exit();
}

$patient_id = $_SESSION['patientID'];
$success_message = '';
$error_message = '';

// Fetch patient data
$query = "SELECT * FROM patients WHERE patientID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = trim($_POST['dob']);
    $zone = trim($_POST['zone']);
    $district = trim($_POST['district']);
    $city = trim($_POST['city']);
    $bloodgroup = trim($_POST['bloodgroup']);
    $gender = trim($_POST['gender']);

    // Validation
    $errors = array();
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($dob)) $errors[] = "Date of birth is required";
    if (empty($zone)) $errors[] = "Zone is required";
    if (empty($district)) $errors[] = "District is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($bloodgroup)) $errors[] = "Blood group is required";
    if (empty($gender)) $errors[] = "Gender is required";

    // Check if email already exists for other users
    $email_check = "SELECT patientID FROM patients WHERE email = ? AND patientID != ?";
    $stmt = $conn->prepare($email_check);
    $stmt->bind_param("si", $email, $patient_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Email already exists";
    }

    if (empty($errors)) {
        // Update patient information
        $update_query = "UPDATE patients SET 
            first_name = ?, 
            last_name = ?, 
            email = ?, 
            number = ?, 
            dob = ?, 
            zone = ?, 
            district = ?, 
            city = ?, 
            bloodgroup = ?, 
            gender = ? 
            WHERE patientID = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssssssssi", 
            $first_name, $last_name, $email, $phone, $dob, 
            $zone, $district, $city, $bloodgroup, $gender, $patient_id);
        
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh patient data
            $query = "SELECT * FROM patients WHERE patientID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $patient = $result->fetch_assoc();
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<div class="profile-container">
    <div class="profile-header">
        <h2>My Profile</h2>
        <p>View and update your personal information</p>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="profile-content">
        <form method="POST" class="profile-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($patient['first_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($patient['last_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($patient['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($patient['number']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($patient['dob']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="Male" <?php echo $patient['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $patient['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $patient['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bloodgroup">Blood Group</label>
                    <select id="bloodgroup" name="bloodgroup" required>
                        <option value="A+" <?php echo $patient['bloodgroup'] == 'A+' ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo $patient['bloodgroup'] == 'A-' ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo $patient['bloodgroup'] == 'B+' ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo $patient['bloodgroup'] == 'B-' ? 'selected' : ''; ?>>B-</option>
                        <option value="AB+" <?php echo $patient['bloodgroup'] == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo $patient['bloodgroup'] == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                        <option value="O+" <?php echo $patient['bloodgroup'] == 'O+' ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo $patient['bloodgroup'] == 'O-' ? 'selected' : ''; ?>>O-</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="zone">Zone</label>
                    <input type="text" id="zone" name="zone" value="<?php echo htmlspecialchars($patient['province']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="district">District</label>
                    <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($patient['district']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($patient['city']); ?>" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="patientdash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </form>
    </div>
</div>

<style>
.profile-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 2rem;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.profile-header {
    text-align: center;
    margin-bottom: 2rem;
}

.profile-header h2 {
    color: #2c3e50;
    font-size: 2rem;
    margin-bottom: 0.5rem;
    margin-top: 2rem;
}

.profile-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.profile-content {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #2c3e50;
    font-weight: 500;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
    text-decoration: none;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-success {
    background: #2ecc71;
    color: white;
}

.alert-danger {
    background: #e74c3c;
    color: white;
}

@media (max-width: 768px) {
    .profile-container {
        margin: 1rem;
        padding: 1rem;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}
</style>

<?php
$conn->close();
?> 