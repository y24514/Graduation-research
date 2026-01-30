<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/basketball_logic/db_config.php';

$NAV_BASE = '.';
require_once __DIR__ . '/header.php';

try {
    // created_at があるが、ID降順の方が確実に新しい順
    $stmt = $pdo->query('SELECT * FROM games ORDER BY id DESC');
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    http_response_code(500);
    $games = [];
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>試合履歴 - BasketLog</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; margin: 0; }
        .container { max-width: 600px; margin: auto; }
        h1 { text-align: center; color: #2c3e50; }
        .game-card {
            background: #fff; padding: 20px; border-radius: 12px;
            margin-bottom: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-decoration: none; color: inherit; display: block;
            border-left: 5px solid #3498db;
        }
        .score-row { display: flex; justify-content: space-between; align-items: center; }
        .team-name { font-weight: bold; font-size: 1.1em; flex: 1; }
        .score { font-size: 1.6em; font-weight: bold; padding: 0 15px; color: #2c3e50; min-width: 80px; text-align: center; }
        .btn-back { display: block; text-align: center; margin-top: 30px; color: #3498db; text-decoration: none; font-weight: bold; }
        .game-id { font-size: 0.8em; color: #aaa; margin-bottom: 5px; }
        .flash { max-width:600px; margin: 0 auto 15px; background:#ecfdf5; border:1px solid #a7f3d0; padding:10px 12px; border-radius:10px; color:#065f46; }
        .err { max-width:600px; margin: 0 auto 15px; background:#fff5f5; border:1px solid #fecaca; padding:10px 12px; border-radius:10px; color:#991b1b; }
    </style>
</head>
<body>
<div class="container">
    <h1>📅 試合履歴</h1>

    <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
        <div class="flash">保存しました。</div>
    <?php endif; ?>

    <?php if (!empty($dbError)): ?>
        <div class="err">DB読み込みエラー: <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (empty($games)): ?>
        <p style="text-align:center; color:#999; background:#fff; padding:20px; border-radius:10px;">
            まだ試合データがありません。
        </p>
    <?php else: ?>
        <?php foreach ($games as $g): ?>
            <a href="<?= htmlspecialchars(sportdata_add_tab_id('view_detail.php?id=' . rawurlencode((string)$g['id']), $__tabId), ENT_QUOTES, 'UTF-8') ?>" class="game-card">
                <div class="game-id">Game ID: #<?= htmlspecialchars((string)$g['id'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="score-row">
                    <span class="team-name" style="text-align:right;"><?= htmlspecialchars((string)$g['team_a_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="score"><?= (int)$g['score_a'] ?> - <?= (int)$g['score_b'] ?></span>
                    <span class="team-name" style="text-align:left;"><?= htmlspecialchars((string)$g['team_b_name'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div style="text-align:right; font-size:0.8em; color:#3498db; margin-top:5px;">詳細スタッツを見る ➔</div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="<?= htmlspecialchars(sportdata_add_tab_id('basketball_index.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>" class="btn-back">← 試合設定に戻る</a>
</div>
</body>
</html>
