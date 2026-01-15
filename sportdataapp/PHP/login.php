<?php 
require_once __DIR__ . '/session_bootstrap.php';

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
$success_message = '';
$group_id = '';
$user_id = '';

// 登録成功メッセージの表示
if(isset($_SESSION['registration_success'])){
    $success_message = '登録が完了しました！ログインしてください。';
    unset($_SESSION['registration_success']);
}

// POSTデータ取得
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $group_id = trim($_POST['group_id'] ?? '');
    $user_id = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    if(isset($_POST['send'])){
        // バリデーション
        if(empty($group_id)){
            $errors[] = '団体IDを入力してください';
        }
        
        if(empty($user_id)){
            $errors[] = 'ユーザーIDを入力してください';
        }
        
        if(empty($password)){
            $errors[] = 'パスワードを入力してください';
        }
        
        // エラーがなければログイン処理
        if(empty($errors)){
            $sql = "SELECT * FROM login_tbl WHERE group_id = ? AND user_id = ?";
            $stmt = mysqli_prepare($link, $sql);
            if(!$stmt){
                $errors[] = 'データベースエラーが発生しました';
            } else {
                mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);

                if($row = mysqli_fetch_assoc($result)){
                    if(password_verify($password, $row['password'])){
                        // セッションハイジャック対策
                        session_regenerate_id(true);

                        // セッション変数に保存
                        $_SESSION['group_id'] = $row['group_id'];
                        $_SESSION['user_id'] = $row['user_id'];
                        $_SESSION['name'] = $row['name'];
                        $_SESSION['dob'] = $row['dob'];
                        $_SESSION['height'] = $row['height'];
                        $_SESSION['weight'] = $row['weight'];
                        $_SESSION['position'] = $row['position'];
                        // ユーザーの種目（任意: DBに sport 列がある場合のみ）
                        $_SESSION['sport'] = isset($row['sport']) ? $row['sport'] : null;
                        $_SESSION['is_admin'] = !empty($row['is_admin']) ? 1 : 0;
                        $_SESSION['is_super_admin'] = !empty($row['is_super_admin']) ? 1 : 0;
                        $_SESSION['show_loader'] = true;
                        $_SESSION['first_login'] = true;
                        $_SESSION['login_time'] = time();

                        // ログイン情報を記憶（クッキー）
                        if($remember_me){
                            setcookie('saved_group_id', $group_id, time() + (86400 * 30), '/'); // 30日間
                            setcookie('saved_user_id', $user_id, time() + (86400 * 30), '/');
                        } else {
                            // クッキーを削除
                            setcookie('saved_group_id', '', time() - 3600, '/');
                            setcookie('saved_user_id', '', time() - 3600, '/');
                        }

                        mysqli_stmt_close($stmt);

                        // 管理者はダッシュボードではなくメンバー閲覧（admin.php）へ
                        $tabId = (string)($_GET['tab_id'] ?? ($_POST['tab_id'] ?? ($GLOBALS['SPORTDATA_TAB_ID'] ?? '')));
                        if ($tabId !== '' && !preg_match('/^[A-Za-z0-9_-]{8,64}$/', $tabId)) {
                            $tabId = '';
                        }
                        $suffix = $tabId !== '' ? ('?tab_id=' . rawurlencode($tabId)) : '';
                        if (!empty($_SESSION['is_admin']) || !empty($_SESSION['is_super_admin'])) {
                            header('Location: admin.php' . $suffix);
                        } else {
                            header('Location: home.php' . $suffix);
                        }
                        exit();
                    } else {
                        $errors[] = 'パスワードが正しくありません';
                        // セキュリティのため、少し待機
                        sleep(1);
                    }
                } else {
                    $errors[] = '団体IDまたはユーザーIDが正しくありません';
                    sleep(1);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// クッキーから保存された情報を取得
if(empty($group_id) && isset($_COOKIE['saved_group_id'])){
    $group_id = $_COOKIE['saved_group_id'];
}
if(empty($user_id) && isset($_COOKIE['saved_user_id'])){
    $user_id = $_COOKIE['saved_user_id'];
}

// HTMLテンプレートを読み込み
require_once __DIR__ . '/../HTML/login.html.php';
