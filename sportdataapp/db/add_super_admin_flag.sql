-- Super Admin flag
-- Adds a global super admin role that can view all groups.

ALTER TABLE login_tbl
  ADD COLUMN is_super_admin TINYINT(1) NOT NULL DEFAULT 0;

CREATE INDEX idx_login_tbl_is_super_admin ON login_tbl (is_super_admin);
