<?php
require_once __DIR__ . '/session_bootstrap.php';
$NAV_BASE = '.';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/basketball_config/db.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_name = trim((string)($_POST['team_name'] ?? ''));

    if ($team_name !== '') {
        try {
            $stmt = $pdo->prepare('INSERT INTO teams (name) VALUES (?)');
            $stmt->execute([$team_name]);
            $new_id = (int)$pdo->lastInsertId();

            $link = sportdata_add_tab_id('registration.php?team_id=' . rawurlencode((string)$new_id) . '&from=new_team', $__tabId);
            $msg = '<div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #c3e6cb;">'
                . 'チーム「<strong>' . htmlspecialchars($team_name, ENT_QUOTES, 'UTF-8') . '</strong>」を登録しました！<br><br>'
                . '<a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block; background:#3498db; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px;">次に選手を登録する</a>'
                . '</div>';
        } catch (PDOException $e) {
            $msg = '<div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #f5c6cb;">'
                . '登録に失敗しました。チーム名が重複している可能性があります。'
                . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規チーム作成</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: sans-serif; background: #f2f2f2; margin: 0; padding: 0; }
        .wrap { padding: 20px; }
        .card { background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 420px; margin: 0 auto; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 16px; }
        .btn { width: 100%; padding: 15px; border: none; border-radius: 8px; background: #e67e22; color: white; font-weight: bold; cursor: pointer; }
    </style>
    <link rel="stylesheet" href="../css/basketball.css">
</head>
<body class="basketball-page">
<div class="wrap">
    <div class="card">
        <h2 style="margin-top:0;">新規チーム登録</h2>
        <?= $msg ?>
        <form method="post">
            <label>チーム名を入力</label>
            <input type="text" name="team_name" placeholder="例：〇〇高校" required autofocus>
            <button type="submit" class="btn">チームを作成</button>
        </form>
        <a href="<?= htmlspecialchars(sportdata_add_tab_id('basketball_index.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>" style="display:block; text-align:center; margin-top:20px; color:#666; text-decoration:none;">← 試合設定へ戻る</a>
    </div>
</div>
</body>
</html>
