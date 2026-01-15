// 日記機能 - モダンなAJAX実装

// モーダル関連の変数
let isEditMode = false;
let editingDiaryId = null;
let pendingDeleteDiaryId = null;

// タブ別セッション対応: tab_id をAJAXにも付与する
function getTabId() {
    try {
        const params = new URLSearchParams(window.location.search);
        const tabId = params.get('tab_id');
        return tabId ? String(tabId) : '';
    } catch (e) {
        return '';
    }
}

function appendTabIdToUrl(url) {
    const tabId = getTabId();
    if (!tabId) return url;
    return url + (url.includes('?') ? '&' : '?') + 'tab_id=' + encodeURIComponent(tabId);
}

function appendTabIdToBody(body) {
    const tabId = getTabId();
    if (!tabId) return body;
    return body + '&tab_id=' + encodeURIComponent(tabId);
}

// ページ読み込み時の初期化
document.addEventListener('DOMContentLoaded', function() {
    // 検索ボックスのイベントリスナー
    const searchBox = document.querySelector('.search-box input');
    if (searchBox) {
        searchBox.addEventListener('input', filterDiaries);
    }

    // タグフィルターのイベントリスナー
    setupTagFilters();

    // モーダルの閉じるボタン
    const closeModalBtn = document.getElementById('closeDiaryModal');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeDiaryModal);
    }

    // モーダルの背景クリックで閉じる
    const modal = document.getElementById('diaryModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeDiaryModal();
            }
        });
    }

    // フォーム送信
    const diaryForm = document.getElementById('diaryForm');
    if (diaryForm) {
        diaryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveDiary();
        });
    }

    // 今日の日付をデフォルトにセット
    const dateInput = document.getElementById('diaryDate');
    if (dateInput && !dateInput.value) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;
    }

    // 文字数カウント
    const contentTextarea = document.getElementById('diaryContent');
    const charCount = document.getElementById('modal-char-count');
    if (contentTextarea && charCount) {
        contentTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // 削除確認モーダル：背景クリックで閉じる
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    if (deleteConfirmModal) {
        deleteConfirmModal.addEventListener('click', function(e) {
            if (e.target === deleteConfirmModal) {
                closeDeleteConfirmModal();
            }
        });
    }
});

function openDeleteConfirmModal(id) {
    pendingDeleteDiaryId = id;

    const okBtn = document.getElementById('deleteConfirmOkBtn');
    if (okBtn) {
        okBtn.disabled = false;
        okBtn.textContent = '削除';
    }

    const modal = document.getElementById('deleteConfirmModal');
    if (!modal) return;

    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
}

function closeDeleteConfirmModal() {
    const modal = document.getElementById('deleteConfirmModal');
    if (!modal) return;

    modal.classList.remove('active');
    setTimeout(() => {
        modal.style.display = 'none';
        pendingDeleteDiaryId = null;
    }, 300);
}

function confirmDeleteDiary() {
    const id = pendingDeleteDiaryId;
    if (!id) {
        alert('削除する日記が選択されていません');
        return;
    }

    const okBtn = document.getElementById('deleteConfirmOkBtn');
    const originalText = okBtn ? okBtn.textContent : '削除';
    if (okBtn) {
        okBtn.disabled = true;
        okBtn.textContent = '削除中...';
    }

    // サーバーに送信
    fetch(appendTabIdToUrl('diary_api.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: appendTabIdToBody('action=delete&id=' + encodeURIComponent(id))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('日記を削除しました', 'success');
            closeDeleteConfirmModal();
            closeDiaryModal();

            // JSで画面から削除（リロードなし）
            const card = document.querySelector('.diary-card[data-id="' + id + '"]');
            const monthSection = card ? card.closest('.diary-month') : null;
            if (card) {
                card.remove();
            }

            // 月セクションが空なら削除
            if (monthSection && monthSection.querySelectorAll('.diary-card').length === 0) {
                monthSection.remove();
            }

            // フィルター状態/空表示を更新
            if (typeof filterDiaries === 'function') {
                filterDiaries();
            } else {
                const remaining = document.querySelectorAll('.diary-card').length;
                updateEmptyState(remaining === 0, false);
            }
        } else {
            alert(data.message || '削除に失敗しました');
            if (okBtn) {
                okBtn.disabled = false;
                okBtn.textContent = originalText;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました');
        if (okBtn) {
            okBtn.disabled = false;
            okBtn.textContent = originalText;
        }
    });
}

// 新しい日記を書くボタン
function openDiaryModal() {
    isEditMode = false;
    editingDiaryId = null;

    // フォームをリセット
    document.getElementById('diaryForm').reset();
    
    // 今日の日付をセット
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('diaryDate').value = today;

    // モーダルタイトルを変更
    document.querySelector('.modal-header h2').textContent = '新しい日記を書く';
    
    // 削除ボタンを非表示
    document.getElementById('deleteBtn').style.display = 'none';

    // モーダルを表示
    const modal = document.getElementById('diaryModal');
    modal.style.display = 'flex';
    
    // アニメーション
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);

    // タイトル入力にフォーカス
    setTimeout(() => {
        document.getElementById('diaryTitle').focus();
    }, 300);
}

