-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2025 at 02:39 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ems`
--

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` enum('pending','ongoing','completed') NOT NULL DEFAULT 'pending',
  `start_date` date NOT NULL,
  `due_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `status`, `start_date`, `due_date`, `created_at`) VALUES
(66, 'cmaca,mc', 'zx,mxczxmcwffkjs', 58, 'completed', '2025-09-29', '2025-10-02', '2025-09-28 06:18:00'),
(67, 'jscjsdv', 'jdcjds', 49, 'completed', '2025-09-29', '2025-09-30', '2025-09-28 12:41:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','tl','employee') NOT NULL DEFAULT 'employee',
  `department` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tl_id` int(11) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `password`, `role`, `department`, `created_at`, `tl_id`, `profile_image`) VALUES
(1, 'Admin', 'admin@example.com', '987654321121', '$2y$10$VKA4pL/eZEd28LBtbZRxHOjDgtQ.tos0v9t7UzL7ps9eKCZb3A2zW', 'admin', 'Managementtt', '2025-09-17 12:27:33', NULL, 'profile_1759212729.jpeg'),
(9, 'sddfcjsdsdcc', 'nishantkfsdanada9asdasfas@gmail.com', '0942269495', '$2y$10$nSp3LpCFupM1cYIi0S3UveErGtBzGeTAMxeDhT6TGLb/PxOg4MO66', 'tl', 'asdaffff', '2025-09-19 08:58:37', 15, 'profile_1759212632.PNG'),
(49, 'Nishant Kana', 'nishantkzfdgsdfgsdanada9@gmail.com', '95463463456', '$2y$10$ICEN.z3eSTCQTyrMGDRD1uogk5yX2.vHQ1dSrVf94TKyuDsFexHH2', 'employee', 'sdfafwa', '2025-09-23 11:20:35', NULL, 'default.png'),
(53, 'ksda', 'admiasdjdn@example.com', '523432523423', '$2y$10$AalEo2XUxvqAgKkBksuC.OFy0CTp39XHIQI6LkZJs/pWDViBhsOe2', 'tl', 'sdafopsaf', '2025-09-25 07:12:32', 9, 'default.png'),
(54, 'xmcc,', 'adminlkzxcc@example.com', '9429223269495', '$2y$10$gYkCNvGdROCkyntMuM/mzOyA/dD8dHXcyKIK5LkBoo4ztOiMDlQYO', 'employee', ',mxzczx', '2025-09-25 07:34:26', 9, 'default.png'),
(56, 'Nishcd', 'nishasdfsdntkanada9@gmail.com', '09434269495', '$2y$10$hfupKrJdG1IWBtPX86r5tOgttkDamsyz7yBzmdJ6JPWm307pLOVxW', 'employee', 'dfwefkwfwlkzxsd', '2025-09-27 07:01:18', 9, 'profile_1759213612.jpeg'),
(57, 'sdfsdsdfsdd', 'nishantkansdfgdsgsdfgada9@gmail.com', '09569269495', '$2y$10$saq02ifEpYeAgQLkX2bgROZOqhN2F2dAtdZTX5BnGBCxijZEVFHjy', 'employee', 'bcvbvbb', '2025-09-28 06:07:44', NULL, 'default.png'),
(58, 'Nishant Kanada', 'nishantkaasdasdasnada9@gmail.com', '094292623239495', '$2y$10$n89Uh26wN/NUaWN/KsU5r.t1t./zl8tstyFgs/Gn7V9ejSrGPDNpm', 'employee', 'asdasda', '2025-09-28 06:43:26', NULL, 'default.png'),
(59, 'Nishant Kanada', 'nishantkanada0@gmail.com', '09429239495', '$2y$10$zhEGIGCpppdiH6vfXUUPjOu4On.a8iOFCeecg4D3f218od2Ju0Nya', 'tl', 'sadasjcas', '2025-09-28 12:37:02', NULL, 'default.png'),
(60, 'mxcx', 'nishantkanadclssldsda9@gmail.com', '09429434269495', '$2y$10$k1AlVcC0y5wGGu.UTwXfBeqvluh/B2L5zACsCurkx5wzVBW9PDylm', 'tl', 'lzvzv', '2025-09-29 07:35:38', NULL, 'default.png'),
(61, 'sdfisafiasdf', 'vatsalgames07@gmail.com', '09412129495', '$2y$10$bL/A84ijz1/YzQKaJZ3oLOLlaSl57rsaOOcv5wj2vDbh8AiPC3Pzm', 'tl', 'ksdflksdfs', '2025-09-29 08:13:02', NULL, 'default.png'),
(62, 'Nishant Kanada', 'nishantkhdcdsanada9@gmail.com', '0942926489495', '$2y$10$/rfvSL.vtGh2P8kBDJde2O3kNoI1cwTZctq8eH499P8kjCAj2eVgu', 'employee', 'sjcsdkc', '2025-09-29 13:34:01', NULL, 'profile_1759152841.jpg'),
(63, 'Nishant', 'nishasdfsdsdnt@gmail.com', '523434523423', '$2y$10$oLcq5zMezq8yldFXyCUop.VNVAjIe5UOSuHzFOoeGSYx1i.nUlRQO', 'employee', 'asdfasdfa', '2025-09-30 05:34:29', NULL, NULL),
(64, 'Nishant', 'priya.kjscasmehta@gmail.com', '9876542321121', '$2y$10$e2bUg5b.PS6MDeRQJRrihOoyQtdNITXibGnnW/zNU3GO2jgqnQvTK', 'employee', 'kjasasda', '2025-09-30 05:49:15', NULL, NULL),
(65, 'kdcsdf', 'nishkaclsant@gmail.com', '9429269445', '$2y$10$AJYiEHjy7d2HNm6jOGGsCOjjdqJZt/VqKKhz8qoBeYp1V39xXVt.W', 'employee', 'etertewtwe', '2025-09-30 05:59:09', NULL, 'profile_1759211981.PNG'),
(66, 'asdasd', 'nishasadasqwdaant@gmail.com', '942926949532', '$2y$10$H6.zMqrRq9hIvVr4J0qQGe6JDRN8TdKG.9zL0zh7cfCe8JjY.3hcG', 'employee', 'asasas', '2025-09-30 06:00:13', NULL, 'profile_1759212013.PNG'),
(67, 'Nishant', 'emacp1@example.comasdasd', '942923469495', '$2y$10$.Q7P2ORj9rUn.AmUGR5H/uquuruO0pSrdr0dCZ76M5brf6rBeNu0i', 'employee', 'dsfs', '2025-09-30 06:09:54', NULL, 'profile_1759212678.PNG'),
(68, 'Nishant', 'nishacccdaant@gmail.com', '52345234423', '$2y$10$e12Q4pMNrOQojl3Ip7dvDOE1jDd2NbtBBchvx82v0b0Ve7JITvaTu', 'employee', 'ascasca', '2025-09-30 06:28:28', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
