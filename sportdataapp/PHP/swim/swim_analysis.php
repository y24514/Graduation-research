<?php
require_once __DIR__ . '/../session_bootstrap.php';

/* ---------------------
   セッションチェック
--------------------- */
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: ../login.php');
    exit;
}

// ページリロード時にローディングを表示
$showLoader = false;

$group_id = $_SESSION['group_id'];
$user_id  = $_SESSION['user_id'];

/* =====================
   DB接続 (環境変数優先)
   実運用では .env などで環境変数を設定してください
===================== */
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'y24514';
$dbPass = getenv('DB_PASS') ?: 'Kr96main0303';
$dbName = getenv('DB_NAME') ?: 'sportdata_db';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    error_log('DB connect error: ' . mysqli_connect_error());
    http_response_code(500);
    echo 'データベース接続に失敗しました。';
    exit;
}
mysqli_set_charset($link, "utf8");

function render_no_data_alert(string $redirect, string $message): void {
    header('Content-Type: text/html; charset=UTF-8');
    $redirectJs = json_encode($redirect, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $messageJs = json_encode($message, JSON_UNESCAPED_UNICODE);

    echo "<!doctype html><html lang=\"ja\"><head>";
    echo "<meta charset=\"utf-8\">";
    echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";
    echo "<title>水泳｜分析</title>";
    echo "<link rel=\"icon\" type=\"image/svg+xml\" href=\"../../img/favicon.svg\">";
    echo "<link rel=\"stylesheet\" href=\"../../css/site.css\">";
    echo "<link rel=\"stylesheet\" href=\"../../css/swim_input.css\">";
    echo "<link rel=\"stylesheet\" href=\"../../css/swim_alert.css\">";
    echo "</head><body>";

    echo "<div class=\"swim-alert-shell\">";
    echo "  <div class=\"swim-alert-backdrop\" id=\"swimAlertBackdrop\" hidden>";
    echo "    <div class=\"swim-alert-modal\" role=\"dialog\" aria-modal=\"true\" aria-labelledby=\"swimAlertTitle\" aria-describedby=\"swimAlertMessage\">";
    echo "      <div class=\"swim-alert-header\"><h1 class=\"swim-alert-title\" id=\"swimAlertTitle\">お知らせ</h1></div>";
    echo "      <div class=\"swim-alert-body\">";
    echo "        <p class=\"swim-alert-message\" id=\"swimAlertMessage\"></p>";
    echo "        <div class=\"swim-alert-actions\">";
    echo "          <button type=\"button\" class=\"swim-alert-btn\" id=\"swimAlertGo\">記録入力へ</button>";
    echo "        </div>";
    echo "      </div>";
    echo "    </div>";
    echo "  </div>";
    echo "</div>";

    echo "<script>\n";
    echo "(() => {\n";
    echo "  const redirect = {$redirectJs};\n";
    echo "  const message = {$messageJs};\n";
    echo "  const backdrop = document.getElementById('swimAlertBackdrop');\n";
    echo "  const msgEl = document.getElementById('swimAlertMessage');\n";
    echo "  const goBtn = document.getElementById('swimAlertGo');\n";
    echo "  msgEl.textContent = message;\n";
    echo "  backdrop.hidden = false;\n";
    echo "  goBtn.addEventListener('click', () => location.replace(redirect));\n";
    echo "  goBtn.focus();\n";
    echo "})();\n";
    echo "</script>";

    echo "</body></html>";
    exit;
}

// GET パラメータでの選択受け取り (combo=pool|event|distance を想定)
$selected_combo = $_GET['combo'] ?? null;
$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$meet_name = isset($_GET['meet_name']) ? trim((string)$_GET['meet_name']) : null;
if ($meet_name === '') {
    $meet_name = null;
}
$sel_pool = null; $sel_event = null; $sel_distance = null;
if ($selected_combo) {
    $parts = explode('|', $selected_combo);
    if (count($parts) === 3) {
        $sel_pool = $parts[0];
        $sel_event = $parts[1];
        $sel_distance = $parts[2];
    }
}

/* 種目一覧を取得（ドロップダウン用） */
$combo_sql = "SELECT pool, event, distance FROM swim_tbl WHERE group_id=? AND user_id=? GROUP BY pool, event, distance ORDER BY pool, event, distance";
$combo_stmt = mysqli_prepare($link, $combo_sql);
$combos = [];
if ($combo_stmt) {
    mysqli_stmt_bind_param($combo_stmt, "ss", $group_id, $user_id);
    if (mysqli_stmt_execute($combo_stmt)) {
        $r = mysqli_stmt_get_result($combo_stmt);
        while ($row = mysqli_fetch_assoc($r)) {
            $combos[] = $row;
        }
    } else {
        error_log('Execute failed (combos): ' . mysqli_stmt_error($combo_stmt));
    }
    mysqli_stmt_close($combo_stmt);
}

// 記録が1件もない場合は、入力を促して記録画面へ誘導
if (empty($combos)) {
    render_no_data_alert('swim_input.php', '水泳の記録データがありません。先に記録を入力してください。');
}

/* =====================
   最新記録（今回）
===================== */

// 選択された種目があればその種目の最新記録を取得、なければ全体の最新を取得
if ($sel_pool && $sel_event && $sel_distance) {
    $sql = "
        SELECT pool, event, distance, total_time
        FROM swim_tbl
        WHERE group_id=? AND user_id=?
          AND pool=? AND event=? AND distance=?
        ORDER BY created_at DESC
        LIMIT 1
    ";
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        error_log('Prepare failed (current filtered): ' . mysqli_error($link) . ' SQL: ' . $sql);
        http_response_code(500);
        echo 'システムエラーが発生しました。';
        exit;
    }
    $d_param = is_numeric($sel_distance) ? (int)$sel_distance : $sel_distance;
    mysqli_stmt_bind_param($stmt, "ssssi", $group_id, $user_id, $sel_pool, $sel_event, $d_param);
} else {
    $sql = "
        SELECT pool, event, distance, total_time
        FROM swim_tbl
        WHERE group_id=? AND user_id=?
        ORDER BY created_at DESC
        LIMIT 1
    ";
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        error_log('Prepare failed (current): ' . mysqli_error($link) . ' SQL: ' . $sql);
        http_response_code(500);
        echo 'システムエラーが発生しました。';
        exit;
    }
    mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
}

