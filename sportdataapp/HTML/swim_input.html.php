<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>水泳｜記録</title>
    <link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
    <link rel="stylesheet" href="../../css/swim_input.css">
    <link rel="stylesheet" href="../../css/site.css">
    
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
    <?php if ($showSuccess): ?>
        <div class="success-banner">記録を保存しました</div>
    <?php endif; ?>

    <!-- 統計サマリー -->
    <div class="stats-summary">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">総記録数</div>
                <div class="stat-value"><?= $total_records ?><span class="stat-unit">件</span></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">ベスト記録</div>
                <div class="stat-value"><?= count($best_records) ?><span class="stat-unit">種目</span></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">最近の記録</div>
                <div class="stat-value"><?= count($recent_records) ?><span class="stat-unit">件</span></div>
            </div>
        </div>
    </div>

    <!-- コンテンツグリッド -->
    <div class="content-grid">
        <!-- 左側: 入力フォーム -->
        <div class="form-panel">
            <h2 class="panel-title">記録入力</h2>
            <form method="post" id="swim-form">

  <!-- トータルタイム（上部・中央） -->
  <div class="time-display-top">
    <div class="time-label">トータルタイム</div>
    <div id="time" class="time-value">00.00.00</div>
    <input type="hidden" id="total_time" name="total_time">
  </div>

  <!-- 3列エリア -->
  <div class="form-items">

    <!-- 左：基本情報 -->
    <div class="form-basic">
      <label>日付</label>
      <input type="date" name="swim_date" required>

      <div class="checkbox-field">
        <input type="checkbox" id="is_official" name="is_official" value="1">
        <label for="is_official" class="checkbox-label">公式戦</label>
      </div>

      <div id="official_fields" style="display: none;">
        <label>大会名</label>
        <input type="text" name="meet_name" id="meet_name">

        <label>ラウンド</label>
        <select name="round" id="round">
          <option value="予選">予選</option>
          <option value="準決勝">準決勝</option>
          <option value="決勝">決勝</option>
          <option value="タイム決勝">タイム決勝</option>
        </select>
      </div>

      <label>体調</label>
      <div class="condition-faces">
        <label class="face-option">
          <input type="radio" name="condition" value="1" required>
          <span class="face-icon">
            <svg viewBox="0 0 50 50" class="face-svg">
              <circle cx="25" cy="25" r="23" fill="#e0e0e0" stroke="#999" stroke-width="1"/>
              <circle cx="17" cy="20" r="2" fill="#666"/>
              <circle cx="33" cy="20" r="2" fill="#666"/>
              <path d="M 15 35 Q 25 28 35 35" stroke="#666" stroke-width="2" fill="none"/>
            </svg>
          </span>
          <span class="face-label">すごく悪い</span>
        </label>
        <label class="face-option">
          <input type="radio" name="condition" value="2">
          <span class="face-icon">
            <svg viewBox="0 0 50 50" class="face-svg">
              <circle cx="25" cy="25" r="23" fill="#e0e0e0" stroke="#999" stroke-width="1"/>
              <circle cx="17" cy="20" r="2" fill="#666"/>
              <circle cx="33" cy="20" r="2" fill="#666"/>
              <line x1="15" y1="32" x2="35" y2="32" stroke="#666" stroke-width="2"/>
            </svg>
          </span>
          <span class="face-label">悪い</span>
        </label>
        <label class="face-option">
          <input type="radio" name="condition" value="3" checked>
          <span class="face-icon">
            <svg viewBox="0 0 50 50" class="face-svg">
              <circle cx="25" cy="25" r="23" fill="#e0e0e0" stroke="#999" stroke-width="1"/>
              <circle cx="17" cy="20" r="2" fill="#666"/>
              <circle cx="33" cy="20" r="2" fill="#666"/>
              <line x1="17" y1="32" x2="33" y2="32" stroke="#666" stroke-width="2"/>
            </svg>
          </span>
          <span class="face-label">普通</span>
        </label>
        <label class="face-option">
          <input type="radio" name="condition" value="4">
          <span class="face-icon">
            <svg viewBox="0 0 50 50" class="face-svg">
              <circle cx="25" cy="25" r="23" fill="#e0e0e0" stroke="#999" stroke-width="1"/>
              <circle cx="17" cy="20" r="2" fill="#666"/>
              <circle cx="33" cy="20" r="2" fill="#666"/>
              <path d="M 15 30 Q 25 37 35 30" stroke="#666" stroke-width="2" fill="none"/>
            </svg>
          </span>
          <span class="face-label">良い</span>
        </label>
        <label class="face-option">
          <input type="radio" name="condition" value="5">
          <span class="face-icon">
            <svg viewBox="0 0 50 50" class="face-svg">
              <circle cx="25" cy="25" r="23" fill="#e0e0e0" stroke="#999" stroke-width="1"/>
              <ellipse cx="17" cy="20" rx="2" ry="3" fill="#666"/>
              <ellipse cx="33" cy="20" rx="2" ry="3" fill="#666"/>
              <path d="M 13 30 Q 25 40 37 30" stroke="#666" stroke-width="2.5" fill="none"/>
            </svg>
          </span>
          <span class="face-label">すごく良い</span>
        </label>
      </div>

      <label>プール</label>
      <select id="pool_type" name="pool" required>
        <option value="" selected disabled>選択してください</option>
        <option value="short">短水路</option>
        <option value="long">長水路</option>
      </select>

      <!-- 種目・距離選択 -->
      <div class="event-selection">
        <h4 class="selection-title">種目・距離選択</h4>
        
        <!-- 自由形 -->
        <div class="event-category">
          <div class="category-header">自由形</div>
          <div class="distance-buttons">
            <button type="button" class="distance-btn" data-event="fr" data-distance="50">50m</button>
            <button type="button" class="distance-btn" data-event="fr" data-distance="100">100m</button>
            <button type="button" class="distance-btn" data-event="fr" data-distance="200">200m</button>
            <button type="button" class="distance-btn" data-event="fr" data-distance="400">400m</button>
            <button type="button" class="distance-btn" data-event="fr" data-distance="800">800m</button>
            <button type="button" class="distance-btn" data-event="fr" data-distance="1500">1500m</button>
          </div>
        </div>

        <!-- 背泳ぎ -->
        <div class="event-category">
          <div class="category-header">背泳ぎ</div>
          <div class="distance-buttons">
            <button type="button" class="distance-btn" data-event="ba" data-distance="50">50m</button>
            <button type="button" class="distance-btn" data-event="ba" data-distance="100">100m</button>
            <button type="button" class="distance-btn" data-event="ba" data-distance="200">200m</button>
          </div>
        </div>

        <!-- 平泳ぎ -->
        <div class="event-category">
          <div class="category-header">平泳ぎ</div>
          <div class="distance-buttons">
            <button type="button" class="distance-btn" data-event="br" data-distance="50">50m</button>
            <button type="button" class="distance-btn" data-event="br" data-distance="100">100m</button>
            <button type="button" class="distance-btn" data-event="br" data-distance="200">200m</button>
          </div>
        </div>

        <!-- バタフライ -->
        <div class="event-category">
          <div class="category-header">バタフライ</div>
          <div class="distance-buttons">
            <button type="button" class="distance-btn" data-event="fly" data-distance="50">50m</button>
            <button type="button" class="distance-btn" data-event="fly" data-distance="100">100m</button>
            <button type="button" class="distance-btn" data-event="fly" data-distance="200">200m</button>
          </div>
        </div>

        <!-- 個人メドレー -->
        <div class="event-category">
          <div class="category-header">個人メドレー</div>
          <div class="distance-buttons">
            <button type="button" class="distance-btn" data-event="im" data-distance="200">200m</button>
            <button type="button" class="distance-btn" data-event="im" data-distance="400">400m</button>
          </div>
        </div>
      </div>

      <!-- 隠しフィールド -->
      <input type="hidden" id="event" name="event" required>
      <input type="hidden" id="distance" name="distance" required>
    </div>

    <!-- 中央：ストローク -->
    <div id="stroke_area" class="form-stroke">
      <label>ストローク回数</label>
    </div>

    <!-- 右：ラップ -->
    <div id="lap_time_area" class="form-lap">
      <label>ラップタイム</label>
    </div>

    <!-- 保存ボタン -->
    <div class="form-submit">
      <input type="submit" value="保存">
    </div>

  </div>
