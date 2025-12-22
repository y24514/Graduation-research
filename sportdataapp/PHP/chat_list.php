<?php
session_start();

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
   グループメンバー一覧取得
===================== */
$members = [];
$stmt = mysqli_prepare($link, "SELECT user_id, name FROM login_tbl WHERE group_id = ? AND user_id != ? ORDER BY name");
mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        // 最新メッセージを取得
        $last_msg_stmt = mysqli_prepare($link, "
            SELECT message, created_at 
            FROM chat_tbl 
            WHERE group_id = ? 
            AND chat_type = 'direct' 
            AND (
                (user_id = ? AND recipient_id = ?) 
                OR 
                (user_id = ? AND recipient_id = ?)
            )
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        mysqli_stmt_bind_param($last_msg_stmt, "sssss", $group_id, $user_id, $row['user_id'], $row['user_id'], $user_id);
        mysqli_stmt_execute($last_msg_stmt);
        $last_result = mysqli_stmt_get_result($last_msg_stmt);
        $last_msg = mysqli_fetch_assoc($last_result);
        mysqli_stmt_close($last_msg_stmt);
        
        $row['last_message'] = $last_msg['message'] ?? '';
        $row['last_time'] = $last_msg['created_at'] ?? null;
        
        // 未読メッセージ数を取得（既読状態テーブルを使用）
        $unread_stmt = mysqli_prepare($link, "
            SELECT COUNT(*) as unread_count
            FROM chat_tbl c
            LEFT JOIN chat_read_status_tbl r 
                ON r.user_id = ? 
                AND r.group_id = ? 
                AND r.chat_type = 'direct' 
                AND r.recipient_id = c.user_id
            WHERE c.group_id = ?
            AND c.chat_type = 'direct'
            AND c.user_id = ?
            AND c.recipient_id = ?
            AND (r.last_read_message_id IS NULL OR c.id > r.last_read_message_id)
        ");
        mysqli_stmt_bind_param($unread_stmt, "sssss", $user_id, $group_id, $group_id, $row['user_id'], $user_id);
        mysqli_stmt_execute($unread_stmt);
        $unread_result = mysqli_stmt_get_result($unread_stmt);
        $unread_row = mysqli_fetch_assoc($unread_result);
        $row['unread_count'] = $unread_row['unread_count'] ?? 0;
        mysqli_stmt_close($unread_stmt);
        
        $members[] = $row;
    }
}
mysqli_stmt_close($stmt);

/* =====================
   グループチャット一覧取得（参加しているもの）
===================== */
$chat_groups = [];
$stmt = mysqli_prepare($link, "
    SELECT g.chat_group_id, g.group_name, g.created_by, g.created_at,
           (SELECT message FROM chat_tbl c WHERE c.chat_group_id = g.chat_group_id ORDER BY c.created_at DESC LIMIT 1) as last_message,
           (SELECT created_at FROM chat_tbl c WHERE c.chat_group_id = g.chat_group_id ORDER BY c.created_at DESC LIMIT 1) as last_time
    FROM chat_group_tbl g
    INNER JOIN chat_group_member_tbl m ON g.chat_group_id = m.chat_group_id
    WHERE m.user_id = ? AND m.group_id = ?
    ORDER BY last_time DESC, g.created_at DESC
");
mysqli_stmt_bind_param($stmt, "ss", $user_id, $group_id);
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        // 未読数を取得
        $unread_stmt = mysqli_prepare($link, "
            SELECT COUNT(*) as unread_count
            FROM chat_tbl c
            LEFT JOIN chat_read_status_tbl r 
                ON r.user_id = ? 
                AND r.group_id = ? 
                AND r.chat_type = 'group' 
                AND r.chat_group_id = c.chat_group_id
            WHERE c.group_id = ?
            AND c.chat_type = 'group'
            AND c.chat_group_id = ?
            AND c.user_id != ?
            AND (r.last_read_message_id IS NULL OR c.id > r.last_read_message_id)
        ");
        mysqli_stmt_bind_param($unread_stmt, "sssis", $user_id, $group_id, $group_id, $row['chat_group_id'], $user_id);
        mysqli_stmt_execute($unread_stmt);
        $unread_result = mysqli_stmt_get_result($unread_stmt);
        $unread_row = mysqli_fetch_assoc($unread_result);
        $row['unread_count'] = $unread_row['unread_count'] ?? 0;
        mysqli_stmt_close($unread_stmt);
        
        $chat_groups[] = $row;
    }
}
mysqli_stmt_close($stmt);

$NAV_BASE = '.';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/chat_list.html.php';
