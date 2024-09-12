-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2024 at 08:14 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quilana`
--

-- --------------------------------------------------------

--
-- Table structure for table `administer_assessment`
--

CREATE TABLE `administer_assessment` (
  `administer_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `timelimit` int(11) NOT NULL,
  `date_administered` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administer_assessment`
--

INSERT INTO `administer_assessment` (`administer_id`, `assessment_id`, `course_id`, `class_id`, `timelimit`, `date_administered`) VALUES
(1, 1, 5, 19, 10, '2024-09-02');

-- --------------------------------------------------------

--
-- Table structure for table `assessment`
--

CREATE TABLE `assessment` (
  `assessment_id` int(11) NOT NULL,
  `assessment_mode` tinyint(1) NOT NULL,
  `assessment_name` varchar(150) NOT NULL,
  `course_id` int(11) NOT NULL,
  `topic` varchar(200) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment`
--

INSERT INTO `assessment` (`assessment_id`, `assessment_mode`, `assessment_name`, `course_id`, `topic`, `faculty_id`, `date_updated`) VALUES
(1, 1, 'assessment test', 5, 'topic test', 1, '2024-09-02 16:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `code` varchar(8) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `assessment_id` int(11) DEFAULT NULL,
  `year` tinyint(4) NOT NULL,
  `section` varchar(2) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `code`, `faculty_id`, `course_id`, `subject`, `class_name`, `student_id`, `assessment_id`, `year`, `section`, `date_created`, `date_updated`) VALUES
(14, '1', 1, 1, 'TEST', 'TEST', NULL, NULL, 3, '1', '2024-08-29 14:22:56', '2024-09-01 14:35:16'),
(15, '2', 1, 1, 'test1', 'test1', NULL, NULL, 3, '2', '2024-08-29 16:43:36', '2024-09-01 14:35:27'),
(16, '3', 1, 6, 'test', 'test', NULL, NULL, 1, '1', '2024-08-29 18:07:14', '2024-09-01 14:35:31'),
(17, '4', 1, 6, 'SAD', 'BSIT 3-1', NULL, NULL, 3, '1', '2024-08-29 18:08:03', '2024-09-01 14:35:35'),
(18, '5', 1, 6, 'IAS', 'BSIT 3-1', NULL, NULL, 3, '1', '2024-08-29 18:08:32', '2024-09-01 14:35:39'),
(19, '6', 1, 5, 'Subject 1', 'BSCE 2-4', NULL, NULL, 2, '4', '2024-08-29 18:11:14', '2024-09-01 14:35:44'),
(20, '2b0c35f8', 1, 5, 'TEST', 'BSCE 1-2', NULL, NULL, 3, '1', '2024-09-01 14:48:57', '2024-09-01 20:10:27'),
(22, 'd9056bb1', 1, 5, 'test', 'test', NULL, NULL, 3, '5', '2024-09-02 14:52:56', '2024-09-02 14:52:56'),
(23, 'd7ea8649', 1, 5, 'SAD', 'BSIT 3-1', NULL, NULL, 0, '', '2024-09-04 13:53:12', '2024-09-04 13:53:12');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `course_name` varchar(150) NOT NULL,
  `faculty_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_id`, `class_id`, `course_name`, `faculty_id`) VALUES
(1, NULL, 'Test 1', 1),
(5, NULL, 'bsce', 1),
(6, NULL, 'BSIT', 1);

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `firstname` varchar(150) NOT NULL,
  `lastname` varchar(150) NOT NULL,
  `faculty_number` varchar(15) NOT NULL,
  `webmail` varchar(150) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` tinyint(1) NOT NULL DEFAULT 2,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `firstname`, `lastname`, `faculty_number`, `webmail`, `username`, `password`, `user_type`, `date_updated`) VALUES
(1, 'test', 'test', '', 'test@gmail.com', 'test', '$2y$10$lGqhAQfwER8lPnGTKEPxYeO4YQ5sP65omqPK4XWPIPL/NIa5pFhca', 2, '2024-08-24 14:46:06');

-- --------------------------------------------------------

--
-- Table structure for table `flashcard`
--

CREATE TABLE `flashcard` (
  `flashcard_id` int(11) NOT NULL,
  `term` varchar(255) NOT NULL,
  `definition` varchar(255) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `get_shared_code`
--

CREATE TABLE `get_shared_code` (
  `getcode_id` int(11) NOT NULL,
  `sharedcode_id` int(11) NOT NULL,
  `is_valid` tinyint(1) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date_entered` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `order_by` int(11) NOT NULL,
  `ques_type` tinyint(1) NOT NULL,
  `total_points` int(3) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `question`, `assessment_id`, `order_by`, `ques_type`, `total_points`, `date_updated`) VALUES
