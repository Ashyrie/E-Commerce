<?php 
$pageTitle = 'Autopay Station';
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
            --hover-color: #f0f0f0;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: var(--secondary-color);
        }

        /* Section Container */
        .section-container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 4rem 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .section-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* Title Styling */
        h2 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        /* Paragraph Styling */
        p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            text-align: justify;
        }

        /* Link Styling */
        a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #50268f;
        }

        /* Button Styling */
        .action-button {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 5px;
            text-decoration: none;
            text-transform: uppercase;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-top: 2rem;
            text-align: center;
        }

        .action-button:hover {
            background-color: #1a0046;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <section class="section-container">
        <h2>Parking System Automation</h2>
        <p>Parking System Automation provides innovative solutions for managing and automating parking facilities. This system is designed to enhance efficiency, minimize errors, and deliver a seamless user experience.</p>
        <p>Our parking automation solutions include ticketless entry, payment integration, and real-time data analytics for improved facility management.</p>
    </section>
    <?php include $tpl . 'footer.php'; ?>
</body>
</html>
