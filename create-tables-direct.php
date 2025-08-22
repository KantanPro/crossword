<?php
/**
 * データベーステーブルの直接作成
 * WordPressの関数を使わずに直接SQLでテーブルを作成します
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>クロスワードプラグイン データベーステーブル直接作成</h1>";

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
    echo "<h2>パズルテーブル作成</h2>";
    $puzzles_table = $table_prefix . 'crossword_puzzles';
    $sql_puzzles = "CREATE TABLE IF NOT EXISTS $puzzles_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title tinytext NOT NULL,
        description text,
        grid_data longtext NOT NULL,
        complete_answer_grid_data longtext NOT NULL,
        words_data longtext NOT NULL,
        hints_data longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate";
    
    if ($mysqli->query($sql_puzzles)) {
        echo "<p style='color: green;'>✓ パズルテーブル ($puzzles_table) 作成成功</p>";
    } else {
        echo "<p style='color: red;'>✗ パズルテーブル作成エラー: " . $mysqli->error . "</p>";
    }
    
    // 進捗テーブル作成
    echo "<h2>進捗テーブル作成</h2>";
    $progress_table = $table_prefix . 'crossword_progress';
    $sql_progress = "CREATE TABLE IF NOT EXISTS $progress_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        puzzle_id mediumint(9) NOT NULL,
        progress_data longtext NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_puzzle (user_id, puzzle_id)
    ) $charset_collate";
    
    if ($mysqli->query($sql_progress)) {
        echo "<p style='color: green;'>✓ 進捗テーブル ($progress_table) 作成成功</p>";
    } else {
        echo "<p style='color: red;'>✗ 進捗テーブル作成エラー: " . $mysqli->error . "</p>";
    }
    
    // デフォルトパズルの挿入
    echo "<h2>デフォルトパズルの作成</h2>";
    
    // 既存のデフォルトパズルをチェック
    $check_result = $mysqli->query("SELECT COUNT(*) as count FROM $puzzles_table");
    $row_count = 0;
    if ($check_result) {
        $count_row = $check_result->fetch_assoc();
        $row_count = $count_row['count'];
    }
    
    if ($row_count == 0) {
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
        
        $stmt = $mysqli->prepare("INSERT INTO $puzzles_table (title, description, grid_data, words_data, hints_data) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $default_title, $default_description, $grid_json, $words_json, $hints_json);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✓ デフォルトパズル作成成功 (ID: " . $mysqli->insert_id . ")</p>";
            } else {
                echo "<p style='color: red;'>✗ デフォルトパズル作成エラー: " . $mysqli->error . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p style='color: red;'>✗ プリペアードステートメント作成エラー: " . $mysqli->error . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ パズルデータは既に存在します（$row_count件）</p>";
    }
    
    // テーブルの確認
    echo "<h2>作成されたテーブルの確認</h2>";
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
            
            // テーブル構造の確認
            echo "<h3>$table の構造:</h3>";
            $structure_result = $mysqli->query("DESCRIBE $table");
            if ($structure_result) {
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr><th>フィールド</th><th>型</th><th>NULL</th><th>キー</th><th>デフォルト</th></tr>";
                
                while ($row = $structure_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p style='color: red;'>✗ $table - 存在しません</p>";
        }
    }
    
    // パズルデータの検証
    if ($row_count > 0) {
        echo "<h2>パズルデータの検証</h2>";
        $verify_result = $mysqli->query("SELECT id, title, 
            JSON_VALID(grid_data) as grid_valid,
            JSON_VALID(words_data) as words_valid,
            JSON_VALID(hints_data) as hints_valid
            FROM $puzzles_table ORDER BY id");
        
        if ($verify_result && $verify_result->num_rows > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>タイトル</th><th>グリッド</th><th>単語</th><th>ヒント</th></tr>";
            
            while ($row = $verify_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td style='color: " . ($row['grid_valid'] ? 'green' : 'red') . ";'>" . ($row['grid_valid'] ? 'OK' : 'NG') . "</td>";
                echo "<td style='color: " . ($row['words_valid'] ? 'green' : 'red') . ";'>" . ($row['words_valid'] ? 'OK' : 'NG') . "</td>";
                echo "<td style='color: " . ($row['hints_valid'] ? 'green' : 'red') . ";'>" . ($row['hints_valid'] ? 'OK' : 'NG') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>✗ 致命的なエラーが発生しました: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>データベーステーブル作成完了</strong></p>";
echo "<p>これで「パズルデータの解析に失敗しました。」エラーが解決されるはずです。</p>";
echo "<p>WordPress管理画面でクロスワードプラグインを確認してください。</p>";
?>
