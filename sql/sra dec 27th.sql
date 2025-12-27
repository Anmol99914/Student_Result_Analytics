-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 27, 2025 at 04:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sra`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrator`
--

INSERT INTO `administrator` (`admin_id`, `name`, `email`, `password`) VALUES
(1, 'Anmol', 'admin@gmail.com', '$2y$10$C0VaausqK4AkkLhZELd9we9.h9vioxUJ0nllLBqYN2v3XnG.Ilwni');

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `faculty` varchar(50) NOT NULL,
  `semester` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `batch_year` year(4) NOT NULL DEFAULT year(curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `faculty`, `semester`, `status`, `created_at`, `batch_year`) VALUES
(1, 'BCA', 1, 'active', '2025-12-25 15:08:35', '2023'),
(2, 'BCA', 2, 'active', '2025-12-25 15:08:35', '2023'),
(3, 'BCA', 3, 'active', '2025-12-25 15:08:35', '2023'),
(4, 'BCA', 4, 'active', '2025-12-25 15:08:35', '2023'),
(5, 'BCA', 5, 'active', '2025-12-25 15:08:35', '2023'),
(6, 'BCA', 6, 'active', '2025-12-25 15:08:35', '2023'),
(7, 'BCA', 7, 'active', '2025-12-25 15:08:35', '2023'),
(8, 'BCA', 8, 'active', '2025-12-25 15:08:35', '2023');

-- --------------------------------------------------------

--
-- Table structure for table `class_backup`
--

