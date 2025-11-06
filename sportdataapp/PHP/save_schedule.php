<?php
session_start();
$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = '127.0.0.1';
$dbName = 'sportdata_db';

$link = mysqli_connect($host, $usr, $pwd, $dbName);
if (!$link) {
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$date = $_POST['date'] ?? null;
$title = $_POST['title'] ?? null;

if ($date && $title) {
    $stmt = mysqli_prepare($link, "INSERT INTO schedule_tbl (group_id, user_id, schedule_date, title) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $group_id, $user_id, $date, $title);
    if (mysqli_stmt_execute($stmt)) {
        echo "予定を登録しました。";
    } else {
        echo "登録に失敗しました: " . mysqli_error($link);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "データが不足しています。";
}

mysqli_close($link);
?>
