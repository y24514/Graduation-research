-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2025-11-20 08:03:15
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
(4, 'cis', 'y24514', 'テスト', 'てすと', '2025-11-02', '2025-11-03', '2025-11-20 14:02:06');

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
(4, 'cis', 'y24514', 'ストローク数を減らす', '2025-11-20 09:46:14');

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

--
-- テーブルのデータのダンプ `pi_tbl`
--

INSERT INTO `pi_tbl` (`id`, `group_id`, `user_id`, `height`, `weight`, `injury`, `sleeptime`, `create_at`) VALUES
(24, 'cis', 'y24514', 170.0, 59.6, '', '08:00:00', '2025-11-17 11:14:36'),
(29, 'cis', 'y24514', 170.0, 59.8, '左腕骨折', '09:00:00', '2025-11-17 11:27:34'),
(31, 'cis', 'y24514', 172.0, 64.6, '', '05:00:00', '2025-11-18 09:43:57');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `calendar_tbl`
--
ALTER TABLE `calendar_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`,`user_id`),
  ADD KEY `calendar_user` (`user_id`);

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
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `calendar_tbl`
--
ALTER TABLE `calendar_tbl`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
