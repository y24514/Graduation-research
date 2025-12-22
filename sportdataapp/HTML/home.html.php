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
        <!-- 目標を上部全体に配置 -->
        <div class="goal">
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
        
        <!-- 左側 -->
        <div class="home-left">
            <!--　ユーザー情報 -->
            <div class="user">
                <div class="user-border">
                    <div class="user-header">
                        <div class="user-avatar-large">
                            <?= mb_substr($userName, 0, 1, 'UTF-8') ?>
                            <div class="user-status-indicator"></div>
                        </div>
                        <div class="user-header-info">
                            <h3 class="user-name"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="user-position"><?= htmlspecialchars($userPosition, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                    <div class="user-stats-grid">
                        <div class="user-stat-item">
                            <div class="stat-icon"></div>
                            <div class="stat-info">
                                <span class="stat-label">生年月日</span>
                                <span class="stat-value"><?= htmlspecialchars($userDob, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>
                        <div class="user-stat-item">
                            <div class="stat-icon"></div>
                            <div class="stat-info">
                                <span class="stat-label">身長</span>
                                <span class="stat-value"><?= htmlspecialchars($userHeight, ENT_QUOTES, 'UTF-8') ?> cm</span>
                            </div>
                        </div>
                        <div class="user-stat-item">
                            <div class="stat-icon"></div>
                            <div class="stat-info">
                                <span class="stat-label">体重</span>
                                <span class="stat-value"><?= htmlspecialchars($userWeight, ENT_QUOTES, 'UTF-8') ?> kg</span>
                            </div>
                        </div>
                    </div>
                    <div class="user-actions">
                        <a href="pi.php" class="user-action-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            体組成記録
                        </a>
                    </div>
                </div>
            </div>

            <!-- メッセージ -->
            <div class="messege">
                <div class="notification-header-section">
                    <h2>お知らせ</h2>
                    <div class="notification-filter">
                        <button class="filter-btn active" data-filter="all">すべて</button>
                        <button class="filter-btn" data-filter="group">グループ</button>
                        <button class="filter-btn" data-filter="direct">DM</button>
                    </div>
                </div>
                <div class="messege-area">
                    <?php if (!empty($chat_notifications)): ?>
                        <?php foreach ($chat_notifications as $index => $notification): ?>
                            <?php 
                                // ダイレクトメッセージの場合は送信者のIDを使用
                                $direct_id = $notification['chat_type'] === 'direct' 
                                    ? $notification['sender_user_id'] 
                                    : '';
                                
                                $chat_url = $notification['chat_type'] === 'group' 
                                    ? 'chat_list.php?type=group&id=' . $notification['chat_group_id']
                                    : 'chat_list.php?type=direct&id=' . urlencode($direct_id);
                            ?>
                            <a href="<?= $chat_url ?>" class="notification-item" data-type="<?= $notification['chat_type'] ?>" style="animation-delay: <?= $index * 0.1 ?>s">
                                <div class="notification-avatar">
                                    <?= mb_substr($notification['sender_name'], 0, 1, 'UTF-8') ?>
                                    <div class="notification-unread-dot"></div>
                                </div>
                                <div class="notification-body">
                                    <div class="notification-header">
                                        <div class="notification-title-group">
                                            <?php if ($notification['chat_type'] === 'group'): ?>
                                                <svg class="notification-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="9" cy="7" r="4"></circle>
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                </svg>
                                            <?php else: ?>
                                                <svg class="notification-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                                </svg>
                                            <?php endif; ?>
                                            <span class="notification-sender"><?= htmlspecialchars($notification['sender_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                        <span class="notification-time"><?= date('m/d H:i', strtotime($notification['created_at'])) ?></span>
                                    </div>
                                    <?php if ($notification['chat_type'] === 'group'): ?>
                                        <div class="notification-group">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                            </svg>
                                            <?= htmlspecialchars($notification['group_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="notification-message">
                                        <?= htmlspecialchars(mb_substr($notification['message'], 0, 60, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>
                                        <?= mb_strlen($notification['message'], 'UTF-8') > 60 ? '...' : '' ?>
                                    </div>
                                </div>
                                <div class="notification-badge <?= $notification['chat_type'] ?>">
                                    <?= $notification['chat_type'] === 'group' ? 'グループ' : 'DM' ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-notifications">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <p>新しいメッセージはありません</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- カレンダーを右側に配置 -->
        <div class="calendar">
            <div class="calendar-header-section">
                <h2>カレンダー</h2>
                <div class="calendar-quick-actions">
                    <button class="calendar-action-btn" onclick="document.querySelector('.fc-today-button').click()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        今日
                    </button>
                </div>
            </div>
            <div id="calendar-area" class="calendar-area"></div>
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
<script src="../js/loading.js"></script>

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
    
    // 通知フィルター機能
    const filterBtns = document.querySelectorAll('.filter-btn');
    const notificationItems = document.querySelectorAll('.notification-item');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // アクティブボタンを切り替え
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            notificationItems.forEach(item => {
                if (filter === 'all') {
                    item.style.display = 'flex';
                } else {
                    if (item.dataset.type === filter) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    });
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
