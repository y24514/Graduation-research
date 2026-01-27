<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>身体情報 - Sports Analytics App</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="../css/pi.css">
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

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
        <div class="success-banner">
            <span class="success-icon">✓</span>
            身体データを登録しました
        </div>
        <?php endif; ?>

        <!-- 統計サマリーカード -->
        <div class="stats-summary">
            <div class="stat-card stat-card-primary">
                <div class="stat-content">
                    <div class="stat-label">総記録数</div>
                    <div class="stat-value"><?= $stats['total_records'] ?><span class="stat-unit">件</span></div>
                </div>
            </div>

            <div class="stat-card stat-card-success">
                <div class="stat-content">
                    <div class="stat-label">平均体重</div>
                    <div class="stat-value">
                        <?= $stats['avg_weight'] ? $stats['avg_weight'] : '--' ?><span class="stat-unit">kg</span>
                    </div>
                    <?php if ($stats['weight_change'] !== null): ?>
                    <div class="stat-change <?= $stats['weight_change'] < 0 ? 'decrease' : 'increase' ?>">
                        <?= $stats['weight_change'] > 0 ? '+' : '' ?><?= $stats['weight_change'] ?>kg
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card stat-card-warning">
                <div class="stat-content">
                    <div class="stat-label">最新BMI</div>
                    <div class="stat-value">
                        <?= $stats['latest']['bmi'] ?? '--' ?>
                    </div>
                    <?php if ($stats['bmi_category']): ?>
                    <div class="bmi-category category-<?= strtolower($stats['bmi_category']) ?>">
                        <?= $stats['bmi_category'] ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card stat-card-info">
                <div class="stat-content">
                    <div class="stat-label">平均睡眠</div>
                    <div class="stat-value">
                        <?= $stats['avg_sleep'] ?? '--' ?><span class="stat-unit">時間</span>
                    </div>
                    <?php if ($stats['sleep_quality']): ?>
                    <div class="sleep-quality quality-<?= strtolower($stats['sleep_quality']) ?>">
                        <?= $stats['sleep_quality'] ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 最新データの表示 -->
        <?php if ($stats['latest']): ?>
        <div class="current-status">
            <h2 class="section-title">
                最新の記録
            </h2>
            <div class="current-data">
                <div class="data-item">
                    <span class="data-label">身長</span>
                    <span class="data-value"><?= $stats['latest']['height'] ?> cm</span>
                </div>
                <div class="data-item">
                    <span class="data-label">体重</span>
                    <span class="data-value"><?= $stats['latest']['weight'] ?> kg</span>
                </div>
                <div class="data-item">
                    <span class="data-label">BMI</span>
                    <span class="data-value"><?= $stats['latest']['bmi'] ?? '--' ?></span>
                </div>
                <div class="data-item">
                    <span class="data-label">睡眠時間</span>
                    <span class="data-value"><?= $stats['latest']['sleep_time'] ?? '--' ?></span>
                </div>
                <div class="data-item full-width">
                    <span class="data-label">怪我/不調</span>
                    <span class="data-value"><?= $stats['latest']['injury'] ?: '特になし' ?></span>
                </div>
                <div class="data-item full-width">
                    <span class="data-label">記録日時</span>
                    <span class="data-value"><?= date('Y年m月d日 H:i', strtotime($stats['latest']['create_at'])) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 入力フォームとグラフエリア -->
        <div class="content-grid">
            <!-- 入力パネル -->
            <div class="input-panel">
                <h2 class="panel-title">
                    データ登録
                </h2>
                <form method="POST" class="pi-form">
                    <div class="form-group">
                        <label for="height">
                            身長 (cm)
                        </label>
                        <input type="number" step="0.1" name="height" id="height" 
                               value="<?= $stats['latest']['height'] ?? '' ?>" 
                               placeholder="例: 170.5" required>
                    </div>

                    <div class="form-group">
                        <label for="weight">
                            体重 (kg)
                        </label>
                        <input type="number" step="0.1" name="weight" id="weight" 
                               placeholder="例: 65.5" required>
                    </div>

                    <div class="form-group">
                        <label for="injury">
                            怪我・不調
                        </label>
                        <textarea name="injury" id="injury" rows="3" 
                                  placeholder="特になければ空欄でOK"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="sleep_time">
                            睡眠時間
                        </label>
                        <input type="time" name="sleep_time" id="sleep_time" required>
                    </div>

                    <button type="submit" class="submit-btn">
                        データを登録
                    </button>
                </form>

                <!-- BMI計算機 -->
                <div class="bmi-calculator">
                    <h3 class="calc-title">BMI計算機</h3>
                    <div class="calc-result">
                        <span class="calc-label">現在のBMI:</span>
                        <span class="calc-value" id="current-bmi">--</span>
                    </div>
                    <div class="bmi-scale">
                        <div class="scale-item">
                            <div class="scale-bar underweight"></div>
                            <span class="scale-label">低体重<br>&lt;18.5</span>
                        </div>
                        <div class="scale-item">
                            <div class="scale-bar normal"></div>
                            <span class="scale-label">普通<br>18.5-24.9</span>
                        </div>
                        <div class="scale-item">
                            <div class="scale-bar overweight"></div>
                            <span class="scale-label">肥満(1)<br>25-29.9</span>
                        </div>
                        <div class="scale-item">
                            <div class="scale-bar obese"></div>
                            <span class="scale-label">肥満(2+)<br>≥30</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- グラフパネル -->
            <div class="graph-panel">
                <div class="chart-section">
                    <h3 class="chart-title">
                        体重・BMIの推移
                    </h3>
                    <div class="chart-container">
                        <canvas id="weight-chart"></canvas>
                    </div>
                </div>

                <div class="chart-section">
                    <h3 class="chart-title">
                        睡眠時間の推移
                    </h3>
                    <div class="chart-container">
                        <canvas id="sleep-chart"></canvas>
                    </div>
                </div>

                <div class="chart-section">
                    <h3 class="chart-title">
                        体重分布
                    </h3>
                    <div class="chart-container">
                        <canvas id="weight-distribution"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 記録一覧テーブル -->
        <div class="records-section">
            <h2 class="section-title">
                記録履歴
                <span class="record-count">(<?= count($records) ?>件)</span>
            </h2>
            <div class="records-table-wrapper">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>記録日時</th>
                            <th>身長 (cm)</th>
                            <th>体重 (kg)</th>
                            <th>BMI</th>
                            <th>睡眠時間</th>
                            <th>怪我・不調</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($records) === 0): ?>
                        <tr>
                            <td colspan="6" class="no-data">まだデータがありません</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($records as $index => $record): ?>
                        <tr class="<?= $index === 0 ? 'latest-record' : '' ?>">
                            <td><?= date('Y/m/d H:i', strtotime($record['create_at'])) ?></td>
                            <td><?= $record['height'] ?></td>
                            <td class="weight-cell">
                                <?= $record['weight'] ?>
                                <?php if ($index < count($records) - 1): 
                                    $diff = $record['weight'] - $records[$index + 1]['weight'];
                                    if ($diff != 0):
                                ?>
                                <span class="weight-diff <?= $diff < 0 ? 'decrease' : 'increase' ?>">
                                    <?= $diff > 0 ? '+' : '' ?><?= round($diff, 1) ?>
                                </span>
                                <?php endif; endif; ?>
                            </td>
                            <td>
                                <?php if ($record['bmi']): ?>
                                <span class="bmi-badge bmi-<?= $record['bmi'] < 18.5 ? 'low' : ($record['bmi'] < 25 ? 'normal' : 'high') ?>">
                                    <?= $record['bmi'] ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td><?= $record['sleep_time'] ?: '--' ?></td>
                            <td class="injury-cell"><?= $record['injury'] ?: '特になし' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // PHPからJavaScriptへのデータ渡し
        const recordsData = <?= json_encode($records) ?>;
    </script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?php if ($showLoader): ?>
    <script src="../js/loading.js"></script>
    <?php endif; ?>
    
    <script src="../js/pi.js"></script>
</body>
</html>
