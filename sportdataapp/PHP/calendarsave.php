<?php
session_start();

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';
$dbName = 'sportdata_db';

$link = mysqli_connect($host, $usr, $pwd, $dbName);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'] ?? null;
$group_id = $_SESSION['group_id'] ?? null;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = $_POST['title'] ?? '';
    $memo = $_POST['memo'] ?? '';
    $startdate = $_POST['startdate'] ?? '';
    $enddate = $_POST['enddate'] ?? '';

    $stmt = mysqli_prepare($link, "INSERT INTO calendar_tbl (group_id, user_id, title, memo, startdate, enddate, create_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "ssssss", $group_id, $user_id, $title, $memo, $startdate, $enddate);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if($success){
        echo "成功";
    }else{
        echo "エラー: " . mysqli_error($link);
    }
}

mysqli_close($link);
?>
