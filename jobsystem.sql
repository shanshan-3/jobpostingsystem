-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 30, 2026 at 05:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jobsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `app_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `cover_message` text NOT NULL,
  `status` enum('pending','shortlisted','rejected','') NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`app_id`, `job_id`, `seeker_id`, `cover_message`, `status`, `applied_at`) VALUES
(5, 14, 6, '', 'shortlisted', '2026-05-30 13:47:12'),
(8, 16, 6, '', 'pending', '2026-05-30 14:44:37');

-- --------------------------------------------------------

--
-- Table structure for table `employer_profiles`
--

CREATE TABLE `employer_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `industry` varchar(100) NOT NULL,
  `location` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `contact_email` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_profiles`
--

INSERT INTO `employer_profiles` (`profile_id`, `user_id`, `company_name`, `industry`, `location`, `description`, `contact_email`) VALUES
(3, 7, 'TechComp', 'Technology', 'Manila, Philippines', 'An IT Company', 'techcomp@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `job_posting`
--

CREATE TABLE `job_posting` (
  `job_id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `required_skills` text NOT NULL,
  `work_type` enum('fulltime','parttime','freelance','') NOT NULL,
  `arrangement` enum('remote','hybrid','onsite','') NOT NULL,
  `accessibility_features` text NOT NULL,
  `status` enum('active','closed','','') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_posting`
--

INSERT INTO `job_posting` (`job_id`, `employer_id`, `title`, `description`, `required_skills`, `work_type`, `arrangement`, `accessibility_features`, `status`, `created_at`) VALUES
(14, 7, 'BACK END DEVELOPER', 'JOB', 'Programming', 'fulltime', 'remote', 'Remote Work Only', 'closed', '2026-05-30 13:44:00'),
(16, 7, 'Full Stack Dev', 'Full Stack', 'Programming', 'parttime', 'hybrid', 'Remote Work Only', 'active', '2026-05-30 14:06:32');

-- --------------------------------------------------------

--
-- Table structure for table `seeker_profiles`
--

CREATE TABLE `seeker_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `location` varchar(150) NOT NULL,
  `skills` text NOT NULL,
  `accessibility_needs` text NOT NULL,
  `work_preference` enum('remote','hybrid','onsite','') NOT NULL,
  `bio` text NOT NULL,
  `resume_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seeker_profiles`
--

INSERT INTO `seeker_profiles` (`profile_id`, `user_id`, `full_name`, `phone`, `location`, `skills`, `accessibility_needs`, `work_preference`, `bio`, `resume_path`) VALUES
(6, 6, 'Christian Orzales', '0912345678', 'USA', 'Typing', 'remote_work_only,flexible_hours', 'hybrid', 'Pogi, Unemployed', ''),
(7, 6, 'Christian Orzales', '0912345678', 'USA', 'Typing', 'remote_work_only,flexible_hours', 'remote', 'Pogi, Unemployed', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('employer','seeker','') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `role`, `created_at`) VALUES
(6, 'seeker@gmail.com', '$2y$10$wkuOMGSiAynqwNOKI34d3.e8k7ozZmLSOaEmf8NVVRoYvxd9CnqVa', 'seeker', '2026-05-30 13:22:29'),
(7, 'employer@gmail.com', '$2y$10$IyC.gPIHDTIPa8cueujJru/KqaKQtyBSvecqmd7r79WKj6GBCkL3.', 'employer', '2026-05-30 13:32:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`app_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `employer_profiles`
--
ALTER TABLE `employer_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `job_posting`
--
ALTER TABLE `job_posting`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `employer_id` (`employer_id`);

--
-- Indexes for table `seeker_profiles`
--
ALTER TABLE `seeker_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employer_profiles`
--
ALTER TABLE `employer_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `job_posting`
--
ALTER TABLE `job_posting`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `seeker_profiles`
--
ALTER TABLE `seeker_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_posting` (`job_id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`seeker_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `employer_profiles`
--
ALTER TABLE `employer_profiles`
  ADD CONSTRAINT `employer_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `job_posting`
--
ALTER TABLE `job_posting`
  ADD CONSTRAINT `job_posting_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `seeker_profiles`
--
ALTER TABLE `seeker_profiles`
  ADD CONSTRAINT `seeker_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
