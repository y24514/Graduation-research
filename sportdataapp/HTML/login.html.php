<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン - スポーツデータ可視化分析アプリ</title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-wrapper">
            <!-- ヘッダー -->
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-running"></i>
                    <h1>SportData</h1>
                </div>
                <p class="subtitle">スポーツデータ分析可視化アプリ</p>
            </div>

            <!-- 成功メッセージ -->
            <?php if(!empty($success_message)): ?>
            <div class="success-box">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($success_message) ?></span>
            </div>
            <?php endif; ?>

            <!-- エラーメッセージ -->
            <?php if(!empty($errors)): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle"></i>
                <div class="error-content">
                    <h4>ログインできませんでした</h4>
                    <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="login-form-container">
                <!-- 左側 - ログインフォーム -->
                <div class="left-section">
                    <div class="section-header">
                        <h2><i class="fas fa-sign-in-alt"></i> ログイン</h2>
                    </div>

                    <form action="" method="post" id="loginForm" novalidate>
                        <!-- 団体ID -->
                        <div class="form-group">
                            <label for="group_id">
                                <i class="fas fa-users"></i> 団体ID
                            </label>
                            <div class="input-wrapper">
                                <input type="text" 
                                       id="group_id" 
                                       name="group_id" 
                                       value="<?= htmlspecialchars($group_id) ?>"
                                       placeholder="団体IDを入力"
                                       autocomplete="organization"
                                       required>
                            </div>
                            <span class="field-error" id="group_id_error"></span>
                        </div>

                        <!-- ユーザーID -->
                        <div class="form-group">
                            <label for="user_id">
                                <i class="fas fa-user"></i> ユーザーID
                            </label>
                            <div class="input-wrapper">
                                <input type="text" 
                                       id="user_id" 
                                       name="user_id" 
                                       value="<?= htmlspecialchars($user_id) ?>"
                                       placeholder="ユーザーIDを入力"
                                       autocomplete="username"
                                       required>
                            </div>
                            <span class="field-error" id="user_id_error"></span>
                        </div>

                        <!-- パスワード -->
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i> パスワード
                            </label>
                            <div class="input-wrapper password-wrapper">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       placeholder="パスワードを入力"
                                       autocomplete="current-password"
                                       required>
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <span class="field-error" id="password_error"></span>
                        </div>

                        <!-- ログイン情報を記憶 -->
                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" 
                                       name="remember_me" 
                                       id="remember_me"
                                       <?= (!empty($group_id) || !empty($user_id)) ? 'checked' : '' ?>>
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">ログイン情報を記憶する</span>
                            </label>
                        </div>

                        <!-- ログインボタン -->
                        <button type="submit" name="send" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>ログイン</span>
                        </button>
                    </form>
                </div>

                <!-- 右側 - 新規登録案内 -->
                <div class="right-section">
                    <div class="registration-invitation">
                        <div class="invitation-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2>はじめての方へ</h2>
                        <p>スポーツデータ可視化分析アプリをご利用いただくには、新規登録が必要です。</p>
                        
                        <div class="features">
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>データ分析・可視化</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>チームメンバー管理</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>パフォーマンス追跡</span>
                            </div>
                        </div>

                        <a href="reg.php" class="btn-register">
                            <i class="fas fa-user-plus"></i>
                            <span>新規登録はこちら</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // パスワード表示切替
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const toggleButton = document.querySelector('.toggle-password');
        const icon = toggleButton.querySelector('i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // フォーム送信前のバリデーション
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        let hasError = false;
        
        // すべてのエラーメッセージをクリア
        document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
        document.querySelectorAll('input').forEach(el => el.classList.remove('error'));
        
        // 必須フィールドチェック
        const groupId = document.getElementById('group_id');
        const userId = document.getElementById('user_id');
        const password = document.getElementById('password');
        
        if (!groupId.value.trim()) {
            document.getElementById('group_id_error').textContent = '団体IDを入力してください';
            groupId.classList.add('error');
            hasError = true;
        }
        
        if (!userId.value.trim()) {
            document.getElementById('user_id_error').textContent = 'ユーザーIDを入力してください';
            userId.classList.add('error');
            hasError = true;
        }
        
        if (!password.value) {
            document.getElementById('password_error').textContent = 'パスワードを入力してください';
            password.classList.add('error');
            hasError = true;
        }
        
        if (hasError) {
            e.preventDefault();
            const firstError = document.querySelector('.error');
            if (firstError) {
                firstError.focus();
            }
        }
    });

    // 入力フィールドにフォーカスした時のエラークリア
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('focus', function() {
            this.classList.remove('error');
            const errorSpan = document.getElementById(this.id + '_error');
            if (errorSpan) {
                errorSpan.textContent = '';
            }
        });
        
        // 入力時のアニメーション
        input.addEventListener('input', function() {
            if(this.value) {
                this.classList.add('has-value');
            } else {
                this.classList.remove('has-value');
            }
        });
        
        // 初期値がある場合
        if(input.value) {
            input.classList.add('has-value');
        }
    });

    // Enterキーでフォーム送信
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });
    });

    // ページロード時のアニメーション
    window.addEventListener('load', function() {
        document.querySelector('.login-wrapper').classList.add('loaded');
    });
    </script>
</body>
</html>
