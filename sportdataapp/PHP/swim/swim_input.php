<?php
require_once __DIR__ . '/../session_bootstrap.php';

/* ---------------------
   セッションチェック
--------------------- */
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: ../login.php');
    exit;
}

// タブごとのセッション分離用 tab_id（リダイレクト時に維持する）
$tabId = (string)($_GET['tab_id'] ?? ($_POST['tab_id'] ?? ($GLOBALS['SPORTDATA_TAB_ID'] ?? '')));

// ページリロード時にローディングを表示
$showLoader = false;

$link = mysqli_connect(
    getenv('DB_HOST') ?: 'localhost',
    getenv('DB_USER') ?: 'sportsdata_user',
    getenv('DB_PASS') ?: 'fujidai14',
    getenv('DB_NAME') ?: 'sportsdata'
);
mysqli_set_charset($link, "utf8");

$group_id = $_SESSION['group_id'];
$user_id  = $_SESSION['user_id'];

$showSuccess = isset($_GET['success']);

// 全種目のベストタイムを取得
$best_times_sql = "SELECT pool, event, distance, MIN(total_time) as best_time FROM swim_tbl WHERE group_id = ? AND user_id = ? GROUP BY pool, event, distance";
$best_stmt = mysqli_prepare($link, $best_times_sql);
mysqli_stmt_bind_param($best_stmt, "ss", $group_id, $user_id);
mysqli_stmt_execute($best_stmt);
$best_result = mysqli_stmt_get_result($best_stmt);
$bestTimes = [];
while ($row = mysqli_fetch_assoc($best_result)) {
    $key = $row['pool'] . '|' . $row['event'] . '|' . $row['distance'];
    $bestTimes[$key] = (float)$row['best_time'];
}
mysqli_stmt_close($best_stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ===== 基本情報 ===== */
    $swim_date  = $_POST['swim_date'];
    $condition  = (int)$_POST['condition'];
    $memo       = $_POST['memo'];

    // 公式戦の入力
    $is_official = isset($_POST['is_official']) && (string)$_POST['is_official'] === '1';
    if ($is_official) {
        $meet_name = trim((string)($_POST['meet_name'] ?? ''));
        $round = trim((string)($_POST['round'] ?? ''));
        $session_type = 'official';

        if ($meet_name === '') {
            $meet_name = null;
        }
        if ($round === '') {
            $round = null;
        }
    } else {
        $meet_name = null;
        $round = null;
        $session_type = 'practice';
    }

    $pool       = $_POST['pool'];
    $event      = $_POST['event'];
    $distance   = (int)$_POST['distance'];
    $total_time = (float)$_POST['total_time'];

    /* ===== ベストタイムチェック ===== */
    $best_check_sql = "SELECT MIN(total_time) as best_time FROM swim_tbl WHERE group_id = ? AND user_id = ? AND pool = ? AND event = ? AND distance = ?";
    $best_stmt = mysqli_prepare($link, $best_check_sql);
    mysqli_stmt_bind_param($best_stmt, "ssssi", $group_id, $user_id, $pool, $event, $distance);
    mysqli_stmt_execute($best_stmt);
    $best_result = mysqli_stmt_get_result($best_stmt);
    $best_row = mysqli_fetch_assoc($best_result);
    $current_best = $best_row['best_time'] ?? null;
    mysqli_stmt_close($best_stmt);

    // 新記録かチェック
    $isNewBest = ($current_best === null || $total_time < $current_best);

    /* ===== ストローク ===== */
    $stroke_data = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'stroke_') === 0) {
            $stroke_data[$key] = (int)$value;
        }
    }

    /* ===== ラップ ===== */
    $lap_data = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'lap_time_') === 0) {
            $lap_data[$key] = $value;
        }
    }

    /* ===== INSERT ===== */
    $sql = "
        INSERT INTO swim_tbl (
            group_id,
            user_id,
            swim_date,
            meet_name,
            round,
            `condition`,
            session_type,
            pool,
            event,
            distance,
            total_time,
            stroke_json,
            lap_json,
            memo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssisssidsss",
        $group_id,
        $user_id,
        $swim_date,
        $meet_name,
        $round,
        $condition,
        $session_type,
        $pool,
        $event,
        $distance,
        $total_time,
        json_encode($stroke_data, JSON_UNESCAPED_UNICODE),
        json_encode($lap_data, JSON_UNESCAPED_UNICODE),
        $memo
    );

    mysqli_stmt_execute($stmt);

    $redirect = 'swim_input.php?success=1';
    if ($tabId !== '') {
        $redirect .= '&tab_id=' . rawurlencode($tabId);
    }
    header('Location: ' . $redirect);
    exit;
}

/* =====================
   統計情報の取得
===================== */
// 総記録数
$count_sql = "SELECT COUNT(*) as total FROM swim_tbl WHERE group_id = ? AND user_id = ?";
$count_stmt = mysqli_prepare($link, $count_sql);
mysqli_stmt_bind_param($count_stmt, "ss", $group_id, $user_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_records = mysqli_fetch_assoc($count_result)['total'];
mysqli_stmt_close($count_stmt);

// 最新5件の記録
$recent_sql = "SELECT pool, event, distance, total_time, swim_date, `condition` FROM swim_tbl WHERE group_id = ? AND user_id = ? ORDER BY swim_date DESC, created_at DESC LIMIT 5";
$recent_stmt = mysqli_prepare($link, $recent_sql);
mysqli_stmt_bind_param($recent_stmt, "ss", $group_id, $user_id);
mysqli_stmt_execute($recent_stmt);
$recent_result = mysqli_stmt_get_result($recent_stmt);
$recent_records = [];
while ($row = mysqli_fetch_assoc($recent_result)) {
    $recent_records[] = $row;
}
mysqli_stmt_close($recent_stmt);

// ベストタイム上位5件
$best_sql = "SELECT pool, event, distance, MIN(total_time) as best_time FROM swim_tbl WHERE group_id = ? AND user_id = ? GROUP BY pool, event, distance ORDER BY best_time ASC LIMIT 5";
$best_stmt = mysqli_prepare($link, $best_sql);
mysqli_stmt_bind_param($best_stmt, "ss", $group_id, $user_id);
mysqli_stmt_execute($best_stmt);
$best_result = mysqli_stmt_get_result($best_stmt);
$best_records = [];
while ($row = mysqli_fetch_assoc($best_result)) {
    $best_records[] = $row;
}
mysqli_stmt_close($best_stmt);

// 種目別の記録数
$event_count_sql = "SELECT event, COUNT(*) as count FROM swim_tbl WHERE group_id = ? AND user_id = ? GROUP BY event ORDER BY count DESC";
$event_count_stmt = mysqli_prepare($link, $event_count_sql);
mysqli_stmt_bind_param($event_count_stmt, "ss", $group_id, $user_id);
mysqli_stmt_execute($event_count_stmt);
$event_count_result = mysqli_stmt_get_result($event_count_stmt);
$event_counts = [];
while ($row = mysqli_fetch_assoc($event_count_result)) {
    $event_counts[] = $row;
}
mysqli_stmt_close($event_count_stmt);

$NAV_BASE = '..';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../../HTML/swim_input.html.php';
