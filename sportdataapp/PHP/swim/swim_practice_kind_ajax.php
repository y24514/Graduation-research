<?php
require_once __DIR__ . '/../session_bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized'], JSON_UNESCAPED_UNICODE);
    exit;
}

$group_id = $_SESSION['group_id'];

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'y24514';
$dbPass = getenv('DB_PASS') ?: 'Kr96main0303';
$dbName = getenv('DB_NAME') ?: 'sportdata_db';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'db_connect_failed'], JSON_UNESCAPED_UNICODE);
    exit;
}
mysqli_set_charset($link, 'utf8');

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

$defaultKinds = ['W-up', 'SKP', 'Pull', 'Kick', 'Swim', 'Drill', 'Main', 'Down'];

// テーブルが無ければ作成（権限が無い場合は失敗してもlistだけ返す）
@mysqli_query(
    $link,
    "CREATE TABLE IF NOT EXISTS swim_practice_kind_tbl (\n"
    . "  id INT AUTO_INCREMENT PRIMARY KEY,\n"
    . "  group_id VARCHAR(64) NOT NULL,\n"
    . "  kind_name VARCHAR(64) NOT NULL,\n"
    . "  sort_order INT NOT NULL DEFAULT 0,\n"
    . "  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n"
    . "  UNIQUE KEY uq_group_kind (group_id, kind_name),\n"
    . "  INDEX idx_group (group_id)\n"
    . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// デフォルト種別をグループへシード（存在していれば無視）
$seedStmt = mysqli_prepare($link, "INSERT IGNORE INTO swim_practice_kind_tbl (group_id, kind_name, sort_order) VALUES (?, ?, ?)");
if ($seedStmt) {
    foreach ($defaultKinds as $idx => $k) {
        $order = $idx + 1;
        mysqli_stmt_bind_param($seedStmt, 'ssi', $group_id, $k, $order);
        @mysqli_stmt_execute($seedStmt);
    }
    mysqli_stmt_close($seedStmt);
}

if ($action === 'add') {
    $name = trim((string)($_POST['kind_name'] ?? ''));
    $name = preg_replace('/\s+/u', ' ', $name);

    if ($name === '' || mb_strlen($name) > 64) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'invalid_kind_name'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = mysqli_prepare($link, "INSERT IGNORE INTO swim_practice_kind_tbl (group_id, kind_name, sort_order) VALUES (?, ?, 0)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ss', $group_id, $name);
        @mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

$kinds = [];
$stmt = mysqli_prepare($link, "SELECT kind_name FROM swim_practice_kind_tbl WHERE group_id = ? ORDER BY sort_order ASC, id ASC");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $group_id);
    if (mysqli_stmt_execute($stmt)) {
        $res = mysqli_stmt_get_result($stmt);
        while ($res && ($row = mysqli_fetch_assoc($res))) {
            $k = (string)($row['kind_name'] ?? '');
            if ($k !== '') $kinds[] = $k;
        }
    }
    mysqli_stmt_close($stmt);
}

echo json_encode(['ok' => true, 'kinds' => $kinds], JSON_UNESCAPED_UNICODE);
