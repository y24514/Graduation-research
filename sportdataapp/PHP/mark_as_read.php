<?php
require_once __DIR__ . '/session_bootstrap.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$chat_type = $_POST['chat_type'] ?? '';
$chat_group_id = isset($_POST['chat_group_id']) ? intval($_POST['chat_group_id']) : null;
$recipient_id = $_POST['recipient_id'] ?? null;

// DB接続
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'sportdata_db';
$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    exit(json_encode(['success' => false, 'error' => 'Database connection failed: ' . mysqli_connect_error()]));
}
mysqli_set_charset($link, 'utf8mb4');

try {
    // 最新メッセージIDを取得
    if ($chat_type === 'direct') {
        $stmt = mysqli_prepare($link, "
            SELECT MAX(id) as last_id 
            FROM chat_tbl 
            WHERE group_id = ? 
            AND chat_type = 'direct' 
            AND ((user_id = ? AND recipient_id = ?) OR (user_id = ? AND recipient_id = ?))
        ");
        mysqli_stmt_bind_param($stmt, "sssss", $group_id, $user_id, $recipient_id, $recipient_id, $user_id);
    } else {
        $stmt = mysqli_prepare($link, "
            SELECT MAX(id) as last_id 
            FROM chat_tbl 
            WHERE group_id = ? 
            AND chat_type = 'group' 
            AND chat_group_id = ?
        ");
        mysqli_stmt_bind_param($stmt, "si", $group_id, $chat_group_id);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $last_message_id = $row['last_id'] ?? null;
    mysqli_stmt_close($stmt);
    
    if ($last_message_id) {
        // 既読状態を更新または挿入
        $stmt = mysqli_prepare($link, "
            INSERT INTO chat_read_status_tbl 
            (group_id, user_id, chat_type, chat_group_id, recipient_id, last_read_message_id, last_read_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            last_read_message_id = GREATEST(last_read_message_id, VALUES(last_read_message_id)),
            last_read_at = NOW()
        ");
        mysqli_stmt_bind_param($stmt, "sssisi", $group_id, $user_id, $chat_type, $chat_group_id, $recipient_id, $last_message_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($link);
            exit(json_encode(['success' => false, 'error' => 'Insert failed: ' . mysqli_error($link)]));
        }
        mysqli_stmt_close($stmt);
    }
    
    echo json_encode([
        'success' => true, 
        'debug' => [
            'user_id' => $user_id,
            'chat_type' => $chat_type,
            'chat_group_id' => $chat_group_id,
            'recipient_id' => $recipient_id,
            'last_message_id' => $last_message_id
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

mysqli_close($link);
