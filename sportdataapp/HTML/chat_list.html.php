<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャット - Sports Analytics App</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/chat_unified.css">
</head>
<body>

<?php require_once __DIR__ . '/../PHP/header.php'; ?>

<div class="chat-unified-container">
    <!-- 左サイドバー: チャットリスト -->
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <h1 class="sidebar-title">トーク</h1>
            <button class="create-group-btn" onclick="openCreateGroupModal()" title="新しいグループを作成"></button>
        </div>
        
        <div class="chat-list-scroll">
            <!-- グループチャット一覧 -->
            <?php if (!empty($chat_groups)): ?>
            <div class="section-divider">グループ</div>
            
            <?php foreach ($chat_groups as $group): ?>
            <a href="#" class="chat-item" onclick="loadChat('group', <?= $group['chat_group_id'] ?>); return false;" data-type="group" data-id="<?= $group['chat_group_id'] ?>">
                <div class="chat-avatar">
                    <?= mb_substr($group['group_name'], 0, 1, 'UTF-8') ?>
                </div>
                <div class="chat-info">
                    <div class="chat-name">
                        <?= htmlspecialchars($group['group_name'], ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($group['last_time']): ?>
                        <span class="chat-time"><?= date('m/d H:i', strtotime($group['last_time'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($group['last_message']): ?>
                    <div class="chat-preview"><?= htmlspecialchars($group['last_message'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
                <?php if (isset($group['unread_count']) && $group['unread_count'] > 0): ?>
                <div class="unread-badge"><?= $group['unread_count'] > 99 ? '99+' : $group['unread_count'] ?></div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- 個人チャット一覧 -->
            <?php if (!empty($members)): ?>
            <div class="section-divider">メンバー</div>
            
            <?php foreach ($members as $member): ?>
            <a href="#" class="chat-item" onclick="loadChat('direct', '<?= htmlspecialchars($member['user_id'], ENT_QUOTES, 'UTF-8') ?>'); return false;" data-type="direct" data-id="<?= htmlspecialchars($member['user_id'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="chat-avatar">
                    <?php if (!empty($member['icon_url'])): ?>
                        <img src="<?= htmlspecialchars($member['icon_url'], ENT_QUOTES, 'UTF-8') ?>" alt="ユーザーアイコン">
                    <?php else: ?>
                        <?= mb_substr($member['name'], 0, 1, 'UTF-8') ?>
                    <?php endif; ?>
                </div>
                <div class="chat-info">
                    <div class="chat-name">
                        <?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($member['last_time']): ?>
                        <span class="chat-time"><?= date('m/d H:i', strtotime($member['last_time'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($member['last_message']): ?>
                    <div class="chat-preview"><?= htmlspecialchars($member['last_message'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
                <?php if (isset($member['unread_count']) && $member['unread_count'] > 0): ?>
                <div class="unread-badge"><?= $member['unread_count'] > 99 ? '99+' : $member['unread_count'] ?></div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 右メインエリア: チャット画面 -->
    <div class="chat-main" id="chatMain">
        <div class="empty-chat">
            <div class="empty-chat-icon">💬</div>
            <p>チャットを選択してください</p>
        </div>
    </div>
</div>

<!-- グループ作成モーダル -->
<div id="createGroupModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">グループ作成</h2>
            <button class="modal-close" onclick="closeCreateGroupModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createGroupForm">
                <div class="form-group">
                    <label for="groupName">グループ名 <span style="color: #e53e3e;">*</span></label>
                    <input type="text" id="groupName" name="groupName" required placeholder="例: チームA">
                </div>
                
                <div class="form-group">
                    <label for="groupDescription">説明</label>
                    <textarea id="groupDescription" name="groupDescription" placeholder="グループの説明を入力（任意）"></textarea>
                </div>
                
                <div class="member-selection">
                    <div class="member-selection-title">メンバーを選択</div>
                    <div class="member-list" id="memberList">
                        <?php if (!empty($members)): ?>
                            <?php foreach ($members as $member): ?>
                            <label class="member-checkbox-item">
                                <input type="checkbox" name="members[]" value="<?= htmlspecialchars($member['user_id'], ENT_QUOTES, 'UTF-8') ?>">
                                <div class="member-checkbox-avatar">
                                    <?php if (!empty($member['icon_url'])): ?>
                                        <img src="<?= htmlspecialchars($member['icon_url'], ENT_QUOTES, 'UTF-8') ?>" alt="ユーザーアイコン">
                                    <?php else: ?>
                                        <?= mb_substr($member['name'], 0, 1, 'UTF-8') ?>
                                    <?php endif; ?>
                                </div>
                                <span class="member-checkbox-name"><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--muted); padding: 20px;">選択可能なメンバーがいません</p>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeCreateGroupModal()">キャンセル</button>
            <button type="submit" form="createGroupForm" class="btn btn-primary">作成</button>
        </div>
    </div>
</div>

<!-- 削除確認モーダル -->
<div id="deleteConfirmModal" class="modal-overlay" style="display: none;">
    <div class="modal-content delete-confirm-modal">
        <div class="modal-body">
            <p class="delete-message">このメッセージを削除しますか?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeDeleteConfirmModal()">キャンセル</button>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">削除</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/loading.js"></script>
<script src="../js/chat_list.js"></script>

</body>
</html>
