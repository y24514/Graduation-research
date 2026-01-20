-- 練習メニューの「種類」（Kick/Pull等）をグループ単位で管理
CREATE TABLE IF NOT EXISTS swim_practice_kind_tbl (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id VARCHAR(64) NOT NULL,
  kind_name VARCHAR(64) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_group_kind (group_id, kind_name),
  INDEX idx_group (group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
