-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 16, 2025 at 07:43 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4
SET
    SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET
    time_zone = "+00:00";

--
-- Database: `placement`
--
-- --------------------------------------------------------
--
-- Table structure for table `admin`
--
CREATE TABLE `admin` (
    `adminID` bigint(20) UNSIGNED NOT NULL,
    `name` varchar(64) NOT NULL,
    `email` varchar(128) NOT NULL,
    `password` varchar(256) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--
INSERT INTO
    `admin` (`adminID`, `name`, `email`, `password`)
VALUES
    (1, 'TPO', 'tpo@somaiya.edu', 'password');

-- --------------------------------------------------------
--
-- Table structure for table `application`
--
CREATE TABLE `application` (
    `applicationID` bigint(20) UNSIGNED NOT NULL,
    `jobID` bigint(20) UNSIGNED NOT NULL,
    `studentID` int(10) UNSIGNED NOT NULL,
    `status` varchar(50) NOT NULL DEFAULT 'Applied',
    `applied_date` datetime NOT NULL DEFAULT current_timestamp(),
    `updated_date` datetime DEFAULT NULL ON UPDATE current_timestamp(),
    `notes` text DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `application_status_history`
--
CREATE TABLE `application_status_history` (
    `historyID` bigint(20) UNSIGNED NOT NULL,
    `applicationID` bigint(20) UNSIGNED NOT NULL,
    `status` varchar(50) NOT NULL,
    `changed_by` bigint(20) UNSIGNED NOT NULL COMMENT 'adminID who changed the status',
    `change_date` datetime NOT NULL DEFAULT current_timestamp(),
    `notes` text DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `branch`
--
CREATE TABLE `branch` (`branch` varchar(64) NOT NULL) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--
INSERT INTO
    `branch` (`branch`)
VALUES
    ('COMPUTER_ENGINEERING'),
    ('ELECTRONICS_AND_COMPUTERS'),
    ('INFORMATION_TECHNOLOGY'),
    ('MECHANICAL_ENGINEERING');

-- --------------------------------------------------------
--
-- Table structure for table `company`
--
CREATE TABLE `company` (
    `companyID` bigint(20) UNSIGNED NOT NULL,
    `name` varchar(64) NOT NULL,
    `industry` varchar(128) NOT NULL,
    `website` varchar(128) NOT NULL,
    `mobile` varchar(10) NOT NULL,
    `email` varchar(128) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `gender`
--
CREATE TABLE `gender` (`gender` varchar(6) NOT NULL) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `gender`
--
INSERT INTO
    `gender` (`gender`)
VALUES
    ('FEMALE'),
    ('MALE'),
    ('OTHERS');

-- --------------------------------------------------------
--
-- Table structure for table `job`
--
CREATE TABLE `job` (
    `jobID` bigint(20) UNSIGNED NOT NULL,
    `companyID` bigint(20) UNSIGNED NOT NULL,
    `adminID` bigint(20) UNSIGNED NOT NULL,
    `title` varchar(128) NOT NULL,
    `description` varchar(2048) NOT NULL,
    `salary` bigint(20) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `student`
--
CREATE TABLE `student` (
    `studentID` bigint(20) UNSIGNED NOT NULL,
    `svvID` varchar(32) NOT NULL,
    `password_hash` varchar(255) NOT NULL,
    `name` varchar(64) NOT NULL,
    `gender` varchar(6) NOT NULL,
    `mobile` varchar(10) NOT NULL,
    `email` varchar(128) NOT NULL,
    `branch` varchar(64) NOT NULL,
    `programme` varchar(32) NOT NULL,
    `graduation` year (4) NOT NULL,
    `cgpa` varchar(5) NOT NULL,
    `resume_path` varchar(511) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Indexes for dumped tables
--
--
-- Indexes for table `admin`
--
ALTER TABLE `admin` ADD PRIMARY KEY (`adminID`),
ADD UNIQUE KEY `adminID` (`adminID`),
ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `application`
--
ALTER TABLE `application` ADD PRIMARY KEY (`applicationID`),
ADD UNIQUE KEY `applicationID` (`applicationID`),
ADD UNIQUE KEY `jobID` (`jobID`, `studentID`);

--
-- Indexes for table `application_status_history`
--
ALTER TABLE `application_status_history` ADD PRIMARY KEY (`historyID`),
ADD KEY `application_history_app` (`applicationID`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch` ADD PRIMARY KEY (`branch`);

--
-- Indexes for table `company`
--
ALTER TABLE `company` ADD PRIMARY KEY (`companyID`),
ADD UNIQUE KEY `companyID` (`companyID`),
ADD UNIQUE KEY `mobile` (`mobile`),
ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `gender`
--
ALTER TABLE `gender` ADD PRIMARY KEY (`gender`);

--
-- Indexes for table `job`
--
ALTER TABLE `job` ADD PRIMARY KEY (`jobID`),
ADD UNIQUE KEY `jobID` (`jobID`),
ADD KEY `job_company` (`companyID`),
ADD KEY `job_admin` (`adminID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student` ADD PRIMARY KEY (`studentID`),
ADD UNIQUE KEY `studentID` (`studentID`),
ADD UNIQUE KEY `mobile` (`mobile`),
ADD UNIQUE KEY `email` (`email`),
ADD UNIQUE KEY `svvID` (`svvID`),
ADD KEY `student_gender` (`gender`),
ADD KEY `student_branch` (`branch`);

--
-- AUTO_INCREMENT for dumped tables
--
--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin` MODIFY `adminID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;

--
-- AUTO_INCREMENT for table `application`
--
ALTER TABLE `application` MODIFY `applicationID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 1;

--
-- AUTO_INCREMENT for table `application_status_history`
--
ALTER TABLE `application_status_history` MODIFY `historyID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 1;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company` MODIFY `companyID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 1;

--
-- AUTO_INCREMENT for table `job`
--
ALTER TABLE `job` MODIFY `jobID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 1;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student` MODIFY `studentID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 1;

--
-- Constraints for dumped tables
--
--
-- Constraints for table `application_status_history`
--
ALTER TABLE `application_status_history` ADD CONSTRAINT `application_history_app` FOREIGN KEY (`applicationID`) REFERENCES `application` (`applicationID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job`
--
ALTER TABLE `job` ADD CONSTRAINT `job_admin` FOREIGN KEY (`adminID`) REFERENCES `admin` (`adminID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `job_company` FOREIGN KEY (`companyID`) REFERENCES `company` (`companyID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student` ADD CONSTRAINT `student_branch` FOREIGN KEY (`branch`) REFERENCES `branch` (`branch`),
ADD CONSTRAINT `student_gender` FOREIGN KEY (`gender`) REFERENCES `gender` (`gender`);

COMMIT;
