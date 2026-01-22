-- 既存の sportsdata にテニス(旧 tennis_db)を追記するための増分SQL
-- 対象: MariaDB 10.4.x (XAMPP想定)
-- 1) sportsdata に tennis_* テーブルを作成
-- 2) tennis_db が存在する場合、tennis_db のデータを INSERT IGNORE でコピー
--
-- 実行前に推奨: DBバックアップ

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

USE sportsdata;

-- 1) テーブル作成（存在しなければ）
CREATE TABLE IF NOT EXISTS `tennis_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `saved_by_user_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tennis_games_group_id_match_date` (`group_id`,`match_date`),
  KEY `idx_tennis_games_saved_by` (`saved_by_user_id`,`match_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tennis_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `player_name` varchar(255) NOT NULL,
  `action_type` varchar(255) NOT NULL,
  `score_a` int(11) NOT NULL,
  `score_b` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tennis_actions_game_id` (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tennis_strategies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` varchar(64) DEFAULT NULL,
  `user_id` varchar(64) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `json_data` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tennis_strategies_group_created` (`group_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 1.5) FKを（無ければ）追加
DELIMITER $$
CREATE PROCEDURE sp_add_fk_tennis_actions_game_if_missing()
BEGIN
  DECLARE fk_count INT DEFAULT 0;
  SELECT COUNT(*) INTO fk_count
    FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = 'sportsdata'
     AND TABLE_NAME = 'tennis_actions'
     AND REFERENCED_TABLE_NAME = 'tennis_games'
     AND CONSTRAINT_NAME = 'fk_tennis_actions_game';

  IF fk_count = 0 THEN
    SET @sql = 'ALTER TABLE `tennis_actions` ADD CONSTRAINT `fk_tennis_actions_game` FOREIGN KEY (`game_id`) REFERENCES `tennis_games` (`id`) ON DELETE CASCADE';
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;

CALL sp_add_fk_tennis_actions_game_if_missing();
DROP PROCEDURE sp_add_fk_tennis_actions_game_if_missing;

-- 2) tennis_db が存在する場合のみデータコピー
DELIMITER $$
CREATE PROCEDURE sp_copy_tennis_db_data_if_exists()
BEGIN
  DECLARE tennis_schema_exists INT DEFAULT 0;
  DECLARE has_group_id INT DEFAULT 0;
  DECLARE has_saved_by INT DEFAULT 0;

  SELECT COUNT(*) INTO tennis_schema_exists
    FROM information_schema.SCHEMATA
   WHERE SCHEMA_NAME = 'tennis_db';

  IF tennis_schema_exists = 0 THEN
    -- tennis_db が無ければ何もしない（空ブロック回避のためno-op）
    DO 0;
  ELSE

  -- games の追加列有無（環境差吸収）
  SELECT COUNT(*) INTO has_group_id
    FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = 'tennis_db'
     AND TABLE_NAME = 'games'
     AND COLUMN_NAME = 'group_id';

  SELECT COUNT(*) INTO has_saved_by
    FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = 'tennis_db'
     AND TABLE_NAME = 'games'
     AND COLUMN_NAME = 'saved_by_user_id';

  -- tennis_games
  IF has_group_id = 1 AND has_saved_by = 1 THEN
    SET @sql_games = 'INSERT IGNORE INTO sportsdata.tennis_games (id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, ai_comment, match_date, group_id, saved_by_user_id) '
                 'SELECT id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, ai_comment, match_date, group_id, saved_by_user_id FROM tennis_db.games';
  ELSEIF has_group_id = 1 AND has_saved_by = 0 THEN
    SET @sql_games = 'INSERT IGNORE INTO sportsdata.tennis_games (id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, ai_comment, match_date, group_id, saved_by_user_id) '
                 'SELECT id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, ai_comment, match_date, group_id, NULL FROM tennis_db.games';
  ELSEIF has_group_id = 0 AND has_saved_by = 1 THEN
    SET @sql_games = 'INSERT IGNORE INTO sportsdata.tennis_games (id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, ai_comment, match_date, group_id, saved_by_user_id) '
                 'SELECT id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, ai_comment, match_date, NULL, saved_by_user_id FROM tennis_db.games';
  ELSE
    SET @sql_games = 'INSERT IGNORE INTO sportsdata.tennis_games (id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, ai_comment, match_date, group_id, saved_by_user_id) '
                 'SELECT id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, ai_comment, match_date, NULL, NULL FROM tennis_db.games';
  END IF;

  PREPARE stmt_games FROM @sql_games;
  EXECUTE stmt_games;
  DEALLOCATE PREPARE stmt_games;

  -- tennis_actions
  SET @sql_actions = 'INSERT IGNORE INTO sportsdata.tennis_actions (id, game_id, player_name, action_type, score_a, score_b, created_at) '
                 'SELECT id, game_id, player_name, action_type, score_a, score_b, created_at FROM tennis_db.actions';
  PREPARE stmt_actions FROM @sql_actions;
  EXECUTE stmt_actions;
  DEALLOCATE PREPARE stmt_actions;

  -- tennis_strategies
  SET @sql_strat = 'INSERT IGNORE INTO sportsdata.tennis_strategies (id, group_id, user_id, name, json_data, created_at) '
               'SELECT id, group_id, user_id, name, json_data, created_at FROM tennis_db.tennis_strategies';
  PREPARE stmt_strat FROM @sql_strat;
  EXECUTE stmt_strat;
  DEALLOCATE PREPARE stmt_strat;

  END IF;

END$$
DELIMITER ;

CALL sp_copy_tennis_db_data_if_exists();
DROP PROCEDURE sp_copy_tennis_db_data_if_exists;

COMMIT;
