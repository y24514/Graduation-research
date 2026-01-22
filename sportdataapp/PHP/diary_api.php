<?php
require_once __DIR__ . '/session_bootstrap.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    echo json_encode(['success' => false, 'message' => '認証エラー']);
    exit;
}

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'sportsdata_user';
$dbPass = getenv('DB_PASS') ?: 'fujidai14';
$dbName = getenv('DB_NAME') ?: 'sportsdata';

$link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$link) {
    echo json_encode(['success' => false, 'message' => 'DB接続エラー']);
    exit;
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

function diary_has_column(mysqli $link, string $columnName): bool {
    $sql = "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'diary_tbl' AND COLUMN_NAME = ? LIMIT 1";
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, 's', $columnName);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $ok = ($res && mysqli_fetch_assoc($res)) ? true : false;
    mysqli_stmt_close($stmt);
    return $ok;
}

$hasSubmitColumn = diary_has_column($link, 'submitted_to_admin');
$hasSubmitAtColumn = diary_has_column($link, 'submitted_at');
$hasFeedbackColumn = diary_has_column($link, 'admin_feedback');
$hasFeedbackAtColumn = diary_has_column($link, 'admin_feedback_at');
$hasFeedbackByColumn = diary_has_column($link, 'admin_feedback_by_user_id');

