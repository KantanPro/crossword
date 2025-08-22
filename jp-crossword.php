<?php
/**
 * Plugin Name: Japanese Crossword Generator
 * Plugin URI: https://example.com/jp-crossword
 * Description: 日本語のクロスワード（文字埋め）を自動生成して表示するプラグイン。ショートコードは [crossword] 。「新規問題」と「ギブアップ（答えを表示）」ボタンをフロントに設置。
 * Version: 1.0.0
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: jp-crossword
 * Domain Path: /languages
 * Network: false
 */

// 直接アクセスを防ぐ
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// プラグインの定数定義
define( 'JPCW_VERSION', '1.0.0' );
define( 'JPCW_PLUGIN_FILE', __FILE__ );
define( 'JPCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JPCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * メインクラス
 */
class JPCrosswordGenerator {
	const HANDLE = 'jp-crossword';

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * フックの初期化
	 */
	private function init_hooks() {
		// プラグインのアクティベーション/デアクティベーション
		register_activation_hook( JPCW_PLUGIN_FILE, [ $this, 'activate' ] );
		register_deactivation_hook( JPCW_PLUGIN_FILE, [ $this, 'deactivate' ] );

		// 初期化
		add_action( 'init', [ $this, 'init' ] );
		
		// ショートコード
		add_shortcode( 'crossword', [ $this, 'shortcode' ] );
		
		// アセット読み込み
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		
		// AJAX処理
		add_action( 'wp_ajax_jpcw_generate', [ $this, 'ajax_generate' ] );
		add_action( 'wp_ajax_nopriv_jpcw_generate', [ $this, 'ajax_generate' ] );
		
		// 管理画面
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * プラグイン初期化
	 */
	public function init() {
		// テキストドメインの読み込み
		load_plugin_textdomain( 'jp-crossword', false, dirname( plugin_basename( JPCW_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * プラグインアクティベーション
	 */
	public function activate() {
		// 必要な権限の確認
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// オプションの初期化
		add_option( 'jpcw_version', JPCW_VERSION );
		add_option( 'jpcw_settings', [
			'default_size' => 10,
			'max_size' => 16,
			'min_size' => 6,
		] );

		// データベーステーブルの作成（必要に応じて）
		$this->create_tables();

		// リライトルールのフラッシュ
		flush_rewrite_rules();
	}

	/**
	 * プラグインデアクティベーション
	 */
	public function deactivate() {
		// リライトルールのフラッシュ
		flush_rewrite_rules();
	}

	/**
	 * データベーステーブルの作成
	 */
	private function create_tables() {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		// 必要に応じてテーブルを作成
		// 現在は使用していないが、将来的な拡張のために準備
	}

	/**
	 * 管理メニューの追加
	 */
	public function admin_menu() {
		add_options_page(
			__( 'Japanese Crossword Settings', 'jp-crossword' ),
			__( 'Japanese Crossword', 'jp-crossword' ),
			'manage_options',
			'jp-crossword',
			[ $this, 'admin_page' ]
		);
	}

	/**
	 * 管理画面の初期化
	 */
	public function admin_init() {
		register_setting( 'jpcw_settings', 'jpcw_settings' );
	}

	/**
	 * 管理画面の表示
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Japanese Crossword Settings', 'jp-crossword' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'jpcw_settings' );
				do_settings_sections( 'jpcw_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * ショートコード出力
	 */
	public function shortcode( $atts ) {
		// ショートコード属性の処理
		$atts = shortcode_atts( [
			'size' => 10,
			'id'   => '',
		], $atts, 'crossword' );

		// サイズの検証
		$settings = get_option( 'jpcw_settings', [] );
		$min_size = isset( $settings['min_size'] ) ? $settings['min_size'] : 6;
		$max_size = isset( $settings['max_size'] ) ? $settings['max_size'] : 16;
		$size = max( $min_size, min( $max_size, intval( $atts['size'] ) ) );

		// ユニークIDの生成
		$id = ! empty( $atts['id'] ) ? sanitize_html_class( $atts['id'] ) : 'jpcw-' . uniqid();

		ob_start();
		?>
		<div class="jpcw-wrapper" id="<?php echo esc_attr( $id ); ?>" data-size="<?php echo esc_attr( $size ); ?>">
			<div class="jpcw-controls">
				<button type="button" class="jpcw-new button button-primary">
					<?php _e( '新規問題', 'jp-crossword' ); ?>
				</button>
				<button type="button" class="jpcw-giveup button button-secondary">
					<?php _e( 'ギブアップ（答えを表示）', 'jp-crossword' ); ?>
				</button>
			</div>
			<div class="jpcw-board" aria-live="polite"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * アセット読み込み
	 */
	public function enqueue_assets() {
		// フロントエンドでのみ読み込み
		if ( is_admin() ) {
			return;
		}

		$base = JPCW_PLUGIN_URL;
		$ver  = JPCW_VERSION;

		// CSSの読み込み
		wp_enqueue_style( 
			self::HANDLE, 
			$base . 'assets/jpcw.css', 
			[], 
			$ver 
		);

		// JavaScriptの読み込み
		wp_enqueue_script( 
			self::HANDLE, 
			$base . 'assets/jpcw.js', 
			[ 'jquery' ], 
			$ver, 
			true 
		);

		// ローカライゼーション
		wp_localize_script( self::HANDLE, 'JPCW', [
			'ajax'  => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'jpcw_nonce' ),
			'i18n'  => [
				'loading' => __( '問題を生成中…', 'jp-crossword' ),
				'error'   => __( '生成に失敗しました。再度お試しください。', 'jp-crossword' ),
				'new'     => __( '新規問題', 'jp-crossword' ),
				'giveup'  => __( 'ギブアップ（答えを表示）', 'jp-crossword' ),
			],
		] );
	}

	/**
	 * AJAX: 問題生成
	 */
	public function ajax_generate() {
		// セキュリティチェック
		if ( ! check_ajax_referer( 'jpcw_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'セキュリティチェックに失敗しました。', 'jp-crossword' ) ] );
		}

		// サイズの検証
		$settings = get_option( 'jpcw_settings', [] );
		$min_size = isset( $settings['min_size'] ) ? $settings['min_size'] : 6;
		$max_size = isset( $settings['max_size'] ) ? $settings['max_size'] : 16;
		
		$size = isset( $_POST['size'] ) ? intval( $_POST['size'] ) : 10;
		$size = max( $min_size, min( $max_size, $size ) );

		// 問題生成
		$result = $this->generate_puzzle( $size );

		if ( empty( $result['grid'] ) ) {
			wp_send_json_error( [ 
				'message' => __( '生成できませんでした。', 'jp-crossword' ) 
			] );
		}

		wp_send_json_success( $result );
	}

	/**
	 * 日本語の単語リスト（かな中心）
	 * 必要に応じてフィルタで拡張可能：apply_filters( 'jpcw_dictionary', $words )
	 */
	private function dictionary() {
		$words = [
			'パズル','クロス','コタエ','ナゾナゾ','ゲーム','タイル','モジ','ゴマス','シロマス','クログリッド',
			'ネコ','イヌ','サクラ','ウミ','ヤマ','カゼ','アメ','ユキ','タイヨウ','ツキ',
			'ガクシュウ','ベンキョウ','ガッコウ','センセイ','セイト',
			'コンピュータ','プログラム','コーディング','データ','アルゴリズム',
			'ニホン','トウキョウ','オオサカ','キョウト','ホッカイドウ',
			'カンタン','ハヤイ','オソイ','タノシイ','ムズカシイ',
			'ミライ','キオク','システム','ネット','コード'
		];
		
		// 2〜8文字あたりを採用
		$words = array_values( array_filter( $words, function($w){ 
			$len = $this->mb_len($w); 
			return $len >= 2 && $len <= 8; 
		}) );
		
		return apply_filters( 'jpcw_dictionary', $words );
	}

	/** マルチバイト長 */
	private function mb_len( $str ) { return function_exists('mb_strlen') ? mb_strlen($str, 'UTF-8') : strlen($str); }
	/** マルチバイト分割 */
	private function mb_split( $str ) {
		if ( function_exists('mb_str_split') ) return mb_str_split( $str, 1, 'UTF-8' );
		$len = $this->mb_len($str); $out = [];
		for($i=0;$i<$len;$i++){ $out[] = mb_substr($str,$i,1,'UTF-8'); }
		return $out;
	}

	/**
	 * 簡易クロス配置アルゴリズム（スケルトン）
	 * - 1語目を横置き
	 * - 以降、既存語と共通文字で交差するよう縦/横に試行
	 * - 衝突しない場合のみ配置
	 * - 余白は黒マス
	 */
	private function generate_puzzle( $size = 10 ) {
		$grid = array_fill(0, $size, array_fill(0, $size, null));
		$placed = [];
		$words  = $this->dictionary();
		shuffle($words);

		$maxWords = 8; // 置き過ぎると詰むので控えめ

		if ( empty($words) ) return [ 'grid' => [], 'size' => $size ];

		// 1語目：横、中央付近
		$w1 = array_shift($words);
		$chars = $this->mb_split($w1);
		if (count($chars) > $size) { $chars = array_slice($chars, 0, $size); }
		$row = intval($size/2);
		$startCol = intval(($size - count($chars))/2);
		for($i=0;$i<count($chars);$i++){ $grid[$row][$startCol+$i] = $chars[$i]; }
		$placed[] = [ 'word'=>$w1, 'row'=>$row, 'col'=>$startCol, 'dir'=>'H', 'len'=>count($chars) ];

		// 以降の語を交差配置
		foreach( $words as $w ){
			if ( count($placed) >= $maxWords ) break;
			$chars = $this->mb_split($w);
			$placedOnce = false;

			// 既存語の各文字と共通文字を探す
			foreach( $placed as $p ){
				$pchars = $this->mb_split($p['word']);
				foreach( $pchars as $pi => $pc ){
					// 現在の語にこの文字が含まれているか
					$matches = array_keys( array_filter($chars, function($c) use ($pc){ return $c === $pc; }) );
					foreach( $matches as $mi ){
						if ( $p['dir'] === 'H' ) {
							// 新語は縦に置く
							$r0 = $p['row'] - $mi; // 新語の開始行
							$c0 = $p['col'] + $pi; // 交点の列
							if ( $this->fits($grid, $size, $r0, $c0, 'V', $chars) ) {
								$this->place($grid, $r0, $c0, 'V', $chars);
								$placed[] = [ 'word'=>$w, 'row'=>$r0, 'col'=>$c0, 'dir'=>'V', 'len'=>count($chars) ];
								$placedOnce = true; break 3;
							}
						} else {
							// 新語は横に置く
							$r0 = $p['row'] + $pi; // 交点の行
							$c0 = $p['col'] - $mi; // 新語の開始列
							if ( $this->fits($grid, $size, $r0, $c0, 'H', $chars) ) {
								$this->place($grid, $r0, $c0, 'H', $chars);
								$placed[] = [ 'word'=>$w, 'row'=>$r0, 'col'=>$c0, 'dir'=>'H', 'len'=>count($chars) ];
								$placedOnce = true; break 3;
							}
						}
					}
				}
			}
		}

		// 未使用マスは黒マスに
		for($r=0;$r<$size;$r++){
			for($c=0;$c<$size;$c++){
				if ($grid[$r][$c] === null) $grid[$r][$c] = '#';
			}
		}

		return [
			'size' => $size,
			'grid' => $grid, // 正解文字（# は黒マス）
			'placed' => $placed,
		];
	}

	/**
	 * 配置可能かチェック（交差位置は同文字ならOK／隣接の連結ルールは簡略化）
	 */
	private function fits(&$grid, $size, $r0, $c0, $dir, $chars){
		$len = count($chars);
		if ($dir==='H'){
			if ($c0 < 0 || $c0 + $len > $size || $r0 < 0 || $r0 >= $size) return false;
			for($i=0;$i<$len;$i++){
				$cell = $grid[$r0][$c0+$i];
				if ($cell !== null && $cell !== $chars[$i]) return false;
			}
		} else {
			if ($r0 < 0 || $r0 + $len > $size || $c0 < 0 || $c0 >= $size) return false;
			for($i=0;$i<$len;$i++){
				$cell = $grid[$r0+$i][$c0];
				if ($cell !== null && $cell !== $chars[$i]) return false;
			}
		}
		return true;
	}

	/** 配置実行 */
	private function place(&$grid, $r0, $c0, $dir, $chars){
		$len = count($chars);
		if ($dir==='H'){
			for($i=0;$i<$len;$i++){ $grid[$r0][$c0+$i] = $chars[$i]; }
		} else {
			for($i=0;$i<$len;$i++){ $grid[$r0+$i][$c0] = $chars[$i]; }
		}
	}
}

new JPCrosswordGenerator();

/**
 * プラグインのアンインストール処理
 */
register_uninstall_hook( __FILE__, 'jpcw_uninstall' );

function jpcw_uninstall() {
	// オプションの削除
	delete_option( 'jpcw_version' );
	delete_option( 'jpcw_settings' );
	
	// 必要に応じてデータベーステーブルの削除
	// 注意: ユーザーデータが含まれる場合は確認ダイアログを表示することを推奨
}