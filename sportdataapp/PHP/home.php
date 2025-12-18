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

$NAV_BASE = '.';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/home.html.php';

