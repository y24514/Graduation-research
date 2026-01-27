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
    $action = (string)($_POST['action'] ?? '');
    $teamId = (int)($_POST['team_id'] ?? 0);

    if ($action === 'rename' && $teamId > 0) {
        $newName = trim((string)($_POST['team_name'] ?? ''));
        if ($newName === '') {
            $msg = '<div class="flash flash--error">チーム名を入力してください。</div>';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE teams SET name = ? WHERE id = ?');
                $stmt->execute([$newName, $teamId]);
                $msg = '<div class="flash flash--ok">チーム名を更新しました。</div>';
            } catch (PDOException $e) {
                // UNIQUE違反など
                $msg = '<div class="flash flash--error">更新に失敗しました（同名チームが存在する可能性があります）。</div>';
            }
        }
    }

    if ($action === 'delete' && $teamId > 0) {
        try {
            // players は FK ON DELETE CASCADE
            $stmt = $pdo->prepare('DELETE FROM teams WHERE id = ?');
            $stmt->execute([$teamId]);
            $msg = '<div class="flash flash--ok">チームを削除しました。</div>';
        } catch (PDOException $e) {
            $msg = '<div class="flash flash--error">削除に失敗しました。</div>';
        }
    }
}

$teams = [];
try {
    $teams = $pdo->query('SELECT id, name, created_at FROM teams ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teams = [];
    $msg = '<div class="flash flash--error">teams テーブルの読み込みに失敗しました。</div>';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>チーム情報の編集</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <meta name="viewport" content="width=1024">
    <style>
        body { font-family: sans-serif; background: #f2f2f2; margin: 0; padding: 0; }
        .wrap { padding: 20px; }
        .card { background: #fff; padding: 22px; border-radius: 14px; max-width: 720px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { margin: 0 0 12px; }
        .note { color: #666; font-size: 0.9em; margin-bottom: 14px; }
        .flash { padding: 10px 12px; border-radius: 10px; margin: 10px 0 14px; font-weight: 600; }
        .flash--ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash--error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .team-list { display: flex; flex-direction: column; gap: 10px; }
        .team-item { border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; display: flex; gap: 10px; align-items: center; }
        .team-id { width: 60px; color: #666; font-size: 0.9em; }
        .team-name { flex: 1; display: flex; gap: 10px; align-items: center; }
        .team-name input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; }
        .actions { display: flex; gap: 8px; }
        .btn { padding: 10px 12px; border: none; border-radius: 10px; cursor: pointer; font-weight: 700; }
        .btn-save { background: #3498db; color: #fff; }
        .btn-save:hover { background: #2980b9; }
        .btn-del { background: #e74c3c; color: #fff; }
        .btn-del:hover { background: #c0392b; }

        @media (max-width: 640px) {
            .team-item { flex-direction: column; align-items: stretch; }
            .team-id { width: auto; }
            .actions { justify-content: flex-end; }
        }
    </style>
    <link rel="stylesheet" href="../css/basketball.css">
</head>
<body class="basketball-page">
<div class="wrap">
    <div class="card">
        <h2>チーム情報の編集</h2>
        <div class="note">チーム名の変更や削除ができます。削除すると、そのチームの選手も削除されます。</div>

        <?= $msg ?>

        <?php if (empty($teams)): ?>
            <p style="color:#666;">チームがありません。先に新規作成してください。</p>
            <p style="margin:12px 0 0;"><a href="<?= htmlspecialchars(sportdata_add_tab_id('register_team.php', $tabIdHidden), ENT_QUOTES, 'UTF-8') ?>" style="text-decoration:none; background:#e67e22; color:#fff; padding:8px 12px; border-radius:10px; font-weight:bold; display:inline-block;">＋ 新チーム登録</a></p>
        <?php else: ?>
            <div class="team-list">
                <?php foreach ($teams as $t): ?>
                    <div class="team-item">
                        <div class="team-id">ID: <?= (int)$t['id'] ?></div>
                        <div class="team-name">
                            <form method="post" style="display:flex; gap:10px; width:100%; align-items:center;" onsubmit="return confirm('チーム名を更新しますか？');">
                                <?php if ($tabIdHidden !== ''): ?>
                                    <input type="hidden" name="tab_id" value="<?= htmlspecialchars($tabIdHidden, ENT_QUOTES, 'UTF-8') ?>">
                                <?php endif; ?>
                                <input type="hidden" name="action" value="rename">
                                <input type="hidden" name="team_id" value="<?= (int)$t['id'] ?>">
                                <input type="text" name="team_name" value="<?= htmlspecialchars((string)$t['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                <div class="actions">
                                    <button type="submit" class="btn btn-save">保存</button>
                                </div>
                            </form>
                            <form method="post" style="margin:0;" onsubmit="return confirm('このチームを削除します。選手も消えます。よろしいですか？');">
                                <?php if ($tabIdHidden !== ''): ?>
                                    <input type="hidden" name="tab_id" value="<?= htmlspecialchars($tabIdHidden, ENT_QUOTES, 'UTF-8') ?>">
                                <?php endif; ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="team_id" value="<?= (int)$t['id'] ?>">
                                <button type="submit" class="btn btn-del">削除</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="<?= htmlspecialchars(sportdata_add_tab_id('basketball_index.php', $tabIdHidden), ENT_QUOTES, 'UTF-8') ?>" style="text-decoration:none; background:#34495e; color:#fff; padding:10px 12px; border-radius:10px; font-weight:bold;">試合設定へ</a>
            <a href="<?= htmlspecialchars(sportdata_add_tab_id('registration.php', $tabIdHidden), ENT_QUOTES, 'UTF-8') ?>" style="text-decoration:none; background:#3498db; color:#fff; padding:10px 12px; border-radius:10px; font-weight:bold;">選手登録へ</a>
        </div>
    </div>
</div>
</body>
</html>
