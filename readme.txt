=== Japanese Crossword Generator ===
Contributors: KantanPro
Tags: crossword, puzzle, japanese, game, entertainment
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: yourusername/jp-crossword

日本語のクロスワード（文字埋め）を自動生成して表示するWordPressプラグイン。GitHubリリース対応の自動更新機能付き。

== Description ==

**Japanese Crossword Generator**は、WordPressサイトに日本語のクロスワードパズルを簡単に追加できるプラグインです。

## 主な機能

* 🎯 **自動生成**: 日本語のクロスワードパズルを自動で生成
* 📱 **レスポンシブ**: モバイルデバイスにも対応したデザイン
* 🔄 **新規問題**: ボタン一つで新しいパズルを生成
* 💡 **ギブアップ**: 答えを見る機能
* ⚙️ **カスタマイズ**: 管理画面での設定
* 🎮 **ショートコード**: `[crossword]` で簡単表示
* 🚀 **GitHub自動更新**: GitHubリリースに対応した自動更新機能

## GitHubリリース対応の自動更新

このプラグインは、GitHubリリースに対応した自動更新機能を備えています。

### セットアップ手順

1. **GitHubリポジトリの設定**
   - プラグインファイルの `JPCW_GITHUB_REPO` 定数を実際のリポジトリ名に変更

2. **GitHub Personal Access Tokenの設定（推奨）**
   - 管理画面 → 設定 → Crossword GitHub でトークンを設定
   - GitHub APIのレート制限を回避できます

3. **リリースの作成**
   - GitHubで新しいリリースを作成
   - タグ名をバージョン番号で設定（例: `v1.0.1`）

### 更新の流れ

1. WordPressの管理画面でプラグイン一覧を表示
2. 新しいバージョンがある場合、更新通知が表示される
3. 「今すぐ更新」ボタンでワンクリック更新
4. GitHubの最新リリースが自動的にダウンロード・インストールされる

## インストール方法

### 1. プラグインのアップロード
プラグインファイルを `/wp-content/plugins/jp-crossword/` ディレクトリにアップロード

### 2. プラグインの有効化
WordPress管理画面の「プラグイン」メニューでプラグインを有効化

### 3. ショートコードの挿入
投稿や固定ページに `[crossword]` ショートコードを挿入

## 使用方法

### 基本的な使用方法
```
[crossword]
```

### サイズを指定する場合
```
[crossword size="12"]
```

### 対応サイズ
* **最小**: 6×6
* **最大**: 16×16
* **デフォルト**: 10×10

## よくある質問

**Q: どのような単語が使用されますか？**
A: プラグイン内蔵の日本語辞書から、2〜8文字の単語が自動選択されます。

**Q: パズルの難易度は調整できますか？**
A: 現在は基本的な難易度のみですが、今後のバージョンで難易度調整機能を追加予定です。

**Q: モバイル対応していますか？**
A: はい、レスポンシブデザインでモバイルデバイスにも対応しています。

**Q: GitHubからの自動更新は安全ですか？**
A: はい、WordPressの標準的な更新システムを使用し、セキュリティチェックも行われます。ただし、本番環境での更新前には必ずバックアップを取得することをお勧めします。

## 開発者向け情報

### フック・フィルター
* `jpcw_dictionary`: 辞書データのカスタマイズ

### カスタマイズ例
```php
// 辞書に単語を追加
add_filter('jpcw_dictionary', function($words) {
    $words['カスタム'] = 'カスタム単語のヒント';
    return $words;
});
```

## 変更履歴

### Version 1.0.0 - 2025年8月22日
* 🎉 ファーストリリース
* ✨ 日本語クロスワードパズルの自動生成機能
* ✨ ショートコード表示機能
* ✨ 新規問題生成とギブアップ機能
* ✨ 管理画面での基本設定
* ✨ レスポンシブデザイン対応
* 🚀 GitHubリリース対応の自動更新機能

## ライセンス

このプラグインは [GPL v2](http://www.gnu.org/licenses/gpl-2.0.html) またはそれ以降のバージョンでライセンスされています。

## サポート

プラグインに関するご質問やご要望がございましたら、GitHubのIssuesページでお知らせください。

* [GitHub Issues](https://github.com/KantanPro/crossword/issues)

## 今後の予定

* [ ] 難易度調整機能
* [ ] カスタム辞書の追加
* [ ] 統計・ランキング機能
* [ ] テーマカスタマイズ機能
* [ ] 多言語対応

---

**Japanese Crossword Generator** - WordPressで楽しむ日本語クロスワードパズル 🎯
