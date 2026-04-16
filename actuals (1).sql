-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 172.27.1.111:3306
-- Generation Time: Apr 01, 2026 at 07:15 AM
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
-- Table structure for table `actuals`
--

CREATE TABLE `actuals` (
  `entry_id` varchar(100) NOT NULL,
  `facility_id` varchar(100) NOT NULL,
  `department_id` varchar(100) DEFAULT NULL,
  `ihris_pid` varchar(200) NOT NULL,
  `schedule_id` int NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `date` date NOT NULL,
  `end` date NOT NULL,
  `allDay` varchar(4) NOT NULL DEFAULT 'true',
  `stream` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `actuals`
--

INSERT INTO `actuals` (`entry_id`, `facility_id`, `department_id`, `ihris_pid`, `schedule_id`, `color`, `date`, `end`, `allDay`, `stream`) VALUES
('2012-06-01person|118032', 'facility|Ghospital-212-4', '', 'person|118032', 24, '#d1a110', '2012-06-01', '2012-06-02', 'true', NULL),
('2012-06-01person|1190138', 'facility|Ghospital-212-4', '', 'person|1190138', 24, '#d1a110', '2012-06-01', '2012-06-02', 'true', NULL),
('2012-06-01person|122148', 'facility|Ghospital-212-4', '', 'person|122148', 24, '#d1a110', '2012-06-01', '2012-06-02', 'true', NULL),
('2012-06-01person|137933', 'facility|Ghospital-212-4', '', 'person|137933', 24, '#d1a110', '2012-06-01', '2012-06-02', 'true', NULL),
('2012-06-01person|156940', 'facility|Ghospital-212-4', '', 'person|156940', 25, '#29910d', '2012-06-01', '2012-06-02', 'true', NULL),
('2012-06-01person|170436', 'facility|Ghospital-212-4', '', 'person|170436', 25, '#29910d', '2012-06-01', '2012-06-02', 'true', NULL),
('2012-06-01person|198472', 'facility|Ghospital-212-4', '', 'person|198472', 25, '#29910d', '2012-06-01', '2012-06-02', 'true', NULL),
('2012-06-01person|626535', 'facility|Ghospital-212-4', '', 'person|626535', 24, '#d1a110', '2012-06-01', '2012-06-02', 'true', NULL),
('2012-06-01person|70325', 'facility|Ghospital-212-4', '', 'person|70325', 24, '#d1a110', '2012-06-01', '2012-06-02', 'true', NULL),
('2012-06-02person|1190138', 'facility|Ghospital-212-4', '', 'person|1190138', 24, '#d1a110', '2012-06-02', '2012-06-03', 'true', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actuals`
--
ALTER TABLE `actuals`
  ADD UNIQUE KEY `entry_id` (`entry_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `ihris_pid` (`ihris_pid`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `entry_id_2` (`entry_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `date` (`date`),
  ADD KEY `facility_id_2` (`facility_id`),
  ADD KEY `entry_id_3` (`entry_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
