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

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $injury = $_POST['injury'];
    $sleep_time = $_POST['sleep_time'];
    $create_at = "";

    $stmt = mysqli_prepare($link, "INSERT INTO pi_tbl(group_id, user_id, height, weight, injury, sleeptime, create_at ) VALUES(?, ?, ?, ?, ?, ?, NOw()) ");
    mysqli_stmt_bind_param($stmt, "ssddss", $group_id, $user_id, $height, $weight, $injury, $sleep_time);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();

    if($success){
        echo "身体データを登録しました。";
    } else{
        echo "エラー" . mysqli_error($link);
    }
}

$stmt2 = mysqli_prepare($link, "SELECT height, weight, injury, sleeptime, create_at FROM pi_tbl WHERE user_id = ? AND group_id = ?");
mysqli_stmt_bind_param($stmt2, "ss", $user_id, $group_id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_bind_result($stmt2, $height, $weight, $injury, $sleep_time, $create_at);

$records = [];

while(mysqli_stmt_fetch($stmt2)){
    $records [] = [
        'height' => $height,
        'weight' => $weight,
        'injury' => $injury,
        'sleep_time' => $sleep_time,
        'create_at' => $create_at
    ];
}
mysqli_stmt_close($stmt2);


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ホームページ</title>
    <link rel="stylesheet" href="../css/pi2.css">
</head>
<body>       
    <!-- メニュー -->
    <div class="meny">
        <nav class="meny-nav">
            <ul>
                <li><button><a href="home.php">ホーム</a></button></li>
                <li><button><a href="pi.php">身体情報</a></button></li>
                <li><button><a href="">テニス</a></button></li>
                <li><button><a href="swim.php">水泳</a></button></li>
                <li><button><a href="">バスケ</a></button></li>
            </ul>
        </nav>
    </div>
    <!-- 身体情報画面　-->
        <div class="container">
            <!-- 入力パネル -->
            <div class="input-panel">
                <h2> データ入力</h2>
                <div class="form-all">
                    <form id=piform action="" method="post" >
                        <label for="height">身長</label>
                        <input type="number" id="height" name="height" placeholder="cm" min="50" max="250" step="0.1"><br>

                        <label for="weight">体重</label>
                        <input type="number" id="weight" name="weight" placeholder="kg" min="0" max="300" step="0.1"><br>

                        <label for="injury">怪我履歴</label>
                        <input type="text" id="injury" name="injury"><br>

                        <label for="sleep_time">睡眠時間</label>
                        <input type="time" id="sleep_time" name="sleep_time" value="05:00" step="900"><br>

                        <input type="submit" value="送信">
                    </form>
                </div>
            </div>
            <!-- グラフパネル -->
                <div class="graph-panel">
                    <div class="graph-panel_1">
                        <h2>身長・体重・BMI</h2>
                        <canvas class="graph height-weight-bmi"></canvas>
                    </div>
                    <div class="graph-panel_2">
                        <div class="panel-item">
                            <h2>睡眠時間</h2>
                            <canvas class="graph sleep"></canvas>
                        </div>
                        <div class="panel-item">
                            <h2>怪我履歴</h2>
                            <div class="graph injury">
                                <?php foreach($records as $r): ?>
                                   <p><?= htmlspecialchars($r['create_at']) ?> : <?= htmlspecialchars($r['injury']) ?><p>                        
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <script>
        const records = <?= json_encode($records, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src=../js/meny.js></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type=module src=../js/pi.js></script>
</body>
</html>
