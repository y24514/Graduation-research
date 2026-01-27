<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024">
    <title>登録情報編集 - Sports Analytics App</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/profile_edit.css">
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<?php require_once __DIR__ . '/../PHP/header.php'; ?>

<div class="profile-edit-container">
    <div class="profile-edit-header">
        <h1><i class="fas fa-user-edit"></i> 登録情報編集</h1>
        <p class="subtitle">プロフィール情報とパスワードの変更</p>
    </div>

    <?php if($success): ?>
    <div class="success-box">
        <i class="fas fa-check-circle"></i>
        <span><?= htmlspecialchars($success_message ?: '登録情報を更新しました', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <?php endif; ?>

    <?php if(!empty($errors)): ?>
    <div class="error-box">
        <i class="fas fa-exclamation-circle"></i>
        <div class="error-content">
            <h4>エラーが発生しました</h4>
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <form action="" method="post" id="profileEditForm" class="profile-form" enctype="multipart/form-data">
        <div class="form-sections">
            <!-- 基本情報セクション -->
            <div class="form-section">
                <h3><i class="fas fa-id-card"></i> 基本情報</h3>

                <div class="avatar-section">
                    <label class="avatar-label"><i class="fas fa-image"></i> ユーザーアイコン</label>
                    <div class="avatar-row">
                        <div class="avatar-preview" aria-label="現在のアイコン">
                            <?php if (!empty($user_icon_url)): ?>
                                <img id="userIconPreview" src="<?= htmlspecialchars($user_icon_url, ENT_QUOTES, 'UTF-8') ?>" alt="ユーザーアイコン">
                            <?php endif; ?>
                            <span id="userIconFallback" class="avatar-fallback" style="<?= !empty($user_icon_url) ? 'display:none' : '' ?>"><?= htmlspecialchars(mb_substr($user_data['name'] ?? '?', 0, 1, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="avatar-controls">
                            <input type="file" id="user_icon" name="user_icon" accept="image/*" class="avatar-input">
                            <small class="field-hint">画像を選択するとプレビューされます（PNG/JPG/GIF/WebP、最大2MB）</small>
                            <div class="avatar-actions">
                                <button type="submit" name="update_icon" class="btn-submit btn-submit--secondary">
                                    <i class="fas fa-image"></i> アイコンのみ保存
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="info-display">
                    <div class="info-item">
                        <label>団体ID</label>
                        <p><?= htmlspecialchars($user_data['group_id']) ?></p>
                    </div>
                    <div class="info-item">
                        <label>ユーザーID</label>
                        <p><?= htmlspecialchars($user_data['user_id']) ?></p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-signature"></i> 氏名
                        <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?= htmlspecialchars($user_data['name']) ?>"
                           required>
                    <span class="field-error" id="name_error"></span>
                </div>

                <div class="form-group">
                    <label for="dob">
                        <i class="fas fa-calendar-alt"></i> 生年月日
                        <span class="required">*</span>
                    </label>
                    <input type="date" 
                           id="dob" 
                           name="dob" 
                           value="<?= htmlspecialchars($user_data['dob']) ?>"
                           required>
                    <span class="field-error" id="dob_error"></span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="height">
                            <i class="fas fa-ruler-vertical"></i> 身長 (cm)
                            <span class="required">*</span>
                        </label>
                        <input type="number" 
                               id="height" 
                               name="height" 
                               value="<?= htmlspecialchars($user_data['height']) ?>"
                               min="100" 
                               max="250"
                               step="0.1"
                               required>
                        <span class="field-error" id="height_error"></span>
                    </div>

                    <div class="form-group">
                        <label for="weight">
                            <i class="fas fa-weight"></i> 体重 (kg)
                            <span class="required">*</span>
                        </label>
                        <input type="number" 
                               id="weight" 
                               name="weight" 
                               value="<?= htmlspecialchars($user_data['weight']) ?>"
                               min="30" 
                               max="200"
                               step="0.1"
                               required>
                        <span class="field-error" id="weight_error"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="position">
                        <i class="fas fa-medal"></i> ポジション / 役職
                        <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="position" 
                           name="position" 
                           value="<?= htmlspecialchars($user_data['position']) ?>"
                           required>
                    <span class="field-error" id="position_error"></span>
                </div>

                <div class="form-group">
                    <label for="sport">
                        <i class="fas fa-basketball-ball"></i> 種目
                        <?php if (!empty($hasSportColumn)): ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <?php if (!empty($hasSportColumn)): ?>
                        <?php $currentSport = (string)($user_data['sport'] ?? ''); ?>
                        <select id="sport" name="sport" required>
                            <option value="" <?= $currentSport === '' ? 'selected' : '' ?>>選択してください</option>
                            <option value="swim" <?= $currentSport === 'swim' ? 'selected' : '' ?>>水泳</option>
                            <option value="basketball" <?= $currentSport === 'basketball' ? 'selected' : '' ?>>バスケ</option>
                            <option value="tennis" <?= $currentSport === 'tennis' ? 'selected' : '' ?>>テニス</option>
                            <option value="all" <?= $currentSport === 'all' ? 'selected' : '' ?>>全て/複数</option>
                        </select>
                        <small class="field-hint">※ 選んだ種目だけがメニューに表示されます（管理者は全て表示）</small>
                    <?php else: ?>
                        <p class="section-note">※ 種目でメニューを出し分けるには、DBに sport 列の追加が必要です（db/add_user_sport.sql）。</p>
                    <?php endif; ?>
                    <span class="field-error" id="sport_error"></span>
                </div>
            </div>

            <!-- パスワード変更セクション -->
            <div class="form-section">
                <h3><i class="fas fa-key"></i> パスワード変更</h3>
                <p class="section-note">パスワードを変更する場合のみ入力してください</p>

                <div class="form-group">
                    <label for="current_password">
                        <i class="fas fa-lock"></i> 現在のパスワード
                    </label>
                    <div class="password-wrapper">
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               placeholder="現在のパスワードを入力">
                        <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="field-error" id="current_password_error"></span>
                </div>

                <div class="form-group">
                    <label for="new_password">
                        <i class="fas fa-key"></i> 新しいパスワード
                    </label>
                    <div class="password-wrapper">
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               placeholder="新しいパスワード（6文字以上）"
                               minlength="6">
                        <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="password-strength" style="display: none;">
                        <div class="strength-bar"></div>
                        <span class="strength-text"></span>
                    </div>
                    <span class="field-error" id="new_password_error"></span>
                    <small class="field-hint">6文字以上の英数字記号</small>
                </div>

                <div class="form-group">
                    <label for="new_password_confirm">
                        <i class="fas fa-check-double"></i> 新しいパスワード確認
                    </label>
                    <div class="password-wrapper">
                        <input type="password" 
                               id="new_password_confirm" 
                               name="new_password_confirm" 
                               placeholder="新しいパスワードを再入力">
                        <button type="button" class="toggle-password" onclick="togglePassword('new_password_confirm')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="field-error" id="new_password_confirm_error"></span>
                </div>
            </div>
        </div>

        <!-- アクションボタン -->
        <div class="form-actions">
            <button type="submit" name="update" class="btn-submit">
                <i class="fas fa-save"></i> 変更を保存
            </button>
            <a href="home.php" class="btn-cancel">
                <i class="fas fa-times"></i> キャンセル
            </a>
        </div>

        <!-- 危険ゾーン（アカウント削除） -->
        <div class="danger-zone" aria-label="危険ゾーン">
            <h3 class="danger-zone__title"><i class="fas fa-triangle-exclamation"></i> アカウント削除</h3>
            <p class="danger-zone__text">
                アカウントを削除すると、個人データ（目標・記録・日記・予定など）が削除されます。チャットは履歴を残すため、あなたの発言は非表示扱いになります。
            </p>
            <div class="form-group">
                <label for="delete_password"><i class="fas fa-lock"></i> 現在のパスワード（確認）</label>
                <div class="password-wrapper">
                    <input type="password"
                           id="delete_password"
                           name="delete_password"
                           autocomplete="current-password"
                           placeholder="削除の確認のため入力">
                    <button type="button" class="toggle-password" onclick="togglePassword('delete_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="field-hint">この操作は取り消せません</small>
                <div id="delete_password_error" class="danger-zone__error" style="display:none;"></div>
            </div>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="delete_account" id="delete_account_hidden" value="">
            <button type="button" id="deleteAccountButton" class="btn-danger" onclick="openDeleteConfirmModal();">
                <i class="fas fa-user-slash"></i> アカウントを削除する
            </button>
        </div>
    </form>
</div>

<!-- 削除確認モーダル -->
<div id="deleteConfirmModal" class="modal-overlay" aria-hidden="true" style="display:none;">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="deleteConfirmTitle" aria-describedby="deleteConfirmBody">
        <div class="modal__header">
            <h3 id="deleteConfirmTitle" class="modal__title"><i class="fas fa-triangle-exclamation"></i> アカウント削除の確認</h3>
            <button type="button" class="modal__close" aria-label="閉じる" onclick="closeDeleteConfirmModal();">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <div id="deleteConfirmBody" class="modal__body">
            <p class="modal__text">
                アカウントを削除すると、個人データ（目標・記録・日記・予定など）が削除されます。
            </p>
            <p class="modal__text">
                チャットは履歴を残すため、あなたの発言は非表示扱いになります。
            </p>
            <p class="modal__text modal__text--danger">この操作は取り消せません。</p>
        </div>
        <div class="modal__actions">
            <button type="button" class="btn-secondary" onclick="closeDeleteConfirmModal();">
                キャンセル
            </button>
            <button type="button" id="deleteConfirmNext" class="btn-danger" onclick="advanceDeleteConfirm();">
                次へ
            </button>
        </div>
    </div>
</div>

<script>
// アイコンプレビュー
const iconInput = document.getElementById('user_icon');
const iconPreview = document.getElementById('userIconPreview');
const iconFallback = document.getElementById('userIconFallback');

if (iconPreview) {
    iconPreview.addEventListener('load', function () {
        if (iconFallback) iconFallback.style.display = 'none';
    });
    iconPreview.addEventListener('error', function () {
        if (iconFallback) iconFallback.style.display = 'flex';
        this.remove();
    });
}

if (iconInput) {
    iconInput.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            alert('画像ファイルを選択してください');
            this.value = '';
            return;
        }

        const url = URL.createObjectURL(file);
        let img = document.getElementById('userIconPreview');
        if (!img) {
            img = document.createElement('img');
            img.id = 'userIconPreview';
            img.alt = 'ユーザーアイコン';
            const previewBox = document.querySelector('.avatar-preview');
            if (previewBox) {
                previewBox.prepend(img);
            }
            img.addEventListener('load', function () {
                if (iconFallback) iconFallback.style.display = 'none';
                URL.revokeObjectURL(url);
            });
        } else {
            img.onload = function () {
                if (iconFallback) iconFallback.style.display = 'none';
                URL.revokeObjectURL(url);
            };
        }
        img.src = url;
    });
}

