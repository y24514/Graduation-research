<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>チーム・選手登録</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/team_player_register.css">
</head>
<body>
<?php
require_once __DIR__ . '/../PHP/header.php';
?>

<div class="tp-container">
    <div class="tp-card">
        <h1 class="tp-title">チーム・選手登録</h1>
        <p class="tp-sub">チームを追加し、各チームに選手（背番号・名前）を登録します。</p>

        <div class="tp-section">
            <h2 class="tp-h2">チーム追加</h2>
            <form id="teamForm" class="tp-form" autocomplete="off">
                <label class="tp-label">
                    チーム名
                    <input id="teamName" class="tp-input" type="text" maxlength="100" required>
                </label>
                <button class="tp-btn" type="submit">追加</button>
            </form>
            <div id="teamError" class="tp-error" aria-live="polite"></div>
        </div>

        <div class="tp-section">
            <h2 class="tp-h2">登録済み</h2>
            <div id="teams" class="tp-teams" aria-live="polite"></div>
        </div>
    </div>
</div>

<script src="../js/team_player_register.js"></script>
</body>
</html>
