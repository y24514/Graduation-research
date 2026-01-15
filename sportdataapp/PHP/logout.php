<?php
require_once __DIR__ . '/session_bootstrap.php';

// セッションの全データを削除
$_SESSION = [];

// セッションクッキーも削除
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// セッションを破棄
session_destroy();

// ログインページにリダイレクト
$tabId = (string)($_GET['tab_id'] ?? ($GLOBALS['SPORTDATA_TAB_ID'] ?? ''));
if ($tabId !== '' && !preg_match('/^[A-Za-z0-9_-]{8,64}$/', $tabId)) {
    $tabId = '';
}
$target = 'login.php' . ($tabId !== '' ? ('?tab_id=' . rawurlencode($tabId)) : '');
header('Location: ' . $target);
exit();
