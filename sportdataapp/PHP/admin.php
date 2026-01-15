<?php
require_once __DIR__ . '/session_bootstrap.php';

if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['is_admin']) && empty($_SESSION['is_super_admin'])) {
    http_response_code(403);
    echo '権限がありません。';
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
    echo 'データベース接続に失敗しました。';
    exit;
}
mysqli_set_charset($link, 'utf8');

$isSuperAdmin = !empty($_SESSION['is_super_admin']);

// 申請テーブル存在チェック
$hasAdminRoleRequestsTable = false;
try {
    $tblRes = mysqli_query(
        $link,
        "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_role_requests' LIMIT 1"
    );
    if ($tblRes && mysqli_num_rows($tblRes) > 0) {
        $hasAdminRoleRequestsTable = true;
    }
    if ($tblRes) {
        mysqli_free_result($tblRes);
    }
} catch (Throwable $e) {
    $hasAdminRoleRequestsTable = false;
}

// お問い合わせテーブル存在チェック
$hasInquiriesTable = false;
try {
    $tblRes = mysqli_query(
        $link,
        "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inquiries_tbl' LIMIT 1"
    );
    if ($tblRes && mysqli_num_rows($tblRes) > 0) {
        $hasInquiriesTable = true;
    }
    if ($tblRes) {
        mysqli_free_result($tblRes);
    }
} catch (Throwable $e) {
    $hasInquiriesTable = false;
}

// CSRFトークン
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = (string)$_SESSION['csrf_token'];

$group_id = (string)$_SESSION['group_id'];

// スーパー管理者: 管理者権限(is_admin)の付与/解除
$adminActionMessage = '';
if (!empty($_SESSION['calendar_flash'])) {
    $adminActionMessage = (string)$_SESSION['calendar_flash'];
    unset($_SESSION['calendar_flash']);
}
if ($isSuperAdmin && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $postedToken = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, $postedToken)) {
        http_response_code(400);
        $adminActionMessage = '不正なリクエストです。';
    } else {
        $adminAction = (string)($_POST['admin_action'] ?? '');

        if ($adminAction === 'handle_request') {
            if (!$hasAdminRoleRequestsTable) {
                $adminActionMessage = '申請テーブルが見つかりません。';
            } else {
                $requestId = (int)($_POST['request_id'] ?? 0);
                $decision = (string)($_POST['decision'] ?? ''); // approve|reject

                if ($requestId <= 0 || ($decision !== 'approve' && $decision !== 'reject')) {
                    $adminActionMessage = '申請の指定が不正です。';
                } else {
                    // 申請取得（pendingのみ）
                    $reqStmt = mysqli_prepare(
                        $link,
                        "SELECT id, group_id, user_id FROM admin_role_requests WHERE id = ? AND status = 'pending' LIMIT 1"
                    );
                    mysqli_stmt_bind_param($reqStmt, 'i', $requestId);
                    mysqli_stmt_execute($reqStmt);
                    $reqRes = mysqli_stmt_get_result($reqStmt);
                    $reqRow = $reqRes ? mysqli_fetch_assoc($reqRes) : null;
                    mysqli_stmt_close($reqStmt);

                    if (empty($reqRow)) {
                        $adminActionMessage = '申請が見つからないか、既に処理済みです。';
                    } else {
                        $reqGroupId = (string)($reqRow['group_id'] ?? '');
                        $reqUserId = (string)($reqRow['user_id'] ?? '');

                        if ($decision === 'approve') {
                            // 対象ユーザー存在チェック
                            $check = mysqli_prepare($link, 'SELECT 1 FROM login_tbl WHERE group_id = ? AND user_id = ? LIMIT 1');
                            mysqli_stmt_bind_param($check, 'ss', $reqGroupId, $reqUserId);
                            mysqli_stmt_execute($check);
                            $checkRes = mysqli_stmt_get_result($check);
                            $exists = ($checkRes && mysqli_fetch_row($checkRes)) ? true : false;
                            mysqli_stmt_close($check);

                            if (!$exists) {
                                $adminActionMessage = '申請対象ユーザーが見つかりません。';
                            } else {
                                $upd = mysqli_prepare($link, 'UPDATE login_tbl SET is_admin = 1 WHERE group_id = ? AND user_id = ? LIMIT 1');
                                mysqli_stmt_bind_param($upd, 'ss', $reqGroupId, $reqUserId);
                                $okUser = mysqli_stmt_execute($upd);
                                mysqli_stmt_close($upd);

                                if ($okUser) {
                                    $actBy = (string)($_SESSION['user_id'] ?? '');
                                    $reqUpd = mysqli_prepare(
                                        $link,
                                        "UPDATE admin_role_requests SET status = 'approved', actioned_by = ?, actioned_at = NOW() WHERE id = ? LIMIT 1"
                                    );
                                    mysqli_stmt_bind_param($reqUpd, 'si', $actBy, $requestId);
                                    mysqli_stmt_execute($reqUpd);
                                    mysqli_stmt_close($reqUpd);
                                    $adminActionMessage = '申請を承認し、管理者権限を付与しました。';
                                } else {
                                    $adminActionMessage = '管理者権限の付与に失敗しました。';
                                }
                            }
                        } else {
                            $actBy = (string)($_SESSION['user_id'] ?? '');
                            $reqUpd = mysqli_prepare(
                                $link,
                                "UPDATE admin_role_requests SET status = 'rejected', actioned_by = ?, actioned_at = NOW() WHERE id = ? LIMIT 1"
                            );
                            mysqli_stmt_bind_param($reqUpd, 'si', $actBy, $requestId);
                            mysqli_stmt_execute($reqUpd);
                            mysqli_stmt_close($reqUpd);
                            $adminActionMessage = '申請を却下しました。';
                        }
                    }
                }
            }
        } elseif ($adminAction === 'reply_inquiry') {
            if (!$hasInquiriesTable) {
                $adminActionMessage = 'お問い合わせテーブルが見つかりません。';
            } else {
                $inquiryId = (int)($_POST['inquiry_id'] ?? 0);
                $response = trim((string)($_POST['response'] ?? ''));

                if ($inquiryId <= 0) {
                    $adminActionMessage = '問い合わせの指定が不正です。';
                } elseif ($response === '') {
                    $adminActionMessage = '返信内容は必須です。';
                } else {
                    $responderUserId = (string)($_SESSION['user_id'] ?? '');
                    $stmt = mysqli_prepare(
                        $link,
                        'UPDATE inquiries_tbl SET status = 1, response = ?, responded_by_user_id = ?, responded_at = NOW() WHERE id = ? LIMIT 1'
                    );
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 'ssi', $response, $responderUserId, $inquiryId);
                        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                            $adminActionMessage = '問い合わせに返信しました。';
                        } else {
                            $adminActionMessage = '返信に失敗しました（対象が見つからない可能性があります）。';
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        $adminActionMessage = '返信準備に失敗しました。';
                    }
                }
            }
        } else {
            $adminActionMessage = 'この操作は許可されていません。';
        }
    }
}

