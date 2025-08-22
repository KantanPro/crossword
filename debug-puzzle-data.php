<?php
/**
 * パズルデータの詳細調査
 * データベースに保存されているパズルデータの内容を詳しく確認します
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>パズルデータの詳細調査</h1>";

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
    
    $table_prefix = 'wp_';
    $puzzles_table = $table_prefix . 'crossword_puzzles';
    
    // 全パズルデータの詳細を取得
    echo "<h2>全パズルデータの詳細</h2>";
    $result = $mysqli->query("SELECT * FROM $puzzles_table ORDER BY id");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<h3>パズルID: " . $row['id'] . "</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
            echo "<tr><th>フィールド</th><th>値</th></tr>";
            
            foreach ($row as $field => $value) {
                if ($field === 'grid_data' || $field === 'words_data' || $field === 'hints_data') {
                    // JSONデータの詳細表示
                    $decoded = json_decode($value, true);
                    if ($decoded === null) {
                        echo "<tr><td>$field</td><td style='color: red;'>JSONデコード失敗</td></tr>";
                    } else {
                        echo "<tr><td>$field</td><td>";
                        echo "<details><summary>JSONデータ（クリックで展開）</summary>";
                        echo "<pre>" . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
                        echo "</details>";
                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td>$field</td><td>" . htmlspecialchars($value) . "</td></tr>";
                }
            }
            echo "</table>";
            
            // データの妥当性チェック
            echo "<h4>データ妥当性チェック:</h4>";
            $grid_data = json_decode($row['grid_data'], true);
            $words_data = json_decode($row['words_data'], true);
            $hints_data = json_decode($row['hints_data'], true);
            
            if ($grid_data === null) {
                echo "<p style='color: red;'>✗ グリッドデータ: JSONデコード失敗</p>";
            } else {
                echo "<p style='color: green;'>✓ グリッドデータ: 有効</p>";
                if (isset($grid_data['size']) && isset($grid_data['grid'])) {
                    echo "<p>　　サイズ: " . $grid_data['size'] . "×" . $grid_data['size'] . "</p>";
                }
            }
            
            if ($words_data === null) {
                echo "<p style='color: red;'>✗ 単語データ: JSONデコード失敗</p>";
            } else {
                echo "<p style='color: green;'>✓ 単語データ: 有効</p>";
                echo "<p>　　単語数: " . count($words_data) . "個</p>";
            }
            
            if ($hints_data === null) {
                echo "<p style='color: red;'>✗ ヒントデータ: JSONデコード失敗</p>";
            } else {
                echo "<p style='color: green;'>✓ ヒントデータ: 有効</p>";
                echo "<p>　　ヒント数: " . count($hints_data) . "個</p>";
            }
            
            echo "<hr>";
        }
    } else {
        echo "<p style='color: red;'>✗ パズルデータが見つかりません</p>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>✗ 致命的なエラーが発生しました: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>調査完了</strong></p>";
?>
