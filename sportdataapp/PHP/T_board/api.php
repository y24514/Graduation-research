<?php
declare(strict_types=1);

require_once __DIR__ . '/../session_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../T_MNO/db.php';
$pdo = getDbConnection();

$action = $_GET['action'] ?? '';
$groupId = $_SESSION['group_id'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

try {
    if ($action === 'save') {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = trim((string)($input['name'] ?? ''));
        $data = (string)($input['data'] ?? '');

        if ($name === '' || $data === '') {
            echo json_encode(['status' => 'error', 'message' => 'データが不足しています'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($groupId !== null && $userId !== null) {
            $stmt = $pdo->prepare('INSERT INTO tennis_strategies (group_id, user_id, name, json_data) VALUES (?, ?, ?, ?)');
            $stmt->execute([$groupId, $userId, $name, $data]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO tennis_strategies (group_id, user_id, name, json_data) VALUES (NULL, NULL, ?, ?)');
            $stmt->execute([$name, $data]);
        }

        echo json_encode(['status' => 'success'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'list') {
        if ($groupId !== null) {
            $stmt = $pdo->prepare('SELECT id, name FROM tennis_strategies WHERE group_id = ? ORDER BY created_at DESC');
            $stmt->execute([$groupId]);
        } else {
            $stmt = $pdo->query('SELECT id, name FROM tennis_strategies WHERE group_id IS NULL ORDER BY created_at DESC');
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'load') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'IDが不正です'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($groupId !== null) {
            $stmt = $pdo->prepare('SELECT json_data FROM tennis_strategies WHERE id = ? AND group_id = ?');
            $stmt->execute([$id, $groupId]);
        } else {
            $stmt = $pdo->prepare('SELECT json_data FROM tennis_strategies WHERE id = ? AND group_id IS NULL');
            $stmt->execute([$id]);
        }

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            echo json_encode(['error' => 'データが見つかりません'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'IDが不正です'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($groupId !== null) {
            $stmt = $pdo->prepare('DELETE FROM tennis_strategies WHERE id = ? AND group_id = ?');
            $stmt->execute([$id, $groupId]);
        } else {
            $stmt = $pdo->prepare('DELETE FROM tennis_strategies WHERE id = ? AND group_id IS NULL');
            $stmt->execute([$id]);
        }
        echo json_encode(['status' => 'success'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['error' => '無効なアクションです'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'サーバーエラーが発生しました', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}