<?php
require_once __DIR__ . '/session_bootstrap.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    echo json_encode(['success' => false, 'error' => 'ログインが必要です']);
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;

if ($message_id <= 0) {
    echo json_encode(['success' => false, 'error' => '無効なメッセージIDです']);
    exit;
}

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'sportsdata_user';
$dbPass = getenv('DB_PASS') ?: 'fujidai14';
$dbName = getenv('DB_NAME') ?: 'sportsdata';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB接続エラー']);
    exit;
}
$conn->set_charset('utf8mb4');

// メッセージが自分のものか確認
$stmt = $conn->prepare("SELECT user_id FROM chat_tbl WHERE id = ? AND group_id = ?");
$stmt->bind_param("is", $message_id, $group_id);
$stmt->execute();
$result = $stmt->get_result();
$message = $result->fetch_assoc();
$stmt->close();

if (!$message || $message['user_id'] !== $user_id) {
    echo json_encode(['success' => false, 'error' => '削除権限がありません']);
    $conn->close();
    exit;
}

// メッセージを論理削除（削除フラグを立てる）
$stmt = $conn->prepare("UPDATE chat_tbl SET is_deleted = 1, deleted_at = NOW() WHERE id = ? AND group_id = ? AND user_id = ?");
$stmt->bind_param("iss", $message_id, $group_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => '削除に失敗しました']);
}

$stmt->close();
$conn->close();
