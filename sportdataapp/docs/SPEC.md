# SportDataApp 全体仕様書（概要）

最終更新: 2026-01-21

## 1. 目的 / 概要
部活動・チーム単位（`group_id`）で、選手の基本情報・目標・コンディション（PI）・日記・チャット・予定（カレンダー）・競技別データ（水泳/テニス/バスケット）を記録/共有するWebアプリ。

実装は PHP（ページ単位）+ MySQL/MariaDB（XAMPP想定）+ JS/CSS。

## 2. ユーザー種別と権限
ユーザーは `login_tbl` のフラグで権限を持つ。

- 一般ユーザー
  - 自分のPI入力、目標入力、日記投稿、チャット利用、競技記録入力など。
- 管理者（`is_admin=1`）
  - 管理画面へのアクセス。
  - 日記の提出物閲覧・フィードバック（機能が有効な場合）。
  - 問い合わせ対応（返信）。
- スーパー管理者（`is_super_admin=1`）
  - 管理者権限申請（`admin_role_requests`）の承認/却下。

## 3. 認証 / セッション設計
### 3.1 ログイン
- 画面: `PHP/login.php`
- DB: `login_tbl`
- 成功時: セッションにユーザー情報を保存し、管理者は管理画面へ、一般はホームへ遷移。

### 3.2 タブ単位のセッション分離（特徴）
- 実装: `PHP/session_bootstrap.php`
- `tab_id` をURLに付与し、`session_name` を `tab_<tab_id>` に切り替えることで、ブラウザの複数タブで別ユーザーとして同時ログインできる設計。

## 4. 主要機能（画面単位）
※ファイル名は代表。HTMLテンプレは `HTML/*.html.php`。

### 4.1 ユーザー登録
- 画面: `PHP/reg.php`
- DB: `login_tbl`
- 補足: `login_tbl` に `sport` 列がある場合のみ種目保存が有効（互換実装が存在）。

### 4.2 ホーム（ダッシュボード）
- 画面: `PHP/home.php`
- 概要: 目標、予定、チャット未読などの概要。
- DB: `goal_tbl`, `calendar_tbl`, `chat_*` ほか。

### 4.3 目標管理
- DB: `goal_tbl`
- 目的: 個人ごとの目標、進捗、期限を管理。

### 4.4 PI（コンディション）管理
- 画面: `PHP/pi.php`
- DB: `pi_tbl`
- 目的: 体重/睡眠/怪我などの記録（体脂肪率・筋肉量・目標体重・メモ等の拡張列あり）。

### 4.5 日記
- 画面: `PHP/diary.php`
- API: `PHP/diary_api.php`
- DB: `diary_tbl`
- 目的: 日記のCRUD。
- 管理者関連:
  - 提出（`submitted_to_admin` 等）/フィードバック（`admin_feedback` 等）は、列が存在する場合に有効（互換実装）。

### 4.6 チャット（個人/グループ）
- 画面: `PHP/chat_list.php`, `PHP/chat.php`
- JS: `js/chat_list.js`, `js/chat.js`
- DB:
  - メッセージ: `chat_tbl`（画像添付 `image_path`, `image_name` / 論理削除 `is_deleted`）
  - グループ: `chat_group_tbl`, `chat_group_member_tbl`
  - 既読: `chat_read_status_tbl`

### 4.7 グループ作成・設定
- 画面: `PHP/create_group.php`, `PHP/group_settings.php`
- Ajax: `PHP/create_group_ajax.php`, `PHP/add_member_ajax.php`, `PHP/delete_group_ajax.php`
- DB: `chat_group_tbl`, `chat_group_member_tbl`
- 補足: 実装上、グループ作成者のみメンバー編集が可能。

### 4.8 カレンダー（予定）
- 画面: `HTML/home.html.php`（カレンダーUI）
- JS: `js/calendar.js`
- DB: `calendar_tbl`（共有フラグ `is_shared`）

### 4.9 問い合わせ
- 画面: `PHP/contact.php`
- Ajax: `PHP/contact_submit_ajax.php`
- DB: `inquiries_tbl`
- 管理者: `PHP/admin.php` で返信。

### 4.10 管理（管理者/スーパー管理者）
- 画面: `PHP/admin.php`
- 主な管理対象:
  - 管理者申請: `admin_role_requests`（スーパー管理者のみ承認/却下）
  - 問い合わせ: `inquiries_tbl`
