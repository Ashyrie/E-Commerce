CREATE DATABASE `deltechecom` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `deltechecom`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Table structure for table `admin`
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `biographical` text NOT NULL,
  `phone` varchar(10) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`, `email`, `phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE admin ADD reset_token VARCHAR(255) NULL;


INSERT INTO `admin` (`username`, `password`, `fullname`, `email`, `biographical`, `phone`, `created`) VALUES
('deltechadmin', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', 'admin', 'contact@admin.com', 'testing lang i2.', '01234565', '2024-10-24 21:44:00');

-- Table structure for table `currencies`
CREATE TABLE `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `currency` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `currency` (`currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `currencies` (`currency`) VALUES ('PHP'), ('USD');

-- Table structure for table `customers`
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_customer` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL UNIQUE, -- Added username
  `email_customer` varchar(255) NOT NULL,
  `phone_customer` varchar(10) NOT NULL,
  `address` TEXT NOT NULL, -- Added address
  `password` VARCHAR(255) NOT NULL, -- Added password
  `date_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE customers ADD note_customer TEXT;
ALTER TABLE customers ADD COLUMN reset_token VARCHAR(255) NULL;


-- eto yung mga bagong fields ai 

ALTER TABLE `customers`
    ADD COLUMN `company_name` VARCHAR(255) NULL AFTER `name_customer`,
    ADD COLUMN `company_address` TEXT NULL AFTER `company_name`,
    ADD COLUMN `job_title` VARCHAR(100) NULL AFTER `company_address`,
    ADD COLUMN verification_code VARCHAR(6) NULL AFTER password,
    ADD COLUMN `business_document` VARCHAR(255) NULL AFTER `verification_code`,
    ADD COLUMN `is_verified` TINYINT(1) DEFAULT 0 NULL AFTER `business_document`;

INSERT INTO `customers` (`name_customer`, `username`, `email_customer`, `phone_customer`, `address`, `password`, `date_at`) VALUES
('Juan Dela Cruz', 'juandc', 'juan.delacruz@example.com', '09171234567', '123 Main St', 'password123', '2023-06-20 22:32:10'),
('Maria Santos', 'marias', 'maria.santos@example.com', '09281234567', '456 Elm St', 'password456', '2023-06-21 14:03:34'),
('Jose Rizal', 'joser', 'jose.rizal@example.com', '09391234567', '789 Oak St', 'password789', '2023-06-21 15:49:54');

-- Table structure for table `orders`
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orders_number` varchar(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_name` text NOT NULL,
  `product_quantity` varchar(20) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `currency` varchar(20) NOT NULL,
  `subtotal` varchar(20) NOT NULL,
  `note_customer` text NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_status` enum('pending', 'cancelled', 'processing', 'pending payment', 'completed', 'failed') NOT NULL DEFAULT 'pending payment',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`currency`) REFERENCES `currencies`(`currency`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert orders AFTER customers are created
INSERT INTO `orders` (`orders_number`, `customer_id`, `product_name`, `product_quantity`, `product_price`, `currency`, `subtotal`, `note_customer`, `order_date`, `order_status`) VALUES
('#23380', 1, 'Parking Sensor Kit - 4 Sensors', '2', 49.99, 'PHP', '99.98', 'Juan Dela Cruz', '2023-06-20 22:32:10', 'completed'),
('#68817', 2, 'Wireless Parking Sensor System', '1', 89.99, 'PHP', '89.99', 'Maria Santos', '2023-06-21 16:23:27', 'completed'),
('#18661', 3, 'Parking Assist Camera', '3', 39.99, 'PHP', '119.97', 'Rush order', '2023-06-21 15:49:54', 'completed');


CREATE TABLE `user_cart` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    product_id INT,
    quantity INT,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);


-- Table structure for table `products`
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_product` text NOT NULL,
  `description_product` text NOT NULL,
  `price_product` decimal(10,2) NOT NULL,
  `currency` varchar(20) NOT NULL,
  `img_product` varchar(255) NOT NULL,
  `stock_product` int(11) NOT NULL,  -- Changed to INT for better stock management
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`currency`) REFERENCES `currencies`(`currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` (`name_product`, `description_product`, `price_product`, `currency`, `img_product`, `stock_product`, `created_at`) VALUES
('Parking Sensor Kit - 4 Sensors', '✅ Complete parking sensor kit with 4 ultrasonic sensors. ✅ Easy installation. ✅ Weather-resistant.', 49.99, 'USD', 'sensor_kit.webp', 20, NOW()),
('Wireless Parking Sensor System', '✅ Wireless parking sensor system for easy installation. ✅ Real-time obstacle detection.', 89.99, 'USD', 'wireless_sensor.jpg', 15, NOW());

-- Table structure for table `status`
CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `status` (`name`) VALUES
('pending'),
('cancelled'),
('processing'),
('pending payment'),
('completed'),
('failed');

ALTER TABLE currencies ADD COLUMN symbol VARCHAR(10);
UPDATE currencies SET symbol = '$' WHERE currency = 'USD';
UPDATE currencies SET symbol = '₱' WHERE currency = 'PHP';

-- AUTO_INCREMENT for dumped tables
ALTER TABLE `admin` AUTO_INCREMENT=2;
ALTER TABLE `currencies` AUTO_INCREMENT=3;
ALTER TABLE `customers` AUTO_INCREMENT=4;  -- Update if needed
ALTER TABLE `orders` AUTO_INCREMENT=4;  -- Update if needed
ALTER TABLE `products` AUTO_INCREMENT=3;  -- Update if needed
ALTER TABLE `status` AUTO_INCREMENT=7;

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` text NOT NULL DEFAULT 'none',
  `message` text NOT NULL,
  `created_c` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT,
    `product_id` INT,
    `message` TEXT,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `messages`
ADD COLUMN sender_type ENUM('customer', 'admin') NOT NULL DEFAULT 'customer';

ALTER TABLE `messages`
ADD COLUMN `sender_id` INT NOT NULL DEFAULT 0;

ALTER TABLE `messages`
ADD COLUMN admin_id INT NULL AFTER customer_id;
---ETONG NASA BABA MGA BAGONG ADD

ALTER TABLE `messages` ADD COLUMN `ticket_id` VARCHAR(20) NOT NULL AFTER `product_id`;

CREATE TABLE `support_tickets` (
    `ticket_id` VARCHAR(20) PRIMARY KEY, -- Unique ticket ID (e.g., "TICKET-20241111-001")
    `customer_id` INT(11) NOT NULL,
    `product_id` INT(11) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `support_tickets` ADD `resolved` ENUM('yes', 'no') NOT NULL DEFAULT 'no';


---latest update (for normalization of some parts nung customer table)

CREATE TABLE `customer_companies` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL,  -- Foreign key to the `customers` table
  `company_name` VARCHAR(255) NOT NULL,
  `company_address` TEXT NOT NULL,
  `job_title` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `customers`
  DROP COLUMN `company_name`,
  DROP COLUMN `company_address`,
  DROP COLUMN `job_title`;

  ALTER TABLE `customer_companies`
    ADD COLUMN `business_document` VARCHAR(255) NULL AFTER `job_title`;


    ALTER TABLE `customers`
    DROP COLUMN `business_document`;
  



COMMIT;


---latest