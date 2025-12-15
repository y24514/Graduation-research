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

    /* ===== ストローク入力生成 ===== */
    strokeArea.innerHTML = `<label>ストローク回数</label><br>`;
    for (let i = 1; i <= intervals; i++) {
        const end = i * intervalSize;
        strokeArea.innerHTML += `
            <h4>${end - intervalSize}〜${end}m</h4>
            <input type="number" name="stroke_${end}" min="0" max="200" required><br>
        `;
    }

    /* ===== ラップ入力生成 ===== */
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
    updateBestDiff(0); // 条件変更時もPB表示更新
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
   計算ロジック
===================== */
function calculateTimes() {
    const lapInputs = document.querySelectorAll(".lap-input");
    let laps = [];

    lapInputs.forEach(input => {
        const v = parseFloat(input.value);
        if (!isNaN(v)) laps.push(v);
    });

    if (laps.length === 0) return;

    const total = laps.reduce((a, b) => a + b, 0);
    const half = Math.floor(laps.length / 2);
    const firstHalf = laps.slice(0, half).reduce((a, b) => a + b, 0);
    const secondHalf = laps.slice(half).reduce((a, b) => a + b, 0);

    document.getElementById("first-half").textContent = firstHalf.toFixed(2);
    document.getElementById("second-half").textContent = secondHalf.toFixed(2);
    document.getElementById("half-diff").textContent = (secondHalf - firstHalf).toFixed(2);

    document.getElementById("time").value = formatTime(total);
    document.getElementById("total_time").value = total.toFixed(2);

    updateBestDiff(total);
    updateCompareTable(total);
}

/* =====================
   現在条件の自己ベスト取得
===================== */
function getCurrentBestTime() {
    if (!BEST_LIST) return null;

    const pool = document.getElementById("pool_type").value;
    const event = document.getElementById("event").value;
    const distance = document.getElementById("distance").value;

    const best = BEST_LIST.find(b =>
        b.pool === pool &&
        b.event === event &&
        String(b.distance) === String(distance)
    );

    return best ? parseFloat(best.best_time) : null;
}

/* =====================
   PB差分表示
===================== */
function updateBestDiff(totalSec) {
    let pbDiffEl = document.getElementById("pb-diff");
    if (!pbDiffEl) {
        pbDiffEl = document.createElement("span");
        pbDiffEl.id = "pb-diff";
        const parent = document.querySelector(".analysis");
        parent.insertAdjacentElement("afterbegin", pbDiffEl);
    }

    const bestTime = getCurrentBestTime();

    if (!bestTime || totalSec <= 0) {
        pbDiffEl.textContent = "---";
        pbDiffEl.style.color = "black";
        return;
    }

    const diff = totalSec - bestTime;
    if (diff < 0) {
        pbDiffEl.textContent = `${diff.toFixed(2)} 秒`;
        pbDiffEl.style.color = "red";
    } else {
        pbDiffEl.textContent = `+${diff.toFixed(2)} 秒`;
        pbDiffEl.style.color = "blue";
    }
}

/* =====================
   秒 → 分:秒
===================== */
function formatTime(sec) {
    const m = Math.floor(sec / 60);
    const s = (sec % 60).toFixed(2).padStart(5, "0");
    return m > 0 ? `${m}:${s}` : s;
}

/* =====================
   自己ベスト一覧表示
===================== */
function renderBestList() {
    const tbody = document.getElementById("best-list-body");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (!BEST_LIST || BEST_LIST.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4">記録なし</td></tr>`;
        return;
    }

    BEST_LIST.forEach(b => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${b.pool === "short" ? "短水路" : "長水路"}</td>
            <td>${eventName(b.event)}</td>
            <td>${b.distance}m</td>
            <td>${formatTime(parseFloat(b.best_time))}</td>
        `;
        tbody.appendChild(tr);
    });
}

/* 種目コード → 名称変換 */
function eventName(code) {
    const map = {
        fly: "バタフライ",
        ba: "背泳ぎ",
        br: "平泳ぎ",
        fr: "自由形",
        im: "個人メドレー"
    };
    return map[code] || code;
}

/* =====================
   比較テーブル更新
===================== */
function updateCompareTable(totalSec) {
    const best = getCurrentBestTime();
    const prev = PREV_TIME;

    document.getElementById("current-time").textContent = formatTime(totalSec);
    document.getElementById("current-time-best").textContent = formatTime(totalSec);

    document.getElementById("prev-time").textContent = prev !== null ? formatTime(prev) : "---";
    document.getElementById("best-time").textContent = best !== null ? formatTime(best) : "---";

    document.getElementById("diff-prev").textContent =
        prev !== null ? (totalSec - prev).toFixed(2) + " 秒" : "---";
    document.getElementById("diff-best").textContent =
        best !== null ? (totalSec - best).toFixed(2) + " 秒" : "---";
}

/* =====================
   イベント登録
===================== */
document.getElementById("pool_type").addEventListener("change", updateSwimInputs);
document.getElementById("event").addEventListener("change", updateSwimInputs);
document.getElementById("distance").addEventListener("change", updateSwimInputs);

document.addEventListener("DOMContentLoaded", () => {
    renderBestList();

    const initialTotal = 0;
    document.getElementById("total_time").value = initialTotal.toFixed(2);
    updateBestDiff(initialTotal);
    updateCompareTable(initialTotal);
});
