<!-- footer section start  -->
<footer>
    <div class="footerContainer">
        <div class="footer-content">
            <div class="footer-section links">
                <h2>Quick Links</h2>
                <ul>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#services">Our Services</a></li>
                    <li><a href="#blog">Blog</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section contact">
                <h2>Contact Us</h2>
                <p><i class="fas fa-envelope"></i> medihealth628@gmail.com</p>
                <p><i class="fas fa-phone"></i> +977-9819096818</p>
                <p><i class="fas fa-phone"></i> +977-9810536236</p>
                <p><i class="fas fa-map-marker-alt"></i> Nepal, Biratnagar-12</p>
            </div>
        </div>
    </div>
    
    <div class="socialIcons">
        <a href="https://www.facebook.com/mahesh.sah.94402" target="_blank"><i class="fa-brands fa-facebook"></i></a>
        <a href="https://www.instagram.com/shubhamchaudhary_sandilya" target="_blank"><i class="fa-brands fa-instagram"></i></a>
        <a href="#"><i class="fa-brands fa-twitter"></i></a>
        <a href="https://www.linkedin.com/in/mahesh-sah-653046250" target="_blank"><i class="fa-brands fa-linkedin"></i></a>
        <a href="#"><i class="fa-brands fa-youtube"></i></a>
    </div>
    
    <div class="footerBottom">
        <p>Copyright &copy;2025; Designed by <span class="designer">MediHealth</span></p>
    </div>

    <style>
        footer {
            background: #2c3e50;
            color: #fff;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .footerContainer {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section {
            padding: 1rem;
        }

        .footer-section h2 {
            color: #3498db;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-section h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background: #3498db;
        }

        .footer-section.links ul {
            list-style: none;
            padding: 0;
        }

        .footer-section.links ul li {
            margin-bottom: 0.8rem;
        }

        .footer-section.links ul li a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s ease;
            display: inline-block;
        }

        .footer-section.links ul li a:hover {
            color: #3498db;
            transform: translateX(5px);
        }

        .footer-section.contact p {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-section.contact i {
            color: #3498db;
            width: 20px;
        }

        .socialIcons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .socialIcons a {
            color: #fff;
            font-size: 1.5rem;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }

        .socialIcons a:hover {
            background: #3498db;
            transform: translateY(-3px);
        }

        .footerBottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .footerBottom p {
            font-size: 0.9rem;
            color: #ccc;
        }

        .designer {
            color: #3498db;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-section h2::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .footer-section.contact p {
                justify-content: center;
            }

            .socialIcons {
                flex-wrap: wrap;
            }

            .socialIcons a {
                width: 35px;
                height: 35px;
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            footer {
                padding: 2rem 0 1rem;
            }

            .footer-section {
                padding: 0.5rem;
            }

            .footer-section h2 {
                font-size: 1.3rem;
            }

            .socialIcons {
                gap: 1rem;
            }
        }
    </style>
</footer>

</body>
</html>