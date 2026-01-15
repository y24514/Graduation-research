<?php
require_once __DIR__ . '/session_bootstrap.php';
$json = file_get_contents('php://input');
$data = json_decode($json, true);
if ($data) {
    // ブラウザのデータをスタッツ（PHPのセッション）に書き込む
    $_SESSION['game'] = $data;
    echo "success";
}