// 日記を編集
function editDiary(id) {
    isEditMode = true;
    editingDiaryId = id;

    // サーバーから日記データを取得
    fetch(appendTabIdToUrl('diary_api.php?action=get&id=' + encodeURIComponent(id)))
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const diary = data.data;
            
            // フォームに値をセット
            document.getElementById('diaryDate').value = diary.diary_date;
            document.getElementById('diaryTitle').value = diary.title;
            document.getElementById('diaryContent').value = diary.content;
            document.getElementById('diaryTags').value = diary.tags || '';

            // 文字数カウントを更新
            const charCount = document.getElementById('modal-char-count');
            if (charCount) {
                charCount.textContent = diary.content.length;
            }

            // モーダルタイトルを変更
            document.querySelector('.modal-header h2').textContent = '日記を編集';
            
            // 削除ボタンを表示
            document.getElementById('deleteBtn').style.display = 'inline-flex';

            // モーダルを表示
            const modal = document.getElementById('diaryModal');
            modal.style.display = 'flex';
            
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        } else {
            alert('日記の読み込みに失敗しました');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました');
    });
}

// モーダルを閉じる
function closeDiaryModal() {
    const modal = document.getElementById('diaryModal');
    modal.classList.remove('active');
    
    setTimeout(() => {
        modal.style.display = 'none';
        document.getElementById('diaryForm').reset();
        isEditMode = false;
        editingDiaryId = null;
    }, 300);
}

// 日記を保存
function saveDiary() {
    const date = document.getElementById('diaryDate').value;
    const title = document.getElementById('diaryTitle').value;
    const content = document.getElementById('diaryContent').value;
    const tags = document.getElementById('diaryTags').value;

    // バリデーション
    if (!date || !content) {
        alert('日付と内容を入力してください');
        return;
    }

    // ボタンを無効化
    const saveBtn = document.querySelector('.save-btn');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = '保存中...';

    // データを準備
    let formData = 'action=save';
    formData += '&diary_date=' + encodeURIComponent(date);
    formData += '&title=' + encodeURIComponent(title);
    formData += '&content=' + encodeURIComponent(content);
    formData += '&tags=' + encodeURIComponent(tags);
    
    if (isEditMode && editingDiaryId) {
        formData += '&id=' + editingDiaryId;
    }

    formData = appendTabIdToBody(formData);

    // サーバーに送信
    fetch(appendTabIdToUrl('diary_api.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 成功メッセージ
            showMessage(isEditMode ? '日記を更新しました' : '日記を保存しました', 'success');
            
            // モーダルを閉じる
            closeDiaryModal();
            
            // ページをリロード
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert(data.message || '保存に失敗しました');
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました');
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    });
}

// 日記を削除
function deleteDiary(id) {
    // 削除はモーダル内からのみ
    if (!id && editingDiaryId) {
        id = editingDiaryId;
    }

    if (!id) {
        alert('削除する日記が選択されていません');
        return;
    }

    openDeleteConfirmModal(id);
}

// 日記を管理者に提出/取消
function toggleSubmitDiary(id) {
    const card = document.querySelector('.diary-card[data-id="' + id + '"]');
    const btn = card ? card.querySelector('button[data-submit-btn="1"]') : null;
    if (!btn) {
        alert('提出ボタンが見つかりません');
        return;
    }

    const isSubmitted = (btn.dataset.submitted === '1');
    const action = isSubmitted ? 'unsubmit' : 'submit';

    btn.disabled = true;

    fetch(appendTabIdToUrl('diary_api.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: appendTabIdToBody('action=' + encodeURIComponent(action) + '&id=' + encodeURIComponent(id))
    })
    .then(response => response.json())
    .then(data => {
        if (!data || !data.success) {
            alert((data && data.message) ? data.message : '更新に失敗しました');
            btn.disabled = false;
            return;
        }

        const nowSubmitted = !isSubmitted;
        btn.dataset.submitted = nowSubmitted ? '1' : '0';
        btn.title = nowSubmitted ? '提出を取り消す' : '管理者に提出';

        // バッジ更新
        const dateRow = card ? card.querySelector('.diary-card-date') : null;
        const badge = dateRow ? dateRow.querySelector('.submit-badge') : null;
        if (nowSubmitted) {
            if (!badge && dateRow) {
                const b = document.createElement('span');
                b.className = 'submit-badge';
                b.textContent = '提出済み';
                dateRow.appendChild(b);
            }
            showMessage('管理者に提出しました', 'success');
        } else {
            if (badge) badge.remove();
            showMessage('提出を取り消しました', 'success');
        }

        btn.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました');
        btn.disabled = false;
    });
}

