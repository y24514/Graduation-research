-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2025-11-13 07:58:44
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
(4, 'cis', 'y24514', 'ストローク数と呼吸数を減らす', '2025-10-30 13:46:50');

-- --------------------------------------------------------

--
-- テーブルの構造 `login_tbl`
--

CREATE TABLE `login_tbl` (
  `id` int(100) NOT NULL,
  `group_id` varchar(100) DEFAULT NULL,
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
(16, '花巻東', 'y24514', '$2y$10$irKSeqOAkgWJ3hAlF3m2ru.SAsNgv7YiSbY7nd5fdbPbFuRlxdSpm', '藤原大輔', '2006-03-03', 170.0, 59.0, 'fly/fr');

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
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `schedule_tbl`
--

CREATE TABLE `schedule_tbl` (
  `id` int(11) NOT NULL,
  `group_id` varchar(50) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `schedule_date` date NOT NULL,
  `title` varchar(100) NOT NULL,
  `memo` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `schedule_tbl`
--

INSERT INTO `schedule_tbl` (`id`, `group_id`, `user_id`, `schedule_date`, `title`, `memo`, `created_at`) VALUES
(1, 'cis', 'y24514', '2026-01-02', '練習試合', NULL, '2025-11-06 04:53:16');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `goal_tbl`
--
ALTER TABLE `goal_tbl`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `goal` (`user_id`),
  ADD KEY `group_id` (`group_id`);

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
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);

--
-- テーブルのインデックス `schedule_tbl`
--
ALTER TABLE `schedule_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_group_id` (`group_id`),
  ADD KEY `schedule_user_id` (`user_id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `goal_tbl`
--
ALTER TABLE `goal_tbl`
  MODIFY `goal_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- テーブルの AUTO_INCREMENT `login_tbl`
--
ALTER TABLE `login_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- テーブルの AUTO_INCREMENT `pi_tbl`
--
ALTER TABLE `pi_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- テーブルの AUTO_INCREMENT `schedule_tbl`
--
ALTER TABLE `schedule_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `goal_tbl`
--
ALTER TABLE `goal_tbl`
  ADD CONSTRAINT `goal` FOREIGN KEY (`user_id`) REFERENCES `login_tbl` (`user_id`),
  ADD CONSTRAINT `goal_group` FOREIGN KEY (`group_id`) REFERENCES `login_tbl` (`group_id`);

--
-- テーブルの制約 `pi_tbl`
--
ALTER TABLE `pi_tbl`
  ADD CONSTRAINT `group_id` FOREIGN KEY (`group_id`) REFERENCES `login_tbl` (`group_id`),
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `login_tbl` (`user_id`);

--
-- テーブルの制約 `schedule_tbl`
--
ALTER TABLE `schedule_tbl`
  ADD CONSTRAINT `schedule_group_id` FOREIGN KEY (`group_id`) REFERENCES `login_tbl` (`group_id`),
  ADD CONSTRAINT `schedule_user_id` FOREIGN KEY (`user_id`) REFERENCES `login_tbl` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
