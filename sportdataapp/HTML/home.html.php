<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ホームページ</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/site.css">

    <script>
        const eventsFromPHP = <?= json_encode($records, JSON_UNESCAPED_UNICODE); ?>;
        const showLoader = <?= $showLoader ? 'true' : 'false' ?>;
        const canShareCalendar = <?= !empty($canShareCalendar) ? 'true' : 'false' ?>;
    </script>
</head>
<body>
<?php if ($showLoader): ?>
    <div class="loader">
        <div class="spinner">
            <div class="progress-bar-container">
                <div class="progress-bar"></div>
            </div>
            <div class="loading-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <p class="txt">こんにちは！<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>さん</p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../PHP/header.php'; ?>

<div class="home">
    <!-- ホーム画面 -->
    <div class="home-container">
        <!-- 上部セクション -->
        <div class="home-top-row">
            <!-- 目標カード -->
            <div class="goal-card card">
                <div class="goal-card-header">
                    <h3 class="goal-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="6"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                        今月の目標
                    </h3>
                </div>
                <div class="goal-card-body">
                    <!-- 目標表示エリア -->
                    <div id="goal-display-area" class="<?= $hasGoalThisMonth ? '' : 'hidden' ?>">
                        <p class="goal-text-display" id="goal-text-display"><?= htmlspecialchars($corrent_goal ?: '', ENT_QUOTES, 'UTF-8') ?></p>
                        <button type="button" id="edit-goal-btn" class="goal-action-btn btn-edit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            編集
                        </button>
                    </div>
                    
                    <!-- 目標入力エリア -->
                    <div id="goal-input-area" class="<?= $hasGoalThisMonth ? 'hidden' : '' ?>">
                        <textarea id="goal-input" class="goal-textarea" placeholder="今月達成したい目標を入力してください..." rows="3"><?= htmlspecialchars($corrent_goal, ENT_QUOTES, 'UTF-8') ?></textarea>
                        <div class="goal-actions">
                            <button type="button" id="save-goal-btn" class="goal-action-btn btn-save">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                保存
                            </button>
                            <button type="button" id="cancel-goal-btn" class="goal-action-btn btn-cancel <?= $hasGoalThisMonth ? '' : 'hidden' ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                キャンセル
                            </button>
                        </div>
                        <div id="goal-message" class="goal-message"></div>
                    </div>
                </div>
            </div>
            
            <!--　ユーザー情報カード -->
            <div class="user-card card">
                <div class="card-body">
                    <div class="user-profile">
                        <div class="user-avatar-large">
                            <?php if (!empty($currentUserIconUrl)): ?>
                                <img src="<?= htmlspecialchars($currentUserIconUrl, ENT_QUOTES, 'UTF-8') ?>" alt="ユーザーアイコン">
                            <?php else: ?>
                                <?= mb_substr($userName, 0, 1, 'UTF-8') ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-info">
                            <h3 class="user-name"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="user-position"><?= htmlspecialchars($userPosition, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                    <?php if (empty($_SESSION['is_admin'])): ?>
                    <div class="user-stats">
                        <div class="stat-item">
                            <span class="stat-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                    <path d="M8 14h.01"></path>
                                    <path d="M12 14h.01"></path>
                                    <path d="M16 14h.01"></path>
                                    <path d="M8 18h.01"></path>
                                    <path d="M12 18h.01"></path>
                                </svg>
                                生年月日
                            </span>
                            <span class="stat-value"><?= htmlspecialchars($userDob, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 3v18h18"></path>
                                    <path d="M18 17V9"></path>
                                    <path d="M13 17V5"></path>
                                    <path d="M8 17v-3"></path>
                                </svg>
                                身長
                            </span>
                            <span class="stat-value"><?= htmlspecialchars($userHeight, ENT_QUOTES, 'UTF-8') ?> cm</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                    <path d="M2 17l10 5 10-5"></path>
                                    <path d="M2 12l10 5 10-5"></path>
                                </svg>
                                体重
                            </span>
                            <span class="stat-value"><?= htmlspecialchars($userWeight, ENT_QUOTES, 'UTF-8') ?> kg</span>
                        </div>
                    </div>
                    <a href="pi.php" class="user-link-btn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        身体情報
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 下部セクション -->
        <div class="home-bottom-row">
            <!-- お知らせカード -->
            <div class="notification-card card">
                <div class="card-header">
                    <h2 class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        お知らせ
                    </h2>
                    <div class="notification-filter">
                        <button class="filter-btn active" data-filter="all">すべて</button>
                        <button class="filter-btn" data-filter="group">グループ</button>
                        <button class="filter-btn" data-filter="direct">DM</button>
                    </div>
                </div>
                <div class="card-body notification-scroll">
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
                                    <?php
                                        $senderId = (string)($notification['sender_user_id'] ?? '');
                                        $senderIconUrl = $senderId !== '' ? ($senderIconUrls[$senderId] ?? null) : null;
                                    ?>
                                    <?php if (!empty($senderIconUrl)): ?>
                                        <img src="<?= htmlspecialchars($senderIconUrl, ENT_QUOTES, 'UTF-8') ?>" alt="送信者アイコン">
                                    <?php else: ?>
                                        <?= mb_substr($notification['sender_name'], 0, 1, 'UTF-8') ?>
                                    <?php endif; ?>
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
                                    </div>
                                    <?php if ($notification['chat_type'] === 'group'): ?>
                                        <div class="notification-group">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                            </svg>
                                            <?= htmlspecialchars($notification['group_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <div class="notification-group-time"><?= date('m/d H:i', strtotime($notification['created_at'])) ?></div>
                                    <?php else: ?>
                                        <div class="notification-dm-time"><?= date('m/d H:i', strtotime($notification['created_at'])) ?></div>
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
            
            <!-- カレンダーカード -->
            <div class="calendar-card card">
                <div class="card-header">
                    <h2 class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        カレンダー
                    </h2>
                </div>
                <div class="card-body">
                    <div id="calendar-area" class="calendar-area"></div>
                </div>
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

            <?php if (!empty($canShareCalendar)): ?>
            <div class="event-form-group">
                <label>共有</label>
                <label class="event-share-row">
                    <input type="checkbox" id="event-is-shared">
                    <span>グループ全員に共有する</span>
                </label>
            </div>
            <?php endif; ?>
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
<script src="../js/calendar.js"></script>

<script>
// 目標管理のJavaScript処理
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('edit-goal-btn');
    const saveBtn = document.getElementById('save-goal-btn');
    const cancelBtn = document.getElementById('cancel-goal-btn');
    const goalDisplayArea = document.getElementById('goal-display-area');
    const goalInputArea = document.getElementById('goal-input-area');
    const goalInput = document.getElementById('goal-input');
    const goalTextDisplay = document.getElementById('goal-text-display');
    const goalMessage = document.getElementById('goal-message');
    
    // 編集ボタンクリック
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            goalDisplayArea.classList.add('hidden');
            goalInputArea.classList.remove('hidden');
            cancelBtn.classList.remove('hidden');
            goalInput.focus();
            goalMessage.textContent = '';
            goalMessage.className = 'goal-message';
        });
    }
    
    // キャンセルボタンクリック
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            goalInputArea.classList.add('hidden');
            goalDisplayArea.classList.remove('hidden');
            goalMessage.textContent = '';
            goalMessage.className = 'goal-message';
        });
    }
    
    // 保存ボタンクリック
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const goalValue = goalInput.value.trim();
            
            if (!goalValue) {
                showMessage('目標を入力してください', 'error');
                return;
            }
            
            // ボタンを無効化
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg> 保存中...';
            
            // AJAX送信
            fetch('../PHP/goalsave.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'goal=' + encodeURIComponent(goalValue)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 成功時の処理
                    goalTextDisplay.textContent = goalValue;
                    goalInputArea.classList.add('hidden');
                    goalDisplayArea.classList.remove('hidden');
                    showMessage('目標を保存しました！', 'success');
                    
                    // 3秒後にメッセージを消す
                    setTimeout(() => {
                        goalMessage.textContent = '';
                        goalMessage.className = 'goal-message';
                    }, 3000);
                } else {
                    showMessage(data.message || '保存に失敗しました', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('エラーが発生しました', 'error');
            })
            .finally(() => {
                // ボタンを有効化
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> 保存';
            });
        });
    }
    
    // メッセージ表示関数
    function showMessage(text, type) {
        goalMessage.textContent = text;
        goalMessage.className = 'goal-message ' + type;
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

    const sharedEl = document.getElementById('event-is-shared');
    if (sharedEl) sharedEl.checked = false;
    
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
        const sharedEl = document.getElementById('event-is-shared');
        const isShared = (typeof canShareCalendar !== 'undefined' && canShareCalendar && sharedEl && sharedEl.checked) ? '1' : '';

        fetch('../PHP/calendarsave.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(Object.assign({
                title: title,
                memo: memo,
                startdate: currentEventInfo.startStr,
                enddate: currentEventInfo.endStr
            }, isShared ? { is_shared: '1' } : {}))
        })
        .then(async (res) => {
            const text = await res.text();
            if (!res.ok) {
                throw new Error(text || '保存に失敗しました');
            }

            // 共有のDB未更新などはここで止める
            if (text.includes('未更新') || text.includes('エラー') || text.includes('未ログイン')) {
                alert(text);
                return;
            }

            // カレンダーにイベントを追加
            if (window.calendarInstance) {
                window.calendarInstance.addEvent({
                    title: title,
                    start: currentEventInfo.startStr,
                    end: currentEventInfo.endStr
                });
            }
            closeEventModal();
        })
        .catch((err) => {
            alert(err?.message || '保存に失敗しました');
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
