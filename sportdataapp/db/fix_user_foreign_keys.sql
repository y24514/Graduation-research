-- Fix broken foreign keys that reference login_tbl by group_id or user_id alone.
-- The current FK design blocks deleting a single user because many tables reference
-- login_tbl(group_id) even though multiple rows share the same group_id.
--
-- How to use:
-- 1) Backup your DB.
-- 2) Run this script in phpMyAdmin (SQL tab) or mysql client.
--
-- Target DB: sportdata_db

START TRANSACTION;

-- 1) Parent key for per-group per-user identity
-- Ensure group_id + user_id is unique in login_tbl so other tables can reference it safely.
ALTER TABLE login_tbl
  ADD UNIQUE KEY uniq_login_group_user (group_id, user_id);

-- 2) calendar_tbl
ALTER TABLE calendar_tbl
  DROP FOREIGN KEY calendar_group,
  DROP FOREIGN KEY calendar_user;

ALTER TABLE calendar_tbl
  ADD CONSTRAINT fk_calendar_group_user
    FOREIGN KEY (group_id, user_id)
    REFERENCES login_tbl (group_id, user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- 3) goal_tbl
ALTER TABLE goal_tbl
  DROP FOREIGN KEY goal_group,
  DROP FOREIGN KEY goal_user;

ALTER TABLE goal_tbl
  ADD KEY idx_goal_group_user (group_id, user_id);

ALTER TABLE goal_tbl
  ADD CONSTRAINT fk_goal_group_user
    FOREIGN KEY (group_id, user_id)
    REFERENCES login_tbl (group_id, user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- 4) pi_tbl
ALTER TABLE pi_tbl
  DROP FOREIGN KEY pi_group,
  DROP FOREIGN KEY pi_user;

ALTER TABLE pi_tbl
  ADD KEY idx_pi_group_user (group_id, user_id);

ALTER TABLE pi_tbl
  ADD CONSTRAINT fk_pi_group_user
    FOREIGN KEY (group_id, user_id)
    REFERENCES login_tbl (group_id, user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- 5) swim_best_tbl
ALTER TABLE swim_best_tbl
  DROP FOREIGN KEY swimbest_group_id,
  DROP FOREIGN KEY swimbest_user_id;

ALTER TABLE swim_best_tbl
  ADD CONSTRAINT fk_swim_best_group_user
    FOREIGN KEY (group_id, user_id)
    REFERENCES login_tbl (group_id, user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- 6) swim_tbl
ALTER TABLE swim_tbl
  DROP FOREIGN KEY swim_group_id,
  DROP FOREIGN KEY swim_user_id;

ALTER TABLE swim_tbl
  ADD KEY idx_swim_group_user (group_id, user_id);

ALTER TABLE swim_tbl
  ADD CONSTRAINT fk_swim_group_user
    FOREIGN KEY (group_id, user_id)
    REFERENCES login_tbl (group_id, user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

COMMIT;
