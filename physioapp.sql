-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 20, 2023 at 11:32 AM
-- Server version: 10.5.19-MariaDB-cll-lve
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u936088023_physioapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patientId` varchar(50) NOT NULL,
  `doctorId` varchar(50) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `patientId` varchar(50) NOT NULL,
  `doctorId` varchar(50) NOT NULL,
  `serviceId` varchar(5) NOT NULL,
  `details` varchar(300) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refreshTokens`
--

CREATE TABLE `refreshTokens` (
  `id` int(11) NOT NULL,
  `userId` varchar(50) NOT NULL,
  `refreshToken` varchar(50) DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(300) NOT NULL,
  `cost` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `cost`, `created_at`) VALUES
('EX001', 'exampleService', 'This is an example service, created by manager 1', 50, '2023-05-06 14:25:44'),
('EX002', 'Example Service 2', 'This is another example service, created for testing', 25, '2023-05-08 12:39:21'),
('EX003', 'Example Service 3', 'Yet another example service.', 100, '2023-05-08 12:39:47'),
('EX987', 'Example 5', 'description ', 50, '2023-06-19 17:13:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `displayName` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `accountType` int(11) NOT NULL DEFAULT 0,
  `ssn` varchar(15) NOT NULL,
  `address` varchar(100) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `displayName`, `email`, `accountType`, `ssn`, `address`, `created_by`, `created_at`) VALUES
('139496bb-1a80-4eb7-bff8-b309c3211e03', 'doctor1', '$2y$10$6LStJqbHVdHNQ4lVk2D.AepLmqnz82HqWRZ9.fsaOIIN76yRgh1mq', 'Άννα Γούλα', 'ics21080@uom.edu.gr', 1, '29110201018', '25ης Μαρτίου', 'bf3f61a7-0964-409f-b453-a170159dfd4b', '2023-04-07 18:06:35'),
('bf3f61a7-0964-409f-b453-a170159dfd4b', 'manager1', '$2y$10$QjchqTrPlRLsIKn9C8ZPOOUYRJEemIJExybt6gTmZP14euYc17/cW', 'Άννα Γούλα', 'ics21081@uom.edu.gr', 0, '29110211111', 'Τσιμισκή 47', NULL, '2023-04-06 16:20:59'),
('dfbb4eba-a770-4f14-b944-6d89289cad66', 'patient1', '$2y$10$dRkMfohY48Ho/7MkYj0yKe5JKl//MeAGQmURe08QyPOkYcmECwJr6', 'Δημήτρης Παπαδόπουλος', 'ics21000@uom.edu.gr', 2, '29110201018', 'Λεωφ. Στρατού 120', '139496bb-1a80-4eb7-bff8-b309c3211e03', '2023-04-09 17:49:29'),
('dfbb4eba-a770-4f14-b944-6d89289cad77', 'patient2', '$2y$10$dRkMfohY48Ho/7MkYj0yKe5JKl//MeAGQmURe08QyPOkYcmECwJr6', 'Σπύρος Δελόγλου', 'ics21999@uom.edu.gr', 2, '29110201099', 'Καρόλου Ντιλ 19', '139496bb-1a80-4eb7-bff8-b309c3211e03', '2023-04-09 17:49:29'),
('e4f27ac1-fb87-4e35-9d79-e11f3aa4dba6', 'marias', '$2y$10$yK2L.KESw/PBOMOEFPOPOeYnYwTtmH61OctMNUh3ErTuWmOp7fkbe', 'Maria Papagianni', 'ics69420@uom.edu.gr', 2, '123456789', 'Konstantinoupoleos 14', '139496bb-1a80-4eb7-bff8-b309c3211e03', '2023-06-20 10:34:44');

-- --------------------------------------------------------

--
-- Table structure for table `xrequests`
--

CREATE TABLE `xrequests` (
  `id` int(11) NOT NULL,
  `originip` varchar(45) NOT NULL DEFAULT '',
  `ts` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Requests from remote IPs';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `xrequests`
--
ALTER TABLE `xrequests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ts` (`ts`),
  ADD KEY `originip` (`originip`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `xrequests`
--
ALTER TABLE `xrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
