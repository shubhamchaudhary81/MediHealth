<?php
include_once('../include/header.php');
include_once('../config/configdatabase.php');

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Here you would typically save the message to a database
        // For now, we'll just show a success message
        $success_message = "Thank you for your message. We'll get back to you soon!";
        
        // Clear the form
        $name = $email = $subject = $message = '';
    }
}
?>

<body>
  <main>
    <!-- Contact Section -->
    <section class="contact-section">
      <div class="container">
        <div class="section-header">
          <h1>Contact Us</h1>
          <p>Have questions? We're here to help you with any healthcare-related inquiries.</p>
        </div>

        <div class="contact-container">
          <!-- Contact Information -->
          <div class="contact-info">
            <div class="info-item">
              <i class="fas fa-map-marker-alt"></i>
              <div class="info-content">
                <h3>Our Location</h3>
                <p>Himalya Darsha Biratnagar-10,Nepal</p>
              </div>
            </div>
            
            <div class="info-item">
              <i class="fas fa-phone-alt"></i>
              <div class="info-content">
                <h3>Phone Number</h3>
                <p>Emergency: 9819096818<br>Support: 9810536236</p>
              </div>
            </div>
            
            <div class="info-item">
              <i class="fas fa-envelope"></i>
              <div class="info-content">
                <h3>Email Address</h3>
                <p>chaudharushubhammedihealth@gmail.com<br>sahmaheshmedihealth@gmail.com</p>
              </div>
            </div>
            
            <div class="info-item">
              <i class="fas fa-clock"></i>
              <div class="info-content">
                <h3>Working Hours</h3>
                <p>Sunday - Thursday: 8:00 AM - 8:00 PM<br>Friday: 9:00 AM - 5:00 PM<br>Saturday: Closed</p>
              </div>
            </div>
          </div>
          
          <!-- Contact Form -->
          <div class="contact-form-container">
            <?php if ($success_message): ?>
              <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
              </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
              <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
              </div>
            <?php endif; ?>
            
            <form class="contact-form" method="POST" action="">
              <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
              </div>
              
              <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
              </div>
              
              <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
              </div>
              
              <div class="form-group">
                <label for="message">Your Message</label>
                <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
              </div>
              
              <button type="submit" class="btn-submit">Send Message</button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Map Section
    <section class="map-section">
      <div class="container">
        <div class="map-container">
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387193.30596073366!2d-74.25986548248684!3d40.69714941932609!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2s!4v1647043439269!5m2!1sen!2s" 
            width="100%" 
            height="400" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy">
          </iframe>
        </div>
      </div>
    </section>
  </main> -->

  <?php include('../include/footer.php'); ?>

  <style>

    .btn-outline {
    background: transparent;
    border: 2px solid #3498db;
    color: #3498db;
    /* padding: 0.85rem 1.8rem;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-size: 1.05rem;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.1); */
}

.btn-outline:hover {
    background: #3498db;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
}

.btn-outline i {
    font-size: 1.2rem;
}
    /* Contact Page Styles */
    .contact-section {
      padding: 4rem 0;
      background: #f8f9fa;
    }

    .section-header {
      text-align: center;
      margin-top: 2rem;
      margin-bottom: 2rem;
    }

    .section-header h1 {
      color: #2c3e50;
      font-size: 2.5rem;
      margin-top: 1rem;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .section-header p {
      color: #7f8c8d;
      font-size: 1.1rem;
      max-width: 600px;
      margin: 0 auto;
    }

    .contact-container {
      display: grid;
      grid-template-columns: 1fr 1.5fr;
      gap: 3rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    /* Contact Information */
    .contact-info {
      display: flex;
      flex-direction: column;
      gap: 2rem;
    }

    .info-item {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .info-item i {
      font-size: 1.5rem;
      color: #3498db;
      margin-top: 0.2rem;
    }

    .info-content h3 {
      color: #2c3e50;
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .info-content p {
      color: #7f8c8d;
      line-height: 1.6;
    }

    /* Contact Form */
    .contact-form-container {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .alert {
      padding: 1rem;
      border-radius: 4px;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
    }

    .alert-error {
      background: #f8d7da;
      color: #721c24;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      color: #2c3e50;
      font-weight: 500;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }

    .form-group textarea {
      resize: vertical;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      border-color: #3498db;
      outline: none;
    }

    .btn-submit {
      width: 100%;
      padding: 1rem;
      background: #3498db;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .btn-submit:hover {
      background: #2980b9;
    }

    /* Map Section */
    .map-section {
      padding: 0 0 4rem;
    }

    .map-container {
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    /* Responsive Styles */
    @media (max-width: 992px) {
      .contact-container {
        grid-template-columns: 1fr;
      }

      .contact-info {
        order: 2;
      }

      .contact-form-container {
        order: 1;
      }
    }

    @media (max-width: 768px) {
      .section-header h1 {
        font-size: 2rem;
      }

      .contact-form-container {
        padding: 1.5rem;
      }

      .map-container {
        height: 300px;
      }

      .map-container iframe {
        height: 100%;
      }
    }
  </style>
</body>
</html> 