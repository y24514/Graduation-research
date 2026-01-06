<?php
session_start();
$NAV_BASE = '.';
require_once __DIR__ . '/header.php';

if (!isset($_SESSION['game'])) {
    http_response_code(200);
    echo '<div style="padding:16px; max-width:800px; margin:0 auto;">';
    echo '<h2 style="margin:8px 0;">試合が開始されていません</h2>';
    echo '<p style="margin:8px 0;">先に「試合設定」からチーム/スタメンを選んで試合開始してください。</p>';
    echo '<p style="margin:12px 0;"><a href="basketball_index.php" style="display:inline-block; padding:10px 14px; border:1px solid #ccc; border-radius:10px; text-decoration:none;">試合設定へ</a></p>';
    echo '</div>';
    exit;
}
$gameData = json_encode($_SESSION['game'], JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>試合記録</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        :root { --teamA: #3498db; --teamB: #e74c3c; --drawer-w: 280px; --bg-color: #f0f2f5; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: var(--bg-color); margin: 0; padding: 0; overflow-x: hidden; }
        .page-pad { padding: 10px; padding-bottom: 100px; }
        .header-area { display: flex; align-items: center; justify-content: center; gap: 10px; max-width: 600px; margin: 0 auto 15px; }
        .score-box { background: #2c3e50; color: #fff; padding: 10px 25px; border-radius: 15px; text-align: center; flex-grow: 1; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .score-num { font-size: 2.8em; font-weight: bold; line-height: 1; }
        .q-label { font-size: 0.9em; color: #f1c40f; font-weight: bold; margin-bottom: 5px; }
        .team-selector { display: flex; gap: 10px; max-width: 600px; margin: 0 auto 15px; }
        .t-btn { flex: 1; padding: 18px; border: none; border-radius: 12px; font-weight: bold; color: #fff; opacity: 0.4; cursor: pointer; }
        .t-btn.A { background: var(--teamA); } .t-btn.B { background: var(--teamB); }
        .t-btn.active { opacity: 1; box-shadow: 0 6px 12px rgba(0,0,0,0.2); transform: translateY(-2px); }
        .team-column { display: none; max-width: 600px; margin: 0 auto; }
        .team-column.active { display: block; }
        .p-card { background: #fff; border: 2px solid #eee; padding: 20px 15px; border-radius: 12px; margin-bottom: 10px; cursor: pointer; font-weight: bold; text-align: center; display: flex; justify-content: space-between; align-items: center; }
        .p-card.selected { border-color: #f1c40f; background: #fffde7; }
        .action-panel { position: fixed; bottom: 0; left: 0; right: 0; height: 55vh; background: #fff; padding: 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; box-shadow: 0 -8px 30px rgba(0,0,0,0.3); transform: translateY(100%); transition: 0.4s; z-index: 2000; border-radius: 25px 25px 0 0; }
        .action-panel.active { transform: translateY(0); }
        .btn-act { border: none; border-radius: 15px; color: white; font-weight: bold; font-size: 1.2em; cursor: pointer; }
        .history-drawer { position: fixed; top: 0; left: 0; width: var(--drawer-w); height: 100%; background: #fff; z-index: 3000; transform: translateX(-100%); transition: 0.3s; display: flex; flex-direction: column; }
        .history-drawer.open { transform: translateX(0); }
        .drawer-handle { position: fixed; top: 100px; left: 0; background: #333; color: #fff; padding: 15px 8px; border-radius: 0 8px 8px 0; z-index: 2500; writing-mode: vertical-rl; font-size: 13px; }
        .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; z-index: 2400; }
        .overlay.active { display: block; }
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 4000; align-items: center; justify-content: center; padding: 20px; }
        .m-content { background: #fff; padding: 25px; border-radius: 20px; width: 100%; max-width: 400px; }
        .bottom-nav-area { display: flex; gap: 10px; max-width: 600px; margin: 20px auto 100px; }
        .btn-bottom { flex: 1; padding: 18px 10px; border: none; border-radius: 12px; font-weight: bold; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-analysis-bottom { background: #fff; color: #333; border: 2px solid #34495e; }
        .btn-q-end-bottom { background: #34495e; color: #fff; }
    </style>
</head>
<body>

<div class="page-pad">

<div class="header-area">
    <div class="score-box">
        <div id="q-label" class="q-label">QUARTER 1</div>
        <div class="score-num"><span id="s-A">0</span><span style="color:#777;margin:0 10px;">-</span><span id="s-B">0</span></div>
    </div>
</div>

<div class="team-selector">
    <button id="btn-teamA" class="t-btn A" onclick="switchT('A')">TEAM A</button>
    <button id="btn-teamB" class="t-btn B" onclick="switchT('B')">TEAM B</button>
</div>

<div id="col-A" class="team-column active"><div id="p-list-A"></div></div>
<div id="col-B" class="team-column"><div id="p-list-B"></div></div>

<div class="bottom-nav-area">
    <button onclick="goAnalysis()" class="btn-bottom btn-analysis-bottom">📊 スタッツ分析</button>
    <button class="btn-bottom btn-q-end-bottom" onclick="nextQuarter()">⏱️ 次のQへ</button>
</div>

<div id="act-p" class="action-panel">
    <div style="grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px; height: 40px;">
        <span id="sel-name" style="font-size: 1.4em; font-weight: bold; color: #333;">選手名</span>
        <button onclick="resetS()" style="background:#eee; border:none; width:40px; height:40px; border-radius:50%; font-weight:bold; font-size:1.2em;">✕</button>
    </div>
    <button class="btn-act" style="background:#3498db" onclick="record('shot',2,'success')">2P ○</button>
    <button class="btn-act" style="background:#9b59b6" onclick="record('shot',3,'success')">3P ○</button>
    <button class="btn-act" style="background:#e67e22" onclick="record('shot',1,'success')">FT ○</button>
    <button class="btn-act" style="background:#7f8c8d" onclick="record('shot',2,'fail')">2P ×</button>
    <button class="btn-act" style="background:#7f8c8d" onclick="record('shot',3,'fail')">3P ×</button>
    <button class="btn-act" style="background:#7f8c8d" onclick="record('shot',1,'fail')">FT ×</button>
    <button class="btn-act" style="background:#e74c3c" onclick="record('foul',0,'success')">ファウル</button>
    <button class="btn-act" style="background:#f39c12" onclick="record('to',0,'success')">TO</button>
    <button class="btn-act" style="background:#2ecc71" onclick="openSub()">選手交代</button>
</div>

<div id="drawer" class="history-drawer">
    <div style="background:#333; color:#fff; padding:20px; font-weight:bold; display:flex; justify-content:space-between;"><span>試合履歴</span><span onclick="toggleDrawer()" style="cursor:pointer;">✕</span></div>
    <div id="h-list" style="padding:15px; overflow-y:auto; flex:1;"></div>
    <button onclick="undo()" style="width:100%; padding:20px; background:#e74c3c; color:#fff; border:none; font-weight:bold;">直前の入力を取り消す</button>
</div>
<div id="handle" class="drawer-handle" onclick="toggleDrawer()">履歴 ◀</div>
<div id="overlay" class="overlay" onclick="toggleDrawer()"></div>
<div id="sub-m" class="modal"><div class="m-content"><h3 style="margin-top:0;">交代して入る選手</h3><div id="b-list" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;"></div><button onclick="closeSub()" style="width:100%; margin-top:20px; padding:15px; border-radius:10px; background:#f9f9f9;">キャンセル</button></div></div>

<script>
    let game = { state: <?=$gameData?>, curT: 'A', selId: null };

    // --- 既存のrender, switchT, selectP, resetS, record, openSub, toggleDrawer, undo はそのまま ---
    function render() {
        const s = game.state;
        document.getElementById('s-A').innerText = s.score.A;
        document.getElementById('s-B').innerText = s.score.B;
        document.getElementById('q-label').innerText = "QUARTER " + s.quarter;
        document.getElementById('btn-teamA').innerText = s.teamNames.A;
        document.getElementById('btn-teamB').innerText = s.teamNames.B;
        document.getElementById('btn-teamA').classList.toggle('active', game.curT === 'A');
        document.getElementById('btn-teamB').classList.toggle('active', game.curT === 'B');
        document.getElementById('col-A').classList.toggle('active', game.curT === 'A');
        document.getElementById('col-B').classList.toggle('active', game.curT === 'B');
        ['A', 'B'].forEach(t => {
            const list = document.getElementById('p-list-' + t);
            list.innerHTML = "";
            s.teams[t].starters.forEach(id => {
                const d = document.createElement('div');
                d.className = 'p-card' + (game.selId === id ? ' selected' : '');
                d.innerHTML = `<span>${s.teams[t].names[id]}</span><span style="font-size:0.7em; color:#999;">★</span>`;
                d.onclick = () => selectP(id);
                list.appendChild(d);
            });
        });
        const h = document.getElementById('h-list');
        h.innerHTML = "";
        [...s.actions].reverse().forEach(a => {
            const d = document.createElement('div'); d.style.padding = "12px 0"; d.style.borderBottom = "1px solid #eee";
            let typeStr = a.type === 'shot' ? (a.result === 'success' ? '● ' : '○ ') + a.point + "P" : (a.type === 'foul' ? 'Foul' : (a.type === 'to' ? 'TO' : '🔄 交代'));
            d.innerHTML = `<small style="color:#999">Q${a.q}</small> <b style="color:${a.team==='A'?'var(--teamA)':'var(--teamB)'}">${a.team}</b> ${a.playerName}<br>${typeStr}`;
            h.appendChild(d);
        });
    }

    function switchT(t) { game.curT = t; resetS(); }
    function selectP(id) { if (game.selId === id) resetS(); else { game.selId = id; document.getElementById('sel-name').innerText = game.state.teams[game.curT].names[id]; document.getElementById('act-p').classList.add('active'); render(); } }
    function resetS() { game.selId = null; document.getElementById('act-p').classList.remove('active'); render(); }
    function record(type, pt, res) {
        if(!game.selId) return;
        game.state.actions.push({ q: game.state.quarter, team: game.curT, player: game.selId, playerName: game.state.teams[game.curT].names[game.selId], type, point: pt, result: res });
        if (type === 'shot' && res === 'success') game.state.score[game.curT] += pt;
        resetS();
    }
    function openSub() {
        const teamData = game.state.teams[game.curT]; if (teamData.bench.length === 0) return alert("控え選手がいません");
        const bList = document.getElementById('b-list'); bList.innerHTML = "";
        teamData.bench.forEach(id => {
            const d = document.createElement('div'); d.className = 'p-card'; d.innerText = teamData.names[id];
            d.onclick = () => {
                const inId = id; const outId = game.selId;
                teamData.starters = teamData.starters.map(sId => sId === outId ? inId : sId);
                teamData.bench = teamData.bench.map(bId => bId === inId ? outId : bId);
                game.state.actions.push({ q: game.state.quarter, team: game.curT, type: 'sub', inId, outId, playerName: teamData.names[outId] + " ➔ " + teamData.names[inId], result: 'success', point: 0 });
                closeSub(); resetS();
            };
            bList.appendChild(d);
        });
        document.getElementById('sub-m').style.display = 'flex';
    }
    function closeSub() { document.getElementById('sub-m').style.display = 'none'; }
    function toggleDrawer() { document.getElementById('drawer').classList.toggle('open'); document.getElementById('overlay').classList.toggle('active'); }
    function undo() {
        const s = game.state; if (s.actions.length === 0) return;
        const last = s.actions.pop();
        if (last.type === 'shot' && last.result === 'success') s.score[last.team] -= last.point;
        if (last.type === 'sub') { const t = s.teams[last.team]; t.starters = t.starters.map(i => i === last.inId ? last.outId : i); t.bench = t.bench.map(i => i === last.outId ? last.inId : i); }
        render();
    }

    // --- ここからが修正・追加された重要ロジック ---

    // サーバー保存処理
    async function saveState() {
    await fetch('save_game.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(game.state) });
    }

// 📊 スタッツ分析ボタン
async function goAnalysis() {
    await saveState();
    // Q4なら直接finalへ、それ以外ならanalysisへ
    if (game.state.quarter >= 4) {
        location.href = "final.php";
    } else {
        location.href = "analysis.php?q=" + game.state.quarter;
    }
}

// ⏱️ 次のQへボタン
async function nextQuarter() {
    if (game.state.quarter >= 4) {
        if (confirm("試合を終了し、最終結果を確認しますか？")) {
            await saveState();
            location.href = "final.php";
        }
        return;
    }
    if (confirm("クォーターを終了し、次へ進みますか？")) {
        game.state.quarter++;
        await saveState();
        render();
        window.scrollTo(0,0);
    }
}

    window.onload = render;
</script>
</div>

</body>
</html>