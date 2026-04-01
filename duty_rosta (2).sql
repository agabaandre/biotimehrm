-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 172.27.1.111:3306
-- Generation Time: Apr 01, 2026 at 07:38 AM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mohattendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `duty_rosta`
--

CREATE TABLE `duty_rosta` (
  `id` int NOT NULL,
  `entry_id` varchar(100) NOT NULL,
  `facility_id` varchar(100) NOT NULL,
  `department_id` varchar(100) DEFAULT NULL,
  `ihris_pid` varchar(200) NOT NULL,
  `schedule_id` int NOT NULL,
  `color` varchar(20) NOT NULL,
  `duty_date` date NOT NULL,
  `end` date NOT NULL,
  `allDay` varchar(4) NOT NULL DEFAULT 'true'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `duty_rosta`
--

INSERT INTO `duty_rosta` (`id`, `entry_id`, `facility_id`, `department_id`, `ihris_pid`, `schedule_id`, `color`, `duty_date`, `end`, `allDay`) VALUES
(2439296, '2021-03-02person|377126', 'facility|213', '', 'person|377126', 14, '#297bb2', '2021-03-02', '2021-03-03', 'true'),
(2439297, '2021-03-03person|377126', 'facility|213', '', 'person|377126', 14, '#297bb2', '2021-03-03', '2021-03-04', 'true'),
(2439298, '2021-03-04person|377126', 'facility|213', '', 'person|377126', 14, '#297bb2', '2021-03-04', '2021-03-05', 'true'),
(10794149, '2021-01-01person|86988', 'facility|HCIII-203-5', NULL, 'person|86988', 14, '#297bb2', '2021-01-01', '2021-01-02', 'true'),
(10794150, '2021-01-02person|86988', 'facility|HCIII-203-5', NULL, 'person|86988', 14, '#297bb2', '2021-01-02', '2021-01-03', 'true'),
(10794151, '2021-01-03person|86988', 'facility|HCIII-203-5', NULL, 'person|86988', 14, '#297bb2', '2021-01-03', '2021-01-04', 'true'),
(10794152, '2021-01-04person|86988', 'facility|HCIII-203-5', NULL, 'person|86988', 14, '#297bb2', '2021-01-04', '2021-01-05', 'true'),
(10794153, '2021-01-05person|86988', 'facility|HCIII-203-5', NULL, 'person|86988', 14, '#297bb2', '2021-01-05', '2021-01-06', 'true'),
(10794154, '2021-01-06person|86988', 'facility|HCIII-203-5', NULL, 'person|86988', 14, '#297bb2', '2021-01-06', '2021-01-07', 'true'),
(10794155, '2021-01-07person|86988', 'facility|HCIII-203-5', NULL, 'person|86988', 14, '#297bb2', '2021-01-07', '2021-01-08', 'true'),
(10794156, '2021-01-08person|86988', 'facility|HCIII-203-5', NULL, 'person|86988', 14, '#297bb2', '2021-01-08', '2021-01-09', 'true');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `duty_rosta`
--
ALTER TABLE `duty_rosta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `entry_id` (`entry_id`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `ihris_pid` (`ihris_pid`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `duty_date` (`duty_date`),
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `duty_rosta`
--
ALTER TABLE `duty_rosta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176165171;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