CREATE TABLE `class_backup` (
  `class_id` int(11) NOT NULL DEFAULT 0,
  `faculty` varchar(50) NOT NULL,
  `semester` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_backup`
--

INSERT INTO `class_backup` (`class_id`, `faculty`, `semester`, `teacher_id`, `status`, `created_at`) VALUES
(10, 'BIT', 1, 1, 'active', '2025-12-20 07:31:07'),
(11, 'BBM', 1, 2, 'active', '2025-12-20 07:46:16'),
(12, 'BCA', 1, 1, 'active', '2025-12-21 02:53:55'),
(13, 'BBA', 1, 2, 'active', '2025-12-21 05:07:18'),
(14, 'BIM', 1, 4, 'active', '2025-12-21 06:07:08');

-- --------------------------------------------------------

--
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_subjects`
--

INSERT INTO `class_subjects` (`id`, `class_id`, `subject_id`, `teacher_id`, `created_at`) VALUES
(1, 1, 16, NULL, '2025-12-27 08:20:01'),
(2, 1, 17, NULL, '2025-12-27 08:20:02'),
(3, 1, 18, NULL, '2025-12-27 08:20:02'),
(4, 1, 19, NULL, '2025-12-27 08:20:02'),
(5, 1, 20, NULL, '2025-12-27 08:20:02'),
(6, 2, 21, NULL, '2025-12-27 08:56:05'),
(7, 2, 22, NULL, '2025-12-27 08:56:05'),
(8, 2, 23, NULL, '2025-12-27 08:56:05'),
(9, 2, 24, NULL, '2025-12-27 08:56:05'),
(10, 2, 25, NULL, '2025-12-27 08:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `student_id` varchar(10) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `due_amount` decimal(10,2) GENERATED ALWAYS AS (`total_amount` - `paid_amount`) STORED,
  `payment_status` enum('Paid','Partial','Unpaid') DEFAULT 'Unpaid',
  `payment_date` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `result`
--

CREATE TABLE `result` (
  `result_id` int(11) NOT NULL,
  `student_id` varchar(10) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `marks_obtained` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `percentage` decimal(5,2) GENERATED ALWAYS AS (`marks_obtained` / `total_marks` * 100) STORED,
  `grade` varchar(2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `semester_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

CREATE TABLE `semester` (
  `semester_id` int(11) NOT NULL,
  `semester_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semester`
--

INSERT INTO `semester` (`semester_id`, `semester_name`) VALUES
(1, 'First Semester'),
(2, 'Second Semester'),
(3, 'Third Semester'),
(4, 'Fourth Semester'),
(5, 'Fifth Semester'),
(6, 'Sixth Semester'),
(7, 'Seventh Semester'),
(8, 'Eighth Semester');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` varchar(10) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `phone_number` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `student`
--
DELIMITER $$
CREATE TRIGGER `prevent_invalid_dates` BEFORE UPDATE ON `student` FOR EACH ROW BEGIN
    IF NEW.updated_at < OLD.created_at THEN
        SET NEW.updated_at = OLD.updated_at; -- Keep old value
        -- Or: SET NEW.updated_at = NOW(); -- Set to current
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `student_backup`
--

CREATE TABLE `student_backup` (
  `student_id` varchar(10) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `phone_number` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_backup`
--

INSERT INTO `student_backup` (`student_id`, `student_name`, `email`, `password`, `class_id`, `semester_id`, `phone_number`, `is_active`, `created_at`, `updated_at`) VALUES
('BBA827', 'Asbin Rai', 'asbin@gmail.com', '$2y$10$93VXnuL3docoqxamvwSbkeJaFieDFYsUFQprrLinDfhXR9BKSfUTq', 13, 1, '9822124154', 1, '2025-12-21 05:08:48', '2025-12-21 05:08:48'),
('BCA413', 'Pradip Khatiwada', 'iampradip@gmail.com', '$2y$10$1p8wsT6MzoeeuA7XZgL2DeL2IHE1VkqwJjL1jr1kuIL8eGNYHZ1b6', 12, 1, '9839381939', 1, '2025-12-21 02:58:07', '2025-12-21 02:58:07'),
('BIM732', 'Bindu Rana Magar', 'iambindu@gmail.com', '$2y$10$JlBcFc/v2xDhUEtdUz4y3uPIx5YYVnoSOtjavS7i2Dos5In7Q7f1i', 14, 1, '9813930188', 1, '2025-12-21 16:15:32', '2025-12-21 16:15:32'),
('BIT183', 'Sachin Giri', 'iamsachin428@gmail.com', '$2y$10$64cf2vXfAF41pNtrzFHXpenfkvOvDwg9oo7AEJnlPDiHtO2ScIdvy', 10, 1, '9818118344', 1, '2025-12-20 08:59:26', '2025-12-20 08:59:26'),
('BIT419', 'Karma Gautam', 'karma429@gmail.com', '$2y$10$k67H/Woi4CC.fNlCJBW5He/ydZIQNMA5ax.62V4uMlm8TjuMJoV4y', 10, 1, '9812393910', 1, '2025-12-20 09:05:31', '2025-12-20 09:05:31'),
('BIT677', 'Ramesh Thapa', 'rameshthapa12@gmail.com', '$2y$10$rld66gXuUhxhPQeahW76C.F46CECb/Oc.1.ZJR4qAmQtegx.1bT.i', 10, 1, '9851030391', 1, '2025-12-20 09:10:03', '2025-12-20 09:10:03');

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject_code` varchar(20) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `credits` int(11) DEFAULT 3,
  `is_elective` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_name`, `created_at`, `subject_code`, `semester`, `credits`, `is_elective`, `status`) VALUES
(16, 'Computer Fundamentals and Applications', '2025-12-27 08:07:31', 'BCA101', 1, 3, 0, 'active'),
(17, 'Society and Technology', '2025-12-27 08:07:31', 'BCA102', 1, 3, 0, 'active'),
(18, 'English I', '2025-12-27 08:07:31', 'BCA103', 1, 4, 0, 'active'),
(19, 'Mathematics I', '2025-12-27 08:07:31', 'BCA104', 1, 2, 0, 'active'),
(20, 'Digital Logic', '2025-12-27 08:07:31', 'BCA105', 1, 2, 0, 'active'),
(21, 'C Programming', '2025-12-27 08:07:31', 'BCA201', 2, 4, 0, 'active'),
(22, 'Financial Accounting', '2025-12-27 08:07:31', 'BCA202', 2, 4, 0, 'active'),
(23, 'English II', '2025-12-27 08:07:31', 'BCA203', 2, 4, 0, 'active'),
(24, 'Mathematics II', '2025-12-27 08:07:31', 'BCA204', 2, 2, 0, 'active'),
(25, 'MicroProcessor and Architecture', '2025-12-27 08:07:31', 'BCA205', 2, 3, 0, 'active'),
(26, 'Data Structures and Algorithms', '2025-12-27 08:07:32', 'BCA301', 3, 4, 0, 'active'),
(27, 'Probability and Statistics', '2025-12-27 08:07:32', 'BCA302', 3, 4, 0, 'active'),
(28, 'System Analysis and Design', '2025-12-27 08:07:32', 'BCA303', 3, 3, 0, 'active'),
(29, 'OOP in Java', '2025-12-27 08:07:32', 'BCA304', 3, 2, 0, 'active'),
(30, 'Web Technology', '2025-12-27 08:07:32', 'BCA305', 3, 3, 0, 'active'),
(31, 'Operating Systems', '2025-12-27 08:07:32', 'BCA401', 4, 4, 0, 'active'),
(32, 'Numerical Methods', '2025-12-27 08:07:32', 'BCA402', 4, 4, 0, 'active'),
(33, 'Software Engineering', '2025-12-27 08:07:32', 'BCA403', 4, 3, 0, 'active'),
(34, 'Scripting Language', '2025-12-27 08:07:32', 'BCA404', 4, 3, 0, 'active'),
(35, 'Database Management System', '2025-12-27 08:07:32', 'BCA405', 4, 3, 0, 'active'),
(36, 'MIS and E-Business', '2025-12-27 08:07:32', 'BCA501', 5, 4, 0, 'active'),
(37, 'DotNet Technology', '2025-12-27 08:07:32', 'BCA502', 5, 4, 0, 'active'),
(38, 'Computer Networking', '2025-12-27 08:07:32', 'BCA503', 5, 3, 0, 'active'),
(39, 'Introduction to Management', '2025-12-27 08:07:33', 'BCA504', 5, 3, 0, 'active'),
(40, 'Computer Graphics and Animation', '2025-12-27 08:07:33', 'BCA505', 5, 3, 0, 'active'),
(41, 'Mobile Programming', '2025-12-27 08:07:33', 'BCA601', 6, 4, 0, 'active'),
(42, 'Distributed System', '2025-12-27 08:07:33', 'BCA602', 6, 4, 0, 'active'),
(43, 'Applied Economics', '2025-12-27 08:07:33', 'BCA603', 6, 3, 0, 'active'),
(44, 'Advanced Java Programming', '2025-12-27 08:07:33', 'BCA604', 6, 3, 0, 'active'),
(45, 'Network Programming', '2025-12-27 08:07:33', 'BCA605', 6, 4, 0, 'active'),
(46, 'Cyber Law and Professional Ethics', '2025-12-27 08:07:34', 'BCA701', 7, 4, 0, 'active'),
(47, 'Cloud Computing', '2025-12-27 08:07:34', 'BCA702', 7, 4, 0, 'active'),
(48, 'Internet of Things', '2025-12-27 08:07:34', 'BCA703', 7, 3, 0, 'active'),
(49, 'Cyber Security', '2025-12-27 08:07:34', 'BCA704', 7, 3, 0, 'active'),
(50, 'Data Mining', '2025-12-27 08:07:34', 'BCA705', 7, 4, 0, 'active'),
(51, 'Machine Learning', '2025-12-27 08:07:34', 'BCA801', 8, 4, 0, 'active'),
(52, 'Big Data Analytics', '2025-12-27 08:07:34', 'BCA802', 8, 4, 0, 'active'),
(53, 'Blockchain Technology', '2025-12-27 08:07:34', 'BCA803', 8, 3, 0, 'active'),
(54, 'Image Processing', '2025-12-27 08:07:34', 'BCA804', 8, 5, 0, 'active'),
(55, 'Network Administration', '2025-12-27 08:07:34', 'BCA805', 8, 2, 0, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `name`, `email`, `password`, `status`, `created_at`, `assigned_class_id`) VALUES
(1, 'Ram Sharma', 'ram@college.com', '$2y$10$UShCXsoHuROIk2/vscxk4Ogkw.proI7SkMzZBaERMmoeeIbrCwxke', 'active', '2025-12-20 04:17:15', NULL),
(2, 'Sita Karki', 'sita@college.com', '$2y$10$yPTtRniEnAyRkZWvZilote0yIP5E2DENGEBE7rDssFD8xql25koee', 'active', '2025-12-20 04:17:15', NULL),
(3, 'Hari Thapa', 'harithapa895@gmail.com', '$2y$10$w/LzDjl8EreAtEnZeph/leNjfH8h61z.uGGCpu8lRT1IZWcoA2ejG', 'active', '2025-12-20 04:17:15', NULL),
(4, 'Gita Rai', 'gita@college.com', '$2y$10$YCTNng5z54CWD7x98MF6gOViS0oQfw8FYINVOu4iXFcLsKc6am3ZC', 'active', '2025-12-20 04:17:15', NULL),
(5, 'Samragya Sharma', 'samragyacollege@gmail.com', '$2y$10$leK3zdH5bmVFljMHSAJvPeOn5UtrlI0JtbyFGRAW8qtxucN.lKQRG', 'active', '2025-12-23 05:54:03', NULL),
(6, 'Sagun Giri', 'sagungiri123@gmail.com', '$2y$10$or19b1dfsn2LAj73s7cBhuG6TEqRWjdPIsArnqRGXu9ew5dst.3Cm', 'active', '2025-12-25 12:18:12', NULL),
(7, 'Ramesh Sharma', 'rameshsharma456@gmail.com', '$2y$10$C85o28jmgacv0.Z/eQZIEeFAKBqpfkHdMJtU/pgBVRElSxLCVoIdi', 'active', '2025-12-25 12:27:15', NULL),
(8, 'Shanti Giri', 'shantigiri951@gmail.com', '$2y$10$Nl7agCX9sDscfGb35TtDAeC7ERcPi3K6BHQA4o88PVxvq.69e0Nxe', 'active', '2025-12-25 12:33:41', NULL),
(9, 'Shyam Shah', 'shyamshah890@gmail.com', '$2y$10$indg2XEL3r6jKZpWQPIChefnIpsevq.M5kyk/CUM7vxEInZVeYFMm', 'active', '2025-12-25 12:34:53', NULL),
(10, 'Lara Jean', 'larajean568@gmail.com', '$2y$10$j2L3G1y2S.eFHPZx/y2bPeN2DRIJyddlgtrvnIQVPj0RbkFzEPFua', 'active', '2025-12-27 15:02:16', NULL),
(11, 'Samaya Gurung', 'samayagurung324@gmail.com', '$2y$10$dYYURem0Io4uypdmtZEFguQnEeFV06P7UxaFq9Sqb8YE0WJeRdW/u', 'active', '2025-12-27 15:20:25', NULL),
(12, 'Nirmal Magar', 'nirmalmagar123@gmail.com', '$2y$10$Wkigu0x5p1ejBvpdMPTNleUAc31gzHoRncl3OXf0TJMJKC3ige.0u', 'active', '2025-12-27 15:25:16', NULL),
(13, 'Nimesh Thapa', 'nimeshthapa123@gmail.com', '$2y$10$UGThPvMzCbf0O8uoNdy43u1avs7CYuU/4D50UrQehaqyAov3JDL8C', 'active', '2025-12-27 16:15:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_backup`
--

CREATE TABLE `teacher_backup` (
  `teacher_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `assigned_class_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_backup`
--

INSERT INTO `teacher_backup` (`teacher_id`, `name`, `email`, `password`, `assigned_class_id`, `status`, `created_at`) VALUES
(1, 'Ram Sharma', 'ram@college.com', '$2y$10$UShCXsoHuROIk2/vscxk4Ogkw.proI7SkMzZBaERMmoeeIbrCwxke', 12, 'active', '2025-12-20 04:17:15'),
(2, 'Sita Karki', 'sita@college.com', '$2y$10$yPTtRniEnAyRkZWvZilote0yIP5E2DENGEBE7rDssFD8xql25koee', 13, 'active', '2025-12-20 04:17:15'),
(3, 'Hari Thapa', 'hari@college.com', '$2y$10$w/LzDjl8EreAtEnZeph/leNjfH8h61z.uGGCpu8lRT1IZWcoA2ejG', NULL, 'inactive', '2025-12-20 04:17:15'),
(4, 'Gita Rai', 'gita@college.com', '$2y$10$YCTNng5z54CWD7x98MF6gOViS0oQfw8FYINVOu4iXFcLsKc6am3ZC', 14, 'active', '2025-12-20 04:17:15'),
(5, 'Samragya Sharma', 'samragyacollege@gmail.com', '$2y$10$leK3zdH5bmVFljMHSAJvPeOn5UtrlI0JtbyFGRAW8qtxucN.lKQRG', NULL, 'active', '2025-12-23 05:54:03'),
(6, 'Sagun Giri', 'sagungiri123@gmail.com', '$2y$10$or19b1dfsn2LAj73s7cBhuG6TEqRWjdPIsArnqRGXu9ew5dst.3Cm', 11, 'active', '2025-12-25 12:18:12'),
(7, 'Ramesh Sharma', 'rameshsharma456@gmail.com', '$2y$10$C85o28jmgacv0.Z/eQZIEeFAKBqpfkHdMJtU/pgBVRElSxLCVoIdi', 11, 'active', '2025-12-25 12:27:15'),
(8, 'Shanti Giri', 'shantigiri951@gmail.com', '$2y$10$Nl7agCX9sDscfGb35TtDAeC7ERcPi3K6BHQA4o88PVxvq.69e0Nxe', NULL, 'active', '2025-12-25 12:33:41'),
(9, 'Shyam Shah', 'shyamshah890@gmail.com', '$2y$10$indg2XEL3r6jKZpWQPIChefnIpsevq.M5kyk/CUM7vxEInZVeYFMm', 13, 'active', '2025-12-25 12:34:53');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_class_assignments`
--

CREATE TABLE `teacher_class_assignments` (
  `assignment_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject`
--

CREATE TABLE `teacher_subject` (
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `class_id` int(11) DEFAULT NULL,
  `semester_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'samragyacollege@gmail.com', '$2y$10$leK3zdH5bmVFljMHSAJvPeOn5UtrlI0JtbyFGRAW8qtxucN.lKQRG', 'teacher'),
(2, 'sagungiri123@gmail.com', '$2y$10$or19b1dfsn2LAj73s7cBhuG6TEqRWjdPIsArnqRGXu9ew5dst.3Cm', 'teacher'),
(3, 'rameshsharma456@gmail.com', '$2y$10$x2pnmEi0FQkPTqUAuLGwoOGbXFu.9iM1qyH4nJ.Wf68jfSKk4FClS', 'teacher'),
(4, 'shantigiri951@gmail.com', '$2y$10$Nl7agCX9sDscfGb35TtDAeC7ERcPi3K6BHQA4o88PVxvq.69e0Nxe', 'teacher'),
(5, 'shyamshah890@gmail.com', '$2y$10$indg2XEL3r6jKZpWQPIChefnIpsevq.M5kyk/CUM7vxEInZVeYFMm', 'teacher'),
(6, 'larajean568@gmail.com', '$2y$10$j2L3G1y2S.eFHPZx/y2bPeN2DRIJyddlgtrvnIQVPj0RbkFzEPFua', 'teacher'),
(7, 'samayagurung324@gmail.com', '$2y$10$dYYURem0Io4uypdmtZEFguQnEeFV06P7UxaFq9Sqb8YE0WJeRdW/u', 'teacher'),
(8, 'nirmalmagar123@gmail.com', '$2y$10$Wkigu0x5p1ejBvpdMPTNleUAc31gzHoRncl3OXf0TJMJKC3ige.0u', 'teacher'),
(9, 'nimeshthapa123@gmail.com', '$2y$10$UGThPvMzCbf0O8uoNdy43u1avs7CYuU/4D50UrQehaqyAov3JDL8C', 'teacher');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `unique_bca_class` (`faculty`,`semester`,`batch_year`);

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_class_subject` (`class_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payment_student` (`student_id`);

--
-- Indexes for table `result`
--
ALTER TABLE `result`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `fk_result_student` (`student_id`),
  ADD KEY `fk_result_subject` (`subject_id`),
  ADD KEY `fk_result_semester` (`semester_id`);

--
-- Indexes for table `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`semester_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_student_class` (`class_id`),
  ADD KEY `fk_student_semester` (`semester_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_teacher_class` (`assigned_class_id`);

--
-- Indexes for table `teacher_class_assignments`
--
ALTER TABLE `teacher_class_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_assignment` (`teacher_id`,`class_id`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_class` (`class_id`);

--
-- Indexes for table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  ADD PRIMARY KEY (`teacher_id`,`subject_id`),
  ADD KEY `fk_teacher_subject_subject` (`subject_id`),
  ADD KEY `fk_teacher_subject_class` (`class_id`),
  ADD KEY `fk_teacher_subject_semester` (`semester_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `result`
--
ALTER TABLE `result`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `semester_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `teacher_class_assignments`
--
ALTER TABLE `teacher_class_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD CONSTRAINT `class_subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subjects_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE SET NULL;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `result`
--
ALTER TABLE `result`
  ADD CONSTRAINT `fk_result_semester` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`semester_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_result_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_result_subject` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `fk_student_class` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_semester` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`semester_id`) ON UPDATE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `fk_teacher_class` FOREIGN KEY (`assigned_class_id`) REFERENCES `class` (`class_id`) ON DELETE SET NULL;

--
-- Constraints for table `teacher_class_assignments`
--
ALTER TABLE `teacher_class_assignments`
  ADD CONSTRAINT `teacher_class_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_class_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  ADD CONSTRAINT `fk_teacher_subject_class` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teacher_subject_semester` FOREIGN KEY (`semester_id`) REFERENCES `semester` (`semester_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teacher_subject_subject` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_teacher_subject_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
