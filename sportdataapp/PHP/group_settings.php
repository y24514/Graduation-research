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
$dbUser = getenv('DB_USER') ?: 'sportsdata_user';
$dbPass = getenv('DB_PASS') ?: 'fujidai14';
$dbName = getenv('DB_NAME') ?: 'sportsdata';

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

$chat_group_id = (int)($_GET['chat_group_id'] ?? 0);

if (!$chat_group_id) {
    header('Location: chat_list.php');
    exit;
}

$success_message = '';
$error_message = '';

/* =====================
   グループ情報取得
===================== */
$group_info = null;
$stmt = mysqli_prepare($link, "SELECT * FROM chat_group_tbl WHERE chat_group_id = ? AND group_id = ?");
mysqli_stmt_bind_param($stmt, "is", $chat_group_id, $group_id);
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    $group_info = mysqli_fetch_assoc($result);
}
mysqli_stmt_close($stmt);

if (!$group_info) {
    header('Location: chat_list.php');
    exit;
}

$is_creator = ($group_info['created_by'] === $user_id);

/* =====================
   メンバー追加処理
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_members'])) {
    if (!$is_creator) {
        $error_message = '作成者のみがメンバーを追加できます。';
    } else {
        $selected_members = $_POST['members'] ?? [];
        
        if (!empty($selected_members)) {
            $add_member_stmt = mysqli_prepare($link, "INSERT IGNORE INTO chat_group_member_tbl (chat_group_id, group_id, user_id) VALUES (?, ?, ?)");
            
            $added_count = 0;
            foreach ($selected_members as $member_id) {
                mysqli_stmt_bind_param($add_member_stmt, "iss", $chat_group_id, $group_id, $member_id);
                if (mysqli_stmt_execute($add_member_stmt)) {
                    if (mysqli_stmt_affected_rows($add_member_stmt) > 0) {
                        $added_count++;
                    }
                }
            }
            mysqli_stmt_close($add_member_stmt);
            
            if ($added_count > 0) {
                $success_message = "{$added_count}人のメンバーを追加しました。";
            } else {
                $error_message = '選択したメンバーは既に追加されています。';
            }
        }
    }
}

/* =====================
   メンバー削除処理
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_member'])) {
    if (!$is_creator) {
        $error_message = '作成者のみがメンバーを削除できます。';
    } else {
        $remove_user_id = $_POST['remove_user_id'];
        
        if ($remove_user_id === $user_id) {
            $error_message = '作成者は自分を削除できません。';
        } else {
            $remove_stmt = mysqli_prepare($link, "DELETE FROM chat_group_member_tbl WHERE chat_group_id = ? AND user_id = ?");
            mysqli_stmt_bind_param($remove_stmt, "is", $chat_group_id, $remove_user_id);
            
            if (mysqli_stmt_execute($remove_stmt) && mysqli_stmt_affected_rows($remove_stmt) > 0) {
                $success_message = 'メンバーを削除しました。';
            } else {
                $error_message = 'メンバーの削除に失敗しました。';
            }
            mysqli_stmt_close($remove_stmt);
        }
    }
}

/* =====================
   現在のメンバー一覧
===================== */
$current_members = [];
$stmt = mysqli_prepare($link, "
    SELECT m.user_id, l.name, m.joined_at
    FROM chat_group_member_tbl m
    LEFT JOIN login_tbl l ON m.user_id = l.user_id AND m.group_id = l.group_id
    WHERE m.chat_group_id = ?
    ORDER BY l.name
");
mysqli_stmt_bind_param($stmt, "i", $chat_group_id);
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $current_members[] = $row;
    }
}
mysqli_stmt_close($stmt);

/* =====================
   追加可能なメンバー取得
===================== */
$available_members = [];
if ($is_creator) {
    $current_member_ids = array_map(function($m) { return $m['user_id']; }, $current_members);
    $placeholders = str_repeat('?,', count($current_member_ids) - 1) . '?';
    
    $stmt = mysqli_prepare($link, "SELECT user_id, name FROM login_tbl WHERE group_id = ? AND user_id NOT IN ($placeholders) ORDER BY name");
    $types = str_repeat('s', count($current_member_ids) + 1);
    $params = array_merge([$group_id], $current_member_ids);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $available_members[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

$NAV_BASE = '.';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/group_settings.html.php';
