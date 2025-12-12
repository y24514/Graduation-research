function updateLapInputs() {
    const distance = Number(document.getElementById("distance").value);
    const poolType = document.getElementById("pool_type").value;
    const lapContainer = document.getElementById("lap_time_area");

    if (!distance || !poolType) return;

    const lapSize = poolType === "short" ? 25 : 50;
    const lapCount = distance / lapSize;

    // 最初の区間タイトルと name をプールタイプに合わせて変更
    lapContainer.innerHTML = `
        <label>ラップタイム</label><br>
        <h4 id="base-lap-title">0〜${lapSize}m のラップタイム</h4>
        <input type="text" id="base-lap" name="lap_time_${lapSize}" 
               placeholder="例: 15.23" pattern="\\d{1,2}\\.\\d{1,2}" required><br>
    `;

    // 2区間目以降を追加
    for (let i = 2; i <= lapCount; i++) {
        const start = (i - 1) * lapSize;
        const end = i * lapSize;

        const h4 = document.createElement("h4");
        h4.textContent = `${start}〜${end}m のラップタイム`;

        const input = document.createElement("input");
        input.type = "text";
        input.name = `lap_time_${end}`;
        input.placeholder = "例: 15.23";
        input.pattern = "\\d{1,2}\\.\\d{1,2}";
        input.required = true;

        lapContainer.appendChild(h4);
        lapContainer.appendChild(input);
        lapContainer.appendChild(document.createElement("br"));
    }
}

// イベント設定
document.getElementById("pool_type").addEventListener("change", updateLapInputs);
document.getElementById("distance").addEventListener("change", updateLapInputs);
