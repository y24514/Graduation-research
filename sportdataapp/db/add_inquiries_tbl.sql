-- お問い合わせ（バグ報告/改善要望）
-- 実行前に必ずバックアップを取ってください。

CREATE TABLE IF NOT EXISTS `inquiries_tbl` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `group_id` VARCHAR(100) NOT NULL,
  `user_id` VARCHAR(50) NOT NULL,
  `category` VARCHAR(20) NOT NULL,
  `subject` VARCHAR(120) NOT NULL,
  `message` TEXT NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0:未対応 1:返信済み',
  `response` TEXT NULL,
  `responded_by_user_id` VARCHAR(50) NULL,
  `responded_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inquiries_group_status_created` (`group_id`, `status`, `created_at`),
  KEY `idx_inquiries_user_created` (`group_id`, `user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
