<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>身体情報</title>
    <link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
    <link rel="stylesheet" href="../css/pi.css">
    <link rel="stylesheet" href="../css/site.css">
</head>
<body>       
    <?php $NAV_BASE = '.'; require_once __DIR__ . '/../PHP/header.php'; ?>
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
