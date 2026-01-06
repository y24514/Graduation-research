<?php
session_start();
// 分析ロジックの読み込み
require_once __DIR__ . '/basketball_logic/analyze.php';

// データの存在チェック
if (!isset($_SESSION['game'])) {
    $NAV_BASE = '.';
    require_once __DIR__ . '/header.php';
    http_response_code(200);
    echo '<div style="padding:16px; max-width:800px; margin:0 auto;">';
    echo '<h2 style="margin:8px 0;">結果を表示する試合がありません</h2>';
    echo '<p style="margin:8px 0;">先に「試合設定」から試合を開始してください。</p>';
    echo '<p style="margin:12px 0;"><a href="basketball_index.php" style="display:inline-block; padding:10px 14px; border:1px solid #ccc; border-radius:10px; text-decoration:none;">試合設定へ</a></p>';
    echo '</div>';
    exit;
}
$game = $_SESSION['game'];

// 1. クォーターごとの得点推移を計算（折れ線グラフ用）
$qStats = [];
$labels = ["1Q", "2Q", "3Q", "4Q"];
$trendA = [];
$trendB = [];
for ($i = 1; $i <= 4; $i++) {
    $res = analyzeQuarter($game, $i);
    $trendA[] = $res['A']['points'];
    $trendB[] = $res['B']['points'];
}

// 2. 全試合を通じた各選手の詳細スタッツを計算
function getFinalStats($game) {
    $final = ['A' => [], 'B' => []];
    foreach (['A', 'B'] as $t) {
        foreach ($game['actions'] as $a) {
            // 交代データは集計から除外
            if ($a['team'] !== $t || $a['type'] === 'sub' || !isset($a['player'])) continue;
            
            $pid = $a['player'];
            if (!isset($final[$t][$pid])) {
                $final[$t][$pid] = [
                    'p1_m'=>0, 'p1_a'=>0, 'p2_m'=>0, 'p2_a'=>0, 'p3_m'=>0, 'p3_a'=>0, 
                    'pts'=>0, 'foul'=>0, 'to'=>0
                ];
            }

            if ($a['type'] === 'shot') {
                $pt = (int)$a['point']; 
                $isSuccess = ($a['result'] === 'success');
                // 1P, 2P, 3P ごとに成功(m)と試投(a)をカウント
                if ($pt === 1) { $final[$t][$pid]['p1_a']++; if($isSuccess) $final[$t][$pid]['p1_m']++; }
                if ($pt === 2) { $final[$t][$pid]['p2_a']++; if($isSuccess) $final[$t][$pid]['p2_m']++; }
                if ($pt === 3) { $final[$t][$pid]['p3_a']++; if($isSuccess) $final[$t][$pid]['p3_m']++; }
                if ($isSuccess) $final[$t][$pid]['pts'] += $pt;
            } elseif ($a['type'] === 'foul') {
                $final[$t][$pid]['foul']++;
            } elseif ($a['type'] === 'to') {
                $final[$t][$pid]['to']++;
            }
        }
    }
    return $final;
}

$finalStats = getFinalStats($game);
$mvpCandidate = null;
$maxRating = -999;

foreach (['A', 'B'] as $t) {
    foreach ($finalStats[$t] as $pid => $s) {
        // 簡易貢献度計算：得点 - 失敗数 - TO - ファウル
        $misses = ($s['p1_a'] - $s['p1_m']) + ($s['p2_a'] - $s['p2_m']) + ($s['p3_a'] - $s['p3_m']);
        $rating = $s['pts'] - $misses - $s['to'] - $s['foul'];
        
        if ($rating > $maxRating) {
            $maxRating = $rating;
            $mvpCandidate = [
                'name' => $game['teams'][$t]['names'][$pid],
                'team' => $game['teamNames'][$t],
                'pts'  => $s['pts'],
                'rating' => $rating
            ];
        }
    }
}

