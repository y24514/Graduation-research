-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2026-01-22 02:20:31
-- サーバのバージョン： 10.4.32-MariaDB
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `sportsdata`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `admin_role_requests`
--

CREATE TABLE `admin_role_requests` (
  `id` int(11) NOT NULL,
  `group_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `actioned_by` varchar(50) DEFAULT NULL,
  `actioned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `admin_role_requests`
--

INSERT INTO `admin_role_requests` (`id`, `group_id`, `user_id`, `name`, `requested_at`, `status`, `actioned_by`, `actioned_at`) VALUES
(1, 'cis', 'mainte', 'mainte', '2026-01-14 10:34:31', 'approved', 'host', '2026-01-14 10:36:00'),
(2, 'cis', 'harunaabe', '顧問の安倍', '2026-01-14 11:24:09', 'approved', 'host', '2026-01-15 14:45:03'),
(3, '花巻東水泳部', 'hanatou', 'host', '2026-01-19 11:49:42', 'approved', 'host', '2026-01-19 11:51:05');

-- --------------------------------------------------------

--
-- テーブルの構造 `basketball_strategies`
--

CREATE TABLE `basketball_strategies` (
  `id` int(11) NOT NULL,
  `group_id` varchar(64) DEFAULT NULL,
  `user_id` varchar(64) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `json_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `calendar_tbl`
--

CREATE TABLE `calendar_tbl` (
  `id` int(100) NOT NULL,
  `group_id` varchar(100) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `memo` varchar(100) NOT NULL,
  `startdate` date NOT NULL,
  `enddate` date NOT NULL,
  `is_shared` tinyint(1) NOT NULL DEFAULT 0,
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `calendar_tbl`
--

INSERT INTO `calendar_tbl` (`id`, `group_id`, `user_id`, `title`, `memo`, `startdate`, `enddate`, `is_shared`, `create_at`) VALUES
(6, 'cis', 'abe', 'あああ', 'あああ', '2025-12-03', '2025-12-04', 0, '2025-12-03 14:42:20'),
(8, 'cis', 'y24514', '合宿', 'きつい', '2025-12-09', '2025-12-12', 0, '2025-12-16 09:33:47'),
(9, 'cis', 'y24514', '遠征', '', '2025-12-20', '2025-12-22', 0, '2025-12-23 08:57:16'),
(10, 'cis', 'y24514', '年末 off', '', '2025-12-29', '2026-01-01', 0, '2025-12-23 08:57:34'),
(11, 'cis', 'mainte', '遠征', '', '2026-01-15', '2026-01-20', 1, '2026-01-15 14:47:31'),
(12, 'cis', 'mainte', 'テスト', '', '2026-01-28', '2026-02-01', 1, '2026-01-19 10:32:21');

-- --------------------------------------------------------

--
-- テーブルの構造 `chat_group_member_tbl`
--

CREATE TABLE `chat_group_member_tbl` (
  `id` int(11) NOT NULL,
  `chat_group_id` int(11) NOT NULL,
  `group_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `chat_group_tbl`
--

CREATE TABLE `chat_group_tbl` (
  `chat_group_id` int(11) NOT NULL,
  `group_id` varchar(50) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_description` text DEFAULT NULL,
  `created_by` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `chat_read_status_tbl`
--

CREATE TABLE `chat_read_status_tbl` (
  `id` int(11) NOT NULL,
  `group_id` varchar(100) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `chat_type` enum('direct','group') NOT NULL,
  `chat_group_id` int(11) DEFAULT NULL,
  `recipient_id` varchar(50) DEFAULT NULL,
  `last_read_message_id` int(11) DEFAULT NULL,
  `last_read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `chat_read_status_tbl`
--

INSERT INTO `chat_read_status_tbl` (`id`, `group_id`, `user_id`, `chat_type`, `chat_group_id`, `recipient_id`, `last_read_message_id`, `last_read_at`) VALUES
(1, 'cis', 'y24514', 'direct', NULL, 'y24520', 11, '2025-12-22 15:34:51'),
(2, 'cis', 'y24514', 'direct', NULL, 'y24515', 6, '2025-12-22 15:34:52'),
(3, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2025-12-22 15:34:53'),
(4, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-22 15:51:57'),
(5, 'cis', 'y24514', 'direct', NULL, 'y24515', 6, '2025-12-22 15:51:58'),
(6, 'cis', 'y24514', 'direct', NULL, 'y24520', 11, '2025-12-22 15:51:59'),
(7, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2025-12-22 15:51:59'),
(8, 'cis', 'y24514', 'direct', NULL, 'y24520', 11, '2025-12-22 15:53:09'),
(9, 'cis', 'y24514', 'direct', NULL, 'y24515', 6, '2025-12-22 16:00:44'),
(10, 'cis', 'y24514', 'direct', NULL, 'y24520', 11, '2025-12-22 16:00:45'),
(11, 'cis', 'y24514', 'direct', NULL, 'y24515', 6, '2025-12-22 16:34:09'),
(12, 'cis', 'y24514', 'direct', NULL, 'y24520', 11, '2025-12-22 16:34:35'),
(16, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-22 16:39:39'),
(17, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-22 16:39:40'),
(18, 'cis', 'y24514', 'direct', NULL, 'y24515', 6, '2025-12-22 16:39:41'),
(19, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-22 16:40:21'),
(22, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-22 16:41:19'),
(23, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-22 16:41:25'),
(24, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-22 16:41:26'),
(25, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 08:56:47'),
(26, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 08:58:13'),
(27, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 08:58:21'),
(28, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 08:58:24'),
(29, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 08:58:57'),
(30, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 09:00:41'),
(31, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 09:00:42'),
(32, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 09:06:24'),
(33, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 09:07:40'),
(34, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 09:09:25'),
(35, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 09:09:30'),
(36, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 09:09:39'),
(37, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 09:09:42'),
(38, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 09:09:44'),
(39, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-23 09:09:47'),
(46, 'cis', 'y24514', 'direct', NULL, 'y24515', 16, '2025-12-23 09:18:49'),
(47, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 10:02:06'),
(48, 'cis', 'y24514', 'direct', NULL, 'y24515', 16, '2025-12-23 10:02:07'),
(49, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-23 10:02:08'),
(50, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-23 10:02:09'),
(51, 'cis', 'y24514', 'direct', NULL, 'y24515', 16, '2025-12-23 10:02:09'),
(52, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 10:02:12'),
(53, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 10:04:01'),
(58, 'cis', 'y24520', 'direct', NULL, 'y24514', 12, '2025-12-23 10:08:17'),
(66, 'cis', 'y24520', 'direct', NULL, 'y24514', 20, '2025-12-23 10:09:51'),
(67, 'cis', 'y24520', 'direct', NULL, 'y24514', 20, '2025-12-23 10:09:53'),
(68, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 10:11:00'),
(71, 'cis', 'y24514', 'direct', NULL, 'y24520', 20, '2025-12-23 10:13:49'),
(74, 'cis', 'y24520', 'direct', NULL, 'y24514', 20, '2025-12-23 10:18:42'),
(75, 'cis', 'y24520', 'direct', NULL, 'y24514', 12, '2025-12-23 10:20:49'),
(76, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 10:21:17'),
(77, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 10:21:28'),
(80, 'cis', 'y24520', 'direct', NULL, 'y24514', 12, '2025-12-23 10:33:20'),
(81, 'cis', 'y24520', 'direct', NULL, 'y24514', 12, '2025-12-23 10:33:21'),
(86, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 11:24:49'),
(87, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 11:24:57'),
(88, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-23 11:27:48'),
(89, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 11:27:48'),
(90, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 11:27:49'),
(91, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 11:27:57'),
(92, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 11:27:58'),
(93, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 11:29:35'),
(94, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 11:29:37'),
(96, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 11:30:03'),
(97, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 11:31:15'),
(98, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 11:32:04'),
(99, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-23 11:32:58'),
(100, 'cis', 'y24520', 'direct', NULL, 'y24514', 12, '2025-12-23 11:45:53'),
(101, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 13:08:21'),
(102, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 13:08:23'),
(103, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-23 13:08:23'),
(104, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2025-12-23 13:08:24'),
(105, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 13:09:36'),
(106, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 13:09:37'),
(107, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 13:09:39'),
(108, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 13:09:40'),
(109, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2025-12-23 13:09:41'),
(110, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 13:09:45'),
(111, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-23 13:09:46'),
(112, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2025-12-23 13:09:47'),
(113, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2025-12-23 13:09:47'),
(114, 'cis', 'y24514', 'direct', NULL, 'y24515', 19, '2025-12-23 14:27:13'),
(115, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-23 14:27:39'),
(116, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2025-12-23 14:27:44'),
(117, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2025-12-23 14:27:50'),
(118, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2025-12-23 14:30:10'),
(119, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 16:07:12'),
(120, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-23 16:07:16'),
(121, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2025-12-24 09:03:07'),
(122, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-24 09:03:08'),
(123, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2025-12-24 09:03:09'),
(124, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-24 09:03:22'),
(125, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2025-12-24 09:03:31'),
(126, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2025-12-24 09:11:16'),
(127, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-24 09:11:19'),
(128, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2025-12-24 09:11:20'),
(129, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-24 10:12:55'),
(130, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2025-12-24 10:12:57'),
(131, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-24 10:12:58'),
(132, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2025-12-24 10:33:41'),
(133, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-24 11:04:38'),
(134, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2025-12-24 11:04:39'),
(135, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2025-12-24 11:11:47'),
(136, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-24 11:11:48'),
(137, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-24 11:11:49'),
(138, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2025-12-24 11:11:49'),
(139, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-24 11:11:50'),
(140, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2026-01-07 09:03:33'),
(141, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2026-01-07 09:03:41'),
(142, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2026-01-07 09:03:50'),
(143, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2026-01-07 09:26:04'),
(144, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2026-01-07 09:26:07'),
(145, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2026-01-07 09:26:08'),
(146, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2026-01-07 09:26:09'),
(147, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2026-01-07 09:26:10'),
(148, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2026-01-07 11:59:00'),
(149, 'cis', 'abe', 'direct', NULL, 'y24520', 26, '2026-01-08 10:02:19'),
(150, 'cis', 'abe', 'direct', NULL, 'y24510', 27, '2026-01-08 10:02:20'),
(151, 'cis', 'abe', 'direct', NULL, 'y24514', 25, '2026-01-08 10:02:22'),
(152, 'cis', 'abe', 'direct', NULL, 'y24514', 25, '2026-01-08 10:02:23'),
(153, 'cis', 'abe', 'direct', NULL, 'y24510', 27, '2026-01-08 10:08:35'),
(154, 'cis', 'abe', 'direct', NULL, 'y24520', 26, '2026-01-08 10:08:35'),
(155, 'cis', 'abe', 'direct', NULL, 'y24514', 25, '2026-01-08 10:08:37'),
(156, 'cis', 'abe', 'direct', NULL, 'y24514', 25, '2026-01-08 10:08:38'),
(157, 'cis', 'abe', 'direct', NULL, 'y24514', 25, '2026-01-08 10:08:39'),
(158, 'cis', 'y24514', 'direct', NULL, 'abe', 25, '2026-01-08 11:37:01'),
(159, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-08 11:47:50'),
(160, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2026-01-08 11:47:57'),
(161, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-08 11:48:08'),
(162, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2026-01-08 13:10:19'),
(163, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-08 13:10:23'),
(164, 'cis', 'y24514', 'direct', NULL, 'y24515', 24, '2026-01-08 13:10:32'),
(166, 'cis', 'y24514', 'direct', NULL, 'y24515', 31, '2026-01-08 13:11:50'),
(167, 'cis', 'abe', 'direct', NULL, 'y24514', 29, '2026-01-08 13:32:59'),
(168, 'cis', 'abe', 'direct', NULL, 'y24514', 29, '2026-01-08 13:33:01'),
(169, 'cis', 'abe', 'direct', NULL, 'y24520', 26, '2026-01-08 13:33:02'),
(170, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-13 10:36:34'),
(171, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-13 11:13:05'),
(172, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2026-01-13 11:13:11'),
(173, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-13 11:24:07'),
(174, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2026-01-13 11:24:08'),
(175, 'cis', 'y24514', 'direct', NULL, 'y24515', 31, '2026-01-13 11:24:10'),
(176, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2026-01-13 11:24:11'),
(177, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 7, '2026-01-13 11:24:12'),
(178, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-13 11:24:12'),
(179, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-13 11:31:28'),
(180, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-13 11:38:25'),
(182, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-14 09:55:05'),
(183, 'cis', 'y24514', 'direct', NULL, 'y24514_3', 32, '2026-01-14 10:32:33'),
(184, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-14 11:41:11'),
(185, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-14 11:41:19'),
(186, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-16 10:10:22'),
(187, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-16 10:11:26'),
(188, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-16 13:55:05'),
(189, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2026-01-19 09:38:30'),
(190, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-20 14:02:10'),
(191, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-20 14:02:17'),
(192, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-21 09:52:21'),
(193, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-21 09:52:22'),
(194, 'cis', 'y24514', 'direct', NULL, 'abe', 29, '2026-01-21 09:52:23');

-- --------------------------------------------------------

--
-- テーブルの構造 `chat_tbl`
--

CREATE TABLE `chat_tbl` (
  `id` int(11) NOT NULL,
  `group_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `chat_type` enum('group','direct') DEFAULT 'group',
  `chat_group_id` int(11) DEFAULT NULL,
  `recipient_id` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `chat_tbl`
--

INSERT INTO `chat_tbl` (`id`, `group_id`, `user_id`, `chat_type`, `chat_group_id`, `recipient_id`, `message`, `image_path`, `image_name`, `is_deleted`, `deleted_at`, `created_at`) VALUES
(1, 'cis', 'y24514', 'direct', NULL, 'y24513', 'こんにちは', NULL, NULL, 1, '2025-12-23 11:33:03', '2025-12-22 10:16:00'),
(2, 'cis', 'y24514_2', 'direct', NULL, 'y24514', 'こんちゃｔ', NULL, NULL, 0, NULL, '2025-12-22 10:20:25'),
(3, 'cis', 'y24514_2', 'direct', NULL, 'y24514', 'こんちゃｔ', NULL, NULL, 0, NULL, '2025-12-22 10:20:34'),
(4, 'cis', 'y24514_2', 'direct', NULL, 'y24514', 'こんにちは', NULL, NULL, 0, NULL, '2025-12-22 10:53:53'),
(5, 'cis', 'y24515', 'direct', NULL, 'y24514', 'こんにちは', NULL, NULL, 1, '2026-01-19 14:30:04', '2025-12-22 11:00:20'),
(6, 'cis', 'y24514', 'direct', NULL, 'y24515', 'こんにちは', NULL, NULL, 0, NULL, '2025-12-22 11:02:08'),
(7, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 'こんにちは', NULL, NULL, 0, NULL, '2025-12-22 14:20:15'),
(8, 'cis', 'y24520', 'direct', NULL, 'y24514', '今週試合いつだっけ？？', NULL, NULL, 0, NULL, '2025-12-22 15:24:45'),
(9, 'cis', 'y24514', 'direct', NULL, 'y24520', '今週の土曜日', NULL, NULL, 1, '2025-12-23 11:24:52', '2025-12-22 15:25:12'),
(10, 'cis', 'y24520', 'direct', NULL, 'y24514', 'ありがとう', NULL, NULL, 0, NULL, '2025-12-22 15:28:55'),
(11, 'cis', 'y24514', 'direct', NULL, 'y24520', 'はーい', NULL, NULL, 1, '2025-12-23 11:29:41', '2025-12-22 15:30:50'),
(12, 'cis', 'y24514', 'direct', NULL, 'y24520', '間に合う？？', NULL, NULL, 1, '2025-12-23 11:27:55', '2025-12-22 16:34:57'),
(13, 'cis', 'y24514', 'direct', NULL, 'y24515', 'どうも', NULL, NULL, 1, '2025-12-23 11:32:11', '2025-12-22 16:39:48'),
(14, 'cis', 'y24515', 'direct', NULL, 'y24514', 'ぺこぺこ', NULL, NULL, 1, '2026-01-19 14:30:04', '2025-12-22 16:40:04'),
(16, 'cis', 'y24515', 'direct', NULL, 'y24514', '楽しいね', NULL, NULL, 1, '2026-01-19 14:30:04', '2025-12-23 09:18:30'),
(19, 'cis', 'y24515', 'direct', NULL, 'y24514', 'たのしいなあああ！？', NULL, NULL, 1, '2026-01-19 14:30:04', '2025-12-23 10:08:44'),
(24, 'cis', 'y24514', 'direct', NULL, 'y24515', '楽しいなぁぁぁああああ', NULL, NULL, 1, '2025-12-24 09:03:17', '2025-12-23 14:27:25'),
(25, 'cis', 'abe', 'direct', NULL, 'y24514', 'こんにちは！卒研進んでいますか？', NULL, NULL, 0, NULL, '2026-01-08 10:01:58'),
(26, 'cis', 'abe', 'direct', NULL, 'y24520', 'こんにちは！卒研進んでいますか？', NULL, NULL, 0, NULL, '2026-01-08 10:02:02'),
(27, 'cis', 'abe', 'direct', NULL, 'y24510', '体調は大丈夫ですか', NULL, NULL, 0, NULL, '2026-01-08 10:02:16'),
(28, 'cis', 'y24514', 'direct', NULL, 'abe', '送れています。', NULL, NULL, 1, '2026-01-08 11:37:34', '2026-01-08 11:37:26'),
(29, 'cis', 'y24514', 'direct', NULL, 'abe', '遅れている状況です', NULL, NULL, 0, NULL, '2026-01-08 11:37:48'),
(30, 'cis', 'y24514', 'direct', NULL, 'y24515', 'テスト　送信', NULL, NULL, 0, NULL, '2026-01-08 13:10:40'),
(31, 'cis', 'y24515', 'direct', NULL, 'y24514', 'テスト送信確認', NULL, NULL, 1, '2026-01-19 14:30:04', '2026-01-08 13:11:20'),
(32, 'cis', 'y24514_3', 'direct', NULL, 'y24514', 'ばーか', NULL, NULL, 0, NULL, '2026-01-14 10:31:32');

-- --------------------------------------------------------

--
-- テーブルの構造 `diary_tbl`
--

CREATE TABLE `diary_tbl` (
  `id` int(11) NOT NULL,
  `group_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `diary_date` date NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `content` text NOT NULL,
  `submitted_to_admin` tinyint(1) NOT NULL DEFAULT 0,
  `submitted_at` datetime DEFAULT NULL,
  `admin_feedback` text DEFAULT NULL,
  `admin_feedback_at` datetime DEFAULT NULL,
  `admin_feedback_by_user_id` varchar(255) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `diary_tbl`
--

INSERT INTO `diary_tbl` (`id`, `group_id`, `user_id`, `diary_date`, `title`, `content`, `submitted_to_admin`, `submitted_at`, `admin_feedback`, `admin_feedback_at`, `admin_feedback_by_user_id`, `tags`, `created_at`, `updated_at`) VALUES
(4, 'cis', 'abe', '2026-01-08', 'おれはジャイアン', 'ガキ大将', 0, NULL, NULL, NULL, NULL, '#じゃいあん', '2026-01-08 10:02:47', '2026-01-08 10:02:47'),
(10, 'cis', 'abe', '2026-01-07', '昨日のこと', '昨日のこと書くの忘れてた', 0, NULL, NULL, NULL, NULL, '#練習', '2026-01-08 10:06:25', '2026-01-08 10:06:25'),
(11, 'cis', 'abe', '2026-01-01', 'あけましておめでとうございます', '今年もがんばるぞ', 0, NULL, NULL, NULL, NULL, '#意気込み', '2026-01-08 10:06:52', '2026-01-08 10:06:52'),
(12, 'cis', 'abe', '2025-12-25', 'クリスマス', 'クリスマスも練習だ！がんばるぞ！', 0, NULL, NULL, NULL, NULL, '#練習', '2026-01-08 10:07:19', '2026-01-08 10:07:19'),
(21, 'cis', 'harunaabe', '2026-01-14', 'おれはジャイアン', 'a', 0, NULL, NULL, NULL, NULL, '', '2026-01-14 11:41:34', '2026-01-14 11:41:34'),
(22, 'cis', 'y24514', '2026-01-15', '合宿', '合宿にいきました', 1, '2026-01-21 09:12:00', 'よく頑張りました', '2026-01-16 10:04:02', 'mainte', '', '2026-01-15 12:51:59', '2026-01-21 09:12:00'),
(23, 'cis', 'y24514', '2026-01-15', '遠征', 'テスト', 1, '2026-01-15 13:30:31', 'よく頑張りました', '2026-01-16 10:04:01', 'mainte', '', '2026-01-15 13:30:27', '2026-01-16 10:04:01');

-- --------------------------------------------------------

--
-- テーブルの構造 `games`
--

CREATE TABLE `games` (
  `id` bigint(20) NOT NULL,
  `team_a_name` varchar(100) NOT NULL,
  `team_b_name` varchar(100) NOT NULL,
  `score_a` int(11) NOT NULL DEFAULT 0,
  `score_b` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `group_id` varchar(64) DEFAULT NULL,
  `saved_by_user_id` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `game_actions`
--

CREATE TABLE `game_actions` (
  `id` bigint(20) NOT NULL,
  `game_id` bigint(20) NOT NULL,
  `quarter` tinyint(4) NOT NULL,
  `team` char(1) NOT NULL,
  `player_id` int(11) NOT NULL,
  `player_name` varchar(100) NOT NULL,
  `action_type` varchar(20) NOT NULL,
  `point` tinyint(4) NOT NULL DEFAULT 0,
  `result` varchar(20) NOT NULL DEFAULT 'success',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- テーブルの構造 `goal_tbl`
--

CREATE TABLE `goal_tbl` (
  `goal_id` int(100) NOT NULL,
  `group_id` varchar(50) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `goal` text NOT NULL,
  `progress` int(3) DEFAULT 0 COMMENT '進捗率(0-100)',
  `deadline` date DEFAULT NULL COMMENT '達成期限',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `goal_tbl`
--

INSERT INTO `goal_tbl` (`goal_id`, `group_id`, `user_id`, `goal`, `progress`, `deadline`, `created_at`) VALUES
(5, 'sangitan', 'h-abe', '120kgになる', 0, NULL, '2025-12-03 13:47:46'),
(6, 'cis', 'abe', '', 0, NULL, '2025-12-03 14:15:07'),
(7, 'cis', 'y24514', '半フリ55秒切る', 100, NULL, '2026-01-07 08:53:28'),
(8, 'cis', 'abe', '県民体ベスト４', 0, NULL, '2026-01-08 14:03:44');

-- --------------------------------------------------------

--
-- テーブルの構造 `inquiries_tbl`
--

CREATE TABLE `inquiries_tbl` (
  `id` int(11) NOT NULL,
  `group_id` varchar(100) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `category` varchar(20) NOT NULL,
  `subject` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:未対応 1:返信済み',
  `response` text DEFAULT NULL,
  `responded_by_user_id` varchar(50) DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `inquiries_tbl`
--

INSERT INTO `inquiries_tbl` (`id`, `group_id`, `user_id`, `category`, `subject`, `message`, `status`, `response`, `responded_by_user_id`, `responded_at`, `created_at`) VALUES
(1, 'cis', 'mainte', 'improve', 'テスト', 'テスト', 1, 'テスト', 'host', '2026-01-15 15:30:26', '2026-01-15 15:09:05'),
(2, 'cis', 'mainte', 'improve', 'テスト', 'ｈｒｈ', 1, 'テスト', 'host', '2026-01-19 11:51:17', '2026-01-15 15:50:15');

-- --------------------------------------------------------

--
-- テーブルの構造 `login_tbl`
--

CREATE TABLE `login_tbl` (
  `id` int(100) NOT NULL,
  `group_id` varchar(100) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(25) NOT NULL,
  `dob` date NOT NULL,
  `height` decimal(5,1) NOT NULL,
  `weight` decimal(5,1) NOT NULL,
  `position` varchar(50) NOT NULL,
  `sport` varchar(20) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `login_tbl`
--

INSERT INTO `login_tbl` (`id`, `group_id`, `user_id`, `password`, `name`, `dob`, `height`, `weight`, `position`, `sport`, `is_admin`, `is_super_admin`) VALUES
(13, 'cis', 'y24514', '$2y$10$MVyXrYFuMD/ULyZMNG40d.8yDyCa2FU4Ydm2EghYzPTmg2nj.lSYK', '藤原大輔', '2006-03-03', 170.0, 59.0, '学生会役員', NULL, 0, 0),
(18, 'sangitan', 'h-abe', '$2y$10$3077kKiMdxYuVABAokwcwOON0P5z5.psI.GWsh2ZlywJLrHYHJHwG', '安倍春菜', '2026-07-03', 190.0, 100.0, 'DF', NULL, 0, 0),
(20, 'cis', 'abe', '$2y$10$5rnhaBbn/ycTIjygFdip1utCRMa/G7oh2CKa.ocM2R8f9JqS82yGO', '剛田武', '2025-11-12', 200.0, 100.0, 'OF', 'all', 0, 0),
(26, 'system', 'host', '$2y$10$TlJs197Yt7J67hbxydxSYeHuyPNA5mPY4vZd0VyBFxuEYAC/n8K6u', 'host', '2000-01-01', 170.0, 60.0, '作成者', NULL, 1, 1),
(28, 'cis', 'mainte', '$2y$10$dHGgYNzX9wNr.Dz6blcEi.1VeQhwE7grRI8f8aXQrhd.k4trxzLZu', 'mainte', '1900-01-01', 0.0, 0.0, '管理者', 'all', 1, 0),
(29, 'cis', 'harunaabe', '$2y$10$e53yk0vW9wbeeE2axN76y.ffnIgu9uQ4TFqBdMnKfU2WYumTu9FHi', '顧問の安倍', '1900-01-01', 0.0, 0.0, '顧問', 'all', 1, 0),
(30, '花巻東水泳部', 'hanatou', '$2y$10$h7DRGtmHGUTHHshA9dF/Oe8BbAXWQVQavFOQkL2r5Tc7bRUsxRIra', 'host', '1900-01-01', 0.0, 0.0, '指導者', 'swim', 1, 0);

-- --------------------------------------------------------

--
-- テーブルの構造 `pi_tbl`
--

CREATE TABLE `pi_tbl` (
  `id` int(100) NOT NULL,
  `group_id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `height` decimal(4,1) NOT NULL,
  `weight` decimal(4,1) NOT NULL,
  `injury` varchar(100) NOT NULL,
  `sleeptime` time NOT NULL,
  `create_at` datetime NOT NULL,
  `body_fat` decimal(4,1) DEFAULT NULL COMMENT '体脂肪率(%)',
  `muscle_mass` decimal(4,1) DEFAULT NULL COMMENT '筋肉量(kg)',
  `target_weight` decimal(4,1) DEFAULT NULL COMMENT '目標体重(kg)',
  `memo` text DEFAULT NULL COMMENT 'メモ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `pi_tbl`
--

INSERT INTO `pi_tbl` (`id`, `group_id`, `user_id`, `height`, `weight`, `injury`, `sleeptime`, `create_at`, `body_fat`, `muscle_mass`, `target_weight`, `memo`) VALUES
(24, 'cis', 'y24514', 170.0, 59.6, '', '08:00:00', '2025-11-17 11:14:36', NULL, NULL, NULL, NULL),
(29, 'cis', 'y24514', 170.0, 59.8, '左腕骨折', '09:00:00', '2025-11-17 11:27:34', NULL, NULL, NULL, NULL),
(31, 'cis', 'y24514', 172.0, 64.6, '', '05:00:00', '2025-11-18 09:43:57', NULL, NULL, NULL, NULL),
(32, 'cis', 'abe', 190.0, 120.0, '', '05:00:00', '2025-12-03 13:55:10', NULL, NULL, NULL, NULL),
(33, 'cis', 'y24514', 172.0, 59.8, '', '05:00:00', '2025-12-23 14:29:21', NULL, NULL, NULL, NULL),
(34, 'cis', 'y24514', 170.3, 60.0, '', '06:00:00', '2025-12-24 09:11:57', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `players`
--

INSERT INTO `players` (`id`, `team_id`, `number`, `name`) VALUES
(1, 1, 4, 'A Player 4'),
(2, 1, 5, 'A Player 5'),
(3, 1, 6, 'A Player 6'),
(4, 1, 7, 'A Player 7'),
(5, 1, 8, 'A Player 8'),
(6, 1, 9, 'A Player 9'),
(7, 1, 10, 'A Player 10'),
(8, 1, 11, 'A Player 11'),
(9, 1, 12, 'A Player 12'),
(10, 1, 13, 'A Player 13'),
(11, 1, 14, 'A Player 14'),
(12, 1, 15, 'A Player 15'),
(13, 2, 4, 'B Player 4'),
(14, 2, 5, 'B Player 5'),
(15, 2, 6, 'B Player 6'),
(16, 2, 7, 'B Player 7'),
(17, 2, 8, 'B Player 8'),
(18, 2, 9, 'B Player 9'),
(19, 2, 10, 'B Player 10'),
(20, 2, 11, 'B Player 11'),
(21, 2, 12, 'B Player 12'),
(22, 2, 13, 'B Player 13'),
(23, 2, 14, 'B Player 14'),
(24, 2, 15, 'B Player 15');

-- --------------------------------------------------------

--
-- テーブルの構造 `swim_best_tbl`
--

CREATE TABLE `swim_best_tbl` (
  `id` int(11) NOT NULL,
  `group_id` varchar(50) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `pool` enum('short','long') NOT NULL,
  `event` varchar(10) NOT NULL,
  `distance` int(11) NOT NULL,
  `best_time` decimal(6,2) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `swim_best_tbl`
--

INSERT INTO `swim_best_tbl` (`id`, `group_id`, `user_id`, `pool`, `event`, `distance`, `best_time`, `updated_at`) VALUES
(1, 'cis', 'y24514', 'long', 'fly', 50, 28.00, '2025-12-15 02:00:18'),
(3, 'cis', 'y24514', 'long', 'fr', 50, 25.78, '2025-12-15 02:09:51');

-- --------------------------------------------------------

--
-- テーブルの構造 `swim_practice_tbl`
--

CREATE TABLE `swim_practice_tbl` (
  `id` int(11) NOT NULL,
  `group_id` varchar(100) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `practice_date` date NOT NULL,
  `title` varchar(100) NOT NULL,
  `menu_text` text DEFAULT NULL,
  `memo` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `swim_practice_tbl`
--

INSERT INTO `swim_practice_tbl` (`id`, `group_id`, `user_id`, `practice_date`, `title`, `menu_text`, `memo`, `created_at`) VALUES
(1, 'cis', 'harunaabe', '2026-01-14', '肺活量アップ', 'Main100＊5', '', '2026-01-14 11:37:49'),
(2, 'cis', 'harunaabe', '2026-01-14', '筋トレ', 'ランニング\r\n腹筋\r\n背筋', '', '2026-01-14 11:38:23'),
(4, 'cis', 'y24514', '2026-01-21', '50*20 Average', '=== Lane 1 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m)\r\n\r\n=== Lane 2 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m)\r\n\r\n=== Lane 3 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m)\r\n\r\n=== Lane 4 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m)\r\n\r\n=== Lane 5 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m)\r\n\r\n=== Lane 6 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m)', '', '2026-01-21 14:59:20'),
(5, 'cis', 'y24514', '2026-01-22', '50*20 Average', '=== Lane 1 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m) (1000m)\r\n\r\n=== Lane 2 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m) (1000m)\r\n\r\n=== Lane 3 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m) (1000m)\r\n\r\n=== Lane 4 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m) (1000m)\r\n\r\n=== Lane 5 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m) (1000m)\r\n\r\n=== Lane 6 ===\r\nW-up 400m x1 @6:00 rest:1:00 Choice [A1] (400m)\r\nPull 100m x4 @2:00 rest:1:00 Choice [EN1] (400m)\r\nMain 50m x20 @1:30 rest:1:00 Choice [Max] - Average (1000m) (1000m)', '', '2026-01-21 14:59:44');

-- --------------------------------------------------------

--
-- テーブルの構造 `swim_tbl`
--

CREATE TABLE `swim_tbl` (
  `id` int(11) NOT NULL,
  `group_id` varchar(100) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `swim_date` date DEFAULT NULL,
  `meet_name` varchar(100) DEFAULT NULL,
  `round` varchar(20) DEFAULT NULL,
  `condition` tinyint(4) DEFAULT NULL,
  `session_type` varchar(20) DEFAULT NULL,
  `pool` enum('short','long') NOT NULL,
  `event` enum('fly','ba','br','fr','im') NOT NULL,
  `distance` int(11) NOT NULL,
  `total_time` decimal(6,2) NOT NULL COMMENT '秒（例: 75.32）',
  `stroke_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`stroke_json`)),
  `lap_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`lap_json`)),
  `memo` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `swim_tbl`
--

INSERT INTO `swim_tbl` (`id`, `group_id`, `user_id`, `swim_date`, `meet_name`, `round`, `condition`, `session_type`, `pool`, `event`, `distance`, `total_time`, `stroke_json`, `lap_json`, `memo`, `created_at`) VALUES
(10, 'cis', 'y24514', NULL, NULL, NULL, NULL, NULL, 'long', 'fly', 50, 29.98, '{\"stroke_50\":10}', '{\"lap_time_50\":\"29.98\"}', NULL, '2025-12-15 02:03:12'),
(11, 'cis', 'y24514', NULL, NULL, NULL, NULL, NULL, 'long', 'fr', 50, 26.99, '{\"stroke_50\":15}', '{\"lap_time_50\":\"26.99\"}', NULL, '2025-12-15 02:04:27'),
(33, 'cis', 'y24514', NULL, NULL, NULL, NULL, NULL, 'long', 'fr', 50, 29.98, '{\"stroke_50\":8}', '{\"lap_time_50\":\"29.98\"}', NULL, '2025-12-15 03:17:29'),
(34, 'cis', 'y24514', NULL, NULL, NULL, NULL, NULL, 'long', 'fr', 50, 25.98, '{\"stroke_50\":22}', '{\"lap_time_50\":\"25.98\"}', NULL, '2025-12-15 03:18:13'),
(35, 'cis', 'y24514', NULL, NULL, NULL, NULL, NULL, 'long', 'fr', 50, 25.98, '{\"stroke_50\":22}', '{\"lap_time_50\":\"25.98\"}', NULL, '2025-12-15 03:21:00'),
(36, 'cis', 'y24514', NULL, NULL, NULL, NULL, NULL, 'long', 'fr', 50, 25.98, '{\"stroke_50\":22}', '{\"lap_time_50\":\"25.98\"}', NULL, '2025-12-15 03:22:54'),
(37, 'cis', 'y24514', NULL, NULL, NULL, NULL, NULL, 'long', 'fr', 50, 25.98, '{\"stroke_50\":22}', '{\"lap_time_50\":\"25.98\"}', NULL, '2025-12-15 03:24:08'),
(38, 'cis', 'y24514', '2025-12-17', NULL, NULL, 3, NULL, 'short', 'fly', 100, 65.81, '{\"stroke_25\":13,\"stroke_50\":14,\"stroke_75\":14,\"stroke_100\":16}', '{\"lap_time_25\":\"13.21\",\"lap_time_50\":\"15.22\",\"lap_time_75\":\"17.25\",\"lap_time_100\":\"20.13\"}', '', '2025-12-17 00:44:56'),
(39, 'cis', 'y24514', '2025-12-18', NULL, NULL, 5, NULL, 'short', 'fly', 100, 62.96, '{\"stroke_25\":16,\"stroke_50\":15,\"stroke_75\":15,\"stroke_100\":16}', '{\"lap_time_25\":\"13.21\",\"lap_time_50\":\"15.55\",\"lap_time_75\":\"14.98\",\"lap_time_100\":\"19.22\"}', '', '2025-12-17 00:46:15'),
(40, 'cis', 'y24514', '2025-12-18', NULL, NULL, 5, NULL, 'short', 'fly', 100, 91.25, '{\"stroke_25\":16,\"stroke_50\":14,\"stroke_75\":18,\"stroke_100\":17}', '{\"lap_time_25\":\"29.00\",\"lap_time_50\":\"25.78\",\"lap_time_75\":\"17.25\",\"lap_time_100\":\"19.22\"}', '', '2025-12-17 00:47:46'),
(41, 'cis', 'y24514', '2025-12-18', NULL, NULL, 5, NULL, 'long', 'fly', 50, 25.23, '{\"stroke_50\":14}', '{\"lap_time_50\":\"25.23\"}', NULL, '2025-12-18 00:37:39'),
(42, 'cis', 'abe', '2025-12-18', NULL, NULL, 5, NULL, 'short', 'fly', 100, 60.80, '{\"stroke_25\":0,\"stroke_50\":2,\"stroke_75\":1,\"stroke_100\":3}', '{\"lap_time_25\":\"15.2\",\"lap_time_50\":\"15.2\",\"lap_time_75\":\"15.2\",\"lap_time_100\":\"15.2\"}', NULL, '2025-12-18 01:44:13'),
(43, 'cis', 'y24514', '2025-12-19', NULL, NULL, 2, NULL, 'long', 'fly', 100, 66.79, '{\"stroke_50\":21,\"stroke_100\":25}', '{\"lap_time_50\":\"29.98\",\"lap_time_100\":\"36.81\"}', NULL, '2025-12-18 04:04:06'),
(44, 'cis', 'y24514', '2025-12-18', NULL, NULL, 3, NULL, 'long', 'fr', 100, 59.98, '{\"stroke_50\":18,\"stroke_100\":18}', '{\"lap_time_50\":\"29.98\",\"lap_time_100\":\"30.00\"}', NULL, '2025-12-18 04:49:52'),
(45, 'cis', 'y24514', '2025-12-24', NULL, NULL, 3, NULL, 'short', 'fr', 50, 27.48, '{\"stroke_25\":18,\"stroke_50\":19}', '{\"lap_time_25\":\"12.14\",\"lap_time_50\":\"15.34\"}', NULL, '2025-12-24 00:13:55'),
(46, 'cis', 'y24514', '2026-01-08', NULL, NULL, 3, NULL, 'long', 'fr', 50, 24.98, '{\"stroke_50\":19}', '{\"lap_time_50\":\"24.98\"}', NULL, '2026-01-08 04:18:28'),
(47, 'cis', 'y24514', '2026-01-08', 'スイミングフェスタ', 'タイム決勝', 1, 'official', 'short', 'fr', 50, 24.45, '{\"stroke_25\":11,\"stroke_50\":13}', '{\"lap_time_25\":\"11.23\",\"lap_time_50\":\"13.22\"}', NULL, '2026-01-08 04:21:33'),
(48, 'cis', 'abe', '2026-01-08', NULL, NULL, 2, 'practice', 'short', 'ba', 100, 63.80, '{\"stroke_25\":10,\"stroke_50\":10,\"stroke_75\":11,\"stroke_100\":12}', '{\"lap_time_25\":\"15.2\",\"lap_time_50\":\"15.2\",\"lap_time_75\":\"15.2\",\"lap_time_100\":\"18.2\"}', NULL, '2026-01-08 04:22:47');

-- --------------------------------------------------------

--
-- テーブルの構造 `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `teams`
--

INSERT INTO `teams` (`id`, `name`) VALUES
(1, 'Team A'),
(2, 'Team B'),
(3, '産技短');

-- --------------------------------------------------------

--
-- テーブルの構造 `tennis_actions`
--

CREATE TABLE `tennis_actions` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `player_name` varchar(255) NOT NULL,
  `action_type` varchar(255) NOT NULL,
  `score_a` int(11) NOT NULL,
  `score_b` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `tennis_actions`
--

INSERT INTO `tennis_actions` (`id`, `game_id`, `player_name`, `action_type`, `score_a`, `score_b`, `created_at`) VALUES
(1, 1, '田中', 'サービスエース', 1, 0, '2026-01-08 09:31:41'),
(2, 1, '田中', 'サービスエース', 2, 0, '2026-01-08 09:31:41'),
(3, 1, '田中', 'サービスエース', 3, 0, '2026-01-08 09:31:41'),
(4, 1, '田中', 'サービスエース', 4, 0, '2026-01-08 09:31:41'),
(5, 1, '田中', 'サービスエース', 1, 0, '2026-01-08 09:31:41'),
(6, 1, '田中', 'サービスエース', 2, 0, '2026-01-08 09:31:41'),
(7, 1, '田中', 'サービスエース', 3, 0, '2026-01-08 09:31:41'),
(8, 1, '田中', 'サービスエース', 4, 0, '2026-01-08 09:31:41'),
(9, 1, '田中', 'サービスエース', 1, 0, '2026-01-08 09:31:41'),
(10, 1, '田中', 'サービスエース', 2, 0, '2026-01-08 09:31:41'),
(11, 1, '田中', 'サービスエース', 3, 0, '2026-01-08 09:31:41'),
(12, 1, '田中', 'サービスエース', 4, 0, '2026-01-08 09:31:41'),
(13, 2, '田中', 'リターンエース', 1, 0, '2026-01-08 10:14:59'),
(14, 2, '田中', 'リターンエース', 2, 0, '2026-01-08 10:14:59'),
(15, 2, '田中', 'リターンエース', 3, 0, '2026-01-08 10:14:59'),
(16, 2, '田中', 'リターンエース', 4, 0, '2026-01-08 10:14:59'),
(17, 2, '田中', 'リターンエース', 1, 0, '2026-01-08 10:14:59'),
(18, 2, '田中', 'リターンエース', 2, 0, '2026-01-08 10:14:59'),
(19, 2, '田中', 'リターンエース', 3, 0, '2026-01-08 10:14:59'),
(20, 2, '田中', 'リターンエース', 4, 0, '2026-01-08 10:14:59'),
(21, 2, '田中', 'リターンエース', 1, 0, '2026-01-08 10:14:59'),
(22, 2, '田中', 'リターンエース', 2, 0, '2026-01-08 10:14:59'),
(23, 2, '田中', 'リターンエース', 3, 0, '2026-01-08 10:14:59'),
(24, 2, '田中', 'リターンエース', 4, 0, '2026-01-08 10:14:59'),
(25, 3, '田中', 'ストローク', 1, 0, '2026-01-08 10:15:42'),
(26, 3, '田中', 'ストローク', 2, 0, '2026-01-08 10:15:42'),
(27, 3, '田中', 'ストローク', 3, 0, '2026-01-08 10:15:42'),
(28, 3, '田中', 'ストローク', 4, 0, '2026-01-08 10:15:42'),
(29, 3, '田中', 'ストローク', 1, 0, '2026-01-08 10:15:42'),
(30, 3, '田中', 'ストローク', 2, 0, '2026-01-08 10:15:42'),
(31, 3, '田中', 'ストローク', 3, 0, '2026-01-08 10:15:42'),
(32, 3, '田中', 'ストローク', 4, 0, '2026-01-08 10:15:42'),
(33, 3, '田中', 'ストローク', 1, 0, '2026-01-08 10:15:43'),
(34, 3, '田中', 'ストローク', 2, 0, '2026-01-08 10:15:43'),
(35, 3, '田中', 'ストローク', 3, 0, '2026-01-08 10:15:43'),
(36, 3, '佐藤', 'ネットタッチ', 4, 0, '2026-01-08 10:15:43'),
(37, 4, 'くまさん', 'サービスエース', 1, 0, '2026-01-08 13:11:20'),
(38, 4, 'くまさん', 'スマッシュ', 2, 0, '2026-01-08 13:11:20'),
(39, 4, 'うさぎさん', 'ネットイン', 2, 1, '2026-01-08 13:11:20'),
(40, 4, 'うさぎさん', 'ネットイン', 2, 2, '2026-01-08 13:11:20'),
(41, 4, 'くまさん', 'リターンエース', 3, 2, '2026-01-08 13:11:20'),
(42, 4, 'くまさん', 'リターンエース', 4, 2, '2026-01-08 13:11:20'),
(43, 4, 'くまさん', 'リターンエース', 1, 0, '2026-01-08 13:11:20'),
(44, 4, 'くまさん', 'リターンエース', 2, 0, '2026-01-08 13:11:20'),
(45, 4, 'くまさん', 'リターンエース', 3, 0, '2026-01-08 13:11:20'),
(46, 4, 'くまさん', 'リターンエース', 4, 0, '2026-01-08 13:11:20'),
(47, 4, 'くまさん', 'ダブルフォルト', 0, 1, '2026-01-08 13:11:20'),
(48, 4, 'くまさん', 'ダブルフォルト', 0, 2, '2026-01-08 13:11:20'),
(49, 4, 'くまさん', 'ダブルフォルト', 0, 3, '2026-01-08 13:11:20'),
(50, 4, 'くまさん', 'アウト', 0, 4, '2026-01-08 13:11:20'),
(51, 4, 'くまさん', 'アウト', 0, 1, '2026-01-08 13:11:20'),
(52, 4, 'うさぎさん', 'アウト', 1, 1, '2026-01-08 13:11:20'),
(53, 4, 'うさぎさん', 'ネット', 2, 1, '2026-01-08 13:11:20'),
(54, 4, 'うさぎさん', 'ネットタッチ', 3, 1, '2026-01-08 13:11:20'),
(55, 4, 'うさぎさん', 'ネットタッチ', 4, 1, '2026-01-08 13:11:20');

-- --------------------------------------------------------

--
-- テーブルの構造 `tennis_games`
--

CREATE TABLE `tennis_games` (
  `id` int(11) NOT NULL,
  `team_a` varchar(255) NOT NULL,
  `team_b` varchar(255) NOT NULL,
  `games_a` int(11) NOT NULL DEFAULT 0,
  `games_b` int(11) NOT NULL DEFAULT 0,
  `player_a1` varchar(255) DEFAULT NULL,
  `player_a2` varchar(255) DEFAULT NULL,
  `player_b1` varchar(255) DEFAULT NULL,
  `player_b2` varchar(255) DEFAULT NULL,
  `ai_comment` text DEFAULT NULL,
  `match_date` datetime NOT NULL DEFAULT current_timestamp(),
  `group_id` varchar(64) DEFAULT NULL,
  `saved_by_user_id` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `tennis_games`
--

INSERT INTO `tennis_games` (`id`, `team_a`, `team_b`, `games_a`, `games_b`, `player_a1`, `player_a2`, `player_b1`, `player_b2`, `ai_comment`, `match_date`, `group_id`, `saved_by_user_id`) VALUES
(1, 'teamA', 'teamB', 3, 0, '田中', '', '佐藤', '', NULL, '2026-01-08 09:31:41', NULL, NULL),
(2, 'teamA', 'teamB', 3, 0, '田中', '', '佐藤', '', NULL, '2026-01-08 10:14:59', NULL, NULL),
(3, 'teamA', 'teamB', 3, 0, '田中', '', '佐藤', '', NULL, '2026-01-08 10:15:42', NULL, NULL),
(4, 'くまさんチーム', 'うさぎさんチーム', 3, 1, 'くまさん', '', 'うさぎさん', '', NULL, '2026-01-08 13:11:20', NULL, NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `tennis_strategies`
--

CREATE TABLE `tennis_strategies` (
  `id` int(11) NOT NULL,
  `group_id` varchar(64) DEFAULT NULL,
  `user_id` varchar(64) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `json_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `tennis_strategies`
--

INSERT INTO `tennis_strategies` (`id`, `group_id`, `user_id`, `name`, `json_data`, `created_at`) VALUES
(1, 'cis', 'harunaabe', '作戦A', '{\"version\":\"5.3.0\",\"objects\":[{\"type\":\"rect\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":30,\"top\":30,\"width\":1658.92,\"height\":819.06,\"fill\":\"\",\"stroke\":\"white\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"rx\":0,\"ry\":0},{\"type\":\"line\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":859.46,\"top\":30,\"width\":0,\"height\":819.06,\"fill\":\"rgb(0,0,0)\",\"stroke\":\"white\",\"strokeWidth\":4,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"x1\":0,\"x2\":0,\"y1\":-409.53,\"y2\":409.53},{\"type\":\"line\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":30,\"top\":161.86,\"width\":1658.92,\"height\":0,\"fill\":\"rgb(0,0,0)\",\"stroke\":\"white\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"x1\":-829.46,\"x2\":829.46,\"y1\":0,\"y2\":0},{\"type\":\"line\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":30,\"top\":717.2,\"width\":1658.92,\"height\":0,\"fill\":\"rgb(0,0,0)\",\"stroke\":\"white\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"x1\":-829.46,\"x2\":829.46,\"y1\":0,\"y2\":0},{\"type\":\"line\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":408.16,\"top\":161.86,\"width\":0,\"height\":555.34,\"fill\":\"rgb(0,0,0)\",\"stroke\":\"white\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"x1\":0,\"x2\":0,\"y1\":-277.67100000000005,\"y2\":277.67100000000005},{\"type\":\"line\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":1310.76,\"top\":161.86,\"width\":0,\"height\":555.34,\"fill\":\"rgb(0,0,0)\",\"stroke\":\"white\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"x1\":0,\"x2\":0,\"y1\":-277.67100000000005,\"y2\":277.67100000000005},{\"type\":\"line\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":408.16,\"top\":439.53,\"width\":902.6,\"height\":0,\"fill\":\"rgb(0,0,0)\",\"stroke\":\"white\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"x1\":-451.2976000000001,\"x2\":451.2976000000001,\"y1\":0,\"y2\":0},{\"type\":\"line\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":30,\"top\":439.53,\"width\":15,\"height\":0,\"fill\":\"rgb(0,0,0)\",\"stroke\":\"white\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"x1\":-7.5,\"x2\":7.5,\"y1\":0,\"y2\":0},{\"type\":\"line\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":1673.92,\"top\":439.53,\"width\":15,\"height\":0,\"fill\":\"rgb(0,0,0)\",\"stroke\":\"white\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"x1\":-7.5,\"x2\":7.5,\"y1\":0,\"y2\":0},{\"type\":\"group\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":238.9,\"top\":337.98,\"width\":38,\"height\":38,\"fill\":\"rgb(0,0,0)\",\"stroke\":null,\"strokeWidth\":0,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"objects\":[{\"type\":\"circle\",\"version\":\"5.3.0\",\"originX\":\"center\",\"originY\":\"center\",\"left\":0,\"top\":0,\"width\":36,\"height\":36,\"fill\":\"#3498db\",\"stroke\":\"#fff\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"radius\":18,\"startAngle\":0,\"endAngle\":360},{\"type\":\"text\",\"version\":\"5.3.0\",\"originX\":\"center\",\"originY\":\"center\",\"left\":0,\"top\":0,\"width\":8,\"height\":18.08,\"fill\":\"#fff\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"fontFamily\":\"Times New Roman\",\"fontWeight\":\"bold\",\"fontSize\":16,\"text\":\"1\",\"underline\":false,\"overline\":false,\"linethrough\":false,\"textAlign\":\"left\",\"fontStyle\":\"normal\",\"lineHeight\":1.16,\"textBackgroundColor\":\"\",\"charSpacing\":0,\"styles\":[],\"direction\":\"ltr\",\"path\":null,\"pathStartOffset\":0,\"pathSide\":\"left\",\"pathAlign\":\"baseline\"}]},{\"type\":\"group\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":566.73,\"top\":557.97,\"width\":38,\"height\":38,\"fill\":\"rgb(0,0,0)\",\"stroke\":null,\"strokeWidth\":0,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"objects\":[{\"type\":\"circle\",\"version\":\"5.3.0\",\"originX\":\"center\",\"originY\":\"center\",\"left\":0,\"top\":0,\"width\":36,\"height\":36,\"fill\":\"#3498db\",\"stroke\":\"#fff\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"radius\":18,\"startAngle\":0,\"endAngle\":360},{\"type\":\"text\",\"version\":\"5.3.0\",\"originX\":\"center\",\"originY\":\"center\",\"left\":0,\"top\":0,\"width\":8,\"height\":18.08,\"fill\":\"#fff\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"fontFamily\":\"Times New Roman\",\"fontWeight\":\"bold\",\"fontSize\":16,\"text\":\"2\",\"underline\":false,\"overline\":false,\"linethrough\":false,\"textAlign\":\"left\",\"fontStyle\":\"normal\",\"lineHeight\":1.16,\"textBackgroundColor\":\"\",\"charSpacing\":0,\"styles\":[],\"direction\":\"ltr\",\"path\":null,\"pathStartOffset\":0,\"pathSide\":\"left\",\"pathAlign\":\"baseline\"}]},{\"type\":\"path\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":251.32,\"top\":160.01,\"width\":0.01,\"height\":0,\"fill\":null,\"stroke\":\"#ffffff\",\"strokeWidth\":4,\"strokeDashArray\":null,\"strokeLineCap\":\"round\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"round\",\"strokeUniform\":false,\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"path\":[[\"M\",253.31549822743386,162.00698554897883],[\"L\",253.32349822743384,162.00698554897883]]},{\"type\":\"group\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":264.89,\"top\":563.97,\"width\":38,\"height\":38,\"fill\":\"rgb(0,0,0)\",\"stroke\":null,\"strokeWidth\":0,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"objects\":[{\"type\":\"circle\",\"version\":\"5.3.0\",\"originX\":\"center\",\"originY\":\"center\",\"left\":0,\"top\":0,\"width\":36,\"height\":36,\"fill\":\"#3498db\",\"stroke\":\"#fff\",\"strokeWidth\":2,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"radius\":18,\"startAngle\":0,\"endAngle\":360},{\"type\":\"text\",\"version\":\"5.3.0\",\"originX\":\"center\",\"originY\":\"center\",\"left\":0,\"top\":0,\"width\":8,\"height\":18.08,\"fill\":\"#fff\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"fontFamily\":\"Times New Roman\",\"fontWeight\":\"bold\",\"fontSize\":16,\"text\":\"2\",\"underline\":false,\"overline\":false,\"linethrough\":false,\"textAlign\":\"left\",\"fontStyle\":\"normal\",\"lineHeight\":1.16,\"textBackgroundColor\":\"\",\"charSpacing\":0,\"styles\":[],\"direction\":\"ltr\",\"path\":null,\"pathStartOffset\":0,\"pathSide\":\"left\",\"pathAlign\":\"baseline\"}]},{\"type\":\"path\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":182.36,\"top\":155.01,\"width\":0.99,\"height\":1,\"fill\":null,\"stroke\":\"#ffffff\",\"strokeWidth\":4,\"strokeDashArray\":null,\"strokeLineCap\":\"round\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"round\",\"strokeUniform\":false,\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"path\":[[\"M\",185.3513495136806,157.00725217298566],[\"Q\",185.35534951368058,157.00725217298566,184.85561312607945,157.507225510585],[\"L\",184.3598767384783,158.0071988481843]]},{\"type\":\"group\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":291.76,\"top\":337.54,\"width\":93,\"height\":16,\"fill\":\"rgb(0,0,0)\",\"stroke\":null,\"strokeWidth\":0,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":12.09,\"scaleY\":3.19,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"objects\":[{\"type\":\"line\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"center\",\"left\":-46.5,\"top\":0,\"width\":80,\"height\":0,\"fill\":\"rgb(0,0,0)\",\"stroke\":\"#fff\",\"strokeWidth\":4,\"strokeDashArray\":[8,4],\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"x1\":-40,\"x2\":40,\"y1\":0,\"y2\":0},{\"type\":\"triangle\",\"version\":\"5.3.0\",\"originX\":\"center\",\"originY\":\"center\",\"left\":38.5,\"top\":0,\"width\":15,\"height\":15,\"fill\":\"#fff\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":90,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0}]},{\"type\":\"group\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"top\",\"left\":579.01,\"top\":554.82,\"width\":93,\"height\":16,\"fill\":\"rgb(0,0,0)\",\"stroke\":null,\"strokeWidth\":0,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":271.38,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"objects\":[{\"type\":\"line\",\"version\":\"5.3.0\",\"originX\":\"left\",\"originY\":\"center\",\"left\":-46.5,\"top\":0,\"width\":80,\"height\":0,\"fill\":\"rgb(0,0,0)\",\"stroke\":\"#f1c40f\",\"strokeWidth\":4,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0,\"x1\":-40,\"x2\":40,\"y1\":0,\"y2\":0},{\"type\":\"triangle\",\"version\":\"5.3.0\",\"originX\":\"center\",\"originY\":\"center\",\"left\":38.5,\"top\":0,\"width\":15,\"height\":15,\"fill\":\"#f1c40f\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeDashOffset\":0,\"strokeLineJoin\":\"miter\",\"strokeUniform\":false,\"strokeMiterLimit\":4,\"scaleX\":1,\"scaleY\":1,\"angle\":90,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"paintFirst\":\"fill\",\"globalCompositeOperation\":\"source-over\",\"skewX\":0,\"skewY\":0}]}],\"background\":\"#2e7d32\"}', '2026-01-14 02:30:01');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `admin_role_requests`
--
ALTER TABLE `admin_role_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_group_status_time` (`group_id`,`status`,`requested_at`),
  ADD KEY `idx_user_group` (`group_id`,`user_id`);

--
-- テーブルのインデックス `basketball_strategies`
--
ALTER TABLE `basketball_strategies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_basketball_strategies_group_created` (`group_id`,`created_at`);

--
-- テーブルのインデックス `calendar_tbl`
--
ALTER TABLE `calendar_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calendar_user` (`user_id`),
  ADD KEY `calendar_group_id` (`group_id`,`user_id`) USING BTREE,
  ADD KEY `idx_calendar_shared` (`group_id`,`is_shared`,`startdate`);

--
-- テーブルのインデックス `chat_group_member_tbl`
--
ALTER TABLE `chat_group_member_tbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_group_member` (`chat_group_id`,`user_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_group` (`chat_group_id`);

--
-- テーブルのインデックス `chat_group_tbl`
--
ALTER TABLE `chat_group_tbl`
  ADD PRIMARY KEY (`chat_group_id`),
  ADD KEY `idx_group` (`group_id`),
  ADD KEY `idx_creator` (`created_by`);

--
-- テーブルのインデックス `chat_read_status_tbl`
--
ALTER TABLE `chat_read_status_tbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_read_status` (`user_id`,`group_id`,`chat_type`,`chat_group_id`,`recipient_id`),
  ADD KEY `idx_user_type` (`user_id`,`group_id`,`chat_type`),
  ADD KEY `idx_chat_group` (`chat_group_id`),
  ADD KEY `idx_recipient` (`recipient_id`);

--
-- テーブルのインデックス `chat_tbl`
--
ALTER TABLE `chat_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_group` (`group_id`),
  ADD KEY `idx_chat_group` (`chat_group_id`),
  ADD KEY `idx_direct` (`user_id`,`recipient_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- テーブルのインデックス `diary_tbl`
--
ALTER TABLE `diary_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`group_id`,`user_id`),
  ADD KEY `idx_date` (`diary_date`),
  ADD KEY `idx_user_date` (`group_id`,`user_id`,`diary_date`),
  ADD KEY `idx_submitted` (`group_id`,`submitted_to_admin`,`submitted_at`),
  ADD KEY `idx_admin_feedback` (`group_id`,`admin_feedback_at`);

--
-- テーブルのインデックス `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_games_created_at` (`created_at`),
  ADD KEY `idx_games_group_id_created_at` (`group_id`,`created_at`),
  ADD KEY `idx_games_saved_by` (`saved_by_user_id`,`created_at`);

--
-- テーブルのインデックス `game_actions`
--
ALTER TABLE `game_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_game_actions_game_id` (`game_id`),
  ADD KEY `idx_game_actions_player_id` (`player_id`),
  ADD KEY `idx_game_actions_quarter_team` (`quarter`,`team`);

--
-- テーブルのインデックス `goal_tbl`
--
ALTER TABLE `goal_tbl`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `goal_group` (`group_id`) USING BTREE,
  ADD KEY `goal_user` (`user_id`) USING BTREE,
  ADD KEY `idx_goal_group_user` (`group_id`,`user_id`);

--
-- テーブルのインデックス `inquiries_tbl`
--
ALTER TABLE `inquiries_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inquiries_group_status_created` (`group_id`,`status`,`created_at`),
  ADD KEY `idx_inquiries_user_created` (`group_id`,`user_id`,`created_at`);

--
-- テーブルのインデックス `login_tbl`
--
ALTER TABLE `login_tbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`password`) USING BTREE,
  ADD UNIQUE KEY `uniq_login_group_user` (`group_id`,`user_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `idx_login_tbl_is_super_admin` (`is_super_admin`),
  ADD KEY `idx_login_tbl_sport` (`sport`);

--
-- テーブルのインデックス `pi_tbl`
--
ALTER TABLE `pi_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pi_group` (`group_id`) USING BTREE,
  ADD KEY `pi_user` (`user_id`) USING BTREE,
  ADD KEY `idx_pi_group_user` (`group_id`,`user_id`);

--
-- テーブルのインデックス `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`);

--
-- テーブルのインデックス `swim_best_tbl`
--
ALTER TABLE `swim_best_tbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_best` (`group_id`,`user_id`,`pool`,`event`,`distance`),
  ADD KEY `swimbest_user_id` (`user_id`);

--
-- テーブルのインデックス `swim_practice_tbl`
--
ALTER TABLE `swim_practice_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_swim_practice_user_date` (`group_id`,`user_id`,`practice_date`);

--
-- テーブルのインデックス `swim_tbl`
--
ALTER TABLE `swim_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `swim_user_id` (`user_id`),
  ADD KEY `idx_swim_group_user` (`group_id`,`user_id`);

--
-- テーブルのインデックス `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `tennis_actions`
--
ALTER TABLE `tennis_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tennis_actions_game_id` (`game_id`);

--
-- テーブルのインデックス `tennis_games`
--
ALTER TABLE `tennis_games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tennis_games_group_id_match_date` (`group_id`,`match_date`),
  ADD KEY `idx_tennis_games_saved_by` (`saved_by_user_id`,`match_date`);

--
-- テーブルのインデックス `tennis_strategies`
--
ALTER TABLE `tennis_strategies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tennis_strategies_group_created` (`group_id`,`created_at`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `admin_role_requests`
--
ALTER TABLE `admin_role_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- テーブルの AUTO_INCREMENT `basketball_strategies`
--
ALTER TABLE `basketball_strategies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `calendar_tbl`
--
ALTER TABLE `calendar_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- テーブルの AUTO_INCREMENT `chat_group_member_tbl`
--
ALTER TABLE `chat_group_member_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- テーブルの AUTO_INCREMENT `chat_group_tbl`
--
ALTER TABLE `chat_group_tbl`
  MODIFY `chat_group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- テーブルの AUTO_INCREMENT `chat_read_status_tbl`
--
ALTER TABLE `chat_read_status_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- テーブルの AUTO_INCREMENT `chat_tbl`
--
ALTER TABLE `chat_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- テーブルの AUTO_INCREMENT `diary_tbl`
--
ALTER TABLE `diary_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- テーブルの AUTO_INCREMENT `games`
--
ALTER TABLE `games`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `game_actions`
--
ALTER TABLE `game_actions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `goal_tbl`
--
ALTER TABLE `goal_tbl`
  MODIFY `goal_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- テーブルの AUTO_INCREMENT `inquiries_tbl`
--
ALTER TABLE `inquiries_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- テーブルの AUTO_INCREMENT `login_tbl`
--
ALTER TABLE `login_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- テーブルの AUTO_INCREMENT `pi_tbl`
--
ALTER TABLE `pi_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- テーブルの AUTO_INCREMENT `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- テーブルの AUTO_INCREMENT `swim_best_tbl`
--
ALTER TABLE `swim_best_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- テーブルの AUTO_INCREMENT `swim_practice_tbl`
--
ALTER TABLE `swim_practice_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- テーブルの AUTO_INCREMENT `swim_tbl`
--
ALTER TABLE `swim_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- テーブルの AUTO_INCREMENT `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- テーブルの AUTO_INCREMENT `tennis_actions`
--
ALTER TABLE `tennis_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- テーブルの AUTO_INCREMENT `tennis_games`
--
ALTER TABLE `tennis_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- テーブルの AUTO_INCREMENT `tennis_strategies`
--
ALTER TABLE `tennis_strategies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `calendar_tbl`
--
ALTER TABLE `calendar_tbl`
  ADD CONSTRAINT `fk_calendar_group_user` FOREIGN KEY (`group_id`,`user_id`) REFERENCES `login_tbl` (`group_id`, `user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- テーブルの制約 `chat_group_member_tbl`
--
ALTER TABLE `chat_group_member_tbl`
  ADD CONSTRAINT `chat_group_member_tbl_ibfk_1` FOREIGN KEY (`chat_group_id`) REFERENCES `chat_group_tbl` (`chat_group_id`) ON DELETE CASCADE;

--
-- テーブルの制約 `chat_tbl`
--
ALTER TABLE `chat_tbl`
  ADD CONSTRAINT `chat_tbl_ibfk_1` FOREIGN KEY (`chat_group_id`) REFERENCES `chat_group_tbl` (`chat_group_id`) ON DELETE CASCADE;

--
-- テーブルの制約 `game_actions`
--
ALTER TABLE `game_actions`
  ADD CONSTRAINT `fk_game_actions_game_id` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `goal_tbl`
--
ALTER TABLE `goal_tbl`
  ADD CONSTRAINT `fk_goal_group_user` FOREIGN KEY (`group_id`,`user_id`) REFERENCES `login_tbl` (`group_id`, `user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- テーブルの制約 `pi_tbl`
--
ALTER TABLE `pi_tbl`
  ADD CONSTRAINT `fk_pi_group_user` FOREIGN KEY (`group_id`,`user_id`) REFERENCES `login_tbl` (`group_id`, `user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- テーブルの制約 `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `swim_best_tbl`
--
ALTER TABLE `swim_best_tbl`
  ADD CONSTRAINT `fk_swim_best_group_user` FOREIGN KEY (`group_id`,`user_id`) REFERENCES `login_tbl` (`group_id`, `user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- テーブルの制約 `swim_tbl`
--
ALTER TABLE `swim_tbl`
  ADD CONSTRAINT `fk_swim_group_user` FOREIGN KEY (`group_id`,`user_id`) REFERENCES `login_tbl` (`group_id`, `user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- テーブルの制約 `tennis_actions`
--
ALTER TABLE `tennis_actions`
  ADD CONSTRAINT `fk_tennis_actions_game` FOREIGN KEY (`game_id`) REFERENCES `tennis_games` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
