<?php
/**
 * クロスワードゲームのメインクラス
 */
class Crossword_Game {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('crossword', array($this, 'crossword_shortcode'));
        add_action('wp_ajax_crossword_save_progress', array($this, 'save_progress'));
        add_action('wp_ajax_nopriv_crossword_save_progress', array($this, 'save_progress'));
        add_action('wp_ajax_crossword_get_puzzle', array($this, 'get_puzzle'));
        add_action('wp_ajax_nopriv_crossword_get_puzzle', array($this, 'get_puzzle'));
        add_action('wp_ajax_crossword_give_up', array($this, 'give_up'));
        add_action('wp_ajax_nopriv_crossword_give_up', array($this, 'give_up'));
    }
    
    /**
     * スクリプトとスタイルの読み込み
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'crossword-game',
            CROSSWORD_PLUGIN_URL . 'assets/js/crossword-game.js',
            array('jquery'),
            CROSSWORD_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'crossword-style',
            CROSSWORD_PLUGIN_URL . 'assets/css/crossword-style.css',
            array(),
            CROSSWORD_PLUGIN_VERSION
        );
        
        // AJAX用のローカライズスクリプト
        wp_localize_script('crossword-game', 'crossword_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('crossword_nonce'),
            'strings' => array(
                'puzzle_completed' => __('パズルが完成しました！', 'crossword-game'),
                'word_correct' => __('正解です！', 'crossword-game'),
                'word_incorrect' => __('不正解です。', 'crossword-game'),
                'save_progress' => __('進捗を保存しました。', 'crossword-game'),
                'error_occurred' => __('エラーが発生しました。', 'crossword-game'),
                'give_up_confirm' => __('本当にギブアップしますか？正解が表示されます。', 'crossword-game'),
                'give_up_processing' => __('ギブアップ処理中...', 'crossword-game'),
                'give_up_success' => __('ギブアップしました。正解を表示します。', 'crossword-game'),
                'give_up_failed' => __('ギブアップ処理に失敗しました: ', 'crossword-game'),
                'show_answer' => __('正解を表示', 'crossword-game'),
                'hide_answer' => __('正解を隠す', 'crossword-game')
            )
        ));
    }
    
    /**
     * クロスワードショートコード
     */
    public function crossword_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'difficulty' => 'medium'
        ), $atts);
        
        // パズルIDが指定されていない場合は何も表示しない（完全廃止）
        if (!isset($atts['id']) || $atts['id'] === '') {
            return '';
        }
        
        // 指定されたIDのパズルを取得
        $puzzle = $this->get_puzzle_by_id($atts['id']);
        if (!$puzzle) {
            return '<div class="crossword-error">パズルID: ' . esc_html($atts['id']) . ' が見つかりません。</div>';
        }
        
        return $this->render_puzzle($puzzle);
    }
    

    
    /**
     * IDでパズルを取得
     */
    private function get_puzzle_by_id($id) {
        global $wpdb;
        
        $table_puzzles = $wpdb->prefix . 'crossword_puzzles';
        $puzzle = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_puzzles WHERE id = %d",
                $id
            )
        );
        
        // デバッグ用のログ出力
        if ($puzzle) {
            error_log('Crossword plugin: Puzzle found by ID ' . $id . ' - Title: ' . $puzzle->title);
            
            // wpdbのエスケープ問題を回避するため、JSONデータを修正
            $puzzle = $this->fix_puzzle_data($puzzle);
        } else {
            error_log('Crossword plugin: No puzzle found with ID ' . $id);
        }
        
        return $puzzle;
    }
    
    /**
     * wpdbのエスケープ問題を修正
     */
    private function fix_puzzle_data($puzzle) {
        if (!$puzzle || !is_object($puzzle)) {
            return $puzzle;
        }
        
        // JSONデータの修正
        if (isset($puzzle->grid_data)) {
            $puzzle->grid_data = $this->fix_json_data($puzzle->grid_data);
        }
        if (isset($puzzle->words_data)) {
            $puzzle->words_data = $this->fix_json_data($puzzle->words_data);
        }
        if (isset($puzzle->hints_data)) {
            $puzzle->hints_data = $this->fix_json_data($puzzle->hints_data);
        }
        
        return $puzzle;
    }
    
    /**
     * JSONデータの修正
     */
    private function fix_json_data($json_string) {
        // wpdbによるエスケープを元に戻す
        $fixed = stripslashes($json_string);
        
        // 追加のエスケープ修正
        $fixed = str_replace('\\"', '"', $fixed);
        $fixed = str_replace('\\\\', '\\', $fixed);
        
        return $fixed;
    }
    
    /**
     * パズルの表示
     */
    private function render_puzzle($puzzle) {
        // パズルオブジェクトのnull値チェック
        if (!$puzzle || !is_object($puzzle)) {
            error_log('Crossword plugin: render_puzzle called with invalid puzzle object');
            return '<div class="crossword-error">パズルデータが無効です。</div>';
        }
        
        // 必要なプロパティの存在チェック
        if (!isset($puzzle->id) || !isset($puzzle->grid_data) || !isset($puzzle->words_data) || !isset($puzzle->hints_data)) {
            error_log('Crossword plugin: render_puzzle called with missing puzzle properties');
            return '<div class="crossword-error">パズルデータが不完全です。</div>';
        }
        
        // デバッグ用のログ出力
        error_log('Crossword plugin: Rendering puzzle ID: ' . $puzzle->id . ', Title: ' . $puzzle->title);
        
        $grid_data = json_decode($puzzle->grid_data, true);
        $words_data = json_decode($puzzle->words_data, true);
        $hints_data = json_decode($puzzle->hints_data, true);
        
        // JSONデコードの結果チェック
        if ($grid_data === null || $words_data === null || $hints_data === null) {
            error_log('Crossword plugin: render_puzzle JSON decode failed for puzzle ID: ' . $puzzle->id);
            error_log('Crossword plugin: grid_data decode result: ' . var_export($grid_data, true));
            error_log('Crossword plugin: words_data decode result: ' . var_export($words_data, true));
            error_log('Crossword plugin: hints_data decode result: ' . var_export($hints_data, true));
            return '<div class="crossword-error">パズルデータの解析に失敗しました。</div>';
        }
        
        $output = '<div class="crossword-game" data-puzzle-id="' . esc_attr($puzzle->id) . '">';
        $output .= '<h2 class="crossword-title">' . esc_html($puzzle->title ?? 'クロスワードパズル') . '</h2>';
        $output .= '<p class="crossword-description">' . esc_html($puzzle->description ?? '') . '</p>';
        
        // ゲームボード
        $output .= '<div class="crossword-board">';
        $output .= $this->render_grid($grid_data, $words_data);
        $output .= '</div>';
        
        // ヒントセクション
        $output .= '<div class="crossword-hints">';
        $output .= '<h3>' . __('ヒント', 'crossword-game') . '</h3>';
        $output .= $this->render_hints($hints_data, $words_data);
        $output .= '</div>';
        
        // コントロール
        $output .= '<div class="crossword-controls">';
        $output .= '<button class="crossword-check-btn">' . __('チェック', 'crossword-game') . '</button>';
        $output .= '<button class="crossword-reset-btn">' . __('リセット', 'crossword-game') . '</button>';
        $output .= '<button class="crossword-save-btn">' . __('進捗保存', 'crossword-game') . '</button>';
        $output .= '<button class="crossword-giveup-btn">' . __('ギブアップ', 'crossword-game') . '</button>';
        $output .= '</div>';
        
        // タイマー
        $output .= '<div class="crossword-timer">';
        $output .= '<span>' . __('経過時間: ', 'crossword-game') . '</span>';
        $output .= '<span class="timer-display">00:00</span>';
        $output .= '</div>';
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * グリッドの表示
     */
    private function render_grid($grid_data, $words_data) {
        // null値チェック
        if (!$grid_data || !is_array($grid_data)) {
            error_log('Crossword plugin: render_grid called with invalid grid_data');
            return '<div class="crossword-error">パズルデータが無効です。</div>';
        }
        
        if (!isset($grid_data['size']) || !isset($grid_data['grid']) || !is_array($grid_data['grid'])) {
            error_log('Crossword plugin: render_grid called with missing or invalid grid data structure');
            return '<div class="crossword-error">グリッドデータの構造が無効です。</div>';
        }
        
        $size = intval($grid_data['size']);
        $grid = $grid_data['grid'];
        
        if ($size <= 0 || $size > 50) {
            error_log('Crossword plugin: render_grid called with invalid size: ' . $size);
            return '<div class="crossword-error">無効なグリッドサイズです。</div>';
        }
        
        $output = '<div class="crossword-grid" style="grid-template-columns: repeat(' . $size . ', 1fr);">';
        
        for ($row = 0; $row < $size; $row++) {
            for ($col = 0; $col < $size; $col++) {
                $cell_class = 'crossword-cell';
                $cell_value = '';
                
                // セルの値の安全な取得
                if (isset($grid[$row]) && isset($grid[$row][$col])) {
                    $cell_value = $grid[$row][$col];
                }
                
                if ($cell_value === '') {
                    $cell_class .= ' empty';
                    $cell_value = '';
                }
                
                $output .= '<div class="' . $cell_class . '" data-row="' . $row . '" data-col="' . $col . '">';
                if ($cell_value !== '') {
                    $output .= '<input type="text" class="crossword-input" maxlength="1" value="' . esc_attr($cell_value) . '" readonly>';
                } else {
                    $output .= '<input type="text" class="crossword-input" maxlength="1" value="">';
                }
                $output .= '</div>';
            }
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * ヒントの表示
     */
    private function render_hints($hints_data, $words_data) {
        // null値チェック
        if (!$hints_data || !is_array($hints_data)) {
            error_log('Crossword plugin: render_hints called with invalid hints_data');
            return '<div class="crossword-error">ヒントデータが無効です。</div>';
        }
        
        if (!$words_data || !is_array($words_data)) {
            error_log('Crossword plugin: render_hints called with invalid words_data');
            return '<div class="crossword-error">単語データが無効です。</div>';
        }
        
        $output = '<div class="hints-container">';
        
        $output .= '<div class="hints-horizontal">';
        $output .= '<h4>' . __('横の単語', 'crossword-game') . '</h4>';
        $output .= '<ul>';
        foreach ($words_data as $word => $data) {
            if (is_array($data) && isset($data['direction']) && $data['direction'] === 'horizontal') {
                $hint = isset($hints_data[$word]) ? $hints_data[$word] : 'ヒントなし';
                $output .= '<li data-word="' . esc_attr($word) . '">';
                $output .= '<strong>' . esc_html($word) . '</strong>: ';
                $output .= '<span class="hint-text">' . esc_html($hint) . '</span>';
                $output .= '</li>';
            }
        }
        $output .= '</ul>';
        $output .= '</div>';
        
        $output .= '<div class="hints-vertical">';
        $output .= '<h4>' . __('縦の単語', 'crossword-game') . '</h4>';
        $output .= '<ul>';
        foreach ($words_data as $word => $data) {
            if (is_array($data) && isset($data['direction']) && $data['direction'] === 'vertical') {
                $hint = isset($hints_data[$word]) ? $hints_data[$word] : 'ヒントなし';
                $output .= '<li data-word="' . esc_attr($word) . '">';
                $output .= '<strong>' . esc_html($word) . '</strong>: ';
                $output .= '<span class="hint-text">' . esc_html($hint) . '</span>';
                $output .= '</li>';
            }
        }
        $output .= '</ul>';
        $output .= '</div>';
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * 進捗の保存
     */
    public function save_progress() {
        try {
            check_ajax_referer('crossword_nonce', 'nonce');
            
            $puzzle_id = intval($_POST['puzzle_id']);
            $progress_data = sanitize_text_field($_POST['progress_data']);
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                wp_die(__('ログインが必要です。', 'crossword-game'));
            }
            
            global $wpdb;
            $table_progress = $wpdb->prefix . 'crossword_progress';
            
            // テーブルが存在するかチェック
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_progress'") != $table_progress) {
                wp_send_json_error(__('進捗保存テーブルが存在しません。', 'crossword-game'));
            }
            
            $result = $wpdb->replace(
                $table_progress,
                array(
                    'user_id' => $user_id,
                    'puzzle_id' => $puzzle_id,
                    'progress_data' => $progress_data,
                    'updated_at' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s')
            );
            
            if ($result !== false) {
                wp_send_json_success(__('進捗を保存しました。', 'crossword-game'));
            } else {
                wp_send_json_error(__('進捗の保存に失敗しました。', 'crossword-game'));
            }
            
        } catch (Exception $e) {
            error_log('Crossword plugin save_progress error: ' . $e->getMessage());
            wp_send_json_error(__('エラーが発生しました。', 'crossword-game'));
        } catch (Error $e) {
            error_log('Crossword plugin save_progress fatal error: ' . $e->getMessage());
            wp_send_json_error(__('致命的なエラーが発生しました。', 'crossword-game'));
        }
    }
    
    /**
     * パズルの取得（AJAX）
     */
    public function get_puzzle() {
        check_ajax_referer('crossword_nonce', 'nonce');
        
        $puzzle_id = intval($_GET['puzzle_id']);
        $puzzle = $this->get_puzzle_by_id($puzzle_id);
        
        if ($puzzle) {
            wp_send_json_success($puzzle);
        } else {
            wp_send_json_error(__('パズルが見つかりません。', 'crossword-game'));
        }
    }
    
    /**
     * ギブアップ時の回答表示（AJAX）
     */
    public function give_up() {
        try {
            check_ajax_referer('crossword_nonce', 'nonce');
            
            $puzzle_id = intval($_POST['puzzle_id']);
            $puzzle = $this->get_puzzle_by_id($puzzle_id);
            
            if (!$puzzle) {
                wp_send_json_error(__('パズルが見つかりません。', 'crossword-game'));
            }
            
            // complete_answer_grid_dataフィールドの存在確認とフォールバック処理
            $complete_answer_grid_data = '';
            if (isset($puzzle->complete_answer_grid_data) && !empty($puzzle->complete_answer_grid_data)) {
                $complete_answer_grid_data = $puzzle->complete_answer_grid_data;
            } else {
                // フィールドが存在しない場合は、grid_dataから完全な正解グリッドを生成
                $grid_data = json_decode($puzzle->grid_data, true);
                if ($grid_data && isset($grid_data['grid'])) {
                    $complete_grid = $grid_data['grid'];
                    // 空マスに適当な文字を設定
                    for ($i = 0; $i < count($complete_grid); $i++) {
                        for ($j = 0; $j < count($complete_grid[$i]); $j++) {
                            if ($complete_grid[$i][$j] === '') {
                                $complete_grid[$i][$j] = 'あ'; // デフォルト文字
                            }
                        }
                    }
                    $complete_answer_grid_data = json_encode(array(
                        'size' => $grid_data['size'],
                        'grid' => $complete_grid
                    ));
                } else {
                    // grid_dataも無効な場合は、元のgrid_dataを使用
                    $complete_answer_grid_data = $puzzle->grid_data;
                }
            }
            
            // パズルの正解データを返す（完全な正解グリッドを含む）
            $answer_data = array(
                'grid_data' => $puzzle->grid_data,
                'complete_answer_grid_data' => $complete_answer_grid_data,
                'words_data' => $puzzle->words_data,
                'hints_data' => $puzzle->hints_data,
                'message' => __('ギブアップしました。正解を表示します。', 'crossword-game')
            );
            
            wp_send_json_success($answer_data);
            
        } catch (Exception $e) {
            error_log('Crossword plugin give_up error: ' . $e->getMessage());
            wp_send_json_error(__('エラーが発生しました。', 'crossword-game'));
        } catch (Error $e) {
            error_log('Crossword plugin give_up fatal error: ' . $e->getMessage());
            wp_send_json_error(__('致命的なエラーが発生しました。', 'crossword-game'));
        }
    }
}
