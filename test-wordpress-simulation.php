<?php
/**
 * WordPress環境でのショートコード処理のシミュレーション
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>WordPress環境でのショートコード処理のシミュレーション</h1>";

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
    
    // パズルデータの取得（WordPressのwpdbをシミュレート）
    echo "<h2>1. パズルデータの取得テスト</h2>";
    
    // パズルID 2の取得テスト
    echo "<h3>パズルID 2の取得テスト</h3>";
    
    $stmt = $mysqli->prepare("SELECT * FROM $puzzles_table WHERE id = ?");
    $id = 2;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $puzzle = $result->fetch_assoc();
        echo "<p style='color: green;'>✓ パズルID 2が見つかりました</p>";
        echo "<p><strong>タイトル:</strong> " . $puzzle['title'] . "</p>";
        echo "<p><strong>ID:</strong> " . $puzzle['id'] . "</p>";
        
        // オブジェクトとして扱う（WordPressのwpdbの動作をシミュレート）
        $puzzle_obj = (object) $puzzle;
        
        // render_puzzleメソッドの処理をシミュレート
        echo "<h4>render_puzzleメソッドの処理シミュレーション</h4>";
        
        // 1. パズルオブジェクトのnull値チェック
        if (!$puzzle_obj || !is_object($puzzle_obj)) {
            echo "<p style='color: red;'>✗ パズルオブジェクトが無効です</p>";
        } else {
            echo "<p style='color: green;'>✓ パズルオブジェクトは有効です</p>";
        }
        
        // 2. 必要なプロパティの存在チェック
        $required_properties = ['id', 'grid_data', 'words_data', 'hints_data'];
        $missing_properties = [];
        
        foreach ($required_properties as $prop) {
            if (!isset($puzzle_obj->$prop)) {
                $missing_properties[] = $prop;
            }
        }
        
        if (empty($missing_properties)) {
            echo "<p style='color: green;'>✓ 必要なプロパティはすべて存在します</p>";
        } else {
            echo "<p style='color: red;'>✗ 不足しているプロパティ: " . implode(', ', $missing_properties) . "</p>";
        }
        
        // 3. JSONデコードのテスト
        echo "<h4>JSONデコードのテスト</h4>";
        
        $grid_data = json_decode($puzzle_obj->grid_data, true);
        $words_data = json_decode($puzzle_obj->words_data, true);
        $hints_data = json_decode($puzzle_obj->hints_data, true);
        
        echo "<p><strong>JSON解析結果:</strong></p>";
        echo "<ul>";
        echo "<li>grid_data: " . ($grid_data !== null ? '✓ 成功' : '✗ 失敗') . "</li>";
        echo "<li>words_data: " . ($words_data !== null ? '✓ 成功' : '✗ 失敗') . "</li>";
        echo "<li>hints_data: " . ($hints_data !== null ? '✓ 成功' : '✗ 失敗') . "</li>";
        echo "</ul>";
        
        if ($grid_data === null || $words_data === null || $hints_data === null) {
            echo "<p style='color: red;'><strong>問題発見:</strong> JSONデコードに失敗しています</p>";
            
            // 生データの確認
            echo "<p><strong>生データの確認:</strong></p>";
            echo "<details>";
            echo "<summary>grid_data（クリックで展開）</summary>";
            echo "<pre>" . htmlspecialchars(substr($puzzle_obj->grid_data, 0, 500)) . "</pre>";
            echo "</details>";
            
            echo "<details>";
            echo "<summary>words_data（クリックで展開）</summary>";
            echo "<pre>" . htmlspecialchars(substr($puzzle_obj->words_data, 0, 500)) . "</pre>";
            echo "</details>";
            
            echo "<details>";
            echo "<summary>hints_data（クリックで展開）</summary>";
            echo "<pre>" . htmlspecialchars(substr($puzzle_obj->hints_data, 0, 500)) . "</pre>";
            echo "</details>";
        } else {
            echo "<p style='color: green;'>✓ JSONデコードは成功しています</p>";
            
            // グリッドデータの構造確認
            echo "<p><strong>グリッドデータの構造確認:</strong></p>";
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
        
    } else {
        echo "<p style='color: red;'>✗ パズルID 2が見つかりません</p>";
    }
    
    $stmt->close();
    
    // デフォルトパズルの取得テスト
    echo "<h3>デフォルトパズルの取得テスト</h3>";
    
    $stmt = $mysqli->prepare("SELECT * FROM $puzzles_table ORDER BY id ASC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $puzzle = $result->fetch_assoc();
        echo "<p style='color: green;'>✓ デフォルトパズルが見つかりました</p>";
        echo "<p><strong>ID:</strong> " . $puzzle['id'] . "</p>";
        echo "<p><strong>タイトル:</strong> " . $puzzle['title'] . "</p>";
        
        // オブジェクトとして扱う
        $puzzle_obj = (object) $puzzle;
        
        // プロパティの確認
        echo "<p><strong>プロパティの確認:</strong></p>";
        echo "<ul>";
        echo "<li>id: " . (isset($puzzle_obj->id) ? $puzzle_obj->id : '未設定') . "</li>";
        echo "<li>title: " . (isset($puzzle_obj->title) ? $puzzle_obj->title : '未設定') . "</li>";
        echo "<li>grid_data: " . (isset($puzzle_obj->grid_data) ? '設定済み' : '未設定') . "</li>";
        echo "<li>words_data: " . (isset($puzzle_obj->words_data) ? '設定済み' : '未設定') . "</li>";
        echo "<li>hints_data: " . (isset($puzzle_obj->hints_data) ? '設定済み' : '未設定') . "</li>";
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>✗ デフォルトパズルが見つかりません</p>";
    }
    
    $stmt->close();
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
}

echo "<h2>2. 問題の分析</h2>";
echo "<p>現在発生しているエラー:</p>";
echo "<ul>";
echo "<li><strong>パズルID 2のデータ解析失敗:</strong> 「パズルデータの解析に失敗しました」</li>";
echo "<li><strong>デフォルトパズルでID 0を探している:</strong> 「パズルID: 0 が見つかりません」</li>";
echo "</ul>";

echo "<h2>3. 推奨される次のステップ</h2>";
echo "<ol>";
echo "<li>このシミュレーション結果を確認し、問題の原因を特定する</li>";
echo "<li>WordPress環境での実際のエラーログを確認する</li>";
echo "<li>必要に応じて、パズルデータを再作成する</li>";
echo "</ol>";
