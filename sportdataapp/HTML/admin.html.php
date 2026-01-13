<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者 - Sports Data</title>
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<?php require_once __DIR__ . '/../PHP/header.php'; ?>

<div class="admin-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">管理者</h1>
            <p class="admin-subtitle">同じ団体（group）のメンバー日記・データ閲覧</p>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">閲覧するメンバー</h2>
            </div>
            <div class="admin-card-body">
                <?php if (!empty($adminActionMessage)): ?>
                    <p class="admin-flash"><?= htmlspecialchars($adminActionMessage, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <form method="get" class="member-form" id="memberForm">
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
                                                    <button type="submit" name="decision" value="approve" class="admin-btn">承認</button>
                                                </form>
                                                <form method="post" style="display:inline-block; margin-left:8px;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="admin_action" value="handle_request">
                                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars((string)($r['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
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

            <div class="admin-grid">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">カレンダー（今後10件）</h2>
                    </div>
                    <div class="admin-card-body">
                        <?php if (!empty($calendarUpcoming)): ?>
                            <ul class="admin-list">
                                <?php foreach ($calendarUpcoming as $c): ?>
                                    <li>
                                        <div class="t"><?= htmlspecialchars($c['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="s"><?= htmlspecialchars(($c['startdate'] ?? '') . ' - ' . ($c['enddate'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
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
                    <h2 class="admin-card-title">日記（最新50件）</h2>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($diaries)): ?>
                        <div class="diary-list">
                            <?php foreach ($diaries as $d): ?>
                                <article class="diary-item">
                                    <div class="diary-top">
                                        <div class="diary-date"><?= htmlspecialchars($d['diary_date'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php if (!empty($d['tags'])): ?>
                                            <div class="diary-tags"><?= htmlspecialchars($d['tags'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($d['title'])): ?>
                                        <h3 class="diary-title"><?= htmlspecialchars($d['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                    <?php endif; ?>
                                    <div class="diary-content"><?= nl2br(htmlspecialchars($d['content'], ENT_QUOTES, 'UTF-8')) ?></div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="empty">日記がありません。</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">水泳データ</h2>
                </div>
                <div class="admin-card-body">
                    <h3 class="section-title">ベスト（上位5）</h3>
                    <?php if (!empty($swimBest)): ?>
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
                    <?php else: ?>
                        <p class="empty">記録がありません。</p>
                    <?php endif; ?>

                    <h3 class="section-title" style="margin-top:16px;">最新（10件）</h3>
                    <?php if (!empty($swimRecent)): ?>
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
                    <?php else: ?>
                        <p class="empty">記録がありません。</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($selectedMember): ?>
            <div class="admin-grid">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">バスケ（最新10件）</h2>
                    </div>
                    <div class="admin-card-body">
                        <?php if (!empty($basketballRecent)): ?>
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
                        <?php else: ?>
                            <p class="empty">データがありません。</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 class="admin-card-title">テニス（最新10件）</h2>
                    </div>
                    <div class="admin-card-body">
                        <?php if (($tennisMode ?? '') === 'unavailable'): ?>
                            <p class="empty">tennis_db に接続できないため表示できません。</p>
                        <?php elseif (!empty($tennisRecent)): ?>
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
                        <?php else: ?>
                            <p class="empty">データがありません。</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
