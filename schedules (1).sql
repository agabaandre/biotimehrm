-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 172.27.1.111:3306
-- Generation Time: Apr 01, 2026 at 07:14 AM
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
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int NOT NULL,
  `schedule` varchar(100) NOT NULL,
  `letter` varchar(1) NOT NULL,
  `starts` time NOT NULL,
  `ends` time NOT NULL,
  `color` varchar(100) NOT NULL,
  `purpose` varchar(1) NOT NULL DEFAULT 'r',
  `status` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `schedule`, `letter`, `starts`, `ends`, `color`, `purpose`, `status`) VALUES
(14, 'Day', 'D', '08:00:00', '17:00:00', '#297bb2', 'r', 1),
(15, 'Evening', 'E', '15:00:00', '21:00:00', '#245270', 'r', 1),
(16, 'Night', 'N', '21:00:00', '08:00:00', '#2f446b', 'r', 1),
(17, 'Off-duty', 'O', '00:00:00', '00:00:00', '#d1a110', 'r', 1),
(18, 'Annual leave', 'A', '00:00:00', '00:00:00', '#B22222', 'r', 1),
(19, 'Study Leave', 'S', '00:00:00', '00:00:00', '#FF8C00', 'r', 1),
(20, 'Maternity Leave', 'M', '00:00:00', '00:00:00', '#9ACD32', 'r', 1),
(21, 'Other Authorised Leave', 'Z', '00:00:00', '00:00:00', '#32CD32', 'r', 1),
(22, 'Present', 'P', '11:00:00', '00:00:00', '#4169E1', 'a', 1),
(23, 'Official Request', 'R', '00:00:00', '00:00:00', '#008B8B', 'a', 1),
(24, 'Off-Duty', 'O', '00:00:00', '00:00:00', '#d1a110', 'a', 1),
(25, 'Leave', 'L', '00:00:00', '00:00:00', '#29910d', 'a', 1),
(26, 'Absent', 'X', '00:00:00', '00:00:00', '#DC143C', 'a', 1),
(27, 'Holiday', 'H', '10:30:00', '00:00:00', '#C71585	', 'a', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `letter` (`letter`),
  ADD KEY `schedule` (`schedule`),
  ADD KEY `purpose` (`purpose`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
