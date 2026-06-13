-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 13, 2026 at 05:15 PM
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
-- Database: `travel`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity`
--

CREATE TABLE `activity` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `trip_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity`
--

INSERT INTO `activity` (`id`, `name`, `start_date`, `end_date`, `notes`, `trip_id`) VALUES
(4, 'Eiffel Tower Visit', '2026-06-02', '2026-06-02', 'Buy tickets online', 14),
(5, 'duiawhidu', '2026-06-13', '2026-06-19', 'dwadada', 16),
(6, 'dwadaddwadwa', '2026-06-13', '2026-06-19', '', 16),
(7, 'duiawhidu', '2026-06-13', '2026-06-19', 'dwadada', 17),
(8, 'dwadaddwadwa', '2026-06-13', '2026-06-19', '', 17);

-- --------------------------------------------------------

--
-- Table structure for table `destination`
--

CREATE TABLE `destination` (
  `id` int(11) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destination`
--

INSERT INTO `destination` (`id`, `city`, `country`, `description`) VALUES
(1, 'Pariz', 'Francuska', 'Grad svjetlosti'),
(2, 'Rim', 'Italija', 'Vječni grad'),
(3, 'London', 'Engleska', 'Grad na Temzi'),
(4, 'Budimpešta', 'Mađarska', 'Bisava na Dunavu'),
(5, 'Dubrovnik', 'Hrvatska', 'Biser Jadrana'),
(6, 'Paris', 'France', 'City of Light');

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `trip_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`id`, `rating`, `comment`, `user_id`, `trip_id`, `created_at`) VALUES
(3, 5, 'duhwaida', 3, 4, '2026-05-23 18:25:27'),
(4, 5, 'super', 7, 4, '2026-05-23 18:27:31'),
(6, 3, 'dwadaw', 3, 5, '2026-06-13 14:18:29');

-- --------------------------------------------------------

--
-- Table structure for table `trip`
--

CREATE TABLE `trip` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `destination_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip`
--

INSERT INTO `trip` (`id`, `name`, `status`, `start_date`, `end_date`, `notes`, `user_id`, `destination_id`) VALUES
(4, 'testmilena', 'planirano', '2026-05-25', '2026-05-25', '', 6, NULL),
(5, 'test2', 'planirano', '2026-05-26', '2026-05-27', 'dawdwad', 6, NULL),
(7, 'test2', 'planirano', '2026-05-26', '2026-05-27', 'dawdwad', 1, NULL),
(9, 'trip marko', 'završeno', '2026-05-29', '2026-05-29', 'dwad', 3, NULL),
(10, 's', 'u toku', '2026-07-24', '2026-10-30', '', 3, NULL),
(13, 'testimp', 'planirano', '2026-05-26', '2026-05-27', 'testiranjeimporta', 1, NULL),
(14, 'Trip to Paris', 'planirano', '2026-06-01', '2026-06-10', 'Visit Eiffel Tower', 3, NULL),
(15, 'Trip to Moscow', 'planirano', '2026-06-01', '2026-06-10', '', 3, NULL),
(16, 'trip djole', 'završeno', '2026-06-13', '2026-06-19', 'dawda', 8, NULL),
(17, 'wdadawda', 'završeno', '2026-06-13', '2026-06-19', 'dawda', 8, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trip_destination`
--

