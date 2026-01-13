-- 管理者権限付与の申請テーブル
-- 目的: 新規登録時などに「管理者権限を希望」申請を保存し、スーパー管理者が承認/却下できるようにする

CREATE TABLE IF NOT EXISTS `admin_role_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `group_id` VARCHAR(50) NOT NULL,
  `user_id` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `actioned_by` VARCHAR(50) DEFAULT NULL,
  `actioned_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_group_status_time` (`group_id`, `status`, `requested_at`),
  KEY `idx_user_group` (`group_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
