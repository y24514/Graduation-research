-- 日記に管理者フィードバックを付けられるようにする拡張
-- 前提: diary_tbl が存在すること
-- 推奨: 先に db/add_diary_submit_to_admin.sql を適用（提出機能）

ALTER TABLE diary_tbl
  ADD COLUMN admin_feedback TEXT NULL AFTER submitted_at,
  ADD COLUMN admin_feedback_at DATETIME NULL AFTER admin_feedback,
  ADD COLUMN admin_feedback_by_user_id VARCHAR(255) NULL AFTER admin_feedback_at,
  ADD INDEX idx_admin_feedback (group_id, admin_feedback_at);
