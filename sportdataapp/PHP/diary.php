<?php
session_start();

/* =====================
   セッションチェック
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: login.php');
    exit;
}

// ローディング表示なし
$showLoader = false;

/* =====================
   DB接続
===================== */
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'y24514';
$dbPass = getenv('DB_PASS') ?: 'Kr96main0303';
$dbName = getenv('DB_NAME') ?: 'sportdata_db';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    error_log('DB connect error: ' . mysqli_connect_error());
    http_response_code(500);
    echo 'データベース接続に失敗しました。';
    exit;
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$userName = $_SESSION['name'] ?? '';

$success_message = '';
$error_message = '';

/* =====================
   日記の保存・更新
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_diary'])) {
    $diary_date = $_POST['diary_date'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (empty($diary_date) || empty($content)) {
        $error_message = '日付と内容は必須です。';
    } else {
        // 既存の日記があるかチェック
        $check_stmt = mysqli_prepare($link, "SELECT id FROM diary_tbl WHERE group_id=? AND user_id=? AND diary_date=?");
        mysqli_stmt_bind_param($check_stmt, "sss", $group_id, $user_id, $diary_date);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_fetch_assoc($result)) {
            // 更新
            $update_stmt = mysqli_prepare($link, "UPDATE diary_tbl SET title=?, content=?, updated_at=NOW() WHERE group_id=? AND user_id=? AND diary_date=?");
            mysqli_stmt_bind_param($update_stmt, "sssss", $title, $content, $group_id, $user_id, $diary_date);
            if (mysqli_stmt_execute($update_stmt)) {
                $success_message = '日記を更新しました。';
            } else {
                $error_message = '日記の更新に失敗しました。';
            }
            mysqli_stmt_close($update_stmt);
        } else {
            // 新規登録
            $insert_stmt = mysqli_prepare($link, "INSERT INTO diary_tbl (group_id, user_id, diary_date, title, content) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($insert_stmt, "sssss", $group_id, $user_id, $diary_date, $title, $content);
            if (mysqli_stmt_execute($insert_stmt)) {
                $success_message = '日記を保存しました。';
            } else {
                $error_message = '日記の保存に失敗しました。';
            }
            mysqli_stmt_close($insert_stmt);
        }
        mysqli_stmt_close($check_stmt);
    }
}

/* =====================
   日記の削除
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_diary'])) {
    $diary_id = (int)$_POST['diary_id'];
    $delete_stmt = mysqli_prepare($link, "DELETE FROM diary_tbl WHERE id=? AND group_id=? AND user_id=?");
    mysqli_stmt_bind_param($delete_stmt, "iss", $diary_id, $group_id, $user_id);
    if (mysqli_stmt_execute($delete_stmt)) {
        $success_message = '日記を削除しました。';
    } else {
        $error_message = '日記の削除に失敗しました。';
    }
    mysqli_stmt_close($delete_stmt);
}

/* =====================
   日記一覧取得
===================== */
$diaries = [];
$stmt = mysqli_prepare($link, "SELECT id, diary_date, title, content, created_at, updated_at FROM diary_tbl WHERE group_id=? AND user_id=? ORDER BY diary_date DESC");
mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $diaries[] = $row;
    }
}
mysqli_stmt_close($stmt);

$NAV_BASE = '.';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/diary.html.php';
