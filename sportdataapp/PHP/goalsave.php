<?php
session_start();

if(!isset($_SESSION['user_id'], $_SESSION['group_id'])){
    header('Location: login.php');
    exit();
}

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';
$dbName = 'sportdata_db';

$link = mysqli_connect($host, $usr, $pwd, $dbName);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link,'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$corrent_goal = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
$goal = $_POST['goal'];

$stmt2 = mysqli_prepare($link, "SELECT * FROM goal_tbl WHERE user_id = ? AND group_id = ?");
mysqli_stmt_bind_param($stmt2, "ss", $user_id , $group_id);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);

if(mysqli_num_rows($result2) > 0){
    $stmt2 = mysqli_prepare($link, "UPDATE goal_tbl SET goal = ? ,created_at = NOW() WHERE user_id = ? AND group_id = ?");
    mysqli_stmt_bind_param($stmt2, "sss", $goal, $user_id, $group_id);
    $success = mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
}else{
    $stmt3 = mysqli_prepare($link,"INSERT INTO goal_tbl(group_id,user_id,goal,created_at)VALUES(?, ?, ?,NOW())");
    mysqli_stmt_bind_param($stmt3, "sss", $group_id, $user_id, $goal);
    $success = mysqli_stmt_execute($stmt3);
    mysqli_stmt_close($stmt3);
}

if($success){
    header('Location: home.php');
    exit();
}else{
    echo "エラー" . mysqli_error($link);
}

mysqli_close($link);
}

?>