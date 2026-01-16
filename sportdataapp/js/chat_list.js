// チャットリストのJavaScript（統合UI用）

let currentChatType = null;
let currentChatId = null;
let messageCheckInterval = null;

// タブ別セッション対応: URLの tab_id をAJAXにも付与する
function getTabId() {
    try {
        const params = new URLSearchParams(window.location.search);
        return params.get('tab_id') || '';
    } catch (e) {
        return '';
    }
}

function appendTabIdToUrl(url) {
    const tabId = getTabId();
    if (!tabId) return url;
    return url + (url.includes('?') ? '&' : '?') + 'tab_id=' + encodeURIComponent(tabId);
}

function withTabIdData(data) {
    const tabId = getTabId();
    if (!tabId) return data;
    return Object.assign({}, data || {}, { tab_id: tabId });
}

document.addEventListener('DOMContentLoaded', function() {
    // URLパラメータから自動的にチャットを開く
    const urlParams = new URLSearchParams(window.location.search);
    const chatType = urlParams.get('type');
    const chatId = urlParams.get('id');
    
    if (chatType && chatId) {
        // URLパラメータで指定されたチャットを開く
        loadChat(chatType, chatId);
        // URLをクリーンにする（type/idのみ削除。tab_idは維持）
        urlParams.delete('type');
        urlParams.delete('id');
        const qs = urlParams.toString();
        window.history.replaceState({}, '', window.location.pathname + (qs ? ('?' + qs) : ''));
    } else {
        // 最初のチャットを自動選択（オプション）
        const firstChat = document.querySelector('.chat-item');
        if (firstChat) {
            // firstChat.click(); // 自動で最初のチャットを開く場合
        }
    }
});

// チャットをロード
function loadChat(type, id) {
    currentChatType = type;
    currentChatId = id;
    
    // アクティブ状態を更新
    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.remove('active');
    });
    
    const activeItem = document.querySelector(`.chat-item[data-type="${type}"][data-id="${id}"]`);
    if (activeItem) {
        activeItem.classList.add('active');
    }
    
    // チャット画面をロード
    const params = type === 'group' 
        ? `type=group&chat_group_id=${id}`
        : `type=direct&recipient=${encodeURIComponent(id)}`;
    
    $.ajax({
        url: appendTabIdToUrl(`chat_content.php?${params}`),
        method: 'GET',
        success: function(response) {
            $('#chatMain').html(response);
            scrollToBottom();
            setupMessageForm();
            
            // 既読状態を記録
            markAsRead(type, id);
            
            // 定期的なメッセージ更新を開始
            if (messageCheckInterval) {
                clearInterval(messageCheckInterval);
            }
            messageCheckInterval = setInterval(function() {
                refreshMessages();
            }, 5000); // 5秒ごと
        },
        error: function() {
            $('#chatMain').html('<div class="empty-chat"><p>チャットの読み込みに失敗しました</p></div>');
        }
    });
}

// メッセージフォームの設定
function setupMessageForm() {
    const messageInput = document.getElementById('message');
    const chatForm = document.getElementById('chatForm');
    const sendBtn = document.getElementById('sendBtn');
    
    if (!messageInput || !chatForm || !sendBtn) return;
    
    // テキストエリアの自動リサイズ
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        
        // 送信ボタンの状態管理
        if (this.value.trim() === '') {
            sendBtn.disabled = true;
            sendBtn.style.opacity = '0.5';
        } else {
            sendBtn.disabled = false;
            sendBtn.style.opacity = '1';
        }
    });
    
    // Enterキーで送信
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (this.value.trim() !== '') {
                sendMessage();
            }
        }
    });
    
    // フォーム送信をインターセプト
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    // 初期状態
    if (messageInput.value.trim() === '') {
        sendBtn.disabled = true;
        sendBtn.style.opacity = '0.5';
    }
}

// メッセージ送信
function sendMessage() {
    const messageInput = document.getElementById('message');
    const sendBtn = document.getElementById('sendBtn');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    // 送信ボタンを無効化
    sendBtn.disabled = true;
    sendBtn.textContent = '送信中...';
    
    const params = currentChatType === 'group' 
        ? `type=group&chat_group_id=${currentChatId}`
        : `type=direct&recipient=${encodeURIComponent(currentChatId)}`;
    
    $.ajax({
        url: appendTabIdToUrl(`chat_content.php?${params}`),
        method: 'POST',
        data: withTabIdData({
            send_message: '1',
            message: message
        }),
        success: function(response) {
            $('#chatMain').html(response);
            scrollToBottom();
            setupMessageForm();
        },
        error: function() {
            alert('メッセージの送信に失敗しました');
            sendBtn.disabled = false;
            sendBtn.textContent = '送信';
        }
    });
}

