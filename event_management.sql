-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 17, 2025 at 04:58 PM
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
-- Database: `event_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `advisor`
--

CREATE TABLE `advisor` (
  `advisor_id` int(11) NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `advisor`
--

INSERT INTO `advisor` (`advisor_id`, `club_id`, `first_name`, `last_name`, `password`) VALUES
(101, 1, 'Zahirul', 'Islam', 'Zahirul123'),
(102, 2, 'Farhana', 'Kabir', 'Farhana456'),
(103, 3, 'Nazmul', 'Ferdous', 'Nazmul789');

-- --------------------------------------------------------

--
-- Table structure for table `advisor_email`
--

CREATE TABLE `advisor_email` (
  `advisor_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advisor_phone`
--

CREATE TABLE `advisor_phone` (
  `advisor_id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `club_id` int(11) NOT NULL,
  `club_name` varchar(100) NOT NULL,
  `registration_date` date NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`club_id`, `club_name`, `registration_date`, `status`) VALUES
(1, 'Science Club', '2023-01-15', 'Active'),
(2, 'Drama Club', '2022-09-10', 'Active'),
(3, 'Sports Club', '2021-06-20', 'Active'),
(4, 'Art Club', '2025-08-17', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `clubs_cell_number`
--

CREATE TABLE `clubs_cell_number` (
  `club_id` int(11) NOT NULL,
  `cell_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clubs_cell_number`
--

INSERT INTO `clubs_cell_number` (`club_id`, `cell_number`) VALUES
(4, '019999999');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `approval_status` varchar(20) DEFAULT NULL,
  `total_budget` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `club_id`, `title`, `event_type`, `event_date`, `start_time`, `end_time`, `approval_status`, `total_budget`) VALUES
(501, 1, 'Science Fair 2025', 'Exhibition', '2025-01-14', '10:00:00', '15:00:00', 'Approved', 30000.00),
(503, 2, 'Drama Fest: Classics Revived', 'Stage Performance', '2024-01-20', '16:00:00', '19:00:00', 'Approved', 25000.00),
(504, 2, 'Playwriting Seminar', 'Seminar', '2025-10-10', '11:00:00', '16:00:00', 'Approved', 10000.00),
(506, 2, 'Dance Battle ', 'Stage Performance', '2025-12-12', '23:38:00', '17:40:00', 'Approved', 200000.00),
(507, 3, 'Intra-University Cricket Tournament', 'Sports event', '2025-12-29', '18:52:00', '18:53:00', 'Approved', 70300.00),
(508, 3, 'Intra-University Football Tournament', 'Sports event', '2025-11-13', '10:09:00', '19:11:00', 'Approved', 70300.00),
(510, 1, 'AI and Robotics Workshop', 'Workshop', '2025-09-25', '20:26:00', '17:26:00', 'Approved', 90000.00);

-- --------------------------------------------------------

--
-- Table structure for table `executive`
--

CREATE TABLE `executive` (
  `student_id` int(11) DEFAULT NULL,
  `vol_id` int(11) DEFAULT NULL,
  `exec_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT 0,
  `comments` text DEFAULT NULL,
  `submitted_on` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `student_id`, `event_id`, `rating`, `comments`, `submitted_on`) VALUES
(1, 1001, 506, 9, 'The dance battle was effing awesome!', '2025-08-17');

-- --------------------------------------------------------

--
-- Table structure for table `joins`
--

CREATE TABLE `joins` (
  `student_id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `join_date` date DEFAULT NULL,
  `join_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `joins`
--

INSERT INTO `joins` (`student_id`, `club_id`, `join_date`, `join_status`) VALUES
(1001, 1, '2025-08-16', 'Active'),
(1001, 2, '2025-08-13', 'Pending'),
(1001, 3, '2025-08-16', 'Pending'),
(1002, 2, '2024-02-15', 'Active'),
(1002, 3, '2025-08-13', 'Pending'),
(1003, 3, '2024-01-20', 'Active'),
(1004, 1, '2024-03-01', 'Active'),
(1005, 2, '2024-01-25', 'Active'),
(1006, 1, '2025-08-17', 'Active'),
(1006, 3, '2024-02-05', 'Inactive'),
(1007, 1, '2024-01-30', 'Active'),
(1007, 2, '2025-08-13', 'Pending'),
(1007, 3, '2025-08-13', 'Pending'),
(1009, 1, '2025-08-17', 'Active'),
(1009, 2, '2025-08-17', 'Pending'),
(1009, 3, '2025-08-17', 'Pending'),
(1010, 2, '2025-08-17', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `house_no` varchar(10) DEFAULT NULL,
  `street_no` varchar(20) DEFAULT NULL,
  `city` varchar(10) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`student_id`, `first_name`, `last_name`, `house_no`, `street_no`, `city`, `date_of_birth`, `password`) VALUES
(1001, 'Imran', 'Kabir', '12A', '7', 'Dhaka', '2000-05-12', 'Imran133'),
(1002, 'Nusrat', 'Akter', '44', '5', 'Chattogram', '2001-11-03', 'Nusrat4357'),
(1003, 'Rifat', 'Mahmud', '23', '6', 'Sylhet', '2002-08-19', 'Rifat1385'),
(1004, 'Tanzila', 'Noor', '8B', '12', 'Rajshahi', '2000-02-28', 'Tanzila3855'),
(1005, 'Farhan', 'Hossain', '33', '9', 'Khulna', '2003-03-22', 'Farhan5122'),
(1006, 'Mehazabien', 'Mahi', '15C', '11', 'Comilla', '2002-07-17', 'Mehazabien4045'),
(1007, 'Abrar', 'Ahmed', '10A', '14', 'Barisal', '2001-09-01', 'Abrar4859'),
(1009, 'Abrar', 'Alam', '10', '6C', 'Dhaka', '2002-04-16', 'Abrar2162'),
(1010, 'Torsha', 'Alam', '10', '6C', 'Dhaka', '2002-04-16', 'Torsha6234'),
(1011, 'Sadman', 'Jawad', '10', '6C', 'Dhaka', '2002-04-16', 'Sadman4684'),
(1023, 'Abrar', 'Alam', '10', '6C', 'Tochigi', '2003-04-16', '111'),
(1025, 'Torsha', 'Alam', '10', '6C', 'Tochigi', '2003-04-16', '112');

-- --------------------------------------------------------

--
-- Table structure for table `members_email`
--

CREATE TABLE `members_email` (
  `student_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members_email`
--

INSERT INTO `members_email` (`student_id`, `email`) VALUES
(1001, 'Imran.the.empyrean@gmail.com'),
(1001, 'Imran.the.grandiose@gmail.com'),
(1023, 'abrar.the.empyrean@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `members_phone`
--

CREATE TABLE `members_phone` (
  `student_id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members_phone`
--

INSERT INTO `members_phone` (`student_id`, `phone_number`) VALUES
(1023, '09018510438'),
(1025, '09018510438');

-- --------------------------------------------------------

--
-- Table structure for table `organizer`
--

CREATE TABLE `organizer` (
  `student_id` int(11) DEFAULT NULL,
  `org_id` int(11) NOT NULL,
  `work_hours` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizer`
--

INSERT INTO `organizer` (`student_id`, `org_id`, `work_hours`) VALUES
(1005, 105, 18),
(1006, 106, 22),
(1007, 107, 16),
(1001, 109, 20),
(1009, 111, 3),
(1011, 112, 9);

-- --------------------------------------------------------

--
-- Table structure for table `partakes`
--

CREATE TABLE `partakes` (
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `attendee_id` int(11) NOT NULL,
  `attendance_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partakes`
--

INSERT INTO `partakes` (`student_id`, `event_id`, `attendee_id`, `attendance_status`) VALUES
(1001, 504, 7, 'Registered'),
(1001, 506, 9, 'Registered'),
(1001, 508, 8, 'Registered'),
(1002, 504, 3, 'Registered'),
(1002, 506, 5, 'Registered'),
(1002, 507, 4, 'Registered'),
(1002, 508, 1, 'Registered'),
(1003, 504, 10, 'Registered'),
(1003, 506, 12, 'Registered'),
(1003, 507, 13, 'Registered'),
(1003, 508, 11, 'Registered'),
(1007, 504, 14, 'Registered'),
(1011, 510, 15, 'Registered');

-- --------------------------------------------------------

--
-- Table structure for table `president`
--

CREATE TABLE `president` (
  `student_id` int(11) DEFAULT NULL,
  `vol_id` int(11) DEFAULT NULL,
  `president_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `president`
--

INSERT INTO `president` (`student_id`, `vol_id`, `president_id`, `start_date`, `end_date`) VALUES
(1002, 2, 2, '2025-08-13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `report_id` int(11) NOT NULL,
  `rpt_title` varchar(100) NOT NULL,
  `event_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `performance_rating` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(11) NOT NULL,
  `club_id` int(11) DEFAULT NULL,
  `resource_name` varchar(20) NOT NULL,
  `resource_type` varchar(50) DEFAULT NULL,
  `availability_status` varchar(20) NOT NULL,
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `club_id`, `resource_name`, `resource_type`, `availability_status`, `quantity`) VALUES
(901, 1, 'Microscope Set', 'Equipment', 'Available', 4),
(902, 1, 'Arduino Kit', 'Hardware', 'Available', 7),
(903, 2, 'Stage Lighting Syste', 'Equipment', 'In Use', 1),
(904, 2, 'Costume Rack', 'Supplies', 'Available', 3),
(905, 3, 'Cricket Gear Set', 'Equipment', 'Available', 2),
(906, 3, 'Football Jerseys', 'Supplies', 'Available', 20);

-- --------------------------------------------------------

--
-- Table structure for table `superadmin`
--

CREATE TABLE `superadmin` (
  `SA_ID` int(11) NOT NULL,
  `advisor_id` int(11) NOT NULL,
  `password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `superadmin`
--

INSERT INTO `superadmin` (`SA_ID`, `advisor_id`, `password`) VALUES
(1, 101, 'adminpass');

-- --------------------------------------------------------

--
-- Table structure for table `supervisor`
--

CREATE TABLE `supervisor` (
  `student_id` int(11) DEFAULT NULL,
  `org_id` int(11) DEFAULT NULL,
  `sup_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supervisor`
--

INSERT INTO `supervisor` (`student_id`, `org_id`, `sup_id`, `start_date`, `end_date`) VALUES
(1005, 105, 1992, '2025-08-13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `volunteer`
--

CREATE TABLE `volunteer` (
  `student_id` int(11) NOT NULL,
  `vol_id` int(11) NOT NULL,
  `report_id` int(11) DEFAULT NULL,
  `start_session` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `volunteer`
--

INSERT INTO `volunteer` (`student_id`, `vol_id`, `report_id`, `start_session`) VALUES
(1002, 2, NULL, '2024-02-01 10:30:00'),
(1003, 3, NULL, '2024-03-10 08:45:00'),
(1004, 4, NULL, '2024-01-20 14:00:00'),
(1010, 5, NULL, '2025-08-17 04:34:00'),
(1023, 11, NULL, '2025-08-17 19:26:00'),
(1025, 12, NULL, '2025-08-17 19:26:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advisor`
--
ALTER TABLE `advisor`
  ADD PRIMARY KEY (`advisor_id`),
  ADD UNIQUE KEY `advisor_id` (`advisor_id`),
  ADD UNIQUE KEY `password` (`password`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `advisor_email`
--
ALTER TABLE `advisor_email`
  ADD PRIMARY KEY (`advisor_id`,`email`);

--
-- Indexes for table `advisor_phone`
--
ALTER TABLE `advisor_phone`
  ADD PRIMARY KEY (`advisor_id`,`phone_number`);

--
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`club_id`),
  ADD UNIQUE KEY `club_id` (`club_id`),
  ADD UNIQUE KEY `club_name` (`club_name`);

--
-- Indexes for table `clubs_cell_number`
--
ALTER TABLE `clubs_cell_number`
  ADD PRIMARY KEY (`club_id`,`cell_number`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD UNIQUE KEY `event_id` (`event_id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `executive`
--
ALTER TABLE `executive`
  ADD PRIMARY KEY (`exec_id`),
  ADD KEY `student_id` (`student_id`,`vol_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD UNIQUE KEY `feedback_id` (`feedback_id`),
  ADD KEY `fk_feedback_member` (`student_id`),
  ADD KEY `fk_feedback_event` (`event_id`);

--
-- Indexes for table `joins`
--
ALTER TABLE `joins`
  ADD PRIMARY KEY (`student_id`,`club_id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `unique_password` (`password`);

--
-- Indexes for table `members_email`
--
ALTER TABLE `members_email`
  ADD PRIMARY KEY (`student_id`,`email`);

--
-- Indexes for table `members_phone`
--
ALTER TABLE `members_phone`
  ADD PRIMARY KEY (`student_id`,`phone_number`);

--
-- Indexes for table `organizer`
--
ALTER TABLE `organizer`
  ADD PRIMARY KEY (`org_id`),
  ADD UNIQUE KEY `org_id` (`org_id`),
  ADD KEY `fk_organizer_member` (`student_id`);

--
-- Indexes for table `partakes`
--
ALTER TABLE `partakes`
  ADD PRIMARY KEY (`student_id`,`event_id`,`attendee_id`),
  ADD UNIQUE KEY `attendee_id` (`attendee_id`),
  ADD KEY `partakes_ibfk_2` (`event_id`);

--
-- Indexes for table `president`
--
ALTER TABLE `president`
  ADD PRIMARY KEY (`president_id`),
  ADD UNIQUE KEY `president_id` (`president_id`),
  ADD KEY `student_id` (`student_id`,`vol_id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `unique_event` (`event_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `superadmin`
--
ALTER TABLE `superadmin`
  ADD PRIMARY KEY (`SA_ID`),
  ADD UNIQUE KEY `SA_ID` (`SA_ID`),
  ADD UNIQUE KEY `advisor_id` (`advisor_id`),
  ADD UNIQUE KEY `unique_advisor` (`advisor_id`);

--
-- Indexes for table `supervisor`
--
ALTER TABLE `supervisor`
  ADD PRIMARY KEY (`sup_id`),
  ADD KEY `org_id` (`org_id`),
  ADD KEY `fk_supervisor_member` (`student_id`);

--
-- Indexes for table `volunteer`
--
ALTER TABLE `volunteer`
  ADD PRIMARY KEY (`student_id`,`vol_id`),
  ADD UNIQUE KEY `vol_id` (`vol_id`),
  ADD KEY `report_id` (`report_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advisor`
--
ALTER TABLE `advisor`
  MODIFY `advisor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `club_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=511;

--
-- AUTO_INCREMENT for table `executive`
--
ALTER TABLE `executive`
  MODIFY `exec_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1026;

--
-- AUTO_INCREMENT for table `organizer`
--
ALTER TABLE `organizer`
  MODIFY `org_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `partakes`
--
ALTER TABLE `partakes`
  MODIFY `attendee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `president`
--
ALTER TABLE `president`
  MODIFY `president_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=907;

--
-- AUTO_INCREMENT for table `superadmin`
--
ALTER TABLE `superadmin`
  MODIFY `SA_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supervisor`
--
ALTER TABLE `supervisor`
  MODIFY `sup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1993;

--
-- AUTO_INCREMENT for table `volunteer`
--
ALTER TABLE `volunteer`
  MODIFY `vol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `advisor`
--
ALTER TABLE `advisor`
  ADD CONSTRAINT `advisor_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`club_id`);

--
-- Constraints for table `advisor_email`
--
ALTER TABLE `advisor_email`
  ADD CONSTRAINT `advisor_email_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `advisor` (`advisor_id`);

--
-- Constraints for table `advisor_phone`
--
ALTER TABLE `advisor_phone`
  ADD CONSTRAINT `advisor_phone_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `advisor` (`advisor_id`);

--
-- Constraints for table `clubs_cell_number`
--
ALTER TABLE `clubs_cell_number`
  ADD CONSTRAINT `clubs_cell_number_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`club_id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`club_id`);

--
-- Constraints for table `executive`
--
ALTER TABLE `executive`
  ADD CONSTRAINT `executive_ibfk_1` FOREIGN KEY (`student_id`,`vol_id`) REFERENCES `volunteer` (`student_id`, `vol_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feedback_member` FOREIGN KEY (`student_id`) REFERENCES `members` (`student_id`);

--
-- Constraints for table `joins`
--
ALTER TABLE `joins`
  ADD CONSTRAINT `fk_joins_member` FOREIGN KEY (`student_id`) REFERENCES `members` (`student_id`),
  ADD CONSTRAINT `joins_ibfk_2` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`club_id`);

--
-- Constraints for table `members_email`
--
ALTER TABLE `members_email`
  ADD CONSTRAINT `fk_members_email` FOREIGN KEY (`student_id`) REFERENCES `members` (`student_id`);

--
-- Constraints for table `members_phone`
--
ALTER TABLE `members_phone`
  ADD CONSTRAINT `fk_members_phone` FOREIGN KEY (`student_id`) REFERENCES `members` (`student_id`);

--
-- Constraints for table `organizer`
--
ALTER TABLE `organizer`
  ADD CONSTRAINT `fk_organizer_member` FOREIGN KEY (`student_id`) REFERENCES `members` (`student_id`);

--
-- Constraints for table `partakes`
--
ALTER TABLE `partakes`
  ADD CONSTRAINT `fk_partakes_member` FOREIGN KEY (`student_id`) REFERENCES `members` (`student_id`),
  ADD CONSTRAINT `partakes_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `president`
--
ALTER TABLE `president`
  ADD CONSTRAINT `president_ibfk_1` FOREIGN KEY (`student_id`,`vol_id`) REFERENCES `volunteer` (`student_id`, `vol_id`);

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `fk_report_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`club_id`);

--
-- Constraints for table `superadmin`
--
ALTER TABLE `superadmin`
  ADD CONSTRAINT `fk_superadmin_advisor` FOREIGN KEY (`advisor_id`) REFERENCES `advisor` (`advisor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `supervisor`
--
ALTER TABLE `supervisor`
  ADD CONSTRAINT `fk_supervisor_member` FOREIGN KEY (`student_id`) REFERENCES `members` (`student_id`),
  ADD CONSTRAINT `supervisor_ibfk_2` FOREIGN KEY (`org_id`) REFERENCES `organizer` (`org_id`);

--
-- Constraints for table `volunteer`
--
ALTER TABLE `volunteer`
  ADD CONSTRAINT `fk_volunteer_member` FOREIGN KEY (`student_id`) REFERENCES `members` (`student_id`),
  ADD CONSTRAINT `volunteer_ibfk_2` FOREIGN KEY (`report_id`) REFERENCES `report` (`report_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
