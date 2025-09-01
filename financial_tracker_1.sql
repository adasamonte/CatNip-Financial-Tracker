-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 06, 2025 at 10:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `financial_tracker_1`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`) VALUES
(1, 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------

--
-- Table structure for table `cat_names`
--

CREATE TABLE `cat_names` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cat_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cat_names`
--

INSERT INTO `cat_names` (`id`, `user_id`, `cat_name`, `created_at`, `updated_at`) VALUES
(1, 4, 'Maki', '2025-03-06 08:45:39', '2025-03-06 09:06:00'),
(6, 7, 'Gorge', '2025-03-06 09:18:23', '2025-03-06 09:18:23');

-- --------------------------------------------------------

--
-- Table structure for table `daily_savings`
--

CREATE TABLE `daily_savings` (
  `Day_ID` int(11) NOT NULL,
  `Day` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `Daily_Income` int(11) NOT NULL,
  `Daily_Fixed_Expenses` int(11) NOT NULL,
  `Daily_Variable_Expenses` int(11) NOT NULL,
  `Unexpected_Daily_Costs` int(11) NOT NULL,
  `Daily_Savings_From_Previous_Day` int(11) NOT NULL,
  `Daily_Savings` int(11) NOT NULL,
  `Description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_savings`
--

INSERT INTO `daily_savings` (`Day_ID`, `Day`, `user_id`, `Daily_Income`, `Daily_Fixed_Expenses`, `Daily_Variable_Expenses`, `Unexpected_Daily_Costs`, `Daily_Savings_From_Previous_Day`, `Daily_Savings`, `Description`) VALUES
(31, 1, 4, 200, 100, 50, 20, 10, 40, ''),
(32, 2, 4, 200, 100, 20, 20, 40, 100, ''),
(33, 3, 4, 200, 100, 50, 50, 100, 100, ''),
(34, 4, 4, 200, 100, 20, 0, 100, 180, ''),
(35, 5, 4, 200, 100, 0, 0, 180, 280, ''),
(36, 6, 4, 200, 100, 50, 20, 280, 310, ''),
(37, 7, 4, 200, 100, 100, 100, 310, 210, ''),
(39, 8, 4, 200, 100, 20, 0, 210, 290, ''),
(40, 1, 7, 200, 100, 50, 20, 10, 40, '');

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `target_amount` decimal(10,2) NOT NULL,
  `current_amount` decimal(10,2) DEFAULT 0.00,
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`goal_id`, `user_id`, `target_amount`, `current_amount`, `deadline`, `created_at`) VALUES
(1, 1, 1000.00, 0.00, '2024-02-02', '2025-02-20 06:44:49'),
(2, 2, 500.00, 0.00, '2025-06-30', '2025-02-20 06:44:49'),
(3, 3, 10000.00, 0.00, '2026-04-02', '2025-02-20 09:17:52'),
(4, 4, 1500.00, 40.00, '2025-04-08', '2025-02-20 10:09:06'),
(5, 5, 80000.00, 0.00, '2025-03-20', '2025-02-25 11:30:58');

-- --------------------------------------------------------

--
-- Table structure for table `moneyhistory`
--

CREATE TABLE `moneyhistory` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `moneyhistory`
--

INSERT INTO `moneyhistory` (`history_id`, `user_id`, `amount`, `description`, `created_at`) VALUES
(11, 1, 10.00, 'Feed', '2025-02-20 08:58:38'),
(12, 1, 100.00, 'Feed', '2025-02-20 08:59:38'),
(13, 1, 100.00, 'Feed', '2025-02-20 08:59:42'),
(78, 4, 40.00, 'Added money to goal', '2025-03-06 09:32:58');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('savings','expense') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `type`, `amount`, `date`, `description`) VALUES
(1, 1, 'savings', 100.00, '2025-02-20 06:44:49', 'Initial savings'),
(2, 1, 'expense', 50.00, '2025-02-20 06:44:49', 'Groceries'),
(3, 2, 'savings', 200.00, '2025-02-20 06:44:49', 'Savings for vacation');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gender` enum('male','female') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `profile_picture`, `created_at`, `gender`) VALUES
(1, 'testuser1', 'test1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profile1.jpg', '2025-02-20 06:44:49', 'male'),
(2, 'testuser2', 'test2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profile2.jpg', '2025-02-20 06:44:49', 'male'),
(3, 'SKURT', 'kurtpogi38@gmail.com', '$2y$10$iWD/JvmnMzofGO72.RqKzuF5EKIoAN6BEygNeucPDWvw1eCldIaJm', 'uploads/profile_pictures/67b6f340cb86c.jpg', '2025-02-20 09:17:52', 'male'),
(4, 'Stef', 'esteffanieringad@gmail.com', '$2y$10$u1bzWixI.BZm3k1tOztxC.Ftoy7T3qeR0raJd/jd9nWC8rTD33FWe', 'uploads/profile_pictures/67b6ff42ea76c.jpg', '2025-02-20 10:09:06', 'female'),
(5, 'boggart', 'boggart@gmail.com', '$2y$10$KWps7js84vpyPc.0IunquuEA2dwuNZ8e9Fi8WqdwNsKF1RO/i0PYu', 'uploads/profile_pictures/67bda9f20250d.jpg', '2025-02-25 11:30:58', 'male'),
(7, 'Jorjitechnic', 'georgehenry@gmail.com', '$2y$10$rmsddiNrFgApV5fiNCU5ZOfQYhqk.o.0Vayv3BrO.N5qlbdENFoyy', 'uploads/profile_pictures/67c962dbf33b1.jpg', '2025-03-06 08:54:52', 'male');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `cat_names`
--
ALTER TABLE `cat_names`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_cat` (`user_id`);

--
-- Indexes for table `daily_savings`
--
ALTER TABLE `daily_savings`
  ADD PRIMARY KEY (`Day_ID`),
  ADD KEY `fk_daily_savings_user` (`user_id`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `moneyhistory`
--
ALTER TABLE `moneyhistory`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cat_names`
--
ALTER TABLE `cat_names`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `daily_savings`
--
ALTER TABLE `daily_savings`
  MODIFY `Day_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `moneyhistory`
--
ALTER TABLE `moneyhistory`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cat_names`
--
ALTER TABLE `cat_names`
  ADD CONSTRAINT `cat_names_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `daily_savings`
--
ALTER TABLE `daily_savings`
  ADD CONSTRAINT `fk_daily_savings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `moneyhistory`
--
ALTER TABLE `moneyhistory`
  ADD CONSTRAINT `moneyhistory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
