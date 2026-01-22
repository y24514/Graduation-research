<?php
require_once __DIR__ . '/session_bootstrap.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['group_id'])) {
    header('Location: login.php');
    exit;
}

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'sportsdata_user';
$dbPass = getenv('DB_PASS') ?: 'fujidai14';
$dbName = getenv('DB_NAME') ?: 'sportsdata';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    error_log('DB connect error: ' . mysqli_connect_error());
    http_response_code(500);
    echo 'データベース接続に失敗しました。';
    exit;
}
mysqli_set_charset($link, 'utf8');

$group_id = $_SESSION['group_id'];
$user_id = $_SESSION['user_id'];

$flash = '';
$flashType = 'success';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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

$tableReady = inquiriesTableExists($link);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$tableReady) {
        $flash = 'お問い合わせテーブルが未作成のため送信できません。先にDB SQLを適用してください。';
        $flashType = 'error';
    } else {
        $postedToken = $_POST['csrf_token'] ?? '';
        if (!hash_equals($csrf_token, $postedToken)) {
            $flash = '不正なリクエストです。';
            $flashType = 'error';
        } else {
            $category = trim($_POST['category'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            $allowedCategories = ['bug', 'improve', 'other'];
            if (!in_array($category, $allowedCategories, true)) {
                $category = 'other';
            }

            if ($subject === '' || $message === '') {
                $flash = '件名と内容は必須です。';
                $flashType = 'error';
            } else {
                $stmt = mysqli_prepare(
                    $link,
                    'INSERT INTO inquiries_tbl (group_id, user_id, category, subject, message) VALUES (?, ?, ?, ?, ?)'
                );
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'sssss', $group_id, $user_id, $category, $subject, $message);
                    if (mysqli_stmt_execute($stmt)) {
                        $flash = 'お問い合わせを送信しました。';
                        $flashType = 'success';
                    } else {
                        $flash = '送信に失敗しました。';
                        $flashType = 'error';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $flash = '送信準備に失敗しました。';
                    $flashType = 'error';
                }
            }
        }
    }
}

$myInquiries = [];
if ($tableReady) {
    $stmt = mysqli_prepare(
        $link,
        'SELECT id, category, subject, message, status, response, created_at, responded_at FROM inquiries_tbl WHERE group_id = ? AND user_id = ? ORDER BY created_at DESC'
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ss', $group_id, $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $myInquiries[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
}

require_once __DIR__ . '/../HTML/contact.html.php';
