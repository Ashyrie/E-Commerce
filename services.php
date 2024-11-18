<?php
session_name('client_session');
session_start();
$pageTitle = 'Services';
include './init.php';
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
        /* Styles from homepage.php */
        :root {
            --primary-color: #280068;
            --red-color: #B84141;
            --yellow-color: #F7C945;
        }

        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Service Section Styling */
        .services-section {
            padding: 4rem 2rem;
            background-color: #f5f5f5;
            text-align: center;
        }

        .services-title {
            color: #280068;
            font-size: 2.5rem;
            text-transform: uppercase;
            margin-bottom: 2rem;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .service-item {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .service-item:hover {
            transform: translateY(-5px);
        }

        .service-item h3 {
            color: #280068;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        .service-item p {
            color: #333;
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <section class="services-section">
        <h2 class="services-title">Our Services</h2>
        <div class="services-grid">
            <div class="service-item">
                <h3>Review and Recommend Parking Station Designs</h3>
                <p>Our team of experts reviews existing parking station layouts, providing design recommendations to enhance flow, maximize space, and improve safety, ensuring a smooth experience for users.</p>
            </div>
            <div class="service-item">
                <h3>Preventive Maintenance for Parking Equipment</h3>
                <p>Regular maintenance keeps parking equipment running efficiently. Our preventive services are designed to detect potential issues early, extend the lifespan of your equipment, and ensure operational reliability.</p>
            </div>
            <div class="service-item">
                <h3>Integration of Parking Equipment</h3>
                <p>We offer seamless integration of new parking technologies with your existing infrastructure, including payment systems, access control, and monitoring solutions, to create a cohesive and efficient system.</p>
            </div>
        </div>
    </section>

    <?php include $tpl . 'footer.php'; ?>
</body>
</html>