CREATE TABLE `trip_destination` (
  `trip_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_destination`
--

INSERT INTO `trip_destination` (`trip_id`, `destination_id`) VALUES
(14, 6),
(16, 3),
(16, 4),
(16, 6),
(17, 3),
(17, 4),
(17, 6);

-- --------------------------------------------------------

--
-- Table structure for table `trip_photo`
--

CREATE TABLE `trip_photo` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `upload_date` date DEFAULT curdate(),
  `caption` varchar(255) DEFAULT NULL,
  `trip_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_photo`
--

INSERT INTO `trip_photo` (`id`, `name`, `url`, `upload_date`, `caption`, `trip_id`) VALUES
(12, 's1', 'https://wallpaperaccess.com/full/11729.jpg', '2026-05-23', '', 9),
(13, 'a', 'https://wallpaperaccess.com/full/11729.jpg', '2026-05-23', '', 9),
(14, 'Eiffel Tower', 'https://example.com/eiffel.jpg', '2026-05-23', 'Beautiful view', 14),
(15, 'dwadwa', 'http://localhost/travel/photos.php?trip_id=16', '2026-06-13', 'dwad', 16),
(16, 'dwada', 'http://localhost/travel/photos.php?trip_id=16', '2026-06-13', 'dad', 16),
(17, 'dwadwa', 'http://localhost/travel/photos.php?trip_id=16', '2026-06-13', 'dwad', 17),
(18, 'dwada', 'http://localhost/travel/photos.php?trip_id=16', '2026-06-13', 'dad', 17);

-- --------------------------------------------------------

--
-- Table structure for table `trip_save`
--

CREATE TABLE `trip_save` (
  `user_id` int(11) NOT NULL,
  `trip_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_save`
--

INSERT INTO `trip_save` (`user_id`, `trip_id`, `saved_at`) VALUES
(3, 9, '2026-05-23 19:55:55'),
(3, 10, '2026-05-23 19:55:54'),
(3, 14, '2026-05-23 19:55:54'),
(8, 10, '2026-06-13 15:09:33'),
(8, 16, '2026-06-13 15:09:34');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `type_id` int(11) NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `email`, `type_id`) VALUES
(1, 'admin', '12345678', NULL, 1),
(3, 'marko', 'marko123', '', 3),
(4, 'jovan', '12345678', '', 3),
(5, 'moderator', '12345678', NULL, 2),
(6, 'milena', 'milena123', 'milena@gmail.com', 3),
(7, 'milan', 'milan123', 'milan@gmail.com', 3),
(8, 'djole', '12345678', 'djordjekrgovic4@gmail.com', 3);

-- --------------------------------------------------------

--
-- Table structure for table `user_type`
--

CREATE TABLE `user_type` (
  `id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_type`
--

INSERT INTO `user_type` (`id`, `type`) VALUES
(1, 'admin'),
(2, 'moderator'),
(3, 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_ibfk_1` (`trip_id`);

--
-- Indexes for table `destination`
--
ALTER TABLE `destination`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `review_ibfk_2` (`trip_id`);

--
-- Indexes for table `trip`
--
ALTER TABLE `trip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `trip_destination`
--
ALTER TABLE `trip_destination`
  ADD PRIMARY KEY (`trip_id`,`destination_id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `trip_photo`
--
ALTER TABLE `trip_photo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_photo_ibfk_1` (`trip_id`);

--
-- Indexes for table `trip_save`
--
ALTER TABLE `trip_save`
  ADD PRIMARY KEY (`user_id`,`trip_id`),
  ADD KEY `trip_save_ibfk_2` (`trip_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_ibfk_1` (`type_id`);

--
-- Indexes for table `user_type`
--
ALTER TABLE `user_type`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity`
--
ALTER TABLE `activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `destination`
--
ALTER TABLE `destination`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `trip`
--
ALTER TABLE `trip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `trip_photo`
--
ALTER TABLE `trip_photo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_type`
--
ALTER TABLE `user_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity`
--
ALTER TABLE `activity`
  ADD CONSTRAINT `activity_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trip` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trip` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trip`
--
ALTER TABLE `trip`
  ADD CONSTRAINT `trip_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `trip_ibfk_2` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`id`);

--
-- Constraints for table `trip_destination`
--
ALTER TABLE `trip_destination`
  ADD CONSTRAINT `trip_destination_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trip` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trip_destination_ibfk_2` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trip_photo`
--
ALTER TABLE `trip_photo`
  ADD CONSTRAINT `trip_photo_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trip` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trip_save`
--
ALTER TABLE `trip_save`
  ADD CONSTRAINT `trip_save_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trip_save_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `trip` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `user_type` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
