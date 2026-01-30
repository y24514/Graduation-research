<?php
require_once __DIR__ . '/session_bootstrap.php';
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
    // ※フロントの action 形式は { q, team, player, type, point, result, ... }
    $stmtAct = $pdo->prepare("INSERT INTO game_actions (game_id, quarter, team, player_id, player_name, action_type, point, result) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($game['actions'] as $a) {
        if (!is_array($a)) continue;

        $type = (string)($a['type'] ?? ($a['action_type'] ?? ''));
        if ($type === '') continue;
        if ($type === 'sub') continue; // 交代は除外（必要なら追加可能）

        $team = (string)($a['team'] ?? '');
        if ($team !== 'A' && $team !== 'B') continue;

        // quarter は `q` が正。互換で `quarter` も許容。
        $quarter = (int)($a['q'] ?? ($a['quarter'] ?? 0));
        if ($quarter <= 0) {
            // 万一欠落している場合は、最低限 1 を入れてNULLを回避
            $quarter = 1;
        }

        $playerId = (int)($a['player'] ?? ($a['player_id'] ?? 0));
        if ($playerId <= 0) continue;

        $playerName = $game['teams'][$team]['names'][$playerId] ?? ($a['playerName'] ?? ($a['player_name'] ?? 'Unknown'));
        $playerName = (string)$playerName;

        $point = (int)($a['point'] ?? 0);
        $result = (string)($a['result'] ?? 'success');

        $stmtAct->execute([
            $gameId,
            $quarter,
            $team,
            $playerId,
            $playerName,
            $type,
            $point,
            $result
        ]);
    }

    $pdo->commit();
    // 保存が終わったらセッションを消して、履歴画面やTOPへ
    unset($_SESSION['game']);
    $tabId = (string)($GLOBALS['SPORTDATA_TAB_ID'] ?? ($_GET['tab_id'] ?? ($_POST['tab_id'] ?? '')));
    if ($tabId !== '' && !preg_match('/^[A-Za-z0-9_-]{8,64}$/', $tabId)) {
        $tabId = '';
    }
    $target = 'history.php?msg=saved' . ($tabId !== '' ? ('&tab_id=' . rawurlencode($tabId)) : '');
    header('Location: ' . $target);

} catch (Exception $e) {
    $pdo->rollBack();
    die("DB保存エラー: " . $e->getMessage());
}