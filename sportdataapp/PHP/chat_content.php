<?php
require_once __DIR__ . '/session_bootstrap.php';

/* =====================
   セッションチェック
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

/* =====================
   DB接続
===================== */
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'y24514';
$dbPass = getenv('DB_PASS') ?: 'Kr96main0303';
$dbName = getenv('DB_NAME') ?: 'sportdata_db';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    error_log('DB connect error: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Database connection failed');
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];

/* =====================
   パラメータ取得
===================== */
$chat_type = $_GET['type'] ?? '';
$chat_group_id = isset($_GET['chat_group_id']) ? intval($_GET['chat_group_id']) : null;
$recipient_id = $_GET['recipient'] ?? null;

// パラメータ検証
if ($chat_type === 'direct' && !$recipient_id) {
    http_response_code(400);
    exit('Invalid parameters');
}
if ($chat_type === 'group' && !$chat_group_id) {
    http_response_code(400);
    exit('Invalid parameters');
}

/* =====================
   グループチャットの権限確認
===================== */
if ($chat_type === 'group') {
    $stmt = mysqli_prepare($link, "SELECT 1 FROM chat_group_member_tbl WHERE chat_group_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "is", $chat_group_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) === 0) {
        http_response_code(403);
        exit('Forbidden');
    }
    mysqli_stmt_close($stmt);
    
    // グループ名を取得
    $stmt = mysqli_prepare($link, "SELECT group_name FROM chat_group_tbl WHERE chat_group_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $chat_group_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $group = mysqli_fetch_assoc($result);
    $chat_title = $group['group_name'] ?? 'グループ';
    mysqli_stmt_close($stmt);
} else {
    // 相手の名前を取得
    $stmt = mysqli_prepare($link, "SELECT name FROM login_tbl WHERE group_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ss", $group_id, $recipient_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $recipient = mysqli_fetch_assoc($result);
    $chat_title = $recipient['name'] ?? '不明';
    mysqli_stmt_close($stmt);
}

/* =====================
   メッセージ送信処理
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $message = trim($_POST['message']);
    $image_path = null;
    $image_name = null;
    
    // 画像アップロード処理
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/chat_images/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $original_name = $_FILES['image']['name'];
        
        // バリデーション
        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $new_filename = uniqid('chat_') . '_' . time() . '.' . $extension;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $target_path)) {
                $image_path = '../uploads/chat_images/' . $new_filename;
                $image_name = $original_name;
            }
        }
    }
    
    // メッセージまたは画像が存在する場合のみ送信
    if (!empty($message) || !empty($image_path)) {
        if ($chat_type === 'direct' && $recipient_id) {
            $stmt = mysqli_prepare($link, "INSERT INTO chat_tbl (group_id, user_id, chat_type, recipient_id, message, image_path, image_name) VALUES (?, ?, 'direct', ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssss", $group_id, $user_id, $recipient_id, $message, $image_path, $image_name);
        } else if ($chat_type === 'group' && $chat_group_id) {
            $stmt = mysqli_prepare($link, "INSERT INTO chat_tbl (group_id, user_id, chat_type, chat_group_id, message, image_path, image_name) VALUES (?, ?, 'group', ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssisss", $group_id, $user_id, $chat_group_id, $message, $image_path, $image_name);
        }
        
        if (isset($stmt)) {
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

/* =====================
   メッセージ一覧取得
===================== */
$messages = [];

if ($chat_type === 'direct' && $recipient_id) {
    $stmt = mysqli_prepare($link, "
        SELECT c.id, c.user_id, c.message, c.created_at, c.is_deleted, l.name 
        FROM chat_tbl c 
        LEFT JOIN login_tbl l ON c.group_id = l.group_id AND c.user_id = l.user_id 
        WHERE c.group_id = ? 
        AND c.chat_type = 'direct'
        AND (
            (c.user_id = ? AND c.recipient_id = ?) 
            OR (c.user_id = ? AND c.recipient_id = ?)
        )
        ORDER BY c.created_at ASC
    ");
    mysqli_stmt_bind_param($stmt, "sssss", $group_id, $user_id, $recipient_id, $recipient_id, $user_id);
} else if ($chat_type === 'group' && $chat_group_id) {
    $stmt = mysqli_prepare($link, "
        SELECT c.id, c.user_id, c.message, c.created_at, c.is_deleted, l.name 
        FROM chat_tbl c 
        LEFT JOIN login_tbl l ON c.group_id = l.group_id AND c.user_id = l.user_id 
        WHERE c.chat_group_id = ? 
        AND c.chat_type = 'group'
        ORDER BY c.created_at ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $chat_group_id);
}

if (isset($stmt) && mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        // 既読状態をチェック（自分のメッセージのみ）
        $row['is_read'] = false;
        if (trim((string)$row['user_id']) === trim((string)$user_id)) {
            if ($chat_type === 'direct') {
                // DMの場合、相手が読んだかどうか
                $read_stmt = mysqli_prepare($link, "
                    SELECT last_read_message_id 
                    FROM chat_read_status_tbl 
                    WHERE user_id = ? 
                    AND group_id = ? 
                    AND chat_type = 'direct' 
                    AND recipient_id = ?
                    AND last_read_message_id >= ?
                ");
                mysqli_stmt_bind_param($read_stmt, "sssi", $recipient_id, $group_id, $user_id, $row['id']);
                mysqli_stmt_execute($read_stmt);
                $read_result = mysqli_stmt_get_result($read_stmt);
                $row['is_read'] = mysqli_num_rows($read_result) > 0;
                mysqli_stmt_close($read_stmt);
            } else if ($chat_type === 'group') {
                // グループの場合、少なくとも1人以上が読んだかどうか
                $read_stmt = mysqli_prepare($link, "
                    SELECT COUNT(DISTINCT user_id) as read_count
                    FROM chat_read_status_tbl 
                    WHERE group_id = ? 
                    AND chat_type = 'group' 
                    AND chat_group_id = ?
                    AND user_id != ?
                    AND last_read_message_id >= ?
                ");
                mysqli_stmt_bind_param($read_stmt, "sisi", $group_id, $chat_group_id, $user_id, $row['id']);
                mysqli_stmt_execute($read_stmt);
                $read_result = mysqli_stmt_get_result($read_stmt);
                $read_row = mysqli_fetch_assoc($read_result);
                $row['is_read'] = ($read_row['read_count'] ?? 0) > 0;
                mysqli_stmt_close($read_stmt);
            }
        }
        $messages[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>

<!-- チャットメインコンテンツ -->
<div class="chat-main-header">
    <h1 class="chat-main-title"><?= htmlspecialchars($chat_title, ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if ($chat_type === 'group'): ?>
    <a href="#" class="settings-link" onclick="loadGroupSettings(<?= $chat_group_id ?>); return false;" title="グループ設定">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        </svg>
    </a>
    <?php endif; ?>
</div>

<div class="chat-messages-area" id="chatMessages">
    <?php if (empty($messages)): ?>
    <div class="empty-chat">
        <p>まだメッセージがありません。<br>最初のメッセージを送信しましょう！</p>
    </div>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
        <?php 
        // デバッグ: user_id比較
        $isMyMessage = trim((string)$msg['user_id']) === trim((string)$user_id);
        $icon = null;
        if (!empty($group_id) && !empty($msg['user_id'])) {
            require_once __DIR__ . '/user_icon_helper.php';
            static $iconCache = [];
            $cacheKey = (string)$group_id . '|' . (string)$msg['user_id'];
            if (!array_key_exists($cacheKey, $iconCache)) {
                $iconCache[$cacheKey] = sportdata_find_user_icon((string)$group_id, (string)$msg['user_id']);
            }
            $icon = $iconCache[$cacheKey];
        }
        // デバッグ出力を有効化
        echo "<!-- DEBUG: msg_user_id=[" . $msg['user_id'] . "] session_user_id=[" . $user_id . "] isMyMessage=" . ($isMyMessage ? 'TRUE' : 'FALSE') . " -->\n";
        ?>
        <div class="message-item <?= $isMyMessage ? 'my-message' : 'other-message' ?>" data-message-id="<?= (int)$msg['id'] ?>">
            <?php if (!$isMyMessage): ?>
            <div class="message-avatar">
                <?php if (!empty($icon['url'])): ?>
                    <img src="<?= htmlspecialchars($icon['url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($msg['name'] ?? 'ユーザー', ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <?= mb_substr($msg['name'] ?? '?', 0, 1, 'UTF-8') ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="message-content">
                <?php if (!$isMyMessage): ?>
                <div class="message-sender"><?= htmlspecialchars($msg['name'] ?? '不明', ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <div class="message-bubble-wrapper">
                    <?php if (!empty($msg['is_deleted']) && $msg['is_deleted'] == 1): ?>
                    <div class="message-bubble deleted-message">
                        メッセージは取り消されました
                    </div>
                    <?php else: ?>
                    <div class="message-bubble" <?= $isMyMessage ? 'onclick="toggleDeleteButton(this)"' : '' ?>>
                        <?= nl2br(htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')) ?>
                    </div>
                    <?php if ($isMyMessage): ?>
                    <button class="message-delete-btn" onclick="event.stopPropagation(); deleteMessage(<?= $msg['id'] ?>)" title="削除">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="message-time">
                    <?= date('H:i', strtotime($msg['created_at'])) ?>
                    <?php if ($isMyMessage && isset($msg['is_read']) && $msg['is_read']): ?>
                    <span class="read-status">既読</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="chat-input-area">
    <form class="chat-form" id="chatForm">
        <input type="hidden" name="send_message" value="1">
        <div class="input-wrapper">
            <textarea 
                id="message" 
                name="message" 
                placeholder="メッセージを入力... (Shift+Enterで改行、Enterで送信)" 
                rows="1"
                required
            ></textarea>
            <button type="submit" class="btn-send" id="sendBtn">送信</button>
        </div>
    </form>
</div>
