<?php
require_once __DIR__ . '/../session_bootstrap.php';

require_once __DIR__ . '/db.php'; // DB共通ファイルを読み込む

// 共通ナビ用：アクセスURLに応じてPHPルートへの相対パスを切り替える
// - /PHP/T_MNO/* で開いている場合: ..
// - /T_MNO/* (互換ラッパー経由) の場合: ../PHP
$uri = $_SERVER['REQUEST_URI'] ?? '';
$NAV_BASE = (stripos($uri, '/PHP/T_MNO/') !== false) ? '..' : '../PHP';

$db = getDbConnection();
$db_status = "";

// --- 1. データの読み込み・保存ロジック ---
if (isset($_GET['id'])) {
    // 履歴から表示する場合（db.phpの関数を使用）
    $data = getGameDetail($db, intval($_GET['id']));
    if (!$data) die("データが見つかりません。");

    $gameData = $data['game'];
    $history  = $data['actions'];
    $d = [
        'teamA'  => $gameData['team_a'], 
        'teamB'  => $gameData['team_b'],
        'gamesA' => $gameData['games_a'], 
        'gamesB' => $gameData['games_b'],
        'a1'     => $gameData['player_a1'] ?? '', 
        'a2'     => $gameData['player_a2'] ?? '',
        'b1'     => $gameData['player_b1'] ?? '', 
        'b2'     => $gameData['player_b2'] ?? ''
    ];
    $db_status = "📊 記録済みデータを表示中";
} else if (isset($_SESSION['data'])) {
    // 試合終了直後の場合
    $d = $_SESSION['data'];
    $history = $d['history'];

    // 管理者閲覧（group 単位）用に、保存データへ紐づけ情報を追加
    if (!isset($d['group_id']) && isset($_SESSION['group_id'])) {
        $d['group_id'] = (string)$_SESSION['group_id'];
    }
    if (!isset($d['saved_by_user_id']) && isset($_SESSION['user_id'])) {
        $d['saved_by_user_id'] = (string)$_SESSION['user_id'];
    }

    if (!isset($_SESSION['last_saved_id'])) {
        // 保存未完了なら保存（db.phpの関数を使用）
        $_SESSION['last_saved_id'] = saveGameResult($db, $d);
        $db_status = "✓ データを保存しました";
    } else {
        $db_status = "✓ 保存済みデータを表示中";
    }
} else {
    header("Location: index.php"); exit;
}

// --- 2. スタッツ集計 (ロジックは変更なし) ---
$activePlayers = [];
foreach ($history as $h) {
    $p = $h['player_name'] ?? $h['player'];
    if (!in_array($p, $activePlayers)) $activePlayers[] = $p;
}

function initActionArray() {
    return [
        "サービスエース"=>0, "スマッシュ"=>0, "ボレー"=>0, "ストローク"=>0, "リターンエース"=>0, "ネットイン"=>0,
        "ダブルフォルト"=>0, "アウト"=>0, "ネット"=>0, "ネットタッチ"=>0, "オーバーネット"=>0, "ボディタッチ"=>0,
        "ダイレクト"=>0, "チップ"=>0
    ];
}

$stats = [];
foreach ($activePlayers as $name) { $stats[$name] = initActionArray(); }
$totalAllActions = count($history) ?: 1;
foreach ($history as $h) {
    $p = $h['player_name'] ?? $h['player'];
    $a = $h['action_type'] ?? $h['action'];
    if (isset($stats[$p][$a])) { $stats[$p][$a]++; }
}

function calcKPI($s, $totalAllActions) {
    $gain = $s["サービスエース"] + $s["スマッシュ"] + $s["ボレー"] + $s["ストローク"] + $s["リターンエース"] + $s["ネットイン"];
    $lose = $s["ダブルフォルト"] + $s["アウト"] + $s["ネット"] + $s["ネットタッチ"] + $s["オーバーネット"] + $s["ボディタッチ"] + $s["ダイレクト"] + $s["チップ"];
    $total = array_sum($s) ?: 1;
    return [
        "scoreRate" => round(($gain/$total)*100), "loseRate" => round(($lose/$total)*100),
        "gain" => $gain, "lose" => $lose, "plusminus" => $gain-$lose,
        "involvement" => round(($total/$totalAllActions)*100),
        "diversity" => round((count(array_filter($s))/14)*100),
        "stability" => round((1-($lose/$total))*100)
    ];
}

