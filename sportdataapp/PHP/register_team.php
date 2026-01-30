<?php
require_once __DIR__ . '/session_bootstrap.php';
$NAV_BASE = '.';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/basketball_config/db.php';

$msg = '';

$tabIdHidden = (string)($GLOBALS['SPORTDATA_TAB_ID'] ?? ($_GET['tab_id'] ?? ($_POST['tab_id'] ?? '')));
if ($tabIdHidden !== '' && !preg_match('/^[A-Za-z0-9_-]{8,64}$/', $tabIdHidden)) {
    $tabIdHidden = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? 'create');

    // 破壊的操作はCSRF検証
    $postedToken = (string)($_POST['csrf_token'] ?? '');
    if ($postedToken === '' || empty($_SESSION['csrf_token']) || !hash_equals((string)$_SESSION['csrf_token'], $postedToken)) {
        $msg = '<div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #f5c6cb;">'
            . '不正な操作として拒否されました（CSRF）。ページを更新してから再試行してください。'
            . '</div>';
    } elseif ($action === 'delete') {
        $teamId = (int)($_POST['team_id'] ?? 0);
        if ($teamId <= 0) {
            $msg = '<div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #f5c6cb;">'
                . '削除対象のチームが指定されていません。'
                . '</div>';
        } else {
            try {
                // players は FK ON DELETE CASCADE の想定
                $stmt = $pdo->prepare('DELETE FROM teams WHERE id = ?');
                $stmt->execute([$teamId]);
                $msg = '<div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #c3e6cb;">'
                    . 'チームを削除しました。'
                    . '</div>';
            } catch (PDOException $e) {
                $msg = '<div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:15px; border:1px solid #f5c6cb;">'
                    . '削除に失敗しました。'
                    . '</div>';
            }
        }
    } else {
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
}

$teams = [];
try {
    // 一覧表示（削除ボタン用）
    $teams = $pdo->query('SELECT id, name FROM teams ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teams = [];
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
        .card { background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 560px; margin: 0 auto; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 16px; }
        .btn { width: 100%; padding: 15px; border: none; border-radius: 8px; background: #e67e22; color: white; font-weight: bold; cursor: pointer; }
        .team-list { margin-top: 18px; border-top: 1px solid #eee; padding-top: 14px; }
        .team-row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 0; border-bottom: 1px solid #f1f1f1; }
        .team-name { font-weight: 700; color:#333; }
        .team-id { color:#999; font-size:0.85em; margin-left:8px; }
        .btn-del { padding: 10px 12px; border: none; border-radius: 10px; background:#e74c3c; color:#fff; font-weight: 800; cursor:pointer; white-space:nowrap; }
        .btn-del:hover { background:#c0392b; }
    </style>
    <link rel="stylesheet" href="../css/basketball.css">
</head>
<body class="basketball-page">
<div class="wrap">
    <div class="card">
        <h2 style="margin-top:0;">新規チーム登録</h2>
        <?= $msg ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($__csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($tabIdHidden !== ''): ?>
                <input type="hidden" name="tab_id" value="<?= htmlspecialchars($tabIdHidden, ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <label>チーム名を入力</label>
            <input type="text" name="team_name" placeholder="例：〇〇高校" required autofocus>
            <button type="submit" class="btn">チームを作成</button>
        </form>

        <div class="team-list">
            <div style="font-weight:800; margin-bottom:8px; color:#333;">既存チーム（削除）</div>
            <?php if (empty($teams)): ?>
                <div style="color:#666; font-size:0.95em;">まだチームがありません。</div>
            <?php else: ?>
                <?php foreach ($teams as $t): ?>
                    <div class="team-row">
                        <div>
                            <span class="team-name"><?= htmlspecialchars((string)$t['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="team-id">#<?= (int)$t['id'] ?></span>
                        </div>
                        <form method="post" style="margin:0;" onsubmit="return confirm('このチームを削除します。選手も消えます。よろしいですか？');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="team_id" value="<?= (int)$t['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($__csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <?php if ($tabIdHidden !== ''): ?>
                                <input type="hidden" name="tab_id" value="<?= htmlspecialchars($tabIdHidden, ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                            <button type="submit" class="btn-del">削除</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <a href="<?= htmlspecialchars(sportdata_add_tab_id('basketball_index.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>" style="display:block; text-align:center; margin-top:20px; color:#666; text-decoration:none;">← 試合設定へ戻る</a>
    </div>
</div>
</body>
</html>
