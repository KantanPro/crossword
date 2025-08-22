<?php
/**
 * 日本語クロスワードパズルの自動生成テスト
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>日本語クロスワードパズルの自動生成テスト</h1>";

// クロスワードジェネレータークラスを読み込み
require_once 'includes/class-crossword-generator.php';

try {
    // ジェネレーターのインスタンスを作成
    $generator = new Crossword_Generator();
    
    echo "<h2>1. 簡単な日本語パズルの生成</h2>";
    
    // 簡単な日本語パズルを生成
    $puzzle = $generator->generate_puzzle('easy', 10, 8);
    
    if ($puzzle) {
        echo "<p style='color: green;'>✓ 簡単な日本語パズルの生成に成功しました</p>";
        
        echo "<h3>生成されたパズルの詳細</h3>";
        echo "<p><strong>タイトル:</strong> " . htmlspecialchars($puzzle['title']) . "</p>";
        echo "<p><strong>説明:</strong> " . htmlspecialchars($puzzle['description']) . "</p>";
        echo "<p><strong>難易度:</strong> " . htmlspecialchars($puzzle['difficulty']) . "</p>";
        
        // グリッドデータの表示
        $grid_data = json_decode($puzzle['grid_data'], true);
        if ($grid_data) {
            echo "<h4>グリッド（" . $grid_data['size'] . "×" . $grid_data['size'] . "）</h4>";
            echo "<div style='font-family: monospace; font-size: 16px; line-height: 1.2;'>";
            foreach ($grid_data['grid'] as $row => $cells) {
                echo "<div>";
                foreach ($cells as $col => $cell) {
                    if ($cell === '') {
                        echo "<span style='display: inline-block; width: 20px; height: 20px; background: #ccc; border: 1px solid #999; text-align: center; line-height: 20px;'>　</span>";
                    } else {
                        echo "<span style='display: inline-block; width: 20px; height: 20px; background: #fff; border: 1px solid #999; text-align: center; line-height: 20px;'>" . htmlspecialchars($cell) . "</span>";
                    }
                }
                echo "</div>";
            }
            echo "</div>";
        }
        
        // 単語データの表示
        $words_data = json_decode($puzzle['words_data'], true);
        if ($words_data) {
            echo "<h4>含まれる単語</h4>";
            echo "<ul>";
            foreach ($words_data as $word => $data) {
                echo "<li><strong>" . htmlspecialchars($word) . "</strong> - 行: " . $data['row'] . ", 列: " . $data['col'] . ", 方向: " . $data['direction'] . "</li>";
            }
            echo "</ul>";
        }
        
        // ヒントデータの表示
        $hints_data = json_decode($puzzle['hints_data'], true);
        if ($hints_data) {
            echo "<h4>ヒント</h4>";
            echo "<ul>";
            foreach ($hints_data as $word => $hint) {
                echo "<li><strong>" . htmlspecialchars($word) . ":</strong> " . htmlspecialchars($hint) . "</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ 簡単な日本語パズルの生成に失敗しました</p>";
    }
    
    echo "<hr>";
    
    echo "<h2>2. 中程度の日本語パズルの生成</h2>";
    
    // 中程度の日本語パズルを生成
    $puzzle_medium = $generator->generate_puzzle('medium', 12, 10);
    
    if ($puzzle_medium) {
        echo "<p style='color: green;'>✓ 中程度の日本語パズルの生成に成功しました</p>";
        
        $grid_data_medium = json_decode($puzzle_medium['grid_data'], true);
        if ($grid_data_medium) {
            echo "<p><strong>グリッドサイズ:</strong> " . $grid_data_medium['size'] . "×" . $grid_data_medium['size'] . "</p>";
        }
        
        $words_data_medium = json_decode($puzzle_medium['words_data'], true);
        if ($words_data_medium) {
            echo "<p><strong>単語数:</strong> " . count($words_data_medium) . "個</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ 中程度の日本語パズルの生成に失敗しました</p>";
    }
    
    echo "<hr>";
    
    echo "<h2>3. カスタム単語でのパズル生成</h2>";
    
    // カスタム単語リストでパズルを生成
    $custom_words = array('さくら', 'もみじ', 'うめ', 'たんぽぽ', 'ひまわり', 'チューリップ', 'ばら', 'ゆり');
    $custom_puzzle = $generator->generate_from_custom_words($custom_words, 8);
    
    if ($custom_puzzle) {
        echo "<p style='color: green;'>✓ カスタム単語でのパズル生成に成功しました</p>";
        
        $grid_data_custom = json_decode($custom_puzzle['grid_data'], true);
        if ($grid_data_custom) {
            echo "<p><strong>グリッドサイズ:</strong> " . $grid_data_custom['size'] . "×" . $grid_data_custom['size'] . "</p>";
        }
        
        $words_data_custom = json_decode($custom_puzzle['words_data'], true);
        if ($words_data_custom) {
            echo "<p><strong>単語数:</strong> " . count($words_data_custom) . "個</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ カスタム単語でのパズル生成に失敗しました</p>";
    }
    
    echo "<hr>";
    
    echo "<h2>4. パズルの品質評価</h2>";
    
    if (isset($puzzle) && $puzzle) {
        $words_data = json_decode($puzzle['words_data'], true);
        if ($words_data) {
            $score = $generator->evaluate_puzzle($words_data);
            echo "<p><strong>パズルの品質スコア:</strong> " . $score . "/100</p>";
            
            if ($score >= 80) {
                echo "<p style='color: green;'>✓ 高品質なパズルです</p>";
            } elseif ($score >= 60) {
                echo "<p style='color: orange;'>○ 良好なパズルです</p>";
            } else {
                echo "<p style='color: red;'>△ 改善の余地があります</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
    echo "<p><strong>スタックトレース:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>✗ エラーが発生しました: " . $e->getMessage() . "</p>";
    echo "<p><strong>スタックトレース:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>5. 使用方法</h2>";
echo "<p>WordPress環境で以下のショートコードを使用してパズルを表示できます：</p>";
echo "<ul>";
echo "<li><code>[crossword id=\"1\"]</code> - 既存のパズルを表示</li>";
echo "<li><code>[crossword id=\"2\"]</code> - 既存のパズルを表示</li>";
echo "</ul>";

echo "<p><strong>注意:</strong> 自動生成されたパズルをデータベースに保存するには、管理画面から手動で追加するか、APIエンドポイントを作成する必要があります。</p>";
