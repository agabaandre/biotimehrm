-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 172.27.1.111:3306
-- Generation Time: Apr 01, 2026 at 07:18 AM
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
-- Table structure for table `ihrisdata`
--

CREATE TABLE `ihrisdata` (
  `id` int NOT NULL,
  `ihris_pid` varchar(200) NOT NULL,
  `district_id` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `dhis_facility_id` varchar(100) DEFAULT NULL,
  `dhis_district_id` varchar(100) DEFAULT NULL,
  `nin` varchar(100) DEFAULT NULL,
  `card_number` varchar(100) DEFAULT NULL,
  `ipps` varchar(100) DEFAULT NULL,
  `facility_type_id` varchar(100) DEFAULT NULL,
  `facility_id` varchar(100) DEFAULT NULL,
  `facility` varchar(100) DEFAULT NULL,
  `department_id` varchar(255) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `division` varchar(255) DEFAULT NULL,
  `section` varchar(100) NOT NULL,
  `unit` varchar(200) DEFAULT NULL,
  `job_id` varchar(100) DEFAULT NULL,
  `job` varchar(100) DEFAULT NULL,
  `employment_terms` varchar(100) DEFAULT NULL,
  `salary_grade` varchar(10) DEFAULT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `othername` varchar(100) DEFAULT NULL,
  `mobile` varchar(100) DEFAULT NULL,
  `telephone` varchar(100) DEFAULT NULL,
  `institution_type_id` varchar(100) DEFAULT NULL,
  `institutiontype_name` varchar(100) NOT NULL,
  `last_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `gender` varchar(30) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `cadre` varchar(100) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `region` varchar(100) NOT NULL,
  `is_incharge` int DEFAULT NULL,
  `is_active_employee` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `ihrisdata`
--

INSERT INTO `ihrisdata` (`id`, `ihris_pid`, `district_id`, `district`, `dhis_facility_id`, `dhis_district_id`, `nin`, `card_number`, `ipps`, `facility_type_id`, `facility_id`, `facility`, `department_id`, `department`, `division`, `section`, `unit`, `job_id`, `job`, `employment_terms`, `salary_grade`, `surname`, `firstname`, `othername`, `mobile`, `telephone`, `institution_type_id`, `institutiontype_name`, `last_update`, `gender`, `birth_date`, `cadre`, `email`, `region`, `is_incharge`, `is_active_employee`) VALUES
(703539, 'person|384798', 'DOKOLO', 'DOKOLO', 'dhis_facility_id', 'dhis_district_id', 'CF75022103KLPJ', '004824719', '822297', 'HCIII', 'facility|HCII-317-4', 'ABALANG DOKOLO Health Centre III', NULL, NULL, NULL, '', NULL, 'job|53210101', 'Nursing Assistant', 'employment_terms|Permanent', NULL, 'Apio', 'Martha', NULL, NULL, '0777777418', 'institution_type|1315297', 'District', '2025-04-22 01:00:02', 'Female', '1975-05-25', 'Nursing Professionals', NULL, 'Lango', NULL, 1),
(1141500, 'person|1420519', 'KABAROLE', 'KABAROLE', 'dhis_facility_id', 'dhis_district_id', NULL, '746104', '746104', 'HCIII', 'facility|896', 'KAGOTE Health Centre III', NULL, NULL, NULL, '', NULL, 'job|96290139', 'Assistant Nursing Officer - Nursing', 'employment_terms|Permanent', NULL, 'Aganyira', 'Alice', '', NULL, NULL, 'institution_type|1388964', 'City', '2025-04-24 01:00:01', 'Female', '1978-11-06', 'Nursing Professionals', NULL, 'Mid Western (Toro)', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ihrisdata`
--
ALTER TABLE `ihrisdata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ihris_pid` (`ihris_pid`),
  ADD KEY `facility_type_id` (`facility_type_id`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `district_id` (`district_id`),
  ADD KEY `id` (`id`,`ihris_pid`),
  ADD KEY `region` (`region`),
  ADD KEY `institution_type_id` (`institution_type_id`),
  ADD KEY `institutiontype_name` (`institutiontype_name`),
  ADD KEY `firstname` (`firstname`),
  ADD KEY `surname` (`surname`),
  ADD KEY `othername` (`othername`),
  ADD KEY `department` (`department`),
  ADD KEY `district` (`district`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `facility` (`facility`),
  ADD KEY `job` (`job`),
  ADD KEY `gender` (`gender`),
  ADD KEY `card_number` (`card_number`),
  ADD KEY `last_update` (`last_update`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ihrisdata`
--
ALTER TABLE `ihrisdata`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35502468;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
