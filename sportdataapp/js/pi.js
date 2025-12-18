/* ==========================================
   身体情報ページ - JavaScript
========================================== */

// Chart.jsのデフォルト設定
Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.1)';
Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

/* ==========================================
   BMI計算機のリアルタイム更新
========================================== */
const heightInput = document.getElementById('height');
const weightInput = document.getElementById('weight');
const currentBMI = document.getElementById('current-bmi');

function calculateBMI() {
    const height = parseFloat(heightInput.value);
    const weight = parseFloat(weightInput.value);
    
    if (height > 0 && weight > 0) {
        const bmi = weight / ((height / 100) ** 2);
        currentBMI.textContent = bmi.toFixed(1);
        
        // BMIに応じて色を変更
        currentBMI.style.color = getBMIColor(bmi);
    } else {
        currentBMI.textContent = '--';
        currentBMI.style.color = '#d69e2e';
    }
}

function getBMIColor(bmi) {
    if (bmi < 18.5) return '#63b3ed'; // 低体重
    if (bmi < 25) return '#68d391';   // 普通
    if (bmi < 30) return '#f6ad55';   // 肥満(1度)
    return '#fc8181';                  // 肥満(2度以上)
}

heightInput.addEventListener('input', calculateBMI);
weightInput.addEventListener('input', calculateBMI);

// ページロード時に計算
window.addEventListener('DOMContentLoaded', calculateBMI);

/* ==========================================
   データの準備
========================================== */
// レコードを新しい順にソート（グラフ表示用に古い順に変換）
const sortedRecords = [...recordsData].reverse();

// 日付ラベルの作成
const dateLabels = sortedRecords.map(record => {
    const date = new Date(record.create_at);
    return `${date.getMonth() + 1}/${date.getDate()}`;
});

// 体重データ
const weightData = sortedRecords.map(record => record.weight);

// BMIデータ
const bmiData = sortedRecords.map(record => record.bmi || null);

// 睡眠時間データ（時間形式から分に変換）
const sleepData = sortedRecords.map(record => {
    if (!record.sleep_time) return null;
    const [hours, minutes] = record.sleep_time.split(':').map(Number);
    return hours + (minutes / 60);
});

