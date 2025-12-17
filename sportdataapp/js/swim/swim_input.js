document.addEventListener("DOMContentLoaded", () => {

    /* =====================
       入力欄生成
    ===================== */
    function updateSwimInputs() {
        const distance = Number(document.getElementById("distance").value);
        const poolType = document.getElementById("pool_type").value;

        const strokeArea = document.getElementById("stroke_area");
        const lapArea = document.getElementById("lap_time_area");

        if (!distance || !poolType) return;

        const intervalSize = poolType === "short" ? 25 : 50;
        const intervals = distance / intervalSize;

        /* ===== ストローク ===== */
        strokeArea.innerHTML = `<label>ストローク回数</label><br>`;
        for (let i = 1; i <= intervals; i++) {
            const end = i * intervalSize;
            strokeArea.innerHTML += `
                <h4>${end - intervalSize}〜${end}m</h4>
                <input type="number" name="stroke_${end}" min="0" max="200" required><br>
            `;
        }

        /* ===== ラップ ===== */
        lapArea.innerHTML = `<label>ラップタイム</label><br>`;
        for (let i = 1; i <= intervals; i++) {
            const end = i * intervalSize;
            lapArea.innerHTML += `
                <h4>${end - intervalSize}〜${end}m</h4>
                <input type="text"
                       name="lap_time_${end}"
                       placeholder="例: 15.23"
                       pattern="\\d{1,2}\\.\\d{1,2}"
                       required
                       class="lap-input"><br>
            `;
        }

        attachLapListeners();
        syncScrollAreas();
        resetTimeDisplay();
        setTimeout(syncFormHeights, 0);
    }

    /* =====================
       フォーム高さ同期
    ===================== */
    function syncFormHeights() {
        const basic = document.querySelector('.form-basic');
        const stroke = document.querySelector('.form-stroke');
        const lap = document.querySelector('.form-lap');

        if (!basic || !stroke || !lap) return;

        const h = basic.offsetHeight;
        stroke.style.height = h + 'px';
        lap.style.height = h + 'px';
    }

    /* =====================
       スクロール同期
    ===================== */
    function syncScrollAreas() {
        const strokeArea = document.getElementById("stroke_area");
        const lapArea = document.getElementById("lap_time_area");

        if (!strokeArea || !lapArea) return;

        // ストロークをスクロールしたらラップも同期
        strokeArea.addEventListener("scroll", () => {
            lapArea.scrollTop = strokeArea.scrollTop;
        });

        // ラップをスクロールしたらストロークも同期
        lapArea.addEventListener("scroll", () => {
            strokeArea.scrollTop = lapArea.scrollTop;
        });
    }

    /* =====================
       ラップ監視
    ===================== */
    function attachLapListeners() {
        document.querySelectorAll(".lap-input").forEach(input => {
            input.addEventListener("input", calculateTimes);
        });
    }

    /* =====================
       合計タイム計算
    ===================== */
    function calculateTimes() {
        const laps = [];
        document.querySelectorAll(".lap-input").forEach(input => {
            const v = parseFloat(input.value);
            if (!isNaN(v)) laps.push(v);
        });

        if (laps.length === 0) return;

        const total = laps.reduce((a, b) => a + b, 0);
        document.getElementById("time").value = formatTime(total);
        document.getElementById("total_time").value = total.toFixed(2);
    }

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = (sec % 60).toFixed(2).padStart(5, "0");
        return m > 0 ? `${m}:${s}` : s;
    }

    function resetTimeDisplay() {
        document.getElementById("time").value = "";
        document.getElementById("total_time").value = "0.00";
    }

    /* =====================
       イベント登録
    ===================== */
    document.getElementById("pool_type").addEventListener("change", updateSwimInputs);
    document.getElementById("event").addEventListener("change", updateSwimInputs);
    document.getElementById("distance").addEventListener("change", updateSwimInputs);

    /* =====================
       初期表示時の高さ同期
    ===================== */
    window.addEventListener('load', syncFormHeights);
});
