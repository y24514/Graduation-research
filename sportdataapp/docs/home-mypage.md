# マイページ（home.php）説明資料（報告書用）

更新日: 2026-02-25

## 1. 目的
本資料は、Sports Analytics App の **マイページ（ダッシュボード）** である `home.php` について、画面構成・表示内容・DB参照/更新・権限制御・ユーザー操作を実装に合わせて整理する。

対象読者: 仕様説明（報告書）、引き継ぎ、テスト設計

## 2. 対象範囲（関連ファイル）
- 画面（PHP）: [sportdataapp/PHP/home.php](../PHP/home.php)
- 画面テンプレ（HTML+JS）: [sportdataapp/HTML/home.html.php](../HTML/home.html.php)
- 目標保存API（JSON）: [sportdataapp/PHP/goalsave.php](../PHP/goalsave.php)
- カレンダー保存API（テキスト応答）: [sportdataapp/PHP/calendarsave.php](../PHP/calendarsave.php)
- カレンダーUI（FullCalendar初期化）: [sportdataapp/js/calendar.js](../js/calendar.js)
- 共通ナビ/権限制御: [sportdataapp/PHP/header.php](../PHP/header.php)
- DBスキーマ: [sportdataapp/db/sportsdata.sql](../db/sportsdata.sql)

## 3. アクセス条件・権限制御

### 3.1 ログイン必須
`$_SESSION['user_id']` と `$_SESSION['group_id']` が無い場合、`login.php` へリダイレクト。

### 3.2 管理者の扱い（重要）
- **管理者（is_admin=1 かつ is_super_admin=0）**
  - `home.php` 冒頭で `admin.php` へリダイレクト（ダッシュボードは使わない運用）。
- **スーパー管理者（is_super_admin=1）**
  - `header.php` の制御により、基本的に `admin.php` 以外へは遷移できない（許可ページのみ）。

結論として、マイページ（home）は主に **一般ユーザー向け** 画面である。

### 3.3 タブ単位セッション（tab_id）
- `session_bootstrap.php` により `tab_id` ごとに `session_name` を分け、同一ブラウザの複数タブで別ユーザー同時利用が可能。
- `home.html.php` 内の目標保存 fetch は `tab_id` を body に含めて送る（セッション維持のため）。

## 4. 画面概要（表示ブロック）
マイページは大きく以下で構成される。

1) 今月の目標（Goalカード）
2) ユーザー情報（プロフィールカード）
3) お知らせ（未読チャット通知）
4) カレンダー（予定表示・登録）

## 5. 表示内容とデータソース

### 5.1 今月の目標（goal_tbl）
- 対象テーブル: `goal_tbl`
- 表示ルール
  - 「今月（created_at が当月範囲）」の **最新1件** を表示
  - 今月のデータが無ければ、入力欄（textarea）を表示

- 今月判定（PHP側）
  - `monthStart = YYYY-mm-01 00:00:00`
  - `monthEnd   = 次月 YYYY-mm-01 00:00:00`

- 更新（保存）
  - フロント（home.html.php）→ `fetch('../PHP/goalsave.php')`
  - サーバ（goalsave.php）
    - 今月の最新goalが存在: UPDATE
    - 無い: INSERT（created_at=NOW）
  - 応答: JSON（success/message）

#### 根拠コード（抜粋）

**今月の最新目標を取得（home.php）**

```php
// 今月の範囲（created_at で判定）
$monthStart = date('Y-m-01 00:00:00');
$monthEnd = date('Y-m-01 00:00:00', strtotime('+1 month'));

// goal表示（今月の最新）
$stmt = sportdata_mysqli_prepare_retry($link, "
    SELECT goal
    FROM goal_tbl
    WHERE group_id = ? AND user_id = ?
      AND created_at >= ? AND created_at < ?
    ORDER BY created_at DESC
    LIMIT 1
", $host, $usr, $pwd, $dbName);
```

**保存（フロント→API呼び出し / home.html.php）**

