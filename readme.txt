=== Japanese Crossword Generator ===
Contributors: yourname
Tags: crossword, puzzle, japanese, game, entertainment
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

日本語のクロスワード（文字埋め）を自動生成して表示するWordPressプラグインです。

== Description ==

Japanese Crossword Generatorは、日本語のクロスワードパズルを自動生成し、WordPressサイトに表示するプラグインです。

**主な機能：**
* 日本語のクロスワードパズルの自動生成
* ショートコード `[crossword]` での簡単表示
* 新規問題生成ボタン
* ギブアップ（答え表示）機能
* レスポンシブデザイン対応
* 管理画面での設定

**使用方法：**
1. プラグインを有効化
2. 投稿や固定ページに `[crossword]` ショートコードを挿入
3. オプションでサイズを指定可能：`[crossword size="12"]`

**対応サイズ：**
* 最小：6×6
* 最大：16×16
* デフォルト：10×10

== Installation ==

1. プラグインファイルを `/wp-content/plugins/jp-crossword/` ディレクトリにアップロード
2. WordPress管理画面の「プラグイン」メニューでプラグインを有効化
3. 投稿や固定ページに `[crossword]` ショートコードを挿入

== Frequently Asked Questions ==

= どのような単語が使用されますか？ =
プラグイン内蔵の日本語辞書から、2〜8文字の単語が自動選択されます。将来的にはカスタム辞書の追加も可能です。

= パズルの難易度は調整できますか？ =
現在は基本的な難易度のみですが、今後のバージョンで難易度調整機能を追加予定です。

= モバイル対応していますか？ =
はい、レスポンシブデザインでモバイルデバイスにも対応しています。

== Screenshots ==

1. フロントエンドでのクロスワード表示
2. 管理画面での設定

== Changelog ==

= 1.0.0 =
* ファーストリリース
* 日本語クロスワードパズルの自動生成機能
* ショートコード表示機能
* 新規問題生成とギブアップ機能
* 管理画面での基本設定
* レスポンシブデザイン対応

== Upgrade Notice ==

= 1.0.0 =
初回リリースです。WordPress 5.0以上、PHP 7.4以上で動作します。
