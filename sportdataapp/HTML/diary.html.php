<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日記 - Sports Analytics App</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/diary.css">
</head>
<body>

<?php require_once __DIR__ . '/../PHP/header.php'; ?>

<div class="diary-container">
    <!-- ヘッダーセクション -->
    <div class="diary-header-section">
        <div class="diary-title-group">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                <line x1="10" y1="6" x2="16" y2="6"></line>
                <line x1="10" y1="10" x2="16" y2="10"></line>
                <line x1="10" y1="14" x2="16" y2="14"></line>
            </svg>
            <h1 class="page-title">日記</h1>
        </div>
        <button id="new-diary-btn" class="new-diary-btn" onclick="openDiaryModal()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            新しい日記を書く
        </button>
    </div>

    <?php if (!empty($isAdminUser) && !empty($hasDiarySubmitColumns)): ?>
    <section class="submitted-section" aria-label="提出された日記">
        <div class="submitted-header">
            <h2 class="submitted-title">提出された日記</h2>
            <p class="submitted-sub">メンバーが「管理者に提出」した日記が表示されます</p>
        </div>
        <?php if (empty($submittedDiaries)): ?>
            <div class="submitted-empty">提出された日記はまだありません</div>
        <?php else: ?>
            <div class="submitted-grid">
                <?php foreach ($submittedDiaries as $sd): ?>
                    <div class="submitted-card">
                        <div class="submitted-meta">
                            <div class="submitted-user"><?= htmlspecialchars($sd['user_name'] ?? $sd['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="submitted-date"><?= htmlspecialchars(date('Y年m月d日', strtotime($sd['diary_date'])), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <?php if (!empty($sd['title'])): ?>
                        <div class="submitted-titleline"><?= htmlspecialchars($sd['title'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <div class="submitted-content"><?= nl2br(htmlspecialchars($sd['content'], ENT_QUOTES, 'UTF-8')) ?></div>

                        <?php if (!empty($hasDiaryFeedbackColumns)): ?>
                            <div class="submitted-feedback">
                                <div class="submitted-feedback-label">フィードバック</div>
                                <textarea class="submitted-feedback-input" id="feedbackText-<?= (int)$sd['id'] ?>" placeholder="ここにフィードバックを入力..." rows="3"><?= htmlspecialchars((string)($sd['admin_feedback'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                <div class="submitted-feedback-actions">
                                    <button type="button" class="submitted-feedback-btn" id="feedbackBtn-<?= (int)$sd['id'] ?>" onclick="saveDiaryFeedback(<?= (int)$sd['id'] ?>)">保存</button>
                                    <div class="submitted-feedback-meta" id="feedbackMeta-<?= (int)$sd['id'] ?>">
                                        <?php if (!empty($sd['admin_feedback_at'])): ?>
                                            最終更新: <?= htmlspecialchars((string)$sd['admin_feedback_at'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="submitted-feedback-note">フィードバック機能を使うにはDBに [sportdataapp/db/add_diary_admin_feedback.sql] を適用してください。</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
    
    <!-- 検索・フィルター -->
    <div class="diary-filters">
        <div class="search-box">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input type="text" id="diary-search" placeholder="日記を検索...">
        </div>
        <div class="filter-tags" id="filter-tags">
            <!-- タグが動的に追加されます -->
        </div>
    </div>
    
    <!-- メッセージエリア -->
    <div id="message-area" class="message-area"></div>
    
    <!-- 日記リスト -->
    <div class="diary-grid" id="diary-grid">
        <?php if (empty($diaries)): ?>
        <div class="empty-state">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            <h3>まだ日記がありません</h3>
            <p>最初の日記を書いてみましょう！</p>
        </div>
        <?php else: ?>
            <?php $currentMonthKey = null; ?>
            <?php foreach ($diaries as $diary): ?>
            <?php
                $ts = strtotime($diary['diary_date']);
                $monthKey = date('Y-m', $ts);
                $monthLabel = date('Y年n月', $ts);
            ?>

            <?php if ($currentMonthKey !== $monthKey): ?>
                <?php if ($currentMonthKey !== null): ?>
                    </div>
                </section>
                <?php endif; ?>
                <?php $currentMonthKey = $monthKey; ?>
                <section class="diary-month" data-month="<?= htmlspecialchars($monthKey, ENT_QUOTES, 'UTF-8') ?>">
                    <h2 class="diary-month-title"><?= htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="diary-month-grid">
            <?php endif; ?>

            <div class="diary-card" data-id="<?= $diary['id'] ?>" data-tags="<?= htmlspecialchars($diary['tags'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <div class="diary-card-header">
                    <div class="diary-card-date">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <?= date('Y年m月d日', strtotime($diary['diary_date'])) ?>
                        <?php if (!empty($hasDiarySubmitColumns) && !empty($diary['submitted_to_admin'])): ?>
                            <span class="submit-badge">提出済み</span>
                        <?php endif; ?>
                    </div>
                    <div class="diary-card-actions">
                        <?php if (!empty($canSubmitDiaryToAdmin) && !empty($hasDiarySubmitColumns)): ?>
                        <button class="btn-icon" data-submit-btn="1" data-submitted="<?= !empty($diary['submitted_to_admin']) ? '1' : '0' ?>" onclick="toggleSubmitDiary(<?= $diary['id'] ?>)" title="管理者に提出">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 2L11 13"></path>
                                <path d="M22 2L15 22l-4-9-9-4 20-7z"></path>
                            </svg>
                        </button>
                        <?php endif; ?>
                        <button class="btn-icon" onclick="editDiary(<?= $diary['id'] ?>)" title="編集">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="btn-icon btn-delete" onclick="deleteDiary(<?= $diary['id'] ?>)" title="削除">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <?php if (!empty($diary['title'])): ?>
                <h3 class="diary-card-title"><?= htmlspecialchars($diary['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <?php endif; ?>
                <div class="diary-card-content">
                    <?= nl2br(htmlspecialchars(mb_substr($diary['content'], 0, 150, 'UTF-8'), ENT_QUOTES, 'UTF-8')) ?>
                    <?= mb_strlen($diary['content'], 'UTF-8') > 150 ? '...' : '' ?>
                </div>

                <?php if (!empty($hasDiaryFeedbackColumns) && !empty($diary['admin_feedback'])): ?>
                    <div class="diary-admin-feedback">
                        <div class="diary-admin-feedback-label">管理者フィードバック</div>
                        <div class="diary-admin-feedback-body"><?= nl2br(htmlspecialchars((string)$diary['admin_feedback'], ENT_QUOTES, 'UTF-8')) ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($diary['tags'])): ?>
                <div class="diary-card-tags">
                    <?php foreach (explode(',', $diary['tags']) as $tag): ?>
                    <span class="tag"><?= htmlspecialchars(trim($tag), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if ($currentMonthKey !== null): ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- 日記モーダル -->
<div id="diaryModal" class="diary-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">新しい日記</h2>
            <button id="closeDiaryModal" class="close-modal-btn" onclick="closeDiaryModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form id="diaryForm" class="modal-body">
            <input type="hidden" id="diary-id" value="">
            
            <div class="form-group">
                <label for="diaryDate">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    日付
                </label>
                <input type="date" id="diaryDate" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="diaryTitle">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="4" y1="9" x2="20" y2="9"></line>
                        <line x1="4" y1="15" x2="20" y2="15"></line>
                        <line x1="10" y1="3" x2="8" y2="21"></line>
                        <line x1="16" y1="3" x2="14" y2="21"></line>
                    </svg>
                    タイトル
                </label>
                <input type="text" id="diaryTitle" placeholder="今日のタイトル（任意）" maxlength="200">
            </div>
            
            <div class="form-group">
                <label for="diaryContent">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    内容 <span class="required">*</span>
                </label>
                <textarea id="diaryContent" rows="10" placeholder="今日の出来事や感想を書いてみましょう..." required></textarea>
                <div class="char-count">
                    <span id="modal-char-count">0</span> 文字
                </div>
            </div>
            
            <div class="form-group">
                <label for="diaryTags">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    タグ
                </label>
                <input type="text" id="diaryTags" placeholder="タグをカンマ区切りで入力（例：練習, 大会, 記録）">
                <small>タグを付けると後で検索しやすくなります</small>
            </div>
        </form>
        <div class="modal-footer">
            <button id="deleteBtn" type="button" class="delete-btn" onclick="deleteDiary()" style="display: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                削除
            </button>
            <button type="button" class="cancel-btn" onclick="closeDiaryModal()">キャンセル</button>
            <button type="submit" class="save-btn" form="diaryForm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                保存
            </button>
        </div>
    </div>
</div>

<!-- 削除確認モーダル -->
<div id="deleteConfirmModal" class="diary-modal delete-confirm" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h2>削除の確認</h2>
            <button type="button" class="close-modal-btn" onclick="closeDeleteConfirmModal()" aria-label="閉じる">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <p id="deleteConfirmMessage" class="confirm-message">この日記を削除しますか？</p>
            <p class="confirm-sub">この操作は取り消せません。</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" onclick="closeDeleteConfirmModal()">キャンセル</button>
            <button id="deleteConfirmOkBtn" type="button" class="delete-btn" onclick="confirmDeleteDiary()">削除</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/loading.js"></script>
<script src="../js/diary.js"></script>

</body>
</html>