// 提出された日記に対する管理者フィードバック保存
function saveDiaryFeedback(id) {
    const textarea = document.getElementById('feedbackText-' + id);
    const btn = document.getElementById('feedbackBtn-' + id);
    const meta = document.getElementById('feedbackMeta-' + id);

    if (!textarea || !btn) {
        alert('フィードバック入力欄が見つかりません');
        return;
    }

    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '保存中...';

    const feedback = textarea.value || '';
    const body = appendTabIdToBody(
        'action=feedback&id=' + encodeURIComponent(id) + '&feedback=' + encodeURIComponent(feedback)
    );

    fetch(appendTabIdToUrl('diary_api.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body
    })
    .then(response => response.json())
    .then(data => {
        if (!data || !data.success) {
            alert((data && data.message) ? data.message : '保存に失敗しました');
            btn.disabled = false;
            btn.textContent = originalText;
            return;
        }

        showMessage('フィードバックを保存しました', 'success');
        if (meta) {
            const now = new Date();
            meta.textContent = '最終更新: ' + now.toLocaleString('ja-JP');
        }

        btn.disabled = false;
        btn.textContent = originalText;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました');
        btn.disabled = false;
        btn.textContent = originalText;
    });
}

// 検索・フィルタリング機能
function filterDiaries() {
    const searchTerm = document.querySelector('.search-box input').value.toLowerCase();
    const cards = document.querySelectorAll('.diary-card');
    const activeFilters = getActiveTagFilters();

    cards.forEach(card => {
        const titleEl = card.querySelector('.diary-card-title');
        const contentEl = card.querySelector('.diary-card-content');

        const title = titleEl ? titleEl.textContent.toLowerCase() : '';
        const content = contentEl ? contentEl.textContent.toLowerCase() : '';
        const tags = Array.from(card.querySelectorAll('.tag')).map(tag => tag.textContent.toLowerCase());

        // 検索キーワードのチェック
        const matchesSearch = !searchTerm || title.includes(searchTerm) || content.includes(searchTerm);

        // タグフィルターのチェック
        const matchesTags = activeFilters.length === 0 || activeFilters.some(filter => tags.includes(filter.toLowerCase()));

        // 両方の条件を満たす場合のみ表示
        if (matchesSearch && matchesTags) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });

    // 月セクション内に表示カードが無ければ、その月を隠す
    document.querySelectorAll('.diary-month').forEach(section => {
        const visibleInMonth = Array.from(section.querySelectorAll('.diary-card'))
            .some(card => card.style.display !== 'none');
        section.style.display = visibleInMonth ? '' : 'none';
    });

    // 結果がない場合
    const visibleCards = Array.from(cards).filter(card => card.style.display !== 'none').length;
    updateEmptyState(visibleCards === 0, searchTerm || activeFilters.length > 0);
}

// タグフィルターのセットアップ
function setupTagFilters() {
    // 全てのタグを収集
    const allTags = new Set();
    document.querySelectorAll('.diary-card .tag').forEach(tag => {
        allTags.add(tag.textContent.trim());
    });

    // フィルタータグエリアにタグを追加
    const filterTagsContainer = document.querySelector('.filter-tags');
    if (filterTagsContainer && allTags.size > 0) {
        filterTagsContainer.innerHTML = '<span style="color: #64748b; font-size: 14px;">タグで絞り込み:</span>';
        
        allTags.forEach(tagText => {
            const filterTag = document.createElement('span');
            filterTag.className = 'filter-tag';
            filterTag.textContent = tagText;
            filterTag.onclick = function() {
                this.classList.toggle('active');
                filterDiaries();
            };
            filterTagsContainer.appendChild(filterTag);
        });
    }
}

// アクティブなタグフィルターを取得
function getActiveTagFilters() {
    const activeTags = [];
    document.querySelectorAll('.filter-tag.active').forEach(tag => {
        activeTags.push(tag.textContent);
    });
    return activeTags;
}

// 空の状態を更新
function updateEmptyState(isEmpty, isFiltering) {
    let emptyState = document.querySelector('.empty-state');
    const diaryGrid = document.querySelector('.diary-grid');

    if (isEmpty) {
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.className = 'empty-state';
            diaryGrid.parentNode.insertBefore(emptyState, diaryGrid.nextSibling);
        }

        if (isFiltering) {
            emptyState.innerHTML = `
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <p>検索条件に一致する日記が見つかりません</p>
            `;
        } else {
            emptyState.innerHTML = `
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <p>まだ日記がありません</p>
                <p style="font-size: 14px; color: #94a3b8;">「新しい日記を書く」ボタンから日記を作成しましょう</p>
            `;
        }
        emptyState.style.display = 'flex';
        diaryGrid.style.display = 'none';
    } else {
        if (emptyState) {
            emptyState.style.display = 'none';
        }
        diaryGrid.style.display = 'block';
    }
}

// メッセージ表示
function showMessage(message, type = 'success') {
    // 既存のメッセージを削除
    const existingMessage = document.querySelector('.toast-message');
    if (existingMessage) {
        existingMessage.remove();
    }

    // メッセージ要素を作成
    const messageDiv = document.createElement('div');
    messageDiv.className = 'toast-message ' + type;
    messageDiv.textContent = message;
    document.body.appendChild(messageDiv);

    // アニメーション
    setTimeout(() => {
        messageDiv.classList.add('show');
    }, 10);

    // 3秒後に削除
    setTimeout(() => {
        messageDiv.classList.remove('show');
        setTimeout(() => {
            messageDiv.remove();
        }, 300);
    }, 3000);
}

