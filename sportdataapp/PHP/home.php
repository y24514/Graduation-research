<?php
session_start();

// ログインしているか確認
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: login.php');
    exit();
}

$showLoader = false;
if (isset($_SESSION['first_login']) && $_SESSION['first_login'] === true) {
    $showLoader = true;
    $_SESSION['first_login'] = false;
}

// DB接続処理
$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';
$dbName = 'sportdata_db';

$link = mysqli_connect($host, $usr, $pwd, $dbName);
if (!$link) {
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$corrent_goal = "";
$hasGoalThisMonth = false;
$currentMonth = date('Y-m');

// セッションからユーザー情報を取得
$userName = $_SESSION['name'] ?? '';
$userDob = $_SESSION['dob'] ?? '';
$userHeight = $_SESSION['height'] ?? '';
$userWeight = $_SESSION['weight'] ?? '';
$userPosition = $_SESSION['position'] ?? '';

// 今月の目標が既に登録されているかチェック
$stmt_check = mysqli_prepare($link, "
    SELECT COUNT(*) as count 
    FROM goal_tbl 
    WHERE group_id = ? AND user_id = ? 
    AND DATE_FORMAT(created_at, '%Y-%m') = ?
");
mysqli_stmt_bind_param($stmt_check, "sss", $group_id, $user_id, $currentMonth);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
if ($row_check = mysqli_fetch_assoc($result_check)) {
    $hasGoalThisMonth = ($row_check['count'] > 0);
}
mysqli_stmt_close($stmt_check);

// goal表示
$stmt = mysqli_prepare($link, "SELECT goal FROM goal_tbl WHERE group_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $corrent_goal = $row['goal'];
}
mysqli_stmt_close($stmt);

// スケジュール表示
$stmt2 = mysqli_prepare($link, "
    SELECT title, startdate, enddate 
    FROM calendar_tbl 
    WHERE group_id = ? AND user_id = ?
");
mysqli_stmt_bind_param($stmt2, "ss", $group_id, $user_id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_bind_result($stmt2, $title, $startdate, $enddate);

$records = [];
while (mysqli_stmt_fetch($stmt2)) {
    $records[] = [
        'title' => $title,
        'start' => $startdate,
        'end' => $enddate
    ];
}
mysqli_stmt_close($stmt2);

// チャット通知を取得（最新5件の未読メッセージのみ）
$stmt_chat = mysqli_prepare($link, "
    SELECT 
        c.id,
        c.message,
        c.created_at,
        c.chat_type,
        c.chat_group_id,
        c.recipient_id,
        c.user_id as sender_user_id,
        l.name as sender_name,
        g.group_name
    FROM chat_tbl c
    LEFT JOIN login_tbl l ON c.user_id = l.user_id AND c.group_id = l.group_id
    LEFT JOIN chat_group_tbl g ON c.chat_group_id = g.chat_group_id
    LEFT JOIN chat_read_status_tbl r ON (
        r.user_id = ? 
        AND r.group_id = ?
        AND (
            (c.chat_type = 'direct' AND r.chat_type = 'direct' AND r.recipient_id = c.user_id)
            OR
            (c.chat_type = 'group' AND r.chat_type = 'group' AND r.chat_group_id = c.chat_group_id)
        )
    )
    WHERE (
        (c.chat_type = 'direct' AND c.recipient_id = ? AND c.group_id = ?)
        OR 
        (c.chat_type = 'group' AND c.chat_group_id IN (
            SELECT chat_group_id FROM chat_group_member_tbl 
            WHERE user_id = ? AND group_id = ?
        ))
    )
    AND c.user_id != ?
    AND c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    AND (r.last_read_message_id IS NULL OR c.id > r.last_read_message_id)
    ORDER BY c.created_at DESC
    LIMIT 5
");
mysqli_stmt_bind_param($stmt_chat, "sssssss", $user_id, $group_id, $user_id, $group_id, $user_id, $group_id, $user_id);
mysqli_stmt_execute($stmt_chat);
$result_chat = mysqli_stmt_get_result($stmt_chat);

$chat_notifications = [];
while ($row_chat = mysqli_fetch_assoc($result_chat)) {
    $chat_notifications[] = $row_chat;
}
mysqli_stmt_close($stmt_chat);

$NAV_BASE = '.';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/home.html.php';