// スーパー管理者: 管理者権限申請（pending一覧）
$pendingAdminRoleRequests = [];
if ($isSuperAdmin && $hasAdminRoleRequestsTable) {
    $stmt = mysqli_prepare(
        $link,
        "SELECT id, group_id, user_id, name, requested_at FROM admin_role_requests WHERE status = 'pending' ORDER BY requested_at DESC LIMIT 200"
    );
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($res && ($row = mysqli_fetch_assoc($res))) {
        $pendingAdminRoleRequests[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// スーパー管理者: お問い合わせ一覧（全ユーザー・最新200件）
$inquiries = [];
if ($isSuperAdmin && $hasInquiriesTable) {
    $stmt = mysqli_prepare(
        $link,
        'SELECT id, group_id, user_id, category, subject, message, status, response, responded_by_user_id, created_at, responded_at FROM inquiries_tbl ORDER BY created_at DESC LIMIT 200'
    );
    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($res && ($row = mysqli_fetch_assoc($res))) {
            $inquiries[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

// スーパー管理者は「問い合わせ/申請受理」だけを表示（閲覧メンバー等は不要）
if ($isSuperAdmin) {
    mysqli_close($link);
    $NAV_BASE = '.';
    require_once __DIR__ . '/../HTML/admin.html.php';
    exit;
}

require_once __DIR__ . '/user_icon_helper.php';

// メンバー一覧（同じgroup）
$members = [];
$memberIconCache = [];

{
    // 管理者（先生/顧問）の閲覧対象から、管理者ユーザーは除外する
    $stmt = mysqli_prepare(
        $link,
        'SELECT user_id, name, position, is_admin FROM login_tbl WHERE group_id = ? AND COALESCE(is_admin, 0) = 0 AND COALESCE(is_super_admin, 0) = 0 ORDER BY name'
    );
    mysqli_stmt_bind_param($stmt, 's', $group_id);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $memberId = (string)($row['user_id'] ?? '');
    if ($memberId !== '' && !array_key_exists($memberId, $memberIconCache)) {
        $icon = sportdata_find_user_icon($group_id, $memberId);
        $memberIconCache[$memberId] = $icon['url'] ?? null;
    }
    $row['icon_url'] = $memberId !== '' ? ($memberIconCache[$memberId] ?? null) : null;
    $members[] = $row;
}
mysqli_stmt_close($stmt);

$selectedUserId = (string)($_GET['user_id'] ?? '');
$selectedUserId = $selectedUserId !== '' ? $selectedUserId : (string)($_POST['user_id'] ?? '');
if ($selectedUserId === '' && !empty($members)) {
    $selectedUserId = (string)$members[0]['user_id'];
}

// 選択ユーザーが同一groupのメンバーか確認
$selectedMember = null;
foreach ($members as $m) {
    if ((string)$m['user_id'] === $selectedUserId) {
        $selectedMember = $m;
        break;
    }
}
if ($selectedMember === null && !empty($members)) {
    $selectedMember = $members[0];
    $selectedUserId = (string)$selectedMember['user_id'];
}

// group切り替えでメンバーがいない場合
if (empty($members)) {
    $selectedMember = null;
    $selectedUserId = '';
}

// 管理画面に出す日記は「提出された日記（通知）」のみにする
$hasDiarySubmitColumns = false;
$hasDiarySubmitAtColumn = false;
try {
    $chk = mysqli_prepare(
        $link,
        "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'diary_tbl' AND COLUMN_NAME IN ('submitted_to_admin','submitted_at')"
    );
    if ($chk) {
        mysqli_stmt_execute($chk);
        $res = mysqli_stmt_get_result($chk);
        while ($res && ($r = mysqli_fetch_assoc($res))) {
            if (($r['COLUMN_NAME'] ?? '') === 'submitted_to_admin') $hasDiarySubmitColumns = true;
            if (($r['COLUMN_NAME'] ?? '') === 'submitted_at') $hasDiarySubmitAtColumn = true;
        }
        mysqli_stmt_close($chk);
    }
} catch (Throwable $e) {
    $hasDiarySubmitColumns = false;
    $hasDiarySubmitAtColumn = false;
}

$submittedDiaryCount = 0;
$submittedDiaryNotifications = [];
if ($hasDiarySubmitColumns) {
    // 件数
    $cnt = mysqli_prepare($link, 'SELECT COUNT(*) AS cnt FROM diary_tbl WHERE group_id = ? AND submitted_to_admin = 1');
    if ($cnt) {
        mysqli_stmt_bind_param($cnt, 's', $group_id);
        mysqli_stmt_execute($cnt);
        $rs = mysqli_stmt_get_result($cnt);
        if ($rs && ($row = mysqli_fetch_assoc($rs))) {
            $submittedDiaryCount = (int)($row['cnt'] ?? 0);
        }
        mysqli_stmt_close($cnt);
    }

    // 最新（通知用）
    $sel = "SELECT d.id, d.diary_date, d.title, d.content, d.tags, d.user_id, COALESCE(l.name, d.user_id) AS user_name";
    if ($hasDiarySubmitAtColumn) {
        $sel .= ", d.submitted_at";
    }
    $sel .= " FROM diary_tbl d LEFT JOIN login_tbl l ON l.group_id = d.group_id AND l.user_id = d.user_id";
    $sel .= " WHERE d.group_id = ? AND d.submitted_to_admin = 1";
    $sel .= " ORDER BY " . ($hasDiarySubmitAtColumn ? "d.submitted_at DESC" : "d.diary_date DESC") . ", d.id DESC LIMIT 20";

    $st = mysqli_prepare($link, $sel);
    if ($st) {
        mysqli_stmt_bind_param($st, 's', $group_id);
        mysqli_stmt_execute($st);
        $rs = mysqli_stmt_get_result($st);
        while ($rs && ($row = mysqli_fetch_assoc($rs))) {
            $submittedDiaryNotifications[] = $row;
        }
        mysqli_stmt_close($st);
    }
}

$swimRecent = [];
$swimBest = [];
if ($selectedMember !== null) {
    // 最新10件
    $stmt = mysqli_prepare(
        $link,
        'SELECT swim_date, pool, event, distance, total_time, `condition`, meet_name, round, session_type FROM swim_tbl WHERE group_id = ? AND user_id = ? ORDER BY swim_date DESC, created_at DESC LIMIT 10'
    );
    mysqli_stmt_bind_param($stmt, 'ss', $group_id, $selectedUserId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $swimRecent[] = $row;
    }
    mysqli_stmt_close($stmt);

    // ベスト5件（種目別）
    $stmt = mysqli_prepare(
        $link,
        'SELECT pool, event, distance, MIN(total_time) as best_time FROM swim_tbl WHERE group_id = ? AND user_id = ? GROUP BY pool, event, distance ORDER BY best_time ASC LIMIT 5'
    );
    mysqli_stmt_bind_param($stmt, 'ss', $group_id, $selectedUserId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $swimBest[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// 目標（最新12件）
$goals = [];
if ($selectedMember !== null) {
    $stmt = mysqli_prepare(
        $link,
        'SELECT goal, created_at FROM goal_tbl WHERE group_id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 12'
    );
    mysqli_stmt_bind_param($stmt, 'ss', $group_id, $selectedUserId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $goals[] = $row;
    }
    mysqli_stmt_close($stmt);
}



// バスケ（gamesテーブルに group_id があれば group で絞る。無ければ全体の最新10件）
$basketballRecent = [];
$basketballHasGroup = false;
$basketballHasSavedBy = false;
try {
    $res = mysqli_query(
        $link,
        "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'games' AND COLUMN_NAME IN ('group_id','saved_by_user_id')"
    );
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            if ($r['COLUMN_NAME'] === 'group_id') $basketballHasGroup = true;
            if ($r['COLUMN_NAME'] === 'saved_by_user_id') $basketballHasSavedBy = true;
        }
        mysqli_free_result($res);
    }
} catch (Throwable $e) {
    // ignore
}

if ($basketballHasGroup) {
    if ($selectedMember !== null && $basketballHasSavedBy) {
        $stmt = mysqli_prepare(
            $link,
            'SELECT id, team_a_name, team_b_name, score_a, score_b, created_at FROM games WHERE group_id = ? AND saved_by_user_id = ? ORDER BY created_at DESC LIMIT 10'
        );
        mysqli_stmt_bind_param($stmt, 'ss', $group_id, $selectedUserId);
    } else {
        $stmt = mysqli_prepare(
            $link,
            'SELECT id, team_a_name, team_b_name, score_a, score_b, created_at FROM games WHERE group_id = ? ORDER BY created_at DESC LIMIT 10'
        );
        mysqli_stmt_bind_param($stmt, 's', $group_id);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $basketballRecent[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    // 互換: 旧スキーマ
    $result = mysqli_query($link, 'SELECT id, team_a_name, team_b_name, score_a, score_b, created_at FROM games ORDER BY created_at DESC LIMIT 10');
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $basketballRecent[] = $row;
        }
        mysqli_free_result($result);
    }
}

// テニス（tennis_db / games。group_id列があれば絞る。メンバー名が含まれる試合を優先）
$tennisRecent = [];
$tennisMode = 'global';
try {
    $tennisDb = null;
    $tennisDsn = 'mysql:host=localhost;dbname=tennis_db;charset=utf8mb4';
    $tennisDb = new PDO($tennisDsn, 'root', '');
    $tennisDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $hasGroupCol = false;
    $colStmt = $tennisDb->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'tennis_db' AND TABLE_NAME = 'games' AND COLUMN_NAME IN ('group_id')");
    foreach ($colStmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        if (($c['COLUMN_NAME'] ?? '') === 'group_id') $hasGroupCol = true;
    }

    if ($selectedMember !== null) {
        $name = (string)($selectedMember['name'] ?? '');
        if ($name !== '') {
            if ($hasGroupCol) {
                $stmt = $tennisDb->prepare(
                    'SELECT id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, match_date FROM games WHERE group_id = ? AND (player_a1 = ? OR player_a2 = ? OR player_b1 = ? OR player_b2 = ?) ORDER BY match_date DESC LIMIT 10'
                );
                $stmt->execute([$group_id, $name, $name, $name, $name]);
                $tennisMode = 'member';
            } else {
                $stmt = $tennisDb->prepare(
                    'SELECT id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, match_date FROM games WHERE (player_a1 = ? OR player_a2 = ? OR player_b1 = ? OR player_b2 = ?) ORDER BY match_date DESC LIMIT 10'
                );
                $stmt->execute([$name, $name, $name, $name]);
                $tennisMode = 'member';
            }
            $tennisRecent = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    if (empty($tennisRecent)) {
        if ($hasGroupCol) {
            $stmt = $tennisDb->prepare('SELECT id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, match_date FROM games WHERE group_id = ? ORDER BY match_date DESC LIMIT 10');
            $stmt->execute([$group_id]);
            $tennisMode = 'group';
        } else {
            $stmt = $tennisDb->query('SELECT id, team_a, team_b, games_a, games_b, player_a1, player_a2, player_b1, player_b2, match_date FROM games ORDER BY match_date DESC LIMIT 10');
            $tennisMode = 'global';
        }
        $tennisRecent = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    // tennis_db が無い/接続できない場合は空のまま
    $tennisRecent = [];
    $tennisMode = 'unavailable';
}

mysqli_close($link);

$NAV_BASE = '.';
require_once __DIR__ . '/../HTML/admin.html.php';
