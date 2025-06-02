<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['patientID'])) {
    header("Location: ../index.php");
    exit();
}

// Check if there's a pending appointment
if (!isset($_SESSION['pending_appointment'])) {
    header("Location: bookappointment.php");
    exit();
}

include_once('../config/configdatabase.php');

$patient_id = $_SESSION['patientID'];
$pending_appointment = $_SESSION['pending_appointment'];

// Fetch doctor information
$query = "SELECT d.*, h.name as hospital_name, dep.department_name 
          FROM doctor d 
          JOIN hospital h ON d.hospitalid = h.id 
          JOIN department dep ON d.department_id = dep.department_id 
          WHERE d.doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $pending_appointment['doctor_id']);
$stmt->execute();
$result = $stmt->get_result();
$doctor_info = $result->fetch_assoc();

// Fetch patient information
$query = "SELECT * FROM patients WHERE patientID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient_info = $result->fetch_assoc();

// Fixed appointment fee
$appointment_fee = 500; // Rs. 500

// Handle payment submission
if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    // In a real application, you would process the payment here
    // For demo purposes, we'll just proceed with booking the appointment
    
    try {
        $conn->begin_transaction();

        // If booking for others, insert other patient data
        $other_patient_id = null;
        if ($pending_appointment['appointment_for'] === 'others') {
            $other_patient = $pending_appointment['other_patient'];
            $insertOther = "INSERT INTO other_patients (name, age, gender, blood_group, relation, address, email, phone) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertOther);
            $stmt->bind_param("sissssss", 
                $other_patient['name'],
                $other_patient['age'],
                $other_patient['gender'],
                $other_patient['blood_group'],
                $other_patient['relation'],
                $other_patient['address'],
                $other_patient['email'],
                $other_patient['phone']
            );
            $stmt->execute();
            $other_patient_id = $conn->insert_id;
        }

        // Insert the appointment
        $insertQuery = "INSERT INTO appointments 
            (patient_id, hospital_id, department_id, doctor_id, appointment_date, appointment_time, 
             reason, status, appointment_for, other_patient_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled', ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssssssssi", 
            $patient_id,
            $pending_appointment['hospital_id'],
            $pending_appointment['department_id'],
            $pending_appointment['doctor_id'],
            $pending_appointment['appointment_date'],
            $pending_appointment['appointment_time'],
            $pending_appointment['reason'],
            $pending_appointment['appointment_for'],
            $other_patient_id
        );
        $stmt->execute();
        $appointment_id = $conn->insert_id;

        // Handle file uploads if any
        if (!empty($_FILES['prescription_files']['name'][0]) || !empty($_FILES['report_files']['name'][0])) {
            // Create a temporary file to store the POST data
            $temp_file = tempnam(sys_get_temp_dir(), 'upload_');
            file_put_contents($temp_file, json_encode([
                'appointment_id' => $appointment_id,
                'doctor_id' => $pending_appointment['doctor_id']
            ]));

            // Create cURL request to handle file upload
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'upload_attachments.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'appointment_id' => $appointment_id,
                'doctor_id' => $pending_appointment['doctor_id'],
                'prescription_files' => $_FILES['prescription_files'],
                'report_files' => $_FILES['report_files']
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            // Clean up temporary file
            unlink($temp_file);
        }

        $conn->commit();

        // Clear pending appointment data
        unset($_SESSION['pending_appointment']);

        // Redirect to patient dashboard with success indicator
        header("Location: patientdash.php?booking_success=1");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error processing payment: " . $e->getMessage();
    }
}

