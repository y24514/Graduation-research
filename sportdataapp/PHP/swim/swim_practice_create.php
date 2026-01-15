<?php
require_once __DIR__ . '/../session_bootstrap.php';

/* ---------------------
   セッションチェック
--------------------- */
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: ../login.php');
    exit;
}

$showLoader = false;
$group_id = $_SESSION['group_id'];
$user_id  = $_SESSION['user_id'];

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

$errors = [];
$showSuccess = isset($_GET['success']);

$practice_total = 0;
$latest_practice_date = null;
$practices = [];

// テーブルがあるか確認（未作成でも画面が壊れないように）
$hasPracticeTable = false;
$tblRes = mysqli_query(
    $link,
    "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'swim_practice_tbl' LIMIT 1"
);
if ($tblRes && mysqli_num_rows($tblRes) > 0) {
    $hasPracticeTable = true;
}
if ($tblRes) {
    mysqli_free_result($tblRes);
}

if ($hasPracticeTable) {
    // 件数/最新日付
    $cntStmt = mysqli_prepare($link, "SELECT COUNT(*) AS total, MAX(practice_date) AS latest_date FROM swim_practice_tbl WHERE group_id = ? AND user_id = ?");
    if ($cntStmt) {
        mysqli_stmt_bind_param($cntStmt, 'ss', $group_id, $user_id);
        if (mysqli_stmt_execute($cntStmt)) {
            $res = mysqli_stmt_get_result($cntStmt);
            $row = $res ? mysqli_fetch_assoc($res) : null;
            if (is_array($row)) {
                $practice_total = (int)($row['total'] ?? 0);
                $latest_practice_date = $row['latest_date'] ?? null;
            }
        }
        mysqli_stmt_close($cntStmt);
    }

    // 一覧（最新20件）
    $listSql = "SELECT id, practice_date, title, menu_text, memo, created_at FROM swim_practice_tbl WHERE group_id = ? AND user_id = ? ORDER BY practice_date DESC, created_at DESC LIMIT 20";
    $listStmt = mysqli_prepare($link, $listSql);
    if ($listStmt) {
        mysqli_stmt_bind_param($listStmt, 'ss', $group_id, $user_id);
        if (mysqli_stmt_execute($listStmt)) {
            $r = mysqli_stmt_get_result($listStmt);
            while ($r && ($row = mysqli_fetch_assoc($r))) {
                $practices[] = $row;
            }
        } else {
            error_log('Execute failed (list swim_practice_tbl): ' . mysqli_stmt_error($listStmt));
        }
        mysqli_stmt_close($listStmt);
    } else {
        error_log('Prepare failed (list swim_practice_tbl): ' . mysqli_error($link));
    }
}

$practice_date = $_POST['practice_date'] ?? date('Y-m-d');
$title = $_POST['title'] ?? '';
$menu_text = $_POST['menu_text'] ?? '';
$memo = $_POST['memo'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$hasPracticeTable) {
        $errors[] = '練習メニューテーブルが未作成です。db/add_swim_practice_tbl.sql を sportdata_db にインポートしてください。';
    }

    $practice_date = trim((string)$practice_date);
    $title = trim((string)$title);
    $menu_text = trim((string)$menu_text);
    $memo = trim((string)$memo);

    if ($practice_date === '') {
        $errors[] = '日付を入力してください';
    }
    if ($title === '') {
        $errors[] = 'タイトルを入力してください';
    }

    if (empty($errors) && $hasPracticeTable) {
        $sql = "INSERT INTO swim_practice_tbl (group_id, user_id, practice_date, title, menu_text, memo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        if (!$stmt) {
            error_log('Prepare failed (swim_practice_tbl): ' . mysqli_error($link));
            $errors[] = 'データベースエラーが発生しました';
        } else {
            mysqli_stmt_bind_param($stmt, 'ssssss', $group_id, $user_id, $practice_date, $title, $menu_text, $memo);
            if (!mysqli_stmt_execute($stmt)) {
                error_log('Execute failed (swim_practice_tbl): ' . mysqli_stmt_error($stmt));
                $errors[] = '保存に失敗しました';
            }
            mysqli_stmt_close($stmt);
        }

        if (empty($errors)) {
            header('Location: swim_practice_create.php?success=1');
            exit;
        }
    }
}

$NAV_BASE = '..';
require_once __DIR__ . '/../../HTML/swim_practice_create.html.php';
