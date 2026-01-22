<?php
require_once __DIR__ . '/session_bootstrap.php';

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

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$userName = $_SESSION['name'] ?? '';

$isAdminUser = !empty($_SESSION['is_admin']) || !empty($_SESSION['is_super_admin']);
$canSubmitDiaryToAdmin = !$isAdminUser;

// 互換: DBに提出用カラムがある場合のみ機能ON
$hasDiarySubmitColumns = false;
$hasDiarySubmitAtColumn = false;
// 互換: 管理者フィードバック用カラム
$hasDiaryFeedbackColumn = false;
$hasDiaryFeedbackAtColumn = false;
$hasDiaryFeedbackByColumn = false;
try {
    $chk = mysqli_prepare(
        $link,
        "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'diary_tbl' AND COLUMN_NAME IN ('submitted_to_admin','submitted_at','admin_feedback','admin_feedback_at','admin_feedback_by_user_id')"
    );
    if ($chk) {
        mysqli_stmt_execute($chk);
        $res = mysqli_stmt_get_result($chk);
        while ($res && ($r = mysqli_fetch_assoc($res))) {
            if (($r['COLUMN_NAME'] ?? '') === 'submitted_to_admin') $hasDiarySubmitColumns = true;
            if (($r['COLUMN_NAME'] ?? '') === 'submitted_at') $hasDiarySubmitAtColumn = true;
            if (($r['COLUMN_NAME'] ?? '') === 'admin_feedback') $hasDiaryFeedbackColumn = true;
            if (($r['COLUMN_NAME'] ?? '') === 'admin_feedback_at') $hasDiaryFeedbackAtColumn = true;
            if (($r['COLUMN_NAME'] ?? '') === 'admin_feedback_by_user_id') $hasDiaryFeedbackByColumn = true;
        }
        mysqli_stmt_close($chk);
    }
} catch (Throwable $e) {
    $hasDiarySubmitColumns = false;
    $hasDiarySubmitAtColumn = false;
    $hasDiaryFeedbackColumn = false;
    $hasDiaryFeedbackAtColumn = false;
    $hasDiaryFeedbackByColumn = false;
}

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
        // 同日でも複数登録できるよう、常に新規登録
        $insert_stmt = mysqli_prepare($link, "INSERT INTO diary_tbl (group_id, user_id, diary_date, title, content) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert_stmt, "sssss", $group_id, $user_id, $diary_date, $title, $content);
        if (mysqli_stmt_execute($insert_stmt)) {
            $success_message = '日記を保存しました。';
        } else {
            $error_message = '日記の保存に失敗しました。';
        }
        mysqli_stmt_close($insert_stmt);
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
$select = "SELECT d.id, d.diary_date, d.title, d.content, d.tags, d.created_at, d.updated_at";
if ($hasDiarySubmitColumns) {
    $select .= ", d.submitted_to_admin";
}
if ($hasDiarySubmitAtColumn) {
    $select .= ", d.submitted_at";
}
$hasDiaryFeedbackColumns = $hasDiaryFeedbackColumn; // テンプレート用
if ($hasDiaryFeedbackColumn) {
    $select .= ", d.admin_feedback";
}
if ($hasDiaryFeedbackAtColumn) {
    $select .= ", d.admin_feedback_at";
}
if ($hasDiaryFeedbackByColumn) {
    $select .= ", d.admin_feedback_by_user_id";
    $select .= ", COALESCE(fb.name, d.admin_feedback_by_user_id) AS admin_feedback_by_user_name";
}
$select .= " FROM diary_tbl d";
if ($hasDiaryFeedbackByColumn) {
    $select .= " LEFT JOIN login_tbl fb ON fb.group_id = d.group_id AND fb.user_id = d.admin_feedback_by_user_id";
}
$select .= " WHERE d.group_id=? AND d.user_id=? ORDER BY d.diary_date DESC, d.created_at DESC, d.id DESC";
$stmt = mysqli_prepare($link, $select);
mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $diaries[] = $row;
    }
}
mysqli_stmt_close($stmt);

// 管理者: 提出された日記一覧
$submittedDiaries = [];
if ($isAdminUser && $hasDiarySubmitColumns) {
    $sel = "SELECT d.id, d.diary_date, d.title, d.content, d.tags, d.user_id, COALESCE(l.name, d.user_id) AS user_name";
    if ($hasDiarySubmitAtColumn) {
        $sel .= ", d.submitted_at";
    }
    if ($hasDiaryFeedbackColumn) {
        $sel .= ", d.admin_feedback";
    }
    if ($hasDiaryFeedbackAtColumn) {
        $sel .= ", d.admin_feedback_at";
    }
    if ($hasDiaryFeedbackByColumn) {
        $sel .= ", d.admin_feedback_by_user_id";
    }
    $sel .= " FROM diary_tbl d LEFT JOIN login_tbl l ON l.group_id = d.group_id AND l.user_id = d.user_id";
    $sel .= " WHERE d.group_id = ? AND d.submitted_to_admin = 1";
    $sel .= " ORDER BY " . ($hasDiarySubmitAtColumn ? "d.submitted_at DESC" : "d.diary_date DESC") . ", d.id DESC LIMIT 200";
    $st = mysqli_prepare($link, $sel);
    if ($st) {
        mysqli_stmt_bind_param($st, 's', $group_id);
        if (mysqli_stmt_execute($st)) {
            $rs = mysqli_stmt_get_result($st);
            while ($rs && ($row = mysqli_fetch_assoc($rs))) {
                $submittedDiaries[] = $row;
            }
        }
        mysqli_stmt_close($st);
    }
}

$NAV_BASE = '.';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/diary.html.php';