include_once('../include/header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - MediHealth</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .payment-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .payment-summary {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #3498db;
        }

        .payment-method.selected {
            border-color: #3498db;
            background: #f0f7ff;
        }

        .payment-form {
            margin-top: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }

        .card-details {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1rem;
        }

        .payment-details {
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .payment-details h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #333;
        }

        .payment-details p {
            margin-bottom: 0.8rem;
            color: #555;
        }

        .btn-pay {
            background: #3498db;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-pay:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        /* Success Modal Styles */
        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .success-modal.active {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        .success-content {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            position: relative;
            transform: scale(0.7);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .success-modal.active .success-content {
            transform: scale(1);
            opacity: 1;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease;
        }

        .success-icon i {
            color: white;
            font-size: 40px;
        }

        .success-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .success-message {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .success-button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .success-button:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .success-icon i {
            animation: checkmark 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>Complete Your Payment</h1>
            <p>Please review your appointment details and proceed with payment</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="payment-summary">
            <h3>Appointment Summary</h3>
            <div class="summary-item">
                <span>Doctor:</span>
                <span>Dr. <?php echo htmlspecialchars($doctor_info['name']); ?></span>
            </div>
            <div class="summary-item">
                <span>Specialization:</span>
                <span><?php echo htmlspecialchars($doctor_info['specialization']); ?></span>
            </div>
            <div class="summary-item">
                <span>Hospital:</span>
                <span><?php echo htmlspecialchars($doctor_info['hospital_name']); ?></span>
            </div>
            <div class="summary-item">
                <span>Department:</span>
                <span><?php echo htmlspecialchars($doctor_info['department_name']); ?></span>
            </div>
            <div class="summary-item">
                <span>Date:</span>
                <span><?php echo date('F j, Y', strtotime($pending_appointment['appointment_date'])); ?></span>
            </div>
            <div class="summary-item">
                <span>Time:</span>
                <span><?php echo date('h:i A', strtotime($pending_appointment['appointment_time'])); ?></span>
            </div>
            <div class="summary-item">
                <span>Appointment Fee:</span>
                <span>Rs. <?php echo number_format($appointment_fee, 2); ?></span>
            </div>
        </div>

        <div class="payment-methods">
            <div class="payment-method selected" data-method="card">
                <h4>Credit / Debit Card</h4>
            </div>
            <div class="payment-method" data-method="bank-transfer">
                <h4>Bank Transfer</h4>
            </div>
            <div class="payment-method" data-method="mobile-payment">
                <h4>Mobile Payment</h4>
            </div>
            <!-- Add more payment methods here -->
        </div>

        <form class="payment-form" method="POST">
            <div id="card-details" class="payment-details">
                <h3>Enter Card Details</h3>
                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" class="form-control" required>
                </div>
                <div class="form-group card-details">
                    <div>
                        <label for="expiry_date">Expiry Date</label>
                        <input type="text" id="expiry_date" name="expiry_date" class="form-control" placeholder="MM/YY" required>
                    </div>
                    <div>
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" class="form-control" required>
                    </div>
                </div>
                <!-- Other card fields if necessary -->
            </div>

            <div id="bank-transfer-details" class="payment-details" style="display: none;">
                <h3>Bank Transfer Instructions (Demo)</h3>
                <p><strong>Bank Name:</strong> Demo Bank</p>
                <p><strong>Account Name:</strong> MediHealth Services</p>
                <p><strong>Account Number:</strong> 1234567890</p>
                <p><strong>Branch:</strong> Main Branch</p>
                <p><strong>Amount:</strong> Rs. <?php echo number_format($appointment_fee, 2); ?></p>
                <p>Please transfer the amount to the bank details above. In a real application, you would confirm the transfer before proceeding.</p>
            </div>

            <div id="mobile-payment-details" class="payment-details" style="display: none;">
                <h3>Mobile Payment Instructions (Demo)</h3>
                <p><strong>Provider:</strong> DemoPay</p>
                <p><strong>Mobile Number:</strong> 9876543210</p>
                <p><strong>Account Name:</strong> MediHealth Pay</p>
                <p><strong>Amount:</strong> Rs. <?php echo number_format($appointment_fee, 2); ?></p>
                <p>Please send the amount via mobile payment to the details above. In a real application, you would verify the transaction.</p>
            </div>

            <!-- Hidden fields for appointment details -->
            <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($pending_appointment['doctor_id']); ?>">
            <input type="hidden" name="hospital_id" value="<?php echo htmlspecialchars($pending_appointment['hospital_id']); ?>">
            <input type="hidden" name="department_id" value="<?php echo htmlspecialchars($pending_appointment['department_id']); ?>">
            <input type="hidden" name="appointment_date" value="<?php echo htmlspecialchars($pending_appointment['appointment_date']); ?>">
            <input type="hidden" name="appointment_time" value="<?php echo htmlspecialchars($pending_appointment['appointment_time']); ?>">
            <input type="hidden" name="reason" value="<?php echo htmlspecialchars($pending_appointment['reason']); ?>">
            <input type="hidden" name="appointment_for" value="<?php echo htmlspecialchars($pending_appointment['appointment_for']); ?>">
            <?php if (isset($pending_appointment['other_patient'])): ?>
                <input type="hidden" name="other_patient_name" value="<?php echo htmlspecialchars($pending_appointment['other_patient']['name']); ?>">
                <input type="hidden" name="other_patient_age" value="<?php echo htmlspecialchars($pending_appointment['other_patient']['age']); ?>">
                <input type="hidden" name="other_patient_gender" value="<?php echo htmlspecialchars($pending_appointment['other_patient']['gender']); ?>">
                <input type="hidden" name="other_patient_blood_group" value="<?php echo htmlspecialchars($pending_appointment['other_patient']['blood_group']); ?>">
                <input type="hidden" name="other_patient_relation" value="<?php echo htmlspecialchars($pending_appointment['other_patient']['relation']); ?>">
                <input type="hidden" name="other_patient_address" value="<?php echo htmlspecialchars($pending_appointment['other_patient']['address']); ?>">
                <input type="hidden" name="other_patient_email" value="<?php echo htmlspecialchars($pending_appointment['other_patient']['email']); ?>">
                <input type="hidden" name="other_patient_phone" value="<?php echo htmlspecialchars($pending_appointment['other_patient']['phone']); ?>">
            <?php endif; ?>

            <!-- Add hidden input for selected payment method -->
            <input type="hidden" name="payment_method" id="selected-payment-method" value="card">

            <!-- File attachments -->
            <?php if (!empty($_SESSION['uploaded_files'])): ?>
                <?php foreach ($_SESSION['uploaded_files'] as $file_type => $files): ?>
                    <?php foreach ($files as $file): ?>
                        <input type="hidden" name="uploaded_files[<?php echo htmlspecialchars($file_type); ?>][]" value="<?php echo htmlspecialchars(json_encode($file)); ?>">
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <button type="submit" class="btn-pay">Pay Now (Rs. <?php echo number_format($appointment_fee, 2); ?>)</button>
        </form>
    </div>

    <!-- Add this before the closing body tag -->
    <div class="success-modal" id="successModal">
        <div class="success-content">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="success-title">Payment Successful!</h2>
            <p class="success-message">Your appointment has been booked successfully. You will receive a confirmation email shortly.</p>
            <button class="success-button" onclick="redirectToDashboard()">Go to Dashboard</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('.payment-method');
            const paymentDetails = document.querySelectorAll('.payment-details');
            const selectedPaymentMethodInput = document.getElementById('selected-payment-method');
            const paymentForm = document.querySelector('.payment-form');

            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    paymentMethods.forEach(m => m.classList.remove('selected'));
                    this.classList.add('selected');
                    paymentDetails.forEach(detail => detail.style.display = 'none');
                    const selectedMethod = this.getAttribute('data-method');
                    document.getElementById(selectedMethod + '-details').style.display = 'block';
                    selectedPaymentMethodInput.value = selectedMethod;
                });
            });

            // Handle form submission
            paymentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Create FormData object
                const formData = new FormData(this);
                
                // Add payment method to form data
                formData.append('payment_method', selectedPaymentMethodInput.value);
                
                // Send the form data to the server
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Show success modal
                    const successModal = document.getElementById('successModal');
                    successModal.classList.add('active');
                    
                    // After 3 seconds, redirect to dashboard
                    setTimeout(redirectToDashboard, 3000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing your payment. Please try again.');
                });
            });
        });

        function redirectToDashboard() {
            window.location.href = 'patientdash.php?booking_success=1';
        }
    </script>
</body>
</html> 