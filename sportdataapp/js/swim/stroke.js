function updateStrokeFields() {
    const distance = Number(document.getElementById("distance").value);
    const poolType = document.getElementById("pool_type").value;
    const strokeArea = document.getElementById("stroke_area");

    if (!distance || !poolType) return;

    const intervalSize = poolType === "short" ? 25 : 50;
    const intervals = distance / intervalSize;

    // 常に最初のブロックを作る
    strokeArea.innerHTML = `
        <label>ストローク回数</label><br>
        <h4>0〜${intervalSize}m のストローク回数</h4>
        <input type="number" name="stroke_${intervalSize}" min="0" max="200" required><br>
    `;

    // 2区間目以降だけ追加
    for (let i = 2; i <= intervals; i++) {
        const start = (i - 1) * intervalSize;
        const end = i * intervalSize;

        const h4 = document.createElement("h4");
        h4.textContent = `${start}〜${end}m のストローク回数`;

        const input = document.createElement("input");
        input.type = "number";
        input.name = `stroke_${end}`;
        input.min = 0;
        input.max = 200;
        input.required = true;

        strokeArea.appendChild(h4);
        strokeArea.appendChild(input);
        strokeArea.appendChild(document.createElement("br"));
    }
}

document.getElementById("pool_type").addEventListener("change", updateStrokeFields);
document.getElementById("distance").addEventListener("change", updateStrokeFields);
