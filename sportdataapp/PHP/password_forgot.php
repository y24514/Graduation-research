<?php
require_once __DIR__ . '/session_bootstrap.php';

// 既存ログイン実装に合わせたDB接続（mysqli）
$usr = getenv('DB_USER') ?: 'sportsdata_user';
$pwd = getenv('DB_PASS') ?: 'fujidai14';
$host = 'localhost';
$dbName = getenv('DB_NAME') ?: 'sportsdata';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $link = mysqli_connect($host, $usr, $pwd);
    mysqli_set_charset($link, 'utf8');
    mysqli_select_db($link, $dbName);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die('DB接続に失敗しました。');
}

$errors = [];
$success_message = '';

$group_id = trim((string)($_POST['group_id'] ?? ($_GET['group_id'] ?? '')));
$user_id = trim((string)($_POST['user_id'] ?? ($_GET['user_id'] ?? '')));
$name = trim((string)($_POST['name'] ?? ''));
$dob = trim((string)($_POST['dob'] ?? ''));

$step = (string)($_GET['step'] ?? 'verify');
if ($step !== 'verify' && $step !== 'reset') $step = 'verify';

// セッションに保存する本人確認の有効期限（分）
$VERIFY_TTL_SECONDS = 10 * 60;

function sportdata_tab_id(): string {
    $tabId = (string)($_GET['tab_id'] ?? ($_POST['tab_id'] ?? ($GLOBALS['SPORTDATA_TAB_ID'] ?? '')));
    if ($tabId !== '' && !preg_match('/^[A-Za-z0-9_-]{8,64}$/', $tabId)) return '';
    return $tabId;
}

function sportdata_csrf_ok(): bool {
    $posted = (string)($_POST['csrf_token'] ?? '');
    $session = (string)($_SESSION['csrf_token'] ?? '');
    return ($posted !== '' && $session !== '' && hash_equals($session, $posted));
}

function sportdata_fail_delay(): void {
    // 総当たり抑止（軽量）
    usleep(250 * 1000);
}

function sportdata_password_valid(string $pw): bool {
    return strlen($pw) >= 6;
}

// 試行回数制限（セッション単位）
if (!isset($_SESSION['pw_reset_attempts'])) {
    $_SESSION['pw_reset_attempts'] = 0;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!sportdata_csrf_ok()) {
        $errors[] = '不正なリクエストです。ページを再読み込みしてから、もう一度お試しください。';
    } else {
        $action = (string)($_POST['action'] ?? '');

        if ($_SESSION['pw_reset_attempts'] >= 15) {
            $errors[] = '試行回数が多すぎます。時間をおいてからお試しください。';
            sportdata_fail_delay();
        } elseif ($action === 'verify') {
            // 本人確認
            if ($group_id === '' || $user_id === '' || $name === '' || $dob === '') {
                $errors[] = 'すべての項目を入力してください。';
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                $errors[] = '生年月日の形式が正しくありません。';
            } else {
                try {
                    $sql = 'SELECT id FROM login_tbl WHERE group_id = ? AND user_id = ? AND name = ? AND dob = ? LIMIT 1';
                    $stmt = mysqli_prepare($link, $sql);
                    mysqli_stmt_bind_param($stmt, 'ssss', $group_id, $user_id, $name, $dob);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $row = $result ? mysqli_fetch_assoc($result) : null;
                    mysqli_stmt_close($stmt);

                    if ($row) {
                        // 本人確認OK: 10分だけ再設定を許可
                        $_SESSION['pw_reset_verified'] = [
                            'group_id' => $group_id,
                            'user_id' => $user_id,
                            'verified_at' => time(),
                        ];
                        $_SESSION['pw_reset_attempts'] = 0;

                        $tabId = sportdata_tab_id();
                        $suffix = $tabId !== '' ? ('&tab_id=' . rawurlencode($tabId)) : '';
                        header('Location: password_forgot.php?step=reset' . $suffix);
                        exit;
                    }

                    $_SESSION['pw_reset_attempts'] += 1;
                    $errors[] = '入力情報が正しくありません。';
                    sportdata_fail_delay();
                } catch (mysqli_sql_exception $e) {
                    $errors[] = '処理に失敗しました。時間をおいてからお試しください。';
                }
            }
        } elseif ($action === 'reset') {
            // 再設定
            $verified = $_SESSION['pw_reset_verified'] ?? null;
            $vGroup = is_array($verified) ? (string)($verified['group_id'] ?? '') : '';
            $vUser = is_array($verified) ? (string)($verified['user_id'] ?? '') : '';
            $vAt = is_array($verified) ? (int)($verified['verified_at'] ?? 0) : 0;

            if ($vGroup === '' || $vUser === '' || $vAt <= 0 || (time() - $vAt) > $VERIFY_TTL_SECONDS) {
                unset($_SESSION['pw_reset_verified']);
                $errors[] = '本人確認の有効期限が切れました。もう一度やり直してください。';
                $step = 'verify';
            } else {
                $newPassword = (string)($_POST['new_password'] ?? '');
                $newPasswordConfirm = (string)($_POST['new_password_confirm'] ?? '');

                if ($newPassword === '' || $newPasswordConfirm === '') {
                    $errors[] = '新しいパスワードを入力してください。';
                } elseif ($newPassword !== $newPasswordConfirm) {
                    $errors[] = 'パスワード確認が一致しません。';
                } elseif (!sportdata_password_valid($newPassword)) {
                    $errors[] = 'パスワードは6文字以上にしてください。';
                } else {
                    try {
                        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $sql = 'UPDATE login_tbl SET password = ? WHERE group_id = ? AND user_id = ? LIMIT 1';
                        $stmt = mysqli_prepare($link, $sql);
                        mysqli_stmt_bind_param($stmt, 'sss', $hash, $vGroup, $vUser);
                        mysqli_stmt_execute($stmt);
                        $affected = mysqli_stmt_affected_rows($stmt);
                        mysqli_stmt_close($stmt);

                        unset($_SESSION['pw_reset_verified']);

                        if ($affected <= 0) {
                            $errors[] = '再設定に失敗しました。もう一度お試しください。';
                            sportdata_fail_delay();
                        } else {
                            // ログイン画面で成功表示
                            $_SESSION['password_reset_success'] = 1;
                            $tabId = sportdata_tab_id();
                            $suffix = $tabId !== '' ? ('?tab_id=' . rawurlencode($tabId)) : '';
                            header('Location: login.php' . $suffix);
                            exit;
                        }
                    } catch (Throwable $e) {
                        $errors[] = '再設定に失敗しました。';
                    }
                }
            }
        }
    }
}

// 画面表示ステップの決定（GETでも、本人確認済なら reset を許可）
if ($step === 'reset') {
    $verified = $_SESSION['pw_reset_verified'] ?? null;
    $vAt = is_array($verified) ? (int)($verified['verified_at'] ?? 0) : 0;
    if ($vAt <= 0 || (time() - $vAt) > $VERIFY_TTL_SECONDS) {
        unset($_SESSION['pw_reset_verified']);
        $step = 'verify';
    }
}

require_once __DIR__ . '/../HTML/password_forgot.html.php';
