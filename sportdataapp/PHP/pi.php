<?php
require_once __DIR__ . '/session_bootstrap.php';

/* =====================
   セッションチェック
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: login.php');
    exit;
}

// ページリロード時にローディングを表示
$showLoader = false;

/* =====================
   DB接続 (環境変数優先)
===================== */
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'sportsdata_user';
$dbPass = getenv('DB_PASS') ?: 'fujidai14';
$dbName = getenv('DB_NAME') ?: 'sportsdata';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    error_log('DB connect error: ' . mysqli_connect_error());
    http_response_code(500);
    echo 'データベース接続に失敗しました。';
    exit;
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];

/* =====================
   データ登録
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $height = (float)$_POST['height'];
    $weight = (float)$_POST['weight'];
    $injury = $_POST['injury'];
    $sleep_time = $_POST['sleep_time'];

    $stmt = mysqli_prepare($link, "INSERT INTO pi_tbl(group_id, user_id, height, weight, injury, sleeptime, create_at) VALUES(?, ?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "ssddss", $group_id, $user_id, $height, $weight, $injury, $sleep_time);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
    } else {
        error_log('Insert error: ' . mysqli_error($link));
    }
}

/* =====================
   全記録取得
===================== */
$stmt2 = mysqli_prepare($link, "SELECT height, weight, injury, sleeptime, create_at FROM pi_tbl WHERE user_id = ? AND group_id = ? ORDER BY create_at DESC");
mysqli_stmt_bind_param($stmt2, "ss", $user_id, $group_id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_bind_result($stmt2, $height, $weight, $injury, $sleep_time, $create_at);

$records = [];
while (mysqli_stmt_fetch($stmt2)) {
    $records[] = [
        'height' => $height,
        'weight' => $weight,
        'injury' => $injury,
        'sleep_time' => $sleep_time,
        'create_at' => $create_at,
        'bmi' => $height > 0 ? round(($weight / (($height / 100) ** 2)), 1) : null
    ];
}
mysqli_stmt_close($stmt2);

/* =====================
   統計情報の計算
===================== */
$stats = [
    'total_records' => count($records),
    'latest' => null,
    'avg_weight' => null,
    'avg_height' => null,
    'avg_bmi' => null,
    'avg_sleep' => null,
    'weight_change' => null,
    'bmi_category' => null,
    'sleep_quality' => null
];

if (count($records) > 0) {
    $stats['latest'] = $records[0];
    
    // 平均計算
    $weights = array_column($records, 'weight');
    $heights = array_column($records, 'height');
    $bmis = array_filter(array_column($records, 'bmi'));
    
    $stats['avg_weight'] = round(array_sum($weights) / count($weights), 1);
    $stats['avg_height'] = round(array_sum($heights) / count($heights), 1);
    $stats['avg_bmi'] = count($bmis) > 0 ? round(array_sum($bmis) / count($bmis), 1) : null;
    
    // 睡眠時間の平均（時間形式から分に変換）
    $sleep_minutes = [];
    foreach ($records as $r) {
        if ($r['sleep_time']) {
            list($h, $m) = explode(':', $r['sleep_time']);
            $sleep_minutes[] = ($h * 60) + $m;
        }
    }
    if (count($sleep_minutes) > 0) {
        $avg_minutes = round(array_sum($sleep_minutes) / count($sleep_minutes));
        $stats['avg_sleep'] = sprintf('%d:%02d', floor($avg_minutes / 60), $avg_minutes % 60);
    }
    
    // 体重変化（最新と最古を比較）
    if (count($records) >= 2) {
        $latest_weight = $records[0]['weight'];
        $oldest_weight = $records[count($records) - 1]['weight'];
        $stats['weight_change'] = round($latest_weight - $oldest_weight, 1);
    }
    
    // BMIカテゴリー
    if ($stats['latest']['bmi']) {
        $bmi = $stats['latest']['bmi'];
        if ($bmi < 18.5) $stats['bmi_category'] = '低体重';
        elseif ($bmi < 25) $stats['bmi_category'] = '普通体重';
        elseif ($bmi < 30) $stats['bmi_category'] = '肥満(1度)';
        else $stats['bmi_category'] = '肥満(2度以上)';
    }
    
    // 睡眠の質評価
    if (count($sleep_minutes) > 0) {
        $avg_minutes = array_sum($sleep_minutes) / count($sleep_minutes);
        if ($avg_minutes >= 420) $stats['sleep_quality'] = '良好';  // 7時間以上
        elseif ($avg_minutes >= 360) $stats['sleep_quality'] = '普通';  // 6-7時間
        else $stats['sleep_quality'] = '不足';  // 6時間未満
    }
}

mysqli_close($link);

$NAV_BASE = '.';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/pi.html.php';
