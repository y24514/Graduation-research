<?php
require_once __DIR__ . '/session_bootstrap.php';

require_once __DIR__ . '/user_icon_helper.php';

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$usr = getenv('DB_USER') ?: 'sportsdata_user';
$pwd = getenv('DB_PASS') ?: 'fujidai14';
$host = 'localhost';

$link = mysqli_connect($host, $usr, $pwd);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');
mysqli_select_db($link, 'sportsdata');

$errors = [];
$success = false;
$success_message = '';
$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];

// CSRFトークン（削除など破壊的操作用）
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = (string)$_SESSION['csrf_token'];

// 互換: login_tbl に sport 列がある場合のみ、種目を保存/出し分けに利用
$hasSportColumn = false;
$sportAllowed = ['all', 'swim', 'basketball', 'tennis'];

$user_icon_info = sportdata_find_user_icon($group_id, $user_id);
$user_icon_url = $user_icon_info['url'] ?? null;

// 現在のユーザー情報を取得
$sql = "SELECT * FROM login_tbl WHERE user_id = ? AND group_id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "ss", $user_id, $group_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if(!$user_data){
    header('Location: login.php');
    exit();
}

// アカウント削除（退会）
if (isset($_POST['delete_account'])) {
    $postedToken = (string)($_POST['csrf_token'] ?? '');
    $deletePassword = (string)($_POST['delete_password'] ?? '');

    if (!hash_equals($csrfToken, $postedToken)) {
        http_response_code(400);
        $errors[] = '不正なリクエストです。';
    } elseif (!empty($user_data['is_super_admin'])) {
        $errors[] = 'スーパー管理者アカウントは削除できません。';
    } elseif ($deletePassword === '') {
        $errors[] = '削除の確認のため、現在のパスワードを入力してください。';
    } elseif (!password_verify($deletePassword, (string)($user_data['password'] ?? ''))) {
        $errors[] = 'パスワードが正しくありません。';
    }

    if (empty($errors)) {
        mysqli_begin_transaction($link);
        try {
            // グループ内の最後のユーザーか判定（group_id 外部キー対策）
            $isLastUserInGroup = false;
            $stmt = mysqli_prepare($link, 'SELECT COUNT(*) FROM login_tbl WHERE group_id = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $group_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $groupUserCount);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
                $isLastUserInGroup = ((int)$groupUserCount <= 1);
            }

            // チャット: 履歴は残しつつ、本人の発言は論理削除（内容非表示）
            $stmt = mysqli_prepare($link, "UPDATE chat_tbl SET is_deleted = 1, deleted_at = NOW() WHERE group_id = ? AND user_id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ss', $group_id, $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            // チャット参加/既読状態の整理
            $stmt = mysqli_prepare($link, 'DELETE FROM chat_group_member_tbl WHERE group_id = ? AND user_id = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ss', $group_id, $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            $stmt = mysqli_prepare($link, 'DELETE FROM chat_read_status_tbl WHERE group_id = ? AND user_id = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ss', $group_id, $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            // 個人データ（代表的なもの）
            foreach (['pi_tbl', 'swim_tbl', 'swim_best_tbl', 'swim_practice_tbl', 'goal_tbl', 'diary_tbl', 'calendar_tbl'] as $tbl) {
                $sql = "DELETE FROM {$tbl} WHERE group_id = ? AND user_id = ?";
                $stmt = mysqli_prepare($link, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'ss', $group_id, $user_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }

            // グループ内最後のユーザーなら、group_id参照が残らないようグループ全体の関連データも削除
            if ($isLastUserInGroup) {
                foreach (['calendar_tbl', 'goal_tbl', 'pi_tbl', 'swim_tbl', 'swim_best_tbl'] as $tbl) {
                    $sql = "DELETE FROM {$tbl} WHERE group_id = ?";
                    $stmt = mysqli_prepare($link, $sql);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 's', $group_id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }
                }
            }

            // ユーザー本体
            $stmt = mysqli_prepare($link, 'DELETE FROM login_tbl WHERE group_id = ? AND user_id = ? LIMIT 1');
            if (!$stmt) {
                throw new RuntimeException('ユーザー削除の準備に失敗しました。');
            }
            mysqli_stmt_bind_param($stmt, 'ss', $group_id, $user_id);
            mysqli_stmt_execute($stmt);
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($affected <= 0) {
                throw new RuntimeException('ユーザーの削除に失敗しました（対象が見つからない可能性があります）。');
            }

            mysqli_commit($link);

            // アイコンファイル削除
            sportdata_delete_user_icons($group_id, $user_id);

            // セッション破棄してログインへ
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();

            header('Location: login.php');
            exit();
        } catch (Throwable $e) {
            mysqli_rollback($link);
            $errors[] = '削除に失敗しました。もう一度お試しください。';
            error_log('delete_account failed: ' . $e->getMessage());
        }
    }
}

$colSportRes = mysqli_query(
    $link,
    "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'login_tbl' AND COLUMN_NAME = 'sport' LIMIT 1"
);
if ($colSportRes && mysqli_num_rows($colSportRes) > 0) {
    $hasSportColumn = true;
}
if ($colSportRes) {
    mysqli_free_result($colSportRes);
}

// Ajaxリクエストの判定
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// POSTデータ取得
$name = isset($_POST['name']) ? trim((string)$_POST['name']) : (string)$user_data['name'];
$dob = isset($_POST['dob']) ? trim((string)$_POST['dob']) : (string)$user_data['dob'];
$height = isset($_POST['height']) ? trim((string)$_POST['height']) : (string)$user_data['height'];
$weight = isset($_POST['weight']) ? trim((string)$_POST['weight']) : (string)$user_data['weight'];
$position = isset($_POST['position']) ? trim((string)$_POST['position']) : (string)$user_data['position'];
$sport = $hasSportColumn
    ? (isset($_POST['sport']) ? trim((string)$_POST['sport']) : (string)($user_data['sport'] ?? ''))
    : '';
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$new_password_confirm = $_POST['new_password_confirm'] ?? '';

function sportdata_detect_mime_type(string $tmpPath): string
{
    if ($tmpPath === '' || !is_file($tmpPath)) {
        return '';
    }

    if (class_exists('finfo')) {
        try {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = (string)$finfo->file($tmpPath);
            if ($mime !== '') return $mime;
        } catch (Throwable $e) {
            // fall through
        }
    }

    if (function_exists('mime_content_type')) {
        $mime = (string)@mime_content_type($tmpPath);
        if ($mime !== '') return $mime;
    }

    $imgInfo = @getimagesize($tmpPath);
    if (is_array($imgInfo) && !empty($imgInfo['mime'])) {
        return (string)$imgInfo['mime'];
    }

    return '';
}

function sportdata_handle_user_icon_upload(array &$errors, string $group_id, string $user_id): ?string
{
    if (!isset($_FILES['user_icon']) || $_FILES['user_icon']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES['user_icon']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'アイコン画像のアップロードに失敗しました';
        return null;
    }

    $maxBytes = 2 * 1024 * 1024; // 2MB
    if (!isset($_FILES['user_icon']['size']) || $_FILES['user_icon']['size'] > $maxBytes) {
        $errors[] = 'アイコン画像は2MB以下にしてください';
        return null;
    }

    $tmp = (string)($_FILES['user_icon']['tmp_name'] ?? '');
    $mime = sportdata_detect_mime_type($tmp);

    $allowed = [
        'image/webp' => 'webp',
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
    ];

    if ($mime === '' || !isset($allowed[$mime])) {
        $errors[] = '対応していない画像形式です（PNG/JPG/GIF/WebP）';
        return null;
    }

    $upload_dir = __DIR__ . '/../uploads/user_icons/';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0777, true);
    }

    $safeGroup = sportdata_sanitize_id_for_filename($group_id);
    $safeUser = sportdata_sanitize_id_for_filename($user_id);
    if ($safeGroup === '' || $safeUser === '') {
        $errors[] = 'ユーザーIDが不正です';
        return null;
    }

    $base = $safeGroup . '__' . $safeUser;
    sportdata_delete_user_icons($group_id, $user_id);

    $ext = $allowed[$mime];
    $dest_abs = $upload_dir . $base . '.' . $ext;

    if (!move_uploaded_file($tmp, $dest_abs)) {
        $errors[] = 'アイコン画像の保存に失敗しました';
        return null;
    }

    @chmod($dest_abs, 0644);
    $_SESSION['user_icon_ver'] = (string)time();
    return '../uploads/user_icons/' . rawurlencode($base . '.' . $ext) . '?v=' . $_SESSION['user_icon_ver'];
}

// アイコンだけ更新（ログイン情報の変更なし）
if (isset($_POST['update_icon'])) {
    $newUrl = sportdata_handle_user_icon_upload($errors, $group_id, $user_id);
    if (empty($errors)) {
        $success = true;
        $success_message = 'アイコンを更新しました';
        if (!empty($newUrl)) {
            $user_icon_url = $newUrl;
        }
    }
}

if(isset($_POST['update'])){
    // 未入力(空文字)は「変更なし」として既存値を保持
    if ($name === '') $name = (string)$user_data['name'];
    if ($dob === '') $dob = (string)$user_data['dob'];
    if ($height === '') $height = (string)$user_data['height'];
    if ($weight === '') $weight = (string)$user_data['weight'];
    if ($position === '') $position = (string)$user_data['position'];

    // バリデーション
    if($name === ''){
        $errors[] = '氏名を入力してください';
    }

    if($dob === ''){
        $errors[] = '生年月日を入力してください';
    }

    if($height === '' || !is_numeric($height) || (float)$height <= 0){
        $errors[] = '正しい身長を入力してください';
    }

    if($weight === '' || !is_numeric($weight) || (float)$weight <= 0){
        $errors[] = '正しい体重を入力してください';
    }

    if($position === ''){
        $errors[] = 'ポジション/役職を入力してください';
    }

    if ($hasSportColumn) {
        if ($sport === '') {
            $errors[] = '種目を選択してください';
        } elseif (!in_array($sport, $sportAllowed, true)) {
            $errors[] = '種目の値が不正です';
        }
    }
    
    // パスワード変更がある場合
    $password_update = false;
    if(!empty($new_password) || !empty($current_password)){
        if(empty($current_password)){
            $errors[] = '現在のパスワードを入力してください';
        } elseif(!password_verify($current_password, $user_data['password'])){
            $errors[] = '現在のパスワードが正しくありません';
        } elseif(empty($new_password)){
            $errors[] = '新しいパスワードを入力してください';
        } elseif(strlen($new_password) < 6){
            $errors[] = '新しいパスワードは6文字以上で入力してください';
        } elseif($new_password !== $new_password_confirm){
            $errors[] = '新しいパスワードが一致しません';
        } else {
            $password_update = true;
        }
    }
    
    // エラーがなければ更新処理
    if(empty($errors)){
        // アイコンアップロード（任意）
        $newUrl = sportdata_handle_user_icon_upload($errors, $group_id, $user_id);
        if (!empty($newUrl)) {
            $user_icon_url = $newUrl;
        }

        if($isAjax && !empty($errors)){
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'errors' => $errors
            ]);
            exit();
        }

        if($password_update){
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            if ($hasSportColumn) {
                $update_sql = "UPDATE login_tbl SET name = ?, dob = ?, height = ?, weight = ?, position = ?, sport = ?, password = ? WHERE user_id = ? AND group_id = ?";
                $update_stmt = mysqli_prepare($link, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ssddsssss", $name, $dob, $height, $weight, $position, $sport, $hash, $user_id, $group_id);
            } else {
                $update_sql = "UPDATE login_tbl SET name = ?, dob = ?, height = ?, weight = ?, position = ?, password = ? WHERE user_id = ? AND group_id = ?";
                $update_stmt = mysqli_prepare($link, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ssddssss", $name, $dob, $height, $weight, $position, $hash, $user_id, $group_id);
            }
        } else {
            if ($hasSportColumn) {
                $update_sql = "UPDATE login_tbl SET name = ?, dob = ?, height = ?, weight = ?, position = ?, sport = ? WHERE user_id = ? AND group_id = ?";
                $update_stmt = mysqli_prepare($link, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ssddssss", $name, $dob, $height, $weight, $position, $sport, $user_id, $group_id);
            } else {
                $update_sql = "UPDATE login_tbl SET name = ?, dob = ?, height = ?, weight = ?, position = ? WHERE user_id = ? AND group_id = ?";
                $update_stmt = mysqli_prepare($link, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ssddsss", $name, $dob, $height, $weight, $position, $user_id, $group_id);
            }
        }
        
        if(mysqli_stmt_execute($update_stmt)){
            $success = true;
            $success_message = '登録情報を更新しました';
            
            // セッション情報を更新
            $_SESSION['name'] = $name;
            $_SESSION['dob'] = $dob;
            $_SESSION['height'] = $height;
            $_SESSION['weight'] = $weight;
            $_SESSION['position'] = $position;
            if ($hasSportColumn) {
                $_SESSION['sport'] = $sport;
            }
            
            // データを再取得
            $user_data['name'] = $name;
            $user_data['dob'] = $dob;
            $user_data['height'] = $height;
            $user_data['weight'] = $weight;
            $user_data['position'] = $position;
            if ($hasSportColumn) {
                $user_data['sport'] = $sport;
            }
            
            if($isAjax){
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => $success_message ?: '登録情報を更新しました'
                ]);
                exit();
            }
        } else {
            $errors[] = "更新に失敗しました: " . mysqli_error($link);
        }
        mysqli_stmt_close($update_stmt);
    }
    
    if($isAjax && !empty($errors)){
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit();
    }
}

$NAV_BASE = '.';
$showLoader = false;

// テンプレート用に、最新のURLを再取得（mtimeベース）
if (empty($user_icon_url)) {
    $user_icon_info = sportdata_find_user_icon($group_id, $user_id);
    $user_icon_url = $user_icon_info['url'] ?? null;
}

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/profile_edit.html.php';
