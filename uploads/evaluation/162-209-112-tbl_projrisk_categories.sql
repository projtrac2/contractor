-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 15, 2023 at 03:01 PM
-- Server version: 8.0.32-0ubuntu0.20.04.2
-- PHP Version: 7.4.3-4ubuntu2.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `county`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_projrisk_categories`
--

CREATE TABLE `tbl_projrisk_categories` (
  `rskid` int NOT NULL,
  `opid` int DEFAULT NULL,
  `department` int DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `created_by` varchar(100) NOT NULL,
  `date_created` date NOT NULL,
  `changed_by` varchar(100) DEFAULT NULL,
  `date_changed` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_projrisk_categories`
--

INSERT INTO `tbl_projrisk_categories` (`rskid`, `opid`, `department`, `category`, `description`, `type`, `created_by`, `date_created`, `changed_by`, `date_changed`) VALUES
(1, NULL, NULL, 'Security', NULL, '1,2,3', '120', '2022-07-20', NULL, NULL),
(2, NULL, NULL, 'Financial', NULL, '2,3', '120', '2022-07-20', NULL, NULL),
(3, NULL, NULL, 'Human Resources', NULL, '2,3', '120', '2022-07-20', NULL, NULL),
(4, NULL, NULL, 'Climatic', NULL, '1,2,3', '120', '2022-07-20', NULL, NULL),
(5, NULL, NULL, 'Environmental', NULL, '2,3', '120', '2022-07-20', NULL, NULL),
(6, NULL, NULL, 'Legal', NULL, '1,2,3', '120', '2022-07-20', NULL, NULL),
(7, NULL, NULL, 'Political', NULL, '1,2,3', '120', '2022-07-20', NULL, NULL),
(8, NULL, NULL, 'Test 123', NULL, '1,2', '118', '2022-12-15', '118', '2022-12-15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_projrisk_categories`
--
ALTER TABLE `tbl_projrisk_categories`
  ADD PRIMARY KEY (`rskid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_projrisk_categories`
--
ALTER TABLE `tbl_projrisk_categories`
  MODIFY `rskid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
