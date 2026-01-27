<?php
require_once __DIR__ . '/session_bootstrap.php';
$NAV_BASE = '.';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/basketball_config/db.php';

$teams = [];
try {
    $teams = $pdo->query('SELECT id, name FROM teams ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $teams = [];
}

$pre_selected_team = (string)($_GET['team_id'] ?? ($_POST['team_id'] ?? ''));
$msg = '';

// チームが選択されている場合、現在の選手リストを取得
$current_players = [];
if ($pre_selected_team !== '') {
    try {
        $stmt = $pdo->prepare('SELECT name, number FROM players WHERE team_id = ? ORDER BY number ASC');
        $stmt->execute([$pre_selected_team]);
        $current_players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $current_players = [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $tid = (string)($_POST['team_id'] ?? '');
    $names = $_POST['names'] ?? [];
    $numbers = $_POST['numbers'] ?? [];

    if ($tid !== '') {
        $valid_players = [];
        $temp_numbers = [];
        $error = '';

        foreach ((array)$names as $i => $name) {
            $n_val = trim((string)$name);
            $num_raw = $numbers[$i] ?? '';
            $num_val = (int)$num_raw;

            if ($n_val !== '' && $num_raw !== '') {
                if ($num_val < 0) {
                    $error = '背番号にマイナスは使用できません。';
                    break;
                }
                if (in_array($num_val, $temp_numbers, true)) {
                    $error = '同じ番号（#' . htmlspecialchars((string)$num_val, ENT_QUOTES, 'UTF-8') . '）が複数入力されています。';
                    break;
                }
                $valid_players[] = ['name' => $n_val, 'num' => $num_val];
                $temp_numbers[] = $num_val;
            }
        }

        if ($error !== '') {
            $msg = "<p style='color:#e74c3c; font-weight:bold;'>" . $error . "</p>";
        } elseif (empty($valid_players)) {
            $msg = "<p style='color:#e74c3c;'>登録する選手を1名以上入力してください。</p>";
        } else {
            try {
                $pdo->beginTransaction();
                $del = $pdo->prepare('DELETE FROM players WHERE team_id = ?');
                $del->execute([$tid]);

                $stmt = $pdo->prepare('INSERT INTO players (team_id, name, number) VALUES (?, ?, ?)');
                foreach ($valid_players as $p) {
                    $stmt->execute([$tid, $p['name'], $p['num']]);
                }
                $pdo->commit();
                $msg = "<p style='color:#2ecc71; font-weight:bold;'>リストを更新しました！</p>";

                // 更新後のリストを再取得
                $stmt = $pdo->prepare('SELECT name, number FROM players WHERE team_id = ? ORDER BY number ASC');
                $stmt->execute([$tid]);
                $current_players = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $msg = "<p style='color:#e74c3c;'>エラー: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
            }
        }

        $pre_selected_team = $tid;
    }
}

$tabSuffix = ($__tabId !== '') ? ('&tab_id=' . rawurlencode($__tabId)) : '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>選手リスト編集</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <meta name="viewport" content="width=1024">
    <style>
        body { font-family: sans-serif; background: #f2f2f2; margin: 0; padding: 0; }
        .wrap { padding: 20px; }
        .card { background: #fff; padding: 25px; border-radius: 15px; max-width: 520px; margin: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .player-row { display: flex; gap: 10px; margin-bottom: 8px; align-items: center; }
        .row-num { width: 25px; color: #999; font-size: 0.8em; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .input-name { flex: 2; }
        .input-number { flex: 1; }
        .btn-update { width: 100%; padding: 15px; background: #3498db; color: white; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 15px; }
        .info-msg { background: #fff3cd; color: #856404; padding: 10px; border-radius: 8px; font-size: 0.85em; margin-bottom: 15px; border: 1px solid #ffeeba; }
    </style>
    <link rel="stylesheet" href="../css/basketball.css">
</head>
<body class="basketball-page">
<div class="wrap">
<div class="card">
    <h2 style="margin:0 0 10px 0;">選手リスト編集</h2>
    <div class="info-msg">チームを選択すると現在のメンバーが表示されます。</div>

    <?= $msg ?>

    <form method="post" id="regForm">
        <label style="font-weight:bold;">チーム選択</label>
        <select name="team_id" required style="width:100%; margin: 10px 0 20px;" id="teamSelect">
            <option value="">-- チームを選択 --</option>
            <?php foreach($teams as $t): ?>
                <option value="<?= (int)$t['id'] ?>" <?= $pre_selected_team == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string)$t['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>

        <div style="display:flex; gap:10px; margin-bottom:5px; font-size:0.8em; font-weight:bold; color:#666;">
            <div style="width:25px;"></div>
            <div style="flex:2;">選手名</div>
            <div style="flex:1;">背番号</div>
        </div>

        <?php for($i=0; $i<10; $i++):
            $p_name = $current_players[$i]['name'] ?? '';
            $p_num = $current_players[$i]['number'] ?? '';
        ?>
        <div class="player-row">
            <div class="row-num"><?= ($i+1) ?></div>
            <input type="text" name="names[]" class="input-name" value="<?= htmlspecialchars((string)$p_name, ENT_QUOTES, 'UTF-8') ?>" placeholder="選手名">
            <input type="number" name="numbers[]" class="input-number" value="<?= htmlspecialchars((string)$p_num, ENT_QUOTES, 'UTF-8') ?>" min="0" placeholder="番号">
        </div>
        <?php endfor; ?>

        <button type="submit" name="save" class="btn-update">この内容で上書き保存</button>
    </form>

    <a href="<?= htmlspecialchars(sportdata_add_tab_id('team_manage.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>" style="display:block; text-align:center; margin-top:14px; color:#666; text-decoration:none;">チーム名を編集する</a>
    <a href="<?= htmlspecialchars(sportdata_add_tab_id('basketball_index.php', $__tabId), ENT_QUOTES, 'UTF-8') ?>" style="display:block; text-align:center; margin-top:20px; color:#3498db; text-decoration:none;">← 試合設定へ戻る</a>
</div>
</div>

<script>
  (function() {
    const sel = document.getElementById('teamSelect');
    if (!sel) return;
    const tabSuffix = <?= json_encode($tabSuffix, JSON_UNESCAPED_SLASHES) ?>;
    sel.addEventListener('change', () => {
      const v = sel.value || '';
      if (!v) return;
      location.href = 'registration.php?team_id=' + encodeURIComponent(v) + tabSuffix;
    });
  })();
</script>
</body>
</html>
