-- Basketball feature tables for sportdata_db
-- Charset/engine aligned with the rest of the project.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `teams` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_teams_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `players` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `team_id` INT NOT NULL,
  `number` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_players_team_id` (`team_id`),
  UNIQUE KEY `uq_players_team_number` (`team_id`, `number`),
  CONSTRAINT `fk_players_team_id` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `games` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `team_a_name` VARCHAR(100) NOT NULL,
  `team_b_name` VARCHAR(100) NOT NULL,
  `score_a` INT NOT NULL DEFAULT 0,
  `score_b` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_games_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `game_actions` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `game_id` BIGINT NOT NULL,
  `quarter` TINYINT NOT NULL,
  `team` CHAR(1) NOT NULL,
  `player_id` INT NOT NULL,
  `player_name` VARCHAR(100) NOT NULL,
  `action_type` VARCHAR(20) NOT NULL,
  `point` TINYINT NOT NULL DEFAULT 0,
  `result` VARCHAR(20) NOT NULL DEFAULT 'success',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_game_actions_game_id` (`game_id`),
  KEY `idx_game_actions_player_id` (`player_id`),
  KEY `idx_game_actions_quarter_team` (`quarter`, `team`),
  CONSTRAINT `fk_game_actions_game_id` FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_game_actions_team` CHECK (`team` IN ('A','B'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
