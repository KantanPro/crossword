=== Japanese Crossword Generator ===
Contributors: KantanPro
Tags: crossword, puzzle, japanese, game, entertainment
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: yourusername/jp-crossword

日本語のクロスワード（文字埋め）を自動生成して表示するプラグイン。ショートコードは [crossword] 。「新規問題」と「ギブアップ（答えを表示）」ボタンをフロントに設置。

== Description ==

日本語のクロスワードパズルを自動生成して表示するWordPressプラグインです。

**主な機能：**
* 日本語のクロスワードパズルを自動生成
* ショートコード `[crossword]` で簡単に表示
* 「新規問題」ボタンで新しいパズルを生成
* 「ギブアップ（答えを表示）」ボタンで解答を確認
* レスポンシブデザインでモバイル対応
* 管理画面での設定調整
* GitHub自動更新機能

**使用方法：**
1. プラグインを有効化
2. 投稿や固定ページに `[crossword]` ショートコードを挿入
3. フロントエンドでクロスワードパズルが表示されます

**ショートコードオプション：**
* `[crossword]` - デフォルトサイズ（10x10）で表示
* `[crossword size="12"]` - 12x12のサイズで表示
* `[crossword id="my-puzzle"]` - カスタムIDを設定

**管理画面設定：**
* 設定 > Japanese Crossword でパズルのサイズ範囲を調整
* GitHub設定でPersonal Access Tokenを設定可能

== Installation ==

1. プラグインファイルを `/wp-content/plugins/jp-crossword/` ディレクトリにアップロード
2. WordPressの管理画面でプラグインを有効化
3. 投稿や固定ページに `[crossword]` ショートコードを挿入

== Frequently Asked Questions ==

= パズルのサイズは変更できますか？ =

はい、ショートコードで `[crossword size="12"]` のように指定できます。また、管理画面で最小・最大サイズを設定できます。

= 日本語以外の言語は対応していますか？ =

現在は日本語のみ対応しています。将来的に他の言語への対応を検討しています。

= パズルの難易度は調整できますか？ =

現在は基本的な難易度のみですが、今後のバージョンで難易度調整機能を追加する予定です。

= カスタムの単語やヒントを追加できますか？ =

現在は組み込みの辞書を使用していますが、フィルターフックで拡張可能です。

== Screenshots ==

1. フロントエンドでのクロスワードパズル表示
2. 管理画面での設定画面
3. モバイルでの表示例

== Changelog ==

= 1.0.1 =
* 微調整

= 1.0.0 =
* 初回リリース
* 日本語クロスワードパズルの自動生成
* ショートコード対応
* 管理画面設定
* GitHub自動更新機能

== Upgrade Notice ==

= 1.0.1 =
微調整を行った安定版です。既存の機能に影響はありません。
