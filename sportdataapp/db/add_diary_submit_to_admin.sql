-- 日記を「管理者に提出」できるようにする拡張
-- 実行前に必ずバックアップを取ってください。

ALTER TABLE diary_tbl
  ADD COLUMN submitted_to_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER content,
  ADD COLUMN submitted_at DATETIME NULL AFTER submitted_to_admin,
  ADD INDEX idx_submitted (group_id, submitted_to_admin, submitted_at);
