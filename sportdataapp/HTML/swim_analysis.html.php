<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>水泳｜分析</title>
<link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
<link rel="stylesheet" href="../../css/swim_analysis.css">
<link rel="stylesheet" href="../../css/site.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

<div class="analysis-container">
    <h1 class="page-title">水泳データ分析</h1>
    
    <!-- 参考リンク -->
    <div class="reference-link">
        <a href="https://tohokuswim.net/sokuhou/S_select.php" target="_blank" rel="noopener noreferrer">
            📊 東北地区水泳速報
        </a>
    </div>
    
    <!-- フィルターエリア -->
    <div class="filter-section">
        <h2 class="filter-title">データフィルター</h2>
        <form method="get" class="filter-form">
            <?php $tab_id = (string)($GLOBALS['SPORTDATA_TAB_ID'] ?? ($_GET['tab_id'] ?? '')); ?>
            <input type="hidden" name="tab_id" value="<?= htmlspecialchars($tab_id, ENT_QUOTES, 'UTF-8') ?>">
            <!-- 種目・距離選択（ボタン式） -->
            <div class="event-selection-analysis">
                <?php
                // ユーザーが出場した種目を種目ごとにグループ化
                $event_groups = [
                    'fr' => ['name' => '自由形', 'distances' => []],
                    'ba' => ['name' => '背泳ぎ', 'distances' => []],
                    'br' => ['name' => '平泳ぎ', 'distances' => []],
                    'fly' => ['name' => 'バタフライ', 'distances' => []],
                    'im' => ['name' => '個人メドレー', 'distances' => []]
                ];
                
                // ユーザーが出場したことのある種目・距離を収集
                foreach ($combos as $c) {
                    if (isset($event_groups[$c['event']])) {
                        $pool_label = $c['pool'] === 'short' ? '短' : '長';
                        $event_groups[$c['event']]['distances'][] = [
                            'pool' => $c['pool'],
                            'distance' => $c['distance'],
                            'pool_label' => $pool_label,
                            'combo' => $c['pool'] . '|' . $c['event'] . '|' . $c['distance']
                        ];
                    }
                }
                
                // 種目ごとに表示
                foreach ($event_groups as $event_key => $event_data):
                    if (empty($event_data['distances'])) continue; // 出場していない種目はスキップ
                ?>
                <div class="event-category-analysis">
                    <div class="category-header-analysis"><?= htmlspecialchars($event_data['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="distance-buttons-analysis">
                        <?php foreach ($event_data['distances'] as $dist_data): 
                            $is_selected = ($selected_combo === $dist_data['combo']) ? ' selected' : '';
                        ?>
                        <button type="button" 
                                class="distance-btn-analysis<?= $is_selected ?>" 
                                data-combo="<?= htmlspecialchars($dist_data['combo'], ENT_QUOTES, 'UTF-8') ?>">
                            <span class="pool-label"><?= $dist_data['pool_label'] ?></span>
                            <?= htmlspecialchars($dist_data['distance'], ENT_QUOTES, 'UTF-8') ?>m
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <input type="hidden" id="combo" name="combo" value="<?= htmlspecialchars($selected_combo ?? '', ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="filter-row">
                <div class="filter-group period-group">
                    <label class="filter-label">期間</label>
                    <div class="date-range">
                        <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from ?? '', ENT_QUOTES, 'UTF-8') ?>" class="filter-input">
                        <span class="date-separator">〜</span>
                        <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to ?? '', ENT_QUOTES, 'UTF-8') ?>" class="filter-input">
                    </div>
                </div>

                <div class="filter-group">
                    <label class="filter-label">大会名</label>
                    <select id="meet_name" name="meet_name" class="filter-input" <?= $selected_combo ? '' : 'disabled' ?> aria-label="大会名で絞り込み">
                        <option value="">すべて</option>
                        <?php if (!empty($meet_options)): ?>
                            <?php foreach ($meet_options as $opt): ?>
                                <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= (isset($meet_name) && $meet_name === $opt) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            
            <div class="filter-actions">
                <?php if ($selected_combo): ?>
                <button type="submit" class="filter-btn primary-btn">絞り込み</button>
                <?php endif; ?>
                <a href="<?= ($tab_id !== '') ? ('?tab_id=' . rawurlencode($tab_id)) : '?' ?>" class="filter-btn reset-btn">リセット</a>
            </div>
        </form>
    </div>

    <?php if (!$selected_combo): ?>
    <!-- 種目未選択時のメッセージ -->
    <div class="no-event-selected">
        <div class="no-event-message">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <h3>種目を選択してください</h3>
            <p>データ分析を表示するには、上記から種目と距離を選択してください</p>
        </div>
    </div>
    <?php else: ?>
    <!-- 現在の選択種目 -->
    <div class="current-event">
        <h2>
            <span class="event-badge"><?= htmlspecialchars($pool === "short" ? "短水路" : "長水路", ENT_QUOTES, 'UTF-8') ?></span>
            <span class="event-title"><?= htmlspecialchars($distance, ENT_QUOTES, 'UTF-8') ?>m <?= htmlspecialchars($event_map[$event] ?? $event, ENT_QUOTES, 'UTF-8') ?></span>
        </h2>
    </div>

    <!-- 統計サマリー -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">記録数</div>
                <div class="stat-value"><?= $stats['count'] ?><span class="stat-unit">回</span></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">ベストタイム</div>
                <div class="stat-value" id="stat-best"></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">平均タイム</div>
                <div class="stat-value" id="stat-avg"></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">改善率</div>
                <div class="stat-value" id="stat-improvement"></div>
            </div>
        </div>
    </div>

