<?php
/**
 * パズルID: 2のデータ調査スクリプト
 * JSONデコード失敗の原因を特定します
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>パズルID: 2 データ詳細調査</h1>";

try {
    // データベース接続（ローカル環境用）
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
    
    // パズルID: 2のデータを取得
    $table_prefix = 'wp_';
    $puzzles_table = $mysqli->real_escape_string($table_prefix . 'crossword_puzzles');
    
    $result = $mysqli->query("SELECT * FROM $puzzles_table WHERE id = 2");
    
    if ($result) {
        if ($result->num_rows > 0) {
            $puzzle = $result->fetch_assoc();
            
            echo "<h2>パズルID: 2 の基本情報</h2>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>フィールド</th><th>値</th></tr>";
            echo "<tr><td>ID</td><td>" . htmlspecialchars($puzzle['id']) . "</td></tr>";
            echo "<tr><td>タイトル</td><td>" . htmlspecialchars($puzzle['title']) . "</td></tr>";
            echo "<tr><td>説明</td><td>" . htmlspecialchars($puzzle['description']) . "</td></tr>";
            echo "<tr><td>作成日</td><td>" . htmlspecialchars($puzzle['created_at']) . "</td></tr>";
            echo "</table>";
            
            // グリッドデータの詳細解析
            echo "<h2>グリッドデータの解析</h2>";
            $grid_data = $puzzle['grid_data'];
            echo "<h3>生データ（最初の500文字）:</h3>";
            echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc; max-height: 200px; overflow-y: scroll;'>";
            echo htmlspecialchars(substr($grid_data, 0, 500));
            echo "</pre>";
            
            echo "<h3>JSONデコード試行:</h3>";
            $grid_json = json_decode($grid_data, true);
            if ($grid_json !== null) {
                echo "<p style='color: green;'>✓ JSONデコード成功</p>";
                echo "<pre style='background: #e8f5e8; padding: 10px; border: 1px solid #4caf50;'>";
                echo htmlspecialchars(print_r($grid_json, true));
                echo "</pre>";
            } else {
                echo "<p style='color: red;'>✗ JSONデコード失敗</p>";
                echo "<p>JSONエラー: " . json_last_error_msg() . "</p>";
                echo "<p>エラーコード: " . json_last_error() . "</p>";
                
                // 文字エンコーディングをチェック
                echo "<h4>文字エンコーディング情報:</h4>";
                echo "<p>文字エンコーディング: " . mb_detect_encoding($grid_data) . "</p>";
                echo "<p>データ長: " . strlen($grid_data) . " バイト</p>";
                
                // 無効な文字をチェック
                echo "<h4>文字の妥当性チェック:</h4>";
                $is_utf8 = mb_check_encoding($grid_data, 'UTF-8');
                echo "<p>UTF-8妥当性: " . ($is_utf8 ? '有効' : '無効') . "</p>";
                
                // JSONの構文エラーを特定
                echo "<h4>JSON構文エラーの詳細:</h4>";
                switch (json_last_error()) {
                    case JSON_ERROR_NONE:
                        echo "<p>エラーなし</p>";
                        break;
                    case JSON_ERROR_DEPTH:
                        echo "<p style='color: red;'>最大スタック深度を超えました</p>";
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        echo "<p style='color: red;'>無効または不正な形式のJSON</p>";
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        echo "<p style='color: red;'>制御文字エラー（エンコーディングの問題の可能性）</p>";
                        break;
                    case JSON_ERROR_SYNTAX:
                        echo "<p style='color: red;'>構文エラー、不正な形式のJSON</p>";
                        break;
                    case JSON_ERROR_UTF8:
                        echo "<p style='color: red;'>不正な形式のUTF-8文字（エンコーディングの問題）</p>";
                        break;
                    default:
                        echo "<p style='color: red;'>不明なエラー</p>";
                        break;
                }
            }
            
            // 単語データの詳細解析
            echo "<h2>単語データの解析</h2>";
            $words_data = $puzzle['words_data'];
            echo "<h3>生データ（最初の500文字）:</h3>";
            echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc; max-height: 200px; overflow-y: scroll;'>";
            echo htmlspecialchars(substr($words_data, 0, 500));
            echo "</pre>";
            
            $words_json = json_decode($words_data, true);
            if ($words_json !== null) {
                echo "<p style='color: green;'>✓ JSONデコード成功</p>";
            } else {
                echo "<p style='color: red;'>✗ JSONデコード失敗: " . json_last_error_msg() . "</p>";
            }
            
            // ヒントデータの詳細解析
            echo "<h2>ヒントデータの解析</h2>";
            $hints_data = $puzzle['hints_data'];
            echo "<h3>生データ（最初の500文字）:</h3>";
            echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc; max-height: 200px; overflow-y: scroll;'>";
            echo htmlspecialchars(substr($hints_data, 0, 500));
            echo "</pre>";
            
            $hints_json = json_decode($hints_data, true);
            if ($hints_json !== null) {
                echo "<p style='color: green;'>✓ JSONデコード成功</p>";
            } else {
                echo "<p style='color: red;'>✗ JSONデコード失敗: " . json_last_error_msg() . "</p>";
            }
            
            // データ修復の提案
            echo "<h2>データ修復の提案</h2>";
            if ($grid_json === null || $words_json === null || $hints_json === null) {
                echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 4px;'>";
                echo "<h3>⚠️ 修復オプション</h3>";
                echo "<ul>";
                echo "<li><strong>オプション1:</strong> パズルID: 2を削除する</li>";
                echo "<li><strong>オプション2:</strong> 破損したデータを手動で修正する</li>";
                echo "<li><strong>オプション3:</strong> 新しいパズルデータで上書きする</li>";
                echo "</ul>";
                echo "</div>";
            }
            
        } else {
            echo "<p style='color: orange;'>⚠ パズルID: 2 が見つかりません</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ クエリ実行エラー: " . $mysqli->error . "</p>";
    }
    
    // 他のパズルデータの状況確認
    echo "<h2>他のパズルデータの状況</h2>";
    $all_result = $mysqli->query("SELECT id, title, 
        CASE WHEN JSON_VALID(grid_data) THEN 'OK' ELSE 'NG' END as grid_status,
        CASE WHEN JSON_VALID(words_data) THEN 'OK' ELSE 'NG' END as words_status,
        CASE WHEN JSON_VALID(hints_data) THEN 'OK' ELSE 'NG' END as hints_status
        FROM $puzzles_table ORDER BY id");
    
    if ($all_result && $all_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>タイトル</th><th>グリッド</th><th>単語</th><th>ヒント</th></tr>";
        
        while ($row = $all_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td style='color: " . ($row['grid_status'] == 'OK' ? 'green' : 'red') . ";'>" . $row['grid_status'] . "</td>";
            echo "<td style='color: " . ($row['words_status'] == 'OK' ? 'green' : 'red') . ";'>" . $row['words_status'] . "</td>";
            echo "<td style='color: " . ($row['hints_status'] == 'OK' ? 'green' : 'red') . ";'>" . $row['hints_status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>✗ 致命的なエラーが発生しました: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>調査完了</strong></p>";
echo "<p>JSONデコード失敗の原因が特定されました。</p>";
?>
