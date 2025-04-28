<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Doctor Table Update Process</h2>";

// Start transaction
$conn->begin_transaction();

try {
    // Step 1: Backup the doctor table
    echo "<p>Step 1: Creating backup of doctor table...</p>";
    $conn->query("CREATE TABLE IF NOT EXISTS doctor_backup AS SELECT * FROM doctor");
    echo "<p>✓ Backup created successfully</p>";
    
    // Step 2: Check if the doctor table exists
    $result = $conn->query("SHOW TABLES LIKE 'doctor'");
    if ($result->num_rows == 0) {
        // Create the doctor table if it doesn't exist
        echo "<p>Step 2: Creating doctor table...</p>";
        $conn->query("CREATE TABLE doctor (
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
        )");
        echo "<p>✓ Doctor table created successfully</p>";
    } else {
        // Add missing columns to the existing doctor table
        echo "<p>Step 2: Adding missing columns to doctor table...</p>";
        
        // Check and add doctor_id column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'doctor_id'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN doctor_id VARCHAR(20) NOT NULL PRIMARY KEY FIRST");
            echo "<p>✓ Added doctor_id column</p>";
        } else {
            // Check if doctor_id is numeric and convert it to VARCHAR if needed
            $result = $conn->query("SELECT doctor_id FROM doctor LIMIT 1");
            $row = $result->fetch_assoc();
            if (isset($row['doctor_id']) && is_numeric($row['doctor_id'])) {
                // Create a backup of the doctor table
                $conn->query("CREATE TABLE IF NOT EXISTS doctor_backup_temp AS SELECT * FROM doctor");
                
                // Drop the primary key constraint
                $conn->query("ALTER TABLE doctor DROP PRIMARY KEY");
                
                // Change the column type to VARCHAR
                $conn->query("ALTER TABLE doctor MODIFY COLUMN doctor_id VARCHAR(20) NOT NULL");
                
                // Update existing doctor_ids to include the DOC prefix
                $conn->query("UPDATE doctor SET doctor_id = CONCAT('DOC', LPAD(doctor_id, 4, '0'))");
                
                // Add the primary key constraint back
                $conn->query("ALTER TABLE doctor ADD PRIMARY KEY (doctor_id)");
                
                echo "<p>✓ Updated doctor_id format to DOC + 4 digits</p>";
            } else {
                echo "<p>✓ doctor_id column already exists with correct format</p>";
            }
        }
        
        // Check and add hospitalid column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'hospitalid'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN hospitalid INT(11) NOT NULL");
            echo "<p>✓ Added hospitalid column</p>";
        } else {
            echo "<p>✓ hospitalid column already exists</p>";
        }
        
        // Check and add department_id column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'department_id'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN department_id INT(11) NOT NULL");
            echo "<p>✓ Added department_id column</p>";
        } else {
            echo "<p>✓ department_id column already exists</p>";
        }
        
        // Check and add name column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'name'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN name VARCHAR(100) NOT NULL");
            echo "<p>✓ Added name column</p>";
        } else {
            echo "<p>✓ name column already exists</p>";
        }
        
        // Check and add email column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'email'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN email VARCHAR(100) NOT NULL");
            echo "<p>✓ Added email column</p>";
        } else {
            echo "<p>✓ email column already exists</p>";
        }
        
        // Check and add phone column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'phone'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN phone VARCHAR(20) NOT NULL");
            echo "<p>✓ Added phone column</p>";
        } else {
            echo "<p>✓ phone column already exists</p>";
        }
        
        // Check and add specialization column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'specialization'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN specialization VARCHAR(100) NOT NULL");
            echo "<p>✓ Added specialization column</p>";
        } else {
            echo "<p>✓ specialization column already exists</p>";
        }
        
        // Check and add qualification column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'qualification'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN qualification VARCHAR(255) NOT NULL DEFAULT 'MBBS'");
            echo "<p>✓ Added qualification column</p>";
        } else {
            echo "<p>✓ qualification column already exists</p>";
        }
        
        // Check and add experience column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'experience'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN experience INT(11) NOT NULL DEFAULT 0");
            echo "<p>✓ Added experience column</p>";
        } else {
            echo "<p>✓ experience column already exists</p>";
        }
        
        // Check and add password column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'password'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN password VARCHAR(255) NOT NULL");
            echo "<p>✓ Added password column</p>";
        } else {
            echo "<p>✓ password column already exists</p>";
        }
        
        // Check and add status column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'status'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
            echo "<p>✓ Added status column</p>";
        } else {
            echo "<p>✓ status column already exists</p>";
        }
        
        // Check and add created_at column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'created_at'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            echo "<p>✓ Added created_at column</p>";
        } else {
            echo "<p>✓ created_at column already exists</p>";
        }
        
        // Check and add schedule column
        $result = $conn->query("SHOW COLUMNS FROM doctor LIKE 'schedule'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE doctor ADD COLUMN schedule TEXT NOT NULL");
            echo "<p>✓ Added schedule column</p>";
        } else {
            echo "<p>✓ schedule column already exists</p>";
        }
    }
    
    // Step 3: Add foreign key constraints if they don't exist
    echo "<p>Step 3: Adding foreign key constraints...</p>";
    
    // Check if hospital foreign key exists
    $result = $conn->query("SHOW CREATE TABLE doctor");
    $row = $result->fetch_assoc();
    if (strpos($row['Create Table'], 'FOREIGN KEY (hospitalid)') === false) {
        $conn->query("ALTER TABLE doctor ADD CONSTRAINT doctor_ibfk_1 FOREIGN KEY (hospitalid) REFERENCES hospital(id) ON DELETE CASCADE");
        echo "<p>✓ Added hospital foreign key constraint</p>";
    } else {
        echo "<p>✓ hospital foreign key constraint already exists</p>";
    }
    
    // Check if department foreign key exists
    if (strpos($row['Create Table'], 'FOREIGN KEY (department_id)') === false) {
        $conn->query("ALTER TABLE doctor ADD CONSTRAINT doctor_ibfk_2 FOREIGN KEY (department_id) REFERENCES department(department_id) ON DELETE CASCADE");
        echo "<p>✓ Added department foreign key constraint</p>";
    } else {
        echo "<p>✓ department foreign key constraint already exists</p>";
    }
    
    // Step 4: Update foreign key constraints in related tables
    echo "<p>Step 4: Updating foreign key constraints in related tables...</p>";
    
    // Check if appointments table exists
    $result = $conn->query("SHOW TABLES LIKE 'appointments'");
    if ($result->num_rows > 0) {
        // Check if the foreign key exists
        $result = $conn->query("SHOW CREATE TABLE appointments");
        $row = $result->fetch_assoc();
        if (strpos($row['Create Table'], 'appointments_ibfk_3') !== false) {
            $conn->query("ALTER TABLE appointments DROP FOREIGN KEY appointments_ibfk_3");
            $conn->query("ALTER TABLE appointments ADD CONSTRAINT appointments_ibfk_3 FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE");
            echo "<p>✓ Updated appointments foreign key constraint</p>";
        } else {
            echo "<p>✓ appointments foreign key constraint is already correct</p>";
        }
    } else {
        echo "<p>✓ appointments table does not exist, skipping</p>";
    }
    
    // Check if patient_reports table exists
    $result = $conn->query("SHOW TABLES LIKE 'patient_reports'");
    if ($result->num_rows > 0) {
        // Check if the foreign key exists
        $result = $conn->query("SHOW CREATE TABLE patient_reports");
        $row = $result->fetch_assoc();
        if (strpos($row['Create Table'], 'patient_reports_ibfk_3') !== false) {
            $conn->query("ALTER TABLE patient_reports DROP FOREIGN KEY patient_reports_ibfk_3");
            $conn->query("ALTER TABLE patient_reports ADD CONSTRAINT patient_reports_ibfk_3 FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE");
            echo "<p>✓ Updated patient_reports foreign key constraint</p>";
        } else {
            echo "<p>✓ patient_reports foreign key constraint is already correct</p>";
        }
    } else {
        echo "<p>✓ patient_reports table does not exist, skipping</p>";
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "<h3 style='color:green;'>Doctor table updated successfully!</h3>";
    echo "<p>All required attributes have been added to the doctor table.</p>";
    echo "<p>The doctor table now has the following structure:</p>";
    echo "<ul>";
    echo "<li>doctor_id (VARCHAR) - Primary Key</li>";
    echo "<li>hospitalid (INT) - Foreign Key to hospital table</li>";
    echo "<li>department_id (INT) - Foreign Key to department table</li>";
    echo "<li>name (VARCHAR)</li>";
    echo "<li>email (VARCHAR)</li>";
    echo "<li>phone (VARCHAR)</li>";
    echo "<li>specialization (VARCHAR)</li>";
    echo "<li>qualification (VARCHAR)</li>";
    echo "<li>experience (INT)</li>";
    echo "<li>password (VARCHAR)</li>";
    echo "<li>status (ENUM)</li>";
    echo "<li>created_at (TIMESTAMP)</li>";
    echo "</ul>";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "<h3 style='color:red;'>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}

// Close the connection
$conn->close();
?>