<?php
require_once __DIR__ . '/../session_bootstrap.php';

// 共通ナビ用：アクセスURLに応じてPHPルートへの相対パスを切り替える
// - /PHP/T_MNO/* で開いている場合: ..
// - /T_MNO/* (互換ラッパー経由) の場合: ../PHP
$uri = $_SERVER['REQUEST_URI'] ?? '';
$NAV_BASE = (stripos($uri, '/PHP/T_MNO/') !== false) ? '..' : '../PHP';

// --- 1. データの初期化 ---
if (isset($_POST['start'])) {
    $_SESSION['data'] = [
        'mode'      => $_POST['mode'] ?? 'single',
        'teamA'     => ($_POST['teamA'] !== '') ? $_POST['teamA'] : 'Team A',
        'teamB'     => ($_POST['teamB'] !== '') ? $_POST['teamB'] : 'Team B',
        'a1'        => $_POST['playerA1'] ?? 'A1',
        'a2'        => ($_POST['mode'] === 'double') ? ($_POST['playerA2'] ?? 'A2') : '',
        'b1'        => $_POST['playerB1'] ?? 'B1',
        'b2'        => ($_POST['mode'] === 'double') ? ($_POST['playerB2'] ?? 'B2') : '',
        'matchType' => intval($_POST['matchType'] ?? 5),
        'scoreA'    => 0, 'scoreB' => 0,
        'gamesA'    => 0, 'gamesB' => 0,
        'history'   => []
    ];
}

if (!isset($_SESSION['data'])) {
    header('Location: index.php');
    exit;
}

$d = &$_SESSION['data'];
$targetG = ($d['matchType'] == 5) ? 3 : 4;
$isFinal = ($d['gamesA'] == $targetG - 1 && $d['gamesB'] == $targetG - 1);

// --- 2. Undo (１つ戻す) の処理 ---
if (isset($_POST['undo'])) {
    if (!empty($d['history'])) {
        array_pop($d['history']);
        if (empty($d['history'])) {
            $d['scoreA'] = 0; $d['scoreB'] = 0;
            $d['gamesA'] = 0; $d['gamesB'] = 0;
        } else {
            $last = end($d['history']);
            $d['scoreA'] = $last['sA'];
            $d['scoreB'] = $last['sB'];
            $d['gamesA'] = $last['gA'];
            $d['gamesB'] = $last['gB'];
        }
    }
} 
// --- 3. スコア計算ロジック ---
elseif (isset($_POST['action_btn'])) {
    $p = $_POST['player'];
    $a = $_POST['action'];
    $gains = ['サービスエース','スマッシュ','ボレー','ストローク','リターンエース','ネットイン'];
    $playersA = array_filter([$d['a1'], $d['a2']]);
    $isPlayerA = in_array($p, $playersA);
    $isGainAction = in_array($a, $gains);

    if (($isPlayerA && $isGainAction) || (!$isPlayerA && !$isGainAction)) {
        $d['scoreA']++;
    } else {
        $d['scoreB']++;
    }

    $d['history'][] = [
        'player' => $p, 'action' => $a,
        'sA' => $d['scoreA'], 'sB' => $d['scoreB'],
        'gA' => $d['gamesA'], 'gB' => $d['gamesB']
    ];

    $targetP = $isFinal ? 7 : 4;
    if (($d['scoreA'] >= $targetP || $d['scoreB'] >= $targetP) && abs($d['scoreA'] - $d['scoreB']) >= 2) {
        if ($d['scoreA'] > $d['scoreB']) $d['gamesA']++; else $d['gamesB']++;
        $d['scoreA'] = 0; $d['scoreB'] = 0;
        if ($d['gamesA'] == $targetG || $d['gamesB'] == $targetG) {
            header('Location: result.php');
            exit;
        }
    }
}

if (isset($_POST['reset'])) {
    unset($_SESSION['data']);
    header('Location: index.php');
    exit;
}

