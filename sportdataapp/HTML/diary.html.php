<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日記 - Sports Data</title>
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/diary.css">
    
    <script>
        const showLoader = <?= $showLoader ? 'true' : 'false' ?>;
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

<div class="diary-container">
    <h1 class="page-title">日記</h1>
    
    <?php if ($success_message): ?>
    <div class="message-banner success">
        <?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="message-banner error">
        <span class="message-icon">✗</span>
        <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>
    
    <!-- 日記入力フォーム -->
    <div class="diary-form-section">
        <h2 class="section-title">新しい日記を書く</h2>
        <form method="post" class="diary-form" id="diaryForm">
            <div class="form-group">
                <label for="diary_date">日付 <span class="required">*</span></label>
                <input type="date" id="diary_date" name="diary_date" required value="<?= date('Y-m-d') ?>">
            </div>
            
            <div class="form-group">
                <label for="title">タイトル</label>
                <input type="text" id="title" name="title" placeholder="今日のタイトル（任意）" maxlength="200">
            </div>
            
            <div class="form-group">
                <label for="content">内容 <span class="required">*</span></label>
                <textarea id="content" name="content" rows="8" placeholder="今日の出来事や感想を書きましょう..." required></textarea>
                <div class="char-count">
                    <span id="charCount">0</span> 文字
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="save_diary" class="btn btn-primary">
                    保存
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    クリア
                </button>
            </div>
        </form>
    </div>
    
    <!-- 日記一覧 -->
    <div class="diary-list-section">
        <h2 class="section-title">過去の日記 (<?= count($diaries) ?>件)</h2>
        
        <?php if (empty($diaries)): ?>
        <div class="empty-state">
            <p>まだ日記がありません。<br>最初の日記を書いてみましょう！</p>
        </div>
        <?php else: ?>
        <div class="diary-list">
            <?php foreach ($diaries as $diary): ?>
            <div class="diary-item">
                <div class="diary-header">
                    <div class="diary-date">
                        <span class="date-icon"></span>
                        <?= date('Y年n月j日 (D)', strtotime($diary['diary_date'])) ?>
                    </div>
                    <div class="diary-actions">
                        <button class="btn-icon-small" onclick="editDiary(<?= $diary['id'] ?>, '<?= htmlspecialchars($diary['diary_date'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($diary['title'], ENT_QUOTES, 'UTF-8') ?>', <?= json_encode($diary['content']) ?>)" title="編集">

                        </button>
                        <form method="post" style="display:inline;" onsubmit="return confirm('本当に削除しますか？')">
                            <input type="hidden" name="diary_id" value="<?= $diary['id'] ?>">
                            <button type="submit" name="delete_diary" class="btn-icon-small delete" title="削除">
                                
                            </button>
                        </form>
                    </div>
                </div>
                
                <?php if (!empty($diary['title'])): ?>
                <h3 class="diary-title"><?= htmlspecialchars($diary['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <?php endif; ?>
                
                <div class="diary-content">
                    <?= nl2br(htmlspecialchars($diary['content'], ENT_QUOTES, 'UTF-8')) ?>
                </div>
                
                <div class="diary-footer">
                    <span class="diary-timestamp">
                        作成: <?= date('Y/m/d H:i', strtotime($diary['created_at'])) ?>
                        <?php if ($diary['updated_at'] !== $diary['created_at']): ?>
                        | 更新: <?= date('Y/m/d H:i', strtotime($diary['updated_at'])) ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/loading.js"></script>
<script src="../js/diary.js"></script>

</body>
</html>
