<?php
/**
 * クロスワードプラグインのパズルデータ確認スクリプト
 * データベースに保存されているパズルデータの状態を確認できます
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>クロスワードプラグイン パズルデータ確認</h1>";

// WordPressの設定ファイルの読み込みをスキップ
// 直接データベース接続設定を使用

try {
    // データベース接続（ローカル環境用）
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $database = 'wordpress';
    
    $mysqli = new mysqli($host, $user, $password, $database);
    
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>✗ データベース接続エラー: " . $mysqli->connect_error . "</p>";
        echo "<p>ローカル環境のデータベース設定を確認してください。</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ データベース接続成功</p>";
    
    // テーブルの存在確認
    $table_prefix = 'wp_';
    $tables = array(
        $mysqli->real_escape_string($table_prefix . 'crossword_puzzles'),
        $mysqli->real_escape_string($table_prefix . 'crossword_progress')
    );
    
    echo "<h2>テーブル存在確認</h2>";
    foreach ($tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✓ {$table} - 存在します</p>";
        } else {
            echo "<p style='color: red;'>✗ {$table} - 存在しません</p>";
        }
    }
    
    // パズルデータの確認
    $puzzles_table = $mysqli->real_escape_string($table_prefix . 'crossword_puzzles');
    $result = $mysqli->query("SELECT * FROM $puzzles_table ORDER BY id DESC LIMIT 5");
    
    if ($result) {
        echo "<h2>パズルデータの確認（最新5件）</h2>";
        
        if ($result->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>タイトル</th><th>グリッドデータ</th><th>単語データ</th><th>ヒントデータ</th><th>作成日</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                
                // グリッドデータの確認
                $grid_data = $row['grid_data'];
                $grid_json = json_decode($grid_data, true);
                if ($grid_json === null) {
                    echo "<td style='color: red;'>JSON解析失敗: " . htmlspecialchars(substr($grid_data, 0, 100)) . "...</td>";
                } else {
                    echo "<td style='color: green;'>✓ 有効なJSON (" . strlen($grid_data) . "文字)</td>";
                }
                
                // 単語データの確認
                $words_data = $row['words_data'];
                $words_json = json_decode($words_data, true);
                if ($words_json === null) {
                    echo "<td style='color: red;'>JSON解析失敗: " . htmlspecialchars(substr($words_data, 0, 100)) . "...</td>";
                } else {
                    echo "<td style='color: green;'>✓ 有効なJSON (" . strlen($words_data) . "文字)</td>";
                }
                
                // ヒントデータの確認
                $hints_data = $row['hints_data'];
                $hints_json = json_decode($hints_data, true);
                if ($hints_json === null) {
                    echo "<td style='color: red;'>JSON解析失敗: " . htmlspecialchars(substr($hints_data, 0, 100)) . "...</td>";
                } else {
                    echo "<td style='color: green;'>✓ 有効なJSON (" . strlen($hints_data) . "文字)</td>";
                }
                
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            
            // 詳細なデータ解析
            echo "<h2>詳細なデータ解析</h2>";
            $result->data_seek(0); // 結果セットを最初に戻す
            
            while ($row = $result->fetch_assoc()) {
                echo "<h3>パズルID: " . $row['id'] . " - " . htmlspecialchars($row['title']) . "</h3>";
                
                // グリッドデータの詳細解析
                $grid_json = json_decode($row['grid_data'], true);
                if ($grid_json !== null) {
                    echo "<h4>グリッドデータ:</h4>";
                    echo "<pre>" . htmlspecialchars(print_r($grid_json, true)) . "</pre>";
                } else {
                    echo "<h4>グリッドデータ（JSON解析失敗）:</h4>";
                    echo "<p>生データ: " . htmlspecialchars($row['grid_data']) . "</p>";
                    echo "<p>JSONエラー: " . json_last_error_msg() . "</p>";
                }
                
                // 単語データの詳細解析
                $words_json = json_decode($row['words_data'], true);
                if ($words_json !== null) {
                    echo "<h4>単語データ:</h4>";
                    echo "<pre>" . htmlspecialchars(print_r($words_json, true)) . "</pre>";
                } else {
                    echo "<h4>単語データ（JSON解析失敗）:</h4>";
                    echo "<p>生データ: " . htmlspecialchars($row['words_data']) . "</p>";
                    echo "<p>JSONエラー: " . json_last_error_msg() . "</p>";
                }
                
                // ヒントデータの詳細解析
                $hints_json = json_decode($row['hints_data'], true);
                if ($hints_json !== null) {
                    echo "<h4>ヒントデータ:</h4>";
                    echo "<pre>" . htmlspecialchars(print_r($hints_json, true)) . "</pre>";
                } else {
                    echo "<h4>ヒントデータ（JSON解析失敗）:</h4>";
                    echo "<p>生データ: " . htmlspecialchars($row['hints_data']) . "</p>";
                    echo "<p>JSONエラー: " . json_last_error_msg() . "</p>";
                }
                
                echo "<hr>";
            }
            
        } else {
            echo "<p style='color: orange;'>⚠ パズルデータが存在しません</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ クエリ実行エラー: " . $mysqli->error . "</p>";
    }
    
    // データベース接続を閉じる
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>✗ 致命的なエラーが発生しました: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>パズルデータ確認完了</strong></p>";
echo "<p>このページでJSON解析失敗が表示されている場合は、データベースのデータに問題があります。</p>";
?>
