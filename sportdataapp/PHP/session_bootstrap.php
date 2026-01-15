<?php
// タブごとにセッションを分離するためのブートストラップ。
// 方針: URLクエリ tab_id を元に session_name を切り替え、同一ブラウザ内でもタブ別ログインを可能にする。

$tabId = (string)($_GET['tab_id'] ?? ($_POST['tab_id'] ?? ''));

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

// 他ファイルでも使えるように公開
$GLOBALS['SPORTDATA_TAB_ID'] = $tabId;