$jsonStats = json_encode($finalStats);
$jsonTeams = json_encode($game['teams']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>試合結果レポート</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --teamA: #3498db; --teamB: #e74c3c; --dark: #2c3e50; }
        body { font-family: sans-serif; background: #f4f7f6; margin: 0; padding: 0; color: #333; }
        .page-pad { padding: 15px; }
        .container { max-width: 800px; margin: auto; }
        
        /* ヘッダー */
        .header { background: var(--dark); color: white; padding: 25px; border-radius: 20px; text-align: center; margin-bottom: 20px; }
        .score-total { font-size: 3rem; font-weight: bold; margin: 10px 0; }
        
        .card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        
        /* チーム・選手選択 */
        .tabs { display: flex; gap: 10px; margin-bottom: 15px; }
        .tab-btn { flex: 1; padding: 12px; border: none; border-radius: 10px; font-weight: bold; background: #ddd; cursor: pointer; }
        .tab-btn.active.A { background: var(--teamA); color: white; }
        .tab-btn.active.B { background: var(--teamB); color: white; }

        .player-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-bottom: 20px; }
        .p-btn { padding: 10px; border: 1px solid #ccc; border-radius: 8px; background: white; cursor: pointer; font-size: 0.9em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .p-btn.active { background: var(--dark); color: white; border-color: var(--dark); }

        /* スタッツ表示 */
        .stat-row { display: flex; justify-content: space-around; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; }
        .stat-box { text-align: center; }
        .stat-val { font-size: 1.5em; font-weight: bold; display: block; }
        .stat-label { font-size: 0.8em; color: #888; }
        .mvp-banner {
    background: linear-gradient(135deg, #f1c40f, #f39c12);
    color: white;
    padding: 20px;
    border-radius: 20px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 0 10px 20px rgba(243, 156, 18, 0.3);
    border: 2px solid #fff;
}
.mvp-label { font-size: 0.8em; font-weight: bold; letter-spacing: 2px; }
.mvp-name { font-size: 2.2em; font-weight: 900; margin: 5px 0; }
    </style>
</head>
<body>

<?php
$NAV_BASE = '..';
$NAV_BASE = '.';
require_once __DIR__ . '/header.php';
?>

<div class="page-pad">

<div class="container">
    <div class="header">
        <div style="font-size: 0.8em; opacity: 0.7;">FINAL SCORE</div>
        <div class="score-total"><?= $game['score']['A'] ?> - <?= $game['score']['B'] ?></div>
        <div style="font-weight: bold;"><?= htmlspecialchars($game['teamNames']['A']) ?> vs <?= htmlspecialchars($game['teamNames']['B']) ?></div>
    </div>

    <div class="container">
    <?php if ($mvpCandidate): ?>
    <div class="mvp-banner">
        <div class="mvp-label">🏆 GAME MVP</div>
        <div class="mvp-name"><?= htmlspecialchars($mvpCandidate['name']) ?></div>
        <div style="font-weight: bold; opacity: 0.9;">
            <?= htmlspecialchars($mvpCandidate['team']) ?> / <?= $mvpCandidate['pts'] ?> PTS
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <h3 style="margin-top:0;">得点推移</h3>
        <canvas id="trendChart" height="120"></canvas>
    </div>

    <div class="tabs">
        <button class="tab-btn active A" onclick="switchTeam('A')"><?= htmlspecialchars($game['teamNames']['A']) ?></button>
        <button class="tab-btn B" onclick="switchTeam('B')"><?= htmlspecialchars($game['teamNames']['B']) ?></button>
    </div>

    

    <div id="playerList" class="player-grid"></div>

    <div class="card" id="detailCard">
    <h3 id="targetName" style="margin-top:0; text-align:center;">選手を選択</h3>
    
    <div class="stat-row" style="margin-bottom: -10px; border-top: none;">
        <div class="stat-box">
            <span class="stat-label">1P (FT)</span>
            <span class="stat-val" id="disp-p1-shot" style="font-size: 1.1em;">0/0</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">2P Shot</span>
            <span class="stat-val" id="disp-p2-shot" style="font-size: 1.1em;">0/0</span>
        </div>
        <div class="stat-box">
            <span class="stat-label">3P Shot</span>
            <span class="stat-val" id="disp-p3-shot" style="font-size: 1.1em;">0/0</span>
        </div>
    </div>

    <div style="max-width: 400px; margin: auto;">
        <canvas id="playerChart"></canvas>
    </div>

    <div class="stat-row">
        <div class="stat-box"><span class="stat-val" id="disp-pts">-</span><span class="stat-label">得点</span></div>
        <div class="stat-box"><span class="stat-val" id="disp-foul">-</span><span class="stat-label">ファウル</span></div>
        <div class="stat-box"><span class="stat-val" id="disp-to">-</span><span class="stat-label">TO</span></div>
    </div>
</div>

    <div style="text-align:center; margin-top: 30px; padding-bottom: 50px;">
    <form action="save_to_db.php" method="POST" onsubmit="return confirm('この内容でDBに保存し、終了しますか？');">
        <button type="submit" style="width:100%; padding:20px; border-radius:15px; border:none; background:#27ae60; color:white; font-weight:bold; font-size:1.2em; cursor:pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
             データを保存して終了
        </button>
    </form>
</div>
</div>

<script>
const stats = <?= $jsonStats ?>;
const teams = <?= $jsonTeams ?>;
let currentTeam = 'A';
let playerChartObj = null;

// 1. 得点推移グラフの描画
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            { label: '<?= $game['teamNames']['A'] ?>', data: <?= json_encode($trendA) ?>, borderColor: '#3498db', tension: 0.3, fill: false },
            { label: '<?= $game['teamNames']['B'] ?>', data: <?= json_encode($trendB) ?>, borderColor: '#e74c3c', tension: 0.3, fill: false }
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// 2. チーム切り替えロジック
function switchTeam(t) {
    currentTeam = t;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`.tab-btn.${t}`).classList.add('active');
    
    const list = document.getElementById('playerList');
    list.innerHTML = '';
    
    // 「実際に記録がある選手（statsに含まれる人）」だけ抽出
    const playedIds = Object.keys(stats[t]);
    
    if (playedIds.length === 0) {
        list.innerHTML = '<p style="grid-column:1/-1; text-align:center; color:#999;">記録なし</p>';
        return;
    }

    playedIds.forEach(pid => {
        const btn = document.createElement('button');
        btn.className = 'p-btn';
        btn.innerText = teams[t].names[pid];
        btn.onclick = () => showPlayerDetail(pid, btn);
        list.appendChild(btn);
    });
    
    // 最初の選手を表示
    list.querySelector('.p-btn').click();
}

// 3. 選手別グラフの描画
// final.php の script タグ内、showPlayerDetail 関数を書き換え
function showPlayerDetail(pid, btn) {
    document.querySelectorAll('.p-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const d = stats[currentTeam][pid];
    document.getElementById('targetName').innerText = teams[currentTeam].names[pid];
    
    // ★ 追加：シュート成績を 0/0 の形式で表示
    document.getElementById('disp-p1-shot').innerText = `${d.p1_m} / ${d.p1_a}`;
    document.getElementById('disp-p2-shot').innerText = `${d.p2_m} / ${d.p2_a}`;
    document.getElementById('disp-p3-shot').innerText = `${d.p3_m} / ${d.p3_a}`;

    document.getElementById('disp-pts').innerText = d.pts;
    document.getElementById('disp-foul').innerText = d.foul;
    document.getElementById('disp-to').innerText = d.to;

    // グラフ描画（Y軸最大40の設定を維持）
    const ctx = document.getElementById('playerChart').getContext('2d');
    if (playerChartObj) playerChartObj.destroy();

    playerChartObj = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['1P', '2P', '3P'],
            datasets: [
                { label: '成功', data: [d.p1_m, d.p2_m, d.p3_m], backgroundColor: '#2ecc71' },
                { label: '失敗', data: [d.p1_a - d.p1_m, d.p2_a - d.p2_m, d.p3_a - d.p3_m], backgroundColor: '#e74c3c' }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true },
                y: { 
                    stacked: true, 
                    beginAtZero: true, 
                    max: 40, 
                    ticks: { stepSize: 10 } 
                }
            },
            plugins: { legend: { display: false } }
        }
    });
}
// 初期起動
window.onload = () => switchTeam('A');
</script>
</div>

</body>
</html>