# ログイン/新規登録のセキュリティ対策（根拠コード抜粋）

本資料は、ログイン機能（login）・新規登録機能（reg）におけるセキュリティ対策を「実装コードの抜粋」と「対策の狙い（脅威モデル）」の形で整理した報告書用資料である。

> 注意: 本資料は「現状の実装」を根拠としてまとめている。

---

## 1. セッションCookieの安全設定（HttpOnly / SameSite / Secure）

**狙い**: セッションIDの漏えいリスク低減（XSSによる読み取り抑止、CSRF耐性の向上、HTTPS時の盗聴耐性向上）、未初期化セッションID受け入れ抑止。

**根拠コード（抜粋）**: `sportdataapp/PHP/session_bootstrap.php`

```php
// セッション基本設定（セキュリティ）
// - use_only_cookies: URL経由でのセッションIDを抑止
// - use_strict_mode: 未初期化セッションIDの受け入れを抑止
// - cookie_httponly: JSからの参照抑止
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');

@ini_set('session.cookie_samesite', 'Lax');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
```

- `use_only_cookies=1` により、URLにセッションIDを付ける方式を抑止
- `use_strict_mode=1` により、未初期化のセッションIDの受け入れを抑止
- `HttpOnly` により、JavaScriptからCookieを参照しにくくする（XSS被害の拡大を抑制）
- `SameSite=Lax` により、外部サイト起点のCSRFリスクを軽減
- `Secure` はHTTPS接続時のみ有効化（localhostのHTTPでも動作するよう配慮）

---

## 2. CSRF対策（トークン方式 + `hash_equals`）

**狙い**: ログイン・登録フォームへの外部サイトからの不正POST（CSRF）を防ぐ。

### 2.1 CSRFトークン生成（セッション共通）

**根拠コード（抜粋）**: `sportdataapp/PHP/session_bootstrap.php`

```php
session_start();

// CSRFトークン（ログイン/登録などヘッダ無しページでも使えるように共通化）
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        // 互換: random_bytes が使えない環境の最低限
        $_SESSION['csrf_token'] = bin2hex((string)mt_rand() . (string)microtime(true));
    }
}
```

- 初回のみトークンを生成し、セッションに保持
- 比較は後述の `hash_equals` を使用（タイミング攻撃に配慮）

### 2.2 フォームにトークンを埋め込む

**根拠コード（抜粋）**: `sportdataapp/HTML/login.html.php`

```php
<form action="" method="post" id="loginForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
```

**根拠コード（抜粋）**: `sportdataapp/HTML/reg.html.php`

```php
<form action="" method="post" id="registrationForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
```

### 2.3 サーバ側でトークン検証

**根拠コード（抜粋）**: `sportdataapp/PHP/login.php`

```php
// CSRFチェック
$postedCsrf = (string)($_POST['csrf_token'] ?? '');
$sessionCsrf = (string)($_SESSION['csrf_token'] ?? '');
if ($postedCsrf === '' || $sessionCsrf === '' || !hash_equals($sessionCsrf, $postedCsrf)) {
    $errors[] = '不正なリクエストです。ページを再読み込みしてから、もう一度お試しください。';
}
```

**根拠コード（抜粋）**: `sportdataapp/PHP/reg.php`

```php
// CSRFチェック
$postedCsrf = (string)($_POST['csrf_token'] ?? '');
$sessionCsrf = (string)($_SESSION['csrf_token'] ?? '');
if ($postedCsrf === '' || $sessionCsrf === '' || !hash_equals($sessionCsrf, $postedCsrf)) {
    $errors[] = '不正なリクエストです。ページを再読み込みしてから、もう一度お試しください。';
}
```

---

## 3. SQLインジェクション対策（Prepared Statement）

**狙い**: フォーム入力（group_id / user_id 等）をSQL文字列として解釈させない。

### 3.1 ログイン時のユーザー検索（`SELECT ... WHERE ... = ?`）

**根拠コード（抜粋）**: `sportdataapp/PHP/login.php`

