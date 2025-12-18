<?php
session_start();

// セッションの全データを削除
$_SESSION = [];

// セッションクッキーも削除
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// セッションを破棄
session_destroy();

// ログインページにリダイレクト
header("Location: login.php");
exit();
