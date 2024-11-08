<?php 
$pageTitle = 'About Us';
include './init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        /* General Styling */
        :root {
            --primary-color: #280068;
            --secondary-color: #f5f5f5;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: var(--secondary-color);
        }

        .section-title {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 1rem;
            text-align: center;
            text-transform: uppercase;
        }

        /* About Us Section */
        .about-container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 4rem 2rem;
            background-color: var(--secondary-color);
            text-align: center;
        }

        .about-content {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .about-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        h3 {
            color: white;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        p {
            color: #555;
            line-height: 1.6;
        }

        /* Vision & Mission Section */
        .vision-mission-container {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .vision-mission {
            flex: 1;
            min-width: 280px;
        }

        /* Partners Section */
        .partners-section {
            padding: 4rem 2rem;
            color: #333;
            text-align: center;
        }

        .partners-container { /* Container hover effect */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
            background-color:  #280068; 
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 1rem;
            border-radius: 10px;
        }

        .partners-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color:  #280068; 
            padding: 2rem;
            border-radius: 10px;
        }

        .partners-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .partner-logo {
            padding: 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 120px;
            background-color: white; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .partner-logo:hover {
    transform: translateY(-5px);
        }
        .partner-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <!-- About Us Section -->
    <section class="about-container">
        <div class="about-content">
            <h2>About Us</h2>
            <p>DELTECH PARKING SYSTEMS AND SOLUTIONS INC. is a company established to provide customized designed systems and automation that cater to the specific needs of its customers.</p>
            <br>
            <p>DELTECH allows its clients to focus on their core business, which is to increase revenues and customer satisfaction, by delivering high quality, reliable, and affordable systems and automation.</p>
        </div>

        <div class="vision-mission-container">
            <div class="vision-mission about-content">
                <h2>Our Vision</h2>
                <p>To be the best technology solutions provider for parking and point of sales systems.</p>
            </div>
            <div class="vision-mission about-content">
                <h2>Our Mission</h2>
                <p>To provide efficient services to our customers using the latest available technology.</p>
            </div>
        </div>
    </section>

    <!-- Partners Section -->
    <section class="partners-section">
        <div class="partners-container">
            <h3>We’ve worked with some of the best companies.</h3>
            <div class="partners-grid">
                <div class="partner-logo"><img src="assets/img/megaworld.png" alt="Megaworld"></div>
                <div class="partner-logo"><img src="assets/img/apmc.png" alt="APMC"></div>
                <div class="partner-logo"><img src="assets/img/mactan.png" alt="Mactan Cebu Airport"></div>
                <div class="partner-logo"><img src="assets/img/nustar.png" alt="Nustar Resort"></div>
                <div class="partner-logo"><img src="assets/img/starmall.png" alt="Starmall"></div>
                <div class="partner-logo"><img src="assets/img/global.png" alt="Global"></div>
                <div class="partner-logo"><img src="assets/img/eton.png" alt="Eton Centris"></div>
                <div class="partner-logo"><img src="assets/img/metro_parking.png" alt="Metro Parking"></div>
                <div class="partner-logo"><img src="assets/img/mpt_mobility.png" alt="MPT Mobility"></div>
                <div class="partner-logo"><img src="assets/img/st_lukes.png" alt="St. Luke's Medical Center"></div>
                <div class="partner-logo"><img src="assets/img/makati_medical.png" alt="Makati Medical Center"></div>
                <div class="partner-logo"><img src="assets/img/mcu.png" alt="MCU"></div>
                <div class="partner-logo"><img src="assets/img/ramky.png" alt="Ramky Group"></div>
                <div class="partner-logo"><img src="assets/img/medical_city.png" alt="The Medical City"></div>
                <div class="partner-logo"><img src="assets/img/u_park.png" alt="U Park"></div>
                <div class="partner-logo"><img src="assets/img/oakridge.png" alt="Oakridge"></div>
                <div class="partner-logo"><img src="assets/img/parqal.png" alt="Parqal"></div>
                <div class="partner-logo"><img src="assets/img/sm.png" alt="SM"></div>
                <div class="partner-logo"><img src="assets/img/solemare.png" alt="SoleMare Parksuites"></div>
                <div class="partner-logo"><img src="assets/img/systems_variable.png" alt="Systems Variable Technicon Inc."></div>
            </div>
        </div>
    </section>

    <?php include $tpl . 'footer.php'; ?>
</body>
</html>
