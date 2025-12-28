-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 28, 2025 at 05:27 PM
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
(42, 'BCA', 1, 'active', '2025-12-28 03:50:30', '2025'),
(43, 'BCA', 2, 'active', '2025-12-28 03:50:45', '2025'),
(44, 'BCA', 3, 'active', '2025-12-28 03:50:55', '2025'),
(45, 'BCA', 4, 'active', '2025-12-28 03:51:08', '2025'),
(46, 'BCA', 5, 'active', '2025-12-28 03:51:25', '2025'),
(47, 'BCA', 6, 'active', '2025-12-28 03:51:35', '2025'),
(48, 'BCA', 7, 'active', '2025-12-28 03:53:49', '2025'),
(49, 'BCA', 8, 'active', '2025-12-28 03:54:26', '2025'),
(50, 'BBM', 1, 'active', '2025-12-28 13:57:21', '2025'),
(51, 'BBM', 2, 'active', '2025-12-28 13:57:21', '2025'),
(52, 'BBM', 3, 'active', '2025-12-28 17:19:25', '2025');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `faculty_code` varchar(10) NOT NULL,
  `faculty_name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `faculty_code`, `faculty_name`, `status`) VALUES
(1, 'BCA', 'Bachelor of Computer Applications', 'active'),
(2, 'BBM', 'Bachelor of Business Management', 'active'),
(3, 'BIM', 'Bachelor of Information Management', 'active');

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
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `student_name`, `email`, `password`, `class_id`, `semester_id`, `phone_number`, `is_active`, `created_at`, `updated_at`) VALUES
('BBM001', 'Sagun Giri', 'bbm001@gmail.com', 'pass123', 50, 1, '9800000103', 1, '2025-12-28 14:07:53', '2025-12-28 14:07:53'),
('BBM002', 'Shanti Giri', 'bbm002@gmail.com', 'pass123', 50, 1, '9800000104', 1, '2025-12-28 14:07:53', '2025-12-28 14:07:53'),
('BCA001', 'Sachin Giri', 'bca001@gmail.com', 'pass123', 42, 1, '9800000001', 1, '2025-12-28 13:45:15', '2025-12-28 13:45:15'),
('BCA002', 'Amir Giri', 'bca002@gmail.com', 'pass123', 42, 1, '9800000002', 1, '2025-12-28 13:45:15', '2025-12-28 13:45:15');

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
-- Table structure for table `student_subject_enrollment`
--

CREATE TABLE `student_subject_enrollment` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year` year(4) NOT NULL DEFAULT 2025,
  `status` enum('enrolled','completed') DEFAULT 'enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subject_enrollment`
--

