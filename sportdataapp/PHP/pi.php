<?php
session_start();

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = '127.0.0.1';


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ホームページ</title>
    <link rel="stylesheet" href="../css/pi.css">
</head>
<body>
    <div class="container">

        <div class="input-panel">
            <form action="" method="post">
                <h3>データ入力</h3>

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

        <div class="graph-panel">
            <div class="graph-panel_1">
                <h2>身長・体重・BMI</h2>
                <div class="graph height-weight-bmi"></div>
            </div>

            <div class="graph-panel_2">
                <div class="panel-item">
                    <h2>睡眠時間</h2>
                    <div class="graph sleep"></div>
                </div>

                <div class="panel-item">
                    <h2>怪我履歴</h2>
                    <div class="graph injury"></div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
