-- Seed data for basketball feature (sportsdata)
-- Run after basketball_tables.sql

-- 必要なら一度リセット（運用中は注意）
-- TRUNCATE TABLE game_actions;
-- TRUNCATE TABLE games;
-- TRUNCATE TABLE players;
-- TRUNCATE TABLE teams;

START TRANSACTION;

-- Teams
INSERT INTO teams (name) VALUES ('Team A')
  ON DUPLICATE KEY UPDATE name = VALUES(name);
INSERT INTO teams (name) VALUES ('Team B')
  ON DUPLICATE KEY UPDATE name = VALUES(name);

-- 既存IDを取得（同名が既にある場合にも対応）
SET @teamA_id := (SELECT id FROM teams WHERE name = 'Team A' LIMIT 1);
SET @teamB_id := (SELECT id FROM teams WHERE name = 'Team B' LIMIT 1);

-- Players (Team A)
INSERT INTO players (team_id, number, name) VALUES
(@teamA_id, 4, 'A Player 4'),
(@teamA_id, 5, 'A Player 5'),
(@teamA_id, 6, 'A Player 6'),
(@teamA_id, 7, 'A Player 7'),
(@teamA_id, 8, 'A Player 8'),
(@teamA_id, 9, 'A Player 9'),
(@teamA_id, 10, 'A Player 10'),
(@teamA_id, 11, 'A Player 11'),
(@teamA_id, 12, 'A Player 12'),
(@teamA_id, 13, 'A Player 13'),
(@teamA_id, 14, 'A Player 14'),
(@teamA_id, 15, 'A Player 15')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Players (Team B)
INSERT INTO players (team_id, number, name) VALUES
(@teamB_id, 4, 'B Player 4'),
(@teamB_id, 5, 'B Player 5'),
(@teamB_id, 6, 'B Player 6'),
(@teamB_id, 7, 'B Player 7'),
(@teamB_id, 8, 'B Player 8'),
(@teamB_id, 9, 'B Player 9'),
(@teamB_id, 10, 'B Player 10'),
(@teamB_id, 11, 'B Player 11'),
(@teamB_id, 12, 'B Player 12'),
(@teamB_id, 13, 'B Player 13'),
(@teamB_id, 14, 'B Player 14'),
(@teamB_id, 15, 'B Player 15')
ON DUPLICATE KEY UPDATE name = VALUES(name);

COMMIT;
