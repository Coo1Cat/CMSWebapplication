-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2023 at 09:40 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `customermanagementdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `cust_item`
--

CREATE TABLE `cust_item` (
  `item_id` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `item_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cust_item`
--

INSERT INTO `cust_item` (`item_id`, `item_name`, `item_quantity`, `item_price`, `item_image`) VALUES
('649839dbd8a76', 'Shirt', 60, 42.00, 'uploads/ssrco,classic_tee,womens,fafafa_ca443f4786,front_alt,square_product,600x600.jpg'),
('6499a9f94b0a0', 'Aye Eye Hoshino Shirt', 46, 23.50, 'uploads/aihoshino.jpg'),
('6499aadf2d944', 'SBaka Shirt', 68, 42.00, 'uploads/6499aafa1b8b2.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `name`, `email`, `phone`) VALUES
(17, 'Felix Liew', 'felixliew@gmail.com', '0123456798'),
(25, 'Jefferd Tate', 'jefferdtate@lowgmail.com', '0124206942'),
(26, 'Wong Andrew', 'wongandrew@smosh.com', '01987654321');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `purchase_id` varchar(255) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `member_name` varchar(255) DEFAULT NULL,
  `item_id` varchar(255) NOT NULL,
  `purchase_quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `purchase_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `purchase_id`, `member_id`, `member_name`, `item_id`, `purchase_quantity`, `total_price`, `purchase_date`) VALUES
(14, 'P6499a665dc247', NULL, NULL, '649839dbd8a76', 2, 84.00, '2023-06-28'),
(15, 'P6499aa3875410', 25, 'Jefferd Tate', '6499a9f94b0a0', 8, 188.00, '2023-06-27'),
(16, 'P6499aa6e360b8', 26, 'Wong Andrew', '649839dbd8a76', 2, 84.00, '2023-05-31'),
(17, 'P6499ab07ca2a9', 17, 'Felix Liew', '6499aadf2d944', 1, 42.00, '2023-06-26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(6, 'test', '$2y$10$g/qCcS4pqd0KXUuHSFPmKuath1JX3QEQwQMeWAjKYt3sVEEILsULG');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cust_item`
--
ALTER TABLE `cust_item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `item_id_2` (`item_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_purchases_item` (`item_id`),
  ADD KEY `purchases_ibfk_1` (`member_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `fk_purchases_item` FOREIGN KEY (`item_id`) REFERENCES `cust_item` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `cust_item` (`item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
