# SportDataApp システム構成図（全体）

このドキュメントは、[system.drawio](system.drawio) に描かれている **SportDataApp 全体のシステム構成**を日本語で補足説明するものです。

更新日: 2026-02-02

---

## 1. 対象範囲

- ローカル環境（Windows + XAMPP）で動く Web アプリ
- 実装技術: **PHP（ページ単位）+ MySQL/MariaDB + JS/CSS**
- 画像等の保存先: `sportdataapp/uploads/`

※ 詳細仕様は [SPEC.md](SPEC.md) を参照。

---

## 2. 構成要素（コンポーネント）

図は大きく「クライアント（ブラウザ）」と「ローカル環境（XAMPP上のサーバー）」に分かれます。

### 2.1 クライアント（ユーザー側）

- **User Browser（Chrome/Edge）**
  - 画面表示（HTML/CSS）
  - 画面操作（JavaScript）
  - Ajax によるデータ送受信（チャット、日記API、グループ操作など）

### 2.2 サーバー側（ローカル：Windows + XAMPP）

- **Apache HTTP Server（XAMPP）**
  - `sportdataapp/` 配下の静的ファイル（CSS/JS/画像等）を配信
  - `sportdataapp/PHP/*.php` へのリクエストを受け、PHPを実行

- **SportDataApp（PHP）**
  - 主な実体は `sportdataapp/PHP/*`
  - 画面単位のページ（例: `login.php`, `home.php`, `chat.php`）
  - Ajax 用のエンドポイント（例: `contact_submit_ajax.php`, `mark_as_read.php`）

- **MySQL / MariaDB（XAMPP）**
  - アプリの永続データを保持
  - 初期スキーマ: `sportdataapp/db/sportsdata.sql`
  - 追加機能の差分: `sportdataapp/db/add_*.sql`

- **File System（サーバーのファイル領域）**
  - `sportdataapp/uploads/`（チャット画像などアップロード物）
  - PHP セッション（通常はファイルベース。加えて本アプリは `tab_id` によるセッション分離を行う）

- **Static Assets**
  - `sportdataapp/css/*`, `sportdataapp/js/*`, `sportdataapp/HTML/*.html.php`

---

## 3. 代表的な機能モジュール（PHPアプリ内部のまとまり）

図では、PHP アプリ内部を次のまとまりで表現しています。

- **Auth & Session**
  - ログイン/登録: `PHP/login.php`, `PHP/reg.php`
  - セッション初期化: `PHP/session_bootstrap.php`
  - 特徴: URLの `tab_id` を用いてタブごとに `session_name` を切り替え、同一ブラウザでも別ユーザー同時ログインを可能にする設計

- **Core Features**
  - ホーム/カレンダー: `PHP/home.php`（UIは `js/calendar.js` などと連携）
  - PI（コンディション）: `PHP/pi.php`
  - 日記: `PHP/diary.php`, `PHP/diary_api.php`
  - プロフィール: `PHP/profile_edit.php`

- **Chat / Group**
  - 一覧/本文: `PHP/chat_list.php`, `PHP/chat.php`
  - メッセージ取得/更新: `PHP/chat_messages.php`, `PHP/mark_as_read.php`
  - グループ操作: `PHP/create_group*.php`, `PHP/add_member_ajax.php` など

- **Admin**
  - 管理画面: `PHP/admin.php`
  - 問い合わせ: `PHP/contact.php`, `PHP/contact_submit_ajax.php`
  - ロール: 管理者 / スーパー管理者（詳細は [admin-roles.md](admin-roles.md)）

- **Sports Modules**
  - 水泳: `PHP/swim/*`
  - バスケ: `PHP/basketball_*`
  - テニス: `PHP/T_MNO/*`

---

## 4. データフロー（通信・I/Oの流れ）

図の矢印は、代表的な入出力の流れを示しています。

1. **ブラウザ → Apache（HTTP）**
   - ユーザーがページを開く、ボタンを押す、Ajax を送る

2. **Apache → Static Assets**
   - CSS/JS/テンプレート等の配信

3. **Apache → PHP アプリ実行**
   - `*.php` に対するリクエストを処理
   - ページ表示（HTML生成）または Ajax 応答（JSON等）を返す

4. **PHP → MySQL/MariaDB（SQL）**
   - ログイン情報、日記、チャット、予定、競技記録などのCRUD

5. **PHP ↔ ファイルシステム**
   - 画像アップロード（`uploads/`）
   - セッション読み書き（タブ分離のため `tab_id` を利用）

---

## 5. 図の開き方（draw.io）

### 5.1 VS Code で開く（推奨）

- VS Code に Draw.io 拡張（例: *Draw.io Integration* 系）が入っている場合、
  - [system.drawio](system.drawio) を開くだけで表示・編集できます。

### 5.2 diagrams.net（Web）で開く

- https://app.diagrams.net/ にアクセス
- 「Open Existing Diagram」→ `sportdataapp/docs/system.drawio` を選択

---

## 6. 補足（必要なら図を拡張できます）

希望があれば、次の詳細図も追加できます。

- 機能別データフロー図（例: チャット送信〜既読更新〜画像保存）
- DB テーブル群の分類（認証/日記/チャット/カレンダー/競技別）
- 「スーパー管理者は管理画面へ誘導」などロールによる画面遷移図
