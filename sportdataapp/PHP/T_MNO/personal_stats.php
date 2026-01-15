<?php
require_once __DIR__ . '/../session_bootstrap.php';
require_once __DIR__ . '/db.php';

// 共通ナビ用：アクセスURLに応じてPHPルートへの相対パスを切り替える
// - /PHP/T_MNO/* で開いている場合: ..
// - /T_MNO/* (互換ラッパー経由) の場合: ../PHP
$uri = $_SERVER['REQUEST_URI'] ?? '';
$NAV_BASE = (stripos($uri, '/PHP/T_MNO/') !== false) ? '..' : '../PHP';
$db = getDbConnection();
$allStats = getAllPlayerActionStats($db);

// 得点系と失点系の分類
$gainActions = ["サービスエース", "スマッシュ", "ボレー", "ストローク", "リターンエース", "ネットイン"];
$loseActions = ["ダブルフォルト", "アウト", "ネット", "ネットタッチ", "オーバーネット", "ボディタッチ", "ダイレクト", "チップ"];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>個人通算スタッツ</title>
    <link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
    <style>
        body { font-family: sans-serif; margin:0; background:#f4f7f9; color: #333; }
        .app-header { height: 60px; background: #2c3e50; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .container { padding: 15px; max-width: 800px; margin: auto; }
        .player-card { background: white; border-radius: 16px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .player-name { font-size: 20px; font-weight: bold; border-bottom: 2px solid #3498db; display: inline-block; margin-bottom: 15px; }
        
        /* 割合バーのスタイル */
        .stat-bar-container { height: 24px; display: flex; border-radius: 12px; overflow: hidden; margin: 10px 0; background: #eee; }
        .bar { height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: bold; transition: width 0.5s; }
        
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px; }
        .stat-list { font-size: 12px; }
        .stat-item { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #f0f0f0; }
        .label-gain { color: #2980b9; font-weight: bold; }
        .label-lose { color: #c0392b; font-weight: bold; }
        .total-count { font-size: 12px; color: #7f8c8d; margin-bottom: 10px; }
        .btn-back { display: block; text-align: center; padding: 15px; background: #2c3e50; color: white; text-decoration: none; border-radius: 10px; margin-top: 20px; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../header.php'; ?>
<header class="app-header">個人別・通算データ分析</header>
<div class="container">
    <?php foreach ($allStats as $name => $s): 
        $totalGain = 0; foreach($gainActions as $ga) $totalGain += $s[$ga];
        $totalLose = 0; foreach($loseActions as $la) $totalLose += $s[$la];
        $total = $s['total'] ?: 1;
        $gainRate = round(($totalGain / $total) * 100);
        $loseRate = round(($totalLose / $total) * 100);
    ?>
    <div class="player-card">
        <div class="player-name"><?= htmlspecialchars($name) ?></div>
        <div class="total-count">通算関与アクション数: <?= $s['total'] ?> 回</div>

        <div style="font-size: 12px; font-weight: bold; margin-bottom: 5px;">得点 vs 失点 比率</div>
        <div class="stat-bar-container">
            <div class="bar" style="width: <?= $gainRate ?>%; background: #3498db;"><?= $gainRate ?>% 得点</div>
            <div class="bar" style="width: <?= $loseRate ?>%; background: #e74c3c;"><?= $loseRate ?>% 失点</div>
        </div>

        <div class="grid">
            <div class="stat-list">
                <div class="label-gain">▼ 得点内訳</div>
                <?php foreach ($gainActions as $ga): if($s[$ga] > 0): ?>
                    <div class="stat-item"><span><?= $ga ?></span><span><?= $s[$ga] ?></span></div>
                <?php endif; endforeach; ?>
            </div>
            <div class="stat-list">
                <div class="label-lose">▼ 失点内訳</div>
                <?php foreach ($loseActions as $la): if($s[$la] > 0): ?>
                    <div class="stat-item"><span><?= $la ?></span><span><?= $s[$la] ?></span></div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <a href="history.php" class="btn-back">履歴に戻る</a>
</div>
</body>
</html>