<!-- 比較テーブル（3列） -->
<div class="layout-two-col">
    <div class="left-col">
            <div class="comparison-card">
                <h3>タイム比較</h3>
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th scope="col">前回</th>
                            <th scope="col">ベスト</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>今回</th>
                            <td id="prev-now"></td>
                            <td><span id="best-now"></span> <span id="pb-badge" class="pb-badge" aria-live="polite"></span></td>
                        </tr>
                        <tr>
                            <th>比較対象</th>
                            <td id="prev-then"></td>
                            <td id="best-then"></td>
                        </tr>
                        <tr class="diff-row">
                            <th>差分</th>
                            <td id="diff-prev"></td>
                            <td id="diff-best"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ラップタイム比較 -->
            <div class="sd-collapse" data-sd-collapse="mobile">
                <button
                    type="button"
                    class="sd-collapse__btn"
                    data-label-collapsed="ラップ比較を表示"
                    data-label-expanded="ラップ比較を隠す"
                    aria-expanded="true"
                >ラップ比較を隠す</button>
                <div class="sd-collapse__panel">
                    <div class="chart-card lap-comparison-section">
                        <h3>ラップタイム比較（最新 / 前回）</h3>
                        <?php if (count($lap_comparison_data) > 0): ?>
                        <div class="comparison-grid">
                            <?php foreach ($lap_comparison_data as $key => $data): 
                                if (!isset($data['time_diff'])) continue;
                                
                                $is_improved = $data['time_diff'] < 0;
                                $stroke_changed = $data['stroke_diff'] != 0;
                            ?>
                            <div class="comparison-card <?= $is_improved ? 'improved' : 'declined' ?>">
                                <div class="comparison-header">
                                    <h4><?= $event_map[$data['event']] ?? $data['event'] ?> <?= $data['distance'] ?>m</h4>
                                    <span class="record-badge"><?= $data['record_count'] ?>回記録</span>
                                </div>
                                <div class="comparison-body">
                                    <div class="time-comparison">
                                        <div class="time-item previous">
                                            <span class="label">前回</span>
                                            <span class="value"><?= number_format($data['previous_time'], 2) ?>秒</span>
                                            <span class="stroke-info"><?= $data['previous_stroke'] ?>St</span>
                                        </div>
                                        <div class="arrow">→</div>
                                        <div class="time-item latest">
                                            <span class="label">最新</span>
                                            <span class="value"><?= number_format($data['latest_time'], 2) ?>秒</span>
                                            <span class="stroke-info"><?= $data['latest_stroke'] ?>St</span>
                                        </div>
                                    </div>
                                    <div class="diff-display <?= $is_improved ? 'better' : 'worse' ?>">
                                        <?php if ($is_improved): ?>
                                            <span class="diff-icon">↓</span>
                                            <span class="diff-value"><?= number_format(abs($data['time_diff']), 2) ?>秒差</span>
                                        <?php else: ?>
                                            <span class="diff-icon">↑</span>
                                            <span class="diff-value"><?= number_format(abs($data['time_diff']), 2) ?>秒差</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($stroke_changed): ?>
                                            <span class="stroke-diff">
                                                (<?= $data['stroke_diff'] > 0 ? '+' : '' ?><?= $data['stroke_diff'] ?>St)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="no-data">比較できる記録がありません</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="right-col">
            <div class="sd-collapse" data-sd-collapse="mobile">
                <button
                    type="button"
                    class="sd-collapse__btn"
                    data-label-collapsed="グラフを表示"
                    data-label-expanded="グラフを隠す"
                    aria-expanded="true"
                >グラフを隠す</button>
                <div class="sd-collapse__panel">
                    <!-- 推移グラフ -->
                    <div class="chart-card">
                        <h3>タイム推移</h3>
                        <canvas id="timeChart"></canvas>
                    </div>

                    <!-- 比較チャート（前回 / 今回, ベスト / 今回） -->
                    <div class="compare-charts">
                        <div class="chart-card small">
                            <h3>前回 / 今回</h3>
                            <canvas id="prevNowChart"></canvas>
                        </div>
                        <div class="chart-card small">
                            <h3>ベスト / 今回</h3>
                            <canvas id="bestNowChart"></canvas>
                        </div>
                    </div>

                    <!-- ペース分析 -->
                    <div class="chart-card">
                        <h3>100mあたりのペース分析</h3>
                        <canvas id="paceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 全記録一覧（フル幅） -->
    <div class="sd-collapse" data-sd-collapse="mobile">
        <button
            type="button"
            class="sd-collapse__btn"
            data-label-collapsed="全記録一覧を表示"
            data-label-expanded="全記録一覧を隠す"
            aria-expanded="true"
        >全記録一覧を隠す</button>
        <div class="sd-collapse__panel">
            <div class="records-full-width">
                <div class="records-card">
                    <h3>全記録一覧</h3>
                    <div class="records-table-wrapper">
                        <table class="records-table">
                            <thead>
                                <tr>
                                    <th>日付</th>
                                    <th>タイム</th>
                                    <th>コンディション</th>
                                    <th>詳細</th>
                                </tr>
                            </thead>
                            <tbody id="records-tbody">
                                <!-- JavaScriptで生成 -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; // $selected_combo の終了 ?>

