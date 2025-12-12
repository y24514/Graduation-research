<?php
session_start();

$user = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';
$dbName = 'sportdata_db';

$link = mysqli_connect($host, $user, $pwd, $dbName);
if (!$link) {
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');

// セッション（タイポ修正）
$group_id = $_SESSION['group_id'];
$user_id  = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 基本情報
    $pool     = $_POST['pool'];      // short / long
    $event    = $_POST['event'];     // fly / ba / br / fr / im
    $distance = $_POST['distance'];  // 25, 50, 100 ...
    $time     = $_POST['time'];      // HH:MM:SS

    // ストローク回数（動的）
    $stroke_data = [];
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'stroke_') === 0) {
            $stroke_data[$key] = (int)$val;
        }
    }

    // ラップタイム（動的）
    $lap_data = [];
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'lap_time_') === 0) {
            $lap_data[$key] = $val;
        }
    }

    // JSON に変換して保存する（DB は TEXT 推奨）
    $stroke_json = json_encode($stroke_data, JSON_UNESCAPED_UNICODE);
    $lap_json    = json_encode($lap_data, JSON_UNESCAPED_UNICODE);

    // INSERT（テーブル名は任意に要調整）
    $sql = "
        INSERT INTO swim_tbl
        (group_id, user_id, pool, event, distance, time, stroke_json, lap_json)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssiss",
        $group_id,
        $user_id,
        $pool,
        $event,
        $distance,
        $time,
        $stroke_json,
        $lap_json
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('保存しました');</script>";
    } else {
        echo "<script>alert('登録に失敗しました');</script>";
    }

    mysqli_stmt_close($stmt);
}
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>水泳</title>
    <link rel="stylesheet" href="../css/swim.css">
</head>
<body>
<div class="all">

    <!-- メニュー -->
    <div class="meny">
        <nav class="meny-nav">
            <ul>
                <li><button><a href="home.php">ホーム</a></button></li>
                <li><button><a href="pi.php">身体情報</a></button></li>
                <li><button><a href="">テニス</a></button></li>
                <li>
                    <button>
                        <a href="swim.php">水泳</a>
                    </button>
                    <li>
                        <button>
                            <a href="swim_analysis">分析</a>
                        </button>
                    </li>
                </li>
                <li><button><a href="">バスケ</a></button></li>
            </ul>
        </nav>
    </div>

    <div class="top">
        <div class="input-form">

            <form id="swim-form" action="" method="post">
                <div class="form-items">

                <!-- プール -->
                <label for="pool">プール</label>
                <select id="pool_type" name="pool">
                    <option value="" selected disabled>選択してください</option>
                    <option value="short">短水路</option>
                    <option value="long">長水路</option>
                </select><br>

                <!-- 種目 -->
                <label for="event">種目</label>
                <select id="event" name="event">
                    <option value="" selected disabled>種目を選択してください</option>
                    <option value="fly">バタフライ</option>
                    <option value="ba">背泳ぎ</option>
                    <option value="br">平泳ぎ</option>
                    <option value="fr">自由形</option>
                    <option value="im">個人メドレー</option>
                </select><br>

                <!-- 距離 -->
                <label for="distance">距離</label>
                <select id="distance" name="distance">
                    <option value="" selected disabled>距離を入力してください</option>
                    <option value="25">25m</option>
                    <option value="50">50m</option>
                    <option value="100">100m</option>
                    <option value="200">200m</option>
                    <option value="400">400m</option>
                    <option value="800">800m</option>
                    <option value="1500">1500m</option>
                </select><br>

                <!-- タイム -->
                <label for="time">タイム</label>
                <input type="time" id="time" name="time" value="00:00:00" step="1"><br>

                <!-- 送信　-->
                <input type="submit" id="submit" name="submit" value="送信">

                </div>

                <!-- ストローク入力欄 -->
                <div id="stroke_area">
                    <label>ストローク回数</label><br>

                    <h4 id="base-interval-title">0〜25m のストローク回数</h4>
                    <input type="number" id="base-stroke" name="stroke_25" min="0" max="200" required><br>
                </div>

                <!-- ラップタイム -->
                <div id="lap_time_area">
                    <label>ラップタイム</label><br>

                    <h4 id="base-lap-title">0〜25m のラップタイム</h4>
                    <input type="text" id="base-lap" name="lap_time_25" placeholder="例: 15.23" pattern="\\d{1,2}\\.\\d{1,2}" required><br>
                </div>


            </form>
        </div>
</div>

<script src="../js/swim/stroke.js"></script>
<script src="../js/swim/lap.js"></script>
</body>
</html>
