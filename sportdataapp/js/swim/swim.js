// ストローク追加
function updateStrokeFields() {
    const distance = Number(document.getElementById("distance").value);
    const poolType = document.getElementById("pool_type").value;
    const area = document.getElementById("stroke_area");

    if (!distance || !poolType) return;

    const interval = poolType === "short" ? 25 : 50;
    const total = distance / interval;

    area.innerHTML = `
        <label>0〜${interval} のストローク回数</label>
        <input type="number" name="stroke_${interval}" min="0" max="200" required>
    `;

    for (let i = 2; i <= total; i++) {
        const start = (i - 1) * interval;
        const end = i * interval;

        area.innerHTML += `
            <h4>${start}〜${end}m のストローク回数</h4>
            <input type="number" name="stroke_${end}" min="0" max="200" required>
        `;
    }
}

// ラップ追加
function updateLapFields() {
    const distance = Number(document.getElementById("distance").value);
    const poolType = document.getElementById("pool_type").value;
    const area = document.getElementById("lap_area");

    if (!distance || !poolType) return;

    const interval = poolType === "short" ? 25 : 50;
    const total = distance / interval;

    area.innerHTML = `
        <label>0〜${interval} のラップタイム</label>
        <input type="text" name="lap_${interval}" placeholder="00:14.32" required>
    `;

    for (let i = 2; i <= total; i++) {
        const start = (i - 1) * interval;
        const end = i * interval;

        area.innerHTML += `
            <h4>${start}〜${end}m のラップタイム</h4>
            <input type="text" name="lap_${end}" placeholder="00:30.12" required>
        `;
    }
}
