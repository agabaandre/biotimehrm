-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 01, 2026 at 08:37 AM
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
-- Table structure for table `biotime_devices`
--

CREATE TABLE `biotime_devices` (
  `id` int NOT NULL,
  `sn` varchar(100) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `area_code` text,
  `user_count` varchar(100) DEFAULT NULL,
  `face_count` varchar(100) DEFAULT NULL,
  `palm_count` varchar(100) DEFAULT NULL,
  `area_name` varchar(100) DEFAULT NULL,
  `last_activity` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `biotime_devices`
--

INSERT INTO `biotime_devices` (`id`, `sn`, `ip_address`, `area_code`, `user_count`, `face_count`, `palm_count`, `area_name`, `last_activity`) VALUES
(1, 'BYUJ191660018', '10.100.96.252', 'facility|787', '894', '0', '0', 'Ministry of Health', '2024-07-23 11:33:29'),
(2, 'BYUJ202060149', '10.100.97.31', 'facility|787', '895', '0', '0', 'Ministry of Health', '2024-07-23 11:29:55'),
(3, 'BYUJ202060148', '192.168.0.192', 'facility|787', '735', '0', '0', 'Ministry of Health', '2024-03-18 15:42:33'),
(4, 'BYUJ202060018', '192.168.101.76', 'facility|787', '895', '0', '0', 'Ministry of Health', '2024-07-23 11:29:54'),
(5, 'MED7241100325', '192.168.0.10', 'facility|Ghospital-112-1', '134', '97', '0', 'KAYUNGA HOSPITAL', '2024-07-23 11:12:45'),
(6, 'MED7241100332', '192.168.0.167', 'facility|Ghospital-112-1', '133', '97', '0', 'KAYUNGA HOSPITAL', '2024-07-22 19:37:50'),
(7, '3929091915179', '192.168.88.12', 'facility|781', '373', '0', '0', 'CHINA-UGANDA Friendship Naguru', '2024-07-23 11:31:45'),
(8, '3929091915167', '192.168.88.81', 'facility|767', '679', '0', '0', 'JINJA RRH', '2024-07-23 11:23:25'),
(9, 'BYUJ202060035', '192.168.1.119', 'UCMB-facility|GHospital-214-1', '191', '0', '0', 'BULUBA HOSPITAL', '2023-12-07 14:55:30'),
(10, '3929091915170', '192.168.1.124', 'facility|769', '504', '0', '0', 'MBARARA Regional Referral Hosp', '2024-07-23 11:33:09'),
(11, 'BYUJ192260042', '192.168.88.254', 'facility|770', '393', '0', '0', 'GULU Regional Referral', '2024-07-23 11:33:31'),
(12, '3929091915172', '172.21.0.17', 'facility|771', '435', '0', '0', 'LIRA Regional Referral', '2024-07-23 11:28:10'),
(13, '3929091915177', '192.168.94.156', 'facility|768', '482', '0', '0', 'MBALE Regional Referral', '2024-07-23 11:32:38'),
(14, '3929091915165', '192.168.10.19', 'facility|772', '345', '0', '0', 'ARUA Regional Referral', '2024-02-05 16:51:32'),
(15, '3929091915184', '192.168.0.12', 'facility|773', '361', '0', '0', 'MOROTO Regional Referral', '2024-07-23 11:30:45'),
(16, '3929091915174', '172.16.0.195', 'facility|775', '368', '0', '0', 'HOIMA Regional Referral', '2024-07-23 11:29:53'),
(17, '3929091915182', '172.16.20.184', 'facility|776', '341', '0', '0', 'KABALE Regional Referral', '2024-07-23 11:29:49'),
(18, '3929091915176', '172.16.10.11', 'facility|777', '330', '0', '0', 'MASAKA Regional Referral', '2024-07-23 11:32:06'),
(19, '3929091915178', '172.23.0.147', 'facility|778', '450', '0', '0', 'MUBENDE Regional Referral', '2024-07-23 11:33:36'),
(20, '3929091915181', '192.168.1.17', 'facility|779', '344', '0', '0', 'SOROTI Regional Referral', '2024-07-23 11:29:38'),
(21, '3929091915183', '192.168.88.201', 'facility|788', '433', '0', '0', 'FORT PORTAL Regional Referral', '2024-07-21 15:05:00'),
(22, '3929091915180', '172.16.0.19', 'facility|Ghospital-313-1', '28', '0', '0', 'YUMBE REGIONAL REFERAL', '2022-05-04 06:37:09'),
(23, 'CEPP185160034', '10.100.102.130', 'facility|789', '361', '0', '0', 'KIRUDDU National Referral Hosp', '2024-07-23 11:33:33'),
(24, 'BYUJ202060146', '10.100.101.181', 'facility|789', '360', '0', '0', 'KIRUDDU National Referral Hosp', '2024-07-23 11:29:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `biotime_devices`
--
ALTER TABLE `biotime_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sn` (`sn`),
  ADD KEY `ip_address` (`ip_address`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `biotime_devices`
--
ALTER TABLE `biotime_devices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
