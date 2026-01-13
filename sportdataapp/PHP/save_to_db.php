<?php
session_start();
require_once __DIR__ . '/basketball_logic/db_config.php';

if (!isset($_SESSION['game'])) {
    die("保存するデータがありません。");
}

$game = $_SESSION['game'];

try {
    $pdo->beginTransaction();

    $groupId = $_SESSION['group_id'] ?? null;
    $savedByUserId = $_SESSION['user_id'] ?? null;

    // 1. gamesテーブルに挿入
    try {
        $stmt = $pdo->prepare("INSERT INTO games (team_a_name, team_b_name, score_a, score_b, group_id, saved_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $game['teamNames']['A'],
            $game['teamNames']['B'],
            $game['score']['A'],
            $game['score']['B'],
            $groupId,
            $savedByUserId,
        ]);
    } catch (Exception $e) {
        // 互換: 既存DBに列が無い場合
        $stmt = $pdo->prepare("INSERT INTO games (team_a_name, team_b_name, score_a, score_b) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $game['teamNames']['A'],
            $game['teamNames']['B'],
            $game['score']['A'],
            $game['score']['B']
        ]);
    }
    $gameId = $pdo->lastInsertId();

    // 2. game_actionsテーブルに全アクションを挿入
    $stmtAct = $pdo->prepare("INSERT INTO game_actions (game_id, quarter, team, player_id, player_name, action_type, point, result) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($game['actions'] as $a) {
        if ($a['type'] === 'sub') continue; // 交代は除外（必要なら追加可能）

        $playerName = $game['teams'][$a['team']]['names'][$a['player']] ?? 'Unknown';
        
        $stmtAct->execute([
            $gameId,
            $a['quarter'],
            $a['team'],
            $a['player'],
            $playerName,
            $a['type'],
            $a['point'] ?? 0,
            $a['result'] ?? 'success'
        ]);
    }

    $pdo->commit();
    // 保存が終わったらセッションを消して、履歴画面やTOPへ
    unset($_SESSION['game']);
    header("Location: history.php?msg=saved"); 

} catch (Exception $e) {
    $pdo->rollBack();
    die("DB保存エラー: " . $e->getMessage());
}