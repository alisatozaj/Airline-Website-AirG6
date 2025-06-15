-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2025 at 02:21 PM
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
-- Database: `airline`
--
CREATE DATABASE IF NOT EXISTS `airline` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `airline`;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `departure_flight_id` int(11) NOT NULL,
  `return_flight_id` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `confirmation_code` varchar(10) DEFAULT NULL,
  `passenger_count` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `departure_flight_id`, `return_flight_id`, `total_price`, `booking_date`, `confirmation_code`, `passenger_count`) VALUES
(49, NULL, 1, 9, 2625.00, '2025-05-24 11:29:48', 'RUS-135', 3),
(52, NULL, 1, 9, 2625.00, '2025-05-24 14:33:57', 'DWA-461', 3),
(53, NULL, 2, 10, 2400.00, '2025-05-24 14:35:30', 'BLS-039', 2),
(54, NULL, 1, NULL, 450.00, '2025-05-24 21:45:33', 'QLC-580', 1),
(56, NULL, 1, 9, 1750.00, '2025-05-28 15:24:23', 'SDT-948', 2),
(57, 2, 1, NULL, 450.00, '2025-05-28 21:47:17', 'TDF-547', 1),
(58, 2, 1, 9, 1750.00, '2025-05-28 22:16:36', 'YUT-829', 2),
(59, 2, 1, 9, 1750.00, '2025-05-29 00:41:39', 'ZGL-170', 2),
(60, 2, 1, 9, 1750.00, '2025-05-29 00:47:05', 'CMB-305', 2),
(61, 2, 1, 9, 1750.00, '2025-05-29 00:54:36', 'MVX-046', 2),
(62, NULL, 1, 9, 1750.00, '2025-05-29 08:19:38', 'VFC-749', 2),
(63, 2, 1, 9, 1750.00, '2025-05-29 10:02:51', 'NQL-245', 2),
(64, 2, 1, 9, 1750.00, '2025-05-29 13:39:42', 'UMD-903', 2),
(65, NULL, 1, 9, 1750.00, '2025-06-06 10:57:01', 'XDW-681', 2);

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

DROP TABLE IF EXISTS `flights`;
CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `departure` varchar(100) DEFAULT NULL,
  `arrival` varchar(100) DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `airline` varchar(100) DEFAULT NULL,
  `seats_available` int(11) DEFAULT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `duration` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flights`
--

INSERT INTO `flights` (`id`, `departure`, `arrival`, `departure_date`, `price`, `airline`, `seats_available`, `departure_time`, `arrival_time`, `duration`) VALUES
(1, 'New York', 'London', '2025-06-01', 450.00, 'AirG6', 113, '18:30:00', '06:15:00', '7h 45m'),
(2, 'Los Angeles', 'Tokyo', '2025-06-05', 600.00, 'AirG6', 80, '11:45:00', '15:30:00', '11h 45m'),
(3, 'London', 'Dubai', '2025-06-10', 500.00, 'AirG6', 100, '13:15:00', '23:15:00', '7h 00m'),
(4, 'Chicago', 'Paris', '2025-06-15', 550.00, 'AirG6', 90, '20:00:00', '10:00:00', '8h 00m'),
(5, 'Sydney', 'Los Angeles', '2025-06-20', 850.00, 'AirG6', 70, '10:30:00', '06:30:00', '14h 00m'),
(6, 'Barcelona', 'Paris', '2025-06-10', 180.00, 'AirG6', 120, '09:45:00', '11:45:00', '2h 00m'),
(7, 'Malaga', 'Berlin', '2025-06-20', 250.00, 'AirG6', 90, '10:00:00', '13:30:00', '3h 30m'),
(8, 'Singapore', 'Hong Kong', '2025-07-01', 350.00, 'AirG6', 95, '14:00:00', '18:00:00', '4h 00m'),
(9, 'London', 'New York', '2025-06-10', 425.00, 'AirG6', 118, '11:00:00', '14:15:00', '8h 15m'),
(10, 'Tokyo', 'Los Angeles', '2025-06-15', 600.00, 'AirG6', 80, '17:00:00', '10:30:00', '10h 30m'),
(11, 'Dubai', 'London', '2025-06-20', 500.00, 'AirG6', 100, '08:30:00', '12:30:00', '7h 00m'),
(12, 'Paris', 'Chicago', '2025-06-25', 550.00, 'AirG6', 90, '13:45:00', '16:15:00', '8h 30m'),
(13, 'Los Angeles', 'Sydney', '2025-07-05', 825.00, 'AirG6', 70, '23:45:00', '07:30:00', '15h 45m'),
(14, 'Paris', 'Barcelona', '2025-07-10', 180.00, 'AirG6', 110, '09:45:00', '11:45:00', '2h 00m'),
(15, 'Berlin', 'Malaga', '2025-07-15', 200.00, 'AirG6', 90, '10:00:00', '13:30:00', '3h 30m'),
(16, 'Hong Kong', 'Singapore', '2025-07-15', 350.00, 'AirG6', 95, '09:30:00', '13:15:00', '3h 45m'),
(22, 'Tirane', 'Rome', '2025-05-30', 100.00, 'AirG6', 100, '10:10:00', '12:12:00', '2h');

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

DROP TABLE IF EXISTS `passengers`;
CREATE TABLE `passengers` (
  `passenger_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` enum('Male','Female') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passengers`