if (!mysqli_stmt_execute($stmt)) {
    error_log('Execute failed (current): ' . mysqli_stmt_error($stmt));
    echo 'システムエラーが発生しました。';
    exit;
}
$res = mysqli_stmt_get_result($stmt);
$current = mysqli_fetch_assoc($res);

if (!$current) {
    render_no_data_alert('swim_input.php', '水泳の記録データがありません。先に記録を入力してください。');
}

$pool     = $current['pool'];
$event    = $current['event'];
$distance = $current['distance'];
$now_raw  = $current['total_time'];

// total_time が文字列なら秒に変換するヘルパー
function parse_time_to_seconds($t) {
    if ($t === null || $t === '') return null;
    if (is_numeric($t)) return (float)$t;
    $t = trim($t);
    if (strpos($t, ':') !== false) {
        $parts = explode(':', $t);
        // h:mm:ss
        if (count($parts) === 3) {
            $h = intval($parts[0]);
            $m = intval($parts[1]);
            $s = floatval($parts[2]);
            return $h * 3600 + $m * 60 + $s;
        }
        // mm:ss
        if (count($parts) === 2) {
            $m = intval($parts[0]);
            $s = floatval($parts[1]);
            return $m * 60 + $s;
        }
    }
    return floatval($t);
}

$now_time = parse_time_to_seconds($now_raw);

