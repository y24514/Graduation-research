-- 日記を同じ日付で複数登録できるようにする（既存DB用）
-- 対象DB: sportsdata

USE sportsdata;

-- 既存のユニーク制約を削除（同日複数登録を許可）
ALTER TABLE diary_tbl
  DROP INDEX unique_user_date;

-- 検索性能のための非ユニークインデックス（任意だが推奨）
-- すでに同名がある場合はエラーになるので必要に応じてコメントアウトしてください
CREATE INDEX idx_user_date ON diary_tbl (group_id, user_id, diary_date);
