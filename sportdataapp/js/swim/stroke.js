function updateStrokeFields() {
    const distance = Number(document.getElementById("distance").value);
    const poolType = document.getElementById("pool_type").value;
    const strokeArea = document.getElementById("stroke_area");

    strokeArea.innerHTML = ""; // 初期化

    if (!distance || !poolType) return; // 両方選ばれるまで何もしない

    const intervalSize = poolType === "short" ? 25 : 50; // 25m or 50m
    const intervals = distance / intervalSize;

    for (let i = 1; i <= intervals; i++) {
        const start = (i - 1) * intervalSize;
        const end = i * intervalSize;

        // ラベル
        const h4 = document.createElement("h4");
        h4.textContent = `${start}〜${end}m のストローク回数`;
        h4.style.display = "block";

        // input
        const input = document.createElement("input");
        input.type = "number";
        input.name = `stroke_${end}`;
        input.min = 0;
        input.max = 200;
        input.step = 1;

        strokeArea.appendChild(h4);
        strokeArea.appendChild(input);
        strokeArea.appendChild(document.createElement("br"));
    }
}

// プール種類・距離が変わったら更新
document.getElementById("pool_type").addEventListener("change", updateStrokeFields);
document.getElementById("distance").addEventListener("change", updateStrokeFields);
