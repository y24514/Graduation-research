// チャット機能のJavaScript

document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('message');
    const chatForm = document.getElementById('chatForm');
    const sendBtn = document.getElementById('sendBtn');
    
    // 最新メッセージまでスクロール
    if (chatMessages) {
        scrollToBottom();
    }
    
    // テキストエリアの自動リサイズ
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        
        // Enterキーで送信、Shift+Enterで改行
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim() !== '') {
                    chatForm.submit();
                }
            }
        });
    }
    
    // 送信ボタンの状態管理
    if (messageInput && sendBtn) {
        messageInput.addEventListener('input', function() {
            if (this.value.trim() === '') {
                sendBtn.disabled = true;
                sendBtn.style.opacity = '0.5';
            } else {
                sendBtn.disabled = false;
                sendBtn.style.opacity = '1';
            }
        });
        
        // 初期状態
        if (messageInput.value.trim() === '') {
            sendBtn.disabled = true;
            sendBtn.style.opacity = '0.5';
        }
    }
    
    // 定期的にメッセージを更新（10秒ごと）
    setInterval(function() {
        // 静かに更新するため、新しいメッセージがある場合のみリロード
        checkNewMessages();
    }, 10000);
});

// 最新メッセージまでスクロール
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// 新しいメッセージをチェック（簡易版）
function checkNewMessages() {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    // 最後のメッセージのIDを取得
    const lastMessage = chatMessages.querySelector('.message-item:last-child');
    if (!lastMessage) return;
    
    // 現在のスクロール位置を保存
    const isScrolledToBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= chatMessages.scrollTop + 50;
    
    // 新しいメッセージがある場合はリロード
    // 注: 実際のプロダクションではAjaxで新しいメッセージのみ取得することを推奨
    if (isScrolledToBottom) {
        // スクロールが下部にある場合のみ自動更新
        // location.reload(); // 完全リロードはユーザー体験を損なうため、コメントアウト
    }
}

// メッセージ送信時のアニメーション
if (document.getElementById('chatForm')) {
    document.getElementById('chatForm').addEventListener('submit', function() {
        const sendBtn = document.getElementById('sendBtn');
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="send-icon">⏳</span>送信中...';
        }
    });
}

// タイムスタンプの相対時間表示（オプション）
function formatTimestamp(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'たった今';
    if (minutes < 60) return `${minutes}分前`;
    if (hours < 24) return `${hours}時間前`;
    if (days < 7) return `${days}日前`;
    
    return date.toLocaleDateString('ja-JP', { 
        year: 'numeric', 
        month: 'numeric', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
