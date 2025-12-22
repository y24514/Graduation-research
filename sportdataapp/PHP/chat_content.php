<?php
session_start();

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
    
    if (!empty($message)) {
        if ($chat_type === 'direct' && $recipient_id) {
            $stmt = mysqli_prepare($link, "INSERT INTO chat_tbl (group_id, user_id, chat_type, recipient_id, message) VALUES (?, ?, 'direct', ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $group_id, $user_id, $recipient_id, $message);
        } else if ($chat_type === 'group' && $chat_group_id) {
            $stmt = mysqli_prepare($link, "INSERT INTO chat_tbl (group_id, user_id, chat_type, chat_group_id, message) VALUES (?, ?, 'group', ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssis", $group_id, $user_id, $chat_group_id, $message);
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
        // デバッグ出力を有効化
        echo "<!-- DEBUG: msg_user_id=[" . $msg['user_id'] . "] session_user_id=[" . $user_id . "] isMyMessage=" . ($isMyMessage ? 'TRUE' : 'FALSE') . " -->\n";
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
