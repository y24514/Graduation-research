<?php
require_once __DIR__ . '/session_bootstrap.php';

/* =====================
   セッションチェック
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: login.php');
    exit;
}

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

$success_message = '';
$error_message = '';

/* =====================
   グループ作成処理
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $group_name = trim($_POST['group_name']);
    $group_description = trim($_POST['group_description']);
    $selected_members = $_POST['members'] ?? [];
    
    if (empty($group_name)) {
        $error_message = 'グループ名は必須です。';
    } else {
        // トランザクション開始
        mysqli_begin_transaction($link);
        
        try {
            // グループ作成
            $stmt = mysqli_prepare($link, "INSERT INTO chat_group_tbl (group_id, group_name, group_description, created_by) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $group_id, $group_name, $group_description, $user_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('グループの作成に失敗しました。');
            }
            
            $chat_group_id = mysqli_insert_id($link);
            mysqli_stmt_close($stmt);
            
            // 作成者を自動的にメンバーに追加
            $member_stmt = mysqli_prepare($link, "INSERT INTO chat_group_member_tbl (chat_group_id, group_id, user_id) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($member_stmt, "iss", $chat_group_id, $group_id, $user_id);
            
            if (!mysqli_stmt_execute($member_stmt)) {
                throw new Exception('メンバーの追加に失敗しました。');
            }
            mysqli_stmt_close($member_stmt);
            
            // 選択されたメンバーを追加
            if (!empty($selected_members)) {
                $add_member_stmt = mysqli_prepare($link, "INSERT INTO chat_group_member_tbl (chat_group_id, group_id, user_id) VALUES (?, ?, ?)");
                
                foreach ($selected_members as $member_id) {
                    if ($member_id !== $user_id) { // 作成者は既に追加済み
                        mysqli_stmt_bind_param($add_member_stmt, "iss", $chat_group_id, $group_id, $member_id);
                        mysqli_stmt_execute($add_member_stmt);
                    }
                }
                mysqli_stmt_close($add_member_stmt);
            }
            
            // コミット
            mysqli_commit($link);
            
            // チャット画面にリダイレクト
            header("Location: chat.php?type=group&chat_group_id=" . $chat_group_id);
            exit;
            
        } catch (Exception $e) {
            mysqli_rollback($link);
            $error_message = $e->getMessage();
        }
    }
}

/* =====================
   グループメンバー候補取得
===================== */
$available_members = [];
$stmt = mysqli_prepare($link, "SELECT user_id, name FROM login_tbl WHERE group_id = ? AND user_id != ? ORDER BY name");
mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $available_members[] = $row;
    }
}
mysqli_stmt_close($stmt);

$NAV_BASE = '.';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/create_group.html.php';
