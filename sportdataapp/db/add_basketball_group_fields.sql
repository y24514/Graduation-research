-- バスケデータを group 単位で扱うための列追加
-- sportsdata の games テーブルへ group_id / saved_by_user_id を追加します。

ALTER TABLE games
  ADD COLUMN group_id VARCHAR(64) NULL,
  ADD COLUMN saved_by_user_id VARCHAR(64) NULL;

CREATE INDEX idx_games_group_id_created_at ON games (group_id, created_at);
CREATE INDEX idx_games_saved_by ON games (saved_by_user_id, created_at);
