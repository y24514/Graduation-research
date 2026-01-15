<?php
require_once __DIR__ . '/../session_bootstrap.php';
require_once __DIR__ . '/db.php';

// 共通ナビ用：アクセスURLに応じてPHPルートへの相対パスを切り替える
// - /PHP/T_MNO/* で開いている場合: ..
// - /T_MNO/* (互換ラッパー経由) の場合: ../PHP
$uri = $_SERVER['REQUEST_URI'] ?? '';
$NAV_BASE = (stripos($uri, '/PHP/T_MNO/') !== false) ? '..' : '../PHP';

$db = getDbConnection();
$games = getAllGames($db);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>試合履歴一覧</title>
    <link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
    <style>
        body { font-family: sans-serif; margin:0; background:#f4f7f9; color: #333; }
        .app-header { height: 60px; background: #2c3e50; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; position: sticky; top:0; z-index: 100; }
        .container { padding: 15px; max-width: 600px; margin: auto; }
        .game-card { 
            background: white; 
            border-radius: 12px; 
            padding: 15px; 
            margin-bottom: 12px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
            display: flex; 
            align-items: center; 
            text-decoration: none; 
            color: inherit;
            border-left: 6px solid #3498db;
            transition: transform 0.2s;
        }
        .game-card:active { transform: scale(0.98); }
        .game-info { flex-grow: 1; }
        .game-date { font-size: 11px; color: #95a5a6; margin-bottom: 5px; }
        .game-teams { font-weight: bold; font-size: 16px; color: #2c3e50; }
        .game-score { font-size: 20px; font-weight: bold; color: #34495e; margin-left: 15px; }
        .no-data { text-align: center; padding: 50px; color: #95a5a6; }
        .btn-new { display: block; background: #2ecc71; color: white; text-align: center; padding: 15px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-bottom: 20px; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../header.php'; ?>
<header class="app-header">試合履歴</header>
<div class="container">
    <a href="index.php" class="btn-new">＋ 新しい試合を記録</a>

    <?php if (empty($games)): ?>
        <div class="no-data">試合データがまだありません。</div>
    <?php else: ?>
        <?php foreach ($games as $g): ?>
            <a href="result.php?id=<?= $g['id'] ?>" class="game-card">
                <div class="game-info">
                    <div class="game-date">試合ID: #<?= $g['id'] ?></div>
                    <div class="game-teams">
                        <?= htmlspecialchars($g['team_a']) ?> <span style="color:#bdc3c7; font-size:12px;">vs</span> <?= htmlspecialchars($g['team_b']) ?>
                    </div>
                    <div style="font-size:12px; color:#7f8c8d; margin-top:4px;">
                        <?= htmlspecialchars($g['player_a1']) ?><?= $g['player_a2'] ? '・'.htmlspecialchars($g['player_a2']) : '' ?> 
                    </div>
                </div>
                <div class="game-score">
                    <?= $g['games_a'] ?> - <?= $g['games_b'] ?>
                </div>
                <div style="margin-left:15px; color:#bdc3c7;">❯</div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>