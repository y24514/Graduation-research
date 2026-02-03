<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/basketball_logic/db_config.php';

$NAV_BASE = '.';

$gameIdRaw = $_GET['id'] ?? null;
$gameId = is_string($gameIdRaw) ? (int)$gameIdRaw : 0;
if ($gameId <= 0) {
    http_response_code(400);
    die('試合IDが指定されていません。');
}

// 1. 試合の基本情報を取得
$stmt = $pdo->prepare('SELECT * FROM games WHERE id = ?');
$stmt->execute([$gameId]);
$gameInfo = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$gameInfo) {
    http_response_code(404);
    die('試合データが見つかりません。');
}

// 2. その試合の全アクションを取得
$stmtAct = $pdo->prepare('SELECT * FROM game_actions WHERE game_id = ? ORDER BY id ASC');
$stmtAct->execute([$gameId]);
$actions = $stmtAct->fetchAll(PDO::FETCH_ASSOC);

// 3. データ集計（DB保存版の action_type に合わせる）
$trendA = [0, 0, 0, 0]; // Q1, Q2, Q3, Q4
$trendB = [0, 0, 0, 0];
$finalStats = ['A' => [], 'B' => []];

foreach ($actions as $a) {
    $t = (string)($a['team'] ?? '');
    if ($t !== 'A' && $t !== 'B') continue;

    $q = (int)($a['quarter'] ?? 4) - 1;
    if ($q < 0 || $q > 3) $q = 3;

    $actionType = (string)($a['action_type'] ?? '');
    $result = (string)($a['result'] ?? '');
    $point = (int)($a['point'] ?? 0);

    // チーム得点推移
    if ($actionType === 'shot' && $result === 'success') {
        if ($t === 'A') $trendA[$q] += $point;
        if ($t === 'B') $trendB[$q] += $point;
    }

    // 個人スタッツ
    $pname = (string)($a['player_name'] ?? '');
    if ($pname === '') $pname = 'Unknown';

    if (!isset($finalStats[$t][$pname])) {
        $finalStats[$t][$pname] = [
            'pts' => 0,
            'p1_m' => 0, 'p1_a' => 0,
            'p2_m' => 0, 'p2_a' => 0,
            'p3_m' => 0, 'p3_a' => 0,
            'foul' => 0,
            'to' => 0,
        ];
    }
    $s = &$finalStats[$t][$pname];

    if ($actionType === 'shot') {
        if ($point === 1) { $s['p1_a']++; if ($result === 'success') { $s['p1_m']++; $s['pts'] += 1; } }
        if ($point === 2) { $s['p2_a']++; if ($result === 'success') { $s['p2_m']++; $s['pts'] += 2; } }
        if ($point === 3) { $s['p3_a']++; if ($result === 'success') { $s['p3_m']++; $s['pts'] += 3; } }
    } elseif ($actionType === 'foul') {
        $s['foul']++;
    } elseif ($actionType === 'to' || $actionType === 'turnover') {
        $s['to']++;
    }
    unset($s);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>試合結果詳細 - BasketLog</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --teamA: #3498db; --teamB: #e74c3c; --dark: #2c3e50; }
        body { font-family: sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
        .page-content { padding: 15px; }
        .container { max-width: 700px; margin: auto; }

        .final-header { background: var(--dark); color: white; padding: 30px 20px; border-radius: 20px; text-align: center; margin-bottom: 20px; }
        .score-row { display: flex; justify-content: space-around; align-items: center; font-size: 2.5em; font-weight: 900; }
        .team-label { font-size: 0.4em; font-weight: normal; flex: 1; }

        .card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h3 { border-left: 5px solid var(--dark); padding-left: 10px; margin: 0 0 15px; }

        table { width: 100%; border-collapse: collapse; font-size: 0.85em; }
        th { background: #f8f9fa; padding: 8px; border-bottom: 2px solid #eee; }
        td { padding: 10px 5px; border-bottom: 1px solid #eee; text-align: center; }
        .player-link { color: var(--teamA); font-weight: bold; cursor: pointer; text-decoration: underline; }
        .pts-bold { font-weight: bold; color: var(--teamB); background: #fff5f5; }

        .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 999; }
        .modal { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 400px; background: white; border-radius: 20px; z-index: 1000; padding: 20px; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 15px; text-align: center; }
        .stat-item { background: #f8f9fa; padding: 10px; border-radius: 10px; }
        .stat-val { font-size: 1.2em; font-weight: bold; color: var(--dark); }
    </style>
</head>
<body>

<?php
require_once __DIR__ . '/header.php';
?>

<main class="page-content">

<div class="container">
    <div class="final-header">
        <div style="margin-bottom:10px; opacity:0.7;">GAME REPORT #<?= htmlspecialchars((string)$gameId, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="score-row">
            <div class="team-label"><?= htmlspecialchars((string)$gameInfo['team_a_name'], ENT_QUOTES, 'UTF-8') ?></div>
            <div><?= (int)$gameInfo['score_a'] ?> - <?= (int)$gameInfo['score_b'] ?></div>
            <div class="team-label" style="color: var(--teamB);"><?= htmlspecialchars((string)$gameInfo['team_b_name'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>

    <div class="card">
        <h3>スコア推移</h3>
        <canvas id="trendChart" height="150"></canvas>
    </div>

    <?php foreach (['A', 'B'] as $t): ?>
        <div class="card">
            <h3 style="border-color: <?= ($t === 'A') ? 'var(--teamA)' : 'var(--teamB)' ?>;">
                <?= htmlspecialchars((string)(($t === 'A') ? $gameInfo['team_a_name'] : $gameInfo['team_b_name']), ENT_QUOTES, 'UTF-8') ?>
            </h3>
            <table>
                <thead>
                    <tr><th>選手</th><th>2P</th><th>3P</th><th>FT</th><th>F</th><th class="pts-bold">PTS</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($finalStats[$t])): ?>
                        <tr><td colspan="6" style="color:#999;">記録なし</td></tr>
                    <?php else: ?>
                        <?php foreach ($finalStats[$t] as $name => $s): ?>
                            <tr>
                                <td class="player-link" onclick='showDetail(<?= json_encode($name, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($s, JSON_UNESCAPED_UNICODE) ?>)'><?= htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int)$s['p2_m'] ?>/<?= (int)$s['p2_a'] ?></td>
                                <td><?= (int)$s['p3_m'] ?>/<?= (int)$s['p3_a'] ?></td>
                                <td><?= (int)$s['p1_m'] ?>/<?= (int)$s['p1_a'] ?></td>
                                <td><?= (int)$s['foul'] ?></td>
                                <td class="pts-bold"><?= (int)$s['pts'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>

    <a href="<?= htmlspecialchars(sportdata_add_tab_id('history.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>" style="display:block; text-align:center; padding:15px; color:#666; text-decoration:none;">← 履歴一覧に戻る</a>
</div>

<div class="overlay" id="overlay" onclick="closeModal()"></div>
<div class="modal" id="modal">
    <h2 id="m-name" style="margin:0 0 15px;">選手名</h2>
    <canvas id="playerChart" height="200"></canvas>
    <div class="stat-grid">
        <div class="stat-item"><div style="font-size:0.7em;">2P</div><div class="stat-val" id="m-p2"></div></div>
        <div class="stat-item"><div style="font-size:0.7em;">3P</div><div class="stat-val" id="m-p3"></div></div>
        <div class="stat-item"><div style="font-size:0.7em;">FT</div><div class="stat-val" id="m-p1"></div></div>
    </div>
    <div class="stat-grid">
        <div class="stat-item"><div style="font-size:0.7em;">Total PTS</div><div class="stat-val" id="m-pts" style="color:var(--teamB);"></div></div>
        <div class="stat-item"><div style="font-size:0.7em;">Fouls</div><div class="stat-val" id="m-foul"></div></div>
        <div class="stat-item"><div style="font-size:0.7em;">TO</div><div class="stat-val" id="m-to"></div></div>
    </div>
    <button onclick="closeModal()" style="width:100%; margin-top:20px; padding:10px; border:none; background:#eee; border-radius:10px;">閉じる</button>
</div>

<script>
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: ['1Q', '2Q', '3Q', '4Q'],
        datasets: [
            { label: <?= json_encode((string)$gameInfo['team_a_name'], JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode($trendA, JSON_UNESCAPED_UNICODE) ?>, borderColor: '#3498db', tension: 0.3, fill: false },
            { label: <?= json_encode((string)$gameInfo['team_b_name'], JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode($trendB, JSON_UNESCAPED_UNICODE) ?>, borderColor: '#e74c3c', tension: 0.3, fill: false }
        ]
    }
});

let pChart = null;
function showDetail(name, s) {
    document.getElementById('m-name').innerText = name;
    document.getElementById('m-p2').innerText = s.p2_m + '/' + s.p2_a;
    document.getElementById('m-p3').innerText = s.p3_m + '/' + s.p3_a;
    document.getElementById('m-p1').innerText = s.p1_m + '/' + s.p1_a;
    document.getElementById('m-pts').innerText = s.pts;
    document.getElementById('m-foul').innerText = s.foul;
    document.getElementById('m-to').innerText = s.to;

    document.getElementById('overlay').style.display = 'block';
    document.getElementById('modal').style.display = 'block';

    const ctx = document.getElementById('playerChart').getContext('2d');
    if (pChart) pChart.destroy();
    pChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['1P', '2P', '3P'],
            datasets: [
                { label: '成功', data: [s.p1_m, s.p2_m, s.p3_m], backgroundColor: '#2ecc71' },
                { label: '失敗', data: [s.p1_a - s.p1_m, s.p2_a - s.p2_m, s.p3_a - s.p3_m], backgroundColor: '#ecf0f1' }
            ]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
}

function closeModal() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('modal').style.display = 'none';
}
</script>

</main>

</body>
</html>
