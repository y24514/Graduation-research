// チャットリストのJavaScript（統合UI用）

let currentChatType = null;
let currentChatId = null;
let messageCheckInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    // URLパラメータから自動的にチャットを開く
    const urlParams = new URLSearchParams(window.location.search);
    const chatType = urlParams.get('type');
    const chatId = urlParams.get('id');
    
    if (chatType && chatId) {
        // URLパラメータで指定されたチャットを開く
        loadChat(chatType, chatId);
        // URLをクリーンにする（パラメータを削除）
        window.history.replaceState({}, '', window.location.pathname);
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
        url: `chat_content.php?${params}`,
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
        url: `chat_content.php?${params}`,
        method: 'POST',
        data: {
            send_message: '1',
            message: message
        },
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
    
    const params = currentChatType === 'group' 
        ? `type=group&chat_group_id=${currentChatId}`
        : `type=direct&recipient=${encodeURIComponent(currentChatId)}`;
    
    // 現在のスクロール位置を保存
    const isScrolledToBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= chatMessages.scrollTop + 50;
    
    $.ajax({
        url: `chat_messages.php?${params}`,
        method: 'GET',
        success: function(response) {
            const currentCount = chatMessages.querySelectorAll('.message-item').length;
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = response;
            const newCount = tempDiv.querySelectorAll('.message-item').length;
            
            if (newCount > currentCount) {
                chatMessages.innerHTML = response;
                if (isScrolledToBottom) {
                    scrollToBottom();
                }
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
                url: '../PHP/create_group_ajax.php',
                method: 'POST',
                data: {
                    group_name: groupName,
                    group_description: groupDescription,
                    members: selectedMembers
                },
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
        url: '../PHP/group_settings_ajax.php?chat_group_id=' + chatGroupId,
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
        url: '../PHP/add_member_ajax.php',
        method: 'POST',
        data: {
            user_id: userId,
            chat_group_id: chatGroupId
        },
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
        url: '../PHP/remove_member_ajax.php',
        method: 'POST',
        data: {
            user_id: userId,
            chat_group_id: chatGroupId
        },
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
        url: '../PHP/delete_group_ajax.php',
        method: 'POST',
        data: {
            chat_group_id: chatGroupId
        },
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
        url: '../PHP/mark_as_read.php',
        method: 'POST',
        data: data,
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
            } else {
                console.error('既読状態の更新に失敗:', response.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('既読状態の更新エラー:', error, xhr.responseText);
        }
    });
}