--

INSERT INTO `passengers` (`passenger_id`, `booking_id`, `full_name`, `gender`) VALUES
(67, 49, 'user one', 'Male'),
(68, 49, 'user two', 'Male'),
(69, 49, 'user three', 'Male'),
(75, 52, 'user ten', 'Female'),
(76, 52, 'user two', 'Male'),
(77, 52, 'user three', 'Male'),
(78, 53, 'user one', 'Male'),
(79, 53, 'user two', 'Female'),
(80, 54, 'Alisa Tozaj', 'Female'),
(83, 56, 'ester', 'Female'),
(84, 56, 'alisa', 'Female'),
(85, 57, 'Alisa Tozaj', 'Female'),
(86, 58, 'user one', 'Male'),
(87, 58, 'user two', 'Female'),
(88, 59, 'Bob', 'Male'),
(89, 59, 'Alice', 'Female'),
(90, 60, 'Bob', 'Male'),
(91, 60, 'Alice', 'Female'),
(92, 61, 'user one', 'Male'),
(93, 61, 'user two', 'Female'),
(94, 62, 'user one', 'Male'),
(95, 62, 'user two', 'Female'),
(96, 63, 'user one', 'Male'),
(97, 63, 'user two', 'Female'),
(98, 64, 'user one', 'Female'),
(99, 64, 'user two', 'Male'),
(100, 65, 'alisa ', 'Female'),
(101, 65, 'fiona', 'Female');

-- --------------------------------------------------------

--
-- Table structure for table `seats_booked`
--

DROP TABLE IF EXISTS `seats_booked`;
CREATE TABLE `seats_booked` (
  `seat_id` int(11) NOT NULL,
  `flight_id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `seat_number` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seats_booked`
--

INSERT INTO `seats_booked` (`seat_id`, `flight_id`, `passenger_id`, `seat_number`) VALUES
(57, 1, 67, 'A5'),
(58, 1, 68, 'A6'),
(59, 1, 69, 'B1'),
(65, 1, 75, 'B2'),
(78, 1, 88, 'C2'),
(79, 1, 90, 'C4'),
(80, 1, 92, 'C3'),
(81, 1, 94, 'A3'),
(82, 1, 96, 'C5'),
(83, 1, 98, 'B6'),
(84, 1, 100, 'A2');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','admin') NOT NULL DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(2, 'admin', '$2y$10$oDhEcA0M8jw9bu/FCmx1luBfx1d/0oiwPz97UDURH.E6yUw4kr28K', 'admin', '2025-05-24 15:48:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `confirmation_code` (`confirmation_code`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `passengers`
--
ALTER TABLE `passengers`
  ADD PRIMARY KEY (`passenger_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `seats_booked`
--
ALTER TABLE `seats_booked`
  ADD PRIMARY KEY (`seat_id`),
  ADD KEY `fk_flight` (`flight_id`),
  ADD KEY `fk_passenger` (`passenger_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `passenger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `seats_booked`
--
ALTER TABLE `seats_booked`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `passengers`
--
ALTER TABLE `passengers`
  ADD CONSTRAINT `passengers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `seats_booked`
--
ALTER TABLE `seats_booked`
  ADD CONSTRAINT `fk_flight` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_passenger` FOREIGN KEY (`passenger_id`) REFERENCES `passengers` (`passenger_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
