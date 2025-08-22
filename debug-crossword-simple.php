<?php
/**
 * クロスワードプラグインのシンプルデバッグスクリプト
 * メモリ使用量を削減して、基本的な動作確認を行います
 */

// エラー表示を有効化
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>クロスワードプラグインシンプルデバッグ</h1>";

// 基本的なPHP情報
echo "<h2>PHP情報</h2>";
echo "<p>PHP バージョン: " . phpversion() . "</p>";
echo "<p>エラー表示: " . (ini_get('display_errors') ? '有効' : '無効') . "</p>";
echo "<p>メモリ制限: " . ini_get('memory_limit') . "</p>";

// ファイルの存在確認
echo "<h2>ファイル存在確認</h2>";
$files = [
    'crossword.php',
    'includes/class-crossword-generator.php',
    'includes/class-crossword-admin.php',
    'includes/class-crossword-game.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ {$file} - 存在します</p>";
    } else {
        echo "<p style='color: red;'>✗ {$file} - 存在しません</p>";
    }
}

// 基本的なクラス読み込みテスト
echo "<h2>クラス読み込みテスト</h2>";
try {
    // メインファイルの読み込み
    if (file_exists('crossword.php')) {
        echo "<p>メインファイルの読み込みテスト...</p>";
        
        // ファイルの内容を確認（最初の数行のみ）
        $content = file_get_contents('crossword.php');
        $lines = explode("\n", $content);
        echo "<p>ファイルサイズ: " . strlen($content) . " バイト</p>";
        echo "<p>行数: " . count($lines) . " 行</p>";
        
        echo "<p style='color: green;'>✓ メインファイルの読み込み成功</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ メインファイルの読み込みエラー: " . $e->getMessage() . "</p>";
}

// クラスファイルの基本的な確認
echo "<h2>クラスファイルの基本確認</h2>";
$classes = [
    'includes/class-crossword-generator.php' => 'Crossword_Generator',
    'includes/class-crossword-admin.php' => 'Crossword_Admin',
    'includes/class-crossword-game.php' => 'Crossword_Game'
];

foreach ($classes as $file => $className) {
    try {
        if (file_exists($file)) {
            // ファイルの内容を確認（最初の数行のみ）
            $content = file_get_contents($file, false, null, 0, 1000);
            
            // クラス名の存在確認
            if (strpos($content, "class $className") !== false) {
                echo "<p style='color: green;'>✓ {$className} - クラス定義が見つかりました</p>";
            } else {
                echo "<p style='color: orange;'>⚠ {$className} - クラス定義が見つかりません</p>";
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
echo "<p>現在のメモリ使用量: " . memory_get_usage(true) . " バイト</p>";
echo "<p>ピークメモリ使用量: " . memory_get_peak_usage(true) . " バイト</p>";

// エラーログの簡単な確認
echo "<h2>エラーログ確認</h2>";
$logFile = '../../debug.log';
if (file_exists($logFile)) {
    echo "<p>WordPressデバッグログが存在します</p>";
    
    // ファイルサイズを確認
    $logSize = filesize($logFile);
    echo "<p>ログファイルサイズ: " . number_format($logSize) . " バイト</p>";
    
    if ($logSize > 10485760) { // 10MB以上
        echo "<p style='color: orange;'>⚠ ログファイルが大きすぎます（10MB以上）</p>";
        echo "<p>メモリ不足の原因となる可能性があります</p>";
    }
    
    // クロスワード関連のログを検索（最後の数行のみ）
    $logContent = tail($logFile, 100);
    $crosswordLogs = [];
    
    $lines = explode("\n", $logContent);
    foreach ($lines as $line) {
        if (stripos($line, 'crossword') !== false) {
            $crosswordLogs[] = $line;
        }
    }
    
    if (!empty($crosswordLogs)) {
        echo "<h3>クロスワード関連のログ（最新" . count($crosswordLogs) . "件）:</h3>";
        echo "<pre>";
        foreach ($crosswordLogs as $log) {
            echo htmlspecialchars($log) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p>クロスワード関連のログは見つかりませんでした</p>";
    }
} else {
    echo "<p>WordPressデバッグログが見つかりません</p>";
}

// ファイルの最後の数行を取得する関数
function tail($filename, $lines = 10) {
    $file = new SplFileObject($filename);
    $file->seek(PHP_INT_MAX);
    $total_lines = $file->key();
    
    $file->seek(max(0, $total_lines - $lines));
    $result = '';
    while (!$file->eof()) {
        $result .= $file->current();
        $file->next();
    }
    
    return $result;
}

echo "<hr>";
echo "<p><strong>シンプルデバッグ完了</strong></p>";
echo "<p>このページでエラーが表示されていない場合は、基本的なファイル構造は正常です。</p>";
echo "<p>問題が続く場合は、WordPress管理画面でプラグインを再有効化してください。</p>";
?>
