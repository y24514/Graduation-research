<?php
require_once __DIR__ . '/../session_bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized'], JSON_UNESCAPED_UNICODE);
    exit;
}

$group_id = $_SESSION['group_id'];
$user_id  = $_SESSION['user_id'];

$id = $_GET['id'] ?? $_POST['id'] ?? '';
$id = trim((string)$id);
if ($id === '' || !ctype_digit($id)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_id'], JSON_UNESCAPED_UNICODE);
    exit;
}
$practiceId = (int)$id;

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'sportsdata_user';
$dbPass = getenv('DB_PASS') ?: 'fujidai14';
$dbName = getenv('DB_NAME') ?: 'sportsdata';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'db_connect_failed'], JSON_UNESCAPED_UNICODE);
    exit;
}
mysqli_set_charset($link, 'utf8');

$stmt = mysqli_prepare(
    $link,
    'SELECT id, practice_date, title, menu_text, memo, created_at FROM swim_practice_tbl WHERE id = ? AND group_id = ? AND user_id = ? LIMIT 1'
);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'prepare_failed'], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param($stmt, 'iss', $practiceId, $group_id, $user_id);
if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'execute_failed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$res = mysqli_stmt_get_result($stmt);
$row = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$row) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'not_found'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(
    [
        'ok' => true,
        'practice' => [
            'id' => (int)$row['id'],
            'practice_date' => (string)($row['practice_date'] ?? ''),
            'title' => (string)($row['title'] ?? ''),
            'menu_text' => (string)($row['menu_text'] ?? ''),
            'memo' => (string)($row['memo'] ?? ''),
            'created_at' => (string)($row['created_at'] ?? ''),
        ],
    ],
    JSON_UNESCAPED_UNICODE
);