// メッセージを更新（新しいメッセージがある場合）
function refreshMessages() {
    if (!currentChatType || !currentChatId) return;
    
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;

    // 末尾メッセージID（新着取得用）
    const lastEl = chatMessages.querySelector('.message-item:last-child');
    const lastId = lastEl ? parseInt(lastEl.getAttribute('data-message-id') || '0', 10) : 0;
    
    const params = currentChatType === 'group' 
        ? `type=group&chat_group_id=${currentChatId}`
        : `type=direct&recipient=${encodeURIComponent(currentChatId)}`;
    
    // 現在のスクロール位置を保存
    const isScrolledToBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= chatMessages.scrollTop + 50;
    
    $.ajax({
        url: appendTabIdToUrl(`chat_messages.php?${params}&after_id=${Number.isFinite(lastId) ? lastId : 0}`),
        method: 'GET',
        success: function(response) {
            const html = (response || '').trim();
            if (html === '') {
                return; // 新着なし
            }

            const tmp = document.createElement('div');
            tmp.innerHTML = html;

            // 既存メッセージの重複挿入を防ぐ（重複すると表示アニメーションが定期的に再発する）
            const nodes = Array.from(tmp.childNodes);
            nodes.forEach(node => {
                if (node.nodeType !== Node.ELEMENT_NODE) {
                    return;
                }

                const el = /** @type {HTMLElement} */ (node);
                if (el.classList.contains('message-item')) {
                    const idAttr = el.getAttribute('data-message-id');
                    if (idAttr) {
                        const exists = chatMessages.querySelector(`.message-item[data-message-id="${CSS.escape(idAttr)}"]`);
                        if (exists) {
                            return;
                        }
                    }
                }
                chatMessages.appendChild(el);
            });

            if (isScrolledToBottom) {
                scrollToBottom();
            }
        }
    });
}

// 最新メッセージまでスクロール
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        setTimeout(function() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 100);
    }
}

// グループ作成モーダルを開く
function openCreateGroupModal() {
    const modal = document.getElementById('createGroupModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// グループ作成モーダルを閉じる
function closeCreateGroupModal() {
    const modal = document.getElementById('createGroupModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // フォームをリセット
        const form = document.getElementById('createGroupForm');
        if (form) {
            form.reset();
        }
    }
}

// モーダル外クリックで閉じる
document.addEventListener('click', function(e) {
    const modal = document.getElementById('createGroupModal');
    if (modal && e.target === modal) {
        closeCreateGroupModal();
    }
});

// Escapeキーでモーダルを閉じる
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateGroupModal();
    }
});

// グループ作成フォーム送信
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createGroupForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const groupName = document.getElementById('groupName').value.trim();
            const groupDescription = document.getElementById('groupDescription').value.trim();
            const selectedMembers = Array.from(document.querySelectorAll('input[name="members[]"]:checked'))
                .map(cb => cb.value);
            
            if (!groupName) {
                alert('グループ名を入力してください');
                return;
            }
            
            // 送信ボタンを無効化
            const submitBtn = document.querySelector('#createGroupModal .btn-primary');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = '作成中...';
            }
            
            $.ajax({
                url: appendTabIdToUrl('../PHP/create_group_ajax.php'),
                method: 'POST',
                data: withTabIdData({
                    group_name: groupName,
                    group_description: groupDescription,
                    members: selectedMembers
                }),
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);
                    if (response.success) {
                        alert('グループを作成しました！');
                        closeCreateGroupModal();
                        // ページをリロードしてグループリストを更新
                        location.reload();
                    } else {
                        alert('エラー: ' + (response.message || 'グループの作成に失敗しました'));
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = '作成';
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', xhr.responseText);
                    alert('通信エラーが発生しました: ' + error);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = '作成';
                    }
                }
            });
        });
    }
});

// グループ設定を読み込む
function loadGroupSettings(chatGroupId) {
    if (messageCheckInterval) {
        clearInterval(messageCheckInterval);
    }
    
    $.ajax({
        url: appendTabIdToUrl('../PHP/group_settings_ajax.php?chat_group_id=' + chatGroupId),
        method: 'GET',
        success: function(response) {
            $('#chatMain').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Settings load error:', error);
            alert('設定の読み込みに失敗しました');
        }
    });
}

// チャットに戻る
function backToChat() {
    if (currentChatType && currentChatId) {
        loadChat(currentChatType, currentChatId);
    }
}

// メンバーを追加
function addMember(userId, chatGroupId) {
    if (!confirm('このメンバーを追加しますか?')) {
        return;
    }
    
    $.ajax({
        url: appendTabIdToUrl('../PHP/add_member_ajax.php'),
        method: 'POST',
        data: withTabIdData({
            user_id: userId,
            chat_group_id: chatGroupId
        }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('メンバーを追加しました');
                loadGroupSettings(chatGroupId);
            } else {
                alert('追加に失敗しました: ' + response.error);
            }
        },
        error: function() {
            alert('通信エラーが発生しました');
        }
    });
}

