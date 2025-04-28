<?php
include_once('configdatabase.php');

// First, add the new columns
$alterTableSQL = "ALTER TABLE hospital 
    ADD COLUMN zone VARCHAR(50) AFTER phone,
    ADD COLUMN district VARCHAR(50) AFTER zone,
    ADD COLUMN city VARCHAR(50) AFTER district";

if ($conn->query($alterTableSQL) === TRUE) {
    echo "New location columns added successfully<br>";
} else {
    echo "Error adding location columns: " . $conn->error . "<br>";
}

// Update existing records with default values if needed
$updateExistingSQL = "UPDATE hospital 
    SET zone = 'Bagmati',
        district = 'Kathmandu',
        city = 'Kathmandu'
    WHERE zone IS NULL 
    OR district IS NULL 
    OR city IS NULL";

if ($conn->query($updateExistingSQL) === TRUE) {
    echo "Existing records updated with default values<br>";
} else {
    echo "Error updating existing records: " . $conn->error . "<br>";
}

// Make the new columns NOT NULL after setting default values
$alterNotNullSQL = "ALTER TABLE hospital 
    MODIFY COLUMN zone VARCHAR(50) NOT NULL,
    MODIFY COLUMN district VARCHAR(50) NOT NULL,
    MODIFY COLUMN city VARCHAR(50) NOT NULL";

if ($conn->query($alterNotNullSQL) === TRUE) {
    echo "Location columns set to NOT NULL successfully<br>";
} else {
    echo "Error setting NOT NULL constraint: " . $conn->error . "<br>";
}

// Remove the old location column if it exists
$dropOldColumnSQL = "ALTER TABLE hospital DROP COLUMN IF EXISTS location";

if ($conn->query($dropOldColumnSQL) === TRUE) {
    echo "Old location column removed successfully<br>";
} else {
    echo "Error removing old location column: " . $conn->error . "<br>";
}

$conn->close();
?> 