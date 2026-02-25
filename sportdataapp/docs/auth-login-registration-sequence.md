# ログイン・新規登録 処理フロー（シーケンス）

更新日: 2026-02-25

この資料は、実装（PHP/DB）に合わせてログインと新規登録の処理順序を図示する。

## 1. セッション開始（tab_id ブートストラップ）
- 対象: `PHP/session_bootstrap.php`
- 目的: URLクエリ `tab_id` を元に `session_name` を切り替え、タブ単位でセッションを分離する。

```mermaid
sequenceDiagram
  autonumber
  participant B as Browser
  participant A as Apache/PHP

  B->>A: GET /PHP/login.php (tab_idなし)
  A->>A: session_bootstrap.php
  A->>A: tab_id生成
  A-->>B: 302 Location: /PHP/login.php?tab_id=...

  B->>A: GET /PHP/login.php?tab_id=...
  A->>A: session_name("SAASESSID_"+tab_id)
  A->>A: session_start()
  A-->>B: login画面HTML
```

## 2. ログイン処理
- 対象: `PHP/login.php`
- 参照: `login_tbl`

```mermaid
sequenceDiagram
  autonumber
  participant U as User
  participant B as Browser
  participant P as PHP(login.php)
  participant DB as MariaDB(login_tbl)

  U->>B: 団体ID/ユーザーID/パスワード入力
  B->>P: POST /PHP/login.php (send)
  P->>P: 入力バリデーション（空チェック）

  alt バリデーションOK
    P->>DB: SELECT * FROM login_tbl WHERE group_id=? AND user_id=?
    DB-->>P: user row (or none)

    alt ユーザーが存在
      P->>P: password_verify(入力, row.password)

      alt パスワード一致
        P->>P: session_regenerate_id(true)
        P->>P: $_SESSION に user情報/権限を格納
        P->>B: Cookie保存（remember_meがONの場合）
        alt admin/super admin
          P-->>B: 302 Location: admin.php?tab_id=...
        else 一般
          P-->>B: 302 Location: home.php?tab_id=...
        end
      else 不一致
        P->>P: エラー追加 + sleep(1)
        P-->>B: login画面HTML（エラー表示）
      end

    else 存在しない
      P->>P: エラー追加 + sleep(1)
      P-->>B: login画面HTML（エラー表示）
    end

  else バリデーションNG
    P-->>B: login画面HTML（エラー表示）
  end
```

## 3. 新規登録処理
- 対象: `PHP/reg.php`
- 更新: `login_tbl`
- 追加（任意）: `admin_role_requests`（管理者権限申請テーブルが存在する場合）

```mermaid
sequenceDiagram
  autonumber
  participant U as User
  participant B as Browser
  participant P as PHP(reg.php)
  participant DB as MariaDB

  U->>B: 新規登録フォーム入力
  B->>P: POST /PHP/reg.php (reg)

  P->>P: 入力バリデーション
  P->>DB: SELECT user_id FROM login_tbl WHERE user_id=? (重複確認)
  DB-->>P: exists? 

  alt バリデーションOK & 重複なし
    P->>P: password_hash()
    P->>DB: INSERT INTO login_tbl (...)
    DB-->>P: OK

    alt wants_admin=1 かつ is_admin=0
      P->>DB: admin_role_requests の存在確認
      DB-->>P: exists? 
      alt テーブルあり
        P->>DB: pending重複確認
        DB-->>P: exists? 
        alt pendingなし
          P->>DB: INSERT admin_role_requests (pending)
          DB-->>P: OK
        end
      end
    end

    alt Ajax
      P-->>B: 200 JSON {success:true, redirect:"login.php"}
    else 通常
      P-->>B: 302 Location: login.php
    end

  else 失敗
    alt Ajax
      P-->>B: 200 JSON {success:false, errors:[...]}
    else 通常
      P-->>B: 登録画面HTML（エラー表示）
    end
  end
```

## 4. 画面遷移メモ（報告書向け）
- login.html.php から reg.php への導線は `tab_id` を引き継ぐ。
- reg.html.php の「ログインに戻る」は tab_id を明示的に引き継がないが、login.php 側で tab_id が無い場合は自動付与・リダイレクトされる。
