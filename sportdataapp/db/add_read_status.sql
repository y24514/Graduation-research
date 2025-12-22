-- chat_tblにIDカラムを追加（まだない場合）
ALTER TABLE chat_tbl ADD COLUMN IF NOT EXISTS id INT AUTO_INCREMENT PRIMARY KEY FIRST;

-- チャットの既読状態を管理するテーブル
DROP TABLE IF EXISTS chat_read_status_tbl;
CREATE TABLE chat_read_status_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id VARCHAR(100) NOT NULL,
    user_id VARCHAR(50) NOT NULL,
    chat_type ENUM('direct', 'group') NOT NULL,
    chat_group_id INT NULL,
    recipient_id VARCHAR(50) NULL,
    last_read_message_id INT NULL,
    last_read_at DATETIME NULL,
    UNIQUE KEY unique_read_status (user_id, group_id, chat_type, chat_group_id, recipient_id),
    INDEX idx_user_type (user_id, group_id, chat_type),
    INDEX idx_chat_group (chat_group_id),
    INDEX idx_recipient (recipient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
