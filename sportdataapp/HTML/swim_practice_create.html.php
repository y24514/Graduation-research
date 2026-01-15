<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>水泳｜練習作成</title>
    <link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
    <!-- 既存の水泳記録ページのレイアウト/トーンに寄せて再利用 -->
    <link rel="stylesheet" href="../../css/swim_input.css">
    <link rel="stylesheet" href="../../css/swim_practice.css">
    <link rel="stylesheet" href="../../css/site.css">

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

<div class="container">
    <?php if ($showSuccess): ?>
        <div class="success-banner">練習メニューを保存しました</div>
    <?php endif; ?>

    <!-- 練習サマリー -->
    <div class="stats-summary practice-summary">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">作成済み</div>
                <div class="stat-value"><?= (int)$practice_total ?><span class="stat-unit">件</span></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">最新日</div>
                <div class="stat-value"><?= !empty($latest_practice_date) ? htmlspecialchars($latest_practice_date, ENT_QUOTES, 'UTF-8') : '—' ?></div>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-box" style="margin: 16px 0;">
            <div class="error-content">
                <h4>エラーが発生しました</h4>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="content-grid practice-grid">
        <div class="form-panel practice-form-panel">
            <h2 class="panel-title">練習メニュー作成</h2>
            <form method="post" class="practice-form">
                <div class="form-basic practice-form-basic">
                    <label>日付</label>
                    <input type="date" name="practice_date" value="<?= htmlspecialchars($practice_date, ENT_QUOTES, 'UTF-8') ?>" required>

                    <label>タイトル</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" placeholder="例: メイン 100m×10" required>

                    <label>メニュー</label>
                    <textarea name="menu_text" rows="8" placeholder="例:
W-up 200
Kick 50×8 (1:10)
Main 100×10 (1:30)
Down 200"><?= htmlspecialchars($menu_text, ENT_QUOTES, 'UTF-8') ?></textarea>

                    <label>メモ</label>
                    <textarea name="memo" rows="4" placeholder="例: 体調、目標、意識したことなど"><?= htmlspecialchars($memo, ENT_QUOTES, 'UTF-8') ?></textarea>

                    <div class="practice-actions">
                        <button type="submit" class="submit-btn">保存</button>
                        <a href="swim_input.php" class="submit-btn practice-link">記録へ戻る</a>
                    </div>

                    <?php if (empty($hasPracticeTable)): ?>
                        <p style="margin-top: 12px; font-size: 0.9rem; opacity: 0.9;">
                            ※ 初回は DB にテーブル作成が必要です（db/add_swim_practice_tbl.sql）。
                        </p>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- 右カラム: 一覧 -->
        <div class="info-panel practice-list-panel">
            <div class="info-section">
                <h3 class="section-title">作成済み練習（最新20件）</h3>

                <?php if (empty($hasPracticeTable)): ?>
                    <div class="no-data">DBテーブル未作成のため一覧を表示できません</div>
                <?php elseif (empty($practices)): ?>
                    <div class="no-data">まだ練習がありません。左のフォームから作成してください。</div>
                <?php else: ?>
                    <div class="practice-list">
                        <?php foreach ($practices as $p): ?>
                            <?php
                            $pDate = $p['practice_date'] ?? '';
                            $pTitle = $p['title'] ?? '';
                            $pMenu = $p['menu_text'] ?? '';
                            $pMemo = $p['memo'] ?? '';
                            ?>
                            <div class="practice-card">
                                <div class="practice-card__top">
                                    <div class="practice-card__date"><?= htmlspecialchars((string)$pDate, ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="practice-card__title"><?= htmlspecialchars((string)$pTitle, ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                                <?php if (trim((string)$pMenu) !== ''): ?>
                                    <pre class="practice-card__menu"><?= htmlspecialchars((string)$pMenu, ENT_QUOTES, 'UTF-8') ?></pre>
                                <?php endif; ?>
                                <?php if (trim((string)$pMemo) !== ''): ?>
                                    <div class="practice-card__memo">
                                        <span class="practice-card__memo-label">メモ</span>
                                        <div class="practice-card__memo-text"><?= nl2br(htmlspecialchars((string)$pMemo, ENT_QUOTES, 'UTF-8')) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
