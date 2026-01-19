<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者 - Sports Analytics App</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<?php require_once __DIR__ . '/../PHP/header.php'; ?>

<?php if (!empty($isSuperAdmin)): ?>

<div class="admin-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">スーパー管理者</h1>
            <p class="admin-subtitle">問い合わせ対応 / 管理者権限申請の受理</p>
        </div>

        <?php if (!empty($adminActionMessage)): ?>
            <div class="admin-card">
                <div class="admin-card-body">
                    <p class="admin-flash"><?= htmlspecialchars($adminActionMessage, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="admin-grid">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">管理者権限申請（未処理）</h2>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($hasAdminRoleRequestsTable)): ?>
                        <p class="empty">申請テーブルが見つかりません。</p>
                    <?php elseif (empty($pendingAdminRoleRequests)): ?>
                        <p class="empty">未処理の申請はありません。</p>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>申請日時</th>
                                        <th>group</th>
                                        <th>ユーザーID</th>
                                        <th>氏名</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingAdminRoleRequests as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['requested_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($r['group_id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($r['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($r['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <form method="post" style="display:inline-block;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="admin_action" value="handle_request">
                                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars((string)($r['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="tab_id" value="<?= htmlspecialchars((string)($_GET['tab_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" name="decision" value="approve" class="admin-btn">承認</button>
                                                </form>
                                                <form method="post" style="display:inline-block; margin-left:8px;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="admin_action" value="handle_request">
                                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars((string)($r['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="tab_id" value="<?= htmlspecialchars((string)($_GET['tab_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" name="decision" value="reject" class="admin-btn">却下</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="admin-grid">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">お問い合わせ（最新200件）</h2>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($hasInquiriesTable)): ?>
                        <p class="empty">お問い合わせテーブルが見つかりません。DBに [sportdataapp/db/add_inquiries_tbl.sql] を適用してください。</p>
                    <?php elseif (empty($inquiries)): ?>
                        <p class="empty">お問い合わせはありません。</p>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>送信日時</th>
                                        <th>group</th>
                                        <th>ユーザーID</th>
                                        <th>種別</th>
                                        <th>件名</th>
                                        <th>内容</th>
                                        <th>返信</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inquiries as $q): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($q['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($q['group_id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($q['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <?php
                                                    $cat = (string)($q['category'] ?? 'other');
                                                    $catLabel = $cat === 'bug' ? 'バグ' : ($cat === 'improve' ? '改善' : 'その他');
                                                ?>
                                                <?= htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td><?= htmlspecialchars($q['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><div class="admin-pre"><?= nl2br(htmlspecialchars($q['message'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div></td>
                                            <td>
                                                <?php if (!empty($q['status'])): ?>
                                                    <div class="admin-pre"><?= nl2br(htmlspecialchars($q['response'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div>
                                                    <?php if (!empty($q['responded_at'])): ?>
                                                        <div class="admin-small">返信日時: <?= htmlspecialchars($q['responded_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <form method="post">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="admin_action" value="reply_inquiry">
                                                        <input type="hidden" name="inquiry_id" value="<?= htmlspecialchars((string)($q['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="tab_id" value="<?= htmlspecialchars((string)($_GET['tab_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                        <textarea name="response" class="admin-textarea" rows="4" required></textarea>
                                                        <div style="margin-top:8px;">
                                                            <button type="submit" class="admin-btn">返信する</button>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php exit; ?>
<?php endif; ?>

<div class="admin-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">管理</h1>
            <p class="admin-subtitle">提出された日記の確認 / メンバーのデータ閲覧</p>
        </div>

        <div class="admin-grid admin-main">
        <div class="admin-group">

        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">閲覧するメンバー</h2>
            </div>
            <div class="admin-card-body">
                <?php if (!empty($adminActionMessage)): ?>
                    <p class="admin-flash"><?= htmlspecialchars($adminActionMessage, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <form method="get" class="member-form" id="memberForm">
                    <input type="hidden" name="tab_id" value="<?= htmlspecialchars((string)($_GET['tab_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($isSuperAdmin)): ?>
                        <label class="member-label" for="groupSelect">group</label>
                        <select id="groupSelect" name="group_id" class="member-select" onchange="document.getElementById('memberForm').submit()">
                            <?php foreach ($availableGroups as $g): ?>
                                <option value="<?= htmlspecialchars($g, ENT_QUOTES, 'UTF-8') ?>" <?= ($g === $group_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <label class="member-label" for="userSelect">メンバー</label>
                    <select id="userSelect" name="user_id" class="member-select" onchange="document.getElementById('memberForm').submit()">
                        <?php foreach ($members as $m): ?>
                            <option value="<?= htmlspecialchars($m['user_id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($selectedMember && $m['user_id'] === $selectedMember['user_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8') ?>（<?= htmlspecialchars($m['user_id'], ENT_QUOTES, 'UTF-8') ?>）
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <noscript>
                        <button type="submit" class="btn">表示</button>
                    </noscript>
                </form>

                <?php if ($selectedMember): ?>
                    <div class="member-summary">
                        <div class="member-avatar">
                            <?php if (!empty($selectedMember['icon_url'])): ?>
                                <img src="<?= htmlspecialchars($selectedMember['icon_url'], ENT_QUOTES, 'UTF-8') ?>" alt="ユーザーアイコン">
                            <?php else: ?>
                                <?= mb_substr($selectedMember['name'], 0, 1, 'UTF-8') ?>
                            <?php endif; ?>
                        </div>
                        <div class="member-meta">
                            <div class="member-name"><?= htmlspecialchars($selectedMember['name'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="member-sub">
                                <span>ユーザーID: <?= htmlspecialchars($selectedMember['user_id'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span>権限: <strong><?= !empty($selectedMember['is_admin']) ? '管理者' : '一般' ?></strong></span>
                                <?php if (!empty($selectedMember['position'])): ?>
                                    <span>役職/ポジション: <?= htmlspecialchars($selectedMember['position'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($isSuperAdmin)): ?>
                        <form method="post" class="admin-actions">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="admin_action" value="toggle_admin">
                            <input type="hidden" name="target_group_id" value="<?= htmlspecialchars($group_id ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="target_user_id" value="<?= htmlspecialchars($selectedMember['user_id'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($selectedMember['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" name="make_admin" value="<?= !empty($selectedMember['is_admin']) ? '0' : '1' ?>" class="admin-btn">
                                <?= !empty($selectedMember['is_admin']) ? '管理者権限を解除' : '管理者権限を付与' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="empty">メンバーが見つかりません。</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($isSuperAdmin)): ?>
            <div class="admin-grid">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">お問い合わせ（最新100件）</h2>
                    </div>
                    <div class="admin-card-body">
                        <?php if (empty($hasInquiriesTable)): ?>
                            <p class="empty">お問い合わせテーブルが見つかりません。DBに [sportdataapp/db/add_inquiries_tbl.sql] を適用してください。</p>
                        <?php elseif (empty($inquiries)): ?>
                            <p class="empty">お問い合わせはありません。</p>
                        <?php else: ?>
                            <div class="table-wrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>送信日時</th>
                                            <th>ユーザーID</th>
                                            <th>種別</th>
                                            <th>件名</th>
                                            <th>内容</th>
                                            <th>返信</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inquiries as $q): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($q['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($q['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <?php
                                                        $cat = (string)($q['category'] ?? 'other');
                                                        $catLabel = $cat === 'bug' ? 'バグ' : ($cat === 'improve' ? '改善' : 'その他');
                                                    ?>
                                                    <?= htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8') ?>
                                                </td>
                                                <td><?= htmlspecialchars($q['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><div class="admin-pre"><?= nl2br(htmlspecialchars($q['message'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div></td>
                                                <td>
                                                    <?php if (!empty($q['status'])): ?>
                                                        <div class="admin-pre"><?= nl2br(htmlspecialchars($q['response'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div>
                                                        <?php if (!empty($q['responded_at'])): ?>
                                                            <div class="admin-small">返信日時: <?= htmlspecialchars($q['responded_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <form method="post">
                                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                            <input type="hidden" name="admin_action" value="reply_inquiry">
                                                            <input type="hidden" name="inquiry_id" value="<?= htmlspecialchars((string)($q['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                            <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($selectedMember['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                            <textarea name="response" class="admin-textarea" rows="4" required></textarea>
                                                            <div style="margin-top:8px;">
                                                                <button type="submit" class="admin-btn">返信する</button>
                                                            </div>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($selectedMember): ?>
            <?php if (!empty($isSuperAdmin) && !empty($pendingAdminRoleRequests)): ?>
            <div class="admin-grid">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">管理者権限申請（未処理）</h2>
                    </div>
                    <div class="admin-card-body">
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>申請日時</th>
                                        <th>ユーザーID</th>
                                        <th>氏名</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingAdminRoleRequests as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['requested_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($r['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($r['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <form method="post" style="display:inline-block;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="admin_action" value="handle_request">
                                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars((string)($r['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($selectedMember['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" name="decision" value="approve" class="admin-btn">承認</button>
                                                </form>
                                                <form method="post" style="display:inline-block; margin-left:8px;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="admin_action" value="handle_request">
                                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars((string)($r['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($selectedMember['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" name="decision" value="reject" class="admin-btn">却下</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="admin-grid">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">目標（最新12件）</h2>
                    </div>
                    <div class="admin-card-body">
                        <?php if (!empty($goals)): ?>
                            <ul class="admin-list">
                                <?php foreach ($goals as $g): ?>
                                    <li>
                                        <div class="t"><?= htmlspecialchars($g['goal'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="s"><?= htmlspecialchars($g['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="empty">データがありません。</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <div class="admin-grid">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">日記（提出通知）</h2>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($hasDiarySubmitColumns)): ?>
                        <p class="empty">提出通知機能が利用できません（DBに列がありません）。[sportdataapp/db/add_diary_submit_to_admin.sql] を適用してください。</p>
                    <?php else: ?>
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                            <p class="empty" style="margin:0;">提出数: <?= htmlspecialchars((string)($submittedDiaryCount ?? 0), ENT_QUOTES, 'UTF-8') ?> 件</p>
                            <a class="admin-btn" href="<?= htmlspecialchars('diary.php' . (!empty($_GET['tab_id']) ? ('?tab_id=' . urlencode((string)$_GET['tab_id'])) : ''), ENT_QUOTES, 'UTF-8') ?>">日記タブで確認</a>
                        </div>

                        <?php if (empty($submittedDiaryNotifications)): ?>
                            <p class="empty" style="margin-top:12px;">提出された日記はありません。</p>
                        <?php else: ?>
                            <ul class="admin-list" style="margin-top:12px;">
                                <?php foreach ($submittedDiaryNotifications as $d): ?>
                                    <li>
                                        <div class="t">
                                            <?= htmlspecialchars(($d['user_name'] ?? $d['user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            <?php if (!empty($d['title'])): ?>
                                                ：<?= htmlspecialchars((string)$d['title'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="s">
                                            <?= htmlspecialchars((string)($d['submitted_at'] ?? $d['diary_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($swimBest) || !empty($swimRecent)): ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">水泳データ</h2>
                    </div>
                    <div class="admin-card-body">
                        <?php if (!empty($swimBest)): ?>
                            <h3 class="section-title">ベスト（上位5）</h3>
                            <div class="table-wrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>プール</th>
                                            <th>種目</th>
                                            <th>距離</th>
                                            <th>ベスト</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($swimBest as $b): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($b['pool'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($b['event'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string)$b['distance'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string)$b['best_time'], ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($swimRecent)): ?>
                            <h3 class="section-title" style="margin-top:16px;">最新（10件）</h3>
                            <div class="table-wrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>日付</th>
                                            <th>区分</th>
                                            <th>プール</th>
                                            <th>種目</th>
                                            <th>距離</th>
                                            <th>タイム</th>
                                            <th>体調</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($swimRecent as $r): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($r['swim_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($r['session_type'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($r['pool'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($r['event'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string)$r['distance'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string)$r['total_time'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars((string)$r['condition'], ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($selectedMember): ?>
            <?php
                $hasBasketball = !empty($basketballRecent);
                $hasTennis = !empty($tennisRecent) && (($tennisMode ?? '') !== 'unavailable');
            ?>
            <?php if ($hasBasketball || $hasTennis): ?>
                <div class="admin-grid">
                    <?php if ($hasBasketball): ?>
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h2 class="admin-card-title">バスケ（最新10件）</h2>
                            </div>
                            <div class="admin-card-body">
                                <div class="table-wrap">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>日時</th>
                                                <th>チームA</th>
                                                <th>スコア</th>
                                                <th>チームB</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($basketballRecent as $g): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($g['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($g['team_a_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars((string)($g['score_a'] ?? '') . ' - ' . (string)($g['score_b'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($g['team_b_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($hasTennis): ?>
                        <div class="admin-card">
                            <div class="admin-card-header">
                                <h2 class="admin-card-title">テニス（最新10件）</h2>
                            </div>
                            <div class="admin-card-body">
                                <div class="table-wrap">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>日付</th>
                                                <th>チームA</th>
                                                <th>スコア</th>
                                                <th>チームB</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tennisRecent as $tg): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($tg['match_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars((string)($tg['team_a'] ?? '') . '（' . (string)($tg['player_a1'] ?? '') . ' ' . (string)($tg['player_a2'] ?? '') . '）', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars((string)($tg['games_a'] ?? '') . ' - ' . (string)($tg['games_b'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars((string)($tg['team_b'] ?? '') . '（' . (string)($tg['player_b1'] ?? '') . ' ' . (string)($tg['player_b2'] ?? '') . '）', ENT_QUOTES, 'UTF-8') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        </div>

        <div class="admin-card admin-calendar-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">共有（カレンダー）</h2>
            </div>
            <div class="admin-card-body">
                <p class="empty">管理者からメンバー全員へ予定を共有できます（メンバーのホームのカレンダーに表示されます）。</p>

                <div id="calendar-area" class="calendar-area"></div>

                <form method="post" action="calendarsave.php<?= !empty($_GET['tab_id']) ? ('?tab_id=' . urlencode((string)$_GET['tab_id'])) : '' ?>" class="admin-actions">
                    <input type="hidden" name="is_shared" value="1">
                    <?php
                        $redir = 'admin.php';
                        $params = [];
                        if (!empty($selectedMember['user_id'])) {
                            $params[] = 'user_id=' . urlencode((string)$selectedMember['user_id']);
                        }
                        if (!empty($_GET['tab_id'])) {
                            $params[] = 'tab_id=' . urlencode((string)$_GET['tab_id']);
                        }
                        if (!empty($params)) {
                            $redir .= '?' . implode('&', $params);
                        }
                    ?>
                    <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redir, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="admin-kv">
                        <div class="row">
                            <div class="k">タイトル</div>
                            <div class="v"><input class="admin-input" id="shared-title" type="text" name="title" maxlength="100" required></div>
                        </div>
                        <div class="row">
                            <div class="k">メモ</div>
                            <div class="v"><input class="admin-input" id="shared-memo" type="text" name="memo" maxlength="100"></div>
                        </div>
                        <div class="row">
                            <div class="k">開始日</div>
                            <div class="v"><input class="admin-input" id="shared-startdate" type="date" name="startdate" required></div>
                        </div>
                        <div class="row">
                            <div class="k">終了日</div>
                            <div class="v"><input class="admin-input" id="shared-enddate" type="date" name="enddate" required></div>
                        </div>
                    </div>

                    <div style="margin-top:12px;">
                        <button type="submit" class="admin-btn">共有する</button>
                    </div>
                </form>
            </div>
        </div>

        </div>

    </div>
</div>

<script>
    const eventsFromPHP = <?= json_encode($calendarSharedRecords ?? [], JSON_UNESCAPED_UNICODE); ?>;

    // admin画面では「日付選択→下の共有フォームに反映」だけ行う
    function openEventModal(info) {
        const startEl = document.getElementById('shared-startdate');
        const endEl = document.getElementById('shared-enddate');
        const titleEl = document.getElementById('shared-title');
        const memoEl = document.getElementById('shared-memo');

        if (startEl) startEl.value = info.startStr;
        if (endEl) endEl.value = info.endStr;
        if (titleEl) {
            titleEl.value = '';
            titleEl.focus();
        }
        if (memoEl) memoEl.value = '';
    }
</script>

<script src="../js/fullcalendar/dist/index.global.min.js"></script>
<script src="../js/calendar.js"></script>

</body>
</html>
