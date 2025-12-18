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
        <p class="txt">こんにちは！<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>さん</p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../PHP/header.php'; ?>

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
                        <tr><th>氏名</th><td><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></td></tr>
                        <tr><th>生年月日</th><td><?= htmlspecialchars($userDob, ENT_QUOTES, 'UTF-8') ?></td></tr>
                        <tr><th>身長</th><td><?= htmlspecialchars($userHeight, ENT_QUOTES, 'UTF-8') ?> cm</td></tr>
                        <tr><th>体重</th><td><?= htmlspecialchars($userWeight, ENT_QUOTES, 'UTF-8') ?> kg</td></tr>
                        <tr><th>ポジション</th><td><?= htmlspecialchars($userPosition, ENT_QUOTES, 'UTF-8') ?></td></tr>
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

<!-- イベント入力モーダル -->
<div id="event-modal" class="event-modal">
    <div class="event-modal-content">
        <div class="event-modal-header">
            <h3>イベント登録</h3>
            <button class="event-modal-close" onclick="closeEventModal()">&times;</button>
        </div>
        <div class="event-modal-body">
            <div class="event-form-group">
                <label for="event-title">イベント名 <span class="required">*</span></label>
                <input type="text" id="event-title" placeholder="例: 水泳大会" required>
            </div>
            <div class="event-form-group">
                <label for="event-memo">メモ</label>
                <textarea id="event-memo" rows="3" placeholder="詳細情報を入力（任意）"></textarea>
            </div>
            <div class="event-form-group">
                <label>期間</label>
                <div class="event-date-range">
                    <span id="event-start-date"></span>
                    <span class="date-separator">〜</span>
                    <span id="event-end-date"></span>
                </div>
            </div>
        </div>
        <div class="event-modal-footer">
            <button class="event-btn event-btn-cancel" onclick="closeEventModal()">キャンセル</button>
            <button class="event-btn event-btn-submit" onclick="submitEvent()">登録</button>
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

// イベントモーダル用グローバル変数
let currentEventInfo = null;

function openEventModal(info) {
    currentEventInfo = info;
    const modal = document.getElementById('event-modal');
    const startDate = new Date(info.startStr);
    const endDate = new Date(info.endStr);
    endDate.setDate(endDate.getDate() - 1); // FullCalendarのendは翌日なので1日引く
    
    document.getElementById('event-start-date').textContent = startDate.toLocaleDateString('ja-JP');
    document.getElementById('event-end-date').textContent = endDate.toLocaleDateString('ja-JP');
    document.getElementById('event-title').value = '';
    document.getElementById('event-memo').value = '';
    
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('show');
        document.getElementById('event-title').focus();
    }, 10);
}

function closeEventModal() {
    const modal = document.getElementById('event-modal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        currentEventInfo = null;
    }, 300);
}

function submitEvent() {
    const title = document.getElementById('event-title').value.trim();
    const memo = document.getElementById('event-memo').value.trim();
    
    if (!title) {
        alert('イベント名を入力してください');
        document.getElementById('event-title').focus();
        return;
    }
    
    if (currentEventInfo) {
        fetch('../PHP/calendarsave.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                title: title,
                memo: memo,
                startdate: currentEventInfo.startStr,
                enddate: currentEventInfo.endStr
            })
        }).then(() => {
            // カレンダーにイベントを追加
            if (window.calendarInstance) {
                window.calendarInstance.addEvent({
                    title: title,
                    start: currentEventInfo.startStr,
                    end: currentEventInfo.endStr
                });
            }
            closeEventModal();
        });
    }
}

// モーダル外クリックで閉じる
document.addEventListener('click', function(e) {
    const modal = document.getElementById('event-modal');
    if (e.target === modal) {
        closeEventModal();
    }
});

// Escキーで閉じる
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEventModal();
    }
});
</script>

</body>
</html>
