<?php
require_once __DIR__ . '/session_bootstrap.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    echo json_encode(['success' => false, 'error' => 'ログインが必要です']);
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$chat_group_id = isset($_POST['chat_group_id']) ? intval($_POST['chat_group_id']) : 0;

if ($chat_group_id <= 0) {
    echo json_encode(['success' => false, 'error' => '無効なパラメータです']);
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

// グループの作成者か確認
$stmt = $conn->prepare("SELECT created_by FROM chat_group_tbl WHERE chat_group_id = ? AND group_id = ?");
$stmt->bind_param("is", $chat_group_id, $group_id);
$stmt->execute();
$result = $stmt->get_result();
$group = $result->fetch_assoc();
$stmt->close();

if (!$group || $group['created_by'] !== $user_id) {
    echo json_encode(['success' => false, 'error' => '権限がありません']);
    $conn->close();
    exit;
}

// トランザクション開始
$conn->begin_transaction();

try {
    // 関連するメッセージを削除
    $stmt = $conn->prepare("DELETE FROM chat_tbl WHERE chat_group_id = ? AND group_id = ?");
    $stmt->bind_param("is", $chat_group_id, $group_id);
    $stmt->execute();
    $stmt->close();
    
    // 既読状態を削除
    $stmt = $conn->prepare("DELETE FROM chat_read_status_tbl WHERE chat_group_id = ? AND group_id = ?");
    $stmt->bind_param("is", $chat_group_id, $group_id);
    $stmt->execute();
    $stmt->close();
    
    // メンバーを削除
    $stmt = $conn->prepare("DELETE FROM chat_group_member_tbl WHERE chat_group_id = ? AND group_id = ?");
    $stmt->bind_param("is", $chat_group_id, $group_id);
    $stmt->execute();
    $stmt->close();
    
    // グループを削除
    $stmt = $conn->prepare("DELETE FROM chat_group_tbl WHERE chat_group_id = ? AND group_id = ?");
    $stmt->bind_param("is", $chat_group_id, $group_id);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => '削除に失敗しました']);
}

$conn->close();
