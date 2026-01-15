<?php
require_once __DIR__ . '/session_bootstrap.php';

/* =====================
   セッションチェック
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: login.php');
    exit;
}

// ローディング表示なし
$showLoader = false;

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
    echo 'データベース接続に失敗しました。';
    exit;
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$userName = $_SESSION['name'] ?? '';

/* =====================
   チャットタイプ判定
===================== */
$chat_type = $_GET['type'] ?? 'group'; // 'group' or 'direct'
$recipient_id = $_GET['recipient'] ?? null;
$chat_group_id = isset($_GET['chat_group_id']) ? (int)$_GET['chat_group_id'] : null;
$recipient_name = '';
$group_name = '';

// グループチャットの場合、グループ情報を取得
if ($chat_type === 'group' && $chat_group_id) {
    $stmt_group = mysqli_prepare($link, "SELECT group_name FROM chat_group_tbl WHERE chat_group_id = ?");
    mysqli_stmt_bind_param($stmt_group, "i", $chat_group_id);
    mysqli_stmt_execute($stmt_group);
    $result_group = mysqli_stmt_get_result($stmt_group);
    if ($row = mysqli_fetch_assoc($result_group)) {
        $group_name = $row['group_name'];
    }
    mysqli_stmt_close($stmt_group);
    
    // グループが見つからない場合
    if (!$group_name) {
        header('Location: chat_list.php');
        exit;
    }
    
    // メンバーかどうか確認
    $member_check = mysqli_prepare($link, "SELECT COUNT(*) as is_member FROM chat_group_member_tbl WHERE chat_group_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($member_check, "is", $chat_group_id, $user_id);
    mysqli_stmt_execute($member_check);
    $result_check = mysqli_stmt_get_result($member_check);
    $check_row = mysqli_fetch_assoc($result_check);
    mysqli_stmt_close($member_check);
    
    if ($check_row['is_member'] == 0) {
        header('Location: chat_list.php');
        exit;
    }
}

// 個人チャットの場合、相手の名前を取得
if ($chat_type === 'direct' && $recipient_id) {
    $stmt_recipient = mysqli_prepare($link, "SELECT name FROM login_tbl WHERE group_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt_recipient, "ss", $group_id, $recipient_id);
    mysqli_stmt_execute($stmt_recipient);
    $result_recipient = mysqli_stmt_get_result($stmt_recipient);
    if ($row = mysqli_fetch_assoc($result_recipient)) {
        $recipient_name = $row['name'];
    }
    mysqli_stmt_close($stmt_recipient);
    
    // 相手が見つからない場合はリダイレクト
    if (!$recipient_name) {
        header('Location: chat_list.php');
        exit;
    }
}

/* =====================
   メッセージ送信
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
            // 個人チャット
            $stmt = mysqli_prepare($link, "INSERT INTO chat_tbl (group_id, user_id, chat_type, recipient_id, message, image_path, image_name) VALUES (?, ?, 'direct', ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssss", $group_id, $user_id, $recipient_id, $message, $image_path, $image_name);
        } else if ($chat_type === 'group' && $chat_group_id) {
            // グループチャット
            $stmt = mysqli_prepare($link, "INSERT INTO chat_tbl (group_id, user_id, chat_type, chat_group_id, message, image_path, image_name) VALUES (?, ?, 'group', ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssisss", $group_id, $user_id, $chat_group_id, $message, $image_path, $image_name);
        } else {
            header('Location: chat_list.php');
            exit;
        }
        
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // リダイレクトでPOST重複送信防止
        $redirect_url = 'chat.php?type=' . $chat_type;
        if ($chat_type === 'direct' && $recipient_id) {
            $redirect_url .= '&recipient=' . urlencode($recipient_id);
        } else if ($chat_type === 'group' && $chat_group_id) {
            $redirect_url .= '&chat_group_id=' . $chat_group_id;
        }
        header('Location: ' . $redirect_url);
        exit;
    }
}

/* =====================
   メッセージ一覧取得
===================== */
$messages = [];

if ($chat_type === 'direct' && $recipient_id) {
    // 個人チャット - 自分と相手のメッセージのみ
    $stmt = mysqli_prepare($link, "
        SELECT c.id, c.user_id, c.message, c.image_path, c.image_name, c.created_at, l.name 
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
    // グループチャット - 特定のグループのメッセージ
    $stmt = mysqli_prepare($link, "
        SELECT c.id, c.user_id, c.message, c.image_path, c.image_name, c.created_at, l.name 
        FROM chat_tbl c 
        LEFT JOIN login_tbl l ON c.group_id = l.group_id AND c.user_id = l.user_id 
        WHERE c.chat_group_id = ? 
        AND c.chat_type = 'group'
        ORDER BY c.created_at ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $chat_group_id);
} else {
    // パラメータ不正
    header('Location: chat_list.php');
    exit;
}

if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
}
mysqli_stmt_close($stmt);

$NAV_BASE = '.';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/chat.html.php';
