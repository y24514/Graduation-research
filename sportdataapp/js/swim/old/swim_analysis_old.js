/* =====================
   ヘルパー: 時間パース/フォーマット
   - 入力は秒 (number) または "m:ss.xx" の文字列などを想定
===================== */
function parseTimeInput(v) {
    if (v === null || v === undefined) return null;
    if (typeof v === 'number') return v;
    if (typeof v === 'string') {
        v = v.trim();
        if (v === '') return null;
        if (v.indexOf(':') !== -1) {
            const parts = v.split(':').map(p => p.trim());
            // support h:mm:ss, mm:ss or m:ss.xx
            if (parts.length === 3) {
                const h = parseInt(parts[0], 10) || 0;
                const m = parseInt(parts[1], 10) || 0;
                const s = parseFloat(parts[2]) || 0;
                return h * 3600 + m * 60 + s;
            } else if (parts.length === 2) {
                const m = parseInt(parts[0], 10) || 0;
                const s = parseFloat(parts[1]) || 0;
                return m * 60 + s;
            }
            // fallback
            const f = parseFloat(v.replace(':', '.'));
            return isNaN(f) ? null : f;
        }
        const f = parseFloat(v);
        return isNaN(f) ? null : f;
    }
    return null;
}

function formatTime(sec) {
    if (sec === null || sec === undefined || isNaN(sec)) return '---';
    const total = Number(sec);
    // format as H:MM:SS.ss if >= 3600, else M:SS.ss or SS.ss
    const hours = Math.floor(total / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const seconds = (total % 60).toFixed(2).padStart(5, '0');
    if (hours > 0) {
        const mm = String(minutes).padStart(2, '0');
        return `${hours}:${mm}:${seconds}`;
    }
    if (minutes > 0) {
        return `${minutes}:${seconds}`;
    }
    return seconds;
}

// Helper: compute nice y-axis min/max/step for seconds data
function computeYAxisOptions(values, opts) {
    opts = opts || {};
    let vals = values.filter(v => v !== null && v !== undefined && !isNaN(v));
    if (vals.length === 0) return { min: 0, max: 10, stepSize: 1 };
    // median check to detect if most data are short but there are large outliers (e.g. mis-parsed "1:31:00")
    vals.sort((a,b) => a - b);
    const midIdx = Math.floor(vals.length / 2);
    const median = vals[midIdx];
    // if median indicates short durations and there are huge outliers, ignore outliers > 3600s
    if (median < 600) {
        const before = vals.length;
        vals = vals.filter(v => v <= 3600);
        if (vals.length === 0) {
            // fallback to original vals if filtering removed all
            vals = values.filter(v => v !== null && v !== undefined && !isNaN(v));
        } else if (vals.length < before) {
            console.debug('computeYAxisOptions: removed large outliers from axis calculation', { before, after: vals.length });
        }
    }
    const minV = Math.min.apply(null, vals);
    const maxV = Math.max.apply(null, vals);
    // if all equal, create a small range
    let min = minV;
    let max = maxV;
    if (Math.abs(max - min) < 1e-6) {
        // choose +/- 1 second or 5% whichever greater
        const delta = Math.max(1, Math.abs(min) * 0.05);
        min = Math.max(0, min - delta);
        max = max + delta;
    }
    const rawRange = max - min;
    const desiredTicks = opts.desiredTicks || 5;
    let roughStep = rawRange / desiredTicks;
    // compute magnitude
    const mag = Math.pow(10, Math.floor(Math.log10(Math.max(roughStep, 1e-9))));
    const candidates = [1, 2, 5, 10];
    let step = mag;
    for (let i = 0; i < candidates.length; i++) {
        const s = candidates[i] * mag;
        const ticks = Math.ceil(rawRange / s);
        if (ticks <= desiredTicks * 1.8 && ticks >= 2) { step = s; break; }
    }
    // If step is fractional (when roughStep < 1), normalize to 0.1, 0.2, 0.5, etc.
    if (roughStep < 1) {
        // try decimal candidates
        const decCandidates = [0.1, 0.2, 0.5, 1];
        for (let s of decCandidates) {
            const ticks = Math.ceil(rawRange / s);
            if (ticks <= desiredTicks * 1.8 && ticks >= 2) { step = s; break; }
        }
    }
    // compute nice min/max aligned to step
    const niceMin = Math.floor(min / step) * step;
    const niceMax = Math.ceil(max / step) * step;
    return { min: Math.max(0, niceMin), max: Math.max(niceMin + step, niceMax), stepSize: step };
}

/* =====================
   比較表示 (DOM 安全チェック含む)
===================== */
const elPrevNow = document.getElementById('prev-now');
const elPrevThen = document.getElementById('prev-then');
const elBestNow = document.getElementById('best-now');
const elBestThen = document.getElementById('best-then');
const elDiffPrev = document.getElementById('diff-prev');
const elDiffBest = document.getElementById('diff-best');
const elPbBadge = document.getElementById('pb-badge');

const nowSec  = parseTimeInput(NOW_TIME);
const prevSec = parseTimeInput(PREV_TIME);
const bestSec = parseTimeInput(BEST_TIME);

if (elPrevNow) elPrevNow.textContent = nowSec !== null ? formatTime(nowSec) : '---';
if (elPrevThen) elPrevThen.textContent = prevSec !== null ? formatTime(prevSec) : 'N/A';
if (elBestNow) elBestNow.textContent = nowSec !== null ? formatTime(nowSec) : '---';
if (elBestThen) elBestThen.textContent = bestSec !== null ? formatTime(bestSec) : 'N/A';

function formatSignedSeconds(diff) {
    if (diff === null || diff === undefined || isNaN(diff)) return '---';
    const s = Math.abs(diff).toFixed(2);
    // show + when worse (positive diff), - when improved (negative diff)
    const sign = diff > 0 ? '+' : (diff < 0 ? '-' : '+');
    // arrow: ▲ worse (slower), ▼ better (faster), = no change
    const arrow = diff > 0 ? '▲' : (diff < 0 ? '▼' : '＝');
    return `${arrow} ${sign}${s} 秒`;
}

// Helper: format a value with diff to current (nowSec)
function formatTimeWithDiff(v) {
    if (v === null || v === undefined || isNaN(v)) return '---';
    const base = formatTime(v) + ' 秒';
    if (typeof nowSec === 'number' && nowSec !== null) {
        const d = nowSec - v; // positive = now is slower (worse)
        return base + ' — ' + formatSignedSeconds(d);
    }
    return base;
}

if (elDiffPrev) {
    if (prevSec !== null && nowSec !== null) {
        const d = nowSec - prevSec;
        // show arrow + signed value, and add title for exact seconds
        elDiffPrev.innerHTML = '<span class="diff-arrow">' + formatSignedSeconds(d) + '</span>';
        elDiffPrev.title = (d < 0 ? '今回が速い（改善）' : (d > 0 ? '今回が遅い（悪化）' : '変化なし'));
        elDiffPrev.style.color = d < 0 ? '#2e7d32' : (d > 0 ? '#c62828' : '#333');
    } else {
        elDiffPrev.textContent = '---';
    }
}

if (elDiffBest) {
    if (bestSec !== null && nowSec !== null) {
        const d = nowSec - bestSec;
        elDiffBest.innerHTML = '<span class="diff-arrow">' + formatSignedSeconds(d) + '</span>';
    elDiffBest.title = (d < 0 ? '今回がベストより速い（更新）' : (d > 0 ? '今回がベストより遅い' : 'ベストと同等'));
        elDiffBest.style.color = d < 0 ? '#2e7d32' : (d > 0 ? '#c62828' : '#333');
    } else {
        elDiffBest.textContent = '---';
    }
}

    // PB 表示: 今回が自己ベストかどうかを表示
    if (elPbBadge) {
        if (nowSec !== null && bestSec !== null) {
            if (nowSec < bestSec) {
                elPbBadge.textContent = '自己ベスト！';
                elPbBadge.classList.add('is-pb');
                elPbBadge.classList.remove('is-tie');
            } else if (Math.abs(nowSec - bestSec) < 0.001) {
                // 同タイム
                elPbBadge.textContent = '自己ベスト(同タイム)';
                elPbBadge.classList.add('is-tie');
                elPbBadge.classList.remove('is-pb');
            } else {
                elPbBadge.textContent = '';
                elPbBadge.classList.remove('is-pb', 'is-tie');
            }
        } else {
            elPbBadge.textContent = '';
            elPbBadge.classList.remove('is-pb', 'is-tie');
        }
    }

/* =====================
   小チャート: 前回 vs 今回, PB vs 今回
===================== */
function renderSmallLineChart(canvasId, labels, data, color) {
    const c = document.getElementById(canvasId);
    if (!c) return null;
    const newCanvas = c.cloneNode(true);
    c.parentNode.replaceChild(newCanvas, c);
    // plugin: draw value labels above points
    const valueLabelPlugin = {
        id: 'valueLabels',
        afterDatasetsDraw: function(chart, args, options) {
            const ctx = chart.ctx;
            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                meta.data.forEach((element, index) => {
                    const val = dataset.data[index];
                    if (val === null || val === undefined) return;
                    const pos = element.tooltipPosition();
                    ctx.save();
                    ctx.fillStyle = '#333';
                    ctx.font = '12px Arial';
                    const label = formatTimeWithDiff(val);
                    const w = ctx.measureText(label).width;
                    ctx.fillText(label, pos.x - w/2, pos.y - 10);
                    ctx.restore();
                });
            });
        }
    };

    const yOpts = computeYAxisOptions(data.filter(v=>v!==null && v!==undefined));

    // If very few data points, don't force stepSize (Chart.js will choose sensible ticks)
    if (data.filter(v => v !== null && v !== undefined).length <= 2) {
        delete yOpts.stepSize;
    }

    // If all values are nearly identical, expand the range slightly so the line is visible
    const dataVals = data.filter(v => v !== null && v !== undefined);
    if (dataVals.length > 0) {
        let min = Math.min(...dataVals);
        let max = Math.max(...dataVals);
        if (Math.abs(max - min) < 0.01) {
            min = min - 0.5;
            max = max + 0.5;
            yOpts.min = Math.max(0, min);
            yOpts.max = max;
        }
    }

    // removed debug logs for production
    return new Chart(newCanvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: canvasId,
                data,
                borderColor: color,
                backgroundColor: color,
                tension: 0.2,
                fill: false,
                pointRadius: 6,
                spanGaps: false
            }]
        },
        plugins: [valueLabelPlugin],
        options: {
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx){ const v = ctx.raw; return (v === null || v === undefined) ? 'N/A' : formatTimeWithDiff(v); } } } },
            scales: { y: { beginAtZero: false, min: yOpts.min, max: yOpts.max, ticks: Object.assign({ callback: v => formatTime(v) }, (yOpts.stepSize ? { stepSize: yOpts.stepSize } : {})) } },
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// 小チャート: 横棒で表示するヘルパ
function renderSmallBarChart(canvasId, labels, data, color) {
    const c = document.getElementById(canvasId);
    if (!c) return null;
    const newCanvas = c.cloneNode(true);
    c.parentNode.replaceChild(newCanvas, c);

    // value label plugin: 数値を棒の右端に描画 (詳細版に置換済み)
        const valueLabelPlugin = {
            id: 'valueLabelsBar',
            afterDatasetsDraw: function(chart) {
                const ctx = chart.ctx;
                chart.data.datasets.forEach((dataset, datasetIndex) => {
                    const meta = chart.getDatasetMeta(datasetIndex);
                    meta.data.forEach((bar, index) => {
                        const val = dataset.data[index];
                        if (val === null || val === undefined) return;
                        // prepare two-line label: time on first line, diff on second
                        const timeLabel = formatTime(val);
                        const diff = (typeof nowSec === 'number' && nowSec !== null) ? (nowSec - val) : null;
                        const diffLabel = diff === null || isNaN(diff) ? '' : formatSignedSeconds(diff);
                        ctx.save();
                        ctx.font = '12px Arial';
                        // measure bar rectangle
                        const model = bar; // Chart.js v3+ elements expose x/y/width/height
                        const barLeft = model.x - (model.width || 0) / 2;
                        const barRight = model.x + (model.width || 0) / 2;
                        const barWidth = Math.max(0, (model.width || 0));
                        const y = model.y;
                        // attempt to draw inside bar if space, else draw to the right
                        const inside = barWidth > 80; // threshold: if wide enough
                        if (inside) {
                            // draw timeLabel in white inside bar, centered vertically
                            ctx.fillStyle = '#fff';
                            ctx.textAlign = 'left';
                            const px = barLeft + 8;
                            ctx.fillText(timeLabel, px, y - 2);
                            if (diffLabel) {
                                ctx.fillStyle = '#fff';
                                ctx.fillText(diffLabel, px, y + 12);
                            }
                        } else {
                            // draw outside in dark color
                            ctx.fillStyle = '#111';
                            ctx.textAlign = 'left';
                            const px = barRight + 8;
                            ctx.fillText(timeLabel, px, y - 2);
                            if (diffLabel) {
                                ctx.fillText(diffLabel, px, y + 12);
                            }
                        }
                        ctx.restore();
                    });
                });
            }
        };

    const xOpts = computeYAxisOptions(data.filter(v=>v!==null && v!==undefined));
    // for bar (horizontal), x axis is numeric
    if (data.filter(v => v !== null && v !== undefined).length <= 2) delete xOpts.stepSize;
    const dataVals = data.filter(v => v !== null && v !== undefined);
    if (dataVals.length > 0) {
        let min = Math.min(...dataVals);
        let max = Math.max(...dataVals);
        if (Math.abs(max - min) < 0.01) { min -= 0.5; max += 0.5; xOpts.min = Math.max(0, min); xOpts.max = max; }
    }

        return new Chart(newCanvas, {
        type: 'bar',
        data: { labels, datasets: [{ label: canvasId, data, backgroundColor: color, borderColor: color }] },
        plugins: [valueLabelPlugin],
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(ctx){ const v = ctx.raw; return (v === null || v === undefined) ? 'N/A' : formatTimeWithDiff(v); } } } },
            scales: { x: { min: xOpts.min, max: xOpts.max, ticks: Object.assign({ callback: v => formatTime(v) }, (xOpts.stepSize ? { stepSize: xOpts.stepSize } : {})) }, y: { ticks: { autoSkip: false } } },
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// 前回 vs 今回 (横棒)
if (document.getElementById('prevNowChart')) {
    const labelsPN = ['前回', '今回'];
    const dataPN = [ prevSec !== null ? prevSec : null, nowSec !== null ? nowSec : null ];
    renderSmallBarChart('prevNowChart', labelsPN, dataPN, '#1976d2');
}

