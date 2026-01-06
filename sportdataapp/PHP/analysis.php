<?php
session_start();
require_once __DIR__ . '/basketball_logic/analyze.php';
$game = $_SESSION['game'] ?? null;
if ($game === null) {
    $NAV_BASE = '.';
    require_once __DIR__ . '/header.php';
    http_response_code(200);
    echo '<div style="padding:16px; max-width:800px; margin:0 auto;">';
    echo '<h2 style="margin:8px 0;">分析する試合がありません</h2>';
    echo '<p style="margin:8px 0;">先に「試合設定」から試合を開始してください。</p>';
    echo '<p style="margin:12px 0;"><a href="basketball_index.php" style="display:inline-block; padding:10px 14px; border:1px solid #ccc; border-radius:10px; text-decoration:none;">試合設定へ</a></p>';
    echo '</div>';
    exit;
}
$q = (int)($_GET['q'] ?? $game['quarter']);
$analysis = analyzeQuarter($game, $q);

// このQだけの合計得点
$qA = $analysis['A']['points'];
$qB = $analysis['B']['points'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>分析結果 - Q<?= $q ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: sans-serif; background: #eee; padding: 0; margin: 0; }
        .page-pad { padding: 15px; }
        .score-card { background: #2c3e50; color: #fff; padding: 20px; border-radius: 15px; text-align: center; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .total-row { font-size: 1.8em; font-weight: bold; }
        .q-row { font-size: 1.1em; color: #f1c40f; margin-top: 10px; border-top: 1px solid #555; padding-top: 10px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media (max-width: 800px) { .grid { grid-template-columns: 1fr; } }
        .card { background: #fff; padding: 15px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; font-size: 0.8em; }
        th, td { border-bottom: 1px solid #eee; padding: 8px 4px; text-align: center; }
        th { background: #34495e; color: white; }
        .pts { font-weight: bold; color: #e67e22; }
    </style>
</head>
<body>

<?php
$NAV_BASE = '.';
require_once __DIR__ . '/header.php';
?>

<div class="page-pad">

<div class="score-card">
    <div style="font-size:0.8em; opacity:0.7; margin-bottom:5px;">TOTAL SCORE</div>
    <div class="total-row">
        <?= htmlspecialchars($game['teamNames']['A']) ?> <?= $game['score']['A'] ?> - <?= $game['score']['B'] ?> <?= htmlspecialchars($game['teamNames']['B']) ?>
    </div>
    <div class="q-row">
        QUARTER <?= $q ?>：<?= $qA ?> - <?= $qB ?>
    </div>
</div>

<div class="grid">
    <?php foreach (['A', 'B'] as $t): ?>
    <div class="card">
        <h3><?= htmlspecialchars($game['teamNames'][$t]) ?></h3>
        <table>
            <thead><tr><th>PLAYER</th><th>2P</th><th>3P</th><th>FT</th><th>F</th><th>TO</th><th class="pts">PTS</th></tr></thead>
            <tbody>
            <?php foreach ($analysis[$t]['players'] as $id => $st): ?>
            <tr>
                <td style="text-align:left"><?= htmlspecialchars($game['teams'][$t]['names'][$id] ?? $id) ?></td>
                <td><?= $st['fg2_m'] ?>/<?= $st['fg2_a'] ?></td>
                <td><?= $st['fg3_m'] ?>/<?= $st['fg3_a'] ?></td>
                <td><?= $st['ft_m'] ?>/<?= $st['ft_a'] ?></td>
                <td><?= $st['foul'] ?></td>
                <td><?= $st['to'] ?></td>
                <td class="pts"><?= $st['pts'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>
</div>

<div style="text-align:center; margin:30px 0;">
    <?php if ($q < 4): ?>
        <a href="game.php" style="background:#333; color:white; padding:15px 40px; border-radius:10px; text-decoration:none; font-weight:bold;">試合記録に戻る</a>
    <?php else: ?>
        <a href="final.php" style="background:#e74c3c; color:white; padding:15px 40px; border-radius:10px; text-decoration:none; font-weight:bold;">試合終了（最終結果へ）</a>
    <?php endif; ?>
</div>

</div>

</body>
</html>