// Ambiguous 3-part parsing fallback: treat "m:s:cs" (e.g. 1:31:00 -> 1m31.00s)
function parse_time_ambiguous_ms_cs($t) {
    if ($t === null || $t === '') return null;
    if (is_numeric($t)) return (float)$t;
    $t = trim($t);
    if (strpos($t, ':') === false) return parse_time_to_seconds($t);
    $parts = explode(':', $t);
    if (count($parts) === 3) {
        $m = intval($parts[0]);
        $s = intval($parts[1]);
        $cs = intval($parts[2]);
        return $m * 60 + $s + ($cs / 100.0);
    }
    return parse_time_to_seconds($t);
}

/* =====================
   前回記録（同条件）
===================== */
$sql = "
    SELECT total_time
    FROM swim_tbl
    WHERE group_id=? AND user_id=?
      AND pool=? AND event=? AND distance=?
    ORDER BY created_at DESC
    LIMIT 1 OFFSET 1
";
$stmt = mysqli_prepare($link, $sql);
if (!$stmt) {
    error_log('Prepare failed (prev): ' . mysqli_error($link) . ' SQL: ' . $sql);
    $prev_time = null;
} else {
    $d_param = is_numeric($distance) ? (int)$distance : $distance;
    mysqli_stmt_bind_param($stmt, "ssssi", $group_id, $user_id, $pool, $event, $d_param);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Execute failed (prev): ' . mysqli_stmt_error($stmt));
        $prev_time = null;
    } else {
        $res = mysqli_stmt_get_result($stmt);
        $prev = mysqli_fetch_assoc($res);
        $prev_raw = $prev['total_time'] ?? null;
        $prev_time = parse_time_to_seconds($prev_raw);
    }
}

/* =====================
   自己ベスト
===================== */
$sql = "
    SELECT best_time
    FROM swim_best_tbl
    WHERE group_id=? AND user_id=?
      AND pool=? AND event=? AND distance=?
    LIMIT 1
";
$stmt = mysqli_prepare($link, $sql);
if (!$stmt) {
    error_log('Prepare failed (best): ' . mysqli_error($link) . ' SQL: ' . $sql);
    $best_time = null;
} else {
    $d_param = is_numeric($distance) ? (int)$distance : $distance;
    mysqli_stmt_bind_param($stmt, "ssssi", $group_id, $user_id, $pool, $event, $d_param);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Execute failed (best): ' . mysqli_stmt_error($stmt));
        $best_time = null;
    } else {
        $res = mysqli_stmt_get_result($stmt);
        $best = mysqli_fetch_assoc($res);
        $best_raw = $best['best_time'] ?? null;
        $best_time = parse_time_to_seconds($best_raw);
    }
}

// フォールバック: swim_best_tbl に値がなければ、履歴テーブルから最小値を取得して自己ベスト扱いにする
if ($best_time === null) {
    $sql_min = "SELECT MIN(total_time) AS min_time FROM swim_tbl WHERE group_id=? AND user_id=? AND pool=? AND event=? AND distance=?";
    $stmt_min = mysqli_prepare($link, $sql_min);
    if ($stmt_min) {
        $d_param = is_numeric($distance) ? (int)$distance : $distance;
        mysqli_stmt_bind_param($stmt_min, "ssssi", $group_id, $user_id, $pool, $event, $d_param);
        if (mysqli_stmt_execute($stmt_min)) {
            $rmin = mysqli_stmt_get_result($stmt_min);
            $rowmin = mysqli_fetch_assoc($rmin);
            $min_time = $rowmin['min_time'] ?? null;
            $best_time = parse_time_to_seconds($min_time);
        }
        mysqli_stmt_close($stmt_min);
    }
}

/* =====================
   推移データ（期間フィルター対応）
===================== */
$where_date = "";
$where_meet = "";
$bind_types = "ssssi";
$bind_params = [$group_id, $user_id, $pool, $event, is_numeric($distance) ? (int)$distance : $distance];

if ($date_from && $date_to) {
    $where_date = " AND swim_date BETWEEN ? AND ?";
    $bind_types .= "ss";
    $bind_params[] = $date_from;
    $bind_params[] = $date_to;
} elseif ($date_from) {
    $where_date = " AND swim_date >= ?";
    $bind_types .= "s";
    $bind_params[] = $date_from;
} elseif ($date_to) {
    $where_date = " AND swim_date <= ?";
    $bind_types .= "s";
    $bind_params[] = $date_to;
}

