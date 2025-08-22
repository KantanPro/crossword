=== Crossword Game ===
Contributors: yourname
Tags: game, puzzle, crossword, interactive, entertainment
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

インタラクティブなクロスワードゲームを提供するWordPressプラグインです。

== Description ==

クロスワードゲームプラグインは、WordPressサイトに楽しいクロスワードパズルを追加できるプラグインです。

**主な機能:**

* 🎯 インタラクティブなクロスワードゲーム
* 📱 レスポンシブデザインでモバイル対応
* ⏱️ タイマー機能
* 💾 進捗の自動保存
* 🎨 美しいモダンなUI
* 🔧 簡単な管理画面
* 📝 ショートコードでの簡単埋め込み

**ゲーム機能:**
- 単語入力と検証
- ヒント表示
- 完成度チェック
- キーボードナビゲーション
- 進捗保存

**管理機能:**
- パズルの作成・編集・削除
- 難易度設定
- 単語とヒントの管理
- 統計情報の表示

== Installation ==

1. プラグインファイルを `/wp-content/plugins/crossword` フォルダにアップロードします
2. WordPress管理画面の「プラグイン」メニューでプラグインを有効化します
3. 管理画面の「クロスワード」メニューからパズルを作成します
4. ページや投稿に `[crossword]` ショートコードを追加します

== Frequently Asked Questions ==

= ショートコードの使い方は？ =

基本的な使い方:
`[crossword]`

特定のパズルを表示:
`[crossword id="1"]`

難易度を指定:
`[crossword difficulty="easy"]`

= パズルはどこで作成できますか？ =

WordPress管理画面の「クロスワード」メニューから作成できます。

= モバイルでも動作しますか？ =

はい、レスポンシブデザインでモバイルデバイスにも対応しています。

= 進捗は保存されますか？ =

ログインユーザーの進捗は自動的に保存されます。

= カスタマイズは可能ですか？ =

CSSとJavaScriptファイルを編集することで、見た目や動作をカスタマイズできます。

== Screenshots ==

1. フロントエンドのゲーム画面
2. 管理画面のパズル一覧
3. 新規パズル作成画面
4. グリッドエディター

== Changelog ==

= 1.0.0 =
* 初回リリース
* 基本的なクロスワードゲーム機能
* 管理画面でのパズル管理
* ショートコード対応
* レスポンシブデザイン

== Upgrade Notice ==

= 1.0.0 =
初回リリースです。

== Usage ==

**ショートコードの使用例:**

```
// 基本的な使い方
[crossword]

// 特定のパズルを表示
[crossword id="1"]

// 難易度を指定
[crossword difficulty="hard"]

// 複数のパラメータを組み合わせ
[crossword id="2" difficulty="medium"]
```

**管理画面での使用方法:**

1. WordPress管理画面にログイン
2. 左メニューの「クロスワード」をクリック
3. 「新規パズル作成」をクリック
4. パズルのタイトル、説明、難易度を入力
5. グリッドサイズを設定
6. グリッドエディターで単語を配置
7. 単語とヒントを追加
8. 「パズルを保存」をクリック

**カスタマイズ:**

プラグインのスタイルをカスタマイズするには、以下のファイルを編集してください:

- `assets/css/crossword-style.css` - フロントエンドのスタイル
- `assets/css/crossword-admin.css` - 管理画面のスタイル

**テーマとの統合:**

テーマの `functions.php` に以下を追加することで、プラグインのスタイルを無効化できます:

```php
function dequeue_crossword_styles() {
    wp_dequeue_style('crossword-style');
    wp_dequeue_style('crossword-admin-style');
}
add_action('wp_enqueue_scripts', 'dequeue_crossword_styles', 20);
add_action('admin_enqueue_scripts', 'dequeue_crossword_styles', 20);
```

== Support ==

サポートが必要な場合は、以下の方法でお問い合わせください:

* GitHub Issues: [プラグインのリポジトリ]
* メール: support@example.com
* ドキュメント: [プラグインのドキュメントサイト]

== Credits ==

このプラグインは以下の技術を使用しています:

* WordPress Plugin API
* jQuery
* CSS Grid
* AJAX

== License ==

このプラグインはGPL v2以降のライセンスの下で提供されています。

詳細は [GPL v2](https://www.gnu.org/licenses/gpl-2.0.html) または [GPL v3](https://www.gnu.org/licenses/gpl-3.0.html) をご覧ください。
