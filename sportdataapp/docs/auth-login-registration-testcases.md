# ログイン・新規登録 試験項目（テスト仕様書）

更新日: 2026-02-25

## 1. 目的
ログイン・新規登録機能の妥当性（正常系/異常系）と、権限/セッション/互換（列の有無）を確認する。

## 2. 前提
- DB投入: `sportdataapp/db/sportsdata.sql`
- 対象URL
  - ログイン: `/sportdataapp/PHP/login.php?tab_id=testtab01`
  - 新規登録: `/sportdataapp/PHP/reg.php?tab_id=testtab01`

## 3. テストデータ例
- 既存ユーザー（例）
  - group_id=`system`, user_id=`host`（super admin）
  - group_id=`cis`, user_id=`mainte`（admin）

## 4. 試験項目一覧

### 4.1 ログイン（正常系）
| ID | 観点 | 手順 | 期待結果 |
|---|---|---|---|
| L-01 | 一般ユーザーでログイン | 正しい group_id/user_id/password で送信 | `home.php` に遷移し、`$_SESSION[group_id,user_id,...]` が設定される |
| L-02 | 管理者でログイン | is_admin=1 のユーザーでログイン | `admin.php` に遷移する |
| L-03 | スーパー管理者でログイン | is_super_admin=1 のユーザーでログイン | `admin.php` に遷移する |
| L-04 | remember_me ON | remember_me をONでログイン | Cookie `saved_group_id`,`saved_user_id` が30日で設定される |
| L-05 | remember_me OFF | remember_me OFF でログイン | 上記Cookieが削除される |

### 4.2 ログイン（異常系）
| ID | 観点 | 手順 | 期待結果 |
|---|---|---|---|
| L-11 | 必須未入力（団体ID） | group_id 空で送信 | エラー表示「団体IDを入力してください」 |
| L-12 | 必須未入力（ユーザーID） | user_id 空で送信 | エラー表示「ユーザーIDを入力してください」 |
| L-13 | 必須未入力（パスワード） | password 空で送信 | エラー表示「パスワードを入力してください」 |
| L-14 | ユーザー不一致 | 存在しない group_id/user_id で送信 | エラー表示「団体IDまたはユーザーIDが正しくありません」 |
| L-15 | パスワード不一致 | 正しい group/user + 誤パスで送信 | エラー表示「パスワードが正しくありません」 |

### 4.3 新規登録（正常系）
| ID | 観点 | 手順 | 期待結果 |
|---|---|---|---|
| R-01 | 一般ユーザー登録 | 必須を満たして登録 | login_tbl に1行追加、login.phpへ遷移 |
| R-02 | sport列ありの登録 | sport を選択して登録 | login_tbl.sport に値が保存される |
| R-03 | wants_admin 申請 | wants_admin=ON で登録 | admin_role_requests に pending が作成される（テーブルがある場合） |
| R-04 | Ajax登録成功 | Ajaxヘッダ付きで登録 | JSONで success=true / redirect=login.php を返す |

### 4.4 新規登録（異常系）
| ID | 観点 | 手順 | 期待結果 |
|---|---|---|---|
| R-11 | user_id短すぎ | user_id 3文字で登録 | エラー「ユーザーIDは4文字以上」 |
| R-12 | user_id重複 | 既存の user_id を指定 | エラー「このユーザーIDは既に使用されています」 |
| R-13 | password短すぎ | 5文字で登録 | エラー「パスワードは6文字以上」 |
| R-14 | password不一致 | password と confirm 不一致 | エラー「パスワードが一致しません」 |
| R-15 | 身体情報未入力（一般） | wants_admin/is_admin OFF で dob/height/weight 空 | エラー（生年月日/身長/体重） |
| R-16 | height/weight不正 | 0 または負数で登録 | エラー「正しい身長/体重」 |
| R-17 | sport不正値 | sport=hack を送信 | エラー「種目の値が不正です」（sport列あり時） |
| R-18 | AjaxでバリデーションNG | Ajax送信で必須不足 | JSON success=false / errors[] を返す |

### 4.5 互換（DB列/テーブルの有無）
| ID | 観点 | 手順 | 期待結果 |
|---|---|---|---|
| C-01 | sport列なし環境 | login_tbl から sport 列を外したDBで登録 | sport は必須にならず、INSERT文がsport無しで実行される |
| C-02 | is_admin列なし環境 | login_tbl から is_admin を外したDBで登録 | INSERT文が is_admin 無しで実行される（管理者登録のチェックボックスは無効） |
| C-03 | admin_role_requests無し | テーブル無しで wants_admin=ON | 登録自体は成功し、申請は作成されない（エラーで落ちない） |

### 4.6 セッション/タブ分離
| ID | 観点 | 手順 | 期待結果 |
|---|---|---|---|
| S-01 | tab_id無しアクセス | /login.php へ tab_id無しでアクセス | 302 で tab_id 付与URLへリダイレクト |
| S-02 | タブごと別ユーザー | tab_id を変えて2タブでログイン | 互いのログイン状態が混ざらない |

### 4.7 セキュリティ簡易観点
| ID | 観点 | 手順 | 期待結果 |
|---|---|---|---|
| SEC-01 | SQLインジェクション | group_id/user_id に `"' OR 1=1 --` を試す | 失敗する（プリペアドステートメントのため） |
| SEC-02 | パスワード平文保存なし | 登録後のDBを確認 | login_tbl.password がハッシュ（`$2y$...`）になっている |
| SEC-03 | セッション再生成 | ログイン成功後にセッションID確認 | session_regenerate_id が効いてIDが更新される |

---

## 5. 実施記録欄（提出用）
- 実施日:
- 実施者:
- 環境（OS/ブラウザ/PHP/DB）:
- 結果（OK/NG）:
- NG詳細・スクリーンショット:
