<?php
// タブごとにセッションを分離するためのブートストラップ。
// 方針: URLクエリ tab_id を元に session_name を切り替え、同一ブラウザ内でもタブ別ログインを可能にする。

$tabId = (string)($_GET['tab_id'] ?? ($_POST['tab_id'] ?? ''));

// セッション基本設定（セキュリティ）
// - use_only_cookies: URL経由でのセッションIDを抑止
// - use_strict_mode: 未初期化セッションIDの受け入れを抑止
// - cookie_httponly: JSからの参照抑止
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');

// SameSite は ini_set できる環境とできない環境があるため、cookie params でも指定する
@ini_set('session.cookie_samesite', 'Lax');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443');

// session_start 前に cookie params を設定
// localhost(http) では secure を false にしないとセッションが保持されない
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

// 不正な値は無効化（cookie名に使うため）
if ($tabId !== '' && !preg_match('/^[A-Za-z0-9_-]{8,64}$/', $tabId)) {
    $tabId = '';
}

// tab_id が無いGETは、リダイレクトで付与して以降の遷移で途切れないようにする
if ($tabId === '' && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET')) {
    try {
        $tabId = bin2hex(random_bytes(8));
    } catch (Throwable $e) {
        $tabId = (string)time();
    }

    $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
    if ($uri === '') {
        $uri = basename((string)($_SERVER['PHP_SELF'] ?? ''));
    }

    $sep = (strpos($uri, '?') !== false) ? '&' : '?';
    $target = $uri . $sep . 'tab_id=' . rawurlencode($tabId);

    if (!headers_sent()) {
        header('Location: ' . $target);
        exit;
    }
    // headers_sent の場合は続行（このケースは通常起きない想定）
}

if ($tabId !== '') {
    session_name('SAASESSID_' . $tabId);
}

session_start();

// CSRFトークン（ログイン/登録などヘッダ無しページでも使えるように共通化）
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        // 互換: random_bytes が使えない環境の最低限
        $_SESSION['csrf_token'] = bin2hex((string)mt_rand() . (string)microtime(true));
    }
}

// 他ファイルでも使えるように公開
$GLOBALS['SPORTDATA_TAB_ID'] = $tabId;
