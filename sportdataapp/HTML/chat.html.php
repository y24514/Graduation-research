<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024">
    <title>チャット - Sports Analytics App</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/chat.css">
    
    <script>
        const showLoader = <?= $showLoader ? 'true' : 'false' ?>;
        const currentUserId = '<?= htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8') ?>';
    </script>
</head>
<body>
<?php if ($showLoader): ?>
    <div class="loader">
        <div class="spinner" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <div class="progress-percent">0%</div>
            <div class="progress-label">読み込み中</div>
            <div class="progress-bar-container" aria-hidden="true">
                <div class="progress-bar"></div>
            </div>
        </div>
        <p class="txt">読み込み中...</p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../PHP/header.php'; ?>

<div class="chat-container">
    <div class="chat-header">
        <div class="header-left">
            <a href="chat_list.php" class="back-button" title="戻る">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </a>
            <h1 class="page-title">
                <?php if ($chat_type === 'direct'): ?>
                    <?= htmlspecialchars($recipient_name, ENT_QUOTES, 'UTF-8') ?>
                <?php else: ?>
                    <?= htmlspecialchars($group_name, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </h1>
        </div>
        <div class="chat-info">
            <?php if ($chat_type === 'group'): ?>
            <a href="group_settings.php?chat_group_id=<?= $chat_group_id ?>" class="settings-link" title="グループ設定">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M12 1v6m0 6v6m5.2-13.2l-4.2 4.2m0 6l4.2 4.2M1 12h6m6 0h6M4.8 4.8l4.2 4.2m0 6l-4.2 4.2"></path>
                </svg>
            </a>
            <?php else: ?>
            <span class="user-count">1対1トーク</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- メッセージ表示エリア -->
    <div class="chat-messages" id="chatMessages">
        <?php if (empty($messages)): ?>
        <div class="empty-state">
            <p>まだメッセージがありません。<br>最初のメッセージを送信しましょう！</p>
        </div>
        <?php else: ?>
        <?php foreach ($messages as $msg): ?>
        <div class="message-item <?= ($msg['user_id'] === $user_id) ? 'my-message' : 'other-message' ?>">
            <div class="message-wrapper">
                <?php if ($msg['user_id'] !== $user_id): ?>
                <div class="message-avatar">
                    <?= mb_substr($msg['name'] ?? 'U', 0, 1) ?>
                </div>
                <?php endif; ?>
                
                <div class="message-content">
                    <?php if ($msg['user_id'] !== $user_id): ?>
                    <div class="message-sender">
                        <?= htmlspecialchars($msg['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="message-bubble">
                        <?php if (!empty($msg['image_path'])): ?>
                        <div class="message-image">
                            <img src="<?= htmlspecialchars($msg['image_path'], ENT_QUOTES, 'UTF-8') ?>" 
                                 alt="<?= htmlspecialchars($msg['image_name'] ?? '画像', ENT_QUOTES, 'UTF-8') ?>"
                                 onclick="openImageModal(this.src)">
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($msg['message'])): ?>
                        <?= nl2br(htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')) ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="message-time">
                        <?= date('Y/m/d H:i', strtotime($msg['created_at'])) ?>
                    </div>
                </div>
                
                <?php if ($msg['user_id'] === $user_id): ?>
                <div class="message-avatar my-avatar">
                    <?= mb_substr($userName, 0, 1) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- メッセージ入力フォーム -->
    <div class="chat-input-area">
        <form method="post" class="chat-form" id="chatForm" action="" enctype="multipart/form-data">
            <input type="hidden" name="send_message" value="1">
            <div class="input-wrapper">
                <label for="imageInput" class="btn-attach" title="画像を添付">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                </label>
                <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">
                <div id="imagePreview" class="image-preview"></div>
                <textarea 
                    id="message" 
                    name="message" 
                    placeholder="メッセージを入力... (Shift+Enterで改行、Enterで送信)" 
                    rows="1"
                ></textarea>
                <button type="submit" class="btn-send" id="sendBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 画像モーダル -->
<div id="imageModal" class="image-modal" onclick="closeImageModal()">
    <span class="image-modal-close">&times;</span>
    <img class="image-modal-content" id="modalImage">
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/loading.js"></script>
<script src="../js/chat.js"></script>

</body>
</html>
