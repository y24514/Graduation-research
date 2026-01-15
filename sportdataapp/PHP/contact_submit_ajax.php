<?php
require_once __DIR__ . '/session_bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['user_id']) || empty($_SESSION['group_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'ログインが必要です。'], JSON_UNESCAPED_UNICODE);
    exit;
}

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'y24514';
$dbPass = getenv('DB_PASS') ?: 'Kr96main0303';
$dbName = getenv('DB_NAME') ?: 'sportdata_db';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    error_log('DB connect error: ' . mysqli_connect_error());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'データベース接続に失敗しました。'], JSON_UNESCAPED_UNICODE);
    exit;
}
mysqli_set_charset($link, 'utf8');

function inquiriesTableExists(mysqli $link): bool {
    $sql = "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inquiries_tbl' LIMIT 1";
    $res = mysqli_query($link, $sql);
    if (!$res) {
        return false;
    }
    $ok = mysqli_num_rows($res) === 1;
    mysqli_free_result($res);
    return $ok;
}

if (!inquiriesTableExists($link)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'お問い合わせテーブルが未作成です。DB SQLを適用してください。'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = (string)$_SESSION['csrf_token'];
$postedToken = (string)($_POST['csrf_token'] ?? '');
if ($postedToken === '' || !hash_equals($csrfToken, $postedToken)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => '不正なリクエストです。'], JSON_UNESCAPED_UNICODE);
    exit;
}

$group_id = (string)$_SESSION['group_id'];
$user_id = (string)$_SESSION['user_id'];

$category = trim((string)($_POST['category'] ?? ''));
$subject = trim((string)($_POST['subject'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

$allowedCategories = ['bug', 'improve', 'other'];
if (!in_array($category, $allowedCategories, true)) {
    $category = 'other';
}

if ($subject === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => '件名と内容は必須です。'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = mysqli_prepare(
    $link,
    'INSERT INTO inquiries_tbl (group_id, user_id, category, subject, message) VALUES (?, ?, ?, ?, ?)'
);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => '送信準備に失敗しました。'], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_stmt_bind_param($stmt, 'sssss', $group_id, $user_id, $category, $subject, $message);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$ok) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => '送信に失敗しました。'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
