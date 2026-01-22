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

    <script src="../../js/fullcalendar/dist/index.global.min.js" defer></script>
    <script src="../../js/swim_practice_create.js" defer></script>

    <script>
        // JS から globalThis/window 経由で参照するため、const ではなく window に載せる
        window.showLoader = <?= $showLoader ? 'true' : 'false' ?>;
        window.tabId = <?= json_encode((string)($GLOBALS['SPORTDATA_TAB_ID'] ?? ($_GET['tab_id'] ?? '')), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        window.initialMenuJson = <?= json_encode((string)($menu_json ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        window.initialMenuText = <?= json_encode((string)($menu_text ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        window.kindOptions = <?= json_encode($kindOptions ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        window.practiceEvents = <?= json_encode($practiceEvents ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        window.practiceCache = <?php
            $cache = [];
            if (!empty($practices) && is_array($practices)) {
                foreach ($practices as $p) {
                    $id = (int)($p['id'] ?? 0);
                    if ($id <= 0) continue;
                    $cache[(string)$id] = [
                        'id' => $id,
                        'practice_date' => (string)($p['practice_date'] ?? ''),
                        'title' => (string)($p['title'] ?? ''),
                        'menu_text' => (string)($p['menu_text'] ?? ''),
                        'memo' => (string)($p['memo'] ?? ''),
                        'created_at' => (string)($p['created_at'] ?? ''),
                    ];
                }
            }
            echo json_encode($cache, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        ?>;
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

<div class="container">
    <?php if ($showSuccess): ?>
        <div class="success-banner">練習メニューを保存しました</div>
    <?php endif; ?>

    <!-- 練習サマリー（不要なら非表示） -->

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
                <input type="hidden" name="tab_id" value="<?= htmlspecialchars((string)($GLOBALS['SPORTDATA_TAB_ID'] ?? ($_GET['tab_id'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-basic practice-form-basic">
                    <label>日付</label>
                    <input type="date" name="practice_date" value="<?= htmlspecialchars($practice_date, ENT_QUOTES, 'UTF-8') ?>" required>

                    <label>タイトル</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" placeholder="例: メイン 100m×10" required>

                    <label>練習スケジュール</label>
                    <div class="lane-planner" data-lane-planner>
                        <div class="lane-tabs" role="tablist" aria-label="レーン切り替え">
                            <button type="button" class="lane-tab is-active" data-lane-tab data-lane="all" role="tab" aria-selected="true">全体</button>
                            <button type="button" class="lane-tab" data-lane-tab data-lane="1" role="tab" aria-selected="false">レーン1</button>
                            <button type="button" class="lane-tab" data-lane-tab data-lane="2" role="tab" aria-selected="false">レーン2</button>
                            <button type="button" class="lane-tab" data-lane-tab data-lane="3" role="tab" aria-selected="false">レーン3</button>
                            <button type="button" class="lane-tab" data-lane-tab data-lane="4" role="tab" aria-selected="false">レーン4</button>
                            <button type="button" class="lane-tab" data-lane-tab data-lane="5" role="tab" aria-selected="false">レーン5</button>
                            <button type="button" class="lane-tab" data-lane-tab data-lane="6" role="tab" aria-selected="false">レーン6</button>
                        </div>

                        <div class="lane-toolbar">
                            <div class="lane-toolbar__left">
                                <button type="button" class="lane-btn" data-action="add-row">行を追加</button>
                                <button type="button" class="lane-btn" data-action="add-kind">種類を追加</button>
                                <button type="button" class="lane-btn" data-action="duplicate-row" disabled>行を複製</button>
                                <button type="button" class="lane-btn lane-btn--danger" data-action="delete-row" disabled>行を削除</button>
                            </div>
                            <div class="lane-toolbar__right">
                                <button type="button" class="lane-btn" data-action="print">印刷プレビュー</button>
                                <button type="button" class="lane-btn" data-action="copy-from-lane">他レーンから複製</button>
                                <button type="button" class="lane-btn" data-action="help">使い方ヒント</button>
                            </div>
                        </div>

                        <div class="lane-panel is-active" data-lane-panel data-lane="all">
                            <div class="lane-empty" style="margin-bottom: 10px;">
                                「全体」で編集すると、全レーンに同じ内容が同時反映されます（あとから各レーンで個別に調整も可能）。
                            </div>

                            <div class="lane-table-wrap">
                                <table class="lane-table">
                                    <thead>
                                    <tr>
                                        <th class="col-kind">種類</th>
                                        <th class="col-dist">距離</th>
                                        <th class="col-reps">本数</th>
                                        <th class="col-cycle">サイクル</th>
                                        <th class="col-set">セット間</th>
                                        <th class="col-stroke">種目</th>
                                        <th class="col-note">内容</th>
                                        <th class="col-intensity">強度</th>
                                        <th class="col-total">折距離</th>
                                    </tr>
                                    </thead>
                                    <tbody data-lane-tbody data-lane="all"></tbody>
                                </table>
                            </div>

                            <div class="lane-footer">
                                <div class="lane-footer__left">
                                    <span class="lane-total-label">合計</span>
                                    <span class="lane-total" data-lane-total data-lane="all">0m</span>
                                </div>
                                <div class="lane-footer__right">
                                    <span class="lane-hint">行をクリックして選択できます</span>
                                </div>
                            </div>
                        </div>

                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <div class="lane-panel" data-lane-panel data-lane="<?= $i ?>">
                                <div class="lane-table-wrap">
                                    <table class="lane-table">
                                        <thead>
                                        <tr>
                                            <th class="col-kind">種類</th>
                                            <th class="col-dist">距離</th>
                                            <th class="col-reps">本数</th>
                                            <th class="col-cycle">サイクル</th>
                                            <th class="col-set">セット間</th>
                                            <th class="col-stroke">種目</th>
                                            <th class="col-note">内容</th>
                                            <th class="col-intensity">強度</th>
                                            <th class="col-total">折距離</th>
                                        </tr>
                                        </thead>
                                        <tbody data-lane-tbody data-lane="<?= $i ?>"></tbody>
                                    </table>
                                </div>

                                <div class="lane-footer">
                                    <div class="lane-footer__left">
                                        <span class="lane-total-label">合計</span>
                                        <span class="lane-total" data-lane-total data-lane="<?= $i ?>">0m</span>
                                    </div>
                                    <div class="lane-footer__right">
                                        <span class="lane-hint">行をクリックして選択できます</span>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>

                        <input type="hidden" name="menu_json" value="" data-menu-json>
                        <textarea name="menu_text" data-menu-text hidden><?= htmlspecialchars($menu_text, ENT_QUOTES, 'UTF-8') ?></textarea>

                        <details class="lane-raw-editor">
                            <summary>テキストで直接編集（互換モード）</summary>
                            <p class="lane-raw-editor__note">表で入力した内容は保存時にこのテキストへ変換されます。ここを編集すると表の内容とは同期しません。</p>
                            <textarea rows="8" data-menu-text-editor placeholder="例:
W-up 200
Kick 50×8 (1:10)
Main 100×10 (1:30)
Down 200"><?= htmlspecialchars($menu_text, ENT_QUOTES, 'UTF-8') ?></textarea>
                        </details>
                    </div>

                    <label>メモ</label>
                    <textarea name="memo" rows="4" placeholder="例: 体調、目標、意識したことなど"><?= htmlspecialchars($memo, ENT_QUOTES, 'UTF-8') ?></textarea>

                    <div class="practice-actions">
                        <button type="submit" class="submit-btn">保存</button>
                        <a href="swim_input.php?tab_id=<?= rawurlencode((string)($GLOBALS['SPORTDATA_TAB_ID'] ?? ($_GET['tab_id'] ?? ''))) ?>" class="submit-btn practice-link">記録へ戻る</a>
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
                <div class="practice-list-header">
                    <h3 class="section-title">作成済み練習（最新20件）</h3>
                    <div class="practice-view-toggle" role="tablist" aria-label="表示切替">
                        <button type="button" class="practice-view-btn is-active" data-action="practice-view" data-view="list" aria-selected="true">リスト</button>
                        <button type="button" class="practice-view-btn" data-action="practice-view" data-view="calendar" aria-selected="false">カレンダー</button>
                    </div>
                </div>

                <div class="practice-list-tools" data-practice-list-tools>
                    <div class="practice-search">
                        <input type="search" class="practice-search__input" placeholder="検索（タイトル / 日付）" data-practice-search>
                        <button type="button" class="practice-search__clear" data-action="practice-search-clear" aria-label="検索をクリア" hidden>×</button>
                    </div>
                    <div class="practice-list-meta">
                        <span class="practice-count" data-practice-count></span>
                    </div>
                </div>

                <?php if (empty($hasPracticeTable)): ?>
                    <div class="no-data">DBテーブル未作成のため一覧を表示できません</div>
                <?php elseif (empty($practices)): ?>
                    <div class="no-data">まだ練習がありません。左のフォームから作成してください。</div>
                <?php else: ?>
                    <div class="practice-list" data-practice-list>
                        <?php $today = date('Y-m-d'); ?>
                        <?php foreach ($practices as $p): ?>
                            <?php
                            $pDate = $p['practice_date'] ?? '';
                            $pTitle = $p['title'] ?? '';
                            $isToday = ($pDate === $today);
                            ?>
                            <div class="practice-card<?= $isToday ? ' is-today' : '' ?>" data-practice-card data-practice-id="<?= (int)($p['id'] ?? 0) ?>" data-practice-date="<?= htmlspecialchars((string)$pDate, ENT_QUOTES, 'UTF-8') ?>" data-practice-title="<?= htmlspecialchars((string)$pTitle, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="button" class="practice-card__header" data-action="open-practice" data-practice-id="<?= (int)($p['id'] ?? 0) ?>">
                                    <span class="practice-card__date"><?= htmlspecialchars((string)$pDate, ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="practice-card__title"><?= htmlspecialchars((string)$pTitle, ENT_QUOTES, 'UTF-8') ?></span>
                                </button>
                                <button type="button" class="practice-card__quick" data-action="quote-practice" data-practice-id="<?= (int)($p['id'] ?? 0) ?>">引用</button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="practice-calendar" data-practice-calendar hidden></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 練習詳細モーダル（リスト/カレンダー共通） -->
<div class="practice-modal" data-practice-modal hidden>
    <div class="practice-modal__backdrop" data-action="close-practice-modal" aria-hidden="true"></div>
    <div class="practice-modal__dialog" role="dialog" aria-modal="true" aria-label="練習の詳細">
        <div class="practice-modal__header">
            <div class="practice-modal__meta">
                <div class="practice-modal__date" data-practice-modal-date></div>
                <div class="practice-modal__title" data-practice-modal-title></div>
            </div>
            <button type="button" class="practice-modal__close" data-action="close-practice-modal" aria-label="閉じる">×</button>
        </div>

        <div class="practice-modal__actions">
            <button type="button" class="lane-btn" data-action="quote-practice" data-practice-id="" data-practice-modal-quote>この練習を引用</button>
            <button type="button" class="lane-btn lane-btn--danger" data-action="delete-practice" data-practice-id="" data-practice-modal-delete>削除</button>
        </div>

        <div class="practice-modal__body">
            <pre class="practice-modal__menu" data-practice-modal-menu></pre>
            <div class="practice-modal__memo" data-practice-modal-memo hidden>
                <div class="practice-modal__memo-label">メモ</div>
                <div class="practice-modal__memo-text" data-practice-modal-memo-text></div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
