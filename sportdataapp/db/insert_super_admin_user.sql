-- Insert a Super Admin user
--
-- 前提:
-- 1) login_tbl に is_super_admin 列があること（なければ db/add_super_admin_flag.sql を先に実行）
-- 2) パスワードは PHP の password_hash() で生成したハッシュを貼り付けること
--
-- ハッシュ生成例（Windows PowerShell / phpが使える場合）:
--   php -r "echo password_hash('YourStrongPassword!', PASSWORD_DEFAULT);"
-- 重要:
-- - login.php は password_verify() を使うため、password列には「平文」ではなくハッシュが必要です。
-- - $2y$10$... で始まる60文字前後の文字列を“省略せず”にそのまま貼ってください。
--
-- 使い方:
-- 1) 下の SET の値を自分用に変更
-- 2) このSQLを実行

-- 事前チェック（列が無いと Unknown column になります）
-- SHOW COLUMNS FROM login_tbl LIKE 'is_super_admin';

-- 照合順序エラー(#1267)回避: 接続・変数・比較を login_tbl に合わせる
-- （login_tbl が utf8mb4_general_ci の環境を想定）
SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

START TRANSACTION;

-- ====== ここを編集 ======
SET @group_id = 'system';
SET @user_id  = 'host';
SET @name     = 'host';
SET @dob      = '2000-01-01';
SET @height   = 170.0;
SET @weight   = 60.0;
SET @position = '作成者';

-- PHPの password_hash() で生成した値に差し替え
SET @password_hash = 'abcd1234';
-- =======================

-- すでに同じ user_id が居る場合は何もしない
INSERT INTO login_tbl (
  group_id, user_id, password, name, dob, height, weight, position, is_admin, is_super_admin
)
SELECT
  @group_id, @user_id, @password_hash, @name, @dob, @height, @weight, @position, 1, 1
WHERE NOT EXISTS (
  SELECT 1 FROM login_tbl WHERE user_id COLLATE utf8mb4_general_ci = @user_id COLLATE utf8mb4_general_ci LIMIT 1
);

COMMIT;

-- 確認
SELECT id, group_id, user_id, name, is_admin, is_super_admin
FROM login_tbl
WHERE user_id = @user_id
LIMIT 1;

-- =======================
-- パスワードが合わない時（ハッシュを入れ直す）
-- ※ @password_hash を正しい password_hash() の値にしてから実行
--
-- UPDATE login_tbl
-- SET password = @password_hash
-- WHERE group_id = @group_id COLLATE utf8mb4_general_ci
--   AND user_id  = @user_id  COLLATE utf8mb4_general_ci
-- LIMIT 1;

-- =======================
-- もし「複数SQLを一括実行できない」「START TRANSACTION がダメ」等の環境なら、下の“単発INSERT版”を使ってください。
-- ※ @password_hash などは直接値に書き換えて実行します。
--
-- INSERT INTO login_tbl (group_id, user_id, password, name, dob, height, weight, position, is_admin, is_super_admin)
-- VALUES ('system', 'superadmin', '$2y$10$REPLACE_WITH_YOUR_PASSWORD_HASH........................................', 'hostuser', '2000-01-01', 170.0, 60.0, '作成者', 1, 1);