```php
$sql = "SELECT * FROM login_tbl WHERE group_id = ? AND user_id = ?";
$stmt = mysqli_prepare($link, $sql);

mysqli_stmt_bind_param($stmt, "ss", $group_id, $user_id);
mysqli_stmt_execute($stmt);
```

### 3.2 登録時のユーザーID重複チェック（`SELECT ... WHERE ... = ?`）

**根拠コード（抜粋）**: `sportdataapp/PHP/reg.php`

```php
$check_sql = "SELECT user_id FROM login_tbl WHERE user_id = ?";
$check_stmt = mysqli_prepare($link, $check_sql);

mysqli_stmt_bind_param($check_stmt, "s", $user_id);
mysqli_stmt_execute($check_stmt);
```

### 3.3 登録時のユーザー作成（`INSERT ... VALUES (?, ?, ...)`）

**根拠コード（抜粋）**: `sportdataapp/PHP/reg.php`

```php
$sql = "INSERT INTO login_tbl (group_id, user_id, password, name, dob, height, weight, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($link, $sql);

mysqli_stmt_bind_param($stmt, "sssssdds", $group_id, $user_id, $hash, $name, $dob, $height, $weight, $position);
```

※実装上はDB互換のため、`sport` 列や `is_admin` 列の有無によりINSERT文が分岐するが、いずれもプレースホルダ `?` を用いたPrepared Statementで実装している。

---

## 4. パスワード保護（ハッシュ化保存 + 照合）

**狙い**: DB漏えい時でも平文パスワードが露出しないようにする。

### 4.1 登録時: ハッシュ化して保存

**根拠コード（抜粋）**: `sportdataapp/PHP/reg.php`

```php
$hash = password_hash($password, PASSWORD_DEFAULT);

`PASSWORD_DEFAULT`

**根拠コード（抜粋）**: `sportdataapp/PHP/login.php`

```php
if (password_verify($password, $row['password'])) {
    // ...
}

---

## 5. セッション固定攻撃（Session Fixation）対策

**狙い**: ログイン前に第三者が知っているセッションIDを、ログイン後も継続して使われることを防ぐ。

**根拠コード（抜粋）**: `sportdataapp/PHP/login.php`

```php
// セッションハイジャック対策
session_regenerate_id(true);
```

- ログイン成功時にセッションIDを再生成することで、固定化されたIDの継続利用を防ぐ

---

## 6. 権限昇格の抑止（任意の「管理者登録」を防止）

**狙い**: 一般ユーザーがフォーム改ざんで `is_admin=1` を送っても、管理者として登録されないようにする。

**根拠コード（抜粋）**: `sportdataapp/PHP/reg.php`

```php
$canSetAdmin = !empty($_SESSION['is_admin']) || !empty($_SESSION['is_super_admin']);
$is_admin = ($canSetAdmin && !empty($_POST['is_admin'])) ? 1 : 0;
```

- 管理者フラグは、ログイン中の管理者/スーパー管理者が作成する場合のみ反映

---

## 7. ログイン情報保持（remember_me）Cookie属性の強化

**狙い**: CookieをJavaScriptから参照させにくくし、外部サイトからの送信リスクを軽減する。

**根拠コード（抜粋）**: `sportdataapp/PHP/login.php`

```php
$cookieBase = [
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
];

if ($remember_me) {
    setcookie('saved_group_id', $group_id, $cookieBase + ['expires' => time() + (86400 * 30)]);
    setcookie('saved_user_id', $user_id, $cookieBase + ['expires' => time() + (86400 * 30)]);
}
```

- `HttpOnly` により、XSS時にCookieが読み取られにくい
- `SameSite=Lax` により、外部起点の送信リスクが低減
- `Secure` はHTTPS接続時のみ有効化

---

## 8. 補足（ブルートフォース対策の簡易要素）

**狙い**: 認証失敗時の総当たり攻撃をわずかに遅延させる。

**根拠コード（抜粋）**: `sportdataapp/PHP/login.php`

```php
$errors[] = 'パスワードが正しくありません';
// セキュリティのため、少し待機
sleep(1);
```

- 本格的な対策（IP/アカウント単位のレート制限等）ではないが、攻撃コストを上げる意図が読み取れる
