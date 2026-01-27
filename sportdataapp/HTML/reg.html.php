<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024">
    <title>新規登録 - Sports Analytics App</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/reg.css">
    <link rel="stylesheet" href="../css/site.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<div class="registration-container">
    <div class="registration-header">
        <h1><i class="fas fa-user-plus"></i> 新規登録</h1>
        <p class="subtitle">Sports Analytics Appへようこそ</p>
    </div>

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

    <form action="" method="post" id="registrationForm" novalidate>
        <div class="reg-form">
            <div class="inner-reg-form">

                <!-- 左側 - ログイン情報 -->
                <div class="form-section left-reg">
                    <h3><i class="fas fa-lock"></i> ログイン情報</h3>

                    <div class="form-group">
                        <label for="group_id">
                            <i class="fas fa-users"></i> 団体ID
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="group_id" 
                               name="group_id" 
                               value="<?= htmlspecialchars($group_id) ?>"
                               placeholder="例: team001"
                               required>
                        <span class="field-error" id="group_id_error"></span>
                    </div>

                    <?php if (!empty($canSetAdmin)): ?>
                    <div class="form-group">
                        <label class="checkbox-label" for="is_admin">
                            <i class="fas fa-user-shield"></i> 管理者として登録
                        </label>
                        <label class="form-checkbox" for="is_admin">
                            <input type="checkbox" id="is_admin" name="is_admin" value="1" <?= !empty($_POST['is_admin']) ? 'checked' : '' ?>>
                            <span>コーチ/先生など（同じgroupのデータ閲覧）</span>
                        </label>
                        <small class="field-hint">※ 管理者のみ設定できます（管理者で登録する場合、身体情報は入力不要）</small>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="checkbox-label" for="wants_admin">
                            <i class="fas fa-user-shield"></i> 管理者権限を希望（申請）
                        </label>
                        <label class="form-checkbox" for="wants_admin">
                            <input type="checkbox" id="wants_admin" name="wants_admin" value="1" <?= !empty($_POST['wants_admin']) ? 'checked' : '' ?>>
                            <span>チェックするとスーパー管理者に申請されます</span>
                        </label>
                        <small class="field-hint">※ 申請中は身体情報の入力は不要です</small>
                    </div>

                    <div class="form-group">
                        <label for="user_id">
                            <i class="fas fa-user"></i> ユーザーID
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="user_id" 
                               name="user_id" 
                               value="<?= htmlspecialchars($user_id) ?>"
                               placeholder="4文字以上"
                               minlength="4"
                               required>
                        <span class="field-error" id="user_id_error"></span>
                        <small class="field-hint">4文字以上の英数字</small>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-key"></i> パスワード
                            <span class="required">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="6文字以上"
                                   minlength="6"
                                   required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="password-strength">
                            <div class="strength-bar"></div>
                            <span class="strength-text"></span>
                        </div>
                        <span class="field-error" id="password_error"></span>
                        <small class="field-hint">6文字以上の英数字記号</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">
                            <i class="fas fa-check-double"></i> パスワード確認
                            <span class="required">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   placeholder="パスワードを再入力"
                                   minlength="6"
                                   required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password_confirm')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="field-error" id="password_confirm_error"></span>
                    </div>
                </div>

                <!-- 右側 - プロフィール情報 -->
                <div class="form-section right-reg">
                    <h3><i class="fas fa-id-card"></i> プロフィール情報</h3>

                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-signature"></i> 氏名
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?= htmlspecialchars($name) ?>"
                               placeholder="例: 山田 太郎"
                               required>
                        <span class="field-error" id="name_error"></span>
                    </div>

                    <div id="bodyInfoFields">
                    <div class="form-group">
                        <label for="dob">
                            <i class="fas fa-calendar-alt"></i> 生年月日
                            <span class="required">*</span>
                        </label>
                        <input type="date" 
                               id="dob" 
                               name="dob" 
                               value="<?= htmlspecialchars($dob) ?>"
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
                                   value="<?= htmlspecialchars($height) ?>"
                                   placeholder="170"
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
                                   value="<?= htmlspecialchars($weight) ?>"
                                   placeholder="65"
                                   min="30" 
                                   max="200"
                                   step="0.1"
                                   required>
                            <span class="field-error" id="weight_error"></span>
                        </div>
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
                               value="<?= htmlspecialchars($position) ?>"
                               placeholder="例: フォワード / 選手"
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
                        <select id="sport" name="sport" <?= !empty($hasSportColumn) ? 'required' : '' ?>>
                            <option value="" <?= empty($sport) ? 'selected' : '' ?>>選択してください</option>
                            <?php
                            $options = [
                                'swim' => '水泳',
                                'basketball' => 'バスケ',
                                'tennis' => 'テニス',
                                'all' => '全て/複数',
                            ];
                            foreach ($options as $val => $label):
                                $selected = ((string)$sport === (string)$val) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
                            endforeach;
                            ?>
                        </select>
                        <?php if (empty($hasSportColumn)): ?>
                            <small class="field-hint">※ 種目でメニューを出し分けるには、DBに sport 列の追加が必要です（db/add_user_sport.sql）。</small>
                        <?php else: ?>
                            <small class="field-hint">※ 選んだ種目だけがメニューに表示されます（管理者は全て表示）</small>
                        <?php endif; ?>
                        <span class="field-error" id="sport_error"></span>
                    </div>
                </div>

            </div>

            <!-- 送信ボタン -->
            <div class="form-actions">
                <button type="submit" name="reg" class="btn-submit">
                    <i class="fas fa-user-plus"></i> 登録する
                </button>
                <a href="login.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> ログインに戻る
                </a>
            </div>
        </div>
    </form>
