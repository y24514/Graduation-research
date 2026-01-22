<?php
require_once __DIR__ . '/session_bootstrap.php';

// ログインチェック
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    http_response_code(401);
    echo '未ログイン';
    exit;
}

$usr = getenv('DB_USER') ?: 'sportsdata_user';
$pwd = getenv('DB_PASS') ?: 'fujidai14';
$host = 'localhost';
$dbName = getenv('DB_NAME') ?: 'sportsdata';

$link = mysqli_connect($host, $usr, $pwd, $dbName);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'] ?? null;
$group_id = $_SESSION['group_id'] ?? null;

function calendar_has_column($link, $table, $column) {
    $table = mysqli_real_escape_string($link, $table);
    $column = mysqli_real_escape_string($link, $column);
    $sql = "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$column}'";
    $res = mysqli_query($link, $sql);
    if (!$res) return false;
    $row = mysqli_fetch_assoc($res);
    mysqli_free_result($res);
    return !empty($row) && (int)$row['cnt'] > 0;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = $_POST['title'] ?? '';
    $memo = $_POST['memo'] ?? '';
    $startdate = $_POST['startdate'] ?? '';
    $enddate = $_POST['enddate'] ?? '';

    // 管理者のみ: 共有予定を作成
    $requestedShared = !empty($_POST['is_shared']);
    $isAdminUser = !empty($_SESSION['is_admin']) || !empty($_SESSION['is_super_admin']);
    $isShared = ($requestedShared && $isAdminUser);

    $redirectTo = $_POST['redirect_to'] ?? '';
    $redirectTo = is_string($redirectTo) ? trim($redirectTo) : '';

    $hasIsShared = calendar_has_column($link, 'calendar_tbl', 'is_shared');
    if ($isShared && !$hasIsShared) {
        $msg = 'DBが未更新のため共有できません（add_calendar_shared_events.sql を適用してください）';
        if ($redirectTo !== '') {
            $_SESSION['calendar_flash'] = $msg;
            header('Location: ' . $redirectTo);
            exit;
        }
        echo $msg;
        exit;
    }

    if ($hasIsShared) {
        $stmt = mysqli_prepare($link, "INSERT INTO calendar_tbl (group_id, user_id, title, memo, startdate, enddate, is_shared, create_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $sharedInt = $isShared ? 1 : 0;
        mysqli_stmt_bind_param($stmt, "ssssssi", $group_id, $user_id, $title, $memo, $startdate, $enddate, $sharedInt);
    } else {
        $stmt = mysqli_prepare($link, "INSERT INTO calendar_tbl (group_id, user_id, title, memo, startdate, enddate, create_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "ssssss", $group_id, $user_id, $title, $memo, $startdate, $enddate);
    }
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success) {
        $msg = $isShared ? '共有予定を追加しました' : '成功';
    } else {
        $msg = 'エラー: ' . mysqli_error($link);
    }

    if ($redirectTo !== '') {
        $_SESSION['calendar_flash'] = $msg;
        header('Location: ' . $redirectTo);
        exit;
    }

    echo $msg;
}

mysqli_close($link);
?>