// パスワード表示切替
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// パスワード強度チェック
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.querySelector('.strength-bar');
    const strengthText = document.querySelector('.strength-text');
    const strengthContainer = document.getElementById('password-strength');
    
    if (password.length === 0) {
        strengthContainer.style.display = 'none';
        return;
    }
    
    strengthContainer.style.display = 'block';
    
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    const strengthLevels = [
        { width: '20%', color: '#e53e3e', text: '非常に弱い' },
        { width: '40%', color: '#ed8936', text: '弱い' },
        { width: '60%', color: '#ecc94b', text: '普通' },
        { width: '80%', color: '#48bb78', text: '強い' },
        { width: '100%', color: '#38a169', text: '非常に強い' }
    ];
    
    const level = Math.min(strength, 4);
    strengthBar.style.width = strengthLevels[level].width;
    strengthBar.style.backgroundColor = strengthLevels[level].color;
    strengthText.textContent = strengthLevels[level].text;
    strengthText.style.color = strengthLevels[level].color;
});

// パスワード確認チェック
document.getElementById('new_password_confirm').addEventListener('input', function() {
    const password = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    const errorSpan = document.getElementById('new_password_confirm_error');
    
    if (confirmPassword && password !== confirmPassword) {
        errorSpan.textContent = 'パスワードが一致しません';
        this.classList.add('error');
    } else {
        errorSpan.textContent = '';
        this.classList.remove('error');
    }
});

