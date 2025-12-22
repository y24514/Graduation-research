// 日記機能のJavaScript

// 文字数カウント
document.addEventListener('DOMContentLoaded', function() {
    const contentTextarea = document.getElementById('content');
    const charCount = document.getElementById('charCount');
    
    if (contentTextarea && charCount) {
        // 初期表示
        charCount.textContent = contentTextarea.value.length;
        
        // 入力時に更新
        contentTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // 成功メッセージを3秒後に自動で消す
    const successBanner = document.querySelector('.message-banner.success');
    if (successBanner) {
        setTimeout(() => {
            successBanner.style.animation = 'slideUp 0.3s ease-out';
            setTimeout(() => {
                successBanner.remove();
            }, 300);
        }, 3000);
    }
});

// フォームをリセット
function resetForm() {
    const form = document.getElementById('diaryForm');
    if (form) {
        form.reset();
        // 日付を今日にリセット
        const dateInput = document.getElementById('diary_date');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.value = today;
        }
        // 文字数カウントをリセット
        const charCount = document.getElementById('charCount');
        if (charCount) {
            charCount.textContent = '0';
        }
    }
}

// 日記を編集
function editDiary(id, date, title, content) {
    // フォームに値をセット
    const dateInput = document.getElementById('diary_date');
    const titleInput = document.getElementById('title');
    const contentInput = document.getElementById('content');
    
    if (dateInput) dateInput.value = date;
    if (titleInput) titleInput.value = title;
    if (contentInput) {
        contentInput.value = content;
        // 文字数カウントを更新
        const charCount = document.getElementById('charCount');
        if (charCount) {
            charCount.textContent = content.length;
        }
    }
    
    // フォームまでスクロール
    const formSection = document.querySelector('.diary-form-section');
    if (formSection) {
        formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // フォームをハイライト
        formSection.style.animation = 'highlight 1s ease-out';
    }
    
    // タイトルにフォーカス（編集していることを明示）
    setTimeout(() => {
        if (titleInput) titleInput.focus();
    }, 500);
}

// アニメーション用のスタイルを動的に追加
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }
    
    @keyframes highlight {
        0% {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        50% {
            box-shadow: 0 0 20px rgba(52, 152, 219, 0.5);
        }
        100% {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    }
`;
document.head.appendChild(style);
