<?php
/**
 * クロスワードパズル生成のテストスクリプト
 * プラグインの外部でパズル生成機能をテストできます
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>クロスワードパズル生成テスト</h1>";

// ファイルの存在確認
if (!file_exists('includes/class-crossword-generator.php')) {
    echo "<p style='color: red;'>✗ Crossword_Generatorクラスファイルが見つかりません</p>";
    exit;
}

// クラスファイルの読み込み
echo "<p>Crossword_Generatorクラスを読み込み中...</p>";
require_once 'includes/class-crossword-generator.php';

try {
    echo "<p style='color: green;'>✓ Crossword_Generatorクラスの読み込み成功</p>";
    
    // パズル生成器のインスタンス化
    echo "<p>パズル生成器を初期化中...</p>";
    $generator = new Crossword_Generator();
    echo "<p style='color: green;'>✓ パズル生成器の初期化成功</p>";
    
    // 簡単なパズルの生成テスト
    echo "<h2>簡単なパズルの生成テスト</h2>";
    echo "<p>難易度: easy, サイズ: 5, 単語数: 5</p>";
    
    $puzzle_data = $generator->generate_puzzle('easy', 5, 5);
    
    if ($puzzle_data) {
        echo "<p style='color: green;'>✓ パズル生成成功</p>";
        
        // 生成されたデータの表示
        echo "<h3>生成されたパズルデータ:</h3>";
        echo "<pre>";
        print_r($puzzle_data);
        echo "</pre>";
        
        // グリッドデータの解析
        $grid_data = json_decode($puzzle_data['grid_data'], true);
        $words_data = json_decode($puzzle_data['words_data'], true);
        $hints_data = json_decode($puzzle_data['hints_data'], true);
        
        echo "<h3>パズルの詳細:</h3>";
        echo "<p>グリッドサイズ: " . $grid_data['size'] . "×" . $grid_data['size'] . "</p>";
        echo "<p>配置された単語数: " . count($words_data) . "</p>";
        
        // グリッドの表示
        echo "<h3>生成されたグリッド:</h3>";
        echo "<div style='font-family: monospace; font-size: 16px;'>";
        for ($row = 0; $row < $grid_data['size']; $row++) {
            echo "<div>";
            for ($col = 0; $col < $grid_data['size']; $col++) {
                $cell = $grid_data['grid'][$row][$col];
                if ($cell === '') {
                    echo "<span style='background: #333; color: #333; padding: 2px; margin: 1px; display: inline-block; width: 20px; height: 20px; text-align: center;'>.</span>";
                } else {
                    echo "<span style='background: #fff; border: 1px solid #ccc; padding: 2px; margin: 1px; display: inline-block; width: 20px; height: 20px; text-align: center;'>$cell</span>";
                }
            }
            echo "</div>";
        }
        echo "</div>";
        
        // 単語とヒントの表示
        echo "<h3>配置された単語とヒント:</h3>";
        echo "<ul>";
        foreach ($words_data as $word => $data) {
            $hint = $hints_data[$word];
            echo "<li><strong>$word</strong> ($data[direction]) - $hint</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>✗ パズル生成失敗</p>";
    }
    
    // より良いパズルの生成テスト
    echo "<h2>より良いパズルの生成テスト</h2>";
    echo "<p>難易度: medium, サイズ: 8, 単語数: 8, 試行回数: 3</p>";
    
    $best_puzzle = $generator->generate_best_puzzle('medium', 8, 8, 3);
    
    if ($best_puzzle) {
        echo "<p style='color: green;'>✓ より良いパズル生成成功</p>";
        
        $best_grid_data = json_decode($best_puzzle['grid_data'], true);
        $best_words_data = json_decode($best_puzzle['words_data'], true);
        
        echo "<p>最適化されたグリッドサイズ: " . $best_grid_data['size'] . "×" . $best_grid_data['size'] . "</p>";
        echo "<p>最適化された単語数: " . count($best_words_data) . "</p>";
        
    } else {
        echo "<p style='color: red;'>✗ より良いパズル生成失敗</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 例外が発生しました: " . $e->getMessage() . "</p>";
    echo "<p>スタックトレース:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>✗ 致命的なエラーが発生しました: " . $e->getMessage() . "</p>";
    echo "<p>スタックトレース:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>テスト完了</strong></p>";
echo "<p>このページでエラーが表示されていない場合は、パズル生成機能は正常に動作しています。</p>";
echo "<p>問題が続く場合は、WordPress管理画面でプラグインを再有効化してください。</p>";
?>
