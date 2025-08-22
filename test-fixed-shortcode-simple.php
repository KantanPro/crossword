<?php
/**
 * 修正後のショートコードの簡単なテスト
 */

echo "<h1>修正後のクロスワードショートコードのテスト</h1>";

echo "<h2>修正内容</h2>";
echo "<ul>";
echo "<li>✓ ショートコード処理の修正: empty() → !isset() || === ''</li>";
echo "<li>✓ デバッグログの追加</li>";
echo "<li>✓ 空のセルにも入力フィールドを追加</li>";
echo "</ul>";

echo "<h2>テスト手順</h2>";
echo "<ol>";
echo "<li>WordPressの管理画面で新しいページを作成</li>";
echo "<li>以下のショートコードを記述：</li>";
echo "<ul>";
echo "<li><code>[crossword id=\"1\"]</code></li>";
echo "<li><code>[crossword id=\"2\"]</code></li>";
echo "<li><code>[crossword]</code></li>";
echo "</ul>";
echo "<li>各ショートコードが正しく表示されることを確認</li>";
echo "</ol>";

echo "<h2>期待される結果</h2>";
echo "<ul>";
echo "<li><strong>[crossword id=\"1\"]</strong> → パズルID 1のクロスワードが表示される</li>";
echo "<li><strong>[crossword id=\"2\"]</strong> → パズルID 2のクロスワードが表示される</li>";
echo "<li><strong>[crossword]</strong> → デフォルトパズル（ID 1）が表示される</li>";
echo "</ul>";

echo "<h2>デバッグ情報</h2>";
echo "<p>修正により、WordPressのエラーログに詳細なデバッグ情報が出力されるようになりました。</p>";
echo "<p>エラーが発生した場合は、WordPressの管理画面 → ツール → サイトヘルス → ログ で確認してください。</p>";

echo "<h2>注意事項</h2>";
echo "<div style='background: #fff3cd; padding: 10px; border-radius: 4px; border-left: 4px solid #ffc107;'>";
echo "<strong>重要:</strong> このHTMLファイルはテスト用です。実際のWordPressサイトでは、ショートコードを直接ページに記述してください。";
echo "</div>";