$kpiLabels = ["scoreRate"=>"得点率", "loseRate"=>"失点率", "gain"=>"得点数", "lose"=>"失点数", "plusminus"=>"+/-", "involvement"=>"関与率", "diversity"=>"多様性", "stability"=>"安定性"];
$winnerTeam = ($d['gamesA'] > $d['gamesB']) ? $d['teamA'] : $d['teamB'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=1024">
    <title>分析レポート</title>
    <link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: sans-serif; margin:0; background:#f4f7f9; color: #333; }
        .app-header { height: 60px; background: #2c3e50; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; position: sticky; top:0; z-index: 100; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { padding: 15px; max-width: 900px; margin: auto; }
        .card { background: white; padding: 20px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 15px; }
        .winner-card { border-top: 10px solid #f1c40f; text-align: center; }
        .score-display { display: flex; justify-content: center; align-items: center; gap: 20px; margin: 15px 0; }
        .game-num { font-size: 42px; font-weight: bold; color: #2c3e50; }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 15px; }
        .player-card { background: white; border-radius: 16px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); position: relative; }
        .kpi-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f0f3f5; font-size: 13px; }
        .action-details { background: rgba(255,255,255,0.6); padding: 10px; border-radius: 10px; margin: 10px 0; font-size: 11px; display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
        .action-item { display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .plus { color: #2980b9; font-weight: bold; } .minus { color: #c0392b; font-weight: bold; }
        .btn-home { display: block; width: 100%; padding: 15px; background: #2c3e50; color: white; text-decoration: none; border-radius: 10px; font-weight: bold; margin-top: 10px; text-align: center; box-sizing: border-box; }
        canvas { margin-top: 10px; }
    </style>
    <link rel="stylesheet" href="../../css/tennis.css">
</head>
<body class="tennis-page tennis-result">
<?php require_once __DIR__ . '/../header.php'; ?>
<header class="app-header">分析レポート</header>
<div class="container">
    <div class="card winner-card">
        <div style="font-size:32px;">🏆</div>
        <div style="font-size: 20px; font-weight: bold; color: #2c3e50;">勝利: <?= htmlspecialchars($winnerTeam) ?></div>
        <div class="score-display">
            <div style="text-align:center;"><div class="game-num"><?= $d['gamesA'] ?></div><div style="font-size:12px; font-weight:bold; color:#7f8c8d;"><?= htmlspecialchars($d['teamA']) ?></div></div>
            <div style="font-size: 24px; color: #bdc3c7;">vs</div>
            <div style="text-align:center;"><div class="game-num"><?= $d['gamesB'] ?></div><div style="font-size:12px; font-weight:bold; color:#7f8c8d;"><?= htmlspecialchars($d['teamB']) ?></div></div>
        </div>
        <div style="display:inline-block; padding: 4px 12px; background: #e8f5e9; color:#27ae60; border-radius: 20px; font-size:11px; font-weight:bold;"><?= $db_status ?></div>
    </div>

    <div class="kpi-grid">
        <?php 
        foreach ($stats as $pName => $s): 
            $kpi = calcKPI($s, $totalAllActions); 
            $chartId = "chart_".md5($pName); 

            // --- チーム判定ロジック ---
            $teamAList = array_filter([$d['a1'], $d['a2']]);
            $isTeamA = in_array($pName, $teamAList);
            
            if ($isTeamA) {
                $tName = $d['teamA']; $cBorder = '#3498db'; $cBg = 'rgba(52, 152, 219, 0.2)'; $cardBg = '#f0f7ff';
            } else {
                $tName = $d['teamB']; $cBorder = '#e74c3c'; $cBg = 'rgba(231, 76, 60, 0.2)'; $cardBg = '#fff5f5';
            }
        ?>
        <div class="player-card" style="background: <?= $cardBg ?>; border-top: 6px solid <?= $cBorder ?>;">
            <div style="font-size: 10px; font-weight: bold; color: <?= $cBorder ?>; margin-bottom: 4px; text-transform: uppercase;">TEAM: <?= htmlspecialchars($tName) ?></div>
            <div style="font-weight: bold; margin-bottom: 10px; display: flex; align-items: center; font-size: 16px;">
                <span style="background: <?= $cBorder ?>; width: 4px; height: 18px; display: inline-block; margin-right: 8px; border-radius: 2px;"></span>
                <?= htmlspecialchars($pName) ?>
            </div>
            
            <div class="kpi-table">
                <?php foreach ($kpi as $key => $val): ?>
                    <div class="kpi-row">
                        <span style="color: #7f8c8d;"><?= $kpiLabels[$key] ?></span>
                        <span class="<?= ($key=='plusminus') ? ($val>=0?'plus':'minus') : '' ?>">
                            <?= ($key=='plusminus' && $val>0) ? '+' : '' ?><?= $val ?><?= in_array($key, ["scoreRate","loseRate","involvement","diversity","stability"]) ? '%' : '' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="font-size: 11px; font-weight: bold; margin-top: 15px; color: #2c3e50;">📝 アクション内訳</div>
            <div class="action-details">
                <?php foreach ($s as $actionName => $count): if($count > 0): ?>
                    <div class="action-item">
                        <span style="color: #666;"><?= $actionName ?></span>
                        <span style="font-weight: bold;"><?= $count ?></span>
                    </div>
                <?php endif; endforeach; ?>
            </div>

            <canvas id="<?= $chartId ?>"></canvas>
            <script>
            new Chart(document.getElementById("<?= $chartId ?>"), {
                type: "radar",
                data: {
                    labels: ["得点率","失点率","関与率","多様性","安定性"],
                    datasets: [{
                        data: [<?= $kpi['scoreRate'] ?>, <?= $kpi['loseRate'] ?>, <?= $kpi['involvement'] ?>, <?= $kpi['diversity'] ?>, <?= $kpi['stability'] ?>],
                        borderColor: '<?= $cBorder ?>', backgroundColor: '<?= $cBg ?>', borderWidth: 3, pointBackgroundColor: '<?= $cBorder ?>', pointRadius: 3
                    }]
                },
                options: { 
                    scales: { r: { min: 0, max: 100, ticks: { display: false }, grid: { color: 'rgba(0,0,0,0.05)' }, angleLines: { color: 'rgba(0,0,0,0.05)' }, pointLabels: { font: { size: 10, weight: 'bold' } } } },
                    plugins: { legend: { display: false } }
                }
            });
            </script>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="display:flex; gap:10px; margin-top:20px;">
        <a href="history.php" class="btn-home" style="background:#7f8c8d; flex:1;">履歴一覧</a>
        <a href="index.php" class="btn-home" style="flex:1;">新しい試合を開始</a>
    </div>
</div>
</body>
</html>