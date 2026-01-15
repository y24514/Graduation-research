<?php
require_once __DIR__ . '/session_bootstrap.php';

// データベース接続情報
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sportdata_db";

header('Content-Type: application/json');

// エラーログを有効化
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です']);
    exit;
}

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];

// POSTデータの取得
$group_name = isset($_POST['group_name']) ? trim($_POST['group_name']) : '';
$group_description = isset($_POST['group_description']) ? trim($_POST['group_description']) : '';
$selected_members = isset($_POST['members']) ? $_POST['members'] : [];

// バリデーション
if (empty($group_name)) {
    echo json_encode(['success' => false, 'message' => 'グループ名を入力してください']);
    exit;
}

// データベース接続
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'データベース接続エラー: ' . $conn->connect_error]);
    exit;
}

$conn->set_charset("utf8mb4");

try {
    // トランザクション開始
    $conn->begin_transaction();
    
    // グループを作成
    $stmt = $conn->prepare("INSERT INTO chat_group_tbl (group_name, group_description, created_by, group_id) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception('プリペアドステートメント作成エラー: ' . $conn->error);
    }
    
    $stmt->bind_param("ssss", $group_name, $group_description, $user_id, $group_id);
    
    if (!$stmt->execute()) {
        throw new Exception('グループの作成に失敗しました: ' . $stmt->error);
    }
    
    $chat_group_id = $conn->insert_id;
    $stmt->close();
    
    // 作成者を自動的にメンバーとして追加
    $stmt = $conn->prepare("INSERT INTO chat_group_member_tbl (chat_group_id, user_id, group_id) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception('メンバー追加用ステートメント作成エラー: ' . $conn->error);
    }
    
    $stmt->bind_param("iss", $chat_group_id, $user_id, $group_id);
    if (!$stmt->execute()) {
        throw new Exception('作成者のメンバー追加に失敗: ' . $stmt->error);
    }
    $stmt->close();
    
    // 選択されたメンバーを追加
    if (!empty($selected_members)) {
        $stmt = $conn->prepare("INSERT INTO chat_group_member_tbl (chat_group_id, user_id, group_id) VALUES (?, ?, ?)");
        
        foreach ($selected_members as $member_user_id) {
            // 作成者の重複を避ける
            if ($member_user_id !== $user_id) {
                $stmt->bind_param("iss", $chat_group_id, $member_user_id, $group_id);
                if (!$stmt->execute()) {
                    error_log('メンバー追加エラー: ' . $stmt->error);
                }
            }
        }
        
        $stmt->close();
    }
    
    // コミット
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'グループを作成しました',
        'chat_group_id' => $chat_group_id
    ]);
    
} catch (Exception $e) {
    // ロールバック
    $conn->rollback();
    
    error_log('グループ作成エラー: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