(1, 'test', 1, 1, 1, 1, '2024-09-02 17:05:42');

-- --------------------------------------------------------

--
-- Table structure for table `question_identifications`
--

CREATE TABLE `question_identifications` (
  `identification_id` int(11) NOT NULL,
  `identification_answer` text NOT NULL,
  `question_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `option_id` int(11) NOT NULL,
  `option_txt` text NOT NULL,
  `is_right` tinyint(1) NOT NULL,
  `question_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_answer`
--

CREATE TABLE `rw_answer` (
  `rw_answer_id` int(11) NOT NULL,
  `answer_text` text NOT NULL,
  `rw_submission_id` int(11) NOT NULL,
  `rw_question_id` int(11) NOT NULL,
  `rw_option_id` int(11) DEFAULT NULL,
  `is_right` tinyint(1) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_questions`
--

CREATE TABLE `rw_questions` (
  `rw_question_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `order_by` int(11) NOT NULL,
  `question_type` tinyint(1) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_question_identifications`
--

CREATE TABLE `rw_question_identifications` (
  `rw_identification_id` int(11) NOT NULL,
  `rw_question_id` int(11) NOT NULL,
  `identification_answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_question_opt`
--

CREATE TABLE `rw_question_opt` (
  `rw_option_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_right` tinyint(1) NOT NULL,
  `rw_question_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_reviewer`
--

CREATE TABLE `rw_reviewer` (
  `reviewer_id` int(11) NOT NULL,
  `sharedcode_int` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `topic` varchar(255) NOT NULL,
  `reviewer_type` tinyint(1) NOT NULL,
  `rw_question_id` int(11) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_student_submission`
--

CREATE TABLE `rw_student_submission` (
  `rw_submission_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `student_score` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_taken` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shared_code`
--

CREATE TABLE `shared_code` (
  `sharedcode_id` int(11) NOT NULL,
  `flashcard_id` int(11) NOT NULL,
  `generated_code` varchar(255) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `firstname` varchar(150) NOT NULL,
  `lastname` varchar(150) NOT NULL,
  `webmail` varchar(150) NOT NULL,
  `student_number` varchar(15) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` tinyint(1) NOT NULL DEFAULT 3,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `firstname`, `lastname`, `webmail`, `student_number`, `username`, `password`, `user_type`, `date_updated`) VALUES
(1, 'test', 'test', 'test@gmail.com', 'test', 'test', '$2y$10$QxKsWR.ylliFsH0LqdGcMe.psI2Q3Eehz/tI5sKK9iHG2y0.rq9qO', 3, '2024-08-23 17:19:42');

-- --------------------------------------------------------

--
-- Table structure for table `student_answer`
--

CREATE TABLE `student_answer` (
  `answer_id` int(11) NOT NULL,
  `answer_text` text NOT NULL,
  `identification_id` int(11) DEFAULT NULL,
  `submission_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `is_right` tinyint(1) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_enrollment`
--

CREATE TABLE `student_enrollment` (
  `studentEnrollment_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_enrollment`
--

INSERT INTO `student_enrollment` (`studentEnrollment_id`, `class_id`, `student_id`, `status`) VALUES
(5, 19, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_results`
--

CREATE TABLE `student_results` (
  `results_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `items` int(3) NOT NULL,
  `score` int(3) NOT NULL,
  `remarks` tinyint(1) DEFAULT NULL,
  `rank` int(3) DEFAULT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_submission`
--

CREATE TABLE `student_submission` (
  `submission_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_score` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_taken` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administer_assessment`
--
ALTER TABLE `administer_assessment`
  ADD PRIMARY KEY (`administer_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `administer_assessment_ibfk_3` (`class_id`);

--
-- Indexes for table `assessment`
--
ALTER TABLE `assessment`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `faculty_id` (`faculty_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`);

--
-- Indexes for table `flashcard`
--
ALTER TABLE `flashcard`
  ADD PRIMARY KEY (`flashcard_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `get_shared_code`
--
ALTER TABLE `get_shared_code`
  ADD PRIMARY KEY (`getcode_id`),
  ADD KEY `sharedcode_id` (`sharedcode_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `question_identifications`
--
ALTER TABLE `question_identifications`
  ADD PRIMARY KEY (`identification_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `rw_answer`
--
ALTER TABLE `rw_answer`
  ADD PRIMARY KEY (`rw_answer_id`),
  ADD KEY `rw_submission_id` (`rw_submission_id`),
  ADD KEY `rw_question_id` (`rw_question_id`),
  ADD KEY `rw_option_id` (`rw_option_id`);

--
-- Indexes for table `rw_questions`
--
ALTER TABLE `rw_questions`
  ADD PRIMARY KEY (`rw_question_id`);

--
-- Indexes for table `rw_question_identifications`
--
ALTER TABLE `rw_question_identifications`
  ADD PRIMARY KEY (`rw_identification_id`),
  ADD KEY `rw_question_id` (`rw_question_id`);

--
-- Indexes for table `rw_question_opt`
--
ALTER TABLE `rw_question_opt`
  ADD PRIMARY KEY (`rw_option_id`),
  ADD KEY `rw_question_id` (`rw_question_id`);

--
-- Indexes for table `rw_reviewer`
--
ALTER TABLE `rw_reviewer`
  ADD PRIMARY KEY (`reviewer_id`),
  ADD KEY `rw_question_id` (`rw_question_id`);

--
-- Indexes for table `rw_student_submission`
--
ALTER TABLE `rw_student_submission`
  ADD PRIMARY KEY (`rw_submission_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `shared_code`
--
ALTER TABLE `shared_code`
  ADD PRIMARY KEY (`sharedcode_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `flashcard_id` (`flashcard_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `student_answer`
--
ALTER TABLE `student_answer`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `submission_id` (`submission_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `student_enrollment`
--
ALTER TABLE `student_enrollment`
  ADD PRIMARY KEY (`studentEnrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `student_enrollment_ibfk_1` (`class_id`);

--
-- Indexes for table `student_results`
--
ALTER TABLE `student_results`
  ADD PRIMARY KEY (`results_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `student_results_ibfk_3` (`class_id`);

--
-- Indexes for table `student_submission`
--
ALTER TABLE `student_submission`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administer_assessment`
--
ALTER TABLE `administer_assessment`
  MODIFY `administer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assessment`
--
ALTER TABLE `assessment`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `flashcard`
--
ALTER TABLE `flashcard`
  MODIFY `flashcard_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `get_shared_code`
--
ALTER TABLE `get_shared_code`
  MODIFY `getcode_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `question_identifications`
--
ALTER TABLE `question_identifications`
  MODIFY `identification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_answer`
--
ALTER TABLE `rw_answer`
  MODIFY `rw_answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_questions`
--
ALTER TABLE `rw_questions`
  MODIFY `rw_question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_question_identifications`
--
ALTER TABLE `rw_question_identifications`
  MODIFY `rw_identification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_question_opt`
--
ALTER TABLE `rw_question_opt`
  MODIFY `rw_option_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_reviewer`
--
ALTER TABLE `rw_reviewer`
  MODIFY `reviewer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_student_submission`
--
ALTER TABLE `rw_student_submission`
  MODIFY `rw_submission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shared_code`
--
ALTER TABLE `shared_code`
  MODIFY `sharedcode_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_answer`
--
ALTER TABLE `student_answer`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_enrollment`
--
ALTER TABLE `student_enrollment`
  MODIFY `studentEnrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_results`
--
ALTER TABLE `student_results`
  MODIFY `results_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_submission`
--
ALTER TABLE `student_submission`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administer_assessment`
--
ALTER TABLE `administer_assessment`
  ADD CONSTRAINT `administer_assessment_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment` (`assessment_id`),
  ADD CONSTRAINT `administer_assessment_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`),
  ADD CONSTRAINT `administer_assessment_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`);

--
-- Constraints for table `assessment`
--
ALTER TABLE `assessment`
  ADD CONSTRAINT `assessment_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`),
  ADD CONSTRAINT `assessment_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `class_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`),
  ADD CONSTRAINT `class_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`);

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `course_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `flashcard`
--
ALTER TABLE `flashcard`
  ADD CONSTRAINT `flashcard_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`);

--
-- Constraints for table `get_shared_code`
--
ALTER TABLE `get_shared_code`
  ADD CONSTRAINT `get_shared_code_ibfk_1` FOREIGN KEY (`sharedcode_id`) REFERENCES `shared_code` (`sharedcode_id`);

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment` (`assessment_id`);

--
-- Constraints for table `question_identifications`
--
ALTER TABLE `question_identifications`
  ADD CONSTRAINT `question_identifications_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`);

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`);

--
-- Constraints for table `rw_answer`
--
ALTER TABLE `rw_answer`
  ADD CONSTRAINT `rw_answer_ibfk_1` FOREIGN KEY (`rw_submission_id`) REFERENCES `rw_student_submission` (`rw_submission_id`),
  ADD CONSTRAINT `rw_answer_ibfk_2` FOREIGN KEY (`rw_question_id`) REFERENCES `rw_questions` (`rw_question_id`),
  ADD CONSTRAINT `rw_answer_ibfk_3` FOREIGN KEY (`rw_option_id`) REFERENCES `rw_question_opt` (`rw_option_id`);

--
-- Constraints for table `rw_question_identifications`
--
ALTER TABLE `rw_question_identifications`
  ADD CONSTRAINT `rw_question_identifications_ibfk_1` FOREIGN KEY (`rw_question_id`) REFERENCES `rw_questions` (`rw_question_id`);

--
-- Constraints for table `rw_question_opt`
--
ALTER TABLE `rw_question_opt`
  ADD CONSTRAINT `rw_question_opt_ibfk_1` FOREIGN KEY (`rw_question_id`) REFERENCES `rw_questions` (`rw_question_id`);

--
-- Constraints for table `rw_reviewer`
--
ALTER TABLE `rw_reviewer`
  ADD CONSTRAINT `rw_reviewer_ibfk_1` FOREIGN KEY (`rw_question_id`) REFERENCES `rw_questions` (`rw_question_id`);

--
-- Constraints for table `rw_student_submission`
--
ALTER TABLE `rw_student_submission`
  ADD CONSTRAINT `rw_student_submission_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `rw_student_submission_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `rw_reviewer` (`reviewer_id`);

--
-- Constraints for table `shared_code`
--
ALTER TABLE `shared_code`
  ADD CONSTRAINT `shared_code_ibfk_1` FOREIGN KEY (`reviewer_id`) REFERENCES `rw_reviewer` (`reviewer_id`),
  ADD CONSTRAINT `shared_code_ibfk_2` FOREIGN KEY (`flashcard_id`) REFERENCES `flashcard` (`flashcard_id`);

--
-- Constraints for table `student_answer`
--
ALTER TABLE `student_answer`
  ADD CONSTRAINT `student_answer_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `student_submission` (`submission_id`),
  ADD CONSTRAINT `student_answer_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`);

--
-- Constraints for table `student_enrollment`
--
ALTER TABLE `student_enrollment`
  ADD CONSTRAINT `student_enrollment_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`),
  ADD CONSTRAINT `student_enrollment_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`);

--
-- Constraints for table `student_results`
--
ALTER TABLE `student_results`
  ADD CONSTRAINT `student_results_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment` (`assessment_id`),
  ADD CONSTRAINT `student_results_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `student_results_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`);

--
-- Constraints for table `student_submission`
--
ALTER TABLE `student_submission`
  ADD CONSTRAINT `student_submission_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment` (`assessment_id`),
  ADD CONSTRAINT `student_submission_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
