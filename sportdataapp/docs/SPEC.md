# SportDataApp 全体仕様書（概要）

実装は PHP（ページ単位）+ MySQL/MariaDB（XAMPP想定）+ JS/CSS。

## 1. この文書の目的・前提
この仕様書は「何ができるか」に加えて、初見でも一通り操作できるように「使い方（画面操作）」「運用時の注意」をまとめる。

### 1.1 想定環境（ローカル）
- Web/DB: XAMPP（Apache + MySQL/MariaDB）
- ブラウザ: Chrome / Edge（最新版想定）
- DB初期化: `sportdataapp/db/sportsdata.sql` を基準スキーマとして投入
  - 追加機能は `sportdataapp/db/add_*.sql` に段階的マイグレーションが存在
- アップロード: `sportdataapp/uploads/` に画像等を保存（権限・パス設定はXAMPPの運用に依存）

### 1.2 画面の開き方（URL）
- 基本入口: `PHP/login.php`
- 本アプリは「タブ単位のセッション分離」を行うため、URLに `tab_id` を付与して開く運用を推奨。
  - 例: `/sportdataapp/PHP/login.php?tab_id=demo1`
  - 同じブラウザで別ユーザーを同時ログインしたい場合は、別タブで `tab_id` を変えて開く（例: `demo2`）。
  - `tab_id` は英数字の任意文字列でよい（実運用では推測されにくい値を推奨）。

### 1.3 基本の画面遷移（全体像）
- ログイン成功 → 一般ユーザーはホーム（ダッシュボード）へ、管理者は管理画面へ遷移
- ナビゲーション（メニュー）から各機能へ移動（実体は `PHP/*.php` をページ遷移）
- 主要なデータ更新はフォーム送信 + 一部Ajax（問い合わせ送信、グループ操作、日記API、チャット等）

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

#### 使い方（ログイン手順）
1. `PHP/login.php?tab_id=任意` を開く
2. メール/ユーザー名（実装のログインキー）とパスワードを入力
3. 成功: 自動で次画面へ遷移
4. 失敗: エラーメッセージ表示（入力値、DB接続、ユーザー無効などが主因）

### 3.2 タブ単位のセッション分離（特徴）
- 実装: `PHP/session_bootstrap.php`
- `tab_id` をURLに付与し、`session_name` を `tab_<tab_id>` に切り替えることで、ブラウザの複数タブで別ユーザーとして同時ログインできる設計。

#### 使い方（タブ分離の例）
- タブA: `.../login.php?tab_id=userA` → ユーザーAでログイン
- タブB: `.../login.php?tab_id=userB` → ユーザーBでログイン
- 注意: `tab_id` を付けない/同一にするとセッションが共有され、意図せずログイン状態が混ざる。

## 4. 主要機能（画面単位）
※ファイル名は代表。HTMLテンプレは `HTML/*.html.php`。

### 4.1 ユーザー登録
- 画面: `PHP/reg.php`
- DB: `login_tbl`
- 補足: `login_tbl` に `sport` 列がある場合のみ種目保存が有効（互換実装が存在）。

#### 使い方（登録手順）
1. 登録画面で必要情報（ユーザー情報・認証情報）を入力
2. 「登録」実行
3. 成功: ログイン画面へ戻る/自動遷移（実装に依存）

#### 入力・挙動の補足
- 種目（sport）: DBに列がある環境のみ保存される。列がない環境ではUIがあっても保存されない/無視される場合がある。

### 4.2 ホーム（ダッシュボード）
- 画面: `PHP/home.php`
- 概要: 目標、予定、チャット未読などの概要。
- DB: `goal_tbl`, `calendar_tbl`, `chat_*` ほか。

#### 使い方（ホームでできること）
- 今日/直近の予定を確認（カレンダーと連動）
- 目標の概要を確認（目標管理と連動）
- チャットの未読や更新を確認（チャットと連動）

#### 運用上の注意
- 情報の表示内容は「そのユーザーのデータが存在するか」に依存する（未登録だと空表示）。

### 4.3 目標管理
- DB: `goal_tbl`
- 目的: 個人ごとの目標、進捗、期限を管理。

#### 使い方（典型フロー）
1. 目標入力/編集画面へ移動
2. 目標（タイトル/本文）、期限、進捗などを入力
3. 保存 → ホーム等にサマリ表示

