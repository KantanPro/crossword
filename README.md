# Japanese Crossword Generator

日本語のクロスワード（文字埋め）を自動生成して表示するWordPressプラグイン。

## 機能

- 日本語クロスワードパズルの自動生成
- ショートコード `[crossword]` で簡単表示
- 「新規問題」と「ギブアップ（答えを表示）」ボタン
- 管理画面での設定
- **GitHubリリース対応の自動更新機能**

## GitHubリリース対応の自動更新

このプラグインは、GitHubリリースに対応した自動更新機能を備えています。

### セットアップ手順

1. **GitHubリポジトリの設定**
   - プラグインファイル `jp-crossword.php` の `JPCW_GITHUB_REPO` 定数を実際のリポジトリ名に変更
   - 例: `define( 'JPCW_GITHUB_REPO', 'KantanPro/crossword' );`

2. **GitHub Personal Access Tokenの設定（推奨）**
   - 管理画面 → 設定 → Crossword GitHub でトークンを設定
   - GitHub APIのレート制限を回避できます
   - [GitHubでトークンを生成](https://github.com/settings/tokens)

3. **リリースの作成**
   - GitHubで新しいリリースを作成
   - タグ名をバージョン番号で設定（例: `v1.0.1`）
   - リリースノートを記入

### 更新の流れ

1. WordPressの管理画面でプラグイン一覧を表示
2. 新しいバージョンがある場合、更新通知が表示される
3. 「今すぐ更新」ボタンでワンクリック更新
4. GitHubの最新リリースが自動的にダウンロード・インストールされる

### 注意事項

- プラグインの更新は、WordPressの標準的な更新システムを使用
- セキュリティと安定性を確保するため、本番環境での更新前には必ずバックアップを取得
- GitHub APIのレート制限に注意（トークン設定を推奨）

## インストール

1. プラグインファイルを `wp-content/plugins/jp-crossword/` ディレクトリにアップロード
2. WordPress管理画面でプラグインを有効化
3. 設定 → Japanese Crossword で設定を調整

## 使用方法

### ショートコード

```
[crossword size="10"]
```

### パラメータ

- `size`: パズルのサイズ（6-16の範囲、デフォルト: 10）
- `id`: カスタムID（オプション）

## 開発者向け

### フィルターフック

```php
// 辞書の拡張
add_filter( 'jpcw_dictionary', function( $words ) {
    $words['新しい単語'] = 'ヒント';
    return $words;
} );
```

### アクションフック

```php
// パズル生成後の処理
add_action( 'jpcw_puzzle_generated', function( $puzzle_data ) {
    // カスタム処理
} );
```

## ライセンス

GPL v2 またはそれ以降

## サポート

- GitHub Issues: [リポジトリのIssues](https://github.com/KantanPro/crossword/issues)
- 機能要望やバグ報告は歓迎します
