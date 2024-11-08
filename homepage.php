
<?php
session_start();
$pageTitle = 'Homepage';
include './init.php';

// Fetching the latest products for display
$stmt = $con->prepare("SELECT id, name_product, price_product, currency, img_product FROM products ORDER BY created_at DESC LIMIT 8");
$stmt->execute();
$latestProducts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* General Styling */
        :root {
            --primary-color: #280068;
            --red-color: #B84141;
            --yellow-color: #F7C945;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Enhanced Hero Slider */
        .hero-slider {
            position: relative;
            height: 80vh;
            overflow: hidden;
        }

        .slider-container {
            display: flex;
            height: 100%;
            transition: transform 0.5s ease-in-out;
            width: 300%; /* Width = 100% * number of slides */
        }

        .slide {
            flex: 0 0 33.333%; /* Width = 100% / number of slides */
            position: relative;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slide-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            padding: 3rem 4rem;
            border-radius: 15px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
        }

        .slide-content h1 {
            color: #280068;
            font-size: 3rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .slide-content p {
            color: #280068;
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Slider Navigation Arrows */
        .slider-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.7);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #280068;
            z-index: 10;
            transition: background-color 0.3s ease;
        }

        .slider-nav-btn:hover {
            background: rgba(255, 255, 255, 0.9);
        }

        .prev-btn {
            left: 20px;
        }

        .next-btn {
            right: 20px;
        }

        .reserve-now-btn {
            background-color: #280068;
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            transition: background-color 0.3s ease;
            text-transform: uppercase;
        }

        .reserve-now-btn:hover {
            background-color: #1a0046;
        }

        /* Info Cards */
        .info-sections {
            display: flex;
            gap: 2rem;
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .info-card {
            flex: 1;
            padding: 2.5rem;
            border-radius: 10px;
            color: white;
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .what-we-do {
            background-color: var(--red-color);
        }

        .our-services {
            background-color: var(--yellow-color);
            color: #333;
        }

        .info-card h2 {
            margin-top: 0;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .info-card p {
            line-height: 1.6;
            margin: 0;
        }

        /* Featured Products Section */
        .featured-products {
            padding: 4rem 2rem;
            text-align: center;
            background-color: #f5f5f5;
        }

        .section-title {
            color: #280068;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            text-align: center;
            text-transform: uppercase;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto 3rem auto;
        }

        .product-item {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-5px);
        }

        .product-item img {
            width: 100%;
            height: auto;
            margin-bottom: 1rem;
            border-radius: 5px;
        }

        .product-item h3 {
            color: #280068;
            margin: 0.5rem 0;
            font-size: 1.2rem;
            text-transform: uppercase;
        }

       /* Updated Partners Section */
       .partners {
            background-color: white;
            text-align: center;
            position: relative;
        }

        .partners h2 {
            color: white;
            margin: 0;
            padding: 1.5rem 0;
            font-size: 2rem;
            text-transform: uppercase;
            background-color: #280068;
            position: relative;
        }

        .partners-grid {
            background-color: white;
            padding: 3rem 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .partner-logo {
            flex: 0 1 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background-color: white;
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .partner-logo:hover {
            background-color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .partner-logo img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .partner-logo:hover img {
            transform: scale(1.1);
        }

        .see-more-btn {
            background-color: #280068;
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            margin: 2rem 0 0 0; /* Removed bottom margin */
            text-transform: uppercase;
            transition: background-color 0.3s ease;
        }

        .see-more-btn:hover {
            background-color: #1a0046;
        }

        .partners::after {
            content: '';
            display: block;
            width: 100%;
            height: 4rem; 
            background-color: #280068;
            margin-top: 2rem; 
        }

        /* Connect Section Styles */
        .connect-section {
            background-color: white;
            padding: 3rem 0;
        }

        .connect-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            gap: 1rem; 
            padding: 0 2rem;
            font-size: 1rem;
     }
        .connect-left {
            flex: 0 1 300px;
            padding-right: 2rem; 
            border-right: 2px solid #e5e5e5; 
    }
        .connect-left img {
            max-width: 250px;
            margin-bottom: 1rem;
        }

        .connect-title {
            font-size: 1.1rem; 
            margin-bottom: 1.5rem;
            color:  #333;
            text-align: left;
            font-weight: bold;
        }

        .social-icons {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 1.5rem;
                max-width: 200px;
            }

            .social-icons a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }

        .social-icons a:hover {
            transform: translateY(-3px);
        }

        .social-icons img {
            width: 35px;
            height: 35px;
        }

        .connect-right {
            flex: 0 1 800px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .connect-links h3 {
            color: #280068;
            margin-bottom: 2rem;
            font-size: 1.2rem; 
            left: -10px;
            top: -10px;
            background: white;
            margin: 0;
            z-index: 3; 
        }

        .connect-links ul {
        list-style: none;
        padding: 20px 0 0 0; 
        margin: 0;
}

        .connect-links li {
            margin-bottom: 0.5rem;
        }

        .connect-links a {
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .connect-links a:hover {
            color: #280068;
        }

        .connect-links:not(:last-child)::after {
            content: '';
            position: absolute;
            right: -1rem;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #e5e5e5;
}

        /* Responsive Design */
        @media (max-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .info-sections {
                flex-direction: column;
            }

            .slide-content {
                padding: 2rem;
            }

            .slide-content h1 {
                font-size: 2rem;
            }

            .slide-content p {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
            }

            .slider-nav-btn {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
        }

            @media (max-width: 768px) {
        .connect-container {
            flex-direction: column;
        }
        
        .connect-right {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .connect-links:not(:last-child) {
            border-right: none;
        }
        
        .connect-left {
            border-right: none;
            padding-right: 0;
            text-align: center;
        }
        
        .connect-links h3 {
            position: relative;
            left: 0;
            text-align: center;
        }
    }
    </style>
</head>
<body>
    <!-- Enhanced Hero Slider -->
    <section class="hero-slider">
        <button class="slider-nav-btn prev-btn">❮</button>
        <button class="slider-nav-btn next-btn">❯</button>
        
        <div class="slider-container">
            <div class="slide">
                <img src="assets/img/p1.jpg" alt="Parking Facility">
                <div class="slide-content">
                    <h1>WELCOME TO DELTECH</h1>
                    <p>PARKING SYSTEMS AND SOLUTIONS INC.</p>
                    <a href="index.php" class="reserve-now-btn">RESERVE NOW</a>
                </div>
            </div>
            <div class="slide">
                <img src="assets/img/p2.jpg" alt="Parking Solutions">
                <div class="slide-content">
                    <h1>WELCOME TO DELTECH</h1>
                    <p>PARKING SYSTEMS AND SOLUTIONS INC.</p>
                    <a href="index.php" class="reserve-now-btn">RESERVE NOW</a>
                </div>
            </div>
            <div class="slide">
                <img src="assets/img/p3.jpg" alt="Parking Technology">
                <div class="slide-content">
                    <h1>WELCOME TO DELTECH</h1>
                    <p>PARKING SYSTEMS AND SOLUTIONS INC.</p>
                    <a href="index.php" class="reserve-now-btn">RESERVE NOW</a>
                </div>
            </div>
        </div>
    </section>

    <section class="info-sections">
    <div class="info-card what-we-do">
        <h2>WHAT WE DO</h2>
        <ul>
            <li><a href="parking_system_automation.php">Parking System Automation</a></li>
            <li><a href="autopay_station.php">Autopay Station</a></li>
            <li><a href="mobile_parking_system.php">Mobile Parking System</a></li>
            <li><a href="hotel_lock_sets.php">Hotel Locks Sets</a></li>
            <li><a href="cctv.php">CCTV</a></li>
            <li><a href="access_control_system.php">Access Control System</a></li>
            <li><a href="traffic_signaling_system.php">Traffic Signaling System</a></li>
            <li><a href="pylon_display.php">Pylon Display</a></li>
        </ul>
    </div>
    <div class="info-card our-services">
        <h2>OUR SERVICES</h2>
        <ul>
            <li><a href="parking_station_design.php">Review and recommend parking station designs</a></li>
            <li><a href="preventive_maintenance.php">Preventive Maintenance for Parking Equipment</a></li>
            <li><a href="equipment_integration.php">Integration of Parking Equipment</a></li>
        </ul>
    </div>
</section>


  <!-- Featured Products -->
<section class="featured-products">
    <h2 class="section-title">OUR FEATURED PRODUCTS</h2>
    <div class="products-grid">
        <?php foreach ($latestProducts as $product): ?>
        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-item">
            <?php 
                $imagePath = 'uploads/' . $product['img_product'];
            ?>
            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name_product']); ?>">
            <h3><?php echo htmlspecialchars($product['name_product']); ?></h3>
            <p><?php echo htmlspecialchars($product['price_product']) . ' ' . htmlspecialchars($product['currency']); ?></p>
        </a>
        <?php endforeach; ?>
    </div>
    <a href="index.php" class="reserve-now-btn">SEE MORE</a>
</section>

    <!-- Updated Partners Section -->
    <section class="partners">
        <h2>DELTECH INDUSTRY PARTNERS</h2>
        <div class="partners-grid">
            <div class="partner-logo"><img src="assets/img/sm.png" alt="SM"></div>
            <div class="partner-logo"><img src="assets/img/mpt_mobility.png" alt="MPT Mobility"></div>
            <div class="partner-logo"><img src="assets/img/eton.png" alt="Eton Centris"></div>
            <div class="partner-logo"><img src="assets/img/mactan.png" alt="Mactan Cebu Airport"></div>
            <div class="partner-logo"><img src="assets/img/u_park.png" alt="U Park"></div>
            <div class="partner-logo"><img src="assets/img/megaworld.png" alt="Megaworld"></div>
            <div class="partner-logo"><img src="assets/img/nustar.png" alt="Nustar Resort"></div>
            <div class="partner-logo"><img src="assets/img/makati_medical.png" alt="Makati Medical Center"></div>
            <div class="partner-logo"><img src="assets/img/starmall.png" alt="Starmall"></div>
            <div class="partner-logo"><img src="assets/img/apmc.png" alt="APMC"></div>
            <div class="partner-logo"><img src="assets/img/global.png" alt="Global"></div>
        </div>
        <a href="about.php" class="see-more-btn">SEE MORE</a>
    </section>
<br>

    <!-- Connect Section -->
<section class="connect-section">
    <div class="connect-container">
        <div class="connect-left">
            <img src="assets/img/deltech2.png" alt="Deltech Logo">
            <h2 class="connect-title">Connect with Deltech</h2>
            <div class="social-icons">
                <a href="https://linkedin.com/company/deltech" target="_blank">
                    <img src="assets/img/linkedin.png" alt="LinkedIn">
                </a>
                <a href="https://facebook.com/deltech" target="_blank">
                    <img src="assets/img/facebook.png" alt="Facebook">
                </a>
                <a href="https://twitter.com/deltech" target="_blank">
                    <img src="assets/img/twitter.png" alt="Twitter">
                </a>
                <a href="https://youtube.com/deltech" target="_blank">
                    <img src="assets/img/youtube.png" alt="YouTube">
                </a>
                <a href="https://deltech.com/rss" target="_blank">
                    <img src="assets/img/rss.png" alt="RSS">
                </a>
                <a href="https://glassdoor.com/deltech" target="_blank">
                    <img src="assets/img/glassdoor.png" alt="Glassdoor">
                </a>
                <a href="https://instagram.com/deltech" target="_blank">
                    <img src="assets/img/instagram.png" alt="Instagram">
                </a>
                <a href="https://xing.com/deltech" target="_blank">
                    <img src="assets/img/xing.png" alt="Xing">
                </a>
            </div>
        </div>
            <!-- Divider Line -->
            <div class="divider"></div>

            <div class="connect-right">
                <div class="connect-links">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="about.php">About us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="offices.php">Offices</a></li>
                    </ul>
                </div>
                <div class="connect-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Products</a></li>
                        <li><a href="about.php">Partners</a></li>
                        <li><a href="awards.php">Awards</a></li>
                    </ul>
                </div>
                <div class="connect-links">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="media.php">Media</a></li>
                        <li><a href="email.php">Email</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <?php include $tpl . 'footer.php'; ?>

    <script>
        // Enhanced slider functionality with horizontal sliding
        const sliderContainer = document.querySelector('.slider-container');
        const slides = document.querySelectorAll('.slide');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        let currentSlide = 0;
        const totalSlides = slides.length;

        function updateSlider() {
            sliderContainer.style.transform = `translateX(-${currentSlide * (100 / totalSlides)}%)`;
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlider();
        }

        // Event listeners for navigation buttons
        nextBtn.addEventListener('click', nextSlide);
        prevBtn.addEventListener('click', prevSlide);

        // Auto-advance slides every 5 seconds
        setInterval(nextSlide, 5000);
    </script>
</body>
</html>