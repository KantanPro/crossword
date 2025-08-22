<?php
/**
 * ショートコード実行の詳細デバッグ
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>ショートコード実行の詳細デバッグ</h1>";

// データベース接続設定
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'wordpress';

try {
    $mysqli = new mysqli($host, $user, $password, $database);
    
    if ($mysqli->connect_error) {
        echo "<p style='color: red;'>✗ データベース接続エラー: " . $mysqli->connect_error . "</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ データベース接続成功</p>";
    
    $table_prefix = 'wp_';
    $puzzles_table = $table_prefix . 'crossword_puzzles';
    
    // パズルデータの詳細確認
    echo "<h2>1. パズルデータの詳細確認</h2>";
    
    $result = $mysqli->query("SELECT * FROM $puzzles_table ORDER BY id");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<h3>パズルID: " . $row['id'] . "</h3>";
            
            // JSONデータの解析テスト
            $grid_data = json_decode($row['grid_data'], true);
            $words_data = json_decode($row['words_data'], true);
            $hints_data = json_decode($row['hints_data'], true);
            
            echo "<p><strong>JSON解析結果:</strong></p>";
            echo "<ul>";
            echo "<li>grid_data: " . ($grid_data !== null ? '✓ 成功' : '✗ 失敗') . "</li>";
            echo "<li>words_data: " . ($words_data !== null ? '✓ 成功' : '✗ 失敗') . "</li>";
            echo "<li>hints_data: " . ($hints_data !== null ? '✓ 成功' : '✗ 失敗') . "</li>";
            echo "</ul>";
            
            if ($grid_data !== null) {
                echo "<p><strong>グリッドデータ構造:</strong></p>";
                echo "<ul>";
                echo "<li>size: " . (isset($grid_data['size']) ? $grid_data['size'] : '未設定') . "</li>";
                echo "<li>grid: " . (isset($grid_data['grid']) && is_array($grid_data['grid']) ? '配列（' . count($grid_data['grid']) . '行）' : '未設定/無効') . "</li>";
                echo "</ul>";
                
                if (isset($grid_data['grid']) && is_array($grid_data['grid'])) {
                    echo "<p><strong>グリッドの内容（最初の3行）:</strong></p>";
                    echo "<pre>";
                    for ($i = 0; $i < min(3, count($grid_data['grid'])); $i++) {
                        echo "行 $i: " . json_encode($grid_data['grid'][$i]) . "\n";
                    }
                    echo "</pre>";
                }
            }
            
            if ($words_data !== null) {
                echo "<p><strong>単語データ:</strong> " . count($words_data) . "個</p>";
                echo "<ul>";
                foreach (array_slice($words_data, 0, 3) as $word => $data) {
                    echo "<li>$word: " . json_encode($data) . "</li>";
                }
                if (count($words_data) > 3) {
                    echo "<li>... 他 " . (count($words_data) - 3) . "個</li>";
                }
                echo "</ul>";
            }
            
            if ($hints_data !== null) {
                echo "<p><strong>ヒントデータ:</strong> " . count($hints_data) . "個</p>";
                echo "<ul>";
                foreach (array_slice($hints_data, 0, 3) as $word => $hint) {
                    echo "<li>$word: $hint</li>";
                }
                if (count($hints_data) > 3) {
                    echo "<li>... 他 " . (count($hints_data) - 3) . "個</li>";
                }
                echo "</ul>";
            }
            
            echo "<hr>";
        }
    } else {
        echo "<p style='color: red;'>✗ パズルデータが見つかりません</p>";
    }
    
    // 特定のパズルIDでの検索テスト
    echo "<h2>2. 特定パズルIDでの検索テスト</h2>";
    
    $test_ids = [1, 2];
    foreach ($test_ids as $id) {
        echo "<h3>パズルID $id の検索テスト</h3>";
        
        $stmt = $mysqli->prepare("SELECT * FROM $puzzles_table WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<p style='color: green;'>✓ パズルID $id が見つかりました</p>";
            echo "<p><strong>タイトル:</strong> " . $row['title'] . "</p>";
            echo "<p><strong>作成日:</strong> " . $row['created_at'] . "</p>";
            
            // JSONデータの再解析テスト
            $grid_data = json_decode($row['grid_data'], true);
            $words_data = json_decode($row['words_data'], true);
            $hints_data = json_decode($row['hints_data'], true);
            
            echo "<p><strong>JSON解析結果:</strong></p>";
            echo "<ul>";
            echo "<li>grid_data: " . ($grid_data !== null ? '✓ 成功' : '✗ 失敗') . "</li>";
            echo "<li>words_data: " . ($words_data !== null ? '✓ 成功' : '✗ 失敗') . "</li>";
            echo "<li>hints_data: " . ($hints_data !== null ? '✓ 成功' : '✗ 失敗') . "</li>";
            echo "</ul>";
            
            if ($grid_data === null || $words_data === null || $hints_data === null) {
                echo "<p style='color: red;'><strong>問題発見:</strong> JSON解析に失敗しています</p>";
                
                // 生データの確認
                echo "<p><strong>生データの確認:</strong></p>";
                echo "<details>";
                echo "<summary>grid_data（クリックで展開）</summary>";
                echo "<pre>" . htmlspecialchars(substr($row['grid_data'], 0, 500)) . "</pre>";
                echo "</details>";
                
                echo "<details>";
                echo "<summary>words_data（クリックで展開）</summary>";
                echo "<pre>" . htmlspecialchars(substr($row['words_data'], 0, 500)) . "</pre>";
                echo "</details>";
                
                echo "<details>";
                echo "<summary>hints_data（クリックで展開）</summary>";
                echo "<pre>" . htmlspecialchars(substr($row['hints_data'], 0, 500)) . "</pre>";
                echo "</details>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ パズルID $id が見つかりません</p>";
        }
        
        $stmt->close();
        echo "<hr>";
    }
    
    // デフォルトパズルの検索テスト
    echo "<h2>3. デフォルトパズルの検索テスト</h2>";
    
    $stmt = $mysqli->prepare("SELECT * FROM $puzzles_table ORDER BY id ASC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<p style='color: green;'>✓ デフォルトパズルが見つかりました</p>";
        echo "<p><strong>ID:</strong> " . $row['id'] . "</p>";
        echo "<p><strong>タイトル:</strong> " . $row['title'] . "</p>";
        echo "<p><strong>作成日:</strong> " . $row['created_at'] . "</p>";
    } else {
        echo "<p style='color: red;'>✗ デフォルトパズルが見つかりません</p>";
    }
    
    $stmt->close();
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
}

echo "<h2>4. 推奨される次のステップ</h2>";
echo "<ol>";
echo "<li>このデバッグ結果を確認し、JSON解析の問題を特定する</li>";
echo "<li>問題のあるデータを修正するか、新しいパズルデータを作成する</li>";
echo "<li>WordPress環境でショートコードを再テストする</li>";
echo "</ol>";