// メンバーを削除
function removeMember(userId, chatGroupId) {
    if (!confirm('このメンバーを削除しますか?')) {
        return;
    }
    
    $.ajax({
        url: appendTabIdToUrl('../PHP/remove_member_ajax.php'),
        method: 'POST',
        data: withTabIdData({
            user_id: userId,
            chat_group_id: chatGroupId
        }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('メンバーを削除しました');
                loadGroupSettings(chatGroupId);
            } else {
                alert('削除に失敗しました: ' + response.error);
            }
        },
        error: function() {
            alert('通信エラーが発生しました');
        }
    });
}

// グループを削除
function deleteGroup(chatGroupId) {
    if (!confirm('本当にこのグループを削除しますか?\nこの操作は取り消せません。')) {
        return;
    }
    
    $.ajax({
        url: appendTabIdToUrl('../PHP/delete_group_ajax.php'),
        method: 'POST',
        data: withTabIdData({
            chat_group_id: chatGroupId
        }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('グループを削除しました');
                location.reload();
            } else {
                alert('削除に失敗しました: ' + response.error);
            }
        },
        error: function() {
            alert('通信エラーが発生しました');
        }
    });
}

// 既読状態を記録
function markAsRead(type, id) {
    const data = {
        chat_type: type
    };
    
    if (type === 'group') {
        data.chat_group_id = id;
    } else {
        data.recipient_id = id;
    }
    
    console.log('Mark as read:', data);
    
    $.ajax({
        url: appendTabIdToUrl('../PHP/mark_as_read.php'),
        method: 'POST',
        data: withTabIdData(data),
        dataType: 'json',
        success: function(response) {
            console.log('Mark as read response:', response);
            if (response.success) {
                // 未読バッジを削除
                const chatItem = document.querySelector(`.chat-item[data-type="${type}"][data-id="${id}"]`);
                if (chatItem) {
                    const badge = chatItem.querySelector('.unread-badge');
                    if (badge) {
                        badge.remove();
                    }
                }
                // チャットリストを更新して未読数を再計算
                updateChatListUnreadCounts();
            } else {
                console.error('既読状態の更新に失敗:', response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('既読状態の更新エラー:', error, xhr.responseText);
        }
    });
}

// チャットリストの未読数を更新
function updateChatListUnreadCounts() {
    // 既にチャットが開かれている場合のみ実行
    if (!currentChatType || !currentChatId) return;
    
    // 現在開いているチャットの未読バッジを確実に削除
    setTimeout(function() {
        const chatItem = document.querySelector(`.chat-item[data-type="${currentChatType}"][data-id="${currentChatId}"]`);
        if (chatItem) {
            const badge = chatItem.querySelector('.unread-badge');
            if (badge) {
                badge.remove();
            }
        }
    }, 500);
}

// メッセージを削除
let pendingDeleteMessageId = null;

function deleteMessage(messageId) {
    pendingDeleteMessageId = messageId;
    openDeleteConfirmModal();
}

function openDeleteConfirmModal() {
    const modal = document.getElementById('deleteConfirmModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeDeleteConfirmModal() {
    const modal = document.getElementById('deleteConfirmModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    pendingDeleteMessageId = null;
}

function confirmDelete() {
    if (!pendingDeleteMessageId) {
        closeDeleteConfirmModal();
        return;
    }
    
    $.ajax({
        url: appendTabIdToUrl('../PHP/delete_message_ajax.php'),
        method: 'POST',
        data: withTabIdData({
            message_id: pendingDeleteMessageId
        }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // メッセージを再読み込み
                refreshMessages();
                closeDeleteConfirmModal();
            } else {
                alert('削除に失敗しました: ' + (response.error || ''));
                closeDeleteConfirmModal();
            }
        },
        error: function() {
            alert('通信エラーが発生しました');
            closeDeleteConfirmModal();
        }
    });
}

// 削除ボタンを表示/非表示切り替え
function toggleDeleteButton(element) {
    const wrapper = element.closest('.message-bubble-wrapper');
    const allWrappers = document.querySelectorAll('.message-bubble-wrapper');
    
    // 他のすべての削除ボタンを非表示
    allWrappers.forEach(w => {
        if (w !== wrapper) {
            w.classList.remove('active');
        }
    });
    
    // クリックしたメッセージの削除ボタンを切り替え
    wrapper.classList.toggle('active');
}

// ドキュメント全体でクリック時に削除ボタンを非表示
document.addEventListener('click', function(e) {
    // メッセージバブルまたは削除ボタンのクリックではない場合
    if (!e.target.closest('.message-bubble') && !e.target.closest('.message-delete-btn')) {
        document.querySelectorAll('.message-bubble-wrapper').forEach(w => {
            w.classList.remove('active');
        });
    }
});
