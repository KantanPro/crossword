<?php
/**
 * 実際のWordPress環境でのショートコードテスト
 * このファイルをWordPressのルートディレクトリに配置して実行してください
 */

// WordPress環境を読み込み
require_once('wp-load.php');

echo "<h1>クロスワードショートコードの実際の動作テスト</h1>";

// データベースの状況確認
global $wpdb;
$table_puzzles = $wpdb->prefix . 'crossword_puzzles';

echo "<h2>1. データベースの状況確認</h2>";

// テーブルの存在確認
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_puzzles'") == $table_puzzles;
echo "<p><strong>クロスワードテーブルの存在:</strong> " . ($table_exists ? '✓ 存在' : '✗ 存在しない') . "</p>";

if ($table_exists) {
    // パズルデータの確認
    $puzzles = $wpdb->get_results("SELECT id, title, created_at FROM $table_puzzles ORDER BY id");
    echo "<p><strong>登録されているパズル:</strong></p>";
    if ($puzzles) {
        echo "<ul>";
        foreach ($puzzles as $puzzle) {
            echo "<li>ID: {$puzzle->id} - {$puzzle->title} (作成日: {$puzzle->created_at})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>パズルデータがありません</p>";
    }
}

echo "<h2>2. ショートコードの動作テスト</h2>";

// ショートコードの登録確認
$shortcodes = $GLOBALS['shortcode_tags'];
if (isset($shortcodes['crossword'])) {
    echo "<p><strong>クロスワードショートコード:</strong> ✓ 登録済み</p>";
    echo "<p><strong>コールバック関数:</strong> " . get_class($shortcodes['crossword'][0]) . "</p>";
} else {
    echo "<p><strong>クロスワードショートコード:</strong> ✗ 未登録</p>";
}

echo "<h2>3. ショートコードの実行テスト</h2>";

// 各ショートコードの実行テスト
$test_cases = array(
    'id="1"' => '[crossword id="1"]',
    'id="2"' => '[crossword id="2"]',
    'no id' => '[crossword]',
);

foreach ($test_cases as $description => $shortcode) {
    echo "<h3>テストケース: $description</h3>";
    echo "<p><strong>ショートコード:</strong> <code>$shortcode</code></p>";
    
    try {
        $result = do_shortcode($shortcode);
        echo "<p><strong>実行結果:</strong></p>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0; background: #f9f9f9;'>";
        echo $result;
        echo "</div>";
        
        // 結果の分析
        if (strpos($result, 'crossword-error') !== false) {
            echo "<p style='color: red;'>✗ エラーが発生しました</p>";
        } elseif (strpos($result, 'crossword-game') !== false) {
            echo "<p style='color: green;'>✓ 正常に表示されました</p>";
        } else {
            echo "<p style='color: orange;'>? 予期しない結果です</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
    } catch (Error $e) {
        echo "<p style='color: red;'>✗ エラーが発生しました: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

echo "<h2>4. プラグインの状態確認</h2>";

// プラグインの有効化状態確認
$active_plugins = get_option('active_plugins');
$crossword_plugin = 'crossword/crossword.php';

if (in_array($crossword_plugin, $active_plugins)) {
    echo "<p><strong>クロスワードプラグイン:</strong> ✓ 有効化済み</p>";
} else {
    echo "<p><strong>クロスワードプラグイン:</strong> ✗ 無効化されています</p>";
}

// プラグインのバージョン確認
$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $crossword_plugin);
if ($plugin_data) {
    echo "<p><strong>プラグイン名:</strong> " . $plugin_data['Name'] . "</p>";
    echo "<p><strong>バージョン:</strong> " . $plugin_data['Version'] . "</p>";
    echo "<p><strong>説明:</strong> " . $plugin_data['Description'] . "</p>";
}

echo "<h2>5. 推奨される次のステップ</h2>";
echo "<ol>";
echo "<li>このテスト結果を確認し、問題があれば修正する</li>";
echo "<li>WordPressの管理画面で、新しいページまたは投稿を作成する</li>";
echo "<li>以下のショートコードを記述してテストする：</li>";
echo "<ul>";
echo "<li><code>[crossword id=\"1\"]</code></li>";
echo "<li><code>[crossword id=\"2\"]</code></li>";
echo "<li><code>[crossword]</code></li>";
echo "</ul>";
echo "<li>各ショートコードが正しく表示されることを確認する</li>";
echo "</ol>";

echo "<p><strong>注意:</strong> このスクリプトはWordPressのルートディレクトリに配置して実行してください。</p>";
