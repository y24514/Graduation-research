<?php
require_once __DIR__ . '/session_bootstrap.php';

if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    http_response_code(401);
    exit;
}

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'y24514';
$dbPass = getenv('DB_PASS') ?: 'Kr96main0303';
$dbName = getenv('DB_NAME') ?: 'sportdata_db';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    http_response_code(500);
    exit;
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$chat_type = $_GET['type'] ?? '';
$chat_group_id = isset($_GET['chat_group_id']) ? intval($_GET['chat_group_id']) : null;
$recipient_id = $_GET['recipient'] ?? null;
$after_id = isset($_GET['after_id']) ? intval($_GET['after_id']) : 0;

$messages = [];

if ($chat_type === 'direct' && $recipient_id) {
    $afterSql = ($after_id > 0) ? " AND c.id > ?" : "";
    $stmt = mysqli_prepare($link, "
        SELECT c.id, c.user_id, c.message, c.image_path, c.image_name, c.created_at, c.is_deleted, l.name 
        FROM chat_tbl c 
        LEFT JOIN login_tbl l ON c.group_id = l.group_id AND c.user_id = l.user_id 
        WHERE c.group_id = ? 
        AND c.chat_type = 'direct'
        AND (
            (c.user_id = ? AND c.recipient_id = ?) 
            OR (c.user_id = ? AND c.recipient_id = ?)
        )
        {$afterSql}
        ORDER BY c.created_at ASC
    ");
    if ($after_id > 0) {
        mysqli_stmt_bind_param($stmt, "sssssi", $group_id, $user_id, $recipient_id, $recipient_id, $user_id, $after_id);
    } else {
        mysqli_stmt_bind_param($stmt, "sssss", $group_id, $user_id, $recipient_id, $recipient_id, $user_id);
    }
} else if ($chat_type === 'group' && $chat_group_id) {
    $afterSql = ($after_id > 0) ? " AND c.id > ?" : "";
    $stmt = mysqli_prepare($link, "
        SELECT c.id, c.user_id, c.message, c.image_path, c.image_name, c.created_at, c.is_deleted, l.name 
        FROM chat_tbl c 
        LEFT JOIN login_tbl l ON c.group_id = l.group_id AND c.user_id = l.user_id 
        WHERE c.chat_group_id = ? 
        AND c.chat_type = 'group'
        {$afterSql}
        ORDER BY c.created_at ASC
    ");
    if ($after_id > 0) {
        mysqli_stmt_bind_param($stmt, "ii", $chat_group_id, $after_id);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $chat_group_id);
    }
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

foreach ($messages as $msg):
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
$isMyMessage = trim((string)$msg['user_id']) === trim((string)$user_id);
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
                <?php if (!empty($msg['image_path'])): ?>
                <div class="message-image">
                    <img src="<?= htmlspecialchars($msg['image_path'], ENT_QUOTES, 'UTF-8') ?>" 
                         alt="<?= htmlspecialchars($msg['image_name'] ?? '画像', ENT_QUOTES, 'UTF-8') ?>"
                         onclick="openImageModal(this.src)">
                </div>
                <?php endif; ?>
                <?php if (!empty($msg['message'])): ?>
                <?= nl2br(htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')) ?>
                <?php endif; ?>
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
<?php endforeach;