// 大会名フィルター
if ($meet_name !== null) {
    $where_meet = " AND meet_name = ?";
    $bind_types .= "s";
    $bind_params[] = $meet_name;
}

// 大会名一覧（ドロップダウン用）: 現在選択中の種目/距離 + 期間条件の範囲で抽出
$meet_options = [];
$meet_stmt = mysqli_prepare(
    $link,
    "SELECT DISTINCT meet_name FROM swim_tbl WHERE group_id=? AND user_id=? AND pool=? AND event=? AND distance=? {$where_date} AND meet_name IS NOT NULL AND meet_name <> '' ORDER BY meet_name ASC"
);
if ($meet_stmt) {
    $meet_bind_types = "ssssi";
    $meet_bind_params = [$group_id, $user_id, $pool, $event, is_numeric($distance) ? (int)$distance : $distance];
    if ($date_from && $date_to) {
        $meet_bind_types .= "ss";
        $meet_bind_params[] = $date_from;
        $meet_bind_params[] = $date_to;
    } elseif ($date_from) {
        $meet_bind_types .= "s";
        $meet_bind_params[] = $date_from;
    } elseif ($date_to) {
        $meet_bind_types .= "s";
        $meet_bind_params[] = $date_to;
    }

    mysqli_stmt_bind_param($meet_stmt, $meet_bind_types, ...$meet_bind_params);
    if (mysqli_stmt_execute($meet_stmt)) {
        $mr = mysqli_stmt_get_result($meet_stmt);
        while ($mrow = mysqli_fetch_assoc($mr)) {
            $name = trim((string)($mrow['meet_name'] ?? ''));
            if ($name !== '') {
                $meet_options[] = $name;
            }
        }
    }
    mysqli_stmt_close($meet_stmt);
}

$sql = "
        SELECT swim_date, total_time, lap_json, stroke_json, `condition`, memo, created_at
    FROM swim_tbl
    WHERE group_id=? AND user_id=?
      AND pool=? AND event=? AND distance=?
            {$where_date}
            {$where_meet}
    ORDER BY swim_date ASC, created_at ASC
";
$stmt = mysqli_prepare($link, $sql);
if (!$stmt) {
    error_log('Prepare failed (history): ' . mysqli_error($link) . ' SQL: ' . $sql);
    $history = [];
} else {
    mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_params);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Execute failed (history): ' . mysqli_stmt_error($stmt));
        $history = [];
    } else {
        $res = mysqli_stmt_get_result($stmt);
        $history = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $raw = $row['total_time'];
            $row['raw_total_time'] = $raw;
            $row['total_time'] = parse_time_to_seconds($raw);
            $history[] = $row;
        }
    }
}

if (isset($stmt) && $stmt) mysqli_stmt_close($stmt);