// PB vs 今回 (横棒)
if (document.getElementById('bestNowChart')) {
    const labelsBN = ['ベスト', '今回'];
    const dataBN = [ bestSec !== null ? bestSec : null, nowSec !== null ? nowSec : null ];
    renderSmallBarChart('bestNowChart', labelsBN, dataBN, '#d32f2f');
}

/* =====================
   Chart.js 推移グラフ
===================== */
const chartCanvas = document.getElementById('timeChart');
if (!chartCanvas) {
    console.debug('timeChart canvas not found');
} else {
    const labels = Array.isArray(HISTORY) ? HISTORY.map(h => h.swim_date) : [];
    const times  = Array.isArray(HISTORY) ? HISTORY.map(h => {
        const v = parseTimeInput(h.total_time);
        return v === null ? null : v;
    }) : [];

    if (labels.length === 0) {
        // データ無し時は canvas を非表示にしてメッセージを表示
        chartCanvas.style.display = 'none';
        const p = document.createElement('p');
        p.textContent = '推移データがありません';
        chartCanvas.parentNode.insertBefore(p, chartCanvas);
    } else {
        const datasets = [
            {
                label: '記録',
                data: times,
                tension: 0.2,
                borderColor: '#1976d2',
                backgroundColor: '#1976d2',
                spanGaps: true,
                pointRadius: 3
            }
        ];

        if (bestSec !== null) {
            datasets.push({
                label: 'ベスト',
                data: labels.map(() => bestSec),
                borderDash: [5,5],
                borderColor: '#d32f2f',
                pointRadius: 0,
                fill: false
            });
        }

        const mainYOpts = computeYAxisOptions(times);

        // If very few points, let Chart.js choose stepSize
        if (times.filter(v => v !== null && v !== undefined).length <= 2) {
            delete mainYOpts.stepSize;
        }
        // If all values nearly identical, expand range slightly
        const mainVals = times.filter(v => v !== null && v !== undefined);
        if (mainVals.length > 0) {
            let min = Math.min(...mainVals);
            let max = Math.max(...mainVals);
            if (Math.abs(max - min) < 0.01) {
                min -= 0.5; max += 0.5;
                mainYOpts.min = Math.max(0, min);
                mainYOpts.max = max;
            }
        }

    // removed debug logs for production
    new Chart(chartCanvas, {
            type: 'line',
            data: {
                labels,
                datasets
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                        const v = context.raw;
                                        if (v === null || v === undefined) return 'データなし';
                                        return formatTimeWithDiff(v);
                                    }
                        }
                    }
                },
                scales: {
                    y: {
                        reverse: true,
                        min: mainYOpts.min,
                        max: mainYOpts.max,
                        ticks: Object.assign({ callback: v => formatTime(v) }, (mainYOpts.stepSize ? { stepSize: mainYOpts.stepSize } : {}))
                    }
                }
            }
        });
    }
}
