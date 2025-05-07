<?php
include_once('./config/configdatabase.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MediHealth</title>
    <link rel="stylesheet" href="css/hello.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="item1"> <img src="assets/logo-fotor-20250118225918.png" width="200px"></div>

            <div class="item2">
                <nav>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="review.php">Review</a></li>
                    </ul>
                </nav>
            </div>

            <div class="item3">
                <!-- <a href="patient/patientregister.php" class="btn-register">Patient Register</a>
                <a href="hospital/hospitalregister.php" class="btn-register">Hospital Register</a> -->
                <a href="index.php" class="btn-register">Home Page</a>

            </div>
        </header>
    </div>

    <main>
        <section class="contact-hero">
            <div class="container">
                <h1>Contact Us</h1>
                <p>We're here to help and answer any questions you might have</p>
            </div>
        </section>

        <section class="contact-content">
            <div class="container">
                <div class="contact-grid">
                    <div class="contact-info">
                        <div class="info-card">
                            <i class="fas fa-map-marker-alt"></i>
                            <h3>Our Location</h3>
                            <p> College Road<br>Biratnagar<br>Province No. 1, Nepal</p>
                        </div>

                        <div class="info-card">
                            <i class="fas fa-phone"></i>
                            <h3>Phone Number</h3>
                            <p>Main: +977 9819096819<br>Support: +977 9810536236</p>
                        </div>

                        <div class="info-card">
                            <i class="fas fa-envelope"></i>
                            <h3>Email Address</h3>
                            <p>info@medihealth.com<br>support@medihealth.com</p>
                        </div>

                        <div class="info-card">
                            <i class="fas fa-clock"></i>
                            <h3>Working Hours</h3>
                            <p>Sunday - Thursday: 9:00 AM - 6:00 PM<br>Friday: 10:00 AM - 4:00 PM<br>Saturday: Closed</p>
                        </div>
                    </div>

                    <div class="contact-form">
                        <h2>Send us a Message</h2>
                        <form action="process_contact.php" method="POST">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required>
                            </div>

                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" id="subject" name="subject" required>
                            </div>

                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" rows="5" required></textarea>
                            </div>

                            <button type="submit" class="submit-btn">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="map-section">
            <div class="container">
                <h2>Find Us on the Map</h2>
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3567.8953659995747!2d87.2797!3d26.4525!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39ef7441a6f1e409%3A0x2ef05689b9c4a6a1!2sBiratnagar%2C%20Nepal!5e0!3m2!1sen!2snp!4v1645564750981!5m2!1sen!2snp" 
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </section>
    </main>

    <?php include_once('include/footer.php'); ?>

    <style>
        .contact-hero {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-top: 80px;
        }

        .contact-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .contact-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .contact-content {
            padding: 80px 0;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .info-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .info-card i {
            font-size: 2rem;
            color: #3498db;
            margin-bottom: 20px;
        }

        .info-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .info-card p {
            color: #666;
            line-height: 1.6;
        }

        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .contact-form h2 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #2c3e50;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group textarea {
            resize: vertical;
        }

        .submit-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background: #2980b9;
        }

        .map-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .map-section h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 40px;
            font-size: 2.5rem;
        }

        .map-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        @media (max-width: 992px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .contact-info {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .contact-hero {
                padding: 60px 0;
            }

            .contact-hero h1 {
                font-size: 2rem;
            }

            .contact-form {
                padding: 20px;
            }
        }
    </style>
</body>
</html> 