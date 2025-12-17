<?php
session_start();

// ログインしているか確認
if (!isset($_SESSION['user_id'], $_SESSION['group_id'])) {
    header('Location: login.php');
    exit();
}

$showLoader = false;
if (isset($_SESSION['first_login']) && $_SESSION['first_login'] === true) {
    $showLoader = true;
    $_SESSION['first_login'] = false;
}


// DB接続処理
$usr = 'y24514';
$pwd = 'Kr96main0303';
$host = 'localhost';
$dbName = 'sportdata_db';

$link = mysqli_connect($host, $usr, $pwd, $dbName);
if (!$link) {
    die('接続失敗:' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8');

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'];
$corrent_goal = "";
$hasGoalThisMonth = false;
$currentMonth = date('Y-m');

// 今月の目標が既に登録されているかチェック
$stmt_check = mysqli_prepare($link, "
    SELECT COUNT(*) as count 
    FROM goal_tbl 
    WHERE group_id = ? AND user_id = ? 
    AND DATE_FORMAT(created_at, '%Y-%m') = ?
");
mysqli_stmt_bind_param($stmt_check, "sss", $group_id, $user_id, $currentMonth);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
if ($row_check = mysqli_fetch_assoc($result_check)) {
    $hasGoalThisMonth = ($row_check['count'] > 0);
}
mysqli_stmt_close($stmt_check);

// goal表示
$stmt = mysqli_prepare($link, "SELECT goal FROM goal_tbl WHERE group_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $corrent_goal = $row['goal'];
}
mysqli_stmt_close($stmt);

// スケジュール表示
$stmt2 = mysqli_prepare($link, "
    SELECT title, startdate, enddate 
    FROM calendar_tbl 
    WHERE group_id = ? AND user_id = ?
");
mysqli_stmt_bind_param($stmt2, "ss", $group_id, $user_id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_bind_result($stmt2, $title, $startdate, $enddate);

$records = [];
while (mysqli_stmt_fetch($stmt2)) {
    $records[] = [
        'title' => $title,
        'start' => $startdate,
        'end' => $enddate
    ];
}
mysqli_stmt_close($stmt2);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ホームページ</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/site.css">

    <script>
        const eventsFromPHP = <?= json_encode($records, JSON_UNESCAPED_UNICODE); ?>;
        const showLoader = <?= $showLoader ? 'true' : 'false' ?>;
    </script>
</head>
<body>
<?php if ($showLoader): ?>
    <div class="loader">
        <div class="spinner"></div>
        <p class="txt">こんにちは！<?php echo ($_SESSION['name']) ?>さん</p>
    </div>
<?php endif; ?>

<?php $NAV_BASE = '.'; require_once __DIR__ . '/header.php'; ?>

<div class="home">

    
    <!-- ホーム画面 -->
    <div class="home-all">
        <!-- 左側 -->
        <div class="home-left">
            <!--　ユーザー情報 -->
            <div class="user">
                <h2>ユーザー情報</h2>
                <div class="user-border">
                    <div class="photo">
                        <img src="../img/default-avatar.png" width="270px" height="300px">
                    </div>
                    <table class="user-information">
                        <tr><th>氏名</th><td><?php echo ($_SESSION['name']) ?></td></tr>
                        <tr><th>生年月日</th><td><?php echo ($_SESSION['dob']) ?></td></tr>
                        <tr><th>身長</th><td><?php echo ($_SESSION['height']) ?> cm</td></tr>
                        <tr><th>体重</th><td><?php echo ($_SESSION['weight']) ?> kg</td></tr>
                        <tr><th>ポジション</th><td><?php echo ($_SESSION['position']) ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- メッセージ -->
            <div class="messege">
                <h2>お知らせ</h2>
                <div class="messege-area"></div>
            </div>
        </div>

        <!-- 右側 -->
        <div class="home-right">
            <!-- 目標 -->
            <div class="goal">
                <h2>目標</h2>
                <div class="goal-border">
                    <!-- 今月の目標が未登録の場合：入力フォーム表示 -->
                    <form id="goal-form" <?= $hasGoalThisMonth ? 'class="hidden"' : '' ?> action="goalsave.php" method="post">
                        <input type="text" id="goal" name="goal" placeholder="今月の目標を入力" value="<?= htmlspecialchars($corrent_goal, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="submit" id="goal-reg" name="submit" value="登録">
                    </form>
                    
                    <!-- 今月の目標が登録済みの場合:現在の目標表示 -->
                    <div id="goal-display" class="<?= !$hasGoalThisMonth ? 'hidden' : '' ?>">
                        <p class="now-goal"><?= htmlspecialchars($corrent_goal ?: '目標が登録されていません', ENT_QUOTES, 'UTF-8') ?></p>
                        <button type="button" id="edit-goal-btn">変更</button>
                    </div>
                </div>
            </div>
            <div class="calendar">
                <h2>カレンダー</h2>
                <div id="calendar-area" class="calendar-area"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
if (showLoader) {
    $.getScript("../js/loading.js");
}
</script>

<script src="../js/fullcalendar/dist/index.global.min.js"></script>
<script src="../js/fullcalendar/packages/interaction/index.global.min.js"></script>
<script src="../js/fullcalendar/packages/daygrid/index.global.min.js"></script>
<script src="../js/calendar.js"></script>

<script>
// 目標の変更ボタン処理
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('edit-goal-btn');
    const goalForm = document.getElementById('goal-form');
    const goalDisplay = document.getElementById('goal-display');
    
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            goalDisplay.classList.add('hidden');
            goalForm.classList.remove('hidden');
            document.getElementById('goal').focus();
        });
    }
});
</script>

</body>
</html>
