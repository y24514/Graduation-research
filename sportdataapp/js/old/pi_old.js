//　フォーム入力時のクリックイベント

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('piform');
    form.addEventListener('submit', (e) => {
        const height = form.height.value.trim();
        const weight = form.weight.value.trim();
        const sleeptime = form.sleep_time.value.trim();

        if (height === '' || weight === '' || sleeptime === ''){
            e.preventDefault();
            alert('必須項目を入力してください');
            return false;
        }

        if(height <= 0 || weight <= 0 ){
            e.preventDefault();
            alert('身長と体重の値が不正な入力です。');
            return false;
        }
    })
})

// 取得した記録を表示する
let heights = records.map(r => Number(r.height));
let weights = records.map(r => Number(r.weight));
let BMIs = records.map(r => {
    let h = Number(r.height) / 100;
    return Number(Number(r.weight) / (h*h)).toFixed(1);
})
let sleep_times = records.map(r => {
    if (!r.sleep_time) return 0;

    const parts = r.sleep_time.split(':');
    const h = Number(parts[0]);
    const m = Number(parts[1]);
    const s = Number(parts[2]);

    return h + m / 60 + s / 3600; 
});

let injurys = records.map(r => r.injury);
let create_ats = records.map(r => r.create_at);

console.log(heights);
console.log(weights);
console.log(BMIs);
console.log(sleep_times);
console.log(injurys);
console.log(create_ats);

// グラフ描画
const graph1 = document.querySelector('.graph.height-weight-bmi');
let chart1 = {
    type: 'line',
    data: {
        labels: create_ats,
        datasets: [{
            label : '身長(cm)',
            data : heights,
            borderColor: 'rgba(255, 0, 0, 1)',
        },{
            label : '体重(kg)',
            data : weights,
            borderColor: 'rgba(0, 8, 255, 1)',
        },{
            label : 'BMI',
            data : BMIs,
            borderColor: 'rgba(255, 242, 0, 1)',
        }]
    },
    options: {
        scales: {
            y: {
                min: 0,
                max: 250,
            }
        },
        responsive: false,
    }
}

let lineChart1 = new Chart(graph1, chart1);

const graph2 = document.querySelector('.graph.sleep');
let chart2 = {
    type: 'line',
    data: {
        labels: create_ats,
        datasets: [{
            label : '睡眠時間',
            data : sleep_times,
            borderColor: 'rgba(255,0,0,1)',
        }]
    },
    options: {
        responsive: false,
    },
}

let lineChart2 = new Chart(graph2, chart2);