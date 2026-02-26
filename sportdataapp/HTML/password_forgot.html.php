<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード再設定 - Sports Analytics App</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-wrapper loaded">
            <div class="login-header">
                <div class="logo">
                    <img src="../img/favicon.svg" alt="SAAロゴ" class="logo-img">
                    <h1>Sports Analytics App</h1>
                </div>
                <p class="subtitle">パスワード再設定</p>
            </div>

            <?php if(!empty($success_message)): ?>
            <div class="success-box">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>

            <?php if(!empty($errors)): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle"></i>
                <div class="error-content">
                    <h4>処理できませんでした</h4>
                    <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="login-form-container" style="grid-template-columns: 1fr;">
                <div class="left-section" style="border-right: none;">
                    <?php $tabIdForLink = (string)($_GET['tab_id'] ?? ($GLOBALS['SPORTDATA_TAB_ID'] ?? '')); ?>

                    <?php if(($step ?? 'verify') === 'reset'): ?>
                        <div class="section-header">
                            <h2><i class="fas fa-key"></i> 新しいパスワードを設定</h2>
                        </div>

                        <form action="" method="post" id="resetForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <?php if ($tabIdForLink !== ''): ?>
                                <input type="hidden" name="tab_id" value="<?= htmlspecialchars($tabIdForLink, ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                            <input type="hidden" name="action" value="reset">

                            <div class="form-group">
                                <label for="new_password"><i class="fas fa-lock"></i> 新しいパスワード</label>
                                <div class="input-wrapper password-wrapper">
                                    <input type="password" id="new_password" name="new_password" placeholder="6文字以上" autocomplete="new-password" required>
                                    <button type="button" class="toggle-password" onclick="togglePasswordField('new_password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <span class="field-error" id="new_password_error"></span>
                            </div>

                            <div class="form-group">
                                <label for="new_password_confirm"><i class="fas fa-lock"></i> 新しいパスワード（確認）</label>
                                <div class="input-wrapper password-wrapper">
                                    <input type="password" id="new_password_confirm" name="new_password_confirm" placeholder="もう一度入力" autocomplete="new-password" required>
                                    <button type="button" class="toggle-password" onclick="togglePasswordField('new_password_confirm', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <span class="field-error" id="new_password_confirm_error"></span>
                            </div>

                            <button type="submit" class="btn-login">
                                <i class="fas fa-check"></i>
                                <span>パスワードを更新</span>
                            </button>

                            <div class="form-options">
                                <a class="forgot-password-link" href="password_forgot.php<?= $tabIdForLink !== '' ? ('?tab_id=' . urlencode($tabIdForLink)) : '' ?>">本人確認からやり直す</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="section-header">
                            <h2><i class="fas fa-user-check"></i> 本人確認</h2>
                        </div>

                        <p style="margin: 0 0 16px; color: #64748b; font-size: 0.95rem;">
                            登録時の情報を入力すると、新しいパスワードを設定できます。
                        </p>

                        <form action="" method="post" id="verifyForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <?php if ($tabIdForLink !== ''): ?>
                                <input type="hidden" name="tab_id" value="<?= htmlspecialchars($tabIdForLink, ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                            <input type="hidden" name="action" value="verify">

                            <div class="form-group">
                                <label for="group_id"><i class="fas fa-users"></i> 団体ID</label>
                                <div class="input-wrapper">
                                    <input type="text" id="group_id" name="group_id" value="<?= htmlspecialchars((string)($group_id ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="団体ID" autocomplete="organization" required>
                                </div>
                                <span class="field-error" id="group_id_error"></span>
                            </div>

                            <div class="form-group">
                                <label for="user_id"><i class="fas fa-user"></i> ユーザーID</label>
                                <div class="input-wrapper">
                                    <input type="text" id="user_id" name="user_id" value="<?= htmlspecialchars((string)($user_id ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ユーザーID" autocomplete="username" required>
                                </div>
                                <span class="field-error" id="user_id_error"></span>
                            </div>

                            <div class="form-group">
                                <label for="name"><i class="fas fa-id-card"></i> 氏名</label>
                                <div class="input-wrapper">
                                    <input type="text" id="name" name="name" value="<?= htmlspecialchars((string)($name ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="氏名" autocomplete="name" required>
                                </div>
                                <span class="field-error" id="name_error"></span>
                            </div>

                            <div class="form-group">
                                <label for="dob"><i class="fas fa-calendar"></i> 生年月日</label>
                                <div class="input-wrapper">
                                    <input type="date" id="dob" name="dob" value="<?= htmlspecialchars((string)($dob ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <span class="field-error" id="dob_error"></span>
                            </div>

                            <button type="submit" class="btn-login">
                                <i class="fas fa-arrow-right"></i>
                                <span>本人確認して続ける</span>
                            </button>

                            <div class="form-options">
                                <a class="forgot-password-link" href="login.php<?= $tabIdForLink !== '' ? ('?tab_id=' . urlencode($tabIdForLink)) : '' ?>">ログイン画面に戻る</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<script>
function togglePasswordField(id, btn) {
    const field = document.getElementById(id);
    if (!field) return;
    const icon = btn ? btn.querySelector('i') : null;

    if (field.type === 'password') {
        field.type = 'text';
        if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
    } else {
        field.type = 'password';
        if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    }
}

(function(){
    const form = document.getElementById('verifyForm');
    if (form) {
        form.addEventListener('submit', function(e){
            let hasError = false;
            document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
            document.querySelectorAll('input').forEach(el => el.classList.remove('error'));

            const requiredIds = ['group_id','user_id','name','dob'];
            requiredIds.forEach((id) => {
                const el = document.getElementById(id);
                if (!el) return;
                if (!String(el.value || '').trim()) {
                    const err = document.getElementById(id + '_error');
                    if (err) err.textContent = '入力してください';
                    el.classList.add('error');
                    hasError = true;
                }
            });

            if (hasError) {
                e.preventDefault();
                const first = document.querySelector('.error');
                if (first) first.focus();
            }
        });
    }

    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e){
            let hasError = false;
            document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
            document.querySelectorAll('input').forEach(el => el.classList.remove('error'));

            const pw = document.getElementById('new_password');
            const pw2 = document.getElementById('new_password_confirm');

            if (!pw || !pw.value) {
                const err = document.getElementById('new_password_error');
                if (err) err.textContent = '入力してください';
                if (pw) pw.classList.add('error');
                hasError = true;
            } else if (pw.value.length < 6) {
                const err = document.getElementById('new_password_error');
                if (err) err.textContent = '6文字以上にしてください';
                pw.classList.add('error');
                hasError = true;
            }

            if (!pw2 || !pw2.value) {
                const err = document.getElementById('new_password_confirm_error');
                if (err) err.textContent = '入力してください';
                if (pw2) pw2.classList.add('error');
                hasError = true;
            } else if (pw && pw2 && pw.value !== pw2.value) {
                const err = document.getElementById('new_password_confirm_error');
                if (err) err.textContent = '一致しません';
                pw2.classList.add('error');
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
                const first = document.querySelector('.error');
                if (first) first.focus();
            }
        });
    }
})();
</script>
</body>
</html>
