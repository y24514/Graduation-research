/* =====================
   時間フォーマット関数
===================== */
function parseTimeInput(v) {
    if (v === null || v === undefined) return null;
    if (typeof v === 'number') return v;
    if (typeof v === 'string') {
        v = v.trim();
        if (v === '') return null;
        if (v.indexOf(':') !== -1) {
            const parts = v.split(':').map(p => p.trim());
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
        }
        const f = parseFloat(v);
        return isNaN(f) ? null : f;
    }
    return null;
}

function formatTime(sec) {
    if (sec === null || sec === undefined || isNaN(sec)) return '---';
    const total = Number(sec);
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

function formatSignedSeconds(diff) {
    if (diff === null || diff === undefined || isNaN(diff)) return '---';
    const s = Math.abs(diff).toFixed(2);
    const sign = diff > 0 ? '+' : (diff < 0 ? '-' : '±');
    const arrow = diff > 0 ? '▲' : (diff < 0 ? '▼' : '＝');
    const colorClass = diff > 0 ? 'text-danger' : (diff < 0 ? 'text-success' : '');
    return `<span class="${colorClass}">${arrow} ${sign}${s}秒</span>`;
}

/* =====================
   データ処理
===================== */
// タイムデータを解析（条件分岐の外で定義）
const nowSec = parseTimeInput(NOW_TIME);
const prevSec = parseTimeInput(PREV_TIME);
const bestSec = parseTimeInput(BEST_TIME);

// 統計要素が存在する場合のみ処理（種目選択時）
const statBest = document.getElementById('stat-best');
const statAvg = document.getElementById('stat-avg');
const statImprovement = document.getElementById('stat-improvement');

if (statBest && statAvg && statImprovement) {
    // 統計情報の表示
    statBest.textContent = STATS.min ? formatTime(STATS.min) : '---';
    statAvg.textContent = STATS.avg ? formatTime(STATS.avg) : '---';
    statImprovement.innerHTML = STATS.improvement_rate !== null 
        ? `<span class="${STATS.improvement_rate > 0 ? 'text-success' : 'text-danger'}">${STATS.improvement_rate > 0 ? '+' : ''}${STATS.improvement_rate.toFixed(1)}%</span>`
        : '---';

    // 比較テーブル
    const elPrevNow = document.getElementById('prev-now');
    const elPrevThen = document.getElementById('prev-then');
    const elBestNow = document.getElementById('best-now');
    const elBestThen = document.getElementById('best-then');
    const elDiffPrev = document.getElementById('diff-prev');
    const elDiffBest = document.getElementById('diff-best');
    const elPbBadge = document.getElementById('pb-badge');

    if (elPrevNow) elPrevNow.textContent = nowSec !== null ? formatTime(nowSec) : '---';
    if (elPrevThen) elPrevThen.textContent = prevSec !== null ? formatTime(prevSec) : 'N/A';
    if (elBestNow) elBestNow.textContent = nowSec !== null ? formatTime(nowSec) : '---';
    if (elBestThen) elBestThen.textContent = bestSec !== null ? formatTime(bestSec) : 'N/A';

if (elDiffPrev && nowSec !== null && prevSec !== null) {
    elDiffPrev.innerHTML = formatSignedSeconds(nowSec - prevSec);
} else if (elDiffPrev) {
    elDiffPrev.textContent = '---';
}

    if (elDiffBest && nowSec !== null && bestSec !== null) {
        elDiffBest.innerHTML = formatSignedSeconds(nowSec - bestSec);
        if (nowSec < bestSec && elPbBadge) {
            elPbBadge.textContent = '🏆 NEW BEST!';
            elPbBadge.classList.add('is-pb');
        } else if (nowSec === bestSec && elPbBadge) {
            elPbBadge.textContent = '🥇 タイ記録';
            elPbBadge.classList.add('is-tie');
        }
    } else if (elDiffBest) {
        elDiffBest.textContent = '---';
    }
} // if (statBest && statAvg && statImprovement) の閉じ括弧

/* =====================
   全記録一覧テーブル
===================== */
const recordsTbody = document.getElementById('records-tbody');
if (recordsTbody && HISTORY.length > 0) {
    const bestTime = STATS.min;
    HISTORY.forEach((record, index) => {
        const row = document.createElement('tr');
        if (record.total_time === bestTime) {
            row.classList.add('record-best');
        }
        
        const conditionLabels = ['最悪', '悪い', '普通', '良い', '最高'];
        const conditionClass = `condition-${record.condition || 3}`;
        const conditionText = conditionLabels[(record.condition || 3) - 1] || '普通';
        
        row.innerHTML = `
            <td>${record.swim_date}</td>
            <td style="font-weight: 600;">${formatTime(record.total_time)}</td>
            <td><span class="condition-badge ${conditionClass}">${conditionText}</span></td>
            <td>${record.memo ? `<span class="record-memo">${record.memo}</span>` : '-'}</td>
        `;
        recordsTbody.appendChild(row);
    });
}

/* =====================
   Chart.js グラフ設定
===================== */
const chartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
        legend: { display: true, position: 'top' },
        tooltip: {
            callbacks: {
                label: function(context) {
                    return formatTime(context.parsed.y);
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: false,
            ticks: {
                callback: function(value) {
                    return formatTime(value);
                }
            }
        }
    }
};

/* =====================
   タイム推移グラフ
===================== */
const timeCtx = document.getElementById('timeChart');
if (timeCtx && HISTORY.length > 0) {
    const labels = HISTORY.map(r => r.swim_date);
    const data = HISTORY.map(r => r.total_time);
    
    new Chart(timeCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'タイム (秒)',
                data: data,
                borderColor: '#3182ce',
                backgroundColor: 'rgba(49, 130, 206, 0.1)',
                borderWidth: 3,
                tension: 0.3,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                ...chartOptions.plugins,
                title: {
                    display: true,
                    text: '記録の推移'
                }
            }
        }
    });
}

