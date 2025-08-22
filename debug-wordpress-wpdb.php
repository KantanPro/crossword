<?php
/**
 * WordPress環境でのwpdbを使用したデータ取得テスト
 * このファイルをWordPressのルートディレクトリに配置して実行してください
 */

// WordPress環境を読み込み
require_once('wp-load.php');

echo "<h1>WordPress環境でのwpdbデータ取得テスト</h1>";

global $wpdb;

$table_puzzles = $wpdb->prefix . 'crossword_puzzles';

echo "<h2>1. テーブル情報の確認</h2>";

// テーブルの存在確認
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_puzzles'") == $table_puzzles;
echo "<p><strong>テーブルの存在:</strong> " . ($table_exists ? '✓ 存在' : '✗ 存在しない') . "</p>";

if (!$table_exists) {
    echo "<p style='color: red;'>テーブルが存在しません。プラグインを再有効化してください。</p>";
    exit;
}

// テーブルの文字セット確認
$table_info = $wpdb->get_row("SHOW CREATE TABLE $table_puzzles");
echo "<p><strong>テーブル作成情報:</strong></p>";
echo "<pre>" . htmlspecialchars($table_info->{'Create Table'}) . "</pre>";

echo "<h2>2. パズルID 2の詳細テスト</h2>";

// パズルID 2の取得（wpdbを使用）
$puzzle = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $table_puzzles WHERE id = %d",
        2
    )
);

if ($puzzle) {
    echo "<p style='color: green;'>✓ パズルID 2が見つかりました</p>";
    echo "<p><strong>ID:</strong> " . $puzzle->id . "</p>";
    echo "<p><strong>タイトル:</strong> " . htmlspecialchars($puzzle->title) . "</p>";
    echo "<p><strong>作成日:</strong> " . $puzzle->created_at . "</p>";
    
    // 生データの確認
    echo "<h3>生データの確認</h3>";
    echo "<p><strong>grid_data:</strong></p>";
    echo "<ul>";
    echo "<li>長さ: " . strlen($puzzle->grid_data) . " バイト</li>";
    echo "<li>タイプ: " . gettype($puzzle->grid_data) . "</li>";
    echo "<li>最初の100文字: " . htmlspecialchars(substr($puzzle->grid_data, 0, 100)) . "</li>";
    echo "</ul>";
    
    echo "<p><strong>words_data:</strong></p>";
    echo "<ul>";
    echo "<li>長さ: " . strlen($puzzle->words_data) . " バイト</li>";
    echo "<li>タイプ: " . gettype($puzzle->words_data) . "</li>";
    echo "<li>最初の100文字: " . htmlspecialchars(substr($puzzle->words_data, 0, 100)) . "</li>";
    echo "</ul>";
    
    echo "<p><strong>hints_data:</strong></p>";
    echo "<ul>";
    echo "<li>長さ: " . strlen($puzzle->hints_data) . " バイト</li>";
    echo "<li>タイプ: " . gettype($puzzle->hints_data) . "</li>";
    echo "<li>最初の100文字: " . htmlspecialchars(substr($puzzle->hints_data, 0, 100)) . "</li>";
    echo "</ul>";
    
    // JSONデコードテスト
    echo "<h3>JSONデコードテスト</h3>";
    
    // json_last_error()をリセット
    json_encode([]);
    
    $grid_data = json_decode($puzzle->grid_data, true);
    $grid_error = json_last_error();
    
    json_encode([]);
    $words_data = json_decode($puzzle->words_data, true);
    $words_error = json_last_error();
    
    json_encode([]);
    $hints_data = json_decode($puzzle->hints_data, true);
    $hints_error = json_last_error();
    
    echo "<p><strong>JSON解析結果:</strong></p>";
    echo "<ul>";
    echo "<li>grid_data: " . ($grid_data !== null ? '✓ 成功' : '✗ 失敗 (エラー: ' . json_last_error_msg() . ')') . "</li>";
    echo "<li>words_data: " . ($words_data !== null ? '✓ 成功' : '✗ 失敗 (エラー: ' . json_last_error_msg() . ')') . "</li>";
    echo "<li>hints_data: " . ($hints_data !== null ? '✓ 成功' : '✗ 失敗 (エラー: ' . json_last_error_msg() . ')') . "</li>";
    echo "</ul>";
    
    // エラーの詳細
    if ($grid_data === null) {
        echo "<p><strong>grid_data JSONエラー詳細:</strong></p>";
        echo "<ul>";
        echo "<li>エラーコード: " . $grid_error . "</li>";
        echo "<li>エラーメッセージ: " . json_last_error_msg() . "</li>";
        echo "<li>文字エンコーディング: " . mb_detect_encoding($puzzle->grid_data) . "</li>";
        echo "</ul>";
        
        // バイナリダンプ
        echo "<p><strong>バイナリダンプ（最初の50バイト）:</strong></p>";
        echo "<pre>";
        for ($i = 0; $i < min(50, strlen($puzzle->grid_data)); $i++) {
            printf("%02x ", ord($puzzle->grid_data[$i]));
            if (($i + 1) % 16 == 0) echo "\n";
        }
        echo "</pre>";
    }
    
    if ($words_data === null) {
        echo "<p><strong>words_data JSONエラー詳細:</strong></p>";
        echo "<ul>";
        echo "<li>エラーコード: " . $words_error . "</li>";
        echo "<li>エラーメッセージ: " . json_last_error_msg() . "</li>";
        echo "<li>文字エンコーディング: " . mb_detect_encoding($puzzle->words_data) . "</li>";
        echo "</ul>";
    }
    
    if ($hints_data === null) {
        echo "<p><strong>hints_data JSONエラー詳細:</strong></p>";
        echo "<ul>";
        echo "<li>エラーコード: " . $hints_error . "</li>";
        echo "<li>エラーメッセージ: " . json_last_error_msg() . "</li>";
        echo "<li>文字エンコーディング: " . mb_detect_encoding($puzzle->hints_data) . "</li>";
        echo "</ul>";
    }
    
} else {
    echo "<p style='color: red;'>✗ パズルID 2が見つかりません</p>";
    echo "<p><strong>wpdbエラー:</strong> " . $wpdb->last_error . "</p>";
}