// 送信ボタン判定（古いブラウザ対策）
(function () {
    const form = document.getElementById('profileEditForm');
    if (!form) return;
    form.querySelectorAll('button[type="submit"]').forEach(btn => {
        btn.addEventListener('click', function () {
            form.dataset.submitButtonName = this.name || '';
        });
    });
})();

// フォーム送信
document.getElementById('profileEditForm').addEventListener('submit', function(e) {
    let submitter = e.submitter;
    if (!submitter && this.dataset.submitButtonName) {
        submitter = this.querySelector('button[name="' + this.dataset.submitButtonName + '"]');
    }
    if (!submitter) {
        submitter = this.querySelector('button[type="submit"]');
    }

    const submitterName = (submitter && submitter.name) ? submitter.name : (this.dataset.submitButtonName || '');
    // アカウント削除は通常POSTで処理（PHP側でリダイレクト/セッション破棄するため）
    if (submitterName === 'delete_account') {
        return;
    }

    e.preventDefault();
    
    const formData = new FormData(this);

    // どの送信ボタンが押されたかをFormDataに反映（FormData(form)だけだと入らないブラウザがある）
    if (submitter && submitter.name) {
        formData.set(submitter.name, submitter.value || '1');
    }

    // 送信ボタン名の退避をクリア
    this.dataset.submitButtonName = '';

    const submitButton = submitter || this.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 更新中...';
    
    fetch('profile_edit.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async (response) => {
        const contentType = response.headers.get('content-type') || '';
        const text = await response.text();

        if (!response.ok) {
            throw new Error('HTTP ' + response.status + ': ' + text.slice(0, 200));
        }

        if (contentType.includes('application/json')) {
            return JSON.parse(text);
        }

        // JSON以外が返ってきた場合（例: PHPがHTMLを返した）
        throw new Error('JSON以外の応答を受信しました: ' + text.slice(0, 200));
    })
    .then(data => {
        if (data.success) {
            showSuccessMessage(data.message);
            // パスワードフィールドをクリア
            document.getElementById('current_password').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('new_password_confirm').value = '';
            document.getElementById('password-strength').style.display = 'none';
        } else {
            showErrors(data.errors);
        }
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    })
    .catch(error => {
        console.error('Error:', error);
        showErrors(['通信エラーが発生しました', String(error && error.message ? error.message : error)]);
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    });
});

