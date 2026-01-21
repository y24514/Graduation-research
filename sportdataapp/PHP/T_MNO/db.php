<?php
// --- データベース接続設定 ---
function getDbConnection() {
    $host   = 'localhost';
    $dbname = 'sportdata_db';
    $user   = 'root';
    $pass   = '';

    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $db = new PDO($dsn, $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die("⚠️ DB接続エラー: " . $e->getMessage());
    }
}

// --- 試合結果を保存する関数 ---
function saveGameResult($db, $d) {
    // 1. gamesテーブルに基本情報を保存
    $groupId = $d['group_id'] ?? null;
    $savedByUserId = $d['saved_by_user_id'] ?? null;

    try {
        $stmt = $db->prepare("INSERT INTO tennis_games (team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, group_id, saved_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $d['teamA'], $d['teamB'], $d['gamesA'], $d['gamesB'],
            $d['a1'], $d['a2'] ?? '', $d['b1'], $d['b2'] ?? '',
            $groupId, $savedByUserId,
        ]);
    } catch (PDOException $e) {
        // 互換: 既存DBに列が無い場合
        $stmt = $db->prepare("INSERT INTO tennis_games (team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $d['teamA'], $d['teamB'], $d['gamesA'], $d['gamesB'],
            $d['a1'], $d['a2'] ?? '', $d['b1'], $d['b2'] ?? ''
        ]);
    }
    $gameId = $db->lastInsertId();

    // 2. actionsテーブルに全履歴を保存
    $stmtAct = $db->prepare("INSERT INTO tennis_actions (game_id, player_name, action_type, score_a, score_b) VALUES (?, ?, ?, ?, ?)");
    foreach ($d['history'] as $h) {
        $stmtAct->execute([$gameId, $h['player'], $h['action'], $h['sA'], $h['sB']]);
    }

    return $gameId;
}

// --- 特定の試合データを取得する関数 ---
function getGameDetail($db, $gameId) {
    $stmt = $db->prepare("SELECT * FROM tennis_games WHERE id = ?");
    $stmt->execute([$gameId]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$game) return null;

    $stmtAct = $db->prepare("SELECT * FROM tennis_actions WHERE game_id = ?");
    $stmtAct->execute([$gameId]);
    $actions = $stmtAct->fetchAll(PDO::FETCH_ASSOC);

    return ['game' => $game, 'actions' => $actions];
}

// --- 試合一覧（全件）を取得する関数 ---
function getAllGames($db) {
    // 新しい順（id DESC）に取得
    $stmt = $db->query("SELECT * FROM tennis_games ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- 選手ごとの勝率・戦績を取得する関数 ---
function getPlayerStats($db) {
    $games = $db->query("SELECT * FROM tennis_games")->fetchAll(PDO::FETCH_ASSOC);
    $stats = [];

    foreach ($games as $g) {
        // この試合の全参加者リスト
        $playersA = array_filter([$g['player_a1'], $g['player_a2']]);
        $playersB = array_filter([$g['player_b1'], $g['player_b2']]);
        $allMatchPlayers = array_merge($playersA, $playersB);

        // どちらが勝ったか
        $winA = $g['games_a'] > $g['games_b'];
        $winB = $g['games_b'] > $g['games_a'];

        foreach ($allMatchPlayers as $p) {
            if (!isset($stats[$p])) {
                $stats[$p] = ['win' => 0, 'lose' => 0, 'total' => 0];
            }
            $stats[$p]['total']++;
            
            // その選手がチームAにいてAが勝った、もしくはチームBにいてBが勝った場合
            if ((in_array($p, $playersA) && $winA) || (in_array($p, $playersB) && $winB)) {
                $stats[$p]['win']++;
            } else {
                $stats[$p]['lose']++;
            }
        }
    }

    // 勝率を計算して並び替える
    foreach ($stats as $name => &$s) {
        $s['rate'] = ($s['total'] > 0) ? round(($s['win'] / $s['total']) * 100, 1) : 0;
    }
    
    // 勝率順にソート
    uasort($stats, function($a, $b) { return $b['rate'] <=> $a['rate']; });
    return $stats;
}
// --- 全選手の通算アクション統計を取得する関数 ---
function getAllPlayerActionStats($db) {
    // 全アクションを新しい順に取得
    $stmt = $db->query("SELECT player_name, action_type FROM tennis_actions");
    $allActions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stats = [];
    foreach ($allActions as $row) {
        $p = $row['player_name'];
        $a = $row['action_type'];
        if (!isset($stats[$p])) {
            $stats[$p] = [
                "サービスエース"=>0, "スマッシュ"=>0, "ボレー"=>0, "ストローク"=>0, "リターンエース"=>0, "ネットイン"=>0,
                "ダブルフォルト"=>0, "アウト"=>0, "ネット"=>0, "ネットタッチ"=>0, "オーバーネット"=>0, "ボディタッチ"=>0,
                "ダイレクト"=>0, "チップ"=>0, "total"=>0
            ];
        }
        if (isset($stats[$p][$a])) {
            $stats[$p][$a]++;
            $stats[$p]['total']++;
        }
    }
    return $stats;
}
?>