</div>

<script>
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
document.getElementById('password').addEventListener('input', function() {
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
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const errorSpan = document.getElementById('password_confirm_error');
    
    if (confirmPassword && password !== confirmPassword) {
        errorSpan.textContent = 'パスワードが一致しません';
        this.classList.add('error');
    } else {
        errorSpan.textContent = '';
        this.classList.remove('error');
    }
});

// 管理者として登録する場合は身体情報を非表示
(function syncBodyInfoVisibility() {
    const adminCheckbox = document.getElementById('is_admin');
    const wantsAdminCheckbox = document.getElementById('wants_admin');
    const bodyInfoFields = document.getElementById('bodyInfoFields');
    if (!bodyInfoFields) return;

    const apply = () => {
        const isAdminRegister = adminCheckbox ? adminCheckbox.checked : false;
        const wantsAdminRegister = wantsAdminCheckbox ? wantsAdminCheckbox.checked : false;
        bodyInfoFields.style.display = (isAdminRegister || wantsAdminRegister) ? 'none' : '';
    };

    if (adminCheckbox) {
        adminCheckbox.addEventListener('change', apply);
    }
    if (wantsAdminCheckbox) {
        wantsAdminCheckbox.addEventListener('change', apply);
    }
    apply();
})();

// フォーム送信前のバリデーション & Ajax送信
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault(); // デフォルトの送信を防ぐ
    
    let hasError = false;
    
    // すべてのエラーメッセージをクリア
    document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
    document.querySelectorAll('input').forEach(el => el.classList.remove('error'));
    
    // 必須フィールドチェック
    // 管理者として登録する場合は身体情報(dob/height/weight)は不要
    const adminCheckbox = document.getElementById('is_admin');
    const wantsAdminCheckbox = document.getElementById('wants_admin');
    const isAdminRegister = adminCheckbox ? adminCheckbox.checked : false;
    const wantsAdminRegister = wantsAdminCheckbox ? wantsAdminCheckbox.checked : false;
    const requiredFields = ['group_id', 'user_id', 'password', 'password_confirm', 'name', 'position'];
    if (!(isAdminRegister || wantsAdminRegister)) {
        requiredFields.push('dob', 'height', 'weight');
    }
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (!field.value.trim()) {
            document.getElementById(fieldName + '_error').textContent = 'この項目は必須です';
            field.classList.add('error');
            hasError = true;
        }
    });
    
    // パスワード確認チェック
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirm').value;
    if (password !== confirmPassword) {
        document.getElementById('password_confirm_error').textContent = 'パスワードが一致しません';
        document.getElementById('password_confirm').classList.add('error');
        hasError = true;
    }
    
    if (hasError) {
        // 最初のエラーフィールドにスクロール
        const firstError = document.querySelector('.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
        return;
    }
    
    // バリデーションOKならAjaxで送信
    const formData = new FormData(this);
    // JSで FormData を作ると、押したsubmitボタン(name=reg)が含まれないため明示的に追加
    formData.append('reg', '1');
    const submitButton = this.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    
    // ボタンを無効化してローディング表示
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 登録中...';
    
    fetch('reg.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 成功モーダルを表示
            showSuccessModal(data.message);
            
            // 2秒後にログインページへリダイレクト
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 2000);
        } else {
            // エラーメッセージを表示
            showErrorMessages(data.errors);
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessages(['通信エラーが発生しました。もう一度お試しください。']);
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    });
});

// 成功モーダルを表示
function showSuccessModal(message) {
    // 既存のモーダルがあれば削除
    const existingModal = document.querySelector('.success-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // モーダルを作成
    const modal = document.createElement('div');
    modal.className = 'success-modal';
    modal.innerHTML = `
        <div class="success-modal-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>登録完了！</h3>
            <p>${message}</p>
            <div class="success-animation">
                <div class="checkmark-circle">
                    <div class="checkmark"></div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // アニメーション用のクラスを追加
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

// エラーメッセージを表示
function showErrorMessages(errors) {
    // 既存のエラーボックスを削除
    const existingErrorBox = document.querySelector('.error-box');
    if (existingErrorBox) {
        existingErrorBox.remove();
    }
    
    // エラーボックスを作成
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
    
    // フォームの前に挿入
    const form = document.querySelector('.reg-form');
    form.parentNode.insertBefore(errorBox, form);
    
    // エラーボックスまでスクロール
    errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// 入力フィールドにフォーカスした時のエラークリア
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

</body>
</html>