- セキュリティ: CSRFトークンが使われる箇所あり。

## 5. 競技別機能
### 5.1 水泳
- 入力: `PHP/swim/swim_input.php`
- 分析/表示: `HTML/swim_analysis.html.php` ほか
- DB:
  - 記録: `swim_tbl`（stroke/lap をJSONで保持）
  - ベスト: `swim_best_tbl`（種目・距離ごとのベストを保持）
  - 練習メニュー: `swim_practice_tbl`

### 5.2 テニス
- 入口: `PHP/T_MNO/index.php`（試合記録UI）
- DB:
  - 試合: `tennis_games`
  - プレーデータ: `tennis_actions`
  - 作戦盤: `tennis_strategies`
- 注意: 一部コードに旧テーブル（`games`/`game_actions` 相当）を作成する互換ロジックが残るため、運用DBは `tennis_*` を正として扱う。

### 5.3 バスケットボール
- 入口: `PHP/basketball_index.php`（チーム/スタメン→試合開始）
- DB:
  - チーム: `teams`
  - 選手: `players`
  - 試合: `games`
  - アクション: `game_actions`
  - 作戦盤: `basketball_strategies`

## 6. データベース（主要テーブル）
スキーマ基準: `db/sportsdata.sql`

- 認証: `login_tbl`
- 管理者申請: `admin_role_requests`
- 目標: `goal_tbl`
- PI: `pi_tbl`
- 日記: `diary_tbl`
- 予定: `calendar_tbl`
- 問い合わせ: `inquiries_tbl`
- チャット: `chat_tbl`, `chat_group_tbl`, `chat_group_member_tbl`, `chat_read_status_tbl`
- 水泳: `swim_tbl`, `swim_best_tbl`, `swim_practice_tbl`
- テニス: `tennis_games`, `tennis_actions`, `tennis_strategies`
- バスケ: `teams`, `players`, `games`, `game_actions`, `basketball_strategies`

ER図は [uml/er.puml](uml/er.puml) を参照。

## 7. 例外・注意点
- 互換実装: DB列の存在をチェックして機能ON/OFFする箇所があるため、環境によって画面の挙動が変わる。
- DB統合: テニスは `tennis_*` テーブルで `sportsdata` に同居する方針（衝突回避）。

## 8. UML
- ユースケース図: [uml/usecase.puml](uml/usecase.puml)
- ER図: [uml/er.puml](uml/er.puml)
- シーケンス図: [uml/sequence-login.puml](uml/sequence-login.puml), [uml/sequence-chat-send.puml](uml/sequence-chat-send.puml), [uml/sequence-diary-submit-feedback.puml](uml/sequence-diary-submit-feedback.puml)

### 8.1 画像（レンダリング済み）
- ユースケース図: [uml/out/usecase.png](uml/out/usecase.png) / [uml/out/usecase.svg](uml/out/usecase.svg)
- ER図: [uml/out/er.png](uml/out/er.png) / [uml/out/er.svg](uml/out/er.svg)
- ER図（分割）:
  - Core: [uml/out/er-core.png](uml/out/er-core.png) / [uml/out/er-core.svg](uml/out/er-core.svg)
  - Chat: [uml/out/er-chat.png](uml/out/er-chat.png) / [uml/out/er-chat.svg](uml/out/er-chat.svg)
  - Swim: [uml/out/er-swim.png](uml/out/er-swim.png) / [uml/out/er-swim.svg](uml/out/er-swim.svg)
  - Basketball: [uml/out/er-basketball.png](uml/out/er-basketball.png) / [uml/out/er-basketball.svg](uml/out/er-basketball.svg)
  - Tennis: [uml/out/er-tennis.png](uml/out/er-tennis.png) / [uml/out/er-tennis.svg](uml/out/er-tennis.svg)
- ログイン: [uml/out/sequence-login.png](uml/out/sequence-login.png) / [uml/out/sequence-login.svg](uml/out/sequence-login.svg)
- チャット送信: [uml/out/sequence-chat-send.png](uml/out/sequence-chat-send.png) / [uml/out/sequence-chat-send.svg](uml/out/sequence-chat-send.svg)
- 日記提出/FB: [uml/out/sequence-diary-submit-feedback.png](uml/out/sequence-diary-submit-feedback.png) / [uml/out/sequence-diary-submit-feedback.svg](uml/out/sequence-diary-submit-feedback.svg)
