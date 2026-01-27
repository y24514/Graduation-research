<?php
require_once __DIR__ . '/session_bootstrap.php';
$NAV_BASE = '.';
require_once __DIR__ . '/header.php';

$tabId = (string)($GLOBALS['SPORTDATA_TAB_ID'] ?? ($_GET['tab_id'] ?? ($_POST['tab_id'] ?? '')));
if ($tabId !== '' && !preg_match('/^[A-Za-z0-9_-]{8,64}$/', $tabId)) {
    $tabId = '';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>バスケ｜チーム管理</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <meta name="viewport" content="width=1024">
    <style>
        body { font-family: sans-serif; background: #f2f2f2; margin: 0; padding: 0; }
        .wrap { padding: 20px; }
        .card { background: #fff; padding: 22px; border-radius: 14px; max-width: 860px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { margin: 0 0 10px; }
        .desc { color: #666; margin: 0 0 16px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .item { display: block; text-decoration: none; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px; background: #fff; color: #111; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
        .item:hover { transform: translateY(-2px); border-color: rgba(52, 152, 219, 0.6); box-shadow: 0 10px 18px rgba(0,0,0,0.08); }
        .title { font-weight: 800; margin: 0 0 6px; }
        .sub { color: #666; font-size: 0.92em; margin: 0; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 0.8em; font-weight: 800; margin-bottom: 10px; }
        .b1 { background: rgba(52,152,219,0.12); color: #1f6fb2; }
        .b2 { background: rgba(230,126,34,0.14); color: #b45309; }
        .b3 { background: rgba(231,76,60,0.14); color: #b91c1c; }

        @media (max-width: 820px) { .grid { grid-template-columns: 1fr; } }
    </style>
    <link rel="stylesheet" href="../css/basketball.css">
</head>
<body class="basketball-page">
<div class="wrap">
    <div class="card">
        <h2>チーム管理</h2>
        <p class="desc">チームの作成・選手登録・チーム名の変更をここから行えます。</p>

        <div class="grid">
            <a class="item" href="<?= htmlspecialchars(sportdata_add_tab_id('registration.php', $tabId), ENT_QUOTES, 'UTF-8') ?>">
                <span class="badge b1">登録</span>
                <p class="title">チーム/選手登録</p>
                <p class="sub">チームを選んで選手リストを上書き保存</p>
            </a>
            <a class="item" href="<?= htmlspecialchars(sportdata_add_tab_id('register_team.php', $tabId), ENT_QUOTES, 'UTF-8') ?>">
                <span class="badge b2">作成</span>
                <p class="title">新チーム登録</p>
                <p class="sub">チームを追加して選手登録へ</p>
            </a>
            <a class="item" href="<?= htmlspecialchars(sportdata_add_tab_id('team_manage.php', $tabId), ENT_QUOTES, 'UTF-8') ?>">
                <span class="badge b3">編集</span>
                <p class="title">チーム編集</p>
                <p class="sub">チーム名の変更 / 削除</p>
            </a>
        </div>

        <div style="margin-top:16px;">
            <a href="<?= htmlspecialchars(sportdata_add_tab_id('basketball_index.php', $tabId), ENT_QUOTES, 'UTF-8') ?>" style="text-decoration:none; background:#34495e; color:#fff; padding:10px 12px; border-radius:10px; font-weight:bold; display:inline-block;">← 試合設定へ</a>
        </div>
    </div>
</div>
</body>
</html>
