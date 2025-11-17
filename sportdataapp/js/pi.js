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

console.log(records);
const graph1 = document.querySelector('.graph height-weight-bmi');
let heights = records.map(r => r.heigh);
let weights = records.map(r => r.weight);

let BMis = records.map(r => {
    let h = r.heigh / 100;
    return (r.weight / (h*h)).toFixed(1);
})

let sleep_times = records.map(r => r.sleep_time);
let injurys = records.map(r => r.injury);
let create_ats = records.map(r => r.create_at);

// グラフ描画