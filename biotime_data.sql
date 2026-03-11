-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 01, 2026 at 08:41 AM
-- Server version: 9.2.0
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attend`
--

-- --------------------------------------------------------

--
-- Table structure for table `biotime_data`
--

CREATE TABLE `biotime_data` (
  `id` int NOT NULL,
  `emp_code` varchar(100) DEFAULT NULL,
  `terminal_sn` varchar(100) DEFAULT NULL,
  `area_alias` varchar(100) DEFAULT NULL,
  `longitude` varchar(100) DEFAULT NULL,
  `latitude` varchar(100) DEFAULT NULL,
  `punch_state` varchar(100) NOT NULL,
  `punch_time` varchar(100) DEFAULT NULL,
  `ihris_pid` varchar(100) DEFAULT NULL,
  `last_sync` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `biotime_data`
--

INSERT INTO `biotime_data` (`id`, `emp_code`, `terminal_sn`, `area_alias`, `longitude`, `latitude`, `punch_state`, `punch_time`, `ihris_pid`, `last_sync`) VALUES
(1, '008698679', '3929091915182', 'KABALE Regional Referral', '0', '0', '0', '2026-01-28 15:10:34', NULL, '2026-01-28 19:51:31'),
(2, '008698417', '3929091915182', 'KABALE Regional Referral', '0', '0', '0', '2026-01-28 15:19:58', NULL, '2026-01-28 19:51:31'),
(3, '019195012', '3929091915182', 'KABALE Regional Referral', '0', '0', '0', '2026-01-28 15:09:53', NULL, '2026-01-28 19:51:31'),
(4, '008861850', '3929091915182', 'KABALE Regional Referral', '0', '0', '0', '2026-01-28 15:09:59', NULL, '2026-01-28 19:51:31'),
(5, '014493224', '3929091915182', 'KABALE Regional Referral', '0', '0', '0', '2026-01-28 15:10:23', NULL, '2026-01-28 19:51:31'),
(6, '003501852', '3929091915182', 'KABALE Regional Referral', '0', '0', '0', '2026-01-28 15:21:21', NULL, '2026-01-28 19:51:31'),
(7, '018769447', '3929091915182', 'KABALE Regional Referral', '0', '0', '0', '2026-01-28 15:21:23', NULL, '2026-01-28 19:51:31'),
(8, '003138503', '3929091915182', 'KABALE Regional Referral', '0', '0', '0', '2026-01-28 15:22:36', NULL, '2026-01-28 19:51:31'),
(9, '008861277', '3929091915182', 'KABALE Regional Referral', '0', '0', '0', '2026-01-28 15:24:23', NULL, '2026-01-28 19:51:31'),
(10, '008986556', '3929091915182', 'KABALE Regional Referral', '0', '0', '0', '2026-01-28 15:25:16', NULL, '2026-01-28 19:51:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `biotime_data`
--
ALTER TABLE `biotime_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_code` (`emp_code`),
  ADD KEY `terminal_sn` (`terminal_sn`),
  ADD KEY `area_alias` (`area_alias`),
  ADD KEY `ihris_pid` (`ihris_pid`),
  ADD KEY `punch_time` (`punch_time`),
  ADD KEY `punch_state` (`punch_state`),
  ADD KEY `id` (`id`),
  ADD KEY `emp_code_2` (`emp_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `biotime_data`
--
ALTER TABLE `biotime_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
