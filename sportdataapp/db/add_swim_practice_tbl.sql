-- 実行DB: sportsdata
-- 水泳: 練習メニュー作成用テーブル

USE sportsdata;

CREATE TABLE IF NOT EXISTS swim_practice_tbl (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id VARCHAR(100) NOT NULL,
  user_id VARCHAR(50) NOT NULL,
  practice_date DATE NOT NULL,
  title VARCHAR(100) NOT NULL,
  menu_text TEXT NULL,
  memo TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_swim_practice_user_date (group_id, user_id, practice_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
