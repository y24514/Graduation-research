<?php
session_start();

$link = mysqli_connect("localhost", "y24514", "Kr96main0303", "sportdata_db");
mysqli_set_charset($link, "utf8");

$group_id = $_SESSION['group_id'];
$user_id  = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pool      = $_POST['pool'];
    $event     = $_POST['event'];
    $distance  = (int)$_POST['distance'];
    $total_time = isset($_POST['total_time']) ? (float)$_POST['total_time'] : 0;

    /* ===== ストロークデータ ===== */
    $stroke_data = [];
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'stroke_') === 0) {
            $stroke_data[$k] = (int)$v;
        }
    }

    /* ===== ラップタイム ===== */
    $lap_data = [];
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'lap_time_') === 0) {
            $lap_data[$k] = $v;
        }
    }

    $stroke_json = json_encode($stroke_data, JSON_UNESCAPED_UNICODE);
    $lap_json    = json_encode($lap_data, JSON_UNESCAPED_UNICODE);

    /* ===== データ保存 ===== */
    $sql = "INSERT INTO swim_tbl
            (group_id, user_id, pool, event, distance, total_time, stroke_json, lap_json)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ssssidss", $group_id, $user_id, $pool, $event, $distance, $total_time, $stroke_json, $lap_json);
    mysqli_stmt_execute($stmt);
    $saved = mysqli_stmt_affected_rows($stmt) > 0;

    echo $saved ? "<script>alert('保存しました');</script>" : "<script>alert('保存失敗');</script>";

    /* ===== 自己ベスト取得・更新 ===== */
    $sql_best = "SELECT best_time
                 FROM swim_best_tbl
                 WHERE group_id = ? AND user_id = ? AND pool = ? AND event = ? AND distance = ?";
    $stmt_best = mysqli_prepare($link, $sql_best);
    mysqli_stmt_bind_param($stmt_best, "ssssi", $group_id, $user_id, $pool, $event, $distance);
    mysqli_stmt_execute($stmt_best);
    $res_best = mysqli_stmt_get_result($stmt_best);
    $row_best = mysqli_fetch_assoc($res_best);

    $best_time   = $row_best['best_time'] ?? null;
    $is_new_best = false;
    $pb_diff     = null;

    if ($best_time === null || $total_time < $best_time) {
        $is_new_best = true;
        $pb_diff     = 0;

        $sql_upsert = "INSERT INTO swim_best_tbl
                       (group_id, user_id, pool, event, distance, best_time)
                       VALUES (?, ?, ?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE best_time = VALUES(best_time)";
        $stmt_up = mysqli_prepare($link, $sql_upsert);
        mysqli_stmt_bind_param($stmt_up, "ssssid", $group_id, $user_id, $pool, $event, $distance, $total_time);
        mysqli_stmt_execute($stmt_up);
    } else {
        $pb_diff = $total_time - $best_time;
    }

    /* ===== 自己ベスト一覧取得 ===== */
    $sql_best_list = "SELECT pool, event, distance, best_time
                      FROM swim_best_tbl
                      WHERE group_id = ? AND user_id = ?
                      ORDER BY pool, event, distance";
    $stmt_list = mysqli_prepare($link, $sql_best_list);
    mysqli_stmt_bind_param($stmt_list, "ss", $group_id, $user_id);
    mysqli_stmt_execute($stmt_list);
    $res_list = mysqli_stmt_get_result($stmt_list);

    $best_list = [];
    while ($row = mysqli_fetch_assoc($res_list)) {
        $best_list[] = $row;
    }

    /* ===== 前回タイム取得 ===== */
    $sql_prev = "SELECT total_time
                 FROM swim_tbl
                 WHERE group_id = ? AND user_id = ? AND pool = ? AND event = ? AND distance = ?
                 ORDER BY created_at DESC
                 LIMIT 1";
    $stmt_prev = mysqli_prepare($link, $sql_prev);
    mysqli_stmt_bind_param($stmt_prev, "ssssi", $group_id, $user_id, $pool, $event, $distance);
    mysqli_stmt_execute($stmt_prev);
    $res_prev = mysqli_stmt_get_result($stmt_prev);
    $row_prev = mysqli_fetch_assoc($res_prev);
    $prev_time = $row_prev['total_time'] ?? null;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>水泳</title>
    <link rel="stylesheet" href="../../css/swim.css">
</head>
<body>
<div class="all">

    <!-- メニュー -->
    <div class="meny">
        <nav class="meny-nav">
            <ul>
                <li><button><a href="../home.php">ホーム</a></button></li>
                <li><button><a href="../pi.php">身体情報</a></button></li>
                <li><button><a href="">テニス</a></button></li>
                <li><button><a href="swim.php">水泳</a></button></li>
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
                    <select id="pool_type" name="pool" required>
                        <option value="" selected disabled>選択してください</option>
                        <option value="short">短水路</option>
                        <option value="long">長水路</option>
                    </select><br>

                    <!-- 種目 -->
                    <label for="event">種目</label>
                    <select id="event" name="event" required>
                        <option value="" selected disabled>種目を選択してください</option>
                        <option value="fly">バタフライ</option>
                        <option value="ba">背泳ぎ</option>
                        <option value="br">平泳ぎ</option>
                        <option value="fr">自由形</option>
                        <option value="im">個人メドレー</option>
                    </select><br>

                    <!-- 距離 -->
                    <label for="distance">距離</label>
                    <select id="distance" name="distance" required>
                        <option value="" selected disabled>距離を入力してください</option>
                        <option value="25">25m</option>
                        <option value="50">50m</option>
                        <option value="100">100m</option>
                        <option value="200">200m</option>
                        <option value="400">400m</option>
                        <option value="800">800m</option>
                        <option value="1500">1500m</option>
                    </select><br>

                    <!-- 表示用（分:秒） -->
                    <input type="text" id="time" readonly>

                    <!-- DB送信用（秒） -->
                    <input type="hidden" id="total_time" name="total_time">

                    <!-- 送信 -->
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
                    <input type="text" id="base-lap" name="lap_time_25" placeholder="例: 15.23" pattern="\d{1,2}\.\d{1,2}" required><br>
                </div>
            </form>
        </div>

        <div class="analysis">
            <p>前半合計：<span id="first-half">0.00</span> 秒</p>
            <p>後半合計：<span id="second-half">0.00</span> 秒</p>
            <p>後半 − 前半：<span id="half-diff">0.00</span> 秒</p>

            <div class="best-list">
                <h3>自己ベスト一覧</h3>
                <table>
                    <thead>
                        <tr>
                            <th>プール</th>
                            <th>種目</th>
                            <th>距離</th>
                            <th>ベスト</th>
                        </tr>
                    </thead>
                    <tbody id="best-list-body">
                    </tbody>
                </table>
            </div>

            <table id="compare-table" border="1" cellspacing="0" cellpadding="4">
                <thead>
                    <tr>
                        <th></th>
                        <th>前回</th>
                        <th>今回</th>
                        <th>差</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>前回との比較</th>
                        <td id="prev-time">---</td>
                        <td id="current-time">---</td>
                        <td id="diff-prev">---</td>
                    </tr>
                    <tr>
                        <th>ベストとの比較</th>
                        <td id="best-time">---</td>
                        <td id="current-time-best">---</td>
                        <td id="diff-best">---</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>

<script src="../../js/swim/swim.js"></script>
<script>
    const pbDiff    = <?= $pb_diff !== null ? $pb_diff : "null" ?>;
    const isNewBest = <?= $is_new_best ? "true" : "false" ?>;
    const bestTime  = <?= $best_time !== null ? $best_time : "null" ?>;
    const BEST_LIST = <?= json_encode($best_list, JSON_UNESCAPED_UNICODE) ?>;
    const PREV_TIME = <?= $prev_time !== null ? $prev_time : "null" ?>;
</script>
</body>
</html>
