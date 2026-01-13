-- テニスデータを group 単位で扱うための列追加
-- tennis_db の games テーブルへ group_id / saved_by_user_id を追加します。

ALTER TABLE games
  ADD COLUMN group_id VARCHAR(64) NULL,
  ADD COLUMN saved_by_user_id VARCHAR(64) NULL;

CREATE INDEX idx_games_group_id_match_date ON games (group_id, match_date);
CREATE INDEX idx_games_saved_by ON games (saved_by_user_id, match_date);
