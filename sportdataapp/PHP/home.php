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

<div class="home">
    <!-- メニュー -->
    <div class="meny">
        <nav class="meny-nav">
            <ul>
                <li><button><a href="home.php">ホーム</a></button></li>
                <li><button><a href="pi.php">身体情報</a></button></li>
                <li><button><a href="">テニス</a></button></li>
                <li><button><a href="swim/swim.php">水泳</a></button></li>
                <li><button><a href="">バスケ</a></button></li>
            </ul>
        </nav>
    </div>
    
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
                    <div class="goal-form">
                        <form action="goalsave.php" method="post">
                            <h3>今月の目標</h3>
                            <input type="text" id="goal" name="goal"><br>
                            <input type="submit" id="goal-reg" name="submit" value="登録">
                        </form>
                    </div>
                    <div class="Viewing-goal">
                        <h3>現在の目標</h3>
                        <div class="now-goal">
                            <p><?php echo htmlspecialchars($corrent_goal) ?></p>
                        </div>
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

</body>
</html>
