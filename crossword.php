<?php
/**
 * Plugin Name: Crossword Game
 * Plugin URI: https://example.com/crossword-game
 * Description: インタラクティブなクロスワードゲームを提供するWordPressプラグインです。
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: crossword-game
 * Domain Path: /languages
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

// プラグインの定数定義
define('CROSSWORD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CROSSWORD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CROSSWORD_PLUGIN_VERSION', '1.0.0');

// プラグインの初期化
function crossword_plugin_init() {
    // テキストドメインの読み込み
    load_plugin_textdomain('crossword-game', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'crossword_plugin_init');

// プラグインの有効化時の処理
function crossword_plugin_activate() {
    error_log('Crossword plugin: Activation started');
    
    try {
        // データベーステーブルの作成
        crossword_create_tables();
        error_log('Crossword plugin: Tables created successfully');
        
        // デフォルトのパズルデータを挿入
        crossword_insert_default_puzzle();
        error_log('Crossword plugin: Default puzzle inserted successfully');
        
        // リライトルールのフラッシュ
        flush_rewrite_rules();
        error_log('Crossword plugin: Rewrite rules flushed');
        
        error_log('Crossword plugin: Activation completed successfully');
    } catch (Exception $e) {
        error_log('Crossword plugin activation error: ' . $e->getMessage());
    } catch (Error $e) {
        error_log('Crossword plugin activation fatal error: ' . $e->getMessage());
    }
}
register_activation_hook(__FILE__, 'crossword_plugin_activate');

// プラグインの無効化時の処理
function crossword_plugin_deactivate() {
    // リライトルールのフラッシュ
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'crossword_plugin_deactivate');

// データベーステーブルの作成
function crossword_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // パズルテーブル
    $table_puzzles = $wpdb->prefix . 'crossword_puzzles';
    $sql_puzzles = "CREATE TABLE $table_puzzles (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        description text,
        difficulty enum('easy', 'medium', 'hard') DEFAULT 'medium',
        grid_data longtext NOT NULL,
        words_data longtext NOT NULL,
        hints_data longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    // ユーザー進捗テーブル
    $table_progress = $wpdb->prefix . 'crossword_progress';
    $sql_progress = "CREATE TABLE $table_progress (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        puzzle_id mediumint(9) NOT NULL,
        progress_data longtext NOT NULL,
        completed tinyint(1) DEFAULT 0,
        time_spent int(11) DEFAULT 0,
        started_at datetime DEFAULT CURRENT_TIMESTAMP,
        completed_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY user_puzzle (user_id, puzzle_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // エラーハンドリングを追加
    $result_puzzles = dbDelta($sql_puzzles);
    $result_progress = dbDelta($sql_progress);
    
    // エラーログの記録
    if (is_wp_error($result_puzzles) || is_wp_error($result_progress)) {
        error_log('Crossword plugin: Database table creation failed');
        if (is_wp_error($result_puzzles)) {
            error_log('Puzzles table error: ' . $result_puzzles->get_error_message());
        }
        if (is_wp_error($result_progress)) {
            error_log('Progress table error: ' . $result_progress->get_error_message());
        }
    }
}

// デフォルトのパズルデータを挿入
function crossword_insert_default_puzzle() {
    global $wpdb;
    
    $table_puzzles = $wpdb->prefix . 'crossword_puzzles';
    
    // テーブルが存在するかチェック
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_puzzles'") != $table_puzzles) {
        error_log('Crossword plugin: Puzzles table does not exist');
        return;
    }
    
    // 既存のデータがあるかチェック
    $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table_puzzles");
    if ($existing > 0) {
        return;
    }
    
    // サンプルパズルのデータ
    $sample_puzzle = array(
        'title' => 'サンプルクロスワード',
        'description' => '基本的な単語を使ったクロスワードパズルです。',
        'difficulty' => 'easy',
        'grid_data' => json_encode(array(
            'size' => 5,
            'grid' => array(
                array('H', 'E', 'L', 'L', 'O'),
                array('E', 'A', 'P', 'P', 'L'),
                array('L', 'P', 'L', 'E', 'E'),
                array('L', 'P', 'E', 'A', 'R'),
                array('O', 'E', 'E', 'R', 'S')
            )
        )),
        'words_data' => json_encode(array(
            'HELLO' => array('row' => 0, 'col' => 0, 'direction' => 'horizontal'),
            'APPLE' => array('row' => 1, 'col' => 1, 'direction' => 'horizontal'),
            'PEAR' => array('row' => 3, 'col' => 1, 'direction' => 'horizontal'),
            'HELP' => array('row' => 0, 'col' => 0, 'direction' => 'vertical'),
            'EARS' => array('row' => 0, 'col' => 4, 'direction' => 'vertical')
        )),
        'hints_data' => json_encode(array(
            'HELLO' => '挨拶の言葉',
            'APPLE' => '赤い果物',
            'PEAR' => '洋ナシ',
            'HELP' => '助ける',
            'EARS' => '聞くための器官'
        ))
    );
    
    $result = $wpdb->insert($table_puzzles, $sample_puzzle);
    
    if ($result === false) {
        error_log('Crossword plugin: Failed to insert default puzzle: ' . $wpdb->last_error);
    }
}

// 必要なファイルを読み込み
require_once CROSSWORD_PLUGIN_PATH . 'includes/class-crossword-game.php';
require_once CROSSWORD_PLUGIN_PATH . 'includes/class-crossword-admin.php';
require_once CROSSWORD_PLUGIN_PATH . 'includes/class-crossword-generator.php';

// プラグインの初期化
function crossword_init() {
    error_log('Crossword plugin: Initialization started');
    
    try {
        // ゲームクラスの初期化
        error_log('Crossword plugin: Initializing Crossword_Game class');
        new Crossword_Game();
        error_log('Crossword plugin: Crossword_Game class initialized successfully');
        
        // 管理画面クラスの初期化
        if (is_admin()) {
            error_log('Crossword plugin: Initializing Crossword_Admin class');
            new Crossword_Admin();
            error_log('Crossword plugin: Crossword_Admin class initialized successfully');
        }
        
        error_log('Crossword plugin: Initialization completed successfully');
    } catch (Exception $e) {
        error_log('Crossword plugin initialization error: ' . $e->getMessage());
        error_log('Crossword plugin initialization error trace: ' . $e->getTraceAsString());
    } catch (Error $e) {
        error_log('Crossword plugin fatal error: ' . $e->getMessage());
        error_log('Crossword plugin fatal error trace: ' . $e->getTraceAsString());
    }
}
add_action('plugins_loaded', 'crossword_init');
