<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: doctorlogin.php");
    exit();
}

require_once('../config/configdatabase.php');

// Get appointment ID from URL
$appointment_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$appointment_id) {
    die("No appointment ID provided");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosis = $_POST['diagnosis'] ?? '';
    $medications = $_POST['medications'] ?? '';

    // Get appointment details
    $appointment_query = "SELECT patient_id, hospital_id FROM appointments WHERE appointment_id = ? AND doctor_id = ?";
    $stmt = $conn->prepare($appointment_query);
    $stmt->bind_param("is", $appointment_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();

    if ($appointment) {
        // Insert prescription
        $insert_query = "INSERT INTO prescriptions (appointment_id, doctor_id, patient_id, hospital_id, diagnosis, medications) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("isssss", 
            $appointment_id,
            $_SESSION['user_id'],
            $appointment['patient_id'],
            $appointment['hospital_id'],
            $diagnosis,
            $medications
        );

        if ($stmt->execute()) {
            // Update appointment status
            $update_query = "UPDATE appointments SET status = 'completed' WHERE appointment_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();

            // Redirect to dashboard
            header("Location: doctordash.php");
            exit();
        }
    }
}

// Fetch appointment details with related information
$query = "SELECT a.*, p.first_name, p.last_name, p.dob, p.gender, p.bloodgroup,
                 h.name as hospital_name, h.zone, h.district, h.city, h.phone as hospital_phone, 
                 h.email as hospital_email, h.website as hospital_website,
                 d.name as doctor_name, d.specialization, d.qualification, d.email as doctor_email, 
                 d.phone as doctor_phone,
                 op.name as other_name, op.age as other_age, op.gender as other_gender, op.blood_group as other_blood_group
          FROM appointments a 
          JOIN patients p ON a.patient_id = p.patientID 
          JOIN hospital h ON a.hospital_id = h.id
          JOIN doctor d ON CAST(a.doctor_id AS CHAR) = d.doctor_id
          LEFT JOIN other_patients op ON a.other_patient_id = op.id
          WHERE a.appointment_id = ? AND CAST(a.doctor_id AS CHAR) = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $appointment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Appointment not found or you don't have permission to view it");
}

// Calculate age from DOB
$dob = new DateTime($data['dob']);
$today = new DateTime();
$age = $dob->diff($today)->y;

