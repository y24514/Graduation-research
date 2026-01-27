<?php
// variables: $csrf_token, $flash, $flashType, $tableReady, $myInquiries
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせ | Sports Analytics App</title>
    <link rel="icon" href="../img/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/contact.css">
</head>
<body>
    <?php require_once __DIR__ . '/../PHP/header.php'; ?>

    <main class="contact-main">
        <h1 class="contact-title">お問い合わせ</h1>
        <p class="contact-desc">バグの報告・改善要望などを送信できます。</p>

        <?php if (!$tableReady): ?>
            <div class="contact-alert contact-alert--error">
                DBにお問い合わせテーブルが見つかりません。管理者が [sportdataapp/db/add_inquiries_tbl.sql] を実行する必要があります。
            </div>
        <?php endif; ?>

        <?php if (!empty($flash)): ?>
            <div class="contact-alert <?php echo $flashType === 'success' ? 'contact-alert--success' : 'contact-alert--error'; ?>">
                <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <section class="contact-card">
            <h2 class="contact-subtitle">送信</h2>
            <?php $tabId = (string)($_GET['tab_id'] ?? ''); ?>
            <form method="POST" action="../PHP/contact.php<?= $tabId !== '' ? ('?tab_id=' . urlencode($tabId)) : '' ?>" class="contact-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                <label class="contact-label">
                    種別
                    <select name="category" class="contact-select" <?php echo $tableReady ? '' : 'disabled'; ?>>
                        <option value="bug">バグ報告</option>
                        <option value="improve">改善要望</option>
                        <option value="other">その他</option>
                    </select>
                </label>

                <label class="contact-label">
                    件名（必須）
                    <input type="text" name="subject" class="contact-input" maxlength="120" required <?php echo $tableReady ? '' : 'disabled'; ?>>
                </label>

                <label class="contact-label">
                    内容（必須）
                    <textarea name="message" class="contact-textarea" rows="6" required <?php echo $tableReady ? '' : 'disabled'; ?>></textarea>
                </label>

                <button type="submit" class="contact-submit" <?php echo $tableReady ? '' : 'disabled'; ?>>送信</button>
            </form>
        </section>

        <section class="contact-card">
            <h2 class="contact-subtitle">送信履歴</h2>

            <?php if (!$tableReady): ?>
                <p class="contact-muted">DB未設定のため履歴を表示できません。</p>
            <?php elseif (empty($myInquiries)): ?>
                <p class="contact-muted">まだお問い合わせはありません。</p>
            <?php else: ?>
                <div class="contact-list">
                    <?php foreach ($myInquiries as $inq): ?>
                        <article class="contact-item">
                            <div class="contact-item-head">
                                <div class="contact-item-title">
                                    <?php echo htmlspecialchars($inq['subject'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="contact-item-badges">
                                    <span class="badge badge--cat">
                                        <?php
                                            $cat = $inq['category'];
                                            echo htmlspecialchars($cat === 'bug' ? 'バグ' : ($cat === 'improve' ? '改善' : 'その他'), ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </span>
                                    <?php if ((int)$inq['status'] === 1): ?>
                                        <span class="badge badge--done">返信済み</span>
                                    <?php else: ?>
                                        <span class="badge badge--pending">未返信</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="contact-item-meta">
                                送信日時: <?php echo htmlspecialchars($inq['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>

                            <div class="contact-item-body">
                                <?php echo nl2br(htmlspecialchars($inq['message'], ENT_QUOTES, 'UTF-8')); ?>
                            </div>

                            <?php if ((int)$inq['status'] === 1 && !empty($inq['response'])): ?>
                                <div class="contact-reply">
                                    <div class="contact-reply-head">返信</div>
                                    <div class="contact-reply-body">
                                        <?php echo nl2br(htmlspecialchars($inq['response'], ENT_QUOTES, 'UTF-8')); ?>
                                    </div>
                                    <?php if (!empty($inq['responded_at'])): ?>
                                        <div class="contact-item-meta">返信日時: <?php echo htmlspecialchars($inq['responded_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
