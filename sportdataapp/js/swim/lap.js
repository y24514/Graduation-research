document.getElementById("pool_type").addEventListener("change", updateLapInputs);
document.getElementById("distance").addEventListener("change", updateLapInputs);

function updateLapInputs() {
    const distance = Number(document.getElementById("distance").value);
    const poolType = document.getElementById("pool_type").value;
    const lapContainer = document.getElementById("lap_time_area");

    lapContainer.innerHTML = "";

    if (!distance || !poolType) return;

    const lapsize = poolType === "short" ? 25 : 50;
    const lapCount = distance / lapsize;

    for (let i = 1; i <= lapCount; i++) {
        const start = (i - 1) * lapsize;
        const end = i * lapsize;

        // ラベル
        const h4 = document.createElement("h4");
        h4.textContent = `${start}〜${end}m のラップタイム`;
        h4.style.display = "block";

        //input
        const input = document.createElement("input");
        input.type = "text";  
        input.name = `lap_time_${end}`;
        input.placeholder = "例: 15.23（秒）";
        input.pattern = "\\d{1,2}\\.\\d{1,2}";

        lapContainer.appendChild(h4);
        lapContainer.appendChild(input);
        lapContainer.appendChild(document.createElement("br"));
    }
}