</form>
        </div>

        <!-- 右側: 統計情報 -->
        <div class="info-panel">
            <!-- ベスト記録 -->
            <div class="info-section">
                <h3 class="section-title">ベストタイム</h3>
                <?php if (count($best_records) > 0): ?>
                <table class="best-table">
                    <thead>
                        <tr>
                            <th>種目</th>
                            <th>距離</th>
                            <th>タイム</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $event_map = [
                            "fly" => "バタフライ",
                            "ba" => "背泳ぎ",
                            "br" => "平泳ぎ",
                            "fr" => "自由形",
                            "im" => "個人メドレー"
                        ];
                        foreach ($best_records as $record): 
                            $minutes = floor($record['best_time'] / 60);
                            $seconds = $record['best_time'] % 60;
                            $time_str = sprintf('%d:%05.2f', $minutes, $seconds);
                        ?>
                        <tr>
                            <td><?= $event_map[$record['event']] ?? $record['event'] ?></td>
                            <td><?= $record['distance'] ?>m</td>
                            <td class="best-time"><?= $time_str ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="no-data">まだ記録がありません</p>
                <?php endif; ?>
            </div>

            <!-- 最近の記録 -->
            <div class="info-section">
                <h3 class="section-title">最近の記録</h3>
                <?php if (count($recent_records) > 0): ?>
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>種目</th>
                            <th>タイム</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_records as $record): 
                            $minutes = floor($record['total_time'] / 60);
                            $seconds = $record['total_time'] % 60;
                            $time_str = sprintf('%d:%05.2f', $minutes, $seconds);
                        ?>
                        <tr>
                            <td><?= date('m/d', strtotime($record['swim_date'])) ?></td>
                            <td><?= $event_map[$record['event']] ?? $record['event'] ?> <?= $record['distance'] ?>m</td>
                            <td><?= $time_str ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="no-data">まだ記録がありません</p>
                <?php endif; ?>
            </div>

            <!-- 種目別記録数 -->
            <div class="info-section">
                <h3 class="section-title">種目別記録数</h3>
                <?php if (count($event_counts) > 0): ?>
                <div class="event-stats">
                    <?php foreach ($event_counts as $ec): ?>
                    <div class="event-stat-item">
                        <span class="event-name"><?= $event_map[$ec['event']] ?? $ec['event'] ?></span>
                        <span class="event-count"><?= $ec['count'] ?>件</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="no-data">まだ記録がありません</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php if ($showLoader): ?>
<script src="../../js/loading.js"></script>
<?php endif; ?>

<script>
// ベストタイム情報をJavaScriptに渡す
const bestTimes = <?php echo json_encode($bestTimes); ?>;
</script>
<script src="../../js/swim/swim_input.js"></script>
</body>
</html>