```js
fetch('../PHP/goalsave.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded',
    'Accept': 'application/json',
  },
  body: 'goal=' + encodeURIComponent(goalValue) + '&tab_id=' + encodeURIComponent(tabId)
})
```

**保存（当月の最新がある場合はUPDATE、なければINSERT / goalsave.php）**

```php
if ($existingGoalId) {
    $stmtUpd = mysqli_prepare($link, "UPDATE goal_tbl SET goal = ? WHERE goal_id = ? AND user_id = ? AND group_id = ?");
    mysqli_stmt_bind_param($stmtUpd, "siss", $goal, $existingGoalId, $user_id, $group_id);
    $success = mysqli_stmt_execute($stmtUpd);
} else {
    $stmtIns = mysqli_prepare($link,"INSERT INTO goal_tbl(group_id,user_id,goal,created_at) VALUES(?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmtIns, "sss", $group_id, $user_id, $goal);
    $success = mysqli_stmt_execute($stmtIns);
}
```

### 5.2 ユーザー情報カード（セッション値）
`login.php` ログイン時にセッションへ保存したプロフィール情報を表示する。
- 表示（一般ユーザーのみ）
  - 生年月日 / 身長 / 体重
  - 「身体情報」ボタン（`pi.php` へリンク）

- 表示（共通）
  - 氏名 / ポジション（役職）
  - アイコン: `user_icon_helper.php` を利用し、画像があれば画像、無ければ氏名先頭1文字。

※管理者は `home.php` に到達しないため、画面上の表示条件（`empty($_SESSION['is_admin'])`）と合わせて、運用上も一般ユーザー前提。

#### 根拠コード（抜粋）

**セッションから表示用ユーザー情報を取得（home.php）**

```php
// セッションからユーザー情報を取得
$userName = $_SESSION['name'] ?? '';
$userDob = $_SESSION['dob'] ?? '';
$userHeight = $_SESSION['height'] ?? '';
$userWeight = $_SESSION['weight'] ?? '';
$userPosition = $_SESSION['position'] ?? '';

require_once __DIR__ . '/user_icon_helper.php';
$currentUserIcon = sportdata_find_user_icon($group_id, $user_id);
$currentUserIconUrl = $currentUserIcon['url'] ?? null;
```

**アイコン表示（画像が無ければ氏名先頭1文字 / home.html.php）**

```php
<?php if (!empty($currentUserIconUrl)): ?>
  <img src="<?= htmlspecialchars($currentUserIconUrl, ENT_QUOTES, 'UTF-8') ?>" alt="ユーザーアイコン">
<?php else: ?>
  <?= mb_substr($userName, 0, 1, 'UTF-8') ?>
<?php endif; ?>
```

### 5.3 お知らせ（未読チャット通知）
- 対象テーブル
  - `chat_tbl`（メッセージ）
  - `chat_read_status_tbl`（既読管理）
  - `chat_group_member_tbl`（グループ所属）
  - `chat_group_tbl`（グループ名表示）
  - `login_tbl`（送信者名表示）

- 抽出条件（home.php）
  - 直近7日（`created_at >= NOW()-7day`）
  - 自分が送信者のものは除外
  - 既読ID（last_read_message_id）より新しいメッセージのみを未読として扱う
  - 最大5件、created_at 降順

- 表示
  - DM/グループでバッジを出し分け
  - 送信者アイコン（存在すれば画像、無ければ先頭1文字）
  - クリックすると `chat_list.php?type=...&id=...` へ遷移

- UI機能
  - 「すべて / グループ / DM」のフィルタボタンで表示を切り替え（JS）。

#### 根拠コード（抜粋）

**未読通知の抽出（最新5件・直近7日・既読IDより新しいもの / home.php）**

