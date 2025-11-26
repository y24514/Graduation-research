<?php
session_start();

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';

$link = mysqli_connect($host, $usr, $pwd);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');
mysqli_select_db($link, 'sportdata_db');

$group_id = $_POST['group_id'] ?? '';
$user_id = $_POST['user_id'] ?? '';
$password = $_POST['password'] ?? '';
$name = $_POST['name'] ?? '';
$dob = $_POST['dob'] ?? '';
$height = $_POST['height'] ?? '';
$weight = $_POST['weight'] ?? '';
$position = $_POST['position'] ?? '';


if(isset($_POST['reg'])){
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO login_tbl (group_id, user_id, password, name, dob, height, weight ,position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    if(!$stmt){
        die("ステートメント準備に失敗しました。". mysqli_error($link));
    }
    mysqli_stmt_bind_param($stmt, "sssssdds", $group_id, $user_id,  $hash, $name, $dob, $height, $weight, $position);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: login.php');
        exit();
    } else {
        echo "❌ 登録に失敗しました: " . mysqli_error($link);
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE>
<html lang="js">
    <head>
        <meta charset="UTF-8">
        <title>新規登録</title>
        <link rel="stylesheet" href="../css/reg.css">
    </head>
    <body>
        <h1>新規登録</h1>
        <form action="" method="post">
            <!-- 団体ID -->
            <label for="group-id">団体ID</label>
            <input type="text" id="group_id" name="group_id" required>

            <!-- ユーザーID -->
            <label for="user-id">ユーザーID</label>
            <input type="text" id="user_id" name="user_id" required>

            <!-- パスワード -->
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" required>

            <!-- 名前 -->
            <label for="name">氏名</label>
            <input type="text" id="name" name="name" required>

            <!-- 生年月日 -->
            <label for="dob">生年月日</label>
            <input type="date" id="dob" name="dob" required>

            <!-- 身長 -->
            <label for="height">身長</label>
            <input type="number" id="height" name="height" required>

            <!-- 体重 -->
            <label for="weight">体重</label>
            <input type="number" id="weight" name="weight" required>

            <!-- ポジション　-->
            <label for="position">ポジション/役職</label>
            <input type="text" id="position" name="position" required>

            <!-- 送信　-->
            <div class="send-box">
            <label for="submit"></label>
            <input type="submit" name="reg" value="送信">
            </div>
        </form>    
    </body>
</html>