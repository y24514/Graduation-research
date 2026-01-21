-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2026-01-21 02:47:01
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
-- データベース: `tennis_db`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `actions`
--

CREATE TABLE `actions` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `player_name` varchar(255) NOT NULL,
  `action_type` varchar(255) NOT NULL,
  `score_a` int(11) NOT NULL,
  `score_b` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `actions`
--

INSERT INTO `actions` (`id`, `game_id`, `player_name`, `action_type`, `score_a`, `score_b`, `created_at`) VALUES
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
-- テーブルの構造 `games`
--

CREATE TABLE `games` (
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
  `match_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `games`
--

INSERT INTO `games` (`id`, `team_a`, `team_b`, `games_a`, `games_b`, `player_a1`, `player_a2`, `player_b1`, `player_b2`, `ai_comment`, `match_date`) VALUES
(1, 'teamA', 'teamB', 3, 0, '田中', '', '佐藤', '', NULL, '2026-01-08 09:31:41'),
(2, 'teamA', 'teamB', 3, 0, '田中', '', '佐藤', '', NULL, '2026-01-08 10:14:59'),
(3, 'teamA', 'teamB', 3, 0, '田中', '', '佐藤', '', NULL, '2026-01-08 10:15:42'),
(4, 'くまさんチーム', 'うさぎさんチーム', 3, 1, 'くまさん', '', 'うさぎさん', '', NULL, '2026-01-08 13:11:20');

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
-- テーブルのインデックス `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_actions_game_id` (`game_id`);

--
-- テーブルのインデックス `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

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
-- テーブルの AUTO_INCREMENT `actions`
--
ALTER TABLE `actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- テーブルの AUTO_INCREMENT `games`
--
ALTER TABLE `games`
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
-- テーブルの制約 `actions`
--
ALTER TABLE `actions`
  ADD CONSTRAINT `fk_actions_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
