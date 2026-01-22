-- 実行DB: sportsdata
-- login_tbl に「種目」(sport) 列を追加します

USE sportsdata;

ALTER TABLE login_tbl
  ADD COLUMN sport VARCHAR(20) NULL DEFAULT NULL AFTER position;

CREATE INDEX idx_login_tbl_sport ON login_tbl (sport);
