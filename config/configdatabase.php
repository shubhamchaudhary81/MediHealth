<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health";

// Step 1: Create connection to MySQL server (no DB selected yet)
$conn = new mysqli($servername, $username, $password);

// Check server connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // echo "Database '$dbname' created successfully<br>";
} else {
    // echo "Error creating database: " . $conn->error . "<br>";
}
$conn->close(); // Close the server connection

// Step 3: Reconnect using the created database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 4: Create 'patients' table
$tableSql = "CREATE TABLE IF NOT EXISTS patients (
    patientID INT(11) AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    number VARCHAR(15) NOT NULL,
    dob DATE NOT NULL,
    province VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    bloodgroup VARCHAR(5) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($tableSql) === TRUE) {
    // echo "Table 'patients' created successfully<br>";
} else {
    // echo "Error creating 'patients' table: " . $conn->error . "<br>";
}

// Create 'department' table
$sql = "CREATE TABLE IF NOT EXISTS department (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE
)";
if ($conn->query($sql) === TRUE) {
    // echo "Table 'department' created successfully<br>";
} else {
    // echo "Error creating 'department' table: " . $conn->error . "<br>";
}

// Truncate department table to reset IDs
$conn->query("TRUNCATE TABLE department");

// Insert sample department data
$sql = "INSERT INTO department (department_name) VALUES
('Anesthesiology'),
('Cardiology'),
('Dental'),
('Dermatology'),
('Emergency'),
('ENT (Ear, Nose, Throat)'),
('Gastroenterology'),
('General Surgery'),
('Gynecology'),
('Infectious Diseases'),
('Internal Medicine'),
('Nephrology'),
('Neurology'),
('Oncology'),
('Ophthalmology'),
('Orthopedics'),
('Pathology'),
('Pediatrics'),
('Physiotherapy'),
('Plastic Surgery'),
('Psychiatry'),
('Pulmonology'),
('Radiology'),
('Rheumatology'),
('Urology')";
if ($conn->query($sql) === TRUE) {
    // echo "Department data inserted successfully<br>";
} else {
    // echo "Error inserting department data: " . $conn->error . "<br>";
}

//create hospital table
$hospitalTableSql = "CREATE TABLE IF NOT EXISTS hospital (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    zone VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    website VARCHAR(150),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($hospitalTableSql) === TRUE) {
    // echo "Table 'hospital' created successfully<br>";
} else {
    // echo "Error creating 'hospital' table: " . $conn->error . "<br>";
}

// Create 'hospitaldepartment' table
// This table will link hospitals to their departments
$hospitalDeptTableSql = "CREATE TABLE IF NOT EXISTS hospitaldepartment (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    hospitalid INT(11) NOT NULL,
    department_id INT(11) NOT NULL,
    FOREIGN KEY (hospitalid) REFERENCES hospital(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES department(department_id) ON DELETE CASCADE
)";
if ($conn->query($hospitalDeptTableSql) === TRUE) {
    // echo "Table 'hospitaldepartment' created successfully<br>";
} else {
    // echo "Error creating 'hospitaldepartment' table: " . $conn->error . "<br>";
}

// Create 'hospitaladmin' table
// This table will store hospital admin details
$hospitalAdminTableSql = "CREATE TABLE IF NOT EXISTS hospitaladmin (
    adminid INT(11) AUTO_INCREMENT PRIMARY KEY,
    hospitalid INT(11) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospitalid) REFERENCES hospital(id) ON DELETE CASCADE
)";
if ($conn->query($hospitalAdminTableSql) === TRUE) {
    // echo "Table 'hospitaladmin' created successfully<br>";
} else {
    // echo "Error creating 'hospitaladmin' table: " . $conn->error . "<br>";
}

// Create 'doctor' table
$doctorTableSql = "CREATE TABLE IF NOT EXISTS doctor (
            doctor_id VARCHAR(20) PRIMARY KEY,
            hospitalid INT(11) NOT NULL,
            department_id INT(11) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            specialization VARCHAR(100) NOT NULL,
            qualification VARCHAR(255) NOT NULL,
            experience INT(11) NOT NULL,
            password VARCHAR(255) NOT NULL,
            schedule TEXT NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (hospitalid) REFERENCES hospital(id) ON DELETE CASCADE,
            FOREIGN KEY (department_id) REFERENCES department(department_id) ON DELETE CASCADE
)";
if ($conn->query($doctorTableSql) === TRUE) {
    // echo "Table 'doctor' created successfully<br>";
} else {
    // echo "Error creating 'doctor' table: " . $conn->error . "<br>";
}

// Create patient_reports table
$create_patient_reports = "CREATE TABLE IF NOT EXISTS patient_reports (
    report_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    patient_id INT(11) NOT NULL,
    hospital_id INT(11) NOT NULL,
    doctor_id VARCHAR(20) NOT NULL,
    report_title VARCHAR(255) NOT NULL,
    report_date DATE NOT NULL,
    report_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patientID) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE
)";

if ($conn->query($create_patient_reports) === TRUE) {
    // echo "Table 'patient_reports' created successfully<br>";
} else {
    echo "Error creating patient_reports table: " . $conn->error . "<br>";
}

// Create 'appointments' table
$appointmentsTableSql = "CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    patient_id INT(11) NOT NULL,
    hospital_id INT(11) NOT NULL,
    doctor_id VARCHAR(20) NOT NULL,
    department_id INT(11) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patientID) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES department(department_id) ON DELETE CASCADE
)";
if ($conn->query($appointmentsTableSql) === TRUE) {
    // echo "Table 'appointments' created successfully<br>";
} else {
    // echo "Error creating 'appointments' table: " . $conn->error . "<br>";
}

// Create superadmin table if it doesn't exist
$create_superadmin_table = "CREATE TABLE IF NOT EXISTS superadmin (
    super_id VARCHAR(50) PRIMARY KEY,
    super_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($create_superadmin_table) === TRUE) {
    // Check if default superadmin exists
    $check_superadmin = "SELECT * FROM superadmin WHERE super_id = 'SA001'";
    $result = $conn->query($check_superadmin);
    
    if ($result->num_rows == 0) {
        // Insert default superadmin (password: admin123)
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_superadmin = "INSERT INTO superadmin (super_id, super_name, password) 
                             VALUES ('SA001', 'Super Admin', '$hashed_password')";
        
        if ($conn->query($insert_superadmin) === TRUE) {
            // Default superadmin created successfully
        } else {
            // Error creating default superadmin
        }
    }
} else {
    // Error creating superadmin table
}

// Create 'prescriptions' table
$prescriptionsTableSql = "CREATE TABLE IF NOT EXISTS prescriptions (
    prescription_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT(11) NOT NULL,
    doctor_id VARCHAR(20) NOT NULL,
    patient_id INT(11) NOT NULL,
    hospital_id INT(11) NOT NULL,
    diagnosis TEXT NOT NULL,
    medications TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(patientID) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id) ON DELETE CASCADE
)";

if ($conn->query($prescriptionsTableSql) === TRUE) {
    // echo "Table 'prescriptions' created successfully<br>";
} else {
    // echo "Error creating 'prescriptions' table: " . $conn->error . "<br>";
}

// Create booked_slots table
$sql = "CREATE TABLE IF NOT EXISTS booked_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id VARCHAR(20) NOT NULL,
    appointment_date DATE NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    booked_count INT DEFAULT 0,
    max_patients INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    // echo "Table booked_slots created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

// $conn->close();
?>
