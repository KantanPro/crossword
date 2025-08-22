<?php
/**
 * Plugin Name:       Crossword Game
 * Plugin URI:        https://example.com/crossword-game
 * Description:       シンプルなクロスワードパズルゲームをWordPressに追加します。[crossword]ショートコードで使用できます。
 * Version:           1.0.0
 * Requires at least: 5.0
 * Tested up to:     6.4
 * Requires PHP:      7.4
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       crossword-game
 * Domain Path:       /languages
 * Network:           false
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'CROSSWORD_GAME_VERSION', '1.0.2' );
define( 'CROSSWORD_GAME_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CROSSWORD_GAME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * プラグインの初期化
 */
function crossword_game_init() {
    // テキストドメインの読み込み
    load_plugin_textdomain( 'crossword-game', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'crossword_game_init' );

/**
 * スクリプトとスタイルの読み込み
 */
function crossword_game_enqueue_scripts() {
    // フロントエンドでのみ読み込み
    if ( ! is_admin() ) {
        wp_enqueue_script( 
            'crossword-game-js', 
            CROSSWORD_GAME_PLUGIN_URL . 'crossword-game.js', 
            array('jquery'), 
            CROSSWORD_GAME_VERSION, 
            true 
        );
        
        wp_enqueue_style( 
            'crossword-game-css', 
            CROSSWORD_GAME_PLUGIN_URL . 'crossword-game.css', 
            array(), 
            CROSSWORD_GAME_VERSION 
        );

        // パズルデータをJavaScriptに渡す
        $words = array(
            'WORDPRESS', 'PLUGIN', 'THEME', 'POST', 'PAGE', 'WIDGET', 'EDITOR',
            'ADMIN', 'USER', 'MEDIA', 'DATABASE', 'SERVER', 'HOSTING', 'UPDATES',
            'CUSTOMIZE', 'SETTINGS', 'SHORTCODE', 'BLOG', 'COMMENT', 'CATEGORY'
        );
        
        wp_localize_script( 'crossword-game-js', 'crossword_data', array(
            'words' => $words,
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'crossword_game_nonce' ),
        ));
    }
}
add_action( 'wp_enqueue_scripts', 'crossword_game_enqueue_scripts' );

/**
 * ショートコード関数：クロスワードゲームを表示
 */
function crossword_game_shortcode( $atts ) {
    // ショートコードの属性を解析
    $atts = shortcode_atts( array(
        'size' => '10',
        'max_words' => '8',
    ), $atts, 'crossword' );
    
    // 出力バッファリング
    ob_start();
    ?>
    <div id="crossword-container" class="crossword-game-container">
        <h3><?php esc_html_e( 'クロスワードパズル', 'crossword-game' ); ?></h3>
        <div id="crossword-grid"></div>
        <div id="crossword-controls">
            <button id="new-game-btn" class="crossword-btn"><?php esc_html_e( '新規問題', 'crossword-game' ); ?></button>
            <button id="solve-btn" class="crossword-btn"><?php esc_html_e( '答えを見る', 'crossword-game' ); ?></button>
        </div>
        <div id="crossword-message"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'crossword', 'crossword_game_shortcode' );

/**
 * プラグインのアクティベーション時の処理
 */
function crossword_game_activate() {
    // 必要に応じてデータベーステーブルの作成やオプションの設定
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'crossword_game_activate' );

/**
 * プラグインのデアクティベーション時の処理
 */
function crossword_game_deactivate() {
    // 必要に応じてクリーンアップ処理
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'crossword_game_deactivate' );

/**
 * プラグインのアンインストール時の処理
 */
function crossword_game_uninstall() {
    // 必要に応じてデータベースのクリーンアップ
}
register_uninstall_hook( __FILE__, 'crossword_game_uninstall' );

/**
 * 管理画面にメニューを追加（オプション）
 */
function crossword_game_admin_menu() {
    add_options_page(
        __( 'クロスワードゲーム設定', 'crossword-game' ),
        __( 'クロスワードゲーム', 'crossword-game' ),
        'manage_options',
        'crossword-game-settings',
        'crossword_game_settings_page'
    );
}
add_action( 'admin_menu', 'crossword_game_admin_menu' );

/**
 * 設定ページの表示
 */
function crossword_game_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p><?php esc_html_e( 'クロスワードゲームの設定を行います。', 'crossword-game' ); ?></p>
        <p><?php esc_html_e( 'ショートコード [crossword] を使用してゲームを表示できます。', 'crossword-game' ); ?></p>
    </div>
    <?php
}

/**
 * プラグインの情報を表示
 */
function crossword_game_plugin_links( $links ) {
    $plugin_links = array(
        '<a href="' . admin_url( 'options-general.php?page=crossword-game-settings' ) . '">' . __( '設定', 'crossword-game' ) . '</a>',
    );
    return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'crossword_game_plugin_links' );
