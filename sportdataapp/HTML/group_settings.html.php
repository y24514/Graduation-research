<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>グループ設定 - Sports Data</title>
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/group_settings.css">
    
    <script>
        const showLoader = <?= $showLoader ? 'true' : 'false' ?>;
        const isCreator = <?= $is_creator ? 'true' : 'false' ?>;
    </script>
</head>
<body>
<?php if ($showLoader): ?>
    <div class="loader">
        <div class="spinner"></div>
        <p class="txt">読み込み中...</p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../PHP/header.php'; ?>

<div class="group-settings-container">
    <div class="page-header">
        <a href="chat.php?type=group&chat_group_id=<?= $chat_group_id ?>" class="back-link">← チャットに戻る</a>
        <h1 class="page-title">⚙️ グループ設定</h1>
    </div>
    
    <?php if ($success_message): ?>
    <div class="message-banner success">
        <span class="message-icon">✓</span>
        <?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="message-banner error">
        <span class="message-icon">✗</span>
        <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>
    
    <!-- グループ情報 -->
    <div class="info-section">
        <h2 class="section-title">グループ情報</h2>
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">グループ名</span>
                <span class="info-value"><?= htmlspecialchars($group_info['group_name'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php if ($group_info['group_description']): ?>
            <div class="info-row">
                <span class="info-label">説明</span>
                <span class="info-value"><?= nl2br(htmlspecialchars($group_info['group_description'], ENT_QUOTES, 'UTF-8')) ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">作成日</span>
                <span class="info-value"><?= date('Y年n月j日 H:i', strtotime($group_info['created_at'])) ?></span>
            </div>
        </div>
    </div>
    
    <!-- メンバー一覧 -->
    <div class="members-section">
        <h2 class="section-title">メンバー (<?= count($current_members) ?>人)</h2>
        <div class="members-list">
            <?php foreach ($current_members as $member): ?>
            <div class="member-card">
                <div class="member-info">
                    <div class="member-avatar">
                        <?= mb_substr($member['name'], 0, 1) ?>
                    </div>
                    <div class="member-details">
                        <span class="member-name"><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if ($member['user_id'] === $group_info['created_by']): ?>
                        <span class="member-badge creator">作成者</span>
                        <?php endif; ?>
                        <?php if ($member['user_id'] === $user_id): ?>
                        <span class="member-badge you">あなた</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($is_creator && $member['user_id'] !== $user_id): ?>
                <form method="post" style="display:inline;" onsubmit="return confirm('<?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>さんをグループから削除しますか？')">
                    <input type="hidden" name="remove_user_id" value="<?= htmlspecialchars($member['user_id'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" name="remove_member" class="btn-remove">削除</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- メンバー追加（作成者のみ） -->
    <?php if ($is_creator && !empty($available_members)): ?>
    <div class="add-members-section">
        <h2 class="section-title">メンバーを追加</h2>
        <form method="post" class="add-members-form">
            <div class="member-list">
                <?php foreach ($available_members as $member): ?>
                <label class="member-item">
                    <input type="checkbox" name="members[]" value="<?= htmlspecialchars($member['user_id'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="member-avatar-small">
                        <?= mb_substr($member['name'], 0, 1) ?>
                    </div>
                    <span class="member-name"><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="checkmark">✓</span>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" name="add_members" class="btn btn-primary">
                <span class="btn-icon">➕</span>
                選択したメンバーを追加
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/loading.js"></script>

<script>
// 成功メッセージを3秒後に消す
const successBanner = document.querySelector('.message-banner.success');
if (successBanner) {
    setTimeout(() => {
        successBanner.style.animation = 'slideUp 0.3s ease-out';
        setTimeout(() => {
            successBanner.remove();
        }, 300);
    }, 3000);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-20px); }
    }
`;
document.head.appendChild(style);
</script>

</body>
</html>
