# コードレビューチェックリスト - coachtech 勤怠管理アプリ

## 1. コード品質

### 1.1 フォーマット・構造
- [x] インデント・改行が整理されているか
- [x] HTMLタグが適切
- [x] 不要なコメントアウトが残っていないか
- [x] 未使用のuse文がないか

### 1.2 命名規則
- [x] id/class名が英単語で意味が伝わる命名になっているか
- [x] 変数名が適切か
- [x] ファイル名規約の遵守

### 1.3 テーブル名・モデル名・コントローラー名の設計書整合性（Must）
- [x] テーブル名・モデル名・コントローラー名が設計書と一致

### 1.4 URL設計の設計書整合性（Must）
- [x] 全ルートのURL・HTTPメソッドが設計書通り

### 1.5 使用技術の遵守
- [x] Laravel Fortify, FormRequest, Eloquent ORM を適切に使用

---

## 2. 機能要件チェック（全51機能）
- [x] FN001-FN017: 認証系（会員登録・ログイン・メール認証・ログアウト・管理者ログイン）
- [x] FN018-FN022: 打刻系（出勤・退勤・休憩・ステータス管理）
- [x] FN023-FN033: 一般ユーザー勤怠（一覧・詳細・修正申請・バリデーション）
- [x] FN034-FN051: 管理者機能（勤怠管理・スタッフ管理・修正申請管理・CSV）

---

## 3. バリデーションメッセージ（完全一致チェック）
- [x] 全10メッセージが完全一致で実装されている

---

## 4. セキュリティ

### 4.1 基本セキュリティ
- [x] SQLインジェクション・XSS・CSRF・認証認可・マスアサインメント対策

### 4.2 認証方式の設計書整合性（Must）
- [x] 単一ガード + roleベースミドルウェア

### 4.3 セッション管理（Must）
- [x] AdminAuthController: logout後にsession invalidate + regenerateToken

### 4.4 トランザクション管理（Must）
- [x] AdminAttendanceController::update: DB::transaction適用済み
- [x] AdminStampCorrectionRequestController::approve: DB::transaction適用済み

---

## 5. テスト
- [x] 16テストファイル、76テストケース
- [x] FNカバレッジ 98%（51中50）
- [x] バリデーションメッセージ 10/10 全カバー
- [x] 全テストパス

---

## 6. パフォーマンス
- [x] N+1クエリなし（eager loading適用済み）
- [x] ページネーション適切

### 残存Should
- [ ] 6.1: 日跨ぎ勤務の時間比較（文字列比較）
- [ ] 6.2: StampCorrectionFormRequest withValidator の日跨ぎ対応

---

## 7. インフラ・環境
- [x] Docker, MySQL, .env.example, README 完備

---

## 8. レイアウト（Should）
- [ ] PC (1400-1540px) 実機確認未実施

---

## 9. ミドルウェア・ルーティング

### 9.1 ミドルウェアのリダイレクト先（Must）
- [x] UserMiddleware: 管理者は /admin/attendance/list にリダイレクト

### 残存Should/Nice to have
- [ ] 9.2: 管理者ログアウトのクロージャルート（AdminAuthControllerへの移動推奨）
- [ ] 9.3: tabパラメータのバリデーション不足
- [ ] 9.4: whereIn を where に簡略化

---

## 残存 Should / Nice to have（PASS条件外）

| ID | 内容 | 影響度 |
|---|---|---|
| S-4 | show.blade.php: $attendance->reason がDBに存在しないカラム参照 | 低（null合体演算子で回避） |
| S-5/TL-S4 | 管理者ログアウトのクロージャルート | 低（動作に問題なし） |
| TL-S1 | exportCsv clock_in nullチェック | 低（NOT NULL制約あり） |
| TL-S2 | 時間比較の文字列比較（日跨ぎ） | 中（日跨ぎ勤務で誤動作の可能性） |
| TL-S3 | tabパラメータバリデーション不足 | 低（不正値でもエラーにならない） |
| TL-N1 | whereIn を where に簡略化 | 極低（動作に影響なし） |

---

## レビュー結果サマリ

### 総合判定: PASS

| カテゴリ | 判定 | 備考 |
|---------|------|------|
| コード品質 | PASS | 命名規則・設計書整合性OK |
| 設計書整合性 | PASS | tech-lead承認済み |
| 機能要件 | PASS | FN001-FN051 全機能カバー |
| バリデーション | PASS | 10メッセージ完全一致 |
| セキュリティ | PASS | セッション管理・トランザクション対応済み |
| テスト | PASS | 76テスト、FNカバレッジ98% |
| パフォーマンス | PASS | N+1なし |
| インフラ | PASS | Docker・MySQL・README完備 |
| ミドルウェア | PASS | リダイレクト先修正済み |

### レビュー履歴
| 回数 | 日付 | 判定 | 備考 |
|------|------|------|------|
| 1 | 2026-02-09 | FAIL | Must5件, Should6件, Nice2件 |
| 2 | 2026-02-09 | FAIL | Must1件（TL-M2 DBトランザクション）未修正 |
| 3 | 2026-02-09 | PASS | 全Must解消。Should6件/Nice1件は残存（PASS条件外） |
