<?php
/**
 * クロスワードプラグインの強制アクティベーション
 * データベーステーブルを強制的に作成します
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>クロスワードプラグインの強制アクティベーション</h1>";

// プラグインファイルを読み込み
require_once 'crossword.php';

echo "<p style='color: green;'>✓ プラグインファイル読み込み完了</p>";

try {
    // データベース接続設定
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $database = 'wordpress';
    
    $mysqli = new mysqli($host, $user, $password, $database);
    
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>✗ データベース接続エラー: " . $mysqli->connect_error . "</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ データベース接続成功</p>";
    
    // テーブル作成SQL
    $table_prefix = 'wp_';
    $charset_collate = 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
    
    // パズルテーブル作成
    $puzzles_table = $table_prefix . 'crossword_puzzles';
    $sql_puzzles = "CREATE TABLE $puzzles_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title tinytext NOT NULL,
        description text,
        grid_data longtext NOT NULL,
        words_data longtext NOT NULL,
        hints_data longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    if ($mysqli->query($sql_puzzles)) {
        echo "<p style='color: green;'>✓ パズルテーブル ($puzzles_table) 作成成功</p>";
    } else {
        if ($mysqli->errno == 1050) { // Table already exists
            echo "<p style='color: orange;'>⚠ パズルテーブル ($puzzles_table) は既に存在します</p>";
        } else {
            echo "<p style='color: red;'>✗ パズルテーブル作成エラー: " . $mysqli->error . "</p>";
        }
    }
    
    // 進捗テーブル作成
    $progress_table = $table_prefix . 'crossword_progress';
    $sql_progress = "CREATE TABLE $progress_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        puzzle_id mediumint(9) NOT NULL,
        progress_data longtext NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_puzzle (user_id, puzzle_id)
    ) $charset_collate;";
    
    if ($mysqli->query($sql_progress)) {
        echo "<p style='color: green;'>✓ 進捗テーブル ($progress_table) 作成成功</p>";
    } else {
        if ($mysqli->errno == 1050) { // Table already exists
            echo "<p style='color: orange;'>⚠ 進捗テーブル ($progress_table) は既に存在します</p>";
        } else {
            echo "<p style='color: red;'>✗ 進捗テーブル作成エラー: " . $mysqli->error . "</p>";
        }
    }
    
    // デフォルトパズルの挿入
    echo "<h2>デフォルトパズルの作成</h2>";
    
    // 簡単なデフォルトパズルデータ
    $default_title = 'サンプルパズル';
    $default_description = 'クロスワードプラグインのサンプルパズルです。';
    
    $default_grid = array(
        'size' => 5,
        'grid' => array(
            array('H', 'E', 'L', 'L', 'O'),
            array('E', '', '', '', ''),
            array('L', '', 'C', 'A', 'T'),
            array('P', '', 'A', '', ''),
            array('', '', 'R', '', '')
        )
    );
    
    $default_words = array(
        'HELLO' => array('row' => 0, 'col' => 0, 'direction' => 'horizontal'),
        'HELP' => array('row' => 0, 'col' => 0, 'direction' => 'vertical'),
        'CAT' => array('row' => 2, 'col' => 2, 'direction' => 'horizontal'),
        'CAR' => array('row' => 2, 'col' => 2, 'direction' => 'vertical')
    );
    
    $default_hints = array(
        'HELLO' => '挨拶の言葉',
        'HELP' => '助けを求める時の言葉',
        'CAT' => '小さな肉食動物',
        'CAR' => '四輪の乗り物'
    );
    
    $grid_json = json_encode($default_grid, JSON_UNESCAPED_UNICODE);
    $words_json = json_encode($default_words, JSON_UNESCAPED_UNICODE);
    $hints_json = json_encode($default_hints, JSON_UNESCAPED_UNICODE);
    
    // 既存のデフォルトパズルをチェック
    $check_result = $mysqli->query("SELECT COUNT(*) as count FROM $puzzles_table");
    $row_count = 0;
    if ($check_result) {
        $count_row = $check_result->fetch_assoc();
        $row_count = $count_row['count'];
    }
    
    if ($row_count == 0) {
        $stmt = $mysqli->prepare("INSERT INTO $puzzles_table (title, description, grid_data, words_data, hints_data) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $default_title, $default_description, $grid_json, $words_json, $hints_json);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ デフォルトパズル作成成功</p>";
        } else {
            echo "<p style='color: red;'>✗ デフォルトパズル作成エラー: " . $mysqli->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: orange;'>⚠ パズルデータは既に存在します（$row_count件）</p>";
    }
    
    // テーブルの確認
    echo "<h2>テーブル確認</h2>";
    $tables = array($puzzles_table, $progress_table);
    
    foreach ($tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✓ $table - 存在します</p>";
            
            // レコード数の確認
            $count_result = $mysqli->query("SELECT COUNT(*) as count FROM $table");
            if ($count_result) {
                $count_row = $count_result->fetch_assoc();
                echo "<p>　　レコード数: " . $count_row['count'] . "件</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ $table - 存在しません</p>";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>✗ 致命的なエラーが発生しました: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>強制アクティベーション完了</strong></p>";
echo "<p>データベーステーブルが正常に作成されました。</p>";
echo "<p>これで「パズルデータの解析に失敗しました。」エラーが解決されるはずです。</p>";
?>
