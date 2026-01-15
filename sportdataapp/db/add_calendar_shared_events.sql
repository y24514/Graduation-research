-- 管理者からグループ全体に共有できるカレンダー予定
-- calendar_tbl に is_shared フラグを追加します（0:個人 1:共有）

ALTER TABLE `calendar_tbl`
  ADD COLUMN `is_shared` TINYINT(1) NOT NULL DEFAULT 0 AFTER `enddate`,
  ADD INDEX `idx_calendar_shared` (`group_id`, `is_shared`, `startdate`);
