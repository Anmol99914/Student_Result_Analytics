-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 23, 2025 at 05:01 PM
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
  `teacher_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `faculty`, `semester`, `teacher_id`, `status`, `created_at`) VALUES
(10, 'BIT', 1, 1, 'active', '2025-12-20 07:31:07'),
(11, 'BBM', 1, 2, 'active', '2025-12-20 07:46:16'),
(12, 'BCA', 1, 1, 'active', '2025-12-21 02:53:55'),
(13, 'BBA', 1, 2, 'active', '2025-12-21 05:07:18'),
(14, 'BIM', 1, 4, 'active', '2025-12-21 06:07:08');

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
('BBA827', 'Asbin Rai', 'asbin@gmail.com', '$2y$10$93VXnuL3docoqxamvwSbkeJaFieDFYsUFQprrLinDfhXR9BKSfUTq', 13, 1, '9822124154', 1, '2025-12-21 05:08:48', '2025-12-21 05:08:48'),
('BCA413', 'Pradip Khatiwada', 'iampradip@gmail.com', '$2y$10$1p8wsT6MzoeeuA7XZgL2DeL2IHE1VkqwJjL1jr1kuIL8eGNYHZ1b6', 12, 1, '9839381939', 1, '2025-12-21 02:58:07', '2025-12-21 02:58:07'),
('BIM732', 'Bindu Rana Magar', 'iambindu@gmail.com', '$2y$10$JlBcFc/v2xDhUEtdUz4y3uPIx5YYVnoSOtjavS7i2Dos5In7Q7f1i', 14, 1, '9813930188', 1, '2025-12-21 16:15:32', '2025-12-21 16:15:32'),
('BIT183', 'Sachin Giri', 'iamsachin428@gmail.com', '$2y$10$64cf2vXfAF41pNtrzFHXpenfkvOvDwg9oo7AEJnlPDiHtO2ScIdvy', 10, 1, '9818118344', 1, '2025-12-20 08:59:26', '2025-12-20 08:59:26'),
('BIT419', 'Karma Gautam', 'karma429@gmail.com', '$2y$10$k67H/Woi4CC.fNlCJBW5He/ydZIQNMA5ax.62V4uMlm8TjuMJoV4y', 10, 1, '9812393910', 1, '2025-12-20 09:05:31', '2025-12-20 09:05:31'),
('BIT677', 'Ramesh Thapa', 'rameshthapa12@gmail.com', '$2y$10$rld66gXuUhxhPQeahW76C.F46CECb/Oc.1.ZJR4qAmQtegx.1bT.i', 10, 1, '9851030391', 1, '2025-12-20 09:10:03', '2025-12-20 09:10:03');

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
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_name`, `created_at`) VALUES
(1, 'Mathematics', '2025-12-21 05:26:30'),
(2, 'English', '2025-12-21 05:26:30'),
(3, 'C Programming', '2025-12-21 05:26:30'),
(4, 'Digital Logic', '2025-12-21 05:26:30'),
(5, 'Java', '2025-12-21 05:26:30'),
(6, 'Science', '2025-12-21 05:26:30'),
(7, 'Computer Science', '2025-12-21 05:26:30'),
(8, 'Nepali', '2025-12-21 05:26:30'),
(9, 'Social Studies', '2025-12-21 05:26:30'),
(10, 'Physics', '2025-12-21 05:26:30'),
(11, 'Chemistry', '2025-12-21 05:26:30'),
(12, 'Biology', '2025-12-21 05:26:30'),
(13, 'Accountancy', '2025-12-21 05:26:30'),
(14, 'Economics', '2025-12-21 05:26:30'),
(15, 'Business Studies', '2025-12-21 05:26:30');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `assigned_class_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `name`, `email`, `password`, `assigned_class_id`, `status`, `created_at`) VALUES
(1, 'Ram Sharma', 'ram@college.com', '$2y$10$UShCXsoHuROIk2/vscxk4Ogkw.proI7SkMzZBaERMmoeeIbrCwxke', 12, 'active', '2025-12-20 04:17:15'),
(2, 'Sita Karki', 'sita@college.com', '$2y$10$yPTtRniEnAyRkZWvZilote0yIP5E2DENGEBE7rDssFD8xql25koee', 13, 'active', '2025-12-20 04:17:15'),
(3, 'Hari Thapa', 'hari@college.com', '$2y$10$w/LzDjl8EreAtEnZeph/leNjfH8h61z.uGGCpu8lRT1IZWcoA2ejG', NULL, 'inactive', '2025-12-20 04:17:15'),
(4, 'Gita Rai', 'gita@college.com', '$2y$10$YCTNng5z54CWD7x98MF6gOViS0oQfw8FYINVOu4iXFcLsKc6am3ZC', 14, 'active', '2025-12-20 04:17:15'),
(5, 'Samragya Sharma', 'samragyacollege@gmail.com', '$2y$10$leK3zdH5bmVFljMHSAJvPeOn5UtrlI0JtbyFGRAW8qtxucN.lKQRG', 12, 'active', '2025-12-23 05:54:03');

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
(1, 'samragyacollege@gmail.com', '$2y$10$leK3zdH5bmVFljMHSAJvPeOn5UtrlI0JtbyFGRAW8qtxucN.lKQRG', 'teacher');

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
  ADD KEY `idx_class_teacher` (`teacher_id`);

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
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_teacher_class` (`assigned_class_id`);

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
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `fk_class_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_teacher_class` FOREIGN KEY (`assigned_class_id`) REFERENCES `class` (`class_id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
