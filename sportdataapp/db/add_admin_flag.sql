-- 管理者フラグ追加（コーチ/先生向け）
-- login_tbl に is_admin を追加します。
-- 既に存在する場合は何もしないように、事前に確認してから実行してください。

ALTER TABLE login_tbl
  ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0;
