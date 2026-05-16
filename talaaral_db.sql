SET SESSION sql_require_primary_key = 0;

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: talaaral_db
-- Generation Time: May 12, 2026 at 07:31 AM
-- Server version: 8.0.46
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `talaaral_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `canvas_courses`
--

CREATE TABLE `canvas_courses` (
  `id` int NOT NULL,
  `course_id` varchar(50) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `is_active` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `program_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `canvas_courses`
--

INSERT INTO `canvas_courses` (`id`, `course_id`, `course_name`, `is_active`, `created_at`, `program_id`) VALUES
(1, '1', 'Integrative Programming and Technologies', 1, '2026-05-09 09:01:52', 1),
(2, '2', 'Financial Markets and Institutions', 1, '2026-05-09 09:01:52', 2),
(3, '3', 'Recruitment and Selection', 1, '2026-05-09 09:01:52', 3),
(4, '4', 'Consumer Behavior', 1, '2026-05-09 09:01:52', 4),
(5, '5', 'Supply Chain Management', 1, '2026-05-09 09:01:52', 5);

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int NOT NULL,
  `program_name` varchar(100) NOT NULL,
  `abbreviation` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `program_name`, `abbreviation`) VALUES
(1, 'Bachelor of Science in Information Technology', 'BSIT'),
(2, 'BSBA major in Financial Management', 'BSBA-FM'),
(3, 'BSBA major in Human Resource Management', 'BSBA-HRM'),
(4, 'BSBA major in Marketing Management', 'BSBA-MM'),
(5, 'BSBA major in Operations Management', 'BSBA-OM');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','missed','archived') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `previous_status` enum('pending','in_progress','completed','missed','archived') DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT '0',
  `reminder_unit` varchar(10) DEFAULT 'none',
  `reminder_value` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  `program_id` int NOT NULL,
  `program_abbreviation` varchar(20) DEFAULT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year','Irregular') NOT NULL,
  `major` varchar(50) DEFAULT NULL,
  `profile_pic` longtext,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `student_number`, `email`, `password_hash`, `is_verified`, `verification_token`, `token_expires`, `program_id`, `program_abbreviation`, `year_level`, `major`, `profile_pic`, `created_at`, `reset_token`, `reset_expiry`) VALUES
(1, 'Jade Cunanan', '2024-200362', '2024-200362@rtu.edu.ph', '$2y$10$LScGtmhkA5Wi/EOxTcWv4.ZfkQNXpSGA/HM4uD5WGFaB5KOyzmPLO', 1, NULL, NULL, 1, 'BSIT', '2nd Year', NULL, 'data:image/webp;base64,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', '2026-05-07 03:52:48', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `canvas_courses`
--
ALTER TABLE `canvas_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_canvas_program` (`program_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD KEY `fk_user_program` (`program_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `canvas_courses`
--
ALTER TABLE `canvas_courses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `canvas_courses`
--
ALTER TABLE `canvas_courses`
  ADD CONSTRAINT `fk_canvas_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

  -- --------------------------------------------------------
-- Table structure for table `announcements`
-- --------------------------------------------------------

CREATE TABLE `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `posted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `likes` int DEFAULT '0',
  `shares` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Sample announcements
INSERT INTO `announcements` (`title`, `message`, `image_url`, `posted_at`, `likes`, `shares`) VALUES
('Enrollment for 2nd Semester 2025-2026', 'The Office of the Registrar announces that enrollment for the 2nd Semester of Academic Year 2025-2026 will begin on June 2, 2026. Students are advised to settle all their accounts before enrolling. For more information, visit the registrar office or check the RTU official website.', NULL, '2026-05-10 08:00:00', 245, 87),
('RTU Free Tutoring Program', 'The Academic Affairs Office is offering free tutoring sessions for all students who need academic assistance. Sessions are available Monday to Friday, 1:00 PM to 5:00 PM at the Learning Resource Center. Register now at the Dean of Students office.', NULL, '2026-05-08 10:30:00', 312, 134),
('Scholarship Application Open', 'Applications for the CHED Scholarship Program for AY 2026-2027 are now open. Eligible students must have a GWA of 1.75 or better and must not be a recipient of any other scholarship. Submit requirements to the Scholarship Office on or before May 31, 2026.', NULL, '2026-05-05 09:00:00', 189, 56);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
