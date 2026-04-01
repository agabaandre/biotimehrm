-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 172.27.1.111:3306
-- Generation Time: Apr 01, 2026 at 06:11 AM
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
-- Table structure for table `person_att_final`
--

CREATE TABLE `person_att_final` (
  `id` int NOT NULL,
  `entry_id` varchar(100) NOT NULL,
  `ihris_pid` varchar(100) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `othername` varchar(100) NOT NULL,
  `gender` varchar(100) DEFAULT NULL,
  `facility_id` varchar(100) NOT NULL,
  `department_id` varchar(100) NOT NULL,
  `facility_name` varchar(150) NOT NULL,
  `district` varchar(100) NOT NULL,
  `facility_type_name` varchar(100) NOT NULL,
  `cadre` varchar(100) NOT NULL,
  `institution_type` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `schedule_id` varchar(20) NOT NULL,
  `duty_date` varchar(100) NOT NULL,
  `job` varchar(100) NOT NULL,
  `P` int NOT NULL,
  `O` int NOT NULL,
  `L` int NOT NULL,
  `R` int NOT NULL,
  `X` int NOT NULL,
  `H` int NOT NULL,
  `base_line` varchar(100) DEFAULT NULL,
  `last_gen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `person_att_final`
--

INSERT INTO `person_att_final` (`id`, `entry_id`, `ihris_pid`, `fullname`, `othername`, `gender`, `facility_id`, `department_id`, `facility_name`, `district`, `facility_type_name`, `cadre`, `institution_type`, `region`, `schedule_id`, `duty_date`, `job`, `P`, `O`, `L`, `R`, `X`, `H`, `base_line`, `last_gen`) VALUES
(194378, 'person|1748276-2022-03', 'person|1748276', 'Zaabali Prossy ', '', '', 'facility|HCIII-112-7', '', 'BUSAALE Health Centre II', 'KAYUNGA', 'HCII', 'Midwifery Professionals', 'District', 'Central North (North Buganda)', '24', '2022-03', 'Enrolled Midwife', 0, 8, 0, 0, 0, 0, '31', '2022-10-31 09:00:00'),
(200799, 'person|1748276-2022-04', 'person|1748276', 'Zaabali Prossy ', '', '', 'facility|HCIII-112-7', '', 'BUSAALE Health Centre II', 'KAYUNGA', 'HCII', 'Midwifery Professionals', 'District', 'Central North (North Buganda)', '24', '2022-04', 'Enrolled Midwife', 0, 8, 0, 0, 0, 0, '30', '2022-10-31 09:00:00'),
(207395, 'person|1748276-2022-05', 'person|1748276', 'Zaabali Prossy ', '', '', 'facility|HCIII-112-7', '', 'BUSAALE Health Centre II', 'KAYUNGA', 'HCII', 'Midwifery Professionals', 'District', 'Central North (North Buganda)', '24', '2022-05', 'Enrolled Midwife', 0, 9, 0, 0, 0, 0, '31', '2022-10-31 09:00:00'),
(3414164, 'person|122106-2021-06', 'person|122106', 'Chebandege Anne ', '', 'Female', 'facility|Ghospital-206-1', '', 'KAPCHORWA HOSPITAL', 'KAPCHORWA', 'General Hospital', 'Nursing Professionals', 'District', 'Elgon (Bukedi, Bugisu & Sebei)', '24', '2021-06', 'Senior Enrolled Nurse - Nursing', 0, 8, 0, 0, 0, 0, '30', '2022-11-03 12:00:00'),
(3416703, 'person|1721383-2021-06', 'person|1721383', 'Kalanda Andrew ', '', 'Male', 'facility|HCII-110-20', '', 'GAYAZA Health Centre II GOVT', 'KYOTERA', 'HCII', 'Support Staffs', 'District', 'Central South (South Buganda)', '22', '2021-06', 'Porter', 25, 0, 0, 0, 0, 0, '30', '2022-11-03 12:00:00'),
(3418434, 'person|343357-2021-06', 'person|343357', 'Mutebi Godfrey ', '', 'Male', 'facility|HCIII-110-9', '', 'MUTUKULA Health Centre III', 'KYOTERA', 'HCIII', 'Support Staffs', 'District', 'Central South (South Buganda)', '22', '2021-06', 'Porter', 27, 0, 0, 0, 1, 0, '30', '2022-11-03 12:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `person_att_final`
--
ALTER TABLE `person_att_final`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `entry_id` (`entry_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `job` (`job`),
  ADD KEY `P` (`P`),
  ADD KEY `O` (`O`),
  ADD KEY `L` (`L`),
  ADD KEY `R` (`R`),
  ADD KEY `X` (`X`),
  ADD KEY `H` (`H`),
  ADD KEY `duty_date` (`duty_date`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `facility_name` (`facility_name`),
  ADD KEY `ihris_pid` (`ihris_pid`),
  ADD KEY `facility_type_name` (`facility_type_name`),
  ADD KEY `cadre` (`cadre`),
  ADD KEY `institution_type` (`institution_type`),
  ADD KEY `region` (`region`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `fullname` (`fullname`),
  ADD KEY `othername` (`othername`),
  ADD KEY `gender` (`gender`),
  ADD KEY `district` (`district`),
  ADD KEY `last_gen` (`last_gen`),
  ADD KEY `base_line` (`base_line`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `person_att_final`
--
ALTER TABLE `person_att_final`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2147106463;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
