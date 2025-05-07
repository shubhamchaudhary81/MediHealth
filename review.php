<?php
include_once('./config/configdatabase.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - MediHealth</title>
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
        <section class="review-hero">
            <div class="container">
                <h1>Patient Reviews</h1>
                <p>What our patients say about their experience with MediHealth</p>
            </div>
        </section>

        <section class="review-content">
            <div class="container">
                <div class="review-stats">
                    <div class="stat-card">
                        <div class="rating">
                            <span class="number">4.8</span>
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                        <p>Average Rating</p>
                    </div>

                    <div class="stat-card">
                        <span class="number">1,234</span>
                        <p>Total Reviews</p>
                    </div>

                    <div class="stat-card">
                        <span class="number">98%</span>
                        <p>Would Recommend</p>
                    </div>
                </div>

                <div class="review-form">
                    <h2>Share Your Experience</h2>
                    <form action="process_review.php" method="POST">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5">
                                <label for="star5"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1"><i class="fas fa-star"></i></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="review">Your Review</label>
                            <textarea id="review" name="review" rows="5" required></textarea>
                        </div>

                        <button type="submit" class="submit-btn">Submit Review</button>
                    </form>
                </div>

                <div class="reviews-list">
                    <h2>Recent Reviews</h2>
                    <div class="review-cards">
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">R</div>
                                    <div>
                                        <h3>Raju</h3>
                                        <div class="stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                <span class="review-date">January 15, 2024</span>
                            </div>
                            <p class="review-text">The online appointment system is so convenient. I was able to book my visit in just a few clicks and received timely reminders. The doctors are professional and caring.</p>
                        </div>

                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">M</div>
                                    <div>
                                        <h3>Mahesh</h3>
                                        <div class="stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                <span class="review-date">January 10, 2024</span>
                            </div>
                            <p class="review-text">I've been using MediHealth for all my family's medical needs. The platform makes it easy to keep track of appointments and medical records. Highly recommended!</p>
                        </div>

                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">S</div>
                                    <div>
                                        <h3>Shibu</h3>
                                        <div class="stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                    </div>
                                </div>
                                <span class="review-date">January 5, 2024</span>
                            </div>
                            <p class="review-text">The care I received was exceptional. I could easily access my test results and the doctor's notes after my visit, which helped me understand my condition better.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include_once('include/footer.php'); ?>

    <style>
        .review-hero {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-top: 80px;
        }

        .review-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .review-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .review-content {
            padding: 80px 0;
        }

        .review-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card .number {
            font-size: 2.5rem;
            color: #3498db;
            font-weight: 700;
            display: block;
            margin-bottom: 10px;
        }

        .stat-card p {
            color: #666;
            font-size: 1.1rem;
        }

        .rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .stars {
            color: #f1c40f;
        }

        .review-form {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 50px;
        }

        .review-form h2 {
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

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            gap: 5px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 1.5rem;
            color: #ddd;
            transition: color 0.3s ease;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #f1c40f;
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

        .reviews-list h2 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .review-cards {
            display: grid;
            gap: 30px;
        }

        .review-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .reviewer-info h3 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .review-date {
            color: #666;
            font-size: 0.9rem;
        }

        .review-text {
            color: #666;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .review-stats {
                grid-template-columns: 1fr;
            }

            .review-hero {
                padding: 60px 0;
            }

            .review-hero h1 {
                font-size: 2rem;
            }

            .review-form {
                padding: 20px;
            }

            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</body>
</html> 