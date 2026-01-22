-- バスケ作戦ボード保存用テーブル
-- 実行DB: sportsdata

CREATE TABLE IF NOT EXISTS `basketball_strategies` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `group_id` VARCHAR(64) NULL,
  `user_id` VARCHAR(64) NULL,
  `name` VARCHAR(255) NOT NULL,
  `json_data` LONGTEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_basketball_strategies_group_created` (`group_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;