/* =====================
   ラップタイム比較データ
===================== */
// 最新10件のラップタイムとストロークを取得（選択された種目に限定）
if ($sel_pool && $sel_event && $sel_distance) {
    // フィルタがある場合は選択された種目のみ
    $lap_where_date = "";
    $lap_where_meet = "";
    $lap_bind_types = "ssssi";
    $lap_bind_params = [$group_id, $user_id, $sel_pool, $sel_event, is_numeric($sel_distance) ? (int)$sel_distance : $sel_distance];

    if ($date_from && $date_to) {
        $lap_where_date = " AND swim_date BETWEEN ? AND ?";
        $lap_bind_types .= "ss";
        $lap_bind_params[] = $date_from;
        $lap_bind_params[] = $date_to;
    } elseif ($date_from) {
        $lap_where_date = " AND swim_date >= ?";
        $lap_bind_types .= "s";
        $lap_bind_params[] = $date_from;
    } elseif ($date_to) {
        $lap_where_date = " AND swim_date <= ?";
        $lap_bind_types .= "s";
        $lap_bind_params[] = $date_to;
    }

    if ($meet_name !== null) {
        $lap_where_meet = " AND meet_name = ?";
        $lap_bind_types .= "s";
        $lap_bind_params[] = $meet_name;
    }

    $lap_compare_sql = "SELECT pool, event, distance, total_time, stroke_json, lap_json, swim_date 
                        FROM swim_tbl 
                        WHERE group_id = ? AND user_id = ? 
                          AND pool = ? AND event = ? AND distance = ?
                          {$lap_where_date}
                          {$lap_where_meet}
                        ORDER BY swim_date DESC, created_at DESC 
                        LIMIT 10";
    $lap_stmt = mysqli_prepare($link, $lap_compare_sql);
    $lap_comparison_data = [];

    if ($lap_stmt) {
        mysqli_stmt_bind_param($lap_stmt, $lap_bind_types, ...$lap_bind_params);
        if (mysqli_stmt_execute($lap_stmt)) {
            $lap_result = mysqli_stmt_get_result($lap_stmt);
            
            while ($row = mysqli_fetch_assoc($lap_result)) {
                $stroke_data = json_decode($row['stroke_json'], true);
                $lap_data = json_decode($row['lap_json'], true);
                
                if ($stroke_data && $lap_data) {
                    foreach ($lap_data as $key => $lap_time) {
                        if (preg_match('/lap_time_(\d+)/', $key, $matches)) {
                            $lap_distance = (int)$matches[1];
                            $stroke_key = 'stroke_' . $lap_distance;
                            $stroke_count = isset($stroke_data[$stroke_key]) ? (int)$stroke_data[$stroke_key] : 0;
                            
                            $event_key = $row['event'] . '_' . $lap_distance . 'm';
                            
                            if (!isset($lap_comparison_data[$event_key])) {
                                $lap_comparison_data[$event_key] = [
                                    'event' => $row['event'],
                                    'distance' => $lap_distance,
                                    'records' => []
                                ];
                            }
                            
                            $lap_comparison_data[$event_key]['records'][] = [
                                'date' => $row['swim_date'],
                                'time' => (float)$lap_time,
                                'stroke' => $stroke_count
                            ];
                        }
                    }
                }
            }
        }
        mysqli_stmt_close($lap_stmt);
    }
} else {
    // フィルタなしの場合は全種目
    $lap_compare_sql = "SELECT pool, event, distance, total_time, stroke_json, lap_json, swim_date 
                        FROM swim_tbl 
                        WHERE group_id = ? AND user_id = ? 
                        ORDER BY swim_date DESC, created_at DESC 
                        LIMIT 10";
    $lap_stmt = mysqli_prepare($link, $lap_compare_sql);
    $lap_comparison_data = [];

    if ($lap_stmt) {
        mysqli_stmt_bind_param($lap_stmt, "ss", $group_id, $user_id);
        if (mysqli_stmt_execute($lap_stmt)) {
            $lap_result = mysqli_stmt_get_result($lap_stmt);
            
            while ($row = mysqli_fetch_assoc($lap_result)) {
                $stroke_data = json_decode($row['stroke_json'], true);
                $lap_data = json_decode($row['lap_json'], true);
                
                if ($stroke_data && $lap_data) {
                    foreach ($lap_data as $key => $lap_time) {
                        if (preg_match('/lap_time_(\d+)/', $key, $matches)) {
                            $lap_distance = (int)$matches[1];
                            $stroke_key = 'stroke_' . $lap_distance;
                            $stroke_count = isset($stroke_data[$stroke_key]) ? (int)$stroke_data[$stroke_key] : 0;
                            
                            $event_key = $row['event'] . '_' . $lap_distance . 'm';
                            
                            if (!isset($lap_comparison_data[$event_key])) {
                                $lap_comparison_data[$event_key] = [
                                    'event' => $row['event'],
                                    'distance' => $lap_distance,
                                    'records' => []
                                ];
                            }
                            
                            $lap_comparison_data[$event_key]['records'][] = [
                                'date' => $row['swim_date'],
                                'time' => (float)$lap_time,
                                'stroke' => $stroke_count
                            ];
                        }
                    }
                }
            }
        }
        mysqli_stmt_close($lap_stmt);
    }
}

