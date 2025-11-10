<?php
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);
*/

use Dom\ChildNode;
session_start();

if(!isset($_SESSION['user_id'], $_SESSION['group_id'])){
    header('Location: login.php');
    exit();
}

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = '127.0.0.1';
$dbName = 'sportdata_db';

$link = mysqli_connect($host, $usr, $pwd, $dbName);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link,'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$corrent_goal = "";

$stmt = mysqli_prepare($link, "SELECT goal FROM goal_tbl WHERE group_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if($row = mysqli_fetch_assoc($result)){
    $corrent_goal = $row['goal'];
}
mysqli_stmt_close($stmt);

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
    echo "目標を登録しました。";
}else{
    echo "エラー" . mysqli_error($link);
}

mysqli_stmt_close($stmt);
mysqli_close($link);
}
?>

<!DOCTYPE>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ホームページ</title>
        <link rel="stylesheet" href="../css/home.css">
    </head>
    <body>
        <div class="home">
            <div class="meny">
                <button class="meny-btn">
                    <img src="../img/images.png" width="70px" height="70px">
                </button>
                <nav class="meny-nav">
                        <ul>
                            <li><a href="home.php">ホーム</a></li>
                            <li><a href="pi.php">身体情報</a></li>
                        </ul>
                </nav>
           </div> 
            <div class="home-left">
                <div class="user">
                    <h2>ユーザー情報</h2>
                    <div class="user-border">
                        <div class="photo">
                            <img src="../img/default-avatar.png" width="270px" height="300px">
                        </div>
                        <table class="user-information">
                            <tr>
                                <th>氏名</th>
                                <td><?php echo ($_SESSION['name'])?></td>
                            </tr>
                            <tr>
                                <th>生年月日</th>
                                <td><?php echo ($_SESSION['dob'])?></td>
                            </tr>
                            <tr>
                                <th>身長</th>
                                <td><?php echo ($_SESSION['height'])?> cm</td>
                            </tr>
                            <tr>
                                <th>体重</th>
                                <td><?php echo ($_SESSION['weight'])?> kg</td>
                            </tr>
                            <tr>
                                <th>ポジション</th>
                                <td><?php echo ($_SESSION['position'])?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="messege">
                    <h2>お知らせ</h2>
                    <div class="messege-area"></div>
                </div>
            </div>
            <div class="home-right">
            <div class="goal">
                    <h2>目標</h2>
                    <div class="goal-border">
                        <div class="goal-form">
                            <form action="" method="post">
                                <h3>今月の目</h3>
                                <input type="text" id="goal" name="goal" value=""><br>
                                <input type="submit" id="goal-reg" name="submit" value="登録">
                            </form>
                        </div>    
                        <div class="Viewing-goal">
                            <h3>現在の目標</h3>
                            <div class="now-goal">
                                <p><?php echo htmlspecialchars($corrent_goal)?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="calendar">
                    <h2>カレンダー</h2>
                    <div id="calendar-area" class="calendar-area"></div>
                </div>
            </div>
        </div>
    <script src="../js/calendar.js"></script>
    <script src="../js/home_meny.js"></script>
    </body>
</html>
