<?php
/**
 * クロスワードプラグインのデバッグスクリプト
 * このファイルをブラウザで直接実行して、プラグインの動作をテストできます
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>クロスワードプラグインデバッグ</h1>";

// 基本的なPHP情報
echo "<h2>PHP情報</h2>";
echo "<p>PHP バージョン: " . phpversion() . "</p>";
echo "<p>エラー表示: " . (ini_get('display_errors') ? '有効' : '無効') . "</p>";

// ファイルの存在確認
echo "<h2>ファイル存在確認</h2>";
$files = [
    'crossword.php',
    'includes/class-crossword-generator.php',
    'includes/class-crossword-admin.php',
    'includes/class-crossword-game.php',
    'assets/css/crossword-style.css',
    'assets/js/crossword-game.js',
    'assets/js/crossword-admin.js'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ {$file} - 存在します</p>";
    } else {
        echo "<p style='color: red;'>✗ {$file} - 存在しません</p>";
    }
}

// ファイルの読み込みテスト
echo "<h2>ファイル読み込みテスト</h2>";
try {
    // メインファイルの読み込み
    if (file_exists('crossword.php')) {
        echo "<p>メインファイルの読み込みテスト...</p>";
        
        // ファイルの内容を確認（最初の数行のみ）
        $content = file_get_contents('crossword.php');
        $lines = explode("\n", $content);
        echo "<p>ファイルサイズ: " . strlen($content) . " バイト</p>";
        echo "<p>行数: " . count($lines) . " 行</p>";
        
        // 最初の10行を表示
        echo "<h3>ファイルの最初の10行:</h3>";
        echo "<pre>";
        for ($i = 0; $i < min(10, count($lines)); $i++) {
            echo htmlspecialchars(($i + 1) . ": " . $lines[$i]) . "\n";
        }
        echo "</pre>";
        
        echo "<p style='color: green;'>✓ メインファイルの読み込み成功</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ メインファイルの読み込みエラー: " . $e->getMessage() . "</p>";
}

// クラスファイルの読み込みテスト
echo "<h2>クラスファイルの読み込みテスト</h2>";
$classes = [
    'includes/class-crossword-generator.php' => 'Crossword_Generator',
    'includes/class-crossword-admin.php' => 'Crossword_Admin',
    'includes/class-crossword-game.php' => 'Crossword_Game'
];

foreach ($classes as $file => $className) {
    try {
        if (file_exists($file)) {
            // ファイルの内容を確認
            $content = file_get_contents($file);
            
            // クラス名の存在確認
            if (strpos($content, "class $className") !== false) {
                echo "<p style='color: green;'>✓ {$className} - クラス定義が見つかりました</p>";
            } else {
                echo "<p style='color: orange;'>⚠ {$className} - クラス定義が見つかりません</p>";
            }
            
            // 構文チェック
            $tokens = token_get_all($content);
            if ($tokens !== false) {
                echo "<p style='color: green;'>✓ {$className} - 構文チェック成功</p>";
            } else {
                echo "<p style='color: red;'>✗ {$className} - 構文エラーがあります</p>";
            }
            
        } else {
            echo "<p style='color: red;'>✗ {$className} - ファイルが存在しません</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ {$className} - エラー: " . $e->getMessage() . "</p>";
    }
}

// メモリ使用量の確認
echo "<h2>システム情報</h2>";
echo "<p>メモリ制限: " . ini_get('memory_limit') . "</p>";
echo "<p>現在のメモリ使用量: " . memory_get_usage(true) . " バイト</p>";
echo "<p>ピークメモリ使用量: " . memory_get_peak_usage(true) . " バイト</p>";

// 実行時間の確認
echo "<p>実行時間: " . microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"] . " 秒</p>";

// エラーログの確認
echo "<h2>エラーログ確認</h2>";
$logFile = '../../debug.log';
if (file_exists($logFile)) {
    echo "<p>WordPressデバッグログが存在します</p>";
    
    // クロスワード関連のログを検索
    $logContent = file_get_contents($logFile);
    $crosswordLogs = [];
    
    $lines = explode("\n", $logContent);
    foreach ($lines as $line) {
        if (stripos($line, 'crossword') !== false) {
            $crosswordLogs[] = $line;
        }
    }
    
    if (!empty($crosswordLogs)) {
        echo "<h3>クロスワード関連のログ（最新10件）:</h3>";
        echo "<pre>";
        $recentLogs = array_slice($crosswordLogs, -10);
        foreach ($recentLogs as $log) {
            echo htmlspecialchars($log) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p>クロスワード関連のログは見つかりませんでした</p>";
    }
} else {
    echo "<p>WordPressデバッグログが見つかりません</p>";
}

echo "<hr>";
echo "<p><strong>デバッグ完了</strong></p>";
echo "<p>このページでエラーが表示されていない場合は、基本的なファイル構造は正常です。</p>";
echo "<p>問題が続く場合は、WordPress管理画面でプラグインを再有効化してください。</p>";
?>