// 各種目の最新と前回の比較を計算
foreach ($lap_comparison_data as $key => &$data) {
    if (count($data['records']) >= 2) {
        // 日付でソート（最新が先頭）
        usort($data['records'], function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        $latest = $data['records'][0];
        $previous = $data['records'][1];
        
        $data['latest_time'] = $latest['time'];
        $data['latest_stroke'] = $latest['stroke'];
        $data['previous_time'] = $previous['time'];
        $data['previous_stroke'] = $previous['stroke'];
        $data['time_diff'] = $latest['time'] - $previous['time'];
        $data['stroke_diff'] = $latest['stroke'] - $previous['stroke'];
        $data['record_count'] = count($data['records']);
    }
}
unset($data);

mysqli_close($link);

// Post-normalization: if dataset median is short (<10min) but some parsed values are hours,
// reinterpret ambiguous 3-part raw strings as mm:ss:cs (common data-entry like "1:31:00" meant 1:31.00)
$numeric_times = array_filter(array_map(function($r){ return $r['total_time']; }, $history), function($v){ return $v !== null; });
$count = count($numeric_times);
$big_count = 0;
foreach ($numeric_times as $v) { if ($v > 3600) $big_count++; }
$median = null;
if ($count > 0) {
    sort($numeric_times);
    $mid = (int) floor($count/2);
    $median = $numeric_times[$mid];
}
if ($median !== null && $median < 600 && $big_count > 0) {
    foreach ($history as &$r) {
        if (isset($r['raw_total_time']) && substr_count($r['raw_total_time'], ':') === 2) {
            $r['total_time'] = parse_time_ambiguous_ms_cs($r['raw_total_time']);
        }
    }
    unset($r);
    if (isset($now_raw) && substr_count($now_raw, ':') === 2 && $now_time !== null && $now_time > 3600) {
        $now_time = parse_time_ambiguous_ms_cs($now_raw);
    }
    if (isset($prev_raw) && substr_count($prev_raw, ':') === 2 && $prev_time !== null && $prev_time > 3600) {
        $prev_time = parse_time_ambiguous_ms_cs($prev_raw);
    }
    if (isset($best_raw) && substr_count($best_raw, ':') === 2 && $best_time !== null && $best_time > 3600) {
        $best_time = parse_time_ambiguous_ms_cs($best_raw);
    }
}

/* =====================
   統計情報の計算
===================== */
$stats = [
    'count' => 0,
    'avg' => null,
    'min' => null,
    'max' => null,
    'std_dev' => null,
    'improvement_rate' => null
];

if (count($history) > 0) {
    $times = array_filter(array_column($history, 'total_time'), function($v) { return $v !== null; });
    if (count($times) > 0) {
        $stats['count'] = count($times);
        $stats['avg'] = array_sum($times) / count($times);
        $stats['min'] = min($times);
        $stats['max'] = max($times);
        
        // 標準偏差
        if (count($times) > 1) {
            $variance = 0.0;
            foreach ($times as $t) {
                $variance += pow($t - $stats['avg'], 2);
            }
            $stats['std_dev'] = sqrt($variance / count($times));
        }
        
        // 改善率（最初の記録と最新の記録を比較）
        if (count($times) >= 2) {
            $first_time = $times[0];
            $last_time = $times[count($times) - 1];
            if ($first_time > 0) {
                $stats['improvement_rate'] = (($first_time - $last_time) / $first_time) * 100;
            }
        }
    }
}

/* 種目名変換 */
$event_map = [
    "fly" => "バタフライ",
    "ba"  => "背泳ぎ",
    "br"  => "平泳ぎ",
    "fr"  => "自由形",
    "im"  => "個人メドレー"
];

$NAV_BASE = '..';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../../HTML/swim_analysis.html.php';
