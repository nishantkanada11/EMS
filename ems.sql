-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2025 at 03:26 PM
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
(21, 'ascascas', 'sdfasfasfasfasf', 20, 'pending', '2025-09-24', '2025-09-27', '2025-09-23 08:00:24'),
(22, 'asdajsd', 'jskadas', 9, 'pending', '2025-09-26', '2025-10-03', '2025-09-23 08:09:48'),
(53, 'sakjdsad', 'oisadasd', 35, 'ongoing', '2025-09-25', '2025-09-28', '2025-09-23 11:55:54'),
(54, 'kcasca', 'ojaxac', 48, 'pending', '2025-09-27', '2025-10-04', '2025-09-23 11:56:20'),
(55, 'sdfasfasdfasf', 'asdfas', 27, 'pending', '2025-09-25', '2025-09-26', '2025-09-23 13:20:29');

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
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `password`, `role`, `department`, `created_at`, `tl_id`, `profile_pic`) VALUES
(1, 'Admin User', 'admin@example.com', '987654321121', '$2y$10$VcxDZfbgZMVrVpIosybPfuuLuMv/abs60IS57BRPfg.bOKONmXS2G', 'admin', 'Management', '2025-09-17 12:27:33', NULL, NULL),
(9, 'sdfgdfsdf', 'nishantkfsdanada9asdasfas@gmail.com', '0942269495', '$2y$10$nSp3LpCFupM1cYIi0S3UveErGtBzGeTAMxeDhT6TGLb/PxOg4MO66', 'tl', 'asdaf', '2025-09-19 08:58:37', 15, NULL),
(12, 'Nishant', 'emp2@example.com', '9429269695', '$2y$10$p3avNFT8rMuCnIy9jJ7jAON8Bf7ELdbGVPJcfPINMxwmxTjzd.fbW', 'employee', 'asdfasfsa', '2025-09-20 05:37:17', NULL, NULL),
(20, 'Nis', 'nishantkanasdfsdda9@gmail.com', '3429239423', '$2y$10$T10WZWx/mQIg7qaY83aUi.IvT97fUjNtroTFa/okrMLwdsbCt1wLW', 'employee', 'dgsdfgs', '2025-09-22 05:20:48', 35, NULL),
(27, 'sdrgss', 'nishanwr64569@gmail.com', '9873457210', '$2y$10$ICEN.z3eSTCQTyrMGDRD1uogk5yX2.vHQ1dSrVf94TKyuDsFexHH2', 'employee', 'dfgdsg', '2025-09-23 11:20:35', NULL, NULL),
(30, 'Srtetrr', 'aretweada9@gmail.com', '98465559876', '$2y$10$ICEN.z3eSTCQTyrMGDRD1uogk5yX2.vHQ1dSrVf94TKyuDsFexHH2', 'employee', 'dfgsdgsf', '2025-09-23 11:20:35', 35, NULL),
(35, 'cxxcsdf', 'rsdxzcva@example.com', '984352590', '$2y$10$ICEN.z3eSTCQTyrMGDRD1uogk5yX2.vHQ1dSrVf94TKyuDsFexHH2', 'tl', 'asder', '2025-09-23 11:20:35', NULL, NULL),
(44, 'ertweh', 'fsgfsds@tgmail.com', '9876445658', '$2y$10$ICEN.z3eSTCQTyrMGDRD1uogk5yX2.vHQ1dSrVf94TKyuDsFexHH2', 'employee', 'fgsgs', '2025-09-23 11:20:35', NULL, NULL),
(48, 'Nishasdf', 'nishantksdfasfanada9@gmail.com', '98569876', '$2y$10$ICEN.z3eSTCQTyrMGDRD1uogk5yX2.vHQ1dSrVf94TKyuDsFexHH2', 'employee', 'zxfgsdg', '2025-09-23 11:20:35', 35, NULL),
(49, 'Nishant Kanada', 'nishantkzfdgsdfgsdanada9@gmail.com', '95463463456', '$2y$10$ICEN.z3eSTCQTyrMGDRD1uogk5yX2.vHQ1dSrVf94TKyuDsFexHH2', 'employee', 'sdfafwa', '2025-09-23 11:20:35', NULL, NULL),
(50, 'Nishant Kanada', 'nishantkanadasdfasda0@gmail.com', '09429349495', '$2y$10$0QAqGwvouv0/kLVnU0s2I.6tXw4wKBSWoFtQkSBYta0h7aNRl.5Eq', 'employee', 'sdfsf', '2025-09-23 13:13:26', NULL, NULL),
(52, 'sadfasdf', 'nishantkanada0@gmail.com', '0943439495', '$2y$10$oId7OMolvb/Ri5aPtgNmse9fk9zxcZ1bQZ5JrPx2YMLduNOnpXmVi', 'employee', 'sdfasdf', '2025-09-23 13:24:12', NULL, NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

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