#### 補足
- 期限や進捗の粒度（%/段階など）は実装側のUIに依存。

### 4.4 PI（コンディション）管理
- 画面: `PHP/pi.php`
- DB: `pi_tbl`
- 目的: 体重/睡眠/怪我などの記録（体脂肪率・筋肉量・目標体重・メモ等の拡張列あり）。

#### 使い方（入力手順）
1. PI画面を開く
2. 日付を選択（または当日）し、体重/睡眠/体調などを入力
3. 保存
4. 過去日の記録を参照・更新（可能なUIの場合）

#### 入力項目の互換性
- 体脂肪率・筋肉量・目標体重・メモ等は「DBに列が存在する環境」でのみ有効。
- 列がない環境では、該当入力欄が表示されない/保存されないなど挙動が変わる。

### 4.5 日記
- 画面: `PHP/diary.php`
- API: `PHP/diary_api.php`
- DB: `diary_tbl`
- 目的: 日記のCRUD。
- 管理者関連:
  - 提出（`submitted_to_admin` 等）/フィードバック（`admin_feedback` 等）は、列が存在する場合に有効（互換実装）。

#### 使い方（一般ユーザー）
- 作成: 日付と本文を入力 → 保存
- 閲覧: 日付を切り替えて過去の日記を確認
- 編集/削除: 自分の日記のみ更新/削除（UIの提供範囲に依存）

#### 使い方（管理者/提出機能がある場合）
- ユーザーが「管理者へ提出」すると、管理者画面で提出一覧として閲覧できる
- 管理者はフィードバックを入力し、ユーザーに返却（閲覧可能）

#### 注意
- 提出・フィードバック系の列がDBに無い場合、提出ボタンや管理者側の表示が出ない/無効になる。

### 4.6 チャット（個人/グループ）
- 画面: `PHP/chat_list.php`, `PHP/chat.php`
- JS: `js/chat_list.js`, `js/chat.js`
- DB:
  - メッセージ: `chat_tbl`（画像添付 `image_path`, `image_name` / 論理削除 `is_deleted`）
  - グループ: `chat_group_tbl`, `chat_group_member_tbl`
  - 既読: `chat_read_status_tbl`

#### 使い方（基本操作）
1. チャット一覧で相手（個人）またはグループを選択
2. 送信欄にメッセージを入力 → 送信
3. 相手のメッセージが時系列で表示される

#### 既読の考え方
- 既読状態は `chat_read_status_tbl` で管理。
- 未読表示の出方は一覧UIの実装に依存する。

#### 画像添付（対応している場合）
- 画像を選択して送信すると `chat_tbl` の `image_path` / `image_name` に保存される。
- 保存先（uploads配下など）はサーバー設定と実装に依存。

#### 論理削除
- `is_deleted=1` のメッセージは「削除済み」として扱われる（表示の仕方はUI実装に依存）。

### 4.7 グループ作成・設定
- 画面: `PHP/create_group.php`, `PHP/group_settings.php`
- Ajax: `PHP/create_group_ajax.php`, `PHP/add_member_ajax.php`, `PHP/delete_group_ajax.php`
- DB: `chat_group_tbl`, `chat_group_member_tbl`
- 補足: 実装上、グループ作成者のみメンバー編集が可能。

#### 使い方（作成）
1. グループ作成画面でグループ名などを入力
2. メンバーを選択して作成
3. 作成後、チャット一覧にグループが出現

#### 使い方（設定）
- グループ名変更、メンバー追加/削除、グループ削除（提供されている操作のみ）

#### 権限ルール
- 原則: 作成者のみがメンバー編集・削除を行える。

### 4.8 カレンダー（予定）
- 画面: `HTML/home.html.php`（カレンダーUI）
- JS: `js/calendar.js`
- DB: `calendar_tbl`（共有フラグ `is_shared`）

#### 使い方（予定の登録）
1. ホームのカレンダーで日付/時間を選択
2. タイトル・詳細を入力して保存
3. 保存後、カレンダーに予定が表示

#### 共有予定
- `is_shared=1` の予定は共有として扱う（誰に共有されるかの範囲は実装設計に依存）。