function showSuccessMessage(message) {
    const existingBox = document.querySelector('.success-box');
    if (existingBox) existingBox.remove();
    
    const successBox = document.createElement('div');
    successBox.className = 'success-box';
    successBox.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;
    
    const container = document.querySelector('.profile-edit-container');
    container.insertBefore(successBox, container.querySelector('.profile-form'));
    successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    setTimeout(() => {
        successBox.remove();
    }, 5000);
}

function showErrors(errors) {
    const existingBox = document.querySelector('.error-box');
    if (existingBox) existingBox.remove();
    
    const errorBox = document.createElement('div');
    errorBox.className = 'error-box';
    errorBox.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <div class="error-content">
            <h4>エラーが発生しました</h4>
            <ul>
                ${errors.map(error => `<li>${error}</li>`).join('')}
            </ul>
        </div>
    `;
    
    const container = document.querySelector('.profile-edit-container');
    container.insertBefore(errorBox, container.querySelector('.profile-form'));
    errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// 入力フィールドのエラークリア
document.querySelectorAll('input').forEach(input => {
    input.addEventListener('focus', function() {
        this.classList.remove('error');
        const errorSpan = document.getElementById(this.id + '_error');
        if (errorSpan) {
            errorSpan.textContent = '';
        }
    });
});
</script>

<script>
let __deleteConfirmStep = 1;

function openDeleteConfirmModal() {
    const pw = document.getElementById('delete_password');
    const err = document.getElementById('delete_password_error');
    if (err) {
        err.textContent = '';
        err.style.display = 'none';
    }

    if (!pw || pw.value.trim() === '') {
        if (err) {
            err.textContent = '削除の確認のため、現在のパスワードを入力してください。';
            err.style.display = 'block';
        }
        if (pw) pw.focus();
        return;
    }

    __deleteConfirmStep = 1;
    updateDeleteConfirmModal();

    const overlay = document.getElementById('deleteConfirmModal');
    if (overlay) {
        overlay.style.display = 'flex';
        overlay.setAttribute('aria-hidden', 'false');
    }
}

function closeDeleteConfirmModal() {
    const overlay = document.getElementById('deleteConfirmModal');
    if (overlay) {
        overlay.style.display = 'none';
        overlay.setAttribute('aria-hidden', 'true');
    }
}

function updateDeleteConfirmModal() {
    const title = document.getElementById('deleteConfirmTitle');
    const body = document.getElementById('deleteConfirmBody');
    const nextBtn = document.getElementById('deleteConfirmNext');

    if (!title || !body || !nextBtn) return;

    if (__deleteConfirmStep === 1) {
        title.innerHTML = '<i class="fas fa-triangle-exclamation"></i> アカウント削除の確認';
        body.innerHTML = `
            <p class="modal__text">アカウントを削除すると、個人データ（目標・記録・日記・予定など）が削除されます。</p>
            <p class="modal__text">チャットは履歴を残すため、あなたの発言は非表示扱いになります。</p>
            <p class="modal__text modal__text--danger">この操作は取り消せません。</p>
        `;
        nextBtn.textContent = '次へ';
    } else {
        title.innerHTML = '<i class="fas fa-triangle-exclamation"></i> 最終確認';
        body.innerHTML = `
            <p class="modal__text modal__text--danger">最終確認：本当にアカウントを削除しますか？</p>
            <p class="modal__text">削除後は元に戻せません。</p>
        `;
        nextBtn.textContent = '削除する';
    }
}

function advanceDeleteConfirm() {
    if (__deleteConfirmStep === 1) {
        __deleteConfirmStep = 2;
        updateDeleteConfirmModal();
        return;
    }

    // 送信前に削除フラグを立てる（programmatic submitでもPHP側が判定できるように）
    const deleteHidden = document.getElementById('delete_account_hidden');
    if (deleteHidden) deleteHidden.value = '1';

    closeDeleteConfirmModal();

    const form = document.getElementById('profileEditForm');
    if (form) {
        // 送信ボタン名判定（AJAX回避）
        form.dataset.submitButtonName = 'delete_account';
        form.submit();
    }
}

// overlayクリックやEscで閉じる
(function () {
    const overlay = document.getElementById('deleteConfirmModal');
    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeDeleteConfirmModal();
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const ov = document.getElementById('deleteConfirmModal');
            if (ov && ov.style.display !== 'none') closeDeleteConfirmModal();
        }
    });
})();
</script>


</body>
</html>
