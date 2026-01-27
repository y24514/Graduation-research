<?php
require_once __DIR__ . '/../session_bootstrap.php';
// セッションをクリアして、プログラム側からの自動入力を防ぐ
unset($_SESSION['last_saved_id']); 
unset($_SESSION['data']); 

// 共通ナビ用：アクセスURLに応じてPHPルートへの相対パスを切り替える
// - /PHP/T_MNO/* で開いている場合: ..
// - /T_MNO/* (互換ラッパー経由) の場合: ../PHP
$uri = $_SERVER['REQUEST_URI'] ?? '';
$NAV_BASE = (stripos($uri, '/PHP/T_MNO/') !== false) ? '..' : '../PHP';

require_once __DIR__ . '/db.php';
$db = getDbConnection();

// DBセットアップ（変更なし）
try {
    $db->exec("CREATE TABLE IF NOT EXISTS games (
        id INT AUTO_INCREMENT PRIMARY KEY,
        team_a VARCHAR(255), team_b VARCHAR(255), 
        games_a INT, games_b INT, 
        player_a1 VARCHAR(255), player_a2 VARCHAR(255),
        player_b1 VARCHAR(255), player_b2 VARCHAR(255),
        match_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $db->exec("CREATE TABLE IF NOT EXISTS actions (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        game_id INT, player_name VARCHAR(255), action_type VARCHAR(255), 
        score_a INT, score_b INT,
        FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
    )");
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024">
    <title>試合設定</title>
    <link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
    <style>
        body { font-family: sans-serif; margin:0; background:#f0f2f5; color: #2c3e50; }
        .app-header { height: 50px; background: #2c3e50; color: white; display: flex; align-items: center; padding: 0 15px; font-weight: bold; }
        .container { padding: 15px; max-width: 500px; margin: auto; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 15px; }
        h3 { margin-top: 0; font-size: 16px; border-left: 4px solid #3498db; padding-left: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 12px; margin-bottom: 5px; font-weight: bold; color: #7f8c8d; }
        input[type="text"], select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 16px; background: #fff; }
        .mode-selector { display: flex; gap: 10px; margin-bottom: 15px; }
        .mode-selector label { flex: 1; text-align: center; cursor: pointer; }
        .mode-selector input { display: none; }
        .mode-btn { display: block; padding: 12px; background: #eee; border-radius: 6px; font-weight: bold; }
        input:checked + .mode-btn { background: #3498db; color: white; }
        .start-btn { width: 100%; padding: 18px; background: #27ae60; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; }
        .double-only { display: none; }
    </style>
    <link rel="stylesheet" href="../../css/tennis.css">
</head>
<body class="tennis-page tennis-index">
<?php require_once __DIR__ . '/../header.php'; ?>
<header class="app-header">テニススコア設定</header>
<div class="container">
    <form action="game.php" method="post" autocomplete="off">
        <div class="card">
            <h3>試合モード</h3>
            <div class="mode-selector">
                <label><input type="radio" name="mode" value="single" checked onclick="toggleMode('single')"><span class="mode-btn">シングルス</span></label>
                <label><input type="radio" name="mode" value="double" onclick="toggleMode('double')"><span class="mode-btn">ダブルス</span></label>
            </div>
            <label>マッチ形式</label>
            <select name="matchType">
                <option value="5">5ゲームマッチ (3G先取)</option>
                <option value="7">7ゲームマッチ (4G先取)</option>
            </select>
        </div>

        <div class="card">
            <h3>Team A (青)</h3>
            <div class="form-group">
                <label>チーム名</label>
                <input type="text" name="teamA" value="" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label>選手1</label>
                <input type="text" name="playerA1" value="" required autocomplete="new-password">
            </div>
            <div class="form-group double-only" id="a2-box">
                <label>選手2</label>
                <input type="text" name="playerA2" value="" autocomplete="new-password">
            </div>
        </div>

        <div class="card">
            <h3>Team B (赤)</h3>
            <div class="form-group">
                <label>チーム名</label>
                <input type="text" name="teamB" value="" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label>選手1</label>
                <input type="text" name="playerB1" value="" required autocomplete="new-password">
            </div>
            <div class="form-group double-only" id="b2-box">
                <label>選手2</label>
                <input type="text" name="playerB2" value="" autocomplete="new-password">
            </div>
        </div>

        <button type="submit" name="start" class="start-btn">試合開始</button>
    </form>
</div>
<script>
function toggleMode(m) { 
    document.querySelectorAll('.double-only').forEach(b => b.style.display = (m==='double')?'block':'none'); 
}
</script>
</body>
</html>