document.addEventListener("DOMContentLoaded", () => {
    const calendarArea = document.getElementById("calendar-area");

    // 今日の年月を取得
    const today = new Date();
    let year = today.getFullYear();
    let month = today.getMonth();

    // カレンダー描画関数
    function renderCalendar(year, month) {
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDay = firstDay.getDay();
        const totalDays = lastDay.getDate();

        let html = `<div class="calendar-header">
                        <button id="prev-month">&lt;</button>
                        <span>${year}年${month + 1}月</span>
                        <button id="next-month">&gt;</button>
                    </div>`;
        html += `<table><tr>`;
        const week = ["日","月","火","水","木","金","土"];
        for (let d of week) html += `<th>${d}</th>`;
        html += `</tr><tr>`;

        // 空セル
        for (let i = 0; i < startDay; i++) html += "<td></td>";

        // 日付を描画
        for (let day = 1; day <= totalDays; day++) {
            const date = new Date(year, month, day);
            const dateStr = date.toISOString().split("T")[0];
            html += `<td class="day" data-date="${dateStr}">${day}</td>`;
            if ((startDay + day) % 7 === 0) html += "</tr><tr>";
        }

        html += "</tr></table>";
        calendarArea.innerHTML = html;

        // 月移動イベント
        document.getElementById("prev-month").onclick = () => {
            month--;
            if (month < 0) { month = 11; year--; }
            renderCalendar(year, month);
        };
        document.getElementById("next-month").onclick = () => {
            month++;
            if (month > 11) { month = 0; year++; }
            renderCalendar(year, month);
        };

        // 日付クリックでスケジュール登録
        document.querySelectorAll(".day").forEach(td => {
            td.onclick = () => {
                const date = td.dataset.date;
                const title = prompt(`${date} の予定を入力してください：`);
                if (title) {
                    saveSchedule(date, title);
                }
            };
        });
    }

    // スケジュール保存（PHPへ送信）
    function saveSchedule(date, title) {
        fetch("../PHP/save_schedule.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `date=${encodeURIComponent(date)}&title=${encodeURIComponent(title)}`
        })
        .then(res => res.text())
        .then(msg => alert(msg))
        .catch(err => console.error("Error:", err));
    }

    renderCalendar(year, month);
});
