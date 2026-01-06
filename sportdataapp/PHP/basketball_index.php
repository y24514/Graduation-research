<?php
session_start();
require_once __DIR__ . "/basketball_config/db.php";

$dbError = '';

$teamA_id = $_POST['teamA'] ?? '';
$teamB_id = $_POST['teamB'] ?? '';
$showPlayers = isset($_POST['show_players']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start'])) {
    if (!empty($_POST['state'])) {
        $_SESSION['game'] = json_decode($_POST['state'], true);
        header("Location: game.php");
        exit;
    }
}
$teams = [];
$playersA = [];
$playersB = [];
try {
    $teams = $pdo->query("SELECT id, name FROM teams")->fetchAll(PDO::FETCH_ASSOC);
    if ($showPlayers && $teamA_id && $teamB_id) {
        $stmt = $pdo->prepare("SELECT id, number, name FROM players WHERE team_id = ? ORDER BY number");
        $stmt->execute([$teamA_id]);
        $playersA = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->execute([$teamB_id]);
        $playersB = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $dbError = $e->getMessage();
    $showPlayers = false;
}

$noTeams = empty($teams);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>試合設定</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: sans-serif; background: #f2f2f2; margin: 0; padding: 0; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .box { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .setup-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 600px) { .setup-grid { grid-template-columns: 1fr; } }
        
        .player { border: 1px solid #ddd; padding: 12px; margin: 5px 0; cursor: pointer; border-radius: 8px; background: #f9f9f9; transition: 0.2s; }
        .player:hover { background: #f0f0f0; }
        .player.starter { background: #c9f7d5 !important; border-color: #2ecc71; box-shadow: inset 0 0 5px rgba(46,204,113,0.5); font-weight: bold; }
        
        select { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; font-size: 16px; }
        .btn { padding: 15px; border: none; border-radius: 8px; color: white; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-blue { background: #3498db; }
        .btn-blue:hover { background: #2980b9; }
        .btn-green { background: #2ecc71; margin-top: 20px; }
        .btn-green:hover { background: #27ae60; }
    </style>
</head>
<body>
<?php
$NAV_BASE = '..';
$NAV_BASE = '.';
require_once __DIR__ . '/header.php';
?>
<div class="container">
    <h1>BasketLog <small style="font-size: 0.5em; color: #666;">試合設定</small></h1>

    <?php if ($noTeams && empty($dbError)): ?>
        <div class="box" style="border: 1px solid #fde68a; background: #fffbeb;">
            <h3 style="margin-top:0; color:#92400e;">チーム/選手が未登録です</h3>
            <p style="margin:0 0 10px; color:#92400e;">sportdata_db の <code>teams</code> と <code>players</code> にデータを入れると選択できるようになります。</p>
            <p style="margin:0; color:#92400e;">同梱の seed SQL（<code>sportdataapp/db/basketball_seed.sql</code>）を phpMyAdmin のSQLタブで実行してください。</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($dbError)): ?>
        <div class="box" style="border: 1px solid #fecaca; background: #fff5f5;">
            <h3 style="margin-top:0; color:#b91c1c;">バスケ用テーブルが未作成です</h3>
            <p style="margin:0 0 10px; color:#7f1d1d;">sportdata_db に teams / players テーブルを作成してください。</p>
            <pre style="white-space:pre-wrap; background:#fff; border:1px solid #e5e7eb; padding:12px; border-radius:10px; font-size:0.9em;">CREATE TABLE `teams` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `players` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `team_id` INT NOT NULL,
  `number` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;</pre>
            <details style="margin-top:10px;">
                <summary style="cursor:pointer; color:#6b7280;">エラー詳細</summary>
                <pre style="white-space:pre-wrap; background:#fff; border:1px solid #e5e7eb; padding:12px; border-radius:10px; font-size:0.85em;"><?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></pre>
            </details>
        </div>
    <?php endif; ?>

    <form method="post" id="setupForm">
        <div class="box">
            <h3>1. チーム選択</h3>
            <div class="setup-grid">
                <div><label>Team A</label><select name="teamA" id="selectA"><?php foreach($teams as $t): ?><option value="<?=$t['id']?>" <?=$teamA_id==$t['id']?'selected':''?>><?=$t['name']?></option><?php endforeach; ?></select></div>
                <div><label>Team B</label><select name="teamB" id="selectB"><?php foreach($teams as $t): ?><option value="<?=$t['id']?>" <?=$teamB_id==$t['id']?'selected':''?>><?=$t['name']?></option><?php endforeach; ?></select></div>
            </div>
            <br><button type="submit" name="show_players" class="btn btn-blue">選手リストを表示</button>
        </div>

        <?php if ($showPlayers): ?>
        <div class="box">
            <h3>2. スタメン選択 (各5人)</h3>
            <div class="setup-grid">
                <div>
                    <h4><?=$teams[array_search($teamA_id, array_column($teams, 'id'))]['name']?></h4>
                    <?php foreach ($playersA as $p): ?><div class="player" data-team="A" data-id="<?=$p['id']?>" data-name="#<?=$p['number']?> <?=$p['name']?>">#<?=$p['number']?> <?=$p['name']?></div><?php endforeach; ?>
                </div>
                <div>
                    <h4><?=$teams[array_search($teamB_id, array_column($teams, 'id'))]['name']?></h4>
                    <?php foreach ($playersB as $p): ?><div class="player" data-team="B" data-id="<?=$p['id']?>" data-name="#<?=$p['number']?> <?=$p['name']?>">#<?=$p['number']?> <?=$p['name']?></div><?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" name="state" id="state">
            <button type="submit" name="start" class="btn btn-green">このメンバーで試合開始！</button>
        </div>
        <?php endif; ?>
    </form>
</div>
<script>
    const state = { teams: { A: { starters: [], names: {} }, B: { starters: [], names: {} } } };
    document.querySelectorAll('.player').forEach(p => {
        p.onclick = () => {
            const team = p.dataset.team; const id = p.dataset.id;
            const otherTeam = (team === 'A' ? 'B' : 'A');
            let s = state.teams[team].starters;
            let otherS = state.teams[otherTeam].starters;

            if (s.includes(id)) {
                state.teams[team].starters = s.filter(x => x !== id);
                p.classList.remove('starter');
            } else {
                if (otherS.includes(id)) { alert("すでに反対のチームで選ばれています"); return; }
                if (s.length < 5) { state.teams[team].starters.push(id); p.classList.add('starter'); }
                else { alert("スタメンは5人までです"); }
            }
        };
    });

    document.getElementById('setupForm').onsubmit = (e) => {
        if (e.submitter.name !== 'start') return true;
        if (state.teams.A.starters.length !== 5 || state.teams.B.starters.length !== 5) { alert("5人ずつ選んでください"); return false; }
        const allNames = {}; document.querySelectorAll('.player').forEach(p => allNames[p.dataset.id] = p.dataset.name);
        const getBench = (t) => [...document.querySelectorAll(`.player[data-team="${t}"]`)].map(p=>p.dataset.id).filter(id=>!state.teams[t].starters.includes(id));
        const selA = document.getElementById('selectA'); const selB = document.getElementById('selectB');
        document.getElementById('state').value = JSON.stringify({
            quarter: 1, score: { A: 0, B: 0 },
            teamNames: { A: selA.options[selA.selectedIndex].text, B: selB.options[selB.selectedIndex].text },
            teams: { A: { starters: state.teams.A.starters, bench: getBench('A'), names: allNames }, B: { starters: state.teams.B.starters, bench: getBench('B'), names: allNames } },
            actions: []
        });
        return true;
    };
</script>
</body>
</html>