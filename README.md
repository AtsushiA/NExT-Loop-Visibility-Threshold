# NExT Loop Visibility Threshold

Query Loop ブロック内の投稿総数が指定した閾値以上の場合にのみ、インナーブロックを表示する WordPress プラグインです。

---

## 機能

- **条件付き表示**: Query Loop の投稿総数が閾値以上 → インナーブロックを表示 / 未満 → 非表示
- **ブロック個別設定**: ブロックごとにサイドバーから閾値を設定できる
- **サイト共通設定**: 管理画面からデフォルト閾値を設定できる
- **ビルド不要**: JavaScript のビルドステップなしで動作

---

## 動作要件

| 項目 | バージョン |
|------|-----------|
| WordPress | 6.1 以上 |
| PHP | 7.4 以上 |

---

## インストール

1. プラグインフォルダを `/wp-content/plugins/NExT-Loop-Visibility-Threshold/` に配置する
2. WordPress 管理画面の「プラグイン」から「NExT Loop Visibility Threshold」を有効化する

---

## 使い方

### 基本手順

1. エディターで **Query Loop** ブロックを配置する
2. Query Loop 内（Post Template 内）に **「Loop Visibility Threshold」** ブロックを追加する
3. ブロックのインナーブロックに表示したいコンテンツを追加する
4. 必要に応じてサイドバーから閾値を設定する

### 閾値の設定

ブロックのサイドバー「表示閾値設定」→「最低投稿数」に数値を入力します。

| 設定値 | 動作 |
|--------|------|
| `0` | サイト設定のデフォルト値を使用 |
| `1` 以上 | 入力した数以上の投稿がある場合に表示 |

### 動作例

閾値を `3` に設定した場合：

| 実際の投稿数 | 表示 |
|------------|------|
| 1 | 非表示 |
| 2 | 非表示 |
| 3 | 表示 |
| 10 | 表示 |

---

## 管理画面設定

`設定 > Loop Visibility Threshold` からサイト全体のデフォルト閾値を設定できます。

ブロック個別の閾値が `0` の場合にこのデフォルト値が使われます。

---

## ファイル構成

```
NExT-Loop-Visibility-Threshold/
├── next-loop-visibility-threshold.php  # メインプラグインファイル
├── block.json                          # ブロックメタデータ
├── assets/
│   ├── editor.js                       # エディタースクリプト
│   └── style.css                       # フロントエンドスタイル
└── includes/
    ├── class-nlvt-settings.php         # 管理画面設定ページ
    └── class-nlvt-block.php            # ブロック登録・レンダリング
```

---

## ライセンス

GPL-2.0-or-later — https://www.gnu.org/licenses/gpl-2.0.html

---

## 変更履歴

### 1.1.0

- セキュリティ: フォールバック関数 `build_query_args_from_context()` の入力値検証を強化
  - `post_type` を `post_type_exists()` で登録済みタイプのみ許可するよう修正
  - `order` を `ASC` / `DESC` のホワイトリストで検証するよう修正
  - `orderby` を WP_Query の許可値ホワイトリストで検証するよう修正

### 1.0.0

- 初回リリース