### 4.9 問い合わせ
- 画面: `PHP/contact.php`
- Ajax: `PHP/contact_submit_ajax.php`
- DB: `inquiries_tbl`
- 管理者: `PHP/admin.php` で返信。

#### 使い方（ユーザー）
1. 問い合わせフォームに内容を入力
2. 送信 → DBに記録
3. 返信がある場合、所定のUI（管理画面/問い合わせ画面の表示仕様）で確認

#### 使い方（管理者）
- 管理画面で問い合わせ一覧を確認し、返信を登録する。

### 4.10 管理（管理者/スーパー管理者）
- 画面: `PHP/admin.php`
- 主な管理対象:
  - 管理者申請: `admin_role_requests`（スーパー管理者のみ承認/却下）
  - 問い合わせ: `inquiries_tbl`
- セキュリティ: CSRFトークンが使われる箇所あり。

#### 使い方（管理者）
- 問い合わせ対応: 未対応/対応済みを確認し、返信を登録
- 日記提出の確認（機能が有効な場合）: 提出一覧の閲覧、フィードバック入力

#### 使い方（スーパー管理者）
- 管理者申請一覧を確認
- 承認: 対象ユーザーに管理者権限（`is_admin` 等）を付与
- 却下: 申請状態を却下として記録

#### 注意（CSRF）
- 一部の更新操作はCSRFトークンを検証するため、画面を経由せず直接POSTすると失敗する。

## 5. 競技別機能
### 5.1 水泳
- 入力: `PHP/swim/swim_input.php`
- 分析/表示: `HTML/swim_analysis.html.php` ほか
- DB:
  - 記録: `swim_tbl`（stroke/lap をJSONで保持）
  - ベスト: `swim_best_tbl`（種目・距離ごとのベストを保持）
  - 練習メニュー: `swim_practice_tbl`

#### 使い方（入力→分析の流れ）
1. 入力画面で日付、種目、距離、タイム等を入力
2. 詳細（ラップ/ストローク等）がある場合、UIの指示に従って入力（DB上はJSONで保持）
3. 保存後、分析/表示画面で推移・ベスト等を確認

#### ベスト管理
- 新記録がベストを更新した場合に `swim_best_tbl` に反映（更新ロジックは実装に依存）。

### 5.2 テニス
- 入口: `PHP/T_MNO/index.php`（試合記録UI）
- DB:
  - 試合: `tennis_games`
  - プレーデータ: `tennis_actions`
  - 作戦盤: `tennis_strategies`
- 注意: 一部コードに旧テーブル（`games`/`game_actions` 相当）を作成する互換ロジックが残るため、運用DBは `tennis_*` を正として扱う。

#### 使い方（試合記録の流れ：代表）
1. 入口ページから試合を作成/選択
2. ラリー/ショット等のアクションを記録
3. 試合終了後、集計結果を確認

#### 運用上の注意
- DBは `tennis_*` を正として運用する（互換テーブルが生成されても参照・保守対象にしない）。

### 5.3 バスケットボール
- 入口: `PHP/basketball_index.php`（チーム/スタメン→試合開始）
- DB:
  - チーム: `teams`
  - 選手: `players`
  - 試合: `games`
  - アクション: `game_actions`
  - 作戦盤: `basketball_strategies`

#### 使い方（試合開始まで）
1. チームを選択（または作成）
2. 選手登録・スタメン設定
3. 試合を開始し、アクション（得点/ファウル等）を記録

#### 作戦盤
- 作戦の保存/読み込みがある場合、`basketball_strategies` に格納される。

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

## 8. よくある運用・トラブルシュート
### 8.1 画面が真っ白/500になる
- PHPエラーの可能性が高い。XAMPPのApacheログ/ PHP error log を確認。
- DB接続情報が環境と一致しているか、スキーマ投入済みかを確認。

### 8.2 ログインできない
- `login_tbl` にユーザーが存在するか
- パスワードの照合方式（平文/ハッシュ）が実装と一致しているか
- `tab_id` の混在により別タブのセッションと勘違いしていないか（URLを確認）

### 8.3 画像送信/アップロードが失敗する
- `uploads/` の書き込み権限
- `php.ini` の `upload_max_filesize` / `post_max_size`
- パスの組み立てが環境（Windowsパス/URL）に合っているか

## 9. UML

## 9. UML
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
