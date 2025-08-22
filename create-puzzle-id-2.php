<?php
/**
 * パズルID: 2の作成
 * 存在しないパズルID: 2を作成して、ショートコードの動作をテストします
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>パズルID: 2の作成</h1>";

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
    
    // パズルID: 2が既に存在するかチェック
    $check_result = $mysqli->query("SELECT id FROM $puzzles_table WHERE id = 2");
    
    if ($check_result && $check_result->num_rows > 0) {
        echo "<p style='color: orange;'>⚠ パズルID: 2は既に存在します</p>";
    } else {
        // パズルID: 2を作成
        echo "<h2>パズルID: 2の作成</h2>";
        
        $title = '中級パズル';
        $description = '少し難しいクロスワードパズルです。';
        
        // 6×6グリッドのパズルデータ
        $grid_data = array(
            'size' => 6,
            'grid' => array(
                array('S', 'U', 'N', 'S', 'E', 'T'),
                array('U', '', '', '', '', ''),
                array('N', '', 'C', 'A', 'T', ''),
                array('S', '', 'A', '', '', ''),
                array('E', '', 'R', '', '', ''),
                array('T', '', '', '', '', '')
            )
        );
        
        $words_data = array(
            'SUNSET' => array('row' => 0, 'col' => 0, 'direction' => 'horizontal'),
            'SUN' => array('row' => 0, 'col' => 0, 'direction' => 'vertical'),
            'CAT' => array('row' => 2, 'col' => 2, 'direction' => 'horizontal'),
            'CAR' => array('row' => 2, 'col' => 2, 'direction' => 'vertical'),
            'SET' => array('row' => 0, 'col' => 3, 'direction' => 'horizontal'),
            'STAR' => array('row' => 0, 'col' => 0, 'direction' => 'vertical')
        );
        
        $hints_data = array(
            'SUNSET' => '夕日が沈む時間',
            'SUN' => '空に輝く星',
            'CAT' => '小さな肉食動物',
            'CAR' => '四輪の乗り物',
            'SET' => '物を置くこと',
            'STAR' => '夜空に輝く光'
        );
        
        $grid_json = json_encode($grid_data, JSON_UNESCAPED_UNICODE);
        $words_json = json_encode($words_data, JSON_UNESCAPED_UNICODE);
        $hints_json = json_encode($hints_data, JSON_UNESCAPED_UNICODE);
        
        // パズルID: 2を強制的に作成
        $stmt = $mysqli->prepare("INSERT INTO $puzzles_table (id, title, description, grid_data, words_data, hints_data) VALUES (2, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $title, $description, $grid_json, $words_json, $hints_json);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✓ パズルID: 2作成成功</p>";
            } else {
                echo "<p style='color: red;'>✗ パズルID: 2作成エラー: " . $mysqli->error . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p style='color: red;'>✗ プリペアードステートメント作成エラー: " . $mysqli->error . "</p>";
        }
    }
    
    // 現在のパズルデータ一覧を表示
    echo "<h2>現在のパズルデータ一覧</h2>";
    $all_result = $mysqli->query("SELECT id, title, description, created_at FROM $puzzles_table ORDER BY id");
    
    if ($all_result && $all_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>タイトル</th><th>説明</th><th>作成日</th></tr>";
        
        while ($row = $all_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
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
echo "<p><strong>パズルID: 2作成完了</strong></p>";
echo "<p>これで [crossword id=\"2\"] が正常に表示されるはずです。</p>";
?>
