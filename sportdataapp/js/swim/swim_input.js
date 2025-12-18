document.addEventListener("DOMContentLoaded", () => {

    /* =====================
       種目・距離選択ボタン
    ===================== */
    const distanceButtons = document.querySelectorAll('.distance-btn');
    const eventInput = document.getElementById('event');
    const distanceInput = document.getElementById('distance');

    distanceButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // すべてのボタンから選択を外す
            distanceButtons.forEach(b => b.classList.remove('selected'));
            
            // クリックしたボタンを選択状態に
            this.classList.add('selected');
            
            // 隠しフィールドに値を設定
            const event = this.dataset.event;
            const distance = this.dataset.distance;
            
            eventInput.value = event;
            distanceInput.value = distance;
            
            // 入力欄を更新
            updateSwimInputs();
        });
    });

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
        document.getElementById("time").textContent = formatTime(total);
        document.getElementById("total_time").value = total.toFixed(2);
        
        // ベストタイムと比較して色を変える
        checkBestTime(total);
    }
    
    /* =====================
       ベストタイム判定
    ===================== */
    function checkBestTime(currentTime) {
        const timeDisplay = document.getElementById("time");
        const pool = document.getElementById("pool_type").value;
        const event = document.getElementById("event").value;
        const distance = document.getElementById("distance").value;
        
        if (!pool || !event || !distance) {
            timeDisplay.classList.remove('best-time');
            return;
        }
        
        const key = pool + '|' + event + '|' + distance;
        const bestTime = bestTimes[key];
        
        if (bestTime === undefined || currentTime < bestTime) {
            // ベストまたは新記録
            timeDisplay.classList.add('best-time');
        } else {
            timeDisplay.classList.remove('best-time');
        }
    }

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = (sec % 60).toFixed(2).padStart(5, "0");
        return m > 0 ? `${m}:${s}` : s;
    }

    function resetTimeDisplay() {
        document.getElementById("time").textContent = "00:00.00";
        document.getElementById("total_time").value = "0.00";
    }

    /* =====================
       イベント登録
    ===================== */
    document.getElementById("pool_type").addEventListener("change", updateSwimInputs);
    // event と distance はボタンで処理されるため、changeイベントは不要

    /* =====================
       公式戦チェックボックスの表示切り替え
    ===================== */
    const officialCheckbox = document.getElementById("is_official");
    const officialFields = document.getElementById("official_fields");
    const meetNameInput = document.getElementById("meet_name");
    const roundSelect = document.getElementById("round");

    if (officialCheckbox && officialFields) {
        officialCheckbox.addEventListener("change", function() {
            if (this.checked) {
                officialFields.style.display = "block";
                meetNameInput.required = true;
                roundSelect.required = true;
            } else {
                officialFields.style.display = "none";
                meetNameInput.required = false;
                roundSelect.required = false;
                meetNameInput.value = "";
            }
        });
    }

    /* =====================
       初期表示時の高さ同期
    ===================== */
    window.addEventListener('load', syncFormHeights);
});
