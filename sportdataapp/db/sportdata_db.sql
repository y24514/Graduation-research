-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2025-12-19 08:37:52
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
(8, 'cis', 'y24514', '合宿', 'きつい', '2025-12-09', '2025-12-12', '2025-12-16 09:33:47');

-- --------------------------------------------------------

--
-- テーブルの構造 `goal_tbl`
--

CREATE TABLE `goal_tbl` (
  `goal_id` int(100) NOT NULL,
  `group_id` varchar(50) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `goal` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `goal_tbl`
--

INSERT INTO `goal_tbl` (`goal_id`, `group_id`, `user_id`, `goal`, `created_at`) VALUES
(5, 'sangitan', 'h-abe', '120kgになる', '2025-12-03 13:47:46'),
(6, 'cis', 'abe', '', '2025-12-03 14:15:07'),
(7, 'cis', 'y24514', '1分切る、呼吸回数、ストローク回数を減らす', '2025-12-18 13:17:51');

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
  `position` varchar(50) NOT NULL
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
(20, 'cis', 'abe', '$2y$10$5rnhaBbn/ycTIjygFdip1utCRMa/G7oh2CKa.ocM2R8f9JqS82yGO', '剛田武', '2025-11-12', 200.0, 100.0, 'OF');

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
(32, 'cis', 'abe', 190.0, 120.0, '', '05:00:00', '2025-12-03 13:55:10', NULL, NULL, NULL, NULL);

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
(44, 'cis', 'y24514', '2025-12-18', NULL, NULL, 3, NULL, 'long', 'fr', 100, 59.98, '{\"stroke_50\":18,\"stroke_100\":18}', '{\"lap_time_50\":\"29.98\",\"lap_time_100\":\"30.00\"}', NULL, '2025-12-18 04:49:52');

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
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `calendar_tbl`
--
ALTER TABLE `calendar_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- テーブルの AUTO_INCREMENT `goal_tbl`
--
ALTER TABLE `goal_tbl`
  MODIFY `goal_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- テーブルの AUTO_INCREMENT `login_tbl`
--
ALTER TABLE `login_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- テーブルの AUTO_INCREMENT `pi_tbl`
--
ALTER TABLE `pi_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- テーブルの AUTO_INCREMENT `swim_best_tbl`
--
ALTER TABLE `swim_best_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- テーブルの AUTO_INCREMENT `swim_tbl`
--
ALTER TABLE `swim_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
