-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2025-12-24 07:45:18
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
-- データベース: `sportdata_db`
--

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
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `calendar_tbl`
--

INSERT INTO `calendar_tbl` (`id`, `group_id`, `user_id`, `title`, `memo`, `startdate`, `enddate`, `create_at`) VALUES
(6, 'cis', 'abe', 'あああ', 'あああ', '2025-12-03', '2025-12-04', '2025-12-03 14:42:20'),
(8, 'cis', 'y24514', '合宿', 'きつい', '2025-12-09', '2025-12-12', '2025-12-16 09:33:47'),
(9, 'cis', 'y24514', '遠征', '', '2025-12-20', '2025-12-22', '2025-12-23 08:57:16'),
(10, 'cis', 'y24514', '年末 off', '', '2025-12-29', '2026-01-01', '2025-12-23 08:57:34');

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

--
-- テーブルのデータのダンプ `chat_group_member_tbl`
--

INSERT INTO `chat_group_member_tbl` (`id`, `chat_group_id`, `group_id`, `user_id`, `joined_at`) VALUES
(5, 2, 'cis', 'y24514', '2025-12-23 11:33:16'),
(6, 2, 'cis', 'y24520', '2025-12-23 11:33:16'),
(7, 2, 'cis', 'y24515', '2025-12-23 11:33:16');

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

--
-- テーブルのデータのダンプ `chat_group_tbl`
--

