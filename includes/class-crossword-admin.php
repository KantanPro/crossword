<?php
/**
 * クロスワードゲームの管理画面クラス
 */
class Crossword_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_crossword_save_puzzle', array($this, 'save_puzzle'));
        add_action('wp_ajax_crossword_delete_puzzle', array($this, 'delete_puzzle'));
        add_action('wp_ajax_crossword_get_puzzle_data', array($this, 'get_puzzle_data'));
        add_action('wp_ajax_crossword_generate_puzzle', array($this, 'generate_puzzle'));
    }
    
    /**
     * 管理メニューの追加
     */
    public function add_admin_menu() {
        add_menu_page(
            __('クロスワードゲーム', 'crossword-game'),
            __('クロスワード', 'crossword-game'),
            'manage_options',
            'crossword-game',
            array($this, 'admin_page'),
            'dashicons-games',
            30
        );
        
        add_submenu_page(
            'crossword-game',
            __('パズル一覧', 'crossword-game'),
            __('パズル一覧', 'crossword-game'),
            'manage_options',
            'crossword-game',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'crossword-game',
            __('新規パズル', 'crossword-game'),
            __('新規パズル', 'crossword-game'),
            'manage_options',
            'crossword-new-puzzle',
            array($this, 'new_puzzle_page')
        );
    }
    
    /**
     * 管理画面用スクリプトの読み込み
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'crossword') === false) {
            return;
        }
        
        wp_enqueue_script(
            'crossword-admin',
            CROSSWORD_PLUGIN_URL . 'assets/js/crossword-admin.js',
            array('jquery'),
            CROSSWORD_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'crossword-admin-style',
            CROSSWORD_PLUGIN_URL . 'assets/css/crossword-admin.css',
            array(),
            CROSSWORD_PLUGIN_VERSION
        );
        
        wp_localize_script('crossword-admin', 'crossword_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('crossword_admin_nonce'),
            'strings' => array(
                'confirm_delete' => __('このパズルを削除してもよろしいですか？', 'crossword-game'),
                'puzzle_saved' => __('パズルを保存しました。', 'crossword-game'),
                'puzzle_deleted' => __('パズルを削除しました。', 'crossword-game'),
                'error_occurred' => __('エラーが発生しました。', 'crossword-game'),
                'puzzle_generated' => __('パズルが自動生成されました。', 'crossword-game'),
                'generating_puzzle' => __('パズルを生成中...', 'crossword-game')
            )
        ));
    }
    
    /**
     * 管理画面のメインページ
     */
    public function admin_page() {
        global $wpdb;
        
        $table_puzzles = $wpdb->prefix . 'crossword_puzzles';
        $puzzles = $wpdb->get_results("SELECT * FROM $table_puzzles ORDER BY created_at DESC");
        
        echo '<div class="wrap">';
        echo '<h1>' . __('クロスワードゲーム管理', 'crossword-game') . '</h1>';
        
        echo '<div class="crossword-admin-header">';
        echo '<a href="' . admin_url('admin.php?page=crossword-new-puzzle') . '" class="button button-primary">';
        echo __('新規パズル作成', 'crossword-game');
        echo '</a>';
        echo '</div>';
        
        if (empty($puzzles)) {
            echo '<p>' . __('パズルがありません。新規パズルを作成してください。', 'crossword-game') . '</p>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . __('ID', 'crossword-game') . '</th>';
            echo '<th>' . __('タイトル', 'crossword-game') . '</th>';
            echo '<th>' . __('難易度', 'crossword-game') . '</th>';
            echo '<th>' . __('作成日', 'crossword-game') . '</th>';
            echo '<th>' . __('操作', 'crossword-game') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($puzzles as $puzzle) {
                echo '<tr>';
                echo '<td>' . esc_html($puzzle->id) . '</td>';
                echo '<td>' . esc_html($puzzle->title) . '</td>';
                echo '<td>' . esc_html($this->get_difficulty_label($puzzle->difficulty)) . '</td>';
                echo '<td>' . esc_html($puzzle->created_at) . '</td>';
                echo '<td>';
                echo '<button class="button edit-puzzle" data-id="' . esc_attr($puzzle->id) . '">' . __('編集', 'crossword-game') . '</button> ';
                echo '<button class="button button-link-delete delete-puzzle" data-id="' . esc_attr($puzzle->id) . '">' . __('削除', 'crossword-game') . '</button>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        }
        
        echo '</div>';
    }
    
    /**
     * 新規パズル作成ページ
     */
    public function new_puzzle_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('新規パズル作成', 'crossword-game') . '</h1>';
        
        echo '<form id="crossword-puzzle-form">';
        echo '<table class="form-table">';
        
        echo '<tr>';
        echo '<th scope="row"><label for="puzzle-title">' . __('タイトル', 'crossword-game') . '</label></th>';
        echo '<td><input type="text" id="puzzle-title" name="title" class="regular-text" required /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="puzzle-description">' . __('説明', 'crossword-game') . '</label></th>';
        echo '<td><textarea id="puzzle-description" name="description" class="large-text" rows="3"></textarea></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="puzzle-difficulty">' . __('難易度', 'crossword-game') . '</label></th>';
        echo '<td>';
        echo '<select id="puzzle-difficulty" name="difficulty">';
        echo '<option value="easy">' . __('簡単', 'crossword-game') . '</option>';
        echo '<option value="medium" selected>' . __('普通', 'crossword-game') . '</option>';
        echo '<option value="hard">' . __('難しい', 'crossword-game') . '</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row"><label for="puzzle-size">' . __('グリッドサイズ', 'crossword-game') . '</label></th>';
        echo '<td><input type="number" id="puzzle-size" name="size" min="3" max="15" value="5" /></td>';
        echo '</tr>';
        
        echo '</table>';
        
        echo '<div class="crossword-grid-editor">';
        echo '<h3>' . __('グリッドエディター', 'crossword-game') . '</h3>';
        echo '<div id="grid-container"></div>';
        echo '</div>';
        
        echo '<div class="crossword-words-editor">';
        echo '<h3>' . __('単語とヒント', 'crossword-game') . '</h3>';
        echo '<div id="words-container"></div>';
        echo '<button type="button" class="button add-word">' . __('単語を追加', 'crossword-game') . '</button>';
        echo '</div>';
        
        // 自動生成セクション
        echo '<div class="crossword-auto-generator">';
        echo '<h3>' . __('自動パズル生成', 'crossword-game') . '</h3>';
        echo '<p>' . __('設定を指定してパズルを自動生成できます。', 'crossword-game') . '</p>';
        echo '<div class="auto-generator-controls">';
        echo '<label>' . __('単語数: ', 'crossword-game') . '<input type="number" id="auto-word-count" min="5" max="20" value="10" /></label>';
        echo '<label>' . __('試行回数: ', 'crossword-game') . '<input type="number" id="auto-attempts" min="1" max="10" value="3" /></label>';
        echo '<button type="button" class="button button-secondary" id="auto-generate-btn">' . __('パズルを自動生成', 'crossword-game') . '</button>';
        echo '</div>';
        echo '<div id="auto-generator-status"></div>';
        echo '</div>';
        
        echo '<p class="submit">';
        echo '<input type="submit" class="button button-primary" value="' . __('パズルを保存', 'crossword-game') . '" />';
        echo '</p>';
        echo '</form>';
        
        echo '</div>';
    }
    
    /**
     * 難易度のラベルを取得
     */
    private function get_difficulty_label($difficulty) {
        $labels = array(
            'easy' => __('簡単', 'crossword-game'),
            'medium' => __('普通', 'crossword-game'),
            'hard' => __('難しい', 'crossword-game')
        );
        
        return isset($labels[$difficulty]) ? $labels[$difficulty] : $difficulty;
    }
    
    /**
     * パズルの保存
     */
    public function save_puzzle() {
        check_ajax_referer('crossword_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('権限がありません。', 'crossword-game'));
        }
        
        $title = sanitize_text_field($_POST['title']);
        $description = sanitize_textarea_field($_POST['description']);
        $difficulty = sanitize_text_field($_POST['difficulty']);
        $grid_data = wp_kses_post($_POST['grid_data']);
        $words_data = wp_kses_post($_POST['words_data']);
        $hints_data = wp_kses_post($_POST['hints_data']);
        
        if (empty($title)) {
            wp_send_json_error(__('タイトルは必須です。', 'crossword-game'));
        }
        
        global $wpdb;
        $table_puzzles = $wpdb->prefix . 'crossword_puzzles';
        
        $result = $wpdb->insert(
            $table_puzzles,
            array(
                'title' => $title,
                'description' => $description,
                'difficulty' => $difficulty,
                'grid_data' => $grid_data,
                'words_data' => $words_data,
                'hints_data' => $hints_data
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result !== false) {
            wp_send_json_success(__('パズルを保存しました。', 'crossword-game'));
        } else {
            wp_send_json_error(__('パズルの保存に失敗しました。', 'crossword-game'));
        }
    }
    
    /**
     * パズルの削除
     */
    public function delete_puzzle() {
        check_ajax_referer('crossword_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('権限がありません。', 'crossword-game'));
        }
        
        $puzzle_id = intval($_POST['puzzle_id']);
        
        global $wpdb;
        $table_puzzles = $wpdb->prefix . 'crossword_puzzles';
        $table_progress = $wpdb->prefix . 'crossword_progress';
        
        // 進捗データも削除
        $wpdb->delete($table_progress, array('puzzle_id' => $puzzle_id), array('%d'));
        
        $result = $wpdb->delete($table_puzzles, array('id' => $puzzle_id), array('%d'));
        
        if ($result !== false) {
            wp_send_json_success(__('パズルを削除しました。', 'crossword-game'));
        } else {
            wp_send_json_error(__('パズルの削除に失敗しました。', 'crossword-game'));
        }
    }
    
    /**
     * パズルデータの取得
     */
    public function get_puzzle_data() {
        check_ajax_referer('crossword_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('権限がありません。', 'crossword-game'));
        }
        
        $puzzle_id = intval($_GET['puzzle_id']);
        
        global $wpdb;
        $table_puzzles = $wpdb->prefix . 'crossword_puzzles';
        $puzzle = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_puzzles WHERE id = %d",
                $puzzle_id
            )
        );
        
        if ($puzzle) {
            wp_send_json_success($puzzle);
        } else {
            wp_send_json_error(__('パズルが見つかりません。', 'crossword-game'));
        }
    }
    
    /**
     * パズルの自動生成
     */
    public function generate_puzzle() {
        try {
            check_ajax_referer('crossword_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_die(__('権限がありません。', 'crossword-game'));
            }
            
            $difficulty = sanitize_text_field($_POST['difficulty']);
            $size = intval($_POST['size']);
            $word_count = intval($_POST['word_count']);
            $attempts = intval($_POST['attempts']);
            
            // パラメータの検証
            if ($size < 5 || $size > 20) {
                wp_send_json_error(__('グリッドサイズは5-20の間で指定してください。', 'crossword-game'));
            }
            
            if ($word_count < 5 || $word_count > 20) {
                wp_send_json_error(__('単語数は5-20の間で指定してください。', 'crossword-game'));
            }
            
            if ($attempts < 1 || $attempts > 10) {
                wp_send_json_error(__('試行回数は1-10の間で指定してください。', 'crossword-game'));
            }
            
            error_log("Crossword admin: Starting puzzle generation - difficulty: $difficulty, size: $size, word_count: $word_count, attempts: $attempts");
            
            // パズル生成器の初期化
            error_log("Crossword admin: Initializing Crossword_Generator");
            $generator = new Crossword_Generator();
            error_log("Crossword admin: Crossword_Generator initialized successfully");
            
            // より良いパズルを生成
            error_log("Crossword admin: Calling generate_best_puzzle");
            $puzzle_data = $generator->generate_best_puzzle($difficulty, $size, $word_count, $attempts);
            error_log("Crossword admin: generate_best_puzzle completed");
            
            if ($puzzle_data) {
                error_log("Crossword admin: Puzzle generation successful, data received");
                // フォームにデータを設定
                $response = array(
                    'success' => true,
                    'message' => __('パズルが自動生成されました。', 'crossword-game'),
                    'data' => $puzzle_data
                );
                
                error_log("Crossword admin: Sending success response");
                wp_send_json_success($response);
            } else {
                error_log("Crossword admin: Puzzle generation failed - no data returned");
                wp_send_json_error(__('パズルの生成に失敗しました。設定を変更して再試行してください。', 'crossword-game'));
            }
            
        } catch (Exception $e) {
            error_log('Crossword plugin generate_puzzle error: ' . $e->getMessage());
            wp_send_json_error(__('エラーが発生しました: ', 'crossword-game') . $e->getMessage());
        } catch (Error $e) {
            error_log('Crossword plugin generate_puzzle fatal error: ' . $e->getMessage());
            wp_send_json_error(__('致命的なエラーが発生しました。', 'crossword-game'));
        }
    }
}
