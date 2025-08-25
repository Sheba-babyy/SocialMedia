-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2025 at 07:31 AM
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
-- Database: `db_miniproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `admin_id` int(10) UNSIGNED NOT NULL,
  `admin_name` varchar(50) NOT NULL,
  `admin_email` varchar(50) NOT NULL,
  `admin_password` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_admin`
--

INSERT INTO `tbl_admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$lPAD4oiNRh7WZR3x/pmbR.4SJ1MZL1oRMCwjoHWZAUoBzGHa7lpT2');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_chat`
--

CREATE TABLE `tbl_chat` (
  `chat_id` int(11) NOT NULL,
  `chat_datetime` datetime NOT NULL,
  `chat_content` varchar(5000) NOT NULL,
  `chat_file` text NOT NULL,
  `user_from_id` int(11) NOT NULL,
  `user_to_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_chat`
--

INSERT INTO `tbl_chat` (`chat_id`, `chat_datetime`, `chat_content`, `chat_file`, `user_from_id`, `user_to_id`) VALUES
(1, '2025-07-19 15:17:18', 'hi', '', 1, 2),
(2, '2025-07-19 15:21:18', 'edi sugalle', '', 2, 1),
(3, '2025-07-19 15:23:08', 'hi', '', 1, 2),
(4, '2025-07-19 15:29:17', 'sugano ninak', '', 2, 1),
(5, '2025-07-19 15:29:23', 'ah di suagm', '', 1, 2),
(6, '2025-07-19 15:30:11', 'enna edukka', '', 2, 1),
(7, '2025-07-19 15:30:23', 'padikka', '', 1, 2),
(9, '2025-07-25 10:40:08', '', '1753420208_Screenshot 2024-06-24 204841.png', 1, 2),
(22, '2025-07-26 06:47:24', 'hii', '', 1, 5),
(25, '2025-08-08 10:38:21', '', '1754629701_f1.jpg', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_comment`
--

CREATE TABLE `tbl_comment` (
  `comment_id` int(11) NOT NULL,
  `comment_content` varchar(50) NOT NULL,
  `comment_date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_comment`
--

INSERT INTO `tbl_comment` (`comment_id`, `comment_content`, `comment_date`, `user_id`, `post_id`) VALUES
(4, 'Very beautiful flower', '2025-08-18', 1, 5),
(5, 'hi', '2025-08-19', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_complaint`
--

CREATE TABLE `tbl_complaint` (
  `complaint_id` int(11) NOT NULL,
  `complaint_title` varchar(100) NOT NULL,
  `complaint_content` varchar(1500) NOT NULL,
  `complaint_reply` varchar(1500) NOT NULL,
  `complaint_status` int(11) NOT NULL DEFAULT 0,
  `complaint_date` date NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_complaint`
--

INSERT INTO `tbl_complaint` (`complaint_id`, `complaint_title`, `complaint_content`, `complaint_reply`, `complaint_status`, `complaint_date`, `user_id`) VALUES
(3, 'Service Delay', 'bbgvv', 'Actions are taken', 1, '2025-07-01', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_district`
--

CREATE TABLE `tbl_district` (
  `district_id` int(11) NOT NULL,
  `district_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_district`
--

INSERT INTO `tbl_district` (`district_id`, `district_name`) VALUES
(1, 'Ernakulam'),
(2, 'Kollam'),
(5, 'kottayam'),
(6, 'Kannur'),
(7, 'Alappuzha'),
(8, 'Idukki'),
(9, 'Kasargod'),
(10, 'Kozhikode'),
(11, 'Malappuram'),
(12, 'Palakkad'),
(13, 'Pathanamthitta'),
(14, 'Thiruvananthapuram'),
(15, 'Thrissur'),
(16, 'Wayanad');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_feedback`
--

CREATE TABLE `tbl_feedback` (
  `feedback_id` int(11) NOT NULL,
  `feedback_content` varchar(5000) NOT NULL,
  `rating_value` int(11) NOT NULL,
  `feedback_date` date NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_feedback`
--

INSERT INTO `tbl_feedback` (`feedback_id`, `feedback_content`, `rating_value`, `feedback_date`, `user_id`) VALUES
(1, 'good', 5, '2025-07-12', 1),
(2, 'The clean interface and thoughtful features make sharing moments with my family so easy and enjoyable', 5, '2025-08-15', 3);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_feedback_comments`
--

CREATE TABLE `tbl_feedback_comments` (
  `comment_id` int(11) NOT NULL,
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `comment_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_feedback_comments`
--

INSERT INTO `tbl_feedback_comments` (`comment_id`, `feedback_id`, `user_id`, `comment_text`, `comment_date`) VALUES
(1, 1, 3, 'helpful', '0000-00-00'),
(2, 2, 1, 'that is  so true', '2025-08-19'),
(4, 2, 1, 'hy', '2025-08-19');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_feedback_likes`
--

CREATE TABLE `tbl_feedback_likes` (
  `like_id` int(11) NOT NULL,
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_feedback_likes`
--

INSERT INTO `tbl_feedback_likes` (`like_id`, `feedback_id`, `user_id`) VALUES
(6, 2, 3),
(9, 1, 3),
(69, 1, 1),
(71, 2, 1),
(80, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_friends`
--

CREATE TABLE `tbl_friends` (
  `friends_id` int(11) NOT NULL,
  `user_from_id` int(11) NOT NULL,
  `user_to_id` int(11) NOT NULL,
  `friends_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_friends`
--

INSERT INTO `tbl_friends` (`friends_id`, `user_from_id`, `user_to_id`, `friends_status`) VALUES
(2, 2, 1, 1),
(3, 5, 1, 1),
(8, 1, 5, 0),
(14, 3, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_group`
--

CREATE TABLE `tbl_group` (
  `group_id` int(11) NOT NULL,
  `group_name` varchar(50) NOT NULL,
  `group_description` varchar(1000) NOT NULL,
  `group_photo` varchar(600) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_group`
--

INSERT INTO `tbl_group` (`group_id`, `group_name`, `group_description`, `group_photo`, `user_id`, `group_status`) VALUES
(2, 'FRIENDS', 'Friends forever...❤️', 'grp_photo.jpg', 1, 'active'),
(3, 'Trip Buddies', 'Let\'s explore the world✈️', 'trip_buddies.jpg', 2, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_groupchat`
--

CREATE TABLE `tbl_groupchat` (
  `groupchat_id` int(11) NOT NULL,
  `groupchat_datetime` datetime NOT NULL,
  `groupchat_content` mediumtext NOT NULL,
  `groupchat_file` text NOT NULL,
  `user_from_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_groupchat`
--

INSERT INTO `tbl_groupchat` (`groupchat_id`, `groupchat_datetime`, `groupchat_content`, `groupchat_file`, `user_from_id`, `group_id`) VALUES
(9, '2025-07-26 06:49:40', 'hi', '', 5, 2),
(13, '2025-08-08 09:54:51', 'hello', '', 1, 2),
(14, '2025-08-08 09:55:01', '', '1754627101_f1.jpg', 1, 2),
(15, '2025-08-08 10:06:31', 'hey', '', 1, 2),
(16, '2025-08-08 10:42:28', '', '1754629947_f9.jpg', 1, 2),
(17, '2025-08-08 10:51:25', 'hi', '', 1, 2),
(18, '2025-08-08 11:03:06', '', '1754631186_f8.jpg', 1, 2),
(19, '2025-08-08 11:04:39', '', '1754631279_table_design.docx', 1, 2),
(20, '2025-08-08 11:05:01', '', '1754631301_f5.png', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_groupmembers`
--

CREATE TABLE `tbl_groupmembers` (
  `groupmembers_id` int(11) NOT NULL,
  `groupmembers_status` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_groupmembers`
--

INSERT INTO `tbl_groupmembers` (`groupmembers_id`, `groupmembers_status`, `user_id`, `group_id`) VALUES
(1, 1, 1, 2),
(6, 0, 3, 2),
(8, 1, 2, 2),
(9, 0, 3, 3),
(10, 0, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_group_reports`
--

CREATE TABLE `tbl_group_reports` (
  `report_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `report_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `report_status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_like`
--

CREATE TABLE `tbl_like` (
  `like_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `like_datetime` datetime NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'post'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_like`
--

INSERT INTO `tbl_like` (`like_id`, `user_id`, `post_id`, `like_datetime`, `type`) VALUES
(3, 0, 4, '2025-07-22 19:13:01', 'post'),
(54, 3, 4, '2025-08-09 14:04:12', 'post'),
(55, 3, 3, '2025-08-09 14:49:20', 'post'),
(56, 3, 3, '2025-08-09 14:49:21', 'post'),
(232, 1, 5, '0000-00-00 00:00:00', 'post'),
(259, 1, 4, '0000-00-00 00:00:00', 'post'),
(261, 1, 3, '0000-00-00 00:00:00', 'post'),
(264, 2, 5, '0000-00-00 00:00:00', 'post'),
(268, 2, 4, '0000-00-00 00:00:00', 'post'),
(270, 2, 3, '0000-00-00 00:00:00', 'post');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_notification`
--

CREATE TABLE `tbl_notification` (
  `notification_id` int(11) NOT NULL,
  `notification_content` varchar(50) NOT NULL,
  `notification_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_place`
--

CREATE TABLE `tbl_place` (
  `place_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `place_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_place`
--

INSERT INTO `tbl_place` (`place_id`, `district_id`, `place_name`) VALUES
(4, 5, 'Pala'),
(6, 1, 'Kothamangalam'),
(7, 1, 'Muvattupuzha');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_post`
--

CREATE TABLE `tbl_post` (
  `post_id` int(11) NOT NULL,
  `post_caption` varchar(50) NOT NULL,
  `post_photo` varchar(500) NOT NULL,
  `post_date` date NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_post`
--

INSERT INTO `tbl_post` (`post_id`, `post_caption`, `post_photo`, `post_date`, `user_id`) VALUES
(3, 'vibing..', 'FuR0YuTaQAAMnSs.jpg', '2025-07-05', 2),
(4, 'Beauty of Nature #Switzerland', 'Beautiful nature __ Switzerland __ WhatsApp status __ %23shorts %23nature %23status.mp4', '2025-07-05', 2),
(5, '', 'f3.jpg', '2025-08-16', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_reports`
--

CREATE TABLE `tbl_reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `reason` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `report_date` datetime NOT NULL,
  `report_status` enum('pending','reviewed','resolved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(30) NOT NULL,
  `user_bio` varchar(2500) NOT NULL,
  `user_gender` varchar(10) NOT NULL,
  `user_status` enum('active','banned') DEFAULT 'active',
  `user_dob` date NOT NULL,
  `user_contact` varchar(11) NOT NULL,
  `user_email` varchar(30) NOT NULL,
  `user_password` varchar(2000) NOT NULL,
  `place_id` int(11) NOT NULL,
  `user_photo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `user_name`, `user_bio`, `user_gender`, `user_status`, `user_dob`, `user_contact`, `user_email`, `user_password`, `place_id`, `user_photo`) VALUES
(1, 'Anu', 'Love Ur self', 'F', 'active', '2002-07-10', '9826373456', 'anu@gmail.com', '$2y$10$T2jYihVIn73ianrMyPCPR.Ze8Sd738bIpRrr1Dpcja7IkjBbDjipq', 6, 'photo.jpg'),
(2, 'Reethu', '💕', 'F', 'active', '1995-11-15', '7853062569', 'reethu@gmail.com', '$2y$10$IWfY/.YwmiJBZQ/MYYAv9uYs.yZTmo9tdkcDU7EGqgs35AMQ/0Fly', 7, 'F_luENVa8AAFQRR.jpeg'),
(3, 'Denna', '', 'F', 'active', '2003-10-13', '7412580369', 'denna@gmail.com', '$2y$10$5Xk4gVoBK2pV6HR7w/5k8O1nBNshtQXl5KFm81Ui0WyN6IoMmuDAi', 6, 'FokPA8eXgAIwPlN.jpeg'),
(4, 'Elna', 'Be happyy..', 'F', 'active', '2009-12-28', '8790832167', 'elna123@gmail.com', '$2y$10$EdEjBAq851qpU9j1SGKsyeOR1eu7YOICemcymRKjv35o1jmzGg9Cq', 6, 'u1.jpg'),
(5, 'jith_popz', '', 'F', 'active', '2025-07-10', '9876543210', 'shebab971@gmail.com', '$2y$10$8kY8Ys8jC6Le8tNInBywk.RFJYBwsSeQBIaHGiJW6jK1R7VUh.LO2', 4, 'photo.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_verifier`
--

CREATE TABLE `tbl_verifier` (
  `verifier_id` int(11) NOT NULL,
  `verifier_name` varchar(50) NOT NULL,
  `verifier_email` varchar(50) NOT NULL,
  `verifier_contact` varchar(50) NOT NULL,
  `verifier_password` varchar(500) NOT NULL,
  `verifier_status` int(11) NOT NULL DEFAULT 0,
  `district_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_verifier`
--

INSERT INTO `tbl_verifier` (`verifier_id`, `verifier_name`, `verifier_email`, `verifier_contact`, `verifier_password`, `verifier_status`, `district_id`) VALUES
(2, 'kk', 'kk@gmail.com', '63467872', '4521', 0, 1),
(3, 'post', 'post@gmail.com', '6282038574', '$2y$10$1kRBEtTsW7CIB3OElTjEGORli4IQV7GLYVl7Jhj30O9OYKUlSWe2S', 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `tbl_chat`
--
ALTER TABLE `tbl_chat`
  ADD PRIMARY KEY (`chat_id`);

--
-- Indexes for table `tbl_comment`
--
ALTER TABLE `tbl_comment`
  ADD PRIMARY KEY (`comment_id`);

--
-- Indexes for table `tbl_complaint`
--
ALTER TABLE `tbl_complaint`
  ADD PRIMARY KEY (`complaint_id`);

--
-- Indexes for table `tbl_district`
--
ALTER TABLE `tbl_district`
  ADD PRIMARY KEY (`district_id`);

--
-- Indexes for table `tbl_feedback`
--
ALTER TABLE `tbl_feedback`
  ADD PRIMARY KEY (`feedback_id`);

--
-- Indexes for table `tbl_feedback_comments`
--
ALTER TABLE `tbl_feedback_comments`
  ADD PRIMARY KEY (`comment_id`);

--
-- Indexes for table `tbl_feedback_likes`
--
ALTER TABLE `tbl_feedback_likes`
  ADD PRIMARY KEY (`like_id`);

--
-- Indexes for table `tbl_friends`
--
ALTER TABLE `tbl_friends`
  ADD PRIMARY KEY (`friends_id`);

--
-- Indexes for table `tbl_group`
--
ALTER TABLE `tbl_group`
  ADD PRIMARY KEY (`group_id`);

--
-- Indexes for table `tbl_groupchat`
--
ALTER TABLE `tbl_groupchat`
  ADD PRIMARY KEY (`groupchat_id`);

--
-- Indexes for table `tbl_groupmembers`
--
ALTER TABLE `tbl_groupmembers`
  ADD PRIMARY KEY (`groupmembers_id`);

--
-- Indexes for table `tbl_group_reports`
--
ALTER TABLE `tbl_group_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_like`
--
ALTER TABLE `tbl_like`
  ADD PRIMARY KEY (`like_id`);

--
-- Indexes for table `tbl_notification`
--
ALTER TABLE `tbl_notification`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `tbl_place`
--
ALTER TABLE `tbl_place`
  ADD PRIMARY KEY (`place_id`);

--
-- Indexes for table `tbl_post`
--
ALTER TABLE `tbl_post`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `tbl_reports`
--
ALTER TABLE `tbl_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `tbl_verifier`
--
ALTER TABLE `tbl_verifier`
  ADD PRIMARY KEY (`verifier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `admin_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_chat`
--
ALTER TABLE `tbl_chat`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tbl_comment`
--
ALTER TABLE `tbl_comment`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_complaint`
--
ALTER TABLE `tbl_complaint`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_district`
--
ALTER TABLE `tbl_district`
  MODIFY `district_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tbl_feedback`
--
ALTER TABLE `tbl_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_feedback_comments`
--
ALTER TABLE `tbl_feedback_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_feedback_likes`
--
ALTER TABLE `tbl_feedback_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `tbl_friends`
--
ALTER TABLE `tbl_friends`
  MODIFY `friends_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_group`
--
ALTER TABLE `tbl_group`
  MODIFY `group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_groupchat`
--
ALTER TABLE `tbl_groupchat`
  MODIFY `groupchat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tbl_groupmembers`
--
ALTER TABLE `tbl_groupmembers`
  MODIFY `groupmembers_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_group_reports`
--
ALTER TABLE `tbl_group_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_like`
--
ALTER TABLE `tbl_like`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=272;

--
-- AUTO_INCREMENT for table `tbl_notification`
--
ALTER TABLE `tbl_notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_place`
--
ALTER TABLE `tbl_place`
  MODIFY `place_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_post`
--
ALTER TABLE `tbl_post`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_reports`
--
ALTER TABLE `tbl_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_verifier`
--
ALTER TABLE `tbl_verifier`
  MODIFY `verifier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_group_reports`
--
ALTER TABLE `tbl_group_reports`
  ADD CONSTRAINT `tbl_group_reports_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `tbl_group` (`group_id`),
  ADD CONSTRAINT `tbl_group_reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`user_id`);

--
-- Constraints for table `tbl_reports`
--
ALTER TABLE `tbl_reports`
  ADD CONSTRAINT `tbl_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`user_id`),
  ADD CONSTRAINT `tbl_reports_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `tbl_post` (`post_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
