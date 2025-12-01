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

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録</title>
    <link rel="stylesheet" href="../css/reg2.css">
</head>

<body>
<h1>新規登録</h1>

<form action="" method="post">
<div class="reg-form">
    <div class="inner-reg-form">

        <!-- 左側 -->
        <div class="left-reg">
            <h3>ログイン情報</h3>

            <label for="group_id">団体ID</label><br>
            <input type="text" id="group_id" name="group_id" required><br>

            <label for="user_id">ユーザーID</label><br>
            <input type="text" id="user_id" name="user_id" required><br>

            <label for="password">パスワード</label><br>
            <input type="password" id="password" name="password" required><br>
        </div>

        <!-- 右側 -->
        <div class="right-reg">
            <h3>プロフィール情報</h3>

            <label for="name">氏名</label><br>
            <input type="text" id="name" name="name" required><br>

            <label for="dob">生年月日</label><br>
            <input type="date" id="dob" name="dob" required><br>

            <label for="height">身長</label><br>
            <input type="number" id="height" name="height" required><br>

            <label for="weight">体重</label><br>
            <input type="number" id="weight" name="weight" required><br>

            <label for="position">ポジション / 役職</label><br>
            <input type="text" id="position" name="position" required><br>
        </div>

    </div>

    <!-- 送信 -->
    <div class="sent-box">
        <input type="submit" name="reg" value="登録">
    </div>

</div>
</form>

</body>
</html>

