<?php
/**
 * プラグイン再有効化後のテーブル確認スクリプト
 * データベーステーブルが正しく作成されたかを確認できます
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>クロスワードプラグイン テーブル確認（再有効化後）</h1>";

echo "<p><strong>重要:</strong> このスクリプトを実行する前に、WordPress管理画面でクロスワードプラグインを無効化→有効化してください。</p>";

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
            
            // テーブル構造の確認
            $structure_result = $mysqli->query("DESCRIBE $table");
            if ($structure_result) {
                echo "<h3>{$table}のテーブル構造:</h3>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
                echo "<tr><th>フィールド</th><th>型</th><th>NULL</th><th>キー</th><th>デフォルト</th><th>追加</th></tr>";
                
                while ($row = $structure_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
                    echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ {$table} - 存在しません</p>";
        }
    }
    
    // パズルデータの確認
    $puzzles_table = $mysqli->real_escape_string($table_prefix . 'crossword_puzzles');
    $result = $mysqli->query("SELECT COUNT(*) as count FROM $puzzles_table");
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<h2>パズルデータの状況</h2>";
        echo "<p>パズルデータの総数: " . $row['count'] . "件</p>";
        
        if ($row['count'] > 0) {
            // 最新のパズルデータを確認
            $latest_result = $mysqli->query("SELECT * FROM $puzzles_table ORDER BY id DESC LIMIT 1");
            if ($latest_result && $latest_result->num_rows > 0) {
                $puzzle = $latest_result->fetch_assoc();
                echo "<h3>最新のパズルデータ:</h3>";
                echo "<p>ID: " . $puzzle['id'] . "</p>";
                echo "<p>タイトル: " . htmlspecialchars($puzzle['title']) . "</p>";
                echo "<p>作成日: " . $puzzle['created_at'] . "</p>";
                
                // JSONデータの妥当性チェック
                $grid_data = $puzzle['grid_data'];
                $words_data = $puzzle['words_data'];
                $hints_data = $puzzle['hints_data'];
                
                $grid_json = json_decode($grid_data, true);
                $words_json = json_decode($words_data, true);
                $hints_json = json_decode($hints_data, true);
                
                echo "<h4>データの妥当性:</h4>";
                echo "<ul>";
                if ($grid_json !== null) {
                    echo "<li style='color: green;'>✓ グリッドデータ: 有効なJSON</li>";
                } else {
                    echo "<li style='color: red;'>✗ グリッドデータ: JSON解析失敗 (" . json_last_error_msg() . ")</li>";
                }
                
                if ($words_json !== null) {
                    echo "<li style='color: green;'>✓ 単語データ: 有効なJSON</li>";
                } else {
                    echo "<li style='color: red;'>✗ 単語データ: JSON解析失敗 (" . json_last_error_msg() . ")</li>";
                }
                
                if ($hints_json !== null) {
                    echo "<li style='color: green;'>✓ ヒントデータ: 有効なJSON</li>";
                } else {
                    echo "<li style='color: red;'>✗ ヒントデータ: JSON解析失敗 (" . json_last_error_msg() . ")</li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ パズルデータが存在しません。プラグインの再有効化が必要です。</p>";
        }
    }
    
    // データベース接続を閉じる
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>✗ 致命的なエラーが発生しました: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>テーブル確認完了</strong></p>";
echo "<p>テーブルが存在しない場合は、WordPress管理画面でプラグインを再有効化してください。</p>";
echo "<p>テーブルが存在する場合は、プラグインは正常に動作するはずです。</p>";
?>
