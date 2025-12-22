-- 日記テーブル
CREATE TABLE IF NOT EXISTS diary_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id VARCHAR(50) NOT NULL,
    user_id VARCHAR(50) NOT NULL,
    diary_date DATE NOT NULL,
    title VARCHAR(200),
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (group_id, user_id),
    INDEX idx_date (diary_date),
    UNIQUE KEY unique_user_date (group_id, user_id, diary_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- チャットグループテーブル
CREATE TABLE IF NOT EXISTS chat_group_tbl (
    chat_group_id INT AUTO_INCREMENT PRIMARY KEY,
    group_id VARCHAR(50) NOT NULL,
    group_name VARCHAR(100) NOT NULL,
    group_description TEXT,
    created_by VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_group (group_id),
    INDEX idx_creator (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- チャットグループメンバーテーブル
CREATE TABLE IF NOT EXISTS chat_group_member_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_group_id INT NOT NULL,
    group_id VARCHAR(50) NOT NULL,
    user_id VARCHAR(50) NOT NULL,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_group_id) REFERENCES chat_group_tbl(chat_group_id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_member (chat_group_id, user_id),
    INDEX idx_user (user_id),
    INDEX idx_group (chat_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- チャットテーブル（個人・グループ対応）
CREATE TABLE IF NOT EXISTS chat_tbl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id VARCHAR(50) NOT NULL,
    user_id VARCHAR(50) NOT NULL,
    chat_type ENUM('group', 'direct') DEFAULT 'group',
    chat_group_id INT DEFAULT NULL,
    recipient_id VARCHAR(50) DEFAULT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_group_id) REFERENCES chat_group_tbl(chat_group_id) ON DELETE CASCADE,
    INDEX idx_group (group_id),
    INDEX idx_chat_group (chat_group_id),
    INDEX idx_direct (user_id, recipient_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