// ボタンの定義を共通化
$acts = ['サービスエース', 'スマッシュ', 'ボレー', 'ストローク', 'リターンエース', 'ネットイン', 'ダブルフォルト', 'アウト', 'ネット', 'ネットタッチ', 'オーバーネット', 'ボディタッチ', 'ダイレクト', 'チップ'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=1024">
    <title>試合記録</title>
    <link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
    <style>
        body { font-family: sans-serif; margin:0; background:#f0f2f5; overflow: hidden; height: 100vh; display: flex; flex-direction: column; }
        .app-header { height: 45px; background: #2c3e50; color: white; display: flex; align-items: center; padding: 0 15px; flex-shrink: 0; }
        .header-title { font-size: 13px; font-weight: bold; flex-grow: 1; text-align: center; }
        .score-section { background: #34495e; color: white; padding: 10px 0; text-align: center; flex-shrink: 0; }
        .score-main { font-size: 48px; font-weight: bold; line-height: 1; margin: 2px 0; }
        .final-mode { background: #c62828 !important; }
        .final-label { background: white; color: #c62828; font-size: 11px; font-weight: bold; padding: 2px 10px; border-radius: 10px; display: inline-block; margin-bottom: 4px; }
        .input-container { flex-grow: 1; display: flex; gap: 4px; padding: 4px; background: #dfe4ea; overflow: hidden; }
        .col { background:white; border-radius:6px; flex:1; display: flex; flex-direction: column; overflow: hidden; }
        .player-name { background: #f8f9fa; padding: 6px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #ddd; text-align: center; }
        .btn-group { flex-grow: 1; display: flex; flex-direction: column; padding: 2px; gap: 2px; overflow-y: auto; }
        .act-btn { width: 100%; flex: 1; border: 1px solid #ccc; border-radius: 4px; font-size: 10px; font-weight: bold; cursor: pointer; min-height: 30px; }
        .gain { background:#e3f2fd; color:#1565c0; border-color: #bbdefb; }
        .lose { background:#ffebee; color:#c62828; border-color: #ffcdd2; }
        .footer-btns { display: flex; gap: 4px; padding: 6px; background: #2c3e50; }
        .footer-btn { flex: 1; height: 40px; border-radius: 4px; border: none; font-weight: bold; }
        .drawer { position: fixed; top: 0; left: -280px; width: 260px; height: 100%; background: #fff; box-shadow: 2px 0 10px rgba(0,0,0,0.3); transition: 0.3s; z-index: 1000; overflow-y: auto; }
        .drawer.open { left: 0; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 900; }
        .overlay.show { display: block; }
        .history-item { padding: 10px; border-bottom: 1px solid #eee; font-size: 12px; }
    </style>
    <link rel="stylesheet" href="../../css/tennis.css">
</head>
<body class="tennis-page tennis-game">

<?php require_once __DIR__ . '/../header.php'; ?>

<div id="historyDrawer" class="drawer">
    <div style="background:#2c3e50; color:white; padding:15px; font-weight:bold;">試合履歴</div>
    <?php foreach(array_reverse($d['history']) as $h): ?>
        <div class="history-item">
            <strong><?= htmlspecialchars($h['player']) ?></strong>: <?= htmlspecialchars($h['action']) ?>
            <span style="float:right; font-weight:bold;"><?= $h['sA'] ?>-<?= $h['sB'] ?></span>
        </div>
    <?php endforeach; ?>
</div>
<div id="overlay" class="overlay" onclick="toggleDrawer()"></div>

<header class="app-header">
    <div style="font-size:24px; cursor:pointer;" onclick="toggleDrawer()">☰</div>
    <div class="header-title"><?= htmlspecialchars($d['teamA']) ?> vs <?= htmlspecialchars($d['teamB']) ?></div>
</header>

<section class="score-section <?= $isFinal ? 'final-mode' : '' ?>">
    <?php if ($isFinal): ?><div class="final-label">🔥 FINAL GAME 🔥</div><?php endif; ?>
    <div style="font-size:11px; opacity:0.8;">Game: <?= $d['gamesA'] ?> - <?= $d['gamesB'] ?></div>
    <div class="score-main"><?= $d['scoreA'] ?> - <?= $d['scoreB'] ?></div>
</section>

<?php if ($d['mode'] === 'double'): ?>
<div style="display: flex; gap: 2px; padding: 4px; background: #2c3e50;">
    <button id="tabA" onclick="switchTeam('A')" style="flex:1; padding:8px; background:#3498db; color:white; border:none; font-weight:bold; border-radius:4px;">Team A (味方)</button>
    <button id="tabB" onclick="switchTeam('B')" style="flex:1; padding:8px; background:#95a5a6; color:white; border:none; font-weight:bold; border-radius:4px;">Team B (相手)</button>
</div>
<?php endif; ?>

<main class="input-container">
    <div id="areaA" style="display: flex; flex:1; gap:4px;">
        <?php foreach(array_filter([$d['a1'], $d['a2']]) as $p): ?>
        <div class="col">
            <div class="player-name"><?= htmlspecialchars($p) ?></div>
            <div class="btn-group">
                <?php foreach($acts as $i => $v): ?>
                <button class="act-btn <?= ($i < 6) ? 'gain' : 'lose' ?>" onclick="act('<?= addslashes($p) ?>','<?= $v ?>')"><?= $v ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div id="areaB" style="display: <?= ($d['mode']==='single' ? 'flex' : 'none') ?>; flex:1; gap:4px;">
        <?php foreach(array_filter([$d['b1'], $d['b2']]) as $p): ?>
        <div class="col">
            <div class="player-name"><?= htmlspecialchars($p) ?></div>
            <div class="btn-group">
                <?php foreach($acts as $i => $v): ?>
                <button class="act-btn <?= ($i < 6) ? 'gain' : 'lose' ?>" onclick="act('<?= addslashes($p) ?>','<?= $v ?>')"><?= $v ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<footer class="footer-btns">
    <form method="post" id="actF" style="display:contents;">
        <input type="hidden" name="player" id="p_in"><input type="hidden" name="action" id="a_in"><input type="hidden" name="action_btn" value="1">
        <button type="submit" name="undo" class="footer-btn" style="background:#fff;">戻す</button>
        <button type="submit" name="reset" class="footer-btn" style="background:#e74c3c; color:white;" onclick="return confirm('途中で終了すると記録されません。終了しますか？')">終了</button>
    </form>
</footer>

<script>
function toggleDrawer() { document.getElementById('historyDrawer').classList.toggle('open'); document.getElementById('overlay').classList.toggle('show'); }
function switchTeam(team) {
    if ("<?= $d['mode'] ?>" === "single") return;
    const areaA = document.getElementById('areaA'); const areaB = document.getElementById('areaB');
    const tabA = document.getElementById('tabA'); const tabB = document.getElementById('tabB');
    if (team === 'A') { areaA.style.display = 'flex'; areaB.style.display = 'none'; tabA.style.background = '#3498db'; tabB.style.background = '#95a5a6'; }
    else { areaA.style.display = 'none'; areaB.style.display = 'flex'; tabA.style.background = '#95a5a6'; tabB.style.background = '#e74c3c'; }
}
function act(p,a){ document.getElementById('p_in').value=p; document.getElementById('a_in').value=a; document.getElementById('actF').submit(); }
</script>
</body>
</html>