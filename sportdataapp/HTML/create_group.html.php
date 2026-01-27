<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>グループ作成 - Sports Analytics App</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/create_group.css">
    
    <script>
        const showLoader = <?= $showLoader ? 'true' : 'false' ?>;
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

<div class="create-group-container">
    <div class="page-header">
        <a href="chat_list.php" class="back-link">← チャット一覧に戻る</a>
        <h1 class="page-title">👥 新しいグループを作成</h1>
    </div>
    
    <?php if ($error_message): ?>
    <div class="message-banner error">
        <span class="message-icon">✗</span>
        <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>
    
    <form method="post" class="create-group-form">
        <!-- グループ情報 -->
        <div class="form-section">
            <h2 class="section-title">グループ情報</h2>
            
            <div class="form-group">
                <label for="group_name">グループ名 <span class="required">*</span></label>
                <input type="text" id="group_name" name="group_name" required maxlength="100" placeholder="例: 水泳部グループ">
            </div>
            
            <div class="form-group">
                <label for="group_description">説明（任意）</label>
                <textarea id="group_description" name="group_description" rows="3" placeholder="グループの説明を入力..."></textarea>
            </div>
        </div>
        
        <!-- メンバー選択 -->
        <div class="form-section">
            <h2 class="section-title">メンバーを招待</h2>
            <p class="section-description">グループに招待するメンバーを選択してください（後から追加も可能です）</p>
            
            <?php if (empty($available_members)): ?>
            <div class="empty-state">
                <p>招待できるメンバーがいません</p>
            </div>
            <?php else: ?>
            <div class="member-list">
                <?php foreach ($available_members as $member): ?>
                <label class="member-item">
                    <input type="checkbox" name="members[]" value="<?= htmlspecialchars($member['user_id'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="member-avatar">
                        <?= mb_substr($member['name'], 0, 1) ?>
                    </div>
                    <span class="member-name"><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="checkmark">✓</span>
                </label>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- ボタン -->
        <div class="form-actions">
            <a href="chat_list.php" class="btn btn-secondary">キャンセル</a>
            <button type="submit" name="create_group" class="btn btn-primary">
                <span class="btn-icon">✨</span>
                グループを作成
            </button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/loading.js"></script>

</body>
</html>
