<?php
require_once __DIR__ . '/session_bootstrap.php';

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

// 今月の範囲（created_at で判定）
$monthStart = date('Y-m-01 00:00:00');
$monthEnd = date('Y-m-01 00:00:00', strtotime('+1 month'));

if($_SERVER['REQUEST_METHOD'] === 'POST'){
header('Content-Type: application/json');

$goal = $_POST['goal'] ?? '';

if (empty(trim($goal))) {
    echo json_encode(['success' => false, 'message' => '目標を入力してください']);
    exit();
}

$stmt2 = mysqli_prepare($link, "
    SELECT goal_id
    FROM goal_tbl
    WHERE user_id = ? AND group_id = ?
      AND created_at >= ? AND created_at < ?
    ORDER BY created_at DESC
    LIMIT 1
");
mysqli_stmt_bind_param($stmt2, "ssss", $user_id , $group_id, $monthStart, $monthEnd);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);

$existingGoalId = null;
if ($row2 = mysqli_fetch_assoc($result2)) {
    $existingGoalId = (int)($row2['goal_id'] ?? 0);
}
mysqli_stmt_close($stmt2);

if ($existingGoalId) {
    $stmtUpd = mysqli_prepare($link, "UPDATE goal_tbl SET goal = ? WHERE goal_id = ? AND user_id = ? AND group_id = ?");
    mysqli_stmt_bind_param($stmtUpd, "siss", $goal, $existingGoalId, $user_id, $group_id);
    $success = mysqli_stmt_execute($stmtUpd);
    mysqli_stmt_close($stmtUpd);
} else {
    $stmtIns = mysqli_prepare($link,"INSERT INTO goal_tbl(group_id,user_id,goal,created_at) VALUES(?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmtIns, "sss", $group_id, $user_id, $goal);
    $success = mysqli_stmt_execute($stmtIns);
    mysqli_stmt_close($stmtIns);
}

mysqli_close($link);

if($success){
    echo json_encode(['success' => true, 'message' => '目標を保存しました']);
}else{
    echo json_encode(['success' => false, 'message' => '保存に失敗しました']);
}
exit();
}

?>