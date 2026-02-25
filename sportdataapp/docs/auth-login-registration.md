# ログイン・新規登録 仕様書（報告書用）

更新日: 2026-02-25

## 1. 目的
Sports Analytics App（SportDataApp）における **ログイン** および **新規登録** 機能について、画面仕様・入力条件・DB参照/更新・セッション管理・権限（一般/管理者/スーパー管理者）を実装に合わせて整理する。

対象読者: 開発者、運用担当、評価者（テスト作成者）

## 2. 対象範囲（実装ファイル）
- ログイン
  - PHP: [sportdataapp/PHP/login.php](../PHP/login.php)
  - HTML: [sportdataapp/HTML/login.html.php](../HTML/login.html.php)
- 新規登録
  - PHP: [sportdataapp/PHP/reg.php](../PHP/reg.php)
  - HTML: [sportdataapp/HTML/reg.html.php](../HTML/reg.html.php)
- セッション（タブ分離）
  - PHP: [sportdataapp/PHP/session_bootstrap.php](../PHP/session_bootstrap.php)
- DB（主要テーブル）
  - スキーマ: [sportdataapp/db/sportsdata.sql](../db/sportsdata.sql)
  - 段階SQL: [sportdataapp/db/add_admin_flag.sql](../db/add_admin_flag.sql), [sportdataapp/db/add_super_admin_flag.sql](../db/add_super_admin_flag.sql), [sportdataapp/db/add_user_sport.sql](../db/add_user_sport.sql), [sportdataapp/db/add_admin_role_requests.sql](../db/add_admin_role_requests.sql)

## 3. 前提環境
- XAMPP（Apache + MySQL/MariaDB）
- PHP 8.2 系を想定
- DB: MariaDB 10.4 系を想定（sportsdata.sql の生成情報より）

## 4. 画面仕様

### 4.1 ログイン画面
- 画面: `PHP/login.php`
- 目的: 団体ID・ユーザーID・パスワードで認証し、セッションを確立する。

#### 入力項目
| 項目 | name | 型 | 必須 | 備考 |
|---|---|---:|---:|---|
| 団体ID | group_id | text | ○ | DB検索キーの一部 |
| ユーザーID | user_id | text | ○ | DB検索キーの一部 |
| パスワード | password | password | ○ | `password_verify()` で照合 |
| ログイン情報を記憶 | remember_me | checkbox | - | Cookie（30日）に group_id / user_id を保存 |

#### 表示メッセージ
- 新規登録完了後の表示
  - `$_SESSION['registration_success']` が存在する場合、「登録が完了しました！ログインしてください。」を表示し、フラグを削除。
- エラー
  - 入力未設定: 「団体IDを入力してください」等
  - 認証失敗: 「団体IDまたはユーザーIDが正しくありません」または「パスワードが正しくありません」

#### 遷移
- 認証成功時
  - 管理者（`is_admin=1` または `is_super_admin=1`）: `admin.php` へ遷移
  - 一般ユーザー: `home.php` へ遷移
- `tab_id` が存在する場合は遷移先URLへ引き継ぐ（`?tab_id=...`）

### 4.2 新規登録画面
- 画面: `PHP/reg.php`
- 目的: login_tbl にユーザーを作成する。

#### 入力項目
| 区分 | 項目 | name | 必須 | バリデーション（サーバ） |
|---|---|---|---:|---|
| ログイン情報 | 団体ID | group_id | ○ | 空チェック |
| ログイン情報 | ユーザーID | user_id | ○ | 4文字以上、重複チェック（※実装上は全体で重複不可扱い） |
| ログイン情報 | パスワード | password | ○ | 6文字以上 |
| ログイン情報 | パスワード確認 | password_confirm | ○ | password と一致 |
| プロフィール | 氏名 | name | ○ | 空チェック |
| プロフィール | 生年月日 | dob | 条件付き | 一般ユーザー登録時に必須（管理者登録/申請時はダミー値投入） |
| プロフィール | 身長 | height | 条件付き | 一般ユーザー登録時に必須（>0） |
| プロフィール | 体重 | weight | 条件付き | 一般ユーザー登録時に必須（>0） |
| プロフィール | ポジション/役職 | position | ○ | 空チェック |
| プロフィール | 種目 | sport | 条件付き | DBに sport 列がある場合のみ必須（swim/basketball/tennis/all） |
| 権限 | 管理者として登録 | is_admin | 条件付き | 「ログイン中の管理者/スーパー管理者」のみ有効化 |
| 権限 | 管理者権限を希望（申請） | wants_admin | 任意 | ONの場合、admin_role_requests に pending を作成（テーブルがある場合） |