echo "<h2>3. デフォルトパズルの取得テスト</h2>";

// デフォルトパズルの取得
$default_puzzle = $wpdb->get_row(
    "SELECT * FROM $table_puzzles ORDER BY id ASC LIMIT 1"
);

if ($default_puzzle) {
    echo "<p style='color: green;'>✓ デフォルトパズルが見つかりました</p>";
    echo "<p><strong>ID:</strong> " . $default_puzzle->id . "</p>";
    echo "<p><strong>タイトル:</strong> " . htmlspecialchars($default_puzzle->title) . "</p>";
} else {
    echo "<p style='color: red;'>✗ デフォルトパズルが見つかりません</p>";
    echo "<p><strong>wpdbエラー:</strong> " . $wpdb->last_error . "</p>";
}

echo "<h2>4. データベース接続情報</h2>";
echo "<ul>";
echo "<li><strong>データベース名:</strong> " . DB_NAME . "</li>";
echo "<li><strong>ホスト:</strong> " . DB_HOST . "</li>";
echo "<li><strong>文字セット:</strong> " . DB_CHARSET . "</li>";
echo "<li><strong>照合順序:</strong> " . DB_COLLATE . "</li>";
echo "</ul>";

echo "<h2>5. 推奨される次のステップ</h2>";
echo "<ol>";
echo "<li>JSONエラーの詳細を確認し、データの問題を特定する</li>";
echo "<li>必要に応じて、パズルデータを再作成する</li>";
echo "<li>WordPress環境でのエンコーディング設定を確認する</li>";
echo "</ol>";
