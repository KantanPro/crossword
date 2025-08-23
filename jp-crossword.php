<?php
/**
 * Plugin Name: Japanese Crossword Generator
 * Plugin URI: https://github.com/KantanPro/crossword
 * Description: 日本語のクロスワード（文字埋め）を自動生成して表示するプラグイン。ショートコードは [crossword] 。「新規問題」と「ギブアップ（答えを表示）」ボタンをフロントに設置。
 * Version: 1.0.1
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Author: KantanPro
 * Author URI: https://github.com/KantanPro
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: jp-crossword
 * Domain Path: /languages
 * Network: false
 * GitHub Plugin URI: KantanPro/crossword
 * Update URI: https://github.com/KantanPro/crossword
 */

// 直接アクセスを防ぐ
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// プラグインの定数定義
define( 'JPCW_VERSION', '1.0.1' );
define( 'JPCW_PLUGIN_FILE', __FILE__ );
define( 'JPCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JPCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'JPCW_GITHUB_REPO', 'KantanPro/crossword' );

/**
 * GitHubリリースチェッカークラス
 */
class JPCW_GitHub_Updater {
	private $plugin_slug;
	private $github_repo;
	private $plugin_file;
	private $github_response;
	private $access_token;

	public function __construct( $plugin_file, $github_repo ) {
		$this->plugin_file = $plugin_file;
		$this->github_repo = $github_repo;
		$this->plugin_slug = basename( dirname( $plugin_file ) );

		// GitHub Personal Access Token（設定可能）
		$this->access_token = get_option( 'jpcw_github_token', '' );

		// フックの追加
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
		add_filter( 'plugins_api', [ $this, 'plugin_info' ], 10, 3 );
		add_filter( 'upgrader_post_install', [ $this, 'upgrader_post_install' ], 10, 3 );
		
		// 管理画面での設定
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * 管理画面の初期化
	 */
	public function admin_init() {
		register_setting( 'jpcw_github_settings', 'jpcw_github_token' );
	}

	/**
	 * 管理メニューの追加
	 */
	public function admin_menu() {
		add_submenu_page(
			'options-general.php',
			__( 'GitHub Settings', 'jp-crossword' ),
			__( 'GitHub Settings', 'jp-crossword' ),
			'manage_options',
			'jp-crossword-github',
			[ $this, 'github_settings_page' ]
		);
	}

	/**
	 * GitHub設定ページ
	 */
	public function github_settings_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'GitHub Settings', 'jp-crossword' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'jpcw_github_settings' );
				?>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="jpcw_github_token"><?php _e( 'GitHub Personal Access Token', 'jp-crossword' ); ?></label>
						</th>
						<td>
							<input type="text" id="jpcw_github_token" name="jpcw_github_token" 
								   value="<?php echo esc_attr( get_option( 'jpcw_github_token', '' ) ); ?>" 
								   class="regular-text" />
							<p class="description">
								<?php _e( 'GitHub APIのレート制限を回避するために、Personal Access Tokenを設定することをお勧めします。', 'jp-crossword' ); ?>
								<br>
								<a href="https://github.com/settings/tokens" target="_blank"><?php _e( 'GitHubでトークンを生成', 'jp-crossword' ); ?></a>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * 更新チェック
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// 最新リリース情報を取得
		$release_info = $this->get_latest_release();
		if ( ! $release_info ) {
			return $transient;
		}

		// バージョン比較
		if ( version_compare( JPCW_VERSION, $release_info['version'], '<' ) ) {
			$obj = new stdClass();
			$obj->slug = $this->plugin_slug;
			$obj->new_version = $release_info['version'];
			$obj->url = $release_info['url'];
			$obj->package = $release_info['download_url'];
			$obj->requires = '5.0';
			$obj->requires_php = '7.4';
			$obj->tested = '6.4';
			$obj->last_updated = $release_info['published_at'];
			$obj->sections = [
				'description' => $release_info['description'],
				'changelog' => $release_info['changelog'],
			];

			$transient->response[ $this->plugin_file ] = $obj;
		}

		return $transient;
	}

	/**
	 * プラグイン情報の取得
	 */
	public function plugin_info( $false, $action, $response ) {
		if ( empty( $response->slug ) || $response->slug !== $this->plugin_slug ) {
			return $false;
		}

		$release_info = $this->get_latest_release();
		if ( ! $release_info ) {
			return $false;
		}

		$response->slug = $this->plugin_slug;
		$response->plugin_name = 'Japanese Crossword Generator';
		$response->version = $release_info['version'];
		$response->author = 'Your Name';
		$response->homepage = $release_info['url'];
		$response->requires = '5.0';
		$response->requires_php = '7.4';
		$response->tested = '6.4';
		$response->last_updated = $release_info['published_at'];
		$response->sections = [
			'description' => $release_info['description'],
			'changelog' => $release_info['changelog'],
		];
		$response->download_link = $release_info['download_url'];

		return $response;
	}

	/**
	 * 最新リリース情報の取得
	 */
	private function get_latest_release() {
		// キャッシュをチェック（1時間）
		$cache_key = 'jpcw_github_latest_release';
		$cached = get_transient( $cache_key );
		if ( $cached !== false ) {
			return $cached;
		}

		$api_url = 'https://api.github.com/repos/' . $this->github_repo . '/releases/latest';
		
		$args = [
			'headers' => [
				'Accept' => 'application/vnd.github.v3+json',
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			],
			'timeout' => 15,
		];

		// アクセストークンがある場合は追加
		if ( ! empty( $this->access_token ) ) {
			$args['headers']['Authorization'] = 'token ' . $this->access_token;
		}

		$response = wp_remote_get( $api_url, $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data ) || ! isset( $data['tag_name'] ) ) {
			return false;
		}

		// バージョン番号をクリーンアップ（v1.0.0 → 1.0.0）
		$version = ltrim( $data['tag_name'], 'v' );

		$release_info = [
			'version' => $version,
			'url' => $data['html_url'],
			'download_url' => $data['zipball_url'],
			'description' => $data['body'],
			'changelog' => $data['body'],
			'published_at' => $data['published_at'],
		];

		// 1時間キャッシュ
		set_transient( $cache_key, $release_info, HOUR_IN_SECONDS );

		return $release_info;
	}

	/**
	 * アップグレード後の処理
	 */
	public function upgrader_post_install( $response, $hook_extra, $result ) {
		if ( isset( $hook_extra['plugin'] ) && $hook_extra['plugin'] === $this->plugin_file ) {
			// キャッシュをクリア
			delete_transient( 'jpcw_github_latest_release' );
		}
		return $response;
	}
}

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
		
		// GitHubアップデーターの初期化
		if ( is_admin() ) {
			new JPCW_GitHub_Updater( JPCW_PLUGIN_FILE, JPCW_GITHUB_REPO );
		}
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
		
		// GitHub設定のサブメニューを追加
		add_submenu_page(
			'options-general.php',
			__( 'Japanese Crossword GitHub Settings', 'jp-crossword' ),
			__( 'Crossword GitHub', 'jp-crossword' ),
			'manage_options',
			'jp-crossword-github',
			[ $this, 'github_settings_page' ]
		);
	}

	/**
	 * 管理画面の初期化
	 */
	public function admin_init() {
		register_setting( 'jpcw_settings', 'jpcw_settings' );
		register_setting( 'jpcw_github_settings', 'jpcw_github_token' );
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
			
			<hr>
			
			<h2><?php _e( 'GitHub Update Status', 'jp-crossword' ); ?></h2>
			<?php $this->display_update_status(); ?>
		</div>
		<?php
	}
	
	/**
	 * GitHub設定ページ
	 */
	public function github_settings_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Japanese Crossword GitHub Settings', 'jp-crossword' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'jpcw_github_settings' );
				?>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="jpcw_github_token"><?php _e( 'GitHub Personal Access Token', 'jp-crossword' ); ?></label>
						</th>
						<td>
							<input type="text" id="jpcw_github_token" name="jpcw_github_token" 
								   value="<?php echo esc_attr( get_option( 'jpcw_github_token', '' ) ); ?>" 
								   class="regular-text" />
							<p class="description">
								<?php _e( 'GitHub APIのレート制限を回避するために、Personal Access Tokenを設定することをお勧めします。', 'jp-crossword' ); ?>
								<br>
								<a href="https://github.com/settings/tokens" target="_blank"><?php _e( 'GitHubでトークンを生成', 'jp-crossword' ); ?></a>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	
	/**
	 * 更新状況の表示
	 */
	private function display_update_status() {
		$updates = get_site_transient( 'update_plugins' );
		$plugin_file = plugin_basename( JPCW_PLUGIN_FILE );
		
		if ( isset( $updates->response[ $plugin_file ] ) ) {
			$update = $updates->response[ $plugin_file ];
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php _e( '新しいバージョンが利用可能です！', 'jp-crossword' ); ?></strong><br>
					<?php printf( __( '現在のバージョン: %s → 新しいバージョン: %s', 'jp-crossword' ), JPCW_VERSION, $update->new_version ); ?>
					<br>
					<a href="<?php echo admin_url( 'plugins.php' ); ?>" class="button button-primary">
						<?php _e( 'プラグイン一覧で更新', 'jp-crossword' ); ?>
					</a>
				</p>
			</div>
			<?php
		} else {
			?>
			<div class="notice notice-info">
				<p><?php _e( 'プラグインは最新のバージョンです。', 'jp-crossword' ); ?></p>
			</div>
			<?php
		}
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
			<div class="jpcw-game-container">
				<h3><?php _e( 'クロスワードパズル', 'jp-crossword' ); ?></h3>
				<div class="jpcw-board"></div>
				<div class="jpcw-controls">
					<button type="button" class="jpcw-new crossword-btn">
						<?php _e( '新規問題', 'jp-crossword' ); ?>
					</button>
					<button type="button" class="jpcw-giveup crossword-btn">
						<?php _e( '答えを見る', 'jp-crossword' ); ?>
					</button>
				</div>
				<div class="jpcw-status"></div>
			</div>
			
			<div class="jpcw-hints">
				<h4><?php _e( 'タテのカギ', 'jp-crossword' ); ?></h4>
				<ul class="jpcw-clues-down"></ul>
				<h4><?php _e( 'ヨコのカギ', 'jp-crossword' ); ?></h4>
				<ul class="jpcw-clues-across"></ul>
			</div>
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
				'hint'    => __( 'ヒントを表示', 'jp-crossword' ),
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
	 * 日本語の単語リスト（かな中心）とヒント
	 * 必要に応じてフィルタで拡張可能：apply_filters( 'jpcw_dictionary', $words )
	 */
	private function dictionary() {
		$words = [
			'テスト' => '試験すること',
			'サンプル' => '見本',
			'デモ' => '実演',
			'サ' => 'さ行の文字',
			'ン' => 'ん行の文字',
			'プ' => 'ぷ行の文字',
			'ル' => 'る行の文字'
		];
		
		// 2〜8文字あたりを採用
		$filtered_words = [];
		foreach ($words as $word => $hint) {
			$len = $this->mb_len($word);
			if ($len >= 2 && $len <= 8) {
				$filtered_words[$word] = $hint;
			}
		}
		
		return apply_filters( 'jpcw_dictionary', $filtered_words );
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
		$grid = array_fill(0, $size, array_fill(0, $size, ''));
		$solution = array_fill(0, $size, array_fill(0, $size, ''));
		$placed = [];
		$words  = $this->dictionary();
		shuffle($words);

		$maxWords = 8; // 置き過ぎると詰むので控えめ

		if ( empty($words) ) return [ 'grid' => [], 'solution' => [], 'size' => $size ];

		// 1語目：横、中央付近
		$w1 = array_key_first($words);
		$hint1 = $words[$w1];
		unset($words[$w1]);
		$chars = $this->mb_split($w1);
		if (count($chars) > $size) { $chars = array_slice($chars, 0, $size); }
		$row = intval($size/2);
		$startCol = intval(($size - count($chars))/2);
		
		// グリッドに'INPUT'マーカー、ソリューションに正解文字を設定
		for($i=0;$i<count($chars);$i++){ 
			$grid[$row][$startCol+$i] = 'INPUT';
			$solution[$row][$startCol+$i] = $chars[$i];
		}
		$placed[] = [ 'word'=>$w1, 'hint'=>$hint1, 'row'=>$row, 'col'=>$startCol, 'dir'=>'H', 'len'=>count($chars) ];

		// 以降の語を交差配置
		foreach( $words as $w => $hint ){
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
								$this->place($grid, $solution, $r0, $c0, 'V', $chars);
								$placed[] = [ 'word'=>$w, 'hint'=>$hint, 'row'=>$r0, 'col'=>$c0, 'dir'=>'V', 'len'=>count($chars) ];
								$placedOnce = true; break 3;
							}
						} else {
							// 新語は横に置く
							$r0 = $p['row'] + $pi; // 交点の行
							$c0 = $p['col'] - $mi; // 新語の開始列
							if ( $this->fits($grid, $size, $r0, $c0, 'H', $chars) ) {
								$this->place($grid, $solution, $r0, $c0, 'H', $chars);
								$placed[] = [ 'word'=>$w, 'hint'=>$hint, 'row'=>$r0, 'col'=>$c0, 'dir'=>'H', 'len'=>count($chars) ];
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

		// デバッグ用：グリッドの内容を確認
		error_log('Generated grid: ' . print_r($grid, true));
		error_log('Placed words: ' . print_r($placed, true));
		
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
	delete_option( 'jpcw_github_token' );
	
	// トランジェントの削除
	delete_transient( 'jpcw_github_latest_release' );
	
	// 必要に応じてデータベーステーブルの削除
	// 注意: ユーザーデータが含まれる場合は確認ダイアログを表示することを推奨
}