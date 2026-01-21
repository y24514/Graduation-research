(() => {
  'use strict';

  const plannerRoot = document.querySelector('[data-lane-planner]');
  if (!plannerRoot) return;

  const LANE_COUNT = 6;
  const tabs = Array.from(plannerRoot.querySelectorAll('[data-lane-tab]'));
  const panels = Array.from(plannerRoot.querySelectorAll('[data-lane-panel]'));
  const menuJsonInput = plannerRoot.querySelector('[data-menu-json]');
  const menuTextHidden = plannerRoot.querySelector('[data-menu-text]');
  const menuTextEditor = plannerRoot.querySelector('[data-menu-text-editor]');

  const dateInput = document.querySelector('input[name="practice_date"]');
  const titleInput = document.querySelector('input[name="title"]');
  const memoTextarea = document.querySelector('textarea[name="memo"]');

  // session_bootstrap.php は tab_id によって session_name を切り替えるため、AJAXにも tab_id を付ける
  const TAB_ID = (() => {
    try {
      const urlTid = new URLSearchParams(globalThis.location ? globalThis.location.search : '').get('tab_id');
      if (urlTid) return urlTid;
    } catch {
      // ignore
    }
    return typeof globalThis.tabId === 'string' ? globalThis.tabId : '';
  })();
  const withTabId = (url) => {
    const t = String(TAB_ID || '').trim();
    if (!t) return url;
    const sep = url.includes('?') ? '&' : '?';
    return `${url}${sep}tab_id=${encodeURIComponent(t)}`;
  };
  const withTabIdParams = (params) => {
    const p = params instanceof URLSearchParams ? params : new URLSearchParams(params);
    const t = String(TAB_ID || '').trim();
    if (t) p.set('tab_id', t);
    return p;
  };

  const toolbarDuplicateBtn = plannerRoot.querySelector('[data-action="duplicate-row"]');
  const toolbarDeleteBtn = plannerRoot.querySelector('[data-action="delete-row"]');

  const defaultRow = () => ({
    kind: '',
    dist: 0,
    reps: 1,
    cycle: '',
    setRest: '',
    stroke: 'Choice',
    note: '',
    intensity: '',
  });

  /** @type {{activeLane: string, lanes: Record<string, any[]>}} */
  const state = {
    activeLane: 'attendance',
    lanes: Object.fromEntries(Array.from({ length: LANE_COUNT }, (_, i) => [`${i + 1}`, [defaultRow()]])),
  };

  let selected = { lane: null, index: null };

  // グループ別の種別候補（PHPから注入）
  let kindListOptions = Array.isArray(globalThis.kindOptions) ? [...globalThis.kindOptions] : [];

  const kindOptionsHtml = (selectedKind) => {
    const uniq = Array.from(new Set(kindListOptions.map((x) => String(x ?? '').trim()).filter(Boolean)));
    const current = String(selectedKind ?? '').trim();

    // 既存データがリストに無い場合も表示できるように追加
    if (current && !uniq.includes(current)) uniq.unshift(current);

    const options = ['<option value="">—</option>', ...uniq.map((k) => {
      const sel = current === k ? 'selected' : '';
      return `<option value="${k}" ${sel}>${k}</option>`;
    })];
    return options.join('');
  };

  const escapeText = (s) => String(s ?? '').replace(/\s+/g, ' ').trim();

  const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, (ch) => {
    switch (ch) {
      case '&': return '&amp;';
      case '<': return '&lt;';
      case '>': return '&gt;';
      case '"': return '&quot;';
      case "'": return '&#39;';
      default: return ch;
    }
  });

  const clipText = (s, max = 160) => {
    const t = String(s ?? '').replace(/\r\n/g, '\n').trim();
    if (!t) return '';
    return t.length > max ? `${t.slice(0, max)}…` : t;
  };

  let practiceTooltipEl = null;
  let practiceTooltipMoveHandler = null;
  const hidePracticeTooltip = () => {
    if (practiceTooltipMoveHandler) {
      document.removeEventListener('mousemove', practiceTooltipMoveHandler);
      practiceTooltipMoveHandler = null;
    }
    if (practiceTooltipEl) {
      practiceTooltipEl.classList.remove('is-visible');
      practiceTooltipEl.remove();
      practiceTooltipEl = null;
    }
  };

  const positionPracticeTooltip = (x, y) => {
    if (!practiceTooltipEl) return;

    const offset = 12;
    const maxPad = 12;
    const vw = document.documentElement?.clientWidth || window.innerWidth;
    const vh = document.documentElement?.clientHeight || window.innerHeight;

    // まずは右下に置き、はみ出す場合に反転
    let left = x + offset;
    let top = y + offset;

    // 一旦配置してサイズ取得
    practiceTooltipEl.style.left = `${left}px`;
    practiceTooltipEl.style.top = `${top}px`;

    const rect = practiceTooltipEl.getBoundingClientRect();

    if (rect.right > vw - maxPad) {
      left = Math.max(maxPad, x - rect.width - offset);
    }
    if (rect.bottom > vh - maxPad) {
      top = Math.max(maxPad, y - rect.height - offset);
    }

    practiceTooltipEl.style.left = `${left}px`;
    practiceTooltipEl.style.top = `${top}px`;
  };

  const showPracticeTooltip = (jsEvent, fcEvent) => {
    if (!jsEvent || !fcEvent) return;

    const id = String(fcEvent.id ?? '').trim();
    if (!id) return;

    const cache = (globalThis.practiceCache && typeof globalThis.practiceCache === 'object') ? globalThis.practiceCache : null;
    const p = cache ? cache[id] : null;

    const title = String((p && p.title) ? p.title : (fcEvent.title ?? '')).trim();
    const date = String((p && p.practice_date) ? p.practice_date : (fcEvent.startStr ?? '')).trim();
    const menu = clipText(p ? p.menu_text : '', 180);
    const memo = clipText(p ? p.memo : '', 120);

    const lines = [];
    if (menu) lines.push(`メニュー: ${menu}`);
    if (memo) lines.push(`メモ: ${memo}`);

    if (!practiceTooltipEl) {
      practiceTooltipEl = document.createElement('div');
      practiceTooltipEl.className = 'practice-tooltip';
      document.body.appendChild(practiceTooltipEl);
    }

    practiceTooltipEl.innerHTML = [
      `<div class="practice-tooltip__title">${escapeHtml(title || '練習')}</div>`,
      date ? `<div class="practice-tooltip__meta">${escapeHtml(date)}</div>` : '',
      lines.length ? `<div class="practice-tooltip__body">${escapeHtml(lines.join('\n'))}</div>` : '<div class="practice-tooltip__body">クリックで詳細を表示</div>',
    ].join('');

    // 初期位置
    const x = Number(jsEvent.clientX ?? 0);
    const y = Number(jsEvent.clientY ?? 0);
    positionPracticeTooltip(x, y);
    practiceTooltipEl.classList.add('is-visible');

    // 追従
    if (!practiceTooltipMoveHandler) {
      practiceTooltipMoveHandler = (e) => {
        if (!(e instanceof MouseEvent)) return;
        positionPracticeTooltip(e.clientX, e.clientY);
      };
      document.addEventListener('mousemove', practiceTooltipMoveHandler, { passive: true });
    }
  };

  const parseMaybeNumber = (value, fallback = 0) => {
    const n = Number(String(value ?? '').trim());
    return Number.isFinite(n) ? n : fallback;
  };

  const rowTotalDistance = (row) => {
    const dist = Math.max(0, parseMaybeNumber(row.dist, 0));
    const reps = Math.max(0, parseMaybeNumber(row.reps, 0));
    return dist * reps;
  };

  const laneTotalDistance = (laneRows) => laneRows.reduce((sum, r) => sum + rowTotalDistance(r), 0);

  const setActiveLane = (lane) => {
    state.activeLane = lane;

    for (const tab of tabs) {
      const isActive = tab.dataset.lane === lane;
      tab.classList.toggle('is-active', isActive);
      tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    }

    for (const panel of panels) {
      panel.classList.toggle('is-active', panel.dataset.lane === lane);
    }

    // 表示切替時に最新 state を反映（全体タブ含む）
    if (isLaneNumber(lane) || isAllLane(lane)) {
      renderLane(lane);
    }

    clearSelection();
  };

  const isLaneNumber = (lane) => /^[1-6]$/.test(String(lane ?? ''));
  const isAllLane = (lane) => String(lane ?? '') === 'all';
  const targetLanesForEdit = (lane) => {
    if (isAllLane(lane)) return Array.from({ length: LANE_COUNT }, (_, i) => `${i + 1}`);
    if (isLaneNumber(lane)) return [String(lane)];
    return [];
  };

  // 「全体」タブの表示内容はレーン1を代表として表示する
  const viewLaneFor = (lane) => (isAllLane(lane) ? '1' : String(lane));

  const clearSelection = () => {
    selected = { lane: null, index: null };
    plannerRoot.querySelectorAll('.lane-row.is-selected').forEach((tr) => tr.classList.remove('is-selected'));
    updateToolbarSelectionState();
  };

  const updateToolbarSelectionState = () => {
    const hasSelection = selected.lane && Number.isInteger(selected.index);
    if (toolbarDuplicateBtn) toolbarDuplicateBtn.disabled = !hasSelection;
    if (toolbarDeleteBtn) toolbarDeleteBtn.disabled = !hasSelection;
  };

  const templateSelect = (options, value, placeholder = '') => {
    const opts = [
      placeholder ? `<option value="">${placeholder}</option>` : '',
      ...options.map((o) => `<option value="${o}">${o}</option>`),
    ].join('');

    return `
      <select class="lane-cell" data-field="select">
        ${opts}
      </select>
    `;
  };

  const renderLane = (lane) => {
    const tbody = plannerRoot.querySelector(`[data-lane-tbody][data-lane="${lane}"]`);
    if (!tbody) return;

    const laneKey = viewLaneFor(lane);
    const rows = state.lanes[laneKey] || [];

    tbody.innerHTML = rows
      .map((row, idx) => {
        const total = rowTotalDistance(row);
        return `
          <tr class="lane-row" data-row-index="${idx}" data-lane="${lane}">
            <td>
              <select class="lane-cell" data-field="kind">
                ${kindOptionsHtml(row.kind)}
              </select>
            </td>
            <td>
              <input class="lane-cell lane-cell--num" data-field="dist" inputmode="numeric" value="${row.dist || ''}" placeholder="m" />
            </td>
            <td>
              <input class="lane-cell lane-cell--num" data-field="reps" inputmode="numeric" value="${row.reps || ''}" placeholder="回" />
            </td>
            <td>
              <input class="lane-cell" data-field="cycle" value="${escapeText(row.cycle)}" placeholder="例: 1:30" />
            </td>
            <td>
              <input class="lane-cell" data-field="setRest" value="${escapeText(row.setRest)}" placeholder="例: 2:00" />
            </td>
            <td>
              <select class="lane-cell" data-field="stroke">
                ${['Choice', 'Fr', 'Br', 'Ba', 'Fly', 'IM'].map((s) => `<option value="${s}" ${row.stroke === s ? 'selected' : ''}>${s}</option>`).join('')}
              </select>
            </td>
            <td>
              <input class="lane-cell" data-field="note" value="${escapeText(row.note)}" placeholder="例: 体を動かす" />
            </td>
            <td>
              <select class="lane-cell" data-field="intensity">
                ${['', 'A1', 'EN1', 'EN2', 'EN3', 'Max'].map((s) => `<option value="${s}" ${row.intensity === s ? 'selected' : ''}>${s || '—'}</option>`).join('')}
              </select>
            </td>
            <td class="lane-total-cell" data-row-total>${total ? `${total}m` : ''}</td>
          </tr>
        `;
      })
      .join('');

    const totalEl = plannerRoot.querySelector(`[data-lane-total][data-lane="${lane}"]`);
    if (totalEl) totalEl.textContent = `${laneTotalDistance(rows)}m`;
  };

  const renderAllLanes = () => {
    for (let i = 1; i <= LANE_COUNT; i++) renderLane(`${i}`);
    renderLane('all');
    syncHiddenFields();
  };

  const stateToMenuText = () => {
    const blocks = [];
    for (let i = 1; i <= LANE_COUNT; i++) {
      const lane = `${i}`;
      const rows = state.lanes[lane] || [];
      const lines = rows
        .filter((r) => Object.values(r).some((v) => String(v ?? '').trim() !== ''))
        .map((r) => {
          const parts = [];
          if (r.kind) parts.push(escapeText(r.kind));
          if (parseMaybeNumber(r.dist, 0) > 0) parts.push(`${parseMaybeNumber(r.dist, 0)}m`);
          if (parseMaybeNumber(r.reps, 0) > 0) parts.push(`x${parseMaybeNumber(r.reps, 0)}`);
          if (r.cycle) parts.push(`@${escapeText(r.cycle)}`);
          if (r.setRest) parts.push(`rest:${escapeText(r.setRest)}`);
          if (r.stroke) parts.push(escapeText(r.stroke));
          if (r.intensity) parts.push(`[${escapeText(r.intensity)}]`);
          if (r.note) parts.push(`- ${escapeText(r.note)}`);
          const total = rowTotalDistance(r);
          if (total > 0) parts.push(`(${total}m)`);
          return parts.join(' ');
        });

      if (lines.length === 0) continue;
      blocks.push(`=== Lane ${lane} ===`);
      blocks.push(...lines);
      blocks.push('');
    }

    return blocks.join('\n').trim();
  };

  const syncHiddenFields = () => {
    if (menuJsonInput) menuJsonInput.value = JSON.stringify({ lanes: state.lanes });

    const generated = stateToMenuText();

    // テキストエディタ側が開いて手入力されている場合は、その内容を優先（表との同期はしない）
    const raw = menuTextEditor ? String(menuTextEditor.value ?? '').trim() : '';
    const useRaw = raw.length > 0 && raw !== String(initialMenuText ?? '').trim();

    const finalText = useRaw ? raw : generated;

    if (menuTextHidden) menuTextHidden.value = finalText;
  };

  const parseLaneTextToState = (text) => {
    const src = String(text ?? '');
    const lines = src.split(/\r?\n/);

    // 生成フォーマット: === Lane N ===
    let currentLane = null;
    const byLane = {};
    for (let i = 1; i <= LANE_COUNT; i++) byLane[`${i}`] = [];

    const headerRe = /^\s*===\s*Lane\s*(\d)\s*===\s*$/i;

    for (const line of lines) {
      const m = line.match(headerRe);
      if (m) {
        currentLane = m[1];
        continue;
      }
      if (!currentLane) continue;
      const trimmed = line.trim();
      if (!trimmed) continue;
      byLane[currentLane].push(trimmed);
    }

    const hasAny = Object.values(byLane).some((arr) => arr.length > 0);
    if (!hasAny) return { ok: false };

    const parseRowLine = (line) => {
      const row = defaultRow();
      let rest = line;

      // intensity [EN2]
      const inten = rest.match(/\[([^\]]+)\]/);
      if (inten) {
        row.intensity = inten[1].trim();
        rest = rest.replace(inten[0], '');
      }

      // note after '- '
      const noteIdx = rest.indexOf(' - ');
      if (noteIdx >= 0) {
        row.note = rest.slice(noteIdx + 3).trim();
        rest = rest.slice(0, noteIdx).trim();
      }

      // rest:2:00
      const setRest = rest.match(/\brest:([^\s]+)\b/i);
      if (setRest) {
        row.setRest = setRest[1].trim();
        rest = rest.replace(setRest[0], '').trim();
      }

      // @1:30
      const cyc = rest.match(/\B@([^\s]+)\b/);
      if (cyc) {
        row.cycle = cyc[1].trim();
        rest = rest.replace(cyc[0], '').trim();
      }

      // dist 50m
      const dist = rest.match(/\b(\d+)m\b/i);
      if (dist) {
        row.dist = parseMaybeNumber(dist[1], 0);
        rest = rest.replace(dist[0], '').trim();
      }

      // reps x8
      const reps = rest.match(/\bx(\d+)\b/i);
      if (reps) {
        row.reps = parseMaybeNumber(reps[1], 1);
        rest = rest.replace(reps[0], '').trim();
      }

      // stroke tokens
      const strokes = ['Choice', 'Fr', 'Br', 'Ba', 'Fly', 'IM'];
      for (const s of strokes) {
        const re = new RegExp(`\\b${s}\\b`);
        if (re.test(rest)) {
          row.stroke = s;
          rest = rest.replace(re, '').trim();
          break;
        }
      }

      // kind = remaining first chunk
      const kind = rest.split(/\s+/).filter(Boolean)[0];
      if (kind) row.kind = kind;

      return row;
    };

    for (let i = 1; i <= LANE_COUNT; i++) {
      const lane = `${i}`;
      const parsedRows = byLane[lane].map(parseRowLine).filter((r) => Object.values(r).some((v) => String(v ?? '').trim() !== ''));
      state.lanes[lane] = parsedRows.length ? parsedRows : [defaultRow()];
    }

    return { ok: true };
  };

  const fetchPracticeById = async (practiceId) => {
    const id = String(practiceId ?? '').trim();
    if (!/^[0-9]+$/.test(id)) throw new Error('invalid_id');

    // 最新20件はページ側にキャッシュしているので、まずはそれを使う（引用の安定化）
    if (globalThis.practiceCache && typeof globalThis.practiceCache === 'object') {
      const cached = globalThis.practiceCache[id];
      if (cached && typeof cached === 'object') return cached;
    }

    const res = await fetch(withTabId(`../../PHP/swim/swim_practice_fetch_ajax.php?id=${encodeURIComponent(id)}`), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    let data;
    try {
      data = await res.json();
    } catch {
      throw new Error('fetch_invalid_json');
    }

    if (!res.ok) {
      const code = (data && data.error) ? String(data.error) : 'http_error';
      throw new Error(code);
    }
    if (!data || data.ok !== true || !data.practice) {
      const code = (data && data.error) ? String(data.error) : 'fetch_failed';
      throw new Error(code);
    }

    // 取得できたらキャッシュ更新
    try {
      if (globalThis.practiceCache && typeof globalThis.practiceCache === 'object') {
        globalThis.practiceCache[id] = data.practice;
      }
    } catch {
      // ignore
    }

    return data.practice;
  };

  const quotePracticeById = async (practiceId) => {
    const p = await fetchPracticeById(practiceId);
    if (dateInput && p.practice_date) dateInput.value = p.practice_date;
    if (titleInput && p.title) titleInput.value = p.title;
    if (memoTextarea) memoTextarea.value = p.memo || '';

    // まず表への復元を試す（自動生成したフォーマットの場合）
    const parsed = parseLaneTextToState(p.menu_text || '');
    if (parsed.ok) {
      renderAllLanes();
      alert('過去の練習を引用しました（表へ復元）');
    } else {
      // 復元できない場合はテキスト編集へ
      if (menuTextEditor) menuTextEditor.value = p.menu_text || '';
      if (menuTextHidden) menuTextHidden.value = p.menu_text || '';
      alert('過去の練習を引用しました（テキストへ反映）');
    }

    syncHiddenFields();
    // 編集エリアへスクロール
    plannerRoot.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  // 練習詳細モーダル
  const practiceModal = document.querySelector('[data-practice-modal]');
  const practiceModalDate = document.querySelector('[data-practice-modal-date]');
  const practiceModalTitle = document.querySelector('[data-practice-modal-title]');
  const practiceModalMenu = document.querySelector('[data-practice-modal-menu]');
  const practiceModalMemoWrap = document.querySelector('[data-practice-modal-memo]');
  const practiceModalMemoText = document.querySelector('[data-practice-modal-memo-text]');
  const practiceModalQuoteBtn = document.querySelector('[data-practice-modal-quote]');

  const closePracticeModal = () => {
    if (!(practiceModal instanceof HTMLElement)) return;
    practiceModal.hidden = true;
  };

  const openPracticeModal = async (practiceId) => {
    if (!(practiceModal instanceof HTMLElement)) return;

    practiceModal.hidden = false;
    if (practiceModalMenu) practiceModalMenu.textContent = '読み込み中...';
    if (practiceModalMemoWrap instanceof HTMLElement) practiceModalMemoWrap.hidden = true;

    try {
      const p = await fetchPracticeById(practiceId);

      if (practiceModalDate) practiceModalDate.textContent = p.practice_date || '';
      if (practiceModalTitle) practiceModalTitle.textContent = p.title || '';
      if (practiceModalMenu) practiceModalMenu.textContent = p.menu_text || '';

      if (practiceModalQuoteBtn instanceof HTMLElement) {
        practiceModalQuoteBtn.setAttribute('data-practice-id', String(p.id ?? practiceId));
      }

      const memo = String(p.memo ?? '').trim();
      if (practiceModalMemoWrap instanceof HTMLElement && practiceModalMemoText) {
        if (memo) {
          practiceModalMemoText.textContent = memo;
          practiceModalMemoWrap.hidden = false;
        } else {
          practiceModalMemoWrap.hidden = true;
        }
      }
    } catch {
      if (practiceModalMenu) practiceModalMenu.textContent = '詳細の取得に失敗しました';
    }
  };

  const addRow = (lane) => {
    const targets = targetLanesForEdit(lane);
    if (targets.length === 0) return;
    for (const t of targets) {
      state.lanes[t] = state.lanes[t] || [];
      state.lanes[t].push(defaultRow());
      renderLane(t);
    }
    if (isAllLane(lane)) renderLane('all');
    syncHiddenFields();
  };

  const duplicateSelectedRow = () => {
    if (!selected.lane || !Number.isInteger(selected.index)) return;
    const lane = selected.lane;
    const targets = targetLanesForEdit(lane);
    if (targets.length === 0) return;

    for (const t of targets) {
      const rows = state.lanes[t] || [];
      const src = rows[selected.index] || defaultRow();
      rows.splice(selected.index + 1, 0, { ...src });
      state.lanes[t] = rows;
      renderLane(t);
    }
    if (isAllLane(lane)) renderLane('all');
    syncHiddenFields();
  };

  const deleteSelectedRow = () => {
    if (!selected.lane || !Number.isInteger(selected.index)) return;
    const lane = selected.lane;
    const targets = targetLanesForEdit(lane);
    if (targets.length === 0) return;

    for (const t of targets) {
      const rows = state.lanes[t] || [];
      rows.splice(selected.index, 1);
      if (rows.length === 0) rows.push(defaultRow());
      state.lanes[t] = rows;
      renderLane(t);
    }
    if (isAllLane(lane)) renderLane('all');
    clearSelection();
    syncHiddenFields();
  };

  const copyFromLane = () => {
    const targetLane = state.activeLane;
    const targets = targetLanesForEdit(targetLane);
    if (targets.length === 0) {
      alert('先にレーンを選んでください');
      return;
    }

    const from = prompt('どのレーンから複製しますか？（1〜6）');
    if (!from || !/^[1-6]$/.test(from)) return;
    if (!isAllLane(targetLane) && from === targetLane) return;

    const ok = confirm(isAllLane(targetLane)
      ? `全レーンをレーン${from}の内容で上書きします。よろしいですか？`
      : `レーン${targetLane}をレーン${from}の内容で上書きします。よろしいですか？`);
    if (!ok) return;

    for (const t of targets) {
      state.lanes[t] = (state.lanes[from] || []).map((r) => ({ ...defaultRow(), ...(r || {}) }));
      if (state.lanes[t].length === 0) state.lanes[t].push(defaultRow());
      renderLane(t);
    }
    if (isAllLane(targetLane)) renderLane('all');
    clearSelection();
    syncHiddenFields();
  };

  const printPreview = () => {
    const lane = viewLaneFor(state.activeLane);
    if (!/^[1-6]$/.test(lane)) {
      alert('先にレーンを選んでください');
      return;
    }

    const rows = state.lanes[lane] || [];
    const htmlRows = rows
      .filter((r) => Object.values(r).some((v) => String(v ?? '').trim() !== ''))
      .map((r) => {
        const total = rowTotalDistance(r);
        return `
          <tr>
            <td>${escapeText(r.kind)}</td>
            <td style="text-align:right">${r.dist ? `${parseMaybeNumber(r.dist, 0)}m` : ''}</td>
            <td style="text-align:right">${r.reps ? `${parseMaybeNumber(r.reps, 0)}` : ''}</td>
            <td>${escapeText(r.cycle)}</td>
            <td>${escapeText(r.setRest)}</td>
            <td>${escapeText(r.stroke)}</td>
            <td>${escapeText(r.note)}</td>
            <td>${escapeText(r.intensity)}</td>
            <td style="text-align:right">${total ? `${total}m` : ''}</td>
          </tr>
        `;
      })
      .join('');

    const w = window.open('', '_blank');
    if (!w) return;

    w.document.write(`
      <html>
        <head>
          <meta charset="UTF-8" />
          <title>練習プレビュー（レーン${lane}）</title>
          <style>
            body { font-family: -apple-system, Segoe UI, Meiryo, sans-serif; padding: 16px; }
            h1 { font-size: 18px; margin: 0 0 12px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 6px 8px; font-size: 12px; }
            th { background: #f5f5f5; text-align: left; }
          </style>
        </head>
        <body>
          <h1>練習スケジュール：レーン${lane}</h1>
          <table>
            <thead>
              <tr>
                <th>種類</th><th>距離</th><th>本数</th><th>サイクル</th><th>セット間</th><th>種目</th><th>内容</th><th>強度</th><th>折距離</th>
              </tr>
            </thead>
            <tbody>
              ${htmlRows || '<tr><td colspan="9">（空）</td></tr>'}
            </tbody>
          </table>
          <script>window.print();</script>
        </body>
      </html>
    `);
    w.document.close();
  };

  const showHelp = () => {
    alert(
      [
        '使い方ヒント',
        '',
        '1) レーンを選んで行を追加',
        '2) 行をクリックすると選択できます（複製/削除が有効化）',
        '3) 他レーンから複製で、現在レーンへコピーできます',
        '4) 保存時は自動でテキスト（menu_text）に変換して保存します',
      ].join('\n')
    );
  };

  // 初期状態の復元（menu_json優先）
  try {
    if (typeof initialMenuJson === 'string' && initialMenuJson.trim()) {
      const parsed = JSON.parse(initialMenuJson);
      if (parsed && parsed.lanes) {
        for (let i = 1; i <= LANE_COUNT; i++) {
          const lane = `${i}`;
          if (Array.isArray(parsed.lanes[lane])) {
            state.lanes[lane] = parsed.lanes[lane].map((r) => ({ ...defaultRow(), ...(r || {}) }));
            if (state.lanes[lane].length === 0) state.lanes[lane].push(defaultRow());
          }
        }
      }
    }
  } catch {
    // ignore
  }

  const ensureKindDatalist = () => {
    let dl = document.getElementById('lane-kind-list');
    if (!dl) {
      dl = document.createElement('datalist');
      dl.id = 'lane-kind-list';
      document.body.appendChild(dl);
    }
    if (!Array.isArray(kindListOptions) || kindListOptions.length === 0) {
      kindListOptions = ['W-up', 'SKP', 'Pull', 'Kick', 'Swim', 'Drill', 'Main', 'Down'];
    }
    const uniq = Array.from(new Set(kindListOptions.map((x) => String(x ?? '').trim()).filter(Boolean)));
    dl.innerHTML = uniq.map((k) => `<option value="${k}"></option>`).join('');
  };

  ensureKindDatalist();

  // タブ切り替え
  plannerRoot.addEventListener('click', (e) => {
    const tab = e.target.closest('[data-lane-tab]');
    if (tab) {
      setActiveLane(tab.dataset.lane);
      return;
    }

    const btn = e.target.closest('[data-action]');
    if (!btn) return;

    const action = btn.getAttribute('data-action');
    if (action === 'add-row') {
      if (/^[1-6]$/.test(state.activeLane) || state.activeLane === 'all') addRow(state.activeLane);
      else alert('先にレーンを選んでください');
    }

    if (action === 'add-kind') {
      const name = prompt('追加する「種類」名を入力してください（例: Kick / Pull / W-up）');
      if (!name) return;
      const trimmed = String(name).trim();
      if (!trimmed) return;

      fetch('../../PHP/swim/swim_practice_kind_ajax.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: withTabIdParams({ action: 'add', kind_name: trimmed }),
      })
        .then((r) => r.json())
        .then((data) => {
          if (!data || data.ok !== true) throw new Error('failed');
          if (Array.isArray(data.kinds)) {
            kindListOptions = data.kinds;
            ensureKindDatalist();
            alert('種類を追加しました');
          }
        })
        .catch(() => {
          alert('種類の追加に失敗しました');
        });
    }

    if (action === 'duplicate-row') duplicateSelectedRow();
    if (action === 'delete-row') deleteSelectedRow();
    if (action === 'copy-from-lane') copyFromLane();
    if (action === 'print') printPreview();
    if (action === 'help') showHelp();
  });

  // 右側のカード「引用」
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="quote-practice"]');
    if (!btn) return;
    const id = btn.getAttribute('data-practice-id');
    if (!id) return;
    closePracticeModal();
    quotePracticeById(id).catch((err) => {
      const code = String(err && err.message ? err.message : 'failed');
      if (code === 'unauthorized') {
        alert('ログイン状態が切れています。ページを再読み込みしてログインし直してください。');
        return;
      }
      if (code === 'not_found') {
        alert('練習が見つかりませんでした（削除された可能性があります）');
        return;
      }
      alert('練習の引用に失敗しました');
    });
  });

  // 右側のカード：クリックでモーダル表示
  document.addEventListener('click', (e) => {
    const header = e.target.closest('[data-action="open-practice"]');
    if (!header) return;
    const id = header.getAttribute('data-practice-id');
    if (!id) return;
    openPracticeModal(id);
  });

  // モーダルを閉じる（×/背景クリック）
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="close-practice-modal"]');
    if (!btn) return;
    closePracticeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closePracticeModal();
  });

  const setPracticeView = (view) => {
    const listEl = document.querySelector('[data-practice-list]');
    const calEl = document.querySelector('[data-practice-calendar]');
    const toolsEl = document.querySelector('[data-practice-list-tools]');
    if (!(listEl instanceof HTMLElement) || !(calEl instanceof HTMLElement)) return;

    const isCalendar = view === 'calendar';
    listEl.hidden = isCalendar;
    calEl.hidden = !isCalendar;
    if (toolsEl instanceof HTMLElement) toolsEl.hidden = isCalendar;

    document.querySelectorAll('[data-action="practice-view"]').forEach((btn) => {
      if (!(btn instanceof HTMLElement)) return;
      const v = btn.getAttribute('data-view');
      const active = v === (isCalendar ? 'calendar' : 'list');
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    try {
      localStorage.setItem('swimPracticeView', isCalendar ? 'calendar' : 'list');
    } catch {
      // ignore
    }

    // FullCalendar は hidden 状態で render すると崩れやすいので、表示時に初期化/描画する
    if (isCalendar) {
      initPracticeCalendarIfNeeded();
      const cal = globalThis._swimPracticeCalendar;
      if (cal && typeof cal.updateSize === 'function') {
        // 初回は render、2回目以降は updateSize
        if (globalThis._swimPracticeCalendarRendered !== true && typeof cal.render === 'function') {
          try {
            cal.render();
            globalThis._swimPracticeCalendarRendered = true;
          } catch {
            // ignore
          }
        } else {
          cal.updateSize();
        }
      }
    }
  };

  const getPracticeEvents = () => {
    if (!Array.isArray(globalThis.practiceEvents)) return [];
    return globalThis.practiceEvents
      .map((e) => ({
        id: String(e.id ?? ''),
        title: String(e.title ?? ''),
        start: String(e.start ?? ''),
        allDay: true,
      }))
      .filter((e) => e.id && /^\d{4}-\d{2}-\d{2}$/.test(e.start));
  };

  const initPracticeCalendarIfNeeded = () => {
    const calendarHost = document.querySelector('[data-practice-calendar]');
    if (!(calendarHost instanceof HTMLElement)) return;
    if (!globalThis.FullCalendar) return;
    if (globalThis._swimPracticeCalendar) return;

    const events = getPracticeEvents();

    try {
      const cal = new FullCalendar.Calendar(calendarHost, {
        locale: 'ja',
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next',
          center: 'title',
          right: '',
        },
        height: 'auto',
        fixedWeekCount: false,
        events,
        eventMouseEnter: (info) => {
          try {
            showPracticeTooltip(info.jsEvent, info.event);
          } catch {
            // ignore
          }
        },
        eventMouseLeave: () => {
          try { hidePracticeTooltip(); } catch { /* ignore */ }
        },
        eventClick: (info) => {
          info.jsEvent.preventDefault();
          try { hidePracticeTooltip(); } catch { /* ignore */ }
          if (info.event && info.event.id) {
            openPracticeCardById(info.event.id);
          }
        },
        dateClick: (info) => {
          try { hidePracticeTooltip(); } catch { /* ignore */ }
          const hit = events.find((ev) => ev.start === info.dateStr);
          if (hit) openPracticeCardById(hit.id);
        },
      });

      globalThis._swimPracticeCalendar = cal;
      globalThis._swimPracticeCalendarRendered = false;
    } catch {
      // ignore
    }
  };

  // 右側：検索（リストのみ）
  const practiceSearchInput = document.querySelector('[data-practice-search]');
  const practiceCountEl = document.querySelector('[data-practice-count]');
  const updatePracticeCount = () => {
    const listEl = document.querySelector('[data-practice-list]');
    if (!(listEl instanceof HTMLElement) || !(practiceCountEl instanceof HTMLElement)) return;
    const cards = Array.from(listEl.querySelectorAll('[data-practice-card]')).filter((el) => el instanceof HTMLElement);
    const shown = cards.filter((el) => !el.hidden).length;
    practiceCountEl.textContent = `${shown}/${cards.length}件`;
  };

  const applyPracticeFilter = (q) => {
    const listEl = document.querySelector('[data-practice-list]');
    if (!(listEl instanceof HTMLElement)) return;
    const query = String(q ?? '').trim().toLowerCase();

    const cards = Array.from(listEl.querySelectorAll('[data-practice-card]'));
    for (const el of cards) {
      if (!(el instanceof HTMLElement)) continue;
      const d = (el.getAttribute('data-practice-date') || '').toLowerCase();
      const t = (el.getAttribute('data-practice-title') || '').toLowerCase();
      const hit = !query || `${d} ${t}`.includes(query);
      el.hidden = !hit;
    }
    updatePracticeCount();
  };

  if (practiceSearchInput instanceof HTMLInputElement) {
    practiceSearchInput.addEventListener('input', () => {
      applyPracticeFilter(practiceSearchInput.value);
      const clearBtn = document.querySelector('[data-action="practice-search-clear"]');
      if (clearBtn instanceof HTMLElement) clearBtn.hidden = !String(practiceSearchInput.value ?? '').trim();
    });
    // 初期件数
    applyPracticeFilter('');
  } else {
    // 検索UIが無いページでもエラーにしない
    try { updatePracticeCount(); } catch { /* ignore */ }
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="practice-search-clear"]');
    if (!btn) return;
    if (practiceSearchInput instanceof HTMLInputElement) {
      practiceSearchInput.value = '';
      practiceSearchInput.focus();
      applyPracticeFilter('');
    }
    if (btn instanceof HTMLElement) btn.hidden = true;
  });

  const openPracticeCardById = (id) => {
    // カレンダーからはモーダルを開く
    openPracticeModal(id);
  };

  // 右側：表示切替（リスト/カレンダー）
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="practice-view"]');
    if (!btn) return;
    const view = btn.getAttribute('data-view');
    if (view === 'list' || view === 'calendar') setPracticeView(view);
  });

  // カレンダーは表示時に初期化/描画する（hidden 初期化を避ける）
  initPracticeCalendarIfNeeded();

  // 初期表示（前回選択を復元）
  try {
    const saved = localStorage.getItem('swimPracticeView');
    if (saved === 'calendar') setPracticeView('calendar');
    else setPracticeView('list');
  } catch {
    setPracticeView('list');
  }



  // 行の選択
  plannerRoot.addEventListener('click', (e) => {
    const tr = e.target.closest('tr.lane-row');
    if (!tr) return;

    const lane = tr.getAttribute('data-lane');
    const idx = Number(tr.getAttribute('data-row-index'));
    if (!lane || !Number.isInteger(idx)) return;

    if (selected.lane === lane && selected.index === idx) {
      clearSelection();
      return;
    }

    plannerRoot.querySelectorAll('.lane-row.is-selected').forEach((row) => row.classList.remove('is-selected'));
    tr.classList.add('is-selected');
    selected = { lane, index: idx };
    updateToolbarSelectionState();
  });

  // 入力反映
  plannerRoot.addEventListener('input', (e) => {
    const el = e.target;
    if (!(el instanceof HTMLElement)) return;

    const tr = el.closest('tr.lane-row');
    if (!tr) return;

    const lane = tr.getAttribute('data-lane');
    const idx = Number(tr.getAttribute('data-row-index'));
    const field = el.getAttribute('data-field');

    if (!lane || !Number.isInteger(idx) || !field) return;

    const value = (el instanceof HTMLInputElement || el instanceof HTMLSelectElement) ? el.value : '';

    const targets = targetLanesForEdit(lane);
    const laneKey = viewLaneFor(lane);

    // 存在しない行は安全に補完してから更新
    const ensureRowAt = (laneNo, index) => {
      state.lanes[laneNo] = state.lanes[laneNo] || [];
      while (state.lanes[laneNo].length <= index) state.lanes[laneNo].push(defaultRow());
      return state.lanes[laneNo][index];
    };

    // all の場合は 1〜6 を同期、通常は該当レーンのみ
    const applyTo = (targets.length > 0) ? targets : [laneKey];
    for (const t of applyTo) {
      const row = ensureRowAt(t, idx);
      if (field === 'dist' || field === 'reps') {
        row[field] = parseMaybeNumber(value, 0);
      } else {
        row[field] = value;
      }
    }

    // 表示中の行（all の場合は代表レーン=1）を基準に表示更新
    const viewRow = (state.lanes[laneKey] || [])[idx];
    if (!viewRow) return;

    // 行距離/合計更新
    const totalCell = tr.querySelector('[data-row-total]');
    if (totalCell) {
      const total = rowTotalDistance(viewRow);
      totalCell.textContent = total ? `${total}m` : '';
    }

    const totalEl = plannerRoot.querySelector(`[data-lane-total][data-lane="${lane}"]`);
    if (totalEl) totalEl.textContent = `${laneTotalDistance(state.lanes[laneKey] || [])}m`;

    syncHiddenFields();
  });

  // フォーム送信前に同期
  const form = plannerRoot.closest('form');
  if (form) {
    form.addEventListener('submit', () => {
      // テキスト編集側の値をhiddenへ反映
      if (menuTextEditor && menuTextHidden) {
        const raw = String(menuTextEditor.value ?? '').trim();
        if (raw.length > 0) menuTextHidden.value = raw;
      }
      syncHiddenFields();
    });
  }

  // 初期描画
  renderAllLanes();
  setActiveLane('attendance');
})();