```php
$stmt_chat = sportdata_mysqli_prepare_retry($link, "
    SELECT 
        c.id,
        c.message,
        c.created_at,
        c.chat_type,
        c.chat_group_id,
        c.recipient_id,
        c.user_id as sender_user_id,
        l.name as sender_name,
        g.group_name
    FROM chat_tbl c
    LEFT JOIN login_tbl l ON c.user_id = l.user_id AND c.group_id = l.group_id
    LEFT JOIN chat_group_tbl g ON c.chat_group_id = g.chat_group_id
    WHERE (
        (c.chat_type = 'direct' AND c.recipient_id = ? AND c.group_id = ?)
        OR 
        (c.chat_type = 'group' AND c.chat_group_id IN (
            SELECT chat_group_id FROM chat_group_member_tbl 
            WHERE user_id = ? AND group_id = ?
        ))
    )
    AND c.user_id != ?
    AND c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    AND c.id > COALESCE(
        (SELECT MAX(last_read_message_id) 
         FROM chat_read_status_tbl 
         WHERE user_id = ? 
         AND group_id = ?
         AND (
            (c.chat_type = 'direct' AND chat_type = 'direct' AND recipient_id = c.user_id)
            OR
            (c.chat_type = 'group' AND chat_type = 'group' AND chat_group_id = c.chat_group_id)
         )
        ), 0)
    ORDER BY c.created_at DESC
    LIMIT 5
", $host, $usr, $pwd, $dbName);
```

**通知クリック時の遷移URL組み立て（グループ/DMで分岐 / home.html.php）**

```php
$direct_id = $notification['chat_type'] === 'direct' 
  ? $notification['sender_user_id'] 
  : '';

$chat_url = $notification['chat_type'] === 'group' 
  ? 'chat_list.php?type=group&id=' . $notification['chat_group_id']
  : 'chat_list.php?type=direct&id=' . urlencode($direct_id);
```

### 5.4 カレンダー（予定表示・登録）
- 対象テーブル: `calendar_tbl`

- 表示（home.php → home.html.php → FullCalendar）
  - `records[]` を PHP で組み立て、`eventsFromPHP` として JS に渡す
  - FullCalendar（`js/fullcalendar/dist/index.global.js`）で月表示
  - 祝日/土日を色分け（`js/calendar.js` の holiday 配列）

- 共有予定（is_shared）
  - `home.php` は DBに `calendar_tbl.is_shared` があるかを **prepareできるか** で判定
  - `is_shared` がある場合
    - `user_id = 自分` または `is_shared=1` を表示対象に含める
  - `is_shared` が無い場合
    - `user_id = 自分` のみ表示

- 予定登録
  - 画面側: カレンダー選択（PCはドラッグ選択、タッチ端末はタップ）でモーダル表示
  - 保存: `fetch('../PHP/calendarsave.php')`
  - 応答: テキスト（成功/エラー文言）
  - 保存成功時: `window.calendarInstance.addEvent(...)` で画面に即時反映

- 共有登録（管理者のみ）
  - `canShareCalendar` が true（is_admin/is_super_admin）だと、モーダルに「共有」チェックが出る
  - `calendarsave.php` でも **管理者のみ** `is_shared=1` を有効化
  - DBに is_shared 列が無い環境で共有をONにすると「DB未更新」メッセージで拒否

#### 根拠コード（抜粋）

**共有予定（is_shared）の列有無を SQL のprepare可否で判定（home.php）**

```php
$calendarHasIsShared = false;
try {
    $probe = mysqli_prepare($link, "SELECT is_shared FROM calendar_tbl LIMIT 0");
    if ($probe !== false) {
        $calendarHasIsShared = true;
        mysqli_stmt_close($probe);
    }
} catch (Throwable $e) {
    $calendarHasIsShared = false;
}
```

**表示対象（自分の予定 + 共有予定）/ home.php**

```php
if ($calendarHasIsShared) {
    $stmt2 = sportdata_mysqli_prepare_retry(
        $link,
        'SELECT title, startdate, enddate, is_shared FROM calendar_tbl WHERE group_id = ? AND (user_id = ? OR is_shared = 1)',
        $host,
        $usr,
        $pwd,
        $dbName
    );
}
```

