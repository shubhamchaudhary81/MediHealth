<?php
include_once('./config/configdatabase.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - MediHealth</title>
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
                        <li><a href="index.php#about">About Us</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="review.php">Review</a></li>
                    </ul>
                </nav>
            </div>

            <div class="item3">
                <a href="index.php" class="btn-register">Home Page</a>
                <!-- <a href="hospital/hospitalregister.php" class="btn-register">Hospital Register</a> -->
            </div>
        </header>
    </div>

    <main>
        <section class="about-hero">
            <div class="container">
                <h1>About MediHealth</h1>
                <p>Your trusted partner in healthcare</p>
            </div>
        </section>

        <section class="about-content">
            <div class="container">
                <div class="about-grid">
                    <div class="about-text">
                        <h2>Our Mission</h2>
                        <p>At MediHealth, we are committed to revolutionizing healthcare accessibility through innovative technology. Our mission is to connect patients with the best healthcare providers, making quality medical care available to everyone.</p>
                        
                        <h2>Our Vision</h2>
                        <p>We envision a world where healthcare is accessible, efficient, and patient-centric. Through our platform, we aim to bridge the gap between patients and healthcare providers, ensuring everyone receives the care they deserve.</p>
                        
                        <h2>What We Offer</h2>
                        <ul>
                            <li>Easy appointment booking with top doctors</li>
                            <li>Digital medical records management</li>
                            <li>24/7 healthcare support</li>
                            <li>Secure and confidential patient data handling</li>
                            <li>Comprehensive healthcare solutions</li>
                        </ul>
                    </div>
                    <div class="about-image">
                        <img src="assets/about-image.jpeg" alt="Healthcare Team">
                    </div>
                </div>
            </div>
        </section>
<!-- 
        <section class="team-section">
            <div class="container">
                <h2>Our Leadership Team</h2>
                <div class="team-grid">
                    <div class="team-member">
                        <div class="member-image">
                            <img src="assets/team-1.jpg" alt="Team Member">
                        </div>
                        <h3>Dr. John Smith</h3>
                        <p>Chief Medical Officer</p>
                    </div>
                    <div class="team-member">
                        <div class="member-image">
                            <img src="assets/team-2.jpg" alt="Team Member">
                        </div>
                        <h3>Sarah Johnson</h3>
                        <p>Head of Operations</p>
                    </div>
                    <div class="team-member">
                        <div class="member-image">
                            <img src="assets/team-3.jpg" alt="Team Member">
                        </div>
                        <h3>Dr. Michael Chen</h3>
                        <p>Technical Director</p>
                    </div>
                </div>
            </div>
        </section> -->
    </main>

    <?php include_once('include/footer.php'); ?>

    <style>
        .about-hero {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-top: 80px;
        }

        .about-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .about-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .about-content {
            padding: 80px 0;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-text h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .about-text p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .about-text ul {
            list-style: none;
            padding: 0;
        }

        .about-text ul li {
            padding: 10px 0;
            color: #666;
            position: relative;
            padding-left: 30px;
        }

        .about-text ul li:before {
            content: '✓';
            color: #3498db;
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .about-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .team-section {
            background: #f8f9fa;
            padding: 80px 0;
        }

        .team-section h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 50px;
            font-size: 2.5rem;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .team-member {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-10px);
        }

        .member-image {
            width: 100%;
            height: 250px;
            overflow: hidden;
        }

        .member-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .team-member h3 {
            color: #2c3e50;
            margin: 20px 0 10px;
            font-size: 1.5rem;
        }

        .team-member p {
            color: #666;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .about-grid {
                grid-template-columns: 1fr;
            }

            .about-hero {
                padding: 60px 0;
            }

            .about-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</body>
</html> 