switch ($action) {
    case 'save':
        $id = $_POST['id'] ?? null;
        $diary_date = $_POST['diary_date'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        
        if (empty($diary_date) || empty($content)) {
            echo json_encode(['success' => false, 'message' => '日付と内容は必須です']);
            exit;
        }
        
        if ($id) {
            // 更新
            $stmt = mysqli_prepare($link, "UPDATE diary_tbl SET diary_date = ?, title = ?, content = ?, tags = ?, updated_at = NOW() WHERE id = ? AND user_id = ? AND group_id = ?");
            mysqli_stmt_bind_param($stmt, "sssssss", $diary_date, $title, $content, $tags, $id, $user_id, $group_id);
        } else {
            // 新規作成
            $stmt = mysqli_prepare($link, "INSERT INTO diary_tbl (group_id, user_id, diary_date, title, content, tags, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            mysqli_stmt_bind_param($stmt, "ssssss", $group_id, $user_id, $diary_date, $title, $content, $tags);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => $id ? '更新しました' : '保存しました']);
        } else {
            echo json_encode(['success' => false, 'message' => 'エラーが発生しました']);
        }
        mysqli_stmt_close($stmt);
        break;
        
    case 'delete':
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'IDが指定されていません']);
            exit;
        }
        
        $stmt = mysqli_prepare($link, "DELETE FROM diary_tbl WHERE id = ? AND user_id = ? AND group_id = ?");
        mysqli_stmt_bind_param($stmt, "sss", $id, $user_id, $group_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => '削除しました']);
        } else {
            echo json_encode(['success' => false, 'message' => '削除に失敗しました']);
        }
        mysqli_stmt_close($stmt);
        break;
        
    case 'get':
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'IDが指定されていません']);
            exit;
        }

        $select = "SELECT id, diary_date, title, content, tags";
        if ($hasSubmitColumn) {
            $select .= ", submitted_to_admin";
        }
        if ($hasSubmitAtColumn) {
            $select .= ", submitted_at";
        }
        if ($hasFeedbackColumn) {
            $select .= ", admin_feedback";
        }
        if ($hasFeedbackAtColumn) {
            $select .= ", admin_feedback_at";
        }
        if ($hasFeedbackByColumn) {
            $select .= ", admin_feedback_by_user_id";
            $select .= ", (SELECT name FROM login_tbl WHERE group_id = diary_tbl.group_id AND user_id = diary_tbl.admin_feedback_by_user_id LIMIT 1) AS admin_feedback_by_user_name";
        }
        $select .= " FROM diary_tbl WHERE id = ? AND user_id = ? AND group_id = ?";
        $stmt = mysqli_prepare($link, $select);
        mysqli_stmt_bind_param($stmt, "sss", $id, $user_id, $group_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => '日記が見つかりません']);
        }
        mysqli_stmt_close($stmt);
        break;

    case 'feedback':
        // 管理者のみ: 提出された日記にフィードバック
        $isAdminUser = !empty($_SESSION['is_admin']) || !empty($_SESSION['is_super_admin']);
        if (!$isAdminUser) {
            echo json_encode(['success' => false, 'message' => '権限がありません']);
            exit;
        }
        if (!$hasFeedbackColumn) {
            echo json_encode([
                'success' => false,
                'message' => 'フィードバック機能が利用できません（DBに列がありません）。db/add_diary_admin_feedback.sql を実行してください。'
            ]);
            exit;
        }
        if (!$hasSubmitColumn) {
            echo json_encode([
                'success' => false,
                'message' => '提出機能が利用できません（DBに列がありません）。db/add_diary_submit_to_admin.sql を実行してください。'
            ]);
            exit;
        }

        $id = $_POST['id'] ?? '';
        $feedback = trim((string)($_POST['feedback'] ?? ''));
        if ($id === '') {
            echo json_encode(['success' => false, 'message' => 'IDが指定されていません']);
            exit;
        }

        // 空文字は「消す」として扱う
        $by = (string)($_SESSION['user_id'] ?? '');

        if ($hasFeedbackAtColumn && $hasFeedbackByColumn) {
            $stmt = mysqli_prepare(
                $link,
                "UPDATE diary_tbl SET admin_feedback = ?, admin_feedback_at = " . ($feedback === '' ? "NULL" : "NOW()") . ", admin_feedback_by_user_id = ? WHERE id = ? AND group_id = ? AND submitted_to_admin = 1"
            );
            mysqli_stmt_bind_param($stmt, 'ssss', $feedback, $by, $id, $group_id);
        } elseif ($hasFeedbackAtColumn) {
            $stmt = mysqli_prepare(
                $link,
                "UPDATE diary_tbl SET admin_feedback = ?, admin_feedback_at = " . ($feedback === '' ? "NULL" : "NOW()") . " WHERE id = ? AND group_id = ? AND submitted_to_admin = 1"
            );
            mysqli_stmt_bind_param($stmt, 'sss', $feedback, $id, $group_id);
        } else {
            $stmt = mysqli_prepare(
                $link,
                "UPDATE diary_tbl SET admin_feedback = ? WHERE id = ? AND group_id = ? AND submitted_to_admin = 1"
            );
            mysqli_stmt_bind_param($stmt, 'sss', $feedback, $id, $group_id);
        }

        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => '更新に失敗しました']);
            exit;
        }

        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) >= 0) {
            echo json_encode(['success' => true, 'message' => 'フィードバックを保存しました']);
        } else {
            echo json_encode(['success' => false, 'message' => '更新に失敗しました']);
        }
        mysqli_stmt_close($stmt);
        break;

    case 'submit':
    case 'unsubmit':
        if (!$hasSubmitColumn) {
            echo json_encode([
                'success' => false,
                'message' => '提出機能が利用できません（DBに列がありません）。db/add_diary_submit_to_admin.sql を実行してください。'
            ]);
            exit;
        }

        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'IDが指定されていません']);
            exit;
        }

        $toSubmit = ($action === 'submit');
        if ($toSubmit) {
            if ($hasSubmitAtColumn) {
                $stmt = mysqli_prepare($link, "UPDATE diary_tbl SET submitted_to_admin = 1, submitted_at = NOW() WHERE id = ? AND user_id = ? AND group_id = ?");
            } else {
                $stmt = mysqli_prepare($link, "UPDATE diary_tbl SET submitted_to_admin = 1 WHERE id = ? AND user_id = ? AND group_id = ?");
            }
        } else {
            if ($hasSubmitAtColumn) {
                $stmt = mysqli_prepare($link, "UPDATE diary_tbl SET submitted_to_admin = 0, submitted_at = NULL WHERE id = ? AND user_id = ? AND group_id = ?");
            } else {
                $stmt = mysqli_prepare($link, "UPDATE diary_tbl SET submitted_to_admin = 0 WHERE id = ? AND user_id = ? AND group_id = ?");
            }
        }

        mysqli_stmt_bind_param($stmt, "sss", $id, $user_id, $group_id);
        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode([
                'success' => true,
                'message' => $toSubmit ? '管理者に提出しました' : '提出を取り消しました',
                'submitted' => $toSubmit ? 1 : 0
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => '更新に失敗しました']);
        }
        mysqli_stmt_close($stmt);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => '不正なアクション']);
        break;
}

mysqli_close($link);
?>