INSERT INTO `chat_group_tbl` (`chat_group_id`, `group_id`, `group_name`, `group_description`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'cis', 'チームA', '', 'y24514', '2025-12-23 11:33:16', '2025-12-23 11:33:16');

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
(13, 'cis', 'y24515', 'direct', NULL, 'y24514', 6, '2025-12-22 16:38:45'),
(14, 'cis', 'y24515', 'direct', NULL, 'y24514', 6, '2025-12-22 16:39:17'),
(15, 'cis', 'y24515', 'direct', NULL, 'y24514', 6, '2025-12-22 16:39:26'),
(16, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-22 16:39:39'),
(17, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-22 16:39:40'),
(18, 'cis', 'y24514', 'direct', NULL, 'y24515', 6, '2025-12-22 16:39:41'),
(19, 'cis', 'y24514', 'direct', NULL, 'y24515', 14, '2025-12-22 16:40:21'),
(20, 'cis', 'y24515', 'direct', NULL, 'y24514', 14, '2025-12-22 16:40:59'),
(21, 'cis', 'y24515', 'direct', NULL, 'y24514', 14, '2025-12-22 16:41:02'),
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
(40, 'cis', 'y24515', 'direct', NULL, 'y24514', 15, '2025-12-23 09:10:22'),
(41, 'cis', 'y24515', 'direct', NULL, 'y24514', 15, '2025-12-23 09:13:41'),
(42, 'cis', 'y24515', 'direct', NULL, 'y24514', 15, '2025-12-23 09:13:46'),
(43, 'cis', 'y24515', 'direct', NULL, 'y24514', 15, '2025-12-23 09:15:51'),
(44, 'cis', 'y24515', 'direct', NULL, 'y24514', 15, '2025-12-23 09:16:42'),
(45, 'cis', 'y24515', 'direct', NULL, 'y24514', 15, '2025-12-23 09:18:14'),
(46, 'cis', 'y24514', 'direct', NULL, 'y24515', 16, '2025-12-23 09:18:49'),
(47, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 10:02:06'),
(48, 'cis', 'y24514', 'direct', NULL, 'y24515', 16, '2025-12-23 10:02:07'),
(49, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-23 10:02:08'),
(50, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-23 10:02:09'),
(51, 'cis', 'y24514', 'direct', NULL, 'y24515', 16, '2025-12-23 10:02:09'),
(52, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 10:02:12'),
(53, 'cis', 'y24514', 'direct', NULL, 'y24520', 12, '2025-12-23 10:04:01'),
(58, 'cis', 'y24520', 'direct', NULL, 'y24514', 12, '2025-12-23 10:08:17'),
(59, 'cis', 'y24515', 'direct', NULL, 'y24514', 16, '2025-12-23 10:08:38'),
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
(139, 'cis', 'y24514', 'direct', NULL, 'y24513', 1, '2025-12-24 11:11:50');

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
(5, 'cis', 'y24515', 'direct', NULL, 'y24514', 'こんにちは', NULL, NULL, 0, NULL, '2025-12-22 11:00:20'),
(6, 'cis', 'y24514', 'direct', NULL, 'y24515', 'こんにちは', NULL, NULL, 0, NULL, '2025-12-22 11:02:08'),
(7, 'cis', 'y24514', 'direct', NULL, 'y24514_2', 'こんにちは', NULL, NULL, 0, NULL, '2025-12-22 14:20:15'),
(8, 'cis', 'y24520', 'direct', NULL, 'y24514', '今週試合いつだっけ？？', NULL, NULL, 0, NULL, '2025-12-22 15:24:45'),
(9, 'cis', 'y24514', 'direct', NULL, 'y24520', '今週の土曜日', NULL, NULL, 1, '2025-12-23 11:24:52', '2025-12-22 15:25:12'),
(10, 'cis', 'y24520', 'direct', NULL, 'y24514', 'ありがとう', NULL, NULL, 0, NULL, '2025-12-22 15:28:55'),
(11, 'cis', 'y24514', 'direct', NULL, 'y24520', 'はーい', NULL, NULL, 1, '2025-12-23 11:29:41', '2025-12-22 15:30:50'),
(12, 'cis', 'y24514', 'direct', NULL, 'y24520', '間に合う？？', NULL, NULL, 1, '2025-12-23 11:27:55', '2025-12-22 16:34:57'),
(13, 'cis', 'y24514', 'direct', NULL, 'y24515', 'どうも', NULL, NULL, 1, '2025-12-23 11:32:11', '2025-12-22 16:39:48'),
(14, 'cis', 'y24515', 'direct', NULL, 'y24514', 'ぺこぺこ', NULL, NULL, 0, NULL, '2025-12-22 16:40:04'),
(16, 'cis', 'y24515', 'direct', NULL, 'y24514', '楽しいね', NULL, NULL, 0, NULL, '2025-12-23 09:18:30'),
(19, 'cis', 'y24515', 'direct', NULL, 'y24514', 'たのしいなあああ！？', NULL, NULL, 0, NULL, '2025-12-23 10:08:44'),
(24, 'cis', 'y24514', 'direct', NULL, 'y24515', '楽しいなぁぁぁああああ', NULL, NULL, 1, '2025-12-24 09:03:17', '2025-12-23 14:27:25');

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
  `tags` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `diary_tbl`
--

INSERT INTO `diary_tbl` (`id`, `group_id`, `user_id`, `diary_date`, `title`, `content`, `tags`, `created_at`, `updated_at`) VALUES
(3, 'cis', 'y24514', '2025-12-24', 'テスト', 'テスト', '', '2025-12-24 09:01:18', '2025-12-24 09:01:18');

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
(7, 'cis', 'y24514', '半フリ55秒切る', 100, NULL, '2025-12-23 14:28:53');

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
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_super_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `login_tbl`
--

INSERT INTO `login_tbl` (`id`, `group_id`, `user_id`, `password`, `name`, `dob`, `height`, `weight`, `position`) VALUES
(13, 'cis', 'y24514', '$2y$10$MVyXrYFuMD/ULyZMNG40d.8yDyCa2FU4Ydm2EghYzPTmg2nj.lSYK', '藤原大輔', '2006-03-03', 170.0, 59.0, '学生会役員'),
(16, '花巻東', 'y24514', '$2y$10$irKSeqOAkgWJ3hAlF3m2ru.SAsNgv7YiSbY7nd5fdbPbFuRlxdSpm', '藤原大輔', '2006-03-03', 170.0, 59.0, 'fly/fr'),
(17, 'cis', 'y24513', '$2y$10$T2CgtBtyPu43inv7IY53reJ5v7f9YCJFboy9AI6rpPZlr9Xv9VCsq', '藤原啄都', '2025-12-18', 172.0, 59.0, 'バタフライ'),
(18, 'sangitan', 'h-abe', '$2y$10$3077kKiMdxYuVABAokwcwOON0P5z5.psI.GWsh2ZlywJLrHYHJHwG', '安倍春菜', '2026-07-03', 190.0, 100.0, 'DF'),
(19, 'cis', 'y24520', '$2y$10$UN6nKQIqkmpClpFUVdQM8ONs76V2bkS2CwuJy2gdC4rdL6TL6c98O', '吉田稜', '2025-07-21', 164.0, 60.0, '前衛'),
(20, 'cis', 'abe', '$2y$10$5rnhaBbn/ycTIjygFdip1utCRMa/G7oh2CKa.ocM2R8f9JqS82yGO', '剛田武', '2025-11-12', 200.0, 100.0, 'OF'),
(21, 'cis', 'y24514_2', '$2y$10$ZT6N5Ll9v.abrtWzETvWr.4pcIATYVuQVoqUmehSen6il6uK3hUp.', '藤原大輔２', '2006-03-03', 170.0, 59.0, 'fly'),
(22, 'cis', 'y24515', '$2y$10$7CRF5eZTnCErAstv1jdyuuP8id/fscLSIfjp7C2hGzvRL9vzC/75i', '藤原叶夢', '2006-02-05', 182.0, 55.0, '二年生'),
(23, 'cis', 'y24510', '$2y$10$QwHSD73GmvpdHH2nybBJluekIlV1UeYF.zgR/V/UPSDUbGnHx31qi', '田村淳道', '2005-10-24', 180.0, 65.0, '二年生');

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
(45, 'cis', 'y24514', '2025-12-24', NULL, NULL, 3, NULL, 'short', 'fr', 50, 27.48, '{\"stroke_25\":18,\"stroke_50\":19}', '{\"lap_time_25\":\"12.14\",\"lap_time_50\":\"15.34\"}', NULL, '2025-12-24 00:13:55');

-- --------------------------------------------------------

--
-- テーブルの構造 `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `calendar_tbl`
--
ALTER TABLE `calendar_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calendar_user` (`user_id`),
  ADD KEY `calendar_group_id` (`group_id`,`user_id`) USING BTREE;

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
  ADD KEY `idx_date` (`diary_date`);

--
-- テーブルのインデックス `goal_tbl`
--
ALTER TABLE `goal_tbl`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `goal_group` (`group_id`) USING BTREE,
  ADD KEY `goal_user` (`user_id`) USING BTREE;

--
-- テーブルのインデックス `login_tbl`
--
ALTER TABLE `login_tbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`password`) USING BTREE,
  ADD KEY `group_id` (`group_id`);

--
-- テーブルのインデックス `pi_tbl`
--
ALTER TABLE `pi_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pi_group` (`group_id`) USING BTREE,
  ADD KEY `pi_user` (`user_id`) USING BTREE;

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
-- テーブルのインデックス `swim_tbl`
--
ALTER TABLE `swim_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `swim_group_id` (`group_id`),
  ADD KEY `swim_user_id` (`user_id`);

--
-- テーブルのインデックス `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `calendar_tbl`
--
ALTER TABLE `calendar_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- テーブルの AUTO_INCREMENT `chat_tbl`
--
ALTER TABLE `chat_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- テーブルの AUTO_INCREMENT `diary_tbl`
--
ALTER TABLE `diary_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- テーブルの AUTO_INCREMENT `goal_tbl`
--
ALTER TABLE `goal_tbl`
  MODIFY `goal_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- テーブルの AUTO_INCREMENT `login_tbl`
--
ALTER TABLE `login_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- テーブルの AUTO_INCREMENT `pi_tbl`
--
ALTER TABLE `pi_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- テーブルの AUTO_INCREMENT `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `swim_best_tbl`
--
ALTER TABLE `swim_best_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- テーブルの AUTO_INCREMENT `swim_tbl`
--
ALTER TABLE `swim_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- テーブルの AUTO_INCREMENT `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `calendar_tbl`
--
ALTER TABLE `calendar_tbl`
  ADD CONSTRAINT `calendar_group` FOREIGN KEY (`group_id`) REFERENCES `login_tbl` (`group_id`),
  ADD CONSTRAINT `calendar_user` FOREIGN KEY (`user_id`) REFERENCES `login_tbl` (`user_id`);

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
-- テーブルの制約 `goal_tbl`
--
ALTER TABLE `goal_tbl`
  ADD CONSTRAINT `goal_group` FOREIGN KEY (`group_id`) REFERENCES `login_tbl` (`group_id`),
  ADD CONSTRAINT `goal_user` FOREIGN KEY (`user_id`) REFERENCES `login_tbl` (`user_id`);

--
-- テーブルの制約 `pi_tbl`
--
ALTER TABLE `pi_tbl`
  ADD CONSTRAINT `pi_group` FOREIGN KEY (`group_id`) REFERENCES `login_tbl` (`group_id`),
  ADD CONSTRAINT `pi_user` FOREIGN KEY (`user_id`) REFERENCES `login_tbl` (`user_id`);

--
-- テーブルの制約 `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `swim_best_tbl`
--
ALTER TABLE `swim_best_tbl`
  ADD CONSTRAINT `swimbest_group_id` FOREIGN KEY (`group_id`) REFERENCES `login_tbl` (`group_id`),
  ADD CONSTRAINT `swimbest_user_id` FOREIGN KEY (`user_id`) REFERENCES `login_tbl` (`user_id`);

--
-- テーブルの制約 `swim_tbl`
--
ALTER TABLE `swim_tbl`
  ADD CONSTRAINT `swim_group_id` FOREIGN KEY (`group_id`) REFERENCES `login_tbl` (`group_id`),
  ADD CONSTRAINT `swim_user_id` FOREIGN KEY (`user_id`) REFERENCES `login_tbl` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
