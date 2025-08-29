-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 29, 2025 at 11:03 AM
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
  `is_incharge` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `ihrisdata`
--

INSERT INTO `ihrisdata` (`id`, `ihris_pid`, `district_id`, `district`, `dhis_facility_id`, `dhis_district_id`, `nin`, `card_number`, `ipps`, `facility_type_id`, `facility_id`, `facility`, `department_id`, `department`, `division`, `section`, `unit`, `job_id`, `job`, `employment_terms`, `salary_grade`, `surname`, `firstname`, `othername`, `mobile`, `telephone`, `institution_type_id`, `institutiontype_name`, `last_update`, `gender`, `birth_date`, `cadre`, `email`, `region`, `is_incharge`) VALUES
(1, 'person|99997', 'KAYUNGA', 'KAYUNGA', 'dhis_facility_id', 'dhis_district_id', 'CF75032105G05L', '826996', '826996', 'Regional Referral Hospital', 'facility|Ghospital-112-1', 'KAYUNGA Regional Referral Hospital', NULL, NULL, NULL, '', NULL, 'job|22210104', 'Nursing Officer - Nursing', 'employment_terms|Permanent', NULL, 'Nabiryo', 'Sarah', NULL, '0782327711', NULL, 'institution_type|1315300', 'Regional Referral Hospital', '2024-08-01 18:00:01', 'Female', '1975-07-25', 'Nursing Professionals', NULL, 'Central North (North Buganda)', NULL),
(2, 'person|290793', 'KAPELEBYONG', 'KAPELEBYONG', 'dhis_facility_id', 'dhis_district_id', 'CM870431022RHC', '960066', '960066', 'HCII', 'facility|HCII-216-19', 'AEKET Health Centre II', NULL, NULL, NULL, '', NULL, 'job|32530503', 'Health Assistant', 'employment_terms|Permanent', NULL, 'Opio', 'Francis', NULL, '0758973655', '0779973655', 'institution_type|1315297', 'District', '2024-08-01 18:00:01', 'Male', '1987-10-10', 'Allied Health Professionals', 'francisopio82@gmail.com', 'Eastern (Teso)', NULL);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58774;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
