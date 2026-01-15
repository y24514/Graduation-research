<?php
require_once __DIR__ . '/../session_bootstrap.php';
$NAV_BASE = '..';
require_once __DIR__ . '/../header.php';
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
            --accent-blue: #3498db;
            --accent-red: #e74c3c;
            --accent-green: #27ae60;
            --accent-orange: #e67e22;
        }

        body, html {
            margin: 0; padding: 0; width: 100%; height: 100%;
            overflow: hidden; background-color: var(--bg-dark);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

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

        /* サイドバー調整 */
        .toolbar {
            width: 120px; background: var(--panel-color);
            display: flex; flex-direction: column; gap: 8px; padding: 10px;
            box-sizing: border-box; overflow-y: auto;
        }

        .tool-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 10px; }
        .tool-group h3 { margin: 0; font-size: 10px; color: #95a5a6; text-align: center; text-transform: uppercase; }
        
        /* 選手ボタンを2列に並べるためのコンテナ */
        .player-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
        }

        button {
            width: 100%; min-height: 35px; border: none; border-radius: 6px;
            font-size: 11px; font-weight: bold; cursor: pointer; color: white;
            transition: all 0.2s;
        }
        .btn-blue { background: var(--accent-blue); }
        .btn-red { background: var(--accent-red); }
        .btn-yellow { background: #f1c40f; color: #333; }
        .btn-dashed { background: #fff; color: #333; border: 2px dashed #7f8c8d; }
        .btn-action { background: #7f8c8d; }
        .btn-clear { background: #c0392b; }
        .btn-save { background: var(--accent-green); margin-top: auto; }

        #canvas-container {
            flex-grow: 1; display: flex; align-items: center; justify-content: center;
            padding: 10px; box-sizing: border-box; background: #1a1a1a;
        }
        canvas { border-radius: 4px; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
    </style>
</head>
<body>

    <div id="rotate-overlay">
        <div class="icon">🔄</div>
        <h2>画面を横向きにしてください</h2>
        <p>バスケットコートを広く使って作戦を立てましょう</p>
    </div>

    <div id="app-wrapper">
        <div class="toolbar">
            <div class="tool-group">
                <h3>Player (Blue)</h3>
                <div class="player-grid">
                    <button class="btn-blue" onclick="addPlayer('#3498db', '1')">1</button>
                    <button class="btn-blue" onclick="addPlayer('#3498db', '2')">2</button>
                    <button class="btn-blue" onclick="addPlayer('#3498db', '3')">3</button>
                    <button class="btn-blue" onclick="addPlayer('#3498db', '4')">4</button>
                    <button class="btn-blue" onclick="addPlayer('#3498db', '5')">5</button>
                </div>
            </div>

            <div class="tool-group">
                <h3>Player (Red)</h3>
                <div class="player-grid">
                    <button class="btn-red" onclick="addPlayer('#e74c3c', 'A')">A</button>
                    <button class="btn-red" onclick="addPlayer('#e74c3c', 'B')">B</button>
                    <button class="btn-red" onclick="addPlayer('#e74c3c', 'C')">C</button>
                    <button class="btn-red" onclick="addPlayer('#e74c3c', 'D')">D</button>
                    <button class="btn-red" onclick="addPlayer('#e74c3c', 'E')">E</button>
                </div>
            </div>

            <div class="tool-group">
                <h3>Draw</h3>
                <button id="btn-pen-toggle" class="btn-action" style="background:var(--accent-orange)" onclick="togglePenMode()">ペン：OFF</button>
            </div>

            <div class="tool-group">
                <h3>Line</h3>
                <button class="btn-yellow" onclick="addArrow('#f1c40f', false)">移動</button>
                <button class="btn-dashed" onclick="addArrow('#fff', true)">パス/シュート</button>
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
                <button class="btn-action" style="background:#34495e" onclick="toggleList()">リスト</button>
            </div>

            <div id="side-panel" style="display:none; position:fixed; right:0; top:0; width:200px; height:100%; background:#ecf0f1; z-index:10001; padding:10px; box-shadow:-2px 0 5px rgba(0,0,0,0.3); overflow-y:auto;">
                <button onclick="toggleList()" style="background:var(--accent-red); color:white; margin-bottom:10px;">閉じる</button>
                <div id="strategy-list"></div>
            </div>
        </div>

        <div id="canvas-container">
            <canvas id="basketballCanvas"></canvas>
        </div>
    </div>

<script>
    let canvas;

    function initCanvas() {
        const container = document.getElementById('canvas-container');
        if (!container || container.clientWidth === 0) return;

        if (canvas) { canvas.dispose(); }

        const canvasWidth = container.clientWidth * 0.98;
        canvas = new fabric.Canvas('basketballCanvas', {
            width: canvasWidth,
            height: Math.min(container.clientHeight * 0.98, canvasWidth * (15/28)),
            backgroundColor: '#d35400',
            preserveObjectStacking: true
        });

        canvas.freeDrawingBrush = new fabric.PencilBrush(canvas);
        canvas.freeDrawingBrush.color = '#ffffff';
        canvas.freeDrawingBrush.width = 4;

        canvas.on('path:created', function(e) {
            e.path.set({ selectable: true });
        });

        drawCourt();
    }

    function drawCourt() {
        const lp = { stroke: 'white', strokeWidth: 2, fill: '', selectable: false, evented: false };
        const w = canvas.width; const h = canvas.height; const p = 20;
        
        canvas.add(new fabric.Rect({ left: p, top: p, width: w-p*2, height: h-p*2, ...lp }));
        canvas.add(new fabric.Line([w/2, p, w/2, h-p], lp));
        
        const centerCircleRadius = h * 0.15;
        canvas.add(new fabric.Circle({ 
            left: w/2 - centerCircleRadius, top: h/2 - centerCircleRadius, 
            radius: centerCircleRadius, ...lp 
        }));

        drawBasketArea(p, h/2, 'left');
        drawBasketArea(w-p, h/2, 'right');

        canvas.renderAll();
    }

    function drawBasketArea(originX, centerY, side) {
        const lp = { stroke: 'white', strokeWidth: 2, fill: '', selectable: false, evented: false };
        const w = canvas.width; const h = canvas.height;
        const keyWidth = w * 0.18;
        const keyHeight = h * 0.35;
        
        const keyLeft = side === 'left' ? originX : originX - keyWidth;
        canvas.add(new fabric.Rect({ 
            left: keyLeft, top: centerY - keyHeight/2, 
            width: keyWidth, height: keyHeight, ...lp 
        }));

        const fsRadius = keyHeight / 2;
        const fsLeft = side === 'left' ? originX + keyWidth - fsRadius : originX - keyWidth - fsRadius;
        canvas.add(new fabric.Circle({
            left: fsLeft, top: centerY - fsRadius,
            radius: fsRadius, ...lp,
            startAngle: side === 'left' ? -Math.PI/2 : Math.PI/2,
            endAngle: side === 'left' ? Math.PI/2 : -Math.PI/2
        }));

        const threePointRadius = h * 0.4;
        const pathData = side === 'left' 
            ? `M ${originX} ${centerY - threePointRadius} A ${threePointRadius} ${threePointRadius} 0 0 1 ${originX} ${centerY + threePointRadius}`
            : `M ${originX} ${centerY - threePointRadius} A ${threePointRadius} ${threePointRadius} 0 0 0 ${originX} ${centerY + threePointRadius}`;
        
        canvas.add(new fabric.Path(pathData, { ...lp }));
    }

    function togglePenMode() {
        canvas.isDrawingMode = !canvas.isDrawingMode;
        const btn = document.getElementById('btn-pen-toggle');
        btn.textContent = canvas.isDrawingMode ? "ペン：ON" : "ペン：OFF";
        btn.style.background = canvas.isDrawingMode ? "#e74c3c" : "#f39c12";
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

    function saveStrategy() {
        const name = document.getElementById('saveName').value;
        if (!name) return alert("作戦名を入力してください");
        const json = JSON.stringify(canvas.toJSON());
        fetch('bapi.php?action=save', {
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
        fetch('bapi.php?action=list').then(r => r.json()).then(data => {
            list.innerHTML = data.map(i => `
                <div style="background:#fff; margin-bottom:5px; padding:5px; border-radius:4px;">
                    <span style="font-size:12px; color:#333">${i.name}</span><br>
                    <button onclick="loadData(${i.id})" style="width:auto; min-height:24px; padding:2px 10px; background:var(--accent-blue)">開く</button>
                </div>
            `).join('');
        });
    }

    function loadData(id) {
        fetch(`bapi.php?action=load&id=${id}`).then(r => r.json()).then(s => {
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