<!-- =====================
     PHP → JS データ渡し
===================== -->
<script>
const NOW_TIME  = <?= json_encode($now_time, JSON_UNESCAPED_UNICODE) ?>;
const PREV_TIME = <?= json_encode($prev_time, JSON_UNESCAPED_UNICODE) ?>;
const BEST_TIME = <?= json_encode($best_time, JSON_UNESCAPED_UNICODE) ?>;
const HISTORY   = <?= json_encode($history, JSON_UNESCAPED_UNICODE) ?>;
const STATS     = <?= json_encode($stats, JSON_UNESCAPED_UNICODE) ?>;
const DISTANCE  = <?= json_encode($distance, JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- 種目選択ボタンのイベント -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const distanceButtons = document.querySelectorAll('.distance-btn-analysis');
    const comboInput = document.getElementById('combo');
    const filterForm = document.querySelector('.filter-form');
    const meetSelect = document.getElementById('meet_name');
    
    distanceButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // すべてのボタンから選択を外す
            distanceButtons.forEach(b => b.classList.remove('selected'));
            
            // クリックしたボタンを選択状態に
            this.classList.add('selected');
            
            // 隠しフィールドに値を設定
            comboInput.value = this.dataset.combo;

            // 種目変更時は大会名フィルターを解除（大会が違うとデータが0件になりやすいため）
            if (meetSelect) {
                meetSelect.value = '';
            }
            
            // 自動でフォーム送信
            filterForm.submit();
        });
    });
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php if ($showLoader): ?>
<script src="../../js/loading.js"></script>
<?php endif; ?>

<!-- JS 別ファイル -->
<script src="../../js/swim/swim_analysis.js"></script>
</body>
</html>
