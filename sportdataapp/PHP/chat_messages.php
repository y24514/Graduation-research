<?php
session_start();

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

$messages = [];

if ($chat_type === 'direct' && $recipient_id) {
    $stmt = mysqli_prepare($link, "
        SELECT c.id, c.user_id, c.message, c.created_at, l.name 
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
        SELECT c.id, c.user_id, c.message, c.created_at, l.name 
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
        $messages[] = $row;
    }
    mysqli_stmt_close($stmt);
}

foreach ($messages as $msg):
$isMyMessage = trim((string)$msg['user_id']) === trim((string)$user_id);
?>
<div class="message-item <?= $isMyMessage ? 'my-message' : 'other-message' ?>">
    <?php if (!$isMyMessage): ?>
    <div class="message-avatar">
        <?= mb_substr($msg['name'] ?? '?', 0, 1, 'UTF-8') ?>
    </div>
    <?php endif; ?>
    <div class="message-content">
        <?php if (!$isMyMessage): ?>
        <div class="message-sender"><?= htmlspecialchars($msg['name'] ?? '不明', ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <div class="message-bubble">
            <?= nl2br(htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')) ?>
        </div>
        <div class="message-time"><?= date('H:i', strtotime($msg['created_at'])) ?></div>
    </div>
</div>
<?php endforeach;