INSERT INTO `student_subject_enrollment` (`enrollment_id`, `student_id`, `subject_id`, `class_id`, `academic_year`, `status`) VALUES
(4, 'BCA001', 16, 42, '2025', 'enrolled'),
(5, 'BCA001', 17, 42, '2025', 'enrolled'),
(6, 'BCA001', 18, 42, '2025', 'enrolled'),
(7, 'BCA002', 16, 42, '2025', 'enrolled'),
(8, 'BCA002', 17, 42, '2025', 'enrolled'),
(12, 'BBM001', 56, 50, '2025', 'enrolled'),
(13, 'BBM001', 57, 50, '2025', 'enrolled'),
(14, 'BBM001', 58, 51, '2025', 'enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject_code` varchar(20) DEFAULT NULL,
  `faculty_id` int(11) NOT NULL,
  `semester` int(11) DEFAULT NULL,
  `credits` int(11) DEFAULT 3,
  `is_elective` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_name`, `created_at`, `subject_code`, `faculty_id`, `semester`, `credits`, `is_elective`, `status`) VALUES
(16, 'Computer Fundamentals and Applications', '2025-12-27 08:07:31', 'BCA101', 1, 1, 3, 0, 'active'),
(17, 'Society and Technology', '2025-12-27 08:07:31', 'BCA102', 1, 1, 3, 0, 'active'),
(18, 'English I', '2025-12-27 08:07:31', 'BCA103', 1, 1, 4, 0, 'active'),
(19, 'Mathematics I', '2025-12-27 08:07:31', 'BCA104', 1, 1, 2, 0, 'active'),
(20, 'Digital Logic', '2025-12-27 08:07:31', 'BCA105', 1, 1, 2, 0, 'active'),
(21, 'C Programming', '2025-12-27 08:07:31', 'BCA201', 1, 2, 4, 0, 'active'),
(22, 'Financial Accounting', '2025-12-27 08:07:31', 'BCA202', 1, 2, 4, 0, 'active'),
(23, 'English II', '2025-12-27 08:07:31', 'BCA203', 1, 2, 4, 0, 'active'),
(24, 'Mathematics II', '2025-12-27 08:07:31', 'BCA204', 1, 2, 2, 0, 'active'),
(25, 'MicroProcessor and Architecture', '2025-12-27 08:07:31', 'BCA205', 1, 2, 3, 0, 'active'),
(26, 'Data Structures and Algorithms', '2025-12-27 08:07:32', 'BCA301', 1, 3, 4, 0, 'active'),
(27, 'Probability and Statistics', '2025-12-27 08:07:32', 'BCA302', 1, 3, 4, 0, 'active'),
(28, 'System Analysis and Design', '2025-12-27 08:07:32', 'BCA303', 1, 3, 3, 0, 'active'),
(29, 'OOP in Java', '2025-12-27 08:07:32', 'BCA304', 1, 3, 2, 0, 'active'),
(30, 'Web Technology', '2025-12-27 08:07:32', 'BCA305', 1, 3, 3, 0, 'active'),
(31, 'Operating Systems', '2025-12-27 08:07:32', 'BCA401', 1, 4, 4, 0, 'active'),
(32, 'Numerical Methods', '2025-12-27 08:07:32', 'BCA402', 1, 4, 4, 0, 'active'),
(33, 'Software Engineering', '2025-12-27 08:07:32', 'BCA403', 1, 4, 3, 0, 'active'),
(34, 'Scripting Language', '2025-12-27 08:07:32', 'BCA404', 1, 4, 3, 0, 'active'),
(35, 'Database Management System', '2025-12-27 08:07:32', 'BCA405', 1, 4, 3, 0, 'active'),
(36, 'MIS and E-Business', '2025-12-27 08:07:32', 'BCA501', 1, 5, 4, 0, 'active'),
(37, 'DotNet Technology', '2025-12-27 08:07:32', 'BCA502', 1, 5, 4, 0, 'active'),
(38, 'Computer Networking', '2025-12-27 08:07:32', 'BCA503', 1, 5, 3, 0, 'active'),
(39, 'Introduction to Management', '2025-12-27 08:07:33', 'BCA504', 1, 5, 3, 0, 'active'),
(40, 'Computer Graphics and Animation', '2025-12-27 08:07:33', 'BCA505', 1, 5, 3, 0, 'active'),
(41, 'Mobile Programming', '2025-12-27 08:07:33', 'BCA601', 1, 6, 4, 0, 'active'),
(42, 'Distributed System', '2025-12-27 08:07:33', 'BCA602', 1, 6, 4, 0, 'active'),
(43, 'Applied Economics', '2025-12-27 08:07:33', 'BCA603', 1, 6, 3, 0, 'active'),
(44, 'Advanced Java Programming', '2025-12-27 08:07:33', 'BCA604', 1, 6, 3, 0, 'active'),
(45, 'Network Programming', '2025-12-27 08:07:33', 'BCA605', 1, 6, 4, 0, 'active'),
(46, 'Cyber Law and Professional Ethics', '2025-12-27 08:07:34', 'BCA701', 1, 7, 4, 0, 'active'),
(47, 'Cloud Computing', '2025-12-27 08:07:34', 'BCA702', 1, 7, 4, 0, 'active'),
(48, 'Internet of Things', '2025-12-27 08:07:34', 'BCA703', 1, 7, 3, 0, 'active'),
(49, 'Cyber Security', '2025-12-27 08:07:34', 'BCA704', 1, 7, 3, 0, 'active'),
(50, 'Data Mining', '2025-12-27 08:07:34', 'BCA705', 1, 7, 4, 0, 'active'),
(51, 'Machine Learning', '2025-12-27 08:07:34', 'BCA801', 1, 8, 4, 0, 'active'),
(52, 'Big Data Analytics', '2025-12-27 08:07:34', 'BCA802', 1, 8, 4, 0, 'active'),
(53, 'Blockchain Technology', '2025-12-27 08:07:34', 'BCA803', 1, 8, 3, 0, 'active'),
(54, 'Image Processing', '2025-12-27 08:07:34', 'BCA804', 1, 8, 5, 0, 'active'),
(55, 'Network Administration', '2025-12-27 08:07:34', 'BCA805', 1, 8, 2, 0, 'active'),
(56, 'Principles of Management', '2025-12-28 13:51:34', 'BBM101', 2, 1, 3, 0, 'active'),
(57, 'Business Communication', '2025-12-28 13:51:34', 'BBM102', 2, 1, 3, 0, 'active'),
(58, 'Marketing Management', '2025-12-28 13:51:34', 'BBM103', 2, 2, 4, 0, 'active');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `name`, `email`, `password`, `status`, `created_at`) VALUES
(1, 'Ram Sharma', 'ram@college.com', '$2y$10$UShCXsoHuROIk2/vscxk4Ogkw.proI7SkMzZBaERMmoeeIbrCwxke', 'active', '2025-12-20 04:17:15'),
(2, 'Sita Karki', 'sita@college.com', '$2y$10$yPTtRniEnAyRkZWvZilote0yIP5E2DENGEBE7rDssFD8xql25koee', 'active', '2025-12-20 04:17:15'),
(3, 'Hari Thapa', 'harithapa895@gmail.com', '$2y$10$w/LzDjl8EreAtEnZeph/leNjfH8h61z.uGGCpu8lRT1IZWcoA2ejG', 'active', '2025-12-20 04:17:15'),
(4, 'Gita Rai', 'gita@college.com', '$2y$10$YCTNng5z54CWD7x98MF6gOViS0oQfw8FYINVOu4iXFcLsKc6am3ZC', 'inactive', '2025-12-20 04:17:15'),
(5, 'Samragya Sharma', 'samragyacollege@gmail.com', '$2y$10$leK3zdH5bmVFljMHSAJvPeOn5UtrlI0JtbyFGRAW8qtxucN.lKQRG', 'active', '2025-12-23 05:54:03'),
(6, 'Sagun Giri', 'sagungiri123@gmail.com', '$2y$10$or19b1dfsn2LAj73s7cBhuG6TEqRWjdPIsArnqRGXu9ew5dst.3Cm', 'active', '2025-12-25 12:18:12'),
(7, 'Ramesh Sharma', 'rameshsharma456@gmail.com', '$2y$10$C85o28jmgacv0.Z/eQZIEeFAKBqpfkHdMJtU/pgBVRElSxLCVoIdi', 'active', '2025-12-25 12:27:15'),
(8, 'Shanti Giri', 'shantigiri951@gmail.com', '$2y$10$Nl7agCX9sDscfGb35TtDAeC7ERcPi3K6BHQA4o88PVxvq.69e0Nxe', 'active', '2025-12-25 12:33:41'),
(9, 'Shyam Shah', 'shyamshah890@gmail.com', '$2y$10$indg2XEL3r6jKZpWQPIChefnIpsevq.M5kyk/CUM7vxEInZVeYFMm', 'active', '2025-12-25 12:34:53'),
(10, 'Lara Jean', 'larajean568@gmail.com', '$2y$10$j2L3G1y2S.eFHPZx/y2bPeN2DRIJyddlgtrvnIQVPj0RbkFzEPFua', 'active', '2025-12-27 15:02:16'),
(11, 'Samaya Gurung', 'samayagurung324@gmail.com', '$2y$10$dYYURem0Io4uypdmtZEFguQnEeFV06P7UxaFq9Sqb8YE0WJeRdW/u', 'active', '2025-12-27 15:20:25'),
(12, 'Nirmal Magar', 'nirmalmagar123@gmail.com', '$2y$10$Wkigu0x5p1ejBvpdMPTNleUAc31gzHoRncl3OXf0TJMJKC3ige.0u', 'active', '2025-12-27 15:25:16'),
(13, 'Nimesh Thapa', 'nimeshthapa123@gmail.com', '$2y$10$UGThPvMzCbf0O8uoNdy43u1avs7CYuU/4D50UrQehaqyAov3JDL8C', 'active', '2025-12-27 16:15:27');

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

--
-- Dumping data for table `teacher_class_assignments`
--

INSERT INTO `teacher_class_assignments` (`assignment_id`, `teacher_id`, `class_id`, `assigned_date`) VALUES
(32, 10, 42, '2025-12-28 03:55:41'),
(33, 13, 42, '2025-12-28 03:55:41'),
(34, 12, 42, '2025-12-28 03:55:41'),
(35, 1, 42, '2025-12-28 03:55:41'),
(36, 7, 42, '2025-12-28 03:55:41');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject_assignment`
--

CREATE TABLE `teacher_subject_assignment` (
  `assignment_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year` year(4) NOT NULL DEFAULT 2025,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','completed') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subject_assignment`
--

INSERT INTO `teacher_subject_assignment` (`assignment_id`, `teacher_id`, `subject_id`, `class_id`, `academic_year`, `start_date`, `end_date`, `status`) VALUES
(2, 1, 16, 42, '2025', '2025-01-01', NULL, 'active'),
(3, 1, 17, 42, '2025', '2025-01-01', NULL, 'active'),
(4, 2, 16, 42, '2025', '2025-07-01', NULL, 'active'),
(6, 3, 16, 42, '2025', '2025-01-01', NULL, 'active'),
(8, 3, 56, 50, '2025', '2025-01-01', NULL, 'active'),
(9, 4, 57, 50, '2025', '2025-01-01', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `status`) VALUES
(1, 'samragyacollege@gmail.com', '$2y$10$leK3zdH5bmVFljMHSAJvPeOn5UtrlI0JtbyFGRAW8qtxucN.lKQRG', 'teacher', 'active'),
(2, 'sagungiri123@gmail.com', '$2y$10$or19b1dfsn2LAj73s7cBhuG6TEqRWjdPIsArnqRGXu9ew5dst.3Cm', 'teacher', 'active'),
(3, 'rameshsharma456@gmail.com', '$2y$10$x2pnmEi0FQkPTqUAuLGwoOGbXFu.9iM1qyH4nJ.Wf68jfSKk4FClS', 'teacher', 'active'),
(4, 'shantigiri951@gmail.com', '$2y$10$Nl7agCX9sDscfGb35TtDAeC7ERcPi3K6BHQA4o88PVxvq.69e0Nxe', 'teacher', 'active'),
(5, 'shyamshah890@gmail.com', '$2y$10$indg2XEL3r6jKZpWQPIChefnIpsevq.M5kyk/CUM7vxEInZVeYFMm', 'teacher', 'active'),
(6, 'larajean568@gmail.com', '$2y$10$j2L3G1y2S.eFHPZx/y2bPeN2DRIJyddlgtrvnIQVPj0RbkFzEPFua', 'teacher', 'active'),
(7, 'samayagurung324@gmail.com', '$2y$10$dYYURem0Io4uypdmtZEFguQnEeFV06P7UxaFq9Sqb8YE0WJeRdW/u', 'teacher', 'active'),
(8, 'nirmalmagar123@gmail.com', '$2y$10$Wkigu0x5p1ejBvpdMPTNleUAc31gzHoRncl3OXf0TJMJKC3ige.0u', 'teacher', 'active'),
(9, 'nimeshthapa123@gmail.com', '$2y$10$UGThPvMzCbf0O8uoNdy43u1avs7CYuU/4D50UrQehaqyAov3JDL8C', 'teacher', 'active'),
(11, 'ram@college.com', '$2y$10$UShCXsoHuROIk2/vscxk4Ogkw.proI7SkMzZBaERMmoeeIbrCwxke', 'teacher', 'active'),
(12, 'sita@college.com', '$2y$10$yPTtRniEnAyRkZWvZilote0yIP5E2DENGEBE7rDssFD8xql25koee', 'teacher', 'active'),
(13, 'harithapa895@gmail.com', '$2y$10$w/LzDjl8EreAtEnZeph/leNjfH8h61z.uGGCpu8lRT1IZWcoA2ejG', 'teacher', 'active'),
(14, 'gita@college.com', '$2y$10$YCTNng5z54CWD7x98MF6gOViS0oQfw8FYINVOu4iXFcLsKc6am3ZC', 'teacher', 'inactive');

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
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `faculty_code` (`faculty_code`),
  ADD UNIQUE KEY `faculty_code_2` (`faculty_code`);

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
-- Indexes for table `student_subject_enrollment`
--
ALTER TABLE `student_subject_enrollment`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `class_id` (`class_id`);

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
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `teacher_class_assignments`
--
ALTER TABLE `teacher_class_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_assignment` (`teacher_id`,`class_id`),
  ADD KEY `idx_teacher` (`teacher_id`),
  ADD KEY `idx_class` (`class_id`);

--
-- Indexes for table `teacher_subject_assignment`
--
ALTER TABLE `teacher_subject_assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `class_id` (`class_id`);

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
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT for table `student_subject_enrollment`
--
ALTER TABLE `student_subject_enrollment`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `teacher_class_assignments`
--
ALTER TABLE `teacher_class_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `teacher_subject_assignment`
--
ALTER TABLE `teacher_subject_assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

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
-- Constraints for table `student_subject_enrollment`
--
ALTER TABLE `student_subject_enrollment`
  ADD CONSTRAINT `student_subject_enrollment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `student_subject_enrollment_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`),
  ADD CONSTRAINT `student_subject_enrollment_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`);

--
-- Constraints for table `teacher_class_assignments`
--
ALTER TABLE `teacher_class_assignments`
  ADD CONSTRAINT `teacher_class_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_class_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_subject_assignment`
--
ALTER TABLE `teacher_subject_assignment`
  ADD CONSTRAINT `teacher_subject_assignment_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`),
  ADD CONSTRAINT `teacher_subject_assignment_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`),
  ADD CONSTRAINT `teacher_subject_assignment_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
