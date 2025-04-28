-- Drop the existing foreign key constraints
ALTER TABLE appointments DROP FOREIGN KEY appointments_ibfk_3;
ALTER TABLE patient_reports DROP FOREIGN KEY patient_reports_ibfk_3;

-- Create a temporary table with the new structure
CREATE TABLE doctor_temp (
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
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospitalid) REFERENCES hospital(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES department(department_id) ON DELETE CASCADE
);

-- Copy data from the old table to the new table
INSERT INTO doctor_temp (doctor_id, hospitalid, department_id, name, email, phone, specialization, qualification, experience, password, status, created_at)
SELECT CONCAT('DOC', LPAD(doctor_id, 6, '0')), hospitalid, department_id, name, email, phone, specialization, qualification, experience, password, status, created_at
FROM doctor;

-- Drop the old table
DROP TABLE doctor;

-- Rename the temporary table to the original name
RENAME TABLE doctor_temp TO doctor;

-- Re-add the foreign key constraints
ALTER TABLE appointments ADD CONSTRAINT appointments_ibfk_3 FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE;
ALTER TABLE patient_reports ADD CONSTRAINT patient_reports_ibfk_3 FOREIGN KEY (doctor_id) REFERENCES doctor(doctor_id) ON DELETE CASCADE; 