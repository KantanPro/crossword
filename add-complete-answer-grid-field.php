<?php
/**
 * 既存のデータベーステーブルにcomplete_answer_grid_dataフィールドを追加
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>クロスワードプラグイン データベースフィールド追加</h1>";

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
    
    // フィールドが既に存在するかチェック
    $check_field = $mysqli->query("SHOW COLUMNS FROM $puzzles_table LIKE 'complete_answer_grid_data'");
    
    if ($check_field->num_rows > 0) {
        echo "<p style='color: blue;'>ℹ complete_answer_grid_dataフィールドは既に存在します</p>";
    } else {
        // フィールドを追加
        $add_field_sql = "ALTER TABLE $puzzles_table ADD COLUMN complete_answer_grid_data longtext NOT NULL AFTER grid_data";
        
        if ($mysqli->query($add_field_sql)) {
            echo "<p style='color: green;'>✓ complete_answer_grid_dataフィールドを追加しました</p>";
            
            // 既存のパズルデータを更新
            $update_result = $mysqli->query("SELECT id, grid_data FROM $puzzles_table");
            
            if ($update_result) {
                $updated_count = 0;
                while ($row = $update_result->fetch_assoc()) {
                    $grid_data = json_decode($row['grid_data'], true);
                    if ($grid_data && isset($grid_data['grid'])) {
                        // 空マスに適当な文字を設定して完全な正解グリッドを作成
                        $complete_grid = $grid_data['grid'];
                        for ($i = 0; $i < count($complete_grid); $i++) {
                            for ($j = 0; $j < count($complete_grid[$i]); $j++) {
                                if ($complete_grid[$i][$j] === '') {
                                    $complete_grid[$i][$j] = 'あ'; // デフォルト文字
                                }
                            }
                        }
                        
                        $complete_answer_grid_data = json_encode(array(
                            'size' => $grid_data['size'],
                            'grid' => $complete_grid
                        ));
                        
                        $update_sql = "UPDATE $puzzles_table SET complete_answer_grid_data = ? WHERE id = ?";
                        $stmt = $mysqli->prepare($update_sql);
                        $stmt->bind_param('si', $complete_answer_grid_data, $row['id']);
                        
                        if ($stmt->execute()) {
                            $updated_count++;
                        }
                        $stmt->close();
                    }
                }
                echo "<p style='color: green;'>✓ {$updated_count}件のパズルデータを更新しました</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ フィールド追加エラー: " . $mysqli->error . "</p>";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ エラーが発生しました: " . $e->getMessage() . "</p>";
}
?>
