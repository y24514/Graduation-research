// ストローク欄を生成
function updateStrokeFields() {
    const distance = Number(document.getElementById("distance").value);
    const poolType = document.getElementById("pool_type").value;
    const strokeArea = document.getElementById("stroke_area");
    strokeArea.innerHTML = "";
    if (!distance || !poolType) return;

    const intervalSize = poolType === "short" ? 25 : 50;
    const intervals = distance / intervalSize;

    for (let i = 1; i <= intervals; i++) {
        const start = (i-1)*intervalSize;
        const end = i*intervalSize;

        const label = document.createElement("label");
        label.textContent = `${start}〜${end}m のストローク回数`;
        label.style.display = "block";

        const input = document.createElement("input");
        input.type = "number";
        input.name = `stroke_${end}`;
        input.min = 0; input.max = 200; input.step = 1;

        strokeArea.appendChild(label);
        strokeArea.appendChild(input);
        strokeArea.appendChild(document.createElement("br"));
    }
}

// ラップタイム欄を生成
function updateLapInputs() {
    const distance = Number(document.getElementById("distance").value);
    const poolType = document.getElementById("pool_type").value;
    const lapContainer = document.getElementById("lap_time_area");
    lapContainer.innerHTML = "";
    if (!distance || !poolType) return;

    const lapsize = poolType === "short" ? 25 : 50;
    const lapCount = distance / lapsize;

    for (let i = 1; i <= lapCount; i++) {
        const start = (i-1)*lapsize;
        const end = i*lapsize;

        const label = document.createElement("label");
        label.textContent = `${start}〜${end}m のラップタイム`;
        label.style.display = "block";

        const input = document.createElement("input");
        input.type = "text";
        input.name = `lap_time_${end}`;
        input.placeholder = "例: 15.23（秒）";
        input.pattern = "\\d{1,2}\\.\\d{1,2}";

        lapContainer.appendChild(label);
        lapContainer.appendChild(input);
        lapContainer.appendChild(document.createElement("br"));
    }
}

// イベント設定
document.getElementById("pool_type").addEventListener("change", updateStrokeFields);
document.getElementById("distance").addEventListener("change", updateStrokeFields);
document.getElementById("pool_type").addEventListener("change", updateLapInputs);
document.getElementById("distance").addEventListener("change", updateLapInputs);
