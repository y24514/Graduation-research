<?php
require_once __DIR__ . '/../session_bootstrap.php';
$NAV_BASE = '..';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sports Analytics App</title>
    <link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <style>
        :root {
            --bg-dark: #121212;
            --panel-color: #2c3e50;
            --accent-green: #2ecc71;
            --accent-orange: #f39c12;
            --accent-red: #e74c3c;
        }

        body, html {
            margin: 0; padding: 0; width: 100%; height: 100%;
            overflow: hidden; background-color: var(--bg-dark);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        /* --- 回転促しオーバーレイ --- */
        #rotate-overlay {
            display: none;
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: var(--bg-dark); color: white;
            z-index: 10000; flex-direction: column;
            justify-content: center; align-items: center; text-align: center;
        }
        #rotate-overlay .icon { font-size: 50px; margin-bottom: 20px; animation: rotateAnim 2s infinite; }
        @keyframes rotateAnim {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(90deg); }
            100% { transform: rotate(0deg); }
        }

        @media (orientation: portrait) {
            #rotate-overlay { display: flex; }
            #app-wrapper { display: none; }
        }

        #app-wrapper { display: flex; width: 100vw; height: 100vh; }

        /* サイドバー */
        .toolbar {
            width: 110px; background: var(--panel-color);
            display: flex; flex-direction: column; gap: 8px; padding: 10px;
            box-sizing: border-box; overflow-y: auto;
        }

        .tool-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 10px; }
        .tool-group h3 { margin: 0; font-size: 10px; color: #95a5a6; text-align: center; text-transform: uppercase; }

        button {
            width: 100%; min-height: 40px; border: none; border-radius: 6px;
            font-size: 11px; font-weight: bold; cursor: pointer; color: white;
            transition: all 0.2s;
        }
        .btn-blue { background: #3498db; }
        .btn-red { background: #e74c3c; }
        .btn-yellow { background: #f1c40f; color: #333; }
        .btn-dashed { background: #fff; color: #333; border: 2px dashed #7f8c8d; }
        .btn-action { background: #7f8c8d; }
        .btn-clear { background: #c0392b; }
        .btn-save { background: var(--accent-green); margin-top: auto; }

        /* キャンバス表示エリア */
        #canvas-container {
            flex-grow: 1; display: flex; align-items: center; justify-content: center;
            padding: 10px; box-sizing: border-box; background: #1a1a1a;
        }
        canvas { border-radius: 4px; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
    </style>
    <link rel="stylesheet" href="../../css/tennis.css">
</head>
<body class="tennis-page tennis-board">

<?php
require_once __DIR__ . '/../header.php';
?>

    <div id="rotate-overlay">
        <div class="icon">🔄</div>
        <h2>画面を横向きにしてください</h2>
        <p>テニスコートを広く使って作戦を立てましょう</p>
    </div>

    <div id="app-wrapper">
        <div class="toolbar">
            <div class="tool-group">
                <h3>Player</h3>
                <button class="btn-blue" onclick="addPlayer('#3498db', '1')">1</button>
                <button class="btn-blue" onclick="addPlayer('#3498db', '2')">2</button>
                <button class="btn-red" onclick="addPlayer('#e74c3c', 'A')">A</button>
                <button class="btn-red" onclick="addPlayer('#e74c3c', 'B')">B</button>
            </div>

            <div class="tool-group">
                <h3>Draw</h3>
                <button id="btn-pen-toggle" class="btn-action" style="background:var(--accent-orange)" onclick="togglePenMode()">ペン：OFF</button>
            </div>

            <div class="tool-group">
                <h3>Line</h3>
                <button class="btn-yellow" onclick="addArrow('#f1c40f', false)">移動</button>
                <button class="btn-dashed" onclick="addArrow('#fff', true)">弾道</button>
            </div>

            <div class="tool-group">
                <h3>Note / Zone</h3>
                <button class="btn-action" style="background:#9b59b6" onclick="addText()">文字</button>
                <button class="btn-action" style="background:rgba(255, 148, 66, 1)" onclick="addZone('circle')">円</button>
                <button class="btn-action" style="background:rgba(35, 127, 255, 1)" onclick="addZone('rect')">四角</button>
            </div>

            <div class="tool-group">
                <h3>Edit</h3>
                <button class="btn-action" onclick="deleteSelected()">選択消去</button>
                <button class="btn-clear" onclick="clearObjects()">全消去</button>
            </div>

            <div class="tool-group">
                <h3>Data</h3>
                <input type="text" id="saveName" placeholder="名前" style="width: 100%; font-size: 10px; padding: 5px; box-sizing: border-box; border-radius: 4px; border:none; margin-bottom:5px;">
                <button class="btn-save" onclick="saveStrategy()">保存</button>
                <button class="btn-action" style="background:#34495e" onclick="toggleList()">保存リスト</button>
            </div>

            <div id="side-panel" style="display:none; position:fixed; right:0; top:0; width:200px; height:100%; background:#ecf0f1; z-index:10001; padding:10px; box-shadow:-2px 0 5px rgba(0,0,0,0.3); overflow-y:auto;">
                <button onclick="toggleList()" style="background:var(--accent-red); color:white; margin-bottom:10px;">閉じる</button>
                <div id="strategy-list"></div>
            </div>
        </div>

        <div id="canvas-container">
            <canvas id="tennisCanvas"></canvas>
        </div>
    </div>

<script>
    let canvas;

    function initCanvas() {
        const container = document.getElementById('canvas-container');
        if (!container || container.clientWidth === 0) return;

        if (canvas) { canvas.dispose(); }

        canvas = new fabric.Canvas('tennisCanvas', {
            width: container.clientWidth * 0.98,
            height: Math.min(container.clientHeight * 0.98, (container.clientWidth * 0.98) * 0.6),
            backgroundColor: '#2e7d32',
            preserveObjectStacking: true
        });

        // 手書き線のデフォルト設定
        canvas.freeDrawingBrush = new fabric.PencilBrush(canvas);
        canvas.freeDrawingBrush.color = '#ffffff';
        canvas.freeDrawingBrush.width = 4;

        // 手書きした線を選択可能にする
        canvas.on('path:created', function(e) {
            e.path.set({ selectable: true });
        });

        drawCourt();
    }

    function drawCourt() {
        const lp = { stroke: 'white', strokeWidth: 2, selectable: false, evented: false };
        const w = canvas.width; const h = canvas.height; const p = 30;
        
        canvas.add(new fabric.Rect({ left: p, top: p, width: w-p*2, height: h-p*2, fill: '', ...lp }));
        canvas.add(new fabric.Line([w/2, p, w/2, h-p], { ...lp, strokeWidth: 4 }));
        
        const s = h * 0.15;
        canvas.add(new fabric.Line([p, p+s, w-p, p+s], lp));
        canvas.add(new fabric.Line([p, h-p-s, w-p, h-p-s], lp));
        
        const v = w * 0.22;
        canvas.add(new fabric.Line([p+v, p+s, p+v, h-p-s], lp));
        canvas.add(new fabric.Line([w-p-v, p+s, w-p-v, h-p-s], lp));
        
        canvas.add(new fabric.Line([p+v, h/2, w-p-v, h/2], lp));
        canvas.add(new fabric.Line([p, h/2, p+15, h/2], lp));
        canvas.add(new fabric.Line([w-p-15, h/2, w-p, h/2], lp));
        
        canvas.renderAll();
    }

    // --- ペンのON/OFFトグル ---
    function togglePenMode() {
        canvas.isDrawingMode = !canvas.isDrawingMode;
        const btn = document.getElementById('btn-pen-toggle');
        
        if (canvas.isDrawingMode) {
            btn.textContent = "ペン：ON";
            btn.style.background = "#e74c3c"; // ON時は赤
        } else {
            btn.textContent = "ペン：OFF";
            btn.style.background = "#f39c12"; // OFF時はオレンジ
        }
    }

    function deleteSelected() {
        const activeObjects = canvas.getActiveObjects();
        if (activeObjects.length > 0) {
            canvas.remove(...activeObjects);
            canvas.discardActiveObject().requestRenderAll();
        }
    }

    function clearObjects() {
        canvas.getObjects().forEach(obj => {
            if (obj.selectable) canvas.remove(obj);
        });
        canvas.requestRenderAll();
    }

    // --- 追加パーツ系 ---
    function addPlayer(color, label) {
        const g = new fabric.Group([
            new fabric.Circle({ radius: 18, fill: color, originX:'center', originY:'center', stroke:'#fff', strokeWidth:2 }),
            new fabric.Text(label, { fontSize:16, fill:'#fff', originX:'center', originY:'center', fontWeight:'bold' })
        ], { left: 50, top: 50, hasControls: false, selectable: true });
        canvas.add(g);
        canvas.setActiveObject(g);
    }

    function addArrow(color, isDashed) {
        const arrow = new fabric.Group([
            new fabric.Line([0, 0, 80, 0], { stroke: color, strokeWidth: 4, strokeDashArray: isDashed ? [8, 4] : null, originY: 'center' }),
            new fabric.Triangle({ width: 15, height: 15, fill: color, angle: 90, originX: 'center', originY: 'center', left: 85, top: 0 })
        ], { left: canvas.width/2, top: canvas.height/2, selectable: true });
        canvas.add(arrow);
    }

    function addText() {
        const t = new fabric.IText('メモ', { left: 100, top: 100, fontSize: 20, fill: '#fff', backgroundColor: 'rgba(0,0,0,0.3)' });
        canvas.add(t);
    }

    function addZone(type) {
        const p = { left: 150, top: 150, fill: 'rgba(255,255,255,0.2)', stroke: '#fff', strokeWidth: 1, strokeDashArray: [5,5] };
        const shape = (type === 'circle') ? new fabric.Circle({ ...p, radius: 40 }) : new fabric.Rect({ ...p, width: 80, height: 50 });
        canvas.add(shape);
    }

    // --- 保存・読み込み系 ---
    function saveStrategy() {
        const name = document.getElementById('saveName').value;
        if (!name) return alert("作戦名を入力してください");
        const json = JSON.stringify(canvas.toJSON());
        fetch('api.php?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, data: json })
        }).then(() => { alert("保存しました"); loadList(); });
    }

    function toggleList() {
        const p = document.getElementById('side-panel');
        p.style.display = (p.style.display === 'none') ? 'block' : 'none';
        if (p.style.display === 'block') loadList();
    }

    function loadList() {
        const list = document.getElementById('strategy-list');
        list.innerHTML = "読込中...";
        fetch('api.php?action=list').then(r => r.json()).then(data => {
            list.innerHTML = data.map(i => `
                <div style="background:#fff; margin-bottom:5px; padding:5px; border-radius:4px;">
                    <span style="font-size:12px; color:#333">${i.name}</span><br>
                    <button onclick="loadData(${i.id})" style="width:auto; min-height:24px; padding:2px 10px; background:#3498db">開く</button>
                </div>
            `).join('');
        });
    }

    function loadData(id) {
        fetch(`api.php?action=load&id=${id}`).then(r => r.json()).then(s => {
            canvas.loadFromJSON(s.json_data, () => {
                canvas.renderAll();
                toggleList();
            });
        });
    }

    window.onload = initCanvas;
    window.addEventListener('resize', () => { setTimeout(initCanvas, 100); });
</script>
</body>
</html>