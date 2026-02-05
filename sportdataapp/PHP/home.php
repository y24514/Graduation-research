<?php
require_once __DIR__ . '/session_bootstrap.php';

// 管理者（先生/顧問）はダッシュボードを使わない
if (!empty($_SESSION['is_admin']) && empty($_SESSION['is_super_admin'])) {
    header('Location: admin.php');
    exit();
}

// ログインしているか確認
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: login.php');
    exit();
}

$showLoader = false;
if (isset($_SESSION['first_login']) && $_SESSION['first_login'] === true) {
    $showLoader = true;
    $_SESSION['first_login'] = false;
}

// DB接続処理
$usr = getenv('DB_USER') ?: 'sportsdata_user';
$pwd = getenv('DB_PASS') ?: 'fujidai14';
$host = 'localhost';
$dbName = getenv('DB_NAME') ?: 'sportsdata';

$link = mysqli_connect($host, $usr, $pwd, $dbName);
if (!$link) {
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');

function sportdata_mysqli_is_disconnect_error(Throwable $e): bool
{
    if ($e instanceof mysqli_sql_exception) {
        $code = (int)$e->getCode();
        if ($code === 2006 || $code === 2013) {
            return true;
        }
    }

    $msg = $e->getMessage();
    return stripos($msg, 'server has gone away') !== false
        || stripos($msg, 'Lost connection to MySQL server') !== false;
}

function sportdata_mysqli_reconnect(string $host, string $usr, string $pwd, string $dbName): mysqli
{
    $newLink = mysqli_connect($host, $usr, $pwd, $dbName);
    if (!$newLink) {
        throw new RuntimeException('DB再接続に失敗しました: ' . mysqli_connect_error());
    }
    mysqli_set_charset($newLink, 'utf8');
    return $newLink;
}

function sportdata_mysqli_ensure_alive(mysqli &$link, string $host, string $usr, string $pwd, string $dbName): void
{
    try {
        if (!mysqli_ping($link)) {
            $link = sportdata_mysqli_reconnect($host, $usr, $pwd, $dbName);
        }
    } catch (Throwable $e) {
        if (sportdata_mysqli_is_disconnect_error($e)) {
            $link = sportdata_mysqli_reconnect($host, $usr, $pwd, $dbName);
            return;
        }
        throw $e;
    }
}

function sportdata_mysqli_prepare_retry(mysqli &$link, string $sql, string $host, string $usr, string $pwd, string $dbName): mysqli_stmt
{
    sportdata_mysqli_ensure_alive($link, $host, $usr, $pwd, $dbName);

    try {
        $stmt = mysqli_prepare($link, $sql);
    } catch (Throwable $e) {
        if (sportdata_mysqli_is_disconnect_error($e)) {
            $link = sportdata_mysqli_reconnect($host, $usr, $pwd, $dbName);
            $stmt = mysqli_prepare($link, $sql);
        } else {
            throw $e;
        }
    }

    if ($stmt === false) {
        $errno = mysqli_errno($link);
        if ($errno === 2006 || $errno === 2013) {
            $link = sportdata_mysqli_reconnect($host, $usr, $pwd, $dbName);
            $stmt = mysqli_prepare($link, $sql);
        }
    }

    if ($stmt === false) {
        throw new RuntimeException('SQLの準備に失敗しました: ' . mysqli_error($link));
    }
    return $stmt;
}

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$corrent_goal = "";
$hasGoalThisMonth = false;
$currentMonth = date('Y-m');

// セッションからユーザー情報を取得
$userName = $_SESSION['name'] ?? '';
$userDob = $_SESSION['dob'] ?? '';
$userHeight = $_SESSION['height'] ?? '';
$userWeight = $_SESSION['weight'] ?? '';
$userPosition = $_SESSION['position'] ?? '';

require_once __DIR__ . '/user_icon_helper.php';
$currentUserIcon = sportdata_find_user_icon($group_id, $user_id);
$currentUserIconUrl = $currentUserIcon['url'] ?? null;

// 今月の範囲（created_at で判定）
$monthStart = date('Y-m-01 00:00:00');
$monthEnd = date('Y-m-01 00:00:00', strtotime('+1 month'));

$dbErrorMessage = null;
$records = [];
$chat_notifications = [];
$senderIconUrls = [];

try {
    // 今月の目標が既に登録されているかチェック
    $stmt_check = sportdata_mysqli_prepare_retry($link, "
        SELECT COUNT(*) as count 
        FROM goal_tbl 
        WHERE group_id = ? AND user_id = ?
          AND created_at >= ? AND created_at < ?
    ", $host, $usr, $pwd, $dbName);
    mysqli_stmt_bind_param($stmt_check, "ssss", $group_id, $user_id, $monthStart, $monthEnd);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    if ($row_check = mysqli_fetch_assoc($result_check)) {
        $hasGoalThisMonth = ($row_check['count'] > 0);
    }
    mysqli_stmt_close($stmt_check);

    // goal表示（今月の最新）
    $stmt = sportdata_mysqli_prepare_retry($link, "
        SELECT goal
        FROM goal_tbl
        WHERE group_id = ? AND user_id = ?
          AND created_at >= ? AND created_at < ?
        ORDER BY created_at DESC
        LIMIT 1
    ", $host, $usr, $pwd, $dbName);
    mysqli_stmt_bind_param($stmt, "ssss", $group_id, $user_id, $monthStart, $monthEnd);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $corrent_goal = $row['goal'];
    }
    mysqli_stmt_close($stmt);

    // スケジュール表示
    // information_schema を参照するクエリは環境によって mysqld クラッシュの引き金になることがあるため避ける。
    // 実際のSQLのprepare可否で is_shared 列の有無を判定する。
    $calendarHasIsShared = false;
    try {
        $probe = mysqli_prepare($link, "SELECT is_shared FROM calendar_tbl LIMIT 0");
        if ($probe !== false) {
            $calendarHasIsShared = true;
            mysqli_stmt_close($probe);
        }
    } catch (Throwable $e) {
        if (sportdata_mysqli_is_disconnect_error($e)) {
            $link = sportdata_mysqli_reconnect($host, $usr, $pwd, $dbName);
        }
        $calendarHasIsShared = false;
    }

    if ($calendarHasIsShared) {
        $stmt2 = sportdata_mysqli_prepare_retry(
            $link,
            'SELECT title, startdate, enddate, is_shared FROM calendar_tbl WHERE group_id = ? AND (user_id = ? OR is_shared = 1)',
            $host,
            $usr,
            $pwd,
            $dbName
        );
        mysqli_stmt_bind_param($stmt2, 'ss', $group_id, $user_id);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        while ($row = mysqli_fetch_assoc($result2)) {
            $records[] = [
                'title' => $row['title'],
                'start' => $row['startdate'],
                'end' => $row['enddate'],
            ];
        }
        mysqli_stmt_close($stmt2);
    } else {
        $stmt2 = sportdata_mysqli_prepare_retry(
            $link,
            'SELECT title, startdate, enddate FROM calendar_tbl WHERE group_id = ? AND user_id = ?',
            $host,
            $usr,
            $pwd,
            $dbName
        );
        mysqli_stmt_bind_param($stmt2, 'ss', $group_id, $user_id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_bind_result($stmt2, $title, $startdate, $enddate);
        while (mysqli_stmt_fetch($stmt2)) {
            $records[] = [
                'title' => $title,
                'start' => $startdate,
                'end' => $enddate,
            ];
        }
        mysqli_stmt_close($stmt2);
    }

    // チャット通知を取得（最新5件の未読メッセージのみ）
    $stmt_chat = sportdata_mysqli_prepare_retry($link, "
        SELECT 
            c.id,
            c.message,
            c.created_at,
            c.chat_type,
            c.chat_group_id,
            c.recipient_id,
            c.user_id as sender_user_id,
            l.name as sender_name,
            g.group_name
        FROM chat_tbl c
        LEFT JOIN login_tbl l ON c.user_id = l.user_id AND c.group_id = l.group_id
        LEFT JOIN chat_group_tbl g ON c.chat_group_id = g.chat_group_id
        WHERE (
            (c.chat_type = 'direct' AND c.recipient_id = ? AND c.group_id = ?)
            OR 
            (c.chat_type = 'group' AND c.chat_group_id IN (
                SELECT chat_group_id FROM chat_group_member_tbl 
                WHERE user_id = ? AND group_id = ?
            ))
        )
        AND c.user_id != ?
        AND c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND c.id > COALESCE(
            (SELECT MAX(last_read_message_id) 
             FROM chat_read_status_tbl 
             WHERE user_id = ? 
             AND group_id = ?
             AND (
                (c.chat_type = 'direct' AND chat_type = 'direct' AND recipient_id = c.user_id)
                OR
                (c.chat_type = 'group' AND chat_type = 'group' AND chat_group_id = c.chat_group_id)
             )
            ), 0)
        ORDER BY c.created_at DESC
        LIMIT 5
    ", $host, $usr, $pwd, $dbName);
    mysqli_stmt_bind_param($stmt_chat, "sssssss", $user_id, $group_id, $user_id, $group_id, $user_id, $user_id, $group_id);
    mysqli_stmt_execute($stmt_chat);
    $result_chat = mysqli_stmt_get_result($stmt_chat);

    while ($row_chat = mysqli_fetch_assoc($result_chat)) {
        $chat_notifications[] = $row_chat;
    }
    mysqli_stmt_close($stmt_chat);

    // 通知一覧の送信者アイコンURL（キャッシュ付き）
    $senderIconCache = [];
    foreach ($chat_notifications as $n) {
        $senderId = (string)($n['sender_user_id'] ?? '');
        if ($senderId === '') {
            continue;
        }
        if (!array_key_exists($senderId, $senderIconCache)) {
            $icon = sportdata_find_user_icon($group_id, $senderId);
            $senderIconCache[$senderId] = $icon['url'] ?? null;
        }
        $senderIconUrls[$senderId] = $senderIconCache[$senderId];
    }
} catch (Throwable $e) {
    error_log('home.php DB error: ' . $e->getMessage());
    $dbErrorMessage = 'DBとの接続が一時的に切れました。ページを再読み込みしてください。';
    $records = [];
    $chat_notifications = [];
    $senderIconUrls = [];
}

$canShareCalendar = !empty($_SESSION['is_admin']) || !empty($_SESSION['is_super_admin']);

$NAV_BASE = '.';

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/home.html.php';

