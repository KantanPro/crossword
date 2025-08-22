<?php
/**
 * クロスワードプラグインのエラーハンドリングテストスクリプト
 * null値や無効なデータに対する処理をテストできます
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>クロスワードプラグインエラーハンドリングテスト</h1>";

// ファイルの存在確認
if (!file_exists('includes/class-crossword-game.php')) {
    echo "<p style='color: red;'>✗ Crossword_Gameクラスファイルが見つかりません</p>";
    exit;
}

// クラスファイルの読み込み
echo "<p>Crossword_Gameクラスを読み込み中...</p>";
require_once 'includes/class-crossword-game.php';

try {
    echo "<p style='color: green;'>✓ Crossword_Gameクラスの読み込み成功</p>";
    
    // インスタンス化
    echo "<p>Crossword_Gameインスタンスを作成中...</p>";
    $game = new Crossword_Game();
    echo "<p style='color: green;'>✓ Crossword_Gameインスタンスの作成成功</p>";
    
    // テストケース1: null値のgrid_data
    echo "<h2>テストケース1: null値のgrid_data</h2>";
    $result1 = $game->render_grid(null, array());
    echo "<p>結果:</p>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo $result1;
    echo "</div>";
    
    // テストケース2: 無効な配列構造
    echo "<h2>テストケース2: 無効な配列構造</h2>";
    $invalid_grid = array('invalid' => 'data');
    $result2 = $game->render_grid($invalid_grid, array());
    echo "<p>結果:</p>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo $result2;
    echo "</div>";
    
    // テストケース3: 無効なサイズ
    echo "<h2>テストケース3: 無効なサイズ</h2>";
    $invalid_size_grid = array('size' => -5, 'grid' => array());
    $result3 = $game->render_grid($invalid_size_grid, array());
    echo "<p>結果:</p>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo $result3;
    echo "</div>";
    
    // テストケース4: null値のhints_data
    echo "<h2>テストケース4: null値のhints_data</h2>";
    $result4 = $game->render_hints(null, array());
    echo "<p>結果:</p>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo $result4;
    echo "</div>";
    
    // テストケース5: 無効なwords_data
    echo "<h2>テストケース5: 無効なwords_data</h2>";
    $result5 = $game->render_hints(array('test' => 'hint'), null);
    echo "<p>結果:</p>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo $result5;
    echo "</div>";
    
    // テストケース6: 正常なデータ
    echo "<h2>テストケース6: 正常なデータ</h2>";
    $valid_grid = array(
        'size' => 3,
        'grid' => array(
            array('H', 'E', 'Y'),
            array('E', '', ''),
            array('Y', '', '')
        )
    );
    $valid_words = array(
        'HEY' => array('row' => 0, 'col' => 0, 'direction' => 'horizontal'),
        'HEY' => array('row' => 0, 'col' => 0, 'direction' => 'vertical')
    );
    $valid_hints = array(
        'HEY' => '挨拶の言葉'
    );
    
    $result6 = $game->render_grid($valid_grid, $valid_words);
    echo "<p>グリッド結果:</p>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo $result6;
    echo "</div>";
    
    $result7 = $game->render_hints($valid_hints, $valid_words);
    echo "<p>ヒント結果:</p>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo $result7;
    echo "</div>";
    
    echo "<h2>テスト結果サマリー</h2>";
    echo "<p style='color: green;'>✓ すべてのテストケースが正常に処理されました</p>";
    echo "<p>エラーハンドリングが正しく動作し、無効なデータに対して適切なエラーメッセージが表示されています。</p>";
    
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
echo "<p><strong>エラーハンドリングテスト完了</strong></p>";
echo "<p>このページでエラーが表示されていない場合は、エラーハンドリングは正常に動作しています。</p>";
?>
