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

// 種別（Kick/Pull等）: グループごとに管理
$defaultKinds = ['W-up', 'SKP', 'Pull', 'Kick', 'Swim', 'Drill', 'Main', 'Down'];
$kindOptions = $defaultKinds;

$hasKindTable = false;
$kindTblRes = mysqli_query(
    $link,
    "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'swim_practice_kind_tbl' LIMIT 1"
);
if ($kindTblRes && mysqli_num_rows($kindTblRes) > 0) {
    $hasKindTable = true;
}
if ($kindTblRes) {
    mysqli_free_result($kindTblRes);
}

if ($hasKindTable) {
    // 初回: デフォルト種別をこのgroupにシード（存在していれば無視）
    $seedStmt = mysqli_prepare($link, "INSERT IGNORE INTO swim_practice_kind_tbl (group_id, kind_name, sort_order) VALUES (?, ?, ?)");
    if ($seedStmt) {
        foreach ($defaultKinds as $idx => $k) {
            $order = $idx + 1;
            mysqli_stmt_bind_param($seedStmt, 'ssi', $group_id, $k, $order);
            @mysqli_stmt_execute($seedStmt);
        }
        mysqli_stmt_close($seedStmt);
    }

    $kinds = [];
    $kStmt = mysqli_prepare($link, "SELECT kind_name FROM swim_practice_kind_tbl WHERE group_id = ? ORDER BY sort_order ASC, id ASC");
    if ($kStmt) {
        mysqli_stmt_bind_param($kStmt, 's', $group_id);
        if (mysqli_stmt_execute($kStmt)) {
            $r = mysqli_stmt_get_result($kStmt);
            while ($r && ($row = mysqli_fetch_assoc($r))) {
                $name = (string)($row['kind_name'] ?? '');
                if ($name !== '') {
                    $kinds[] = $name;
                }
            }
        }
        mysqli_stmt_close($kStmt);
    }
    if (!empty($kinds)) {
        $kindOptions = $kinds;
    }
}

$practice_total = 0;
$latest_practice_date = null;
$practices = [];
$practiceEvents = [];

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

    // カレンダー表示用（FullCalendar events）
    foreach ($practices as $p) {
        $id = (int)($p['id'] ?? 0);
        $date = (string)($p['practice_date'] ?? '');
        // YYYY-MM-DD 以外が来てもなるべく安全に
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : '';
        if ($id <= 0 || $date === '') continue;

        $titleText = trim((string)($p['title'] ?? ''));
        if ($titleText === '') $titleText = '（無題）';

        $practiceEvents[] = [
            'id' => (string)$id,
            'title' => $titleText,
            'start' => $date,
            'allDay' => true,
        ];
    }
}

$practice_date = $_POST['practice_date'] ?? date('Y-m-d');
$title = $_POST['title'] ?? '';
$menu_text = $_POST['menu_text'] ?? '';
$menu_json = $_POST['menu_json'] ?? '';
$memo = $_POST['memo'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$hasPracticeTable) {
        $errors[] = '練習メニューテーブルが未作成です。db/add_swim_practice_tbl.sql を sportdata_db にインポートしてください。';
    }

    $practice_date = trim((string)$practice_date);
    $title = trim((string)$title);
    $menu_text = trim((string)$menu_text);
    $menu_json = trim((string)$menu_json);
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
            $tabId = (string)($GLOBALS['SPORTDATA_TAB_ID'] ?? ($_POST['tab_id'] ?? ($_GET['tab_id'] ?? '')));
            $target = 'swim_practice_create.php?success=1';
            if ($tabId !== '') {
                $target .= '&tab_id=' . rawurlencode($tabId);
            }
            header('Location: ' . $target);
            exit;
        }
    }
}

$NAV_BASE = '..';
require_once __DIR__ . '/../../HTML/swim_practice_create.html.php';
