<?php
include_once('./config/configdatabase.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - MediHealth</title>
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
        <section class="blog-hero">
            <div class="container">
                <h1>MediHealth Blog</h1>
                <p>Stay informed about the latest in healthcare</p>
            </div>
        </section>

        <section class="blog-content">
            <div class="container">
                <div class="blog-grid">
                    <div class="blog-posts">
                        <article class="blog-post">
                            <div class="post-image">
                                <img src="assets/blog-1.jpeg" alt="Healthcare Technology">
                            </div>
                            <div class="post-content">
                                <div class="post-meta">
                                    <span><i class="far fa-calendar"></i> January 15, 2024</span>
                                    <span><i class="far fa-user"></i> Dr. Sarah Johnson</span>
                                </div>
                                <h2>The Future of Telemedicine</h2>
                                <p>Explore how telemedicine is revolutionizing healthcare delivery and making medical services more accessible to patients worldwide.</p>
                                <a href="#" class="read-more">Read More</a>
                            </div>
                        </article>

                        <article class="blog-post">
                            <div class="post-image">
                                <img src="assets/blog-2.jpeg" alt="Mental Health">
                            </div>
                            <div class="post-content">
                                <div class="post-meta">
                                    <span><i class="far fa-calendar"></i> January 10, 2024</span>
                                    <span><i class="far fa-user"></i> Dr. Michael Chen</span>
                                </div>
                                <h2>Mental Health in the Digital Age</h2>
                                <p>Understanding the impact of technology on mental health and how digital tools can support mental well-being.</p>
                                <a href="#" class="read-more">Read More</a>
                            </div>
                        </article>

                        <article class="blog-post">
                            <div class="post-image">
                                <img src="assets/blog-3.jpeg" alt="Preventive Care">
                            </div>
                            <div class="post-content">
                                <div class="post-meta">
                                    <span><i class="far fa-calendar"></i> January 5, 2024</span>
                                    <span><i class="far fa-user"></i> Dr. John Smith</span>
                                </div>
                                <h2>Preventive Healthcare: A Proactive Approach</h2>
                                <p>Learn about the importance of preventive healthcare and how regular check-ups can help maintain good health.</p>
                                <a href="#" class="read-more">Read More</a>
                            </div>
                        </article>
                    </div>

                    <aside class="blog-sidebar">
                        <div class="sidebar-widget">
                            <h3>Categories</h3>
                            <ul>
                                <li><a href="#">Healthcare Technology</a></li>
                                <li><a href="#">Mental Health</a></li>
                                <li><a href="#">Preventive Care</a></li>
                                <li><a href="#">Medical Research</a></li>
                                <li><a href="#">Patient Care</a></li>
                            </ul>
                        </div>

                        <div class="sidebar-widget">
                            <h3>Recent Posts</h3>
                            <ul>
                                <li><a href="#">The Future of Telemedicine</a></li>
                                <li><a href="#">Mental Health in the Digital Age</a></li>
                                <li><a href="#">Preventive Healthcare: A Proactive Approach</a></li>
                            </ul>
                        </div>

                        <div class="sidebar-widget">
                            <h3>Subscribe to Our Newsletter</h3>
                            <form class="newsletter-form">
                                <input type="email" placeholder="Enter your email">
                                <button type="submit">Subscribe</button>
                            </form>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </main>

    <?php include_once('include/footer.php'); ?>

    <style>
        .blog-hero {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-top: 80px;
        }

        .blog-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .blog-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .blog-content {
            padding: 80px 0;
        }

        .blog-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 50px;
        }

        .blog-post {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .post-image {
            width: 100%;
            height: 300px;
            overflow: hidden;
        }

        .post-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .post-content {
            padding: 30px;
        }

        .post-meta {
            display: flex;
            gap: 20px;
            color: #666;
            margin-bottom: 15px;
        }

        .post-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .post-content h2 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }

        .post-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .read-more {
            display: inline-block;
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .read-more:hover {
            color: #2980b9;
        }

        .blog-sidebar {
            position: sticky;
            top: 100px;
        }

        .sidebar-widget {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .sidebar-widget h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .sidebar-widget ul {
            list-style: none;
            padding: 0;
        }

        .sidebar-widget ul li {
            margin-bottom: 10px;
        }

        .sidebar-widget ul li a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .sidebar-widget ul li a:hover {
            color: #3498db;
        }

        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .newsletter-form input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .newsletter-form button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .newsletter-form button:hover {
            background: #2980b9;
        }

        @media (max-width: 768px) {
            .blog-grid {
                grid-template-columns: 1fr;
            }

            .blog-hero {
                padding: 60px 0;
            }

            .blog-hero h1 {
                font-size: 2rem;
            }

            .blog-sidebar {
                position: static;
            }
        }
    </style>
</body>
</html> 