/* =====================
   前回 vs 今回
===================== */
const prevNowCtx = document.getElementById('prevNowChart');
if (prevNowCtx && prevSec !== null && nowSec !== null) {
    new Chart(prevNowCtx, {
        type: 'bar',
        data: {
            labels: ['前回', '今回'],
            datasets: [{
                label: 'タイム (秒)',
                data: [prevSec, nowSec],
                backgroundColor: [
                    'rgba(203, 213, 224, 0.7)',
                    nowSec < prevSec ? 'rgba(56, 161, 105, 0.7)' : 'rgba(229, 62, 62, 0.7)'
                ],
                borderColor: [
                    'rgba(203, 213, 224, 1)',
                    nowSec < prevSec ? 'rgba(56, 161, 105, 1)' : 'rgba(229, 62, 62, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                ...chartOptions.plugins,
                legend: { display: false }
            }
        }
    });
}

/* =====================
   ベスト vs 今回
===================== */
const bestNowCtx = document.getElementById('bestNowChart');
if (bestNowCtx && bestSec !== null && nowSec !== null) {
    new Chart(bestNowCtx, {
        type: 'bar',
        data: {
            labels: ['ベスト', '今回'],
            datasets: [{
                label: 'タイム (秒)',
                data: [bestSec, nowSec],
                backgroundColor: [
                    'rgba(212, 175, 55, 0.7)',
                    nowSec <= bestSec ? 'rgba(56, 161, 105, 0.7)' : 'rgba(203, 213, 224, 0.7)'
                ],
                borderColor: [
                    'rgba(212, 175, 55, 1)',
                    nowSec <= bestSec ? 'rgba(56, 161, 105, 1)' : 'rgba(203, 213, 224, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                ...chartOptions.plugins,
                legend: { display: false }
            }
        }
    });
}

/* =====================
   ペース分析 (100mあたり)
===================== */
const paceCtx = document.getElementById('paceChart');
if (paceCtx && HISTORY.length > 0 && DISTANCE) {
    const distance = parseFloat(DISTANCE);
    const paceData = HISTORY.map(r => {
        if (r.total_time && distance > 0) {
            return (r.total_time / distance) * 100; // 100mあたりの秒数
        }
        return null;
    }).filter(p => p !== null);
    
    if (paceData.length > 0) {
        new Chart(paceCtx, {
            type: 'bar',
            data: {
                labels: HISTORY.map(r => r.swim_date),
                datasets: [{
                    label: '100mペース (秒)',
                    data: paceData,
                    backgroundColor: 'rgba(214, 158, 46, 0.7)',
                    borderColor: 'rgba(214, 158, 46, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                    title: {
                        display: true,
                        text: `100mあたりのペース推移 (${DISTANCE}m種目)`
                    }
                }
            }
        });
    }
}
