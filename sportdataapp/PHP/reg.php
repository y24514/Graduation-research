<?php
session_start();

$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';

$link = mysqli_connect($host, $usr, $pwd);
if(!$link){
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');
mysqli_select_db($link, 'sportdata_db');

$errors = [];
$success = false;

// Ajaxリクエストの判定
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// POSTデータ取得
$group_id = $_POST['group_id'] ?? '';
$user_id = $_POST['user_id'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$name = $_POST['name'] ?? '';
$dob = $_POST['dob'] ?? '';
$height = $_POST['height'] ?? '';
$weight = $_POST['weight'] ?? '';
$position = $_POST['position'] ?? '';

// スーパー管理者への「管理者権限希望（申請）」
$wants_admin = !empty($_POST['wants_admin']) ? 1 : 0;

// セキュリティ: 誰でも管理者になれないようにする
// 管理者フラグを付けて登録できるのは「ログイン中の管理者/スーパー管理者が作成する場合」のみ
$canSetAdmin = !empty($_SESSION['is_admin']) || !empty($_SESSION['is_super_admin']);
$is_admin = ($canSetAdmin && !empty($_POST['is_admin'])) ? 1 : 0;

if(isset($_POST['reg'])){
    // バリデーション
    if(empty($group_id)){
        $errors[] = '団体IDを入力してください';
    }
    
    if(empty($user_id)){
        $errors[] = 'ユーザーIDを入力してください';
    } elseif(strlen($user_id) < 4){
        $errors[] = 'ユーザーIDは4文字以上で入力してください';
    } else {
        // ユーザーID重複チェック
        $check_sql = "SELECT user_id FROM login_tbl WHERE user_id = ?";
        $check_stmt = mysqli_prepare($link, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $user_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        if(mysqli_stmt_num_rows($check_stmt) > 0){
            $errors[] = 'このユーザーIDは既に使用されています';
        }
        mysqli_stmt_close($check_stmt);
    }
    
    if(empty($password)){
        $errors[] = 'パスワードを入力してください';
    } elseif(strlen($password) < 6){
        $errors[] = 'パスワードは6文字以上で入力してください';
    } elseif($password !== $password_confirm){
        $errors[] = 'パスワードが一致しません';
    }
    
    if(empty($name)){
        $errors[] = '氏名を入力してください';
    }

    // 身体情報は「管理者として登録」または「管理者権限希望（申請）」の場合は不要
    // ※ login_tbl の dob/height/weight は NOT NULL のため、未入力時はサーバ側でダミー値を入れる
    if (!$is_admin && !$wants_admin) {
        if(empty($dob)){
            $errors[] = '生年月日を入力してください';
        }
        if(empty($height) || $height <= 0){
            $errors[] = '正しい身長を入力してください';
        }
        if(empty($weight) || $weight <= 0){
            $errors[] = '正しい体重を入力してください';
        }
    } else {
        if (empty($dob)) {
            $dob = '1900-01-01';
        }
        $height = is_numeric($height) ? (float)$height : 0.0;
        $weight = is_numeric($weight) ? (float)$weight : 0.0;
    }
    
    if(empty($position)){
        $errors[] = 'ポジション/役職を入力してください';
    }
    
    // エラーがなければ登録処理
    if(empty($errors)){
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // 互換: is_admin 列があるDBのみ、管理者フラグも保存
        $hasIsAdminColumn = false;
        $colRes = mysqli_query(
            $link,
            "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'login_tbl' AND COLUMN_NAME = 'is_admin' LIMIT 1"
        );
        if ($colRes && mysqli_num_rows($colRes) > 0) {
            $hasIsAdminColumn = true;
        }
        if ($colRes) {
            mysqli_free_result($colRes);
        }

        if ($hasIsAdminColumn) {
            $sql = "INSERT INTO login_tbl (group_id, user_id, password, name, dob, height, weight, position, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        } else {
            $sql = "INSERT INTO login_tbl (group_id, user_id, password, name, dob, height, weight, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        }

        $stmt = mysqli_prepare($link, $sql);
        if(!$stmt){
            $errors[] = "データベースエラー: " . mysqli_error($link);
        } else {
            if ($hasIsAdminColumn) {
                mysqli_stmt_bind_param($stmt, "sssssddsi", $group_id, $user_id, $hash, $name, $dob, $height, $weight, $position, $is_admin);
            } else {
                mysqli_stmt_bind_param($stmt, "sssssdds", $group_id, $user_id, $hash, $name, $dob, $height, $weight, $position);
            }

            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                $_SESSION['registration_success'] = true;
                mysqli_stmt_close($stmt);

                // 申請: wants_admin がONで、かつ直接 is_admin で作っていない場合のみ
                if (!empty($wants_admin) && empty($is_admin)) {
                    $hasReqTable = false;
                    $tblRes = mysqli_query(
                        $link,
                        "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_role_requests' LIMIT 1"
                    );
                    if ($tblRes && mysqli_num_rows($tblRes) > 0) {
                        $hasReqTable = true;
                    }
                    if ($tblRes) {
                        mysqli_free_result($tblRes);
                    }

                    if ($hasReqTable) {
                        // 既に pending があれば重複作成しない
                        $chk = mysqli_prepare($link, "SELECT 1 FROM admin_role_requests WHERE group_id = ? AND user_id = ? AND status = 'pending' LIMIT 1");
                        if ($chk) {
                            mysqli_stmt_bind_param($chk, 'ss', $group_id, $user_id);
                            mysqli_stmt_execute($chk);
                            $chkRes = mysqli_stmt_get_result($chk);
                            $existsPending = ($chkRes && mysqli_fetch_row($chkRes)) ? true : false;
                            mysqli_stmt_close($chk);

                            if (!$existsPending) {
                                $insReq = mysqli_prepare($link, 'INSERT INTO admin_role_requests (group_id, user_id, name, status) VALUES (?, ?, ?, \'pending\')');
                                if ($insReq) {
                                    mysqli_stmt_bind_param($insReq, 'sss', $group_id, $user_id, $name);
                                    mysqli_stmt_execute($insReq);
                                    mysqli_stmt_close($insReq);
                                }
                            }
                        }
                    }
                }
                
                // Ajaxリクエストの場合はJSONを返す
                if($isAjax){
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => (!empty($wants_admin) && empty($is_admin)) ? '登録が完了しました！（管理者権限を申請しました）' : '登録が完了しました！',
                        'redirect' => 'login.php'
                    ]);
                    exit();
                } else {
                    header('Location: login.php');
                    exit();
                }
            } else {
                $errors[] = "登録に失敗しました: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Ajaxリクエストでエラーがある場合
    if($isAjax && !empty($errors)){
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit();
    }
}


// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/reg.html.php';