#### 補足（身体情報の扱い）
- 管理者として登録（is_admin=1）または申請（wants_admin=1）の場合
  - dob が未入力なら `1900-01-01`
  - height/weight は数値変換し、未入力/非数値は 0.0
- 理由（実装由来）: `login_tbl.dob/height/weight` が NOT NULL のため。

#### 送信方式
- 通常フォーム送信（HTML）
- Ajax（`X-Requested-With: XMLHttpRequest`）時は JSON を返す
  - 成功: `{ success: true, redirect: 'login.php' }`
  - 失敗: `{ success: false, errors: [...] }`

## 5. 認証・セッション設計

### 5.1 tab_id による「タブ単位のセッション分離」
- 実装: `PHP/session_bootstrap.php`
- 仕様
  - `tab_id`（英数字/`_`/`-`、8〜64文字）をURLから取得し、`session_name('SAASESSID_' . tab_id)` を設定して `session_start()`。
  - `tab_id` が無い GET は、同URLへ `?tab_id=自動生成` を付与してリダイレクトし、以後の遷移で途切れにくくする。

### 5.2 ログイン成功時に保存されるセッション値
`login.php` で `login_tbl` の行を読み、主に以下を `$_SESSION` に保存する。
- group_id / user_id / name / dob / height / weight / position
- sport（列がある場合）
- is_admin / is_super_admin
- show_loader / first_login / login_time

### 5.3 セッション固定攻撃対策
- 認証成功時に `session_regenerate_id(true)` を実行。

### 5.4 ログイン情報の保存（Cookie）
- remember_me がONの場合
  - `saved_group_id`, `saved_user_id` を **30日** 保存
- OFFの場合
  - 上記Cookieを削除

## 6. DB仕様（認証関連）

### 6.1 login_tbl
- 役割: ログイン認証、プロフィール、権限フラグを保持
- 主要列（sportsdata.sql）
  - group_id (varchar)
  - user_id (varchar)
  - password (varchar 255) ※ password_hash の結果
  - name, dob, height, weight, position
  - sport (varchar 20, nullable)
  - is_admin (tinyint)
  - is_super_admin (tinyint)

#### キー/制約（dumpより）
- `uniq_login_group_user (group_id, user_id)` がユニーク
- ※実装上のユーザーID重複チェックは `user_id` 単体で行うため、運用上は「user_id を全体で一意」として扱う。

### 6.2 admin_role_requests（管理者権限の申請）
- 役割: 一般ユーザーが「管理者権限希望」を送った履歴と状態を保持
- status: `pending` / `approved` / `rejected`
- reg.php は以下を行う（テーブルが存在する場合）
  - wants_admin=1 かつ is_admin=0 のとき、(group_id,user_id) の pending が無ければ INSERT

## 7. 例外・エラー時の基本方針
- DB接続失敗
  - login.php は 500 を返し、ユーザー向け文言で終了
- 認証失敗
  - 失敗理由に応じたメッセージを表示し、`sleep(1)` で遅延（総当たり対策の軽微な抑止）
- Ajax時の例外
  - reg.php は 500 + JSON で汎用エラーを返し、JSON破損を避ける

## 8. 報告書にそのまま書ける要約（200〜300字）
本システムは団体ID・ユーザーID・パスワードによる認証を行い、成功時にセッションへユーザー情報と権限フラグを保持する。セッションは `tab_id` によりタブ単位で分離され、同一ブラウザで複数ユーザー同時利用を可能にしている。新規登録ではパスワードはハッシュ化して保存し、必要に応じて管理者登録または管理者権限申請（pending）を作成する。