// Format hospital address
$hospital_address = $data['city'] . ', ' . $data['district'] . ', ' . $data['zone'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Medical Prescription - MediHealth</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #004080;
      --accent: #007bff;
      --bg: #eef4fa;
      --text-dark: #222;
      --text-muted: #666;
      --card-bg: #fff;
    }

    * {
      box-sizing: border-box;
            margin: 0;
            padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      margin: 0;
            padding: 20px;
      color: var(--text-dark);
    }

    .container {
      max-width: 900px;
      margin: auto;
      background-color: var(--card-bg);
      padding: 30px;
      border-radius: 14px;
      box-shadow: 0 10px 30px rgba(0, 64, 128, 0.15);
      border-left: 6px solid var(--primary);
    }

    .hospital-info {
      text-align: center;
      margin-bottom: 20px;
    }

    .hospital-info h1 {
      font-size: 24px;
      color: var(--primary);
      margin: 0;
    }

    .hospital-info p {
      margin: 4px 0;
      font-size: 14px;
      color: var(--text-muted);
    }

    .header {
      border-bottom: 2px solid var(--primary);
      padding-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }

    .header h2 {
      margin: 0;
      font-size: 22px;
      color: var(--primary);
    }

    .header p {
      margin: 4px 0;
      font-size: 14px;
      color: var(--text-muted);
    }

    .header img {
      width: 50px;
    }

    .section {
      margin-top: 30px;
    }

    .section h3 {
      font-size: 18px;
      color: var(--accent);
      margin-bottom: 10px;
      border-left: 4px solid var(--accent);
      padding-left: 10px;
    }

    .editable-box {
      padding: 20px;
      background-color: #f9fcff;
      border-radius: 10px;
      min-height: 100px;
      border: 1px solid #d0e4f5;
      font-size: 15px;
      line-height: 1.6;
      outline: none;
    }

    .footer {
      display: flex;
      justify-content: space-between;
      margin-top: 40px;
      align-items: center;
    }

    .signature {
      width: 200px;
      height: 40px;
      border-bottom: 2px solid #000;
    }

    .qr {
      width: 90px;
      height: 90px;
      background-color: #dbe9fa;
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--primary);
      border-radius: 8px;
      font-size: 12px;
    }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-secondary {
            background: var(--accent);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

    @media print {
      body {
        background: #fff;
        padding: 0;
      }

      .container {
        box-shadow: none;
        border-left: none;
        padding: 20px;
      }

      .editable-box {
        border: none;
        background: none;
      }

            .action-buttons {
                display: none;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 20px;
            }

            .header {
                flex-direction: column;
                gap: 15px;
            }

            .header img {
                align-self: center;
            }

            .footer {
                flex-direction: column;
                gap: 20px;
                align-items: center;
            }

            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
        <form method="POST" action="">
    <!-- HOSPITAL INFO -->
    <div class="hospital-info">
                <h1><?php echo htmlspecialchars($data['hospital_name']); ?></h1>
                <p><?php echo htmlspecialchars($hospital_address); ?></p>
                <p>Phone: <?php echo htmlspecialchars($data['hospital_phone']); ?> | Email: <?php echo htmlspecialchars($data['hospital_email']); ?></p>
                <p>Website: <?php echo htmlspecialchars($data['hospital_website']); ?></p>
    </div>

    <!-- DOCTOR HEADER -->
    <div class="header">
      <div>
                    <h2>Dr. <?php echo htmlspecialchars($data['doctor_name']); ?></h2>
                    <p><?php echo htmlspecialchars($data['specialization']); ?> | <?php echo htmlspecialchars($data['qualification']); ?></p>
                    <p><?php echo htmlspecialchars($data['doctor_email']); ?> | <?php echo htmlspecialchars($data['doctor_phone']); ?></p>
      </div>
      <img src="https://img.icons8.com/ios-filled/100/004080/doctor-male.png" alt="Doctor Icon">
    </div>

    <!-- PATIENT SECTION -->
    <div class="section">
      <h3>Patient Details</h3>
                <div class="editable-box">
<?php if ($data['appointment_for'] === 'others'): ?>
    Name: <?php echo htmlspecialchars($data['other_name']); ?><br>
    Age: <?php echo htmlspecialchars($data['other_age']); ?> years<br>
    Gender: <?php echo htmlspecialchars($data['other_gender']); ?><br>
    Blood Group: <?php echo htmlspecialchars($data['other_blood_group']); ?><br>
<?php else: ?>
    Name: <?php echo htmlspecialchars($data['first_name'] . ' ' . $data['last_name']); ?><br>
    Age: <?php echo $age; ?> years<br>
    Gender: <?php echo htmlspecialchars($data['gender']); ?><br>
    Blood Group: <?php echo htmlspecialchars($data['bloodgroup']); ?><br>
<?php endif; ?>
    Date: <?php echo date('d/m/Y'); ?>
      </div>
    </div>

    <!-- DIAGNOSIS -->
    <div class="section">
      <h3>Diagnosis</h3>
                <textarea name="diagnosis" class="editable-box" style="width: 100%; min-height: 100px;"><?php echo htmlspecialchars($data['reason']); ?></textarea>
    </div>

    <!-- MEDICATIONS -->
    <div class="section">
      <h3>Prescribed Medicines</h3>
                <textarea name="medications" class="editable-box" style="width: 100%; min-height: 100px;">
• Medicine 1 – dosage
• Medicine 2 – dosage
• Medicine 3 – dosage</textarea>
    </div>

    <!-- FOOTER -->
    <div class="footer">
      <div>
        <div class="signature"></div>
                    <p style="margin-top: 6px;">Doctor's Signature</p>
      </div>
      <div class="qr">QR Code</div>
    </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="btn btn-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Print Prescription
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Save Prescription
                </button>
            </div>
        </form>
  </div>
</body>
</html>