**PHP→JSへイベント配列を渡す（home.html.php）**

```php
<script>
    const eventsFromPHP = <?= json_encode($records, JSON_UNESCAPED_UNICODE); ?>;
    const canShareCalendar = <?= !empty($canShareCalendar) ? 'true' : 'false' ?>;
</script>
```

**FullCalendarへeventsを渡し、選択時にモーダルを開く（calendar.js）**

```js
var calendar = new FullCalendar.Calendar(calendarEl, {
  selectable: !isTouch,
  events: (typeof eventsFromPHP !== 'undefined' ? eventsFromPHP : []),
  select: function(info) {
    openEventModal(info);
    calendar.unselect();
  }
});
```

**登録API呼び出しと、成功時の即時反映（home.html.php）**

```js
const sharedEl = document.getElementById('event-is-shared');
const isShared = (typeof canShareCalendar !== 'undefined' && canShareCalendar && sharedEl && sharedEl.checked) ? '1' : '';

fetch('../PHP/calendarsave.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  body: new URLSearchParams(Object.assign({
    title: title,
    memo: memo,
    startdate: currentEventInfo.startStr,
    enddate: currentEventInfo.endStr
  }, isShared ? { is_shared: '1' } : {}))
})
.then(async (res) => {
  const text = await res.text();
  if (!res.ok) throw new Error(text || '保存に失敗しました');
  if (text.includes('未更新') || text.includes('エラー') || text.includes('未ログイン')) {
    alert(text);
    return;
  }
  if (window.calendarInstance) {
    window.calendarInstance.addEvent({ title, start: currentEventInfo.startStr, end: currentEventInfo.endStr });
  }
})
```

**共有登録のサーバ側制御（管理者のみ + DB列が必要 / calendarsave.php）**

```php
$requestedShared = !empty($_POST['is_shared']);
$isAdminUser = !empty($_SESSION['is_admin']) || !empty($_SESSION['is_super_admin']);
$isShared = ($requestedShared && $isAdminUser);

$hasIsShared = calendar_has_column($link, 'calendar_tbl', 'is_shared');
if ($isShared && !$hasIsShared) {
    echo 'DBが未更新のため共有できません（add_calendar_shared_events.sql を適用してください）';
    exit;
}
```

## 6. ローディング演出（初回ログイン時）
- `home.php` は `$_SESSION['first_login'] === true` のときだけローダー表示をONにし、表示後に false にする。
- `home.html.php` で `showLoader` をJSへ渡し、`js/loading.js` が進捗表示を行う想定。

## 7. エラー時の挙動

### 7.1 DB接続断への耐性
`home.php` は `mysqli_ping` 等を利用し、切断（2006/2013）検知時に再接続して処理を継続する。

### 7.2 DBエラー表示
例外発生時は
- 画面上部に「DBとの接続が一時的に切れました。ページを再読み込みしてください。」を表示
- カレンダー/通知などの配列は空として描画（画面が落ちない）

## 8. 画面操作手順（報告書向けに短く）
1. `login.php` でログイン成功すると、一般ユーザーは `home.php`（マイページ）へ遷移する。
2. 「今月の目標」を入力し「保存」を押すと、当月の目標が保存され画面に反映される。
3. 「お知らせ」から未読メッセージ（DM/グループ）をクリックすると、該当チャットへ移動できる。
4. カレンダーで日付範囲を選択（またはタップ）し、イベント名を入力して登録すると予定が追加される。

## 9. テスト観点（要点）
- 目標
  - 当月データなし→INSERT、当月データあり→UPDATE になること
  - 未入力（空/空白）で保存できないこと
- 通知
  - 既読IDより新しいメッセージのみが出ること
  - 7日より古いメッセージが出ないこと
  - 最大5件に制限されること
- カレンダー
  - 予定登録がDBへ保存され、画面にも即時反映されること
  - 共有ONは管理者のみ、かつ is_shared 列が無い場合は拒否されること
- 権限
  - 管理者が home に入らず admin へ誘導されること