/* ==========================================
   グラフ1: 体重・BMIの推移
========================================== */
const weightChartCtx = document.getElementById('weight-chart');
if (weightChartCtx && sortedRecords.length > 0) {
    new Chart(weightChartCtx, {
        type: 'line',
        data: {
            labels: dateLabels,
            datasets: [
                {
                    label: '体重 (kg)',
                    data: weightData,
                    borderColor: '#3182ce',
                    backgroundColor: 'rgba(49, 130, 206, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'BMI',
                    data: bmiData,
                    borderColor: '#ed8936',
                    backgroundColor: 'rgba(237, 137, 54, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 12, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.95)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#e2e8f0',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toFixed(1);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: '体重 (kg)',
                        font: { size: 12, weight: '600' }
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'BMI',
                        font: { size: 12, weight: '600' }
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

/* ==========================================
   グラフ2: 睡眠時間の推移
========================================== */
const sleepChartCtx = document.getElementById('sleep-chart');
if (sleepChartCtx && sortedRecords.length > 0) {
    // 推奨睡眠時間の範囲（7-9時間）
    const recommendedMin = new Array(dateLabels.length).fill(7);
    const recommendedMax = new Array(dateLabels.length).fill(9);
    
    new Chart(sleepChartCtx, {
        type: 'bar',
        data: {
            labels: dateLabels,
            datasets: [
                {
                    label: '睡眠時間',
                    data: sleepData,
                    backgroundColor: sleepData.map(val => {
                        if (!val) return 'rgba(148, 163, 184, 0.5)';
                        if (val >= 7 && val <= 9) return 'rgba(56, 161, 105, 0.6)';
                        if (val >= 6 && val < 7) return 'rgba(237, 137, 54, 0.6)';
                        return 'rgba(229, 62, 62, 0.6)';
                    }),
                    borderColor: sleepData.map(val => {
                        if (!val) return '#94a3b8';
                        if (val >= 7 && val <= 9) return '#38a169';
                        if (val >= 6 && val < 7) return '#ed8936';
                        return '#e53e3e';
                    }),
                    borderWidth: 2,
                    borderRadius: 6
                },
                {
                    label: '推奨範囲（下限）',
                    data: recommendedMin,
                    type: 'line',
                    borderColor: 'rgba(56, 161, 105, 0.5)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    pointRadius: 0
                },
                {
                    label: '推奨範囲（上限）',
                    data: recommendedMax,
                    type: 'line',
                    borderColor: 'rgba(56, 161, 105, 0.5)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 12, weight: '600' },
                        filter: function(item, chart) {
                            // 推奨範囲の凡例を1つにまとめる
                            return !item.text.includes('上限');
                        },
                        generateLabels: function(chart) {
                            const original = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                            original.forEach(label => {
                                if (label.text.includes('下限')) {
                                    label.text = '推奨範囲 (7-9時間)';
                                }
                            });
                            return original.filter(label => !label.text.includes('上限'));
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.95)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#e2e8f0',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label.includes('推奨')) {
                                return null;
                            }
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                const hours = Math.floor(context.parsed.y);
                                const minutes = Math.round((context.parsed.y - hours) * 60);
                                label += `${hours}時間${minutes}分`;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    beginAtZero: true,
                    max: 12,
                    title: {
                        display: true,
                        text: '時間',
                        font: { size: 12, weight: '600' }
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + 'h';
                        }
                    }
                }
            }
        }
    });
}

/* ==========================================
   グラフ3: 体重分布（ヒストグラム風）
========================================== */
const weightDistCtx = document.getElementById('weight-distribution');
if (weightDistCtx && sortedRecords.length > 0) {
    // 体重の範囲を計算
    const minWeight = Math.min(...weightData);
    const maxWeight = Math.max(...weightData);
    const range = maxWeight - minWeight;
    
    // ビンの数を決定（最大10個）
    const binCount = Math.min(10, Math.ceil(range / 0.5));
    const binSize = range / binCount;
    
    // ビンを作成
    const bins = [];
    const binLabels = [];
    for (let i = 0; i < binCount; i++) {
        const binStart = minWeight + (i * binSize);
        const binEnd = binStart + binSize;
        binLabels.push(`${binStart.toFixed(1)}-${binEnd.toFixed(1)}`);
        
        // このビンに入る体重の数をカウント
        const count = weightData.filter(w => w >= binStart && w < binEnd).length;
        bins.push(count);
    }
    
    // 最後のビンに最大値を含める
    if (weightData.includes(maxWeight)) {
        bins[bins.length - 1]++;
    }
    
    new Chart(weightDistCtx, {
        type: 'bar',
        data: {
            labels: binLabels,
            datasets: [{
                label: '記録回数',
                data: bins,
                backgroundColor: 'rgba(139, 92, 246, 0.6)',
                borderColor: '#8b5cf6',
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 12, weight: '600' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.95)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#e2e8f0',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return `記録回数: ${context.parsed.y}回`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: '体重範囲 (kg)',
                        font: { size: 12, weight: '600' }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: '回数',
                        font: { size: 12, weight: '600' }
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)'
                    },
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value + '回';
                        }
                    }
                }
            }
        }
    });
}

/* ==========================================
   データがない場合のメッセージ表示
========================================== */
if (sortedRecords.length === 0) {
    const noDataMessage = '<div style="text-align: center; padding: 2rem; color: #94a3b8;">データを登録するとグラフが表示されます</div>';
    
    if (weightChartCtx) {
        weightChartCtx.parentElement.innerHTML = noDataMessage;
    }
    if (sleepChartCtx) {
        sleepChartCtx.parentElement.innerHTML = noDataMessage;
    }
    if (weightDistCtx) {
        weightDistCtx.parentElement.innerHTML = noDataMessage;
    }
}

/* ==========================================
   フォーム送信前のバリデーション
========================================== */
const piForm = document.querySelector('.pi-form');
if (piForm) {
    piForm.addEventListener('submit', function(e) {
        const height = parseFloat(heightInput.value);
        const weight = parseFloat(weightInput.value);
        
        // 現実的な範囲チェック
        if (height < 100 || height > 250) {
            e.preventDefault();
            alert('身長は100cm〜250cmの範囲で入力してください');
            return false;
        }
        
        if (weight < 30 || weight > 200) {
            e.preventDefault();
            alert('体重は30kg〜200kgの範囲で入力してください');
            return false;
        }
        
        return true;
    });
}

console.log('身体情報ページ初期化完了:', {
    記録数: recordsData.length,
    平均体重: weightData.length > 0 ? (weightData.reduce((a, b) => a + b, 0) / weightData.length).toFixed(1) + 'kg' : 'データなし',
    平均睡眠: sleepData.filter(v => v).length > 0 ? (sleepData.filter(v => v).reduce((a, b) => a + b, 0) / sleepData.filter(v => v).length).toFixed(1) + 'h' : 'データなし'
});
