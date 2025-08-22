<?php
/**
 * Plugin Name: Crossword Game Generator
 * Plugin URI: https://example.com/crossword-game
 * Description: 自動生成されるクロスワードゲームを提供するWordPressプラグイン
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: crossword-game
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// プラグインクラスの定義
class CrosswordGamePlugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('crossword', array($this, 'crossword_shortcode'));
        add_action('wp_ajax_generate_crossword', array($this, 'generate_crossword_ajax'));
        add_action('wp_ajax_nopriv_generate_crossword', array($this, 'generate_crossword_ajax'));
        add_action('wp_ajax_check_answer', array($this, 'check_answer_ajax'));
        add_action('wp_ajax_nopriv_check_answer', array($this, 'check_answer_ajax'));
        add_action('wp_ajax_give_up', array($this, 'give_up_ajax'));
        add_action('wp_ajax_nopriv_give_up', array($this, 'give_up_ajax'));
    }
    
    public function init() {
        // 初期化処理
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('crossword-game', plugin_dir_url(__FILE__) . 'assets/js/crossword-game.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('crossword-game', plugin_dir_url(__FILE__) . 'assets/css/crossword-game.css', array(), '1.0.0');
        
        wp_localize_script('crossword-game', 'crossword_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('crossword_nonce')
        ));
    }
    
    public function crossword_shortcode($atts) {
        $default_atts = array(
            'size' => '10x10',
            'difficulty' => 'medium'
        );
        $atts = shortcode_atts($default_atts, $atts);
        
        $crossword_data = $this->generate_crossword($atts['size'], $atts['difficulty']);
        
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/crossword-game.php';
        return ob_get_clean();
    }
    
    public function generate_crossword($size = '10x10', $difficulty = 'medium') {
        $sizes = explode('x', $size);
        $width = intval($sizes[0]);
        $height = intval($sizes[1]);
        
        // 日本語の単語リスト（サンプル）
        $words = $this->get_word_list($difficulty);
        
        // クロスワード生成ロジック
        $crossword = $this->create_crossword_grid($width, $height, $words);
        
        return $crossword;
    }
    
    private function get_word_list($difficulty) {
        // 難易度に応じた単語リスト
        $word_lists = array(
            'easy' => array(
                'こんにちは', 'さようなら', 'おはよう', 'こんばんは',
                'ありがとう', 'すみません', 'お疲れ様', 'お元気ですか',
                'いただきます', 'ごちそうさま'
            ),
            'medium' => array(
                'プログラミング', 'ウェブサイト', 'インターネット', 'コンピュータ',
                'スマートフォン', 'アプリケーション', 'データベース', 'サーバー',
                'ネットワーク', 'セキュリティ'
            ),
            'hard' => array(
                'アルゴリズム', 'フレームワーク', 'アーキテクチャ', 'インフラストラクチャ',
                'オブジェクト指向', 'データ構造', 'パフォーマンス', 'スケーラビリティ',
                'マイクロサービス', 'クラウドコンピューティング'
            )
        );
        
        return isset($word_lists[$difficulty]) ? $word_lists[$difficulty] : $word_lists['medium'];
    }
    
    private function create_crossword_grid($width, $height, $words) {
        // グリッドの初期化
        $grid = array();
        for ($i = 0; $i < $height; $i++) {
            $grid[$i] = array();
            for ($j = 0; $j < $width; $j++) {
                $grid[$i][$j] = '';
            }
        }
        
        $placed_words = array();
        $clues = array();
        
        // 単語を配置
        foreach ($words as $word) {
            $placed = $this->place_word($grid, $word, $placed_words);
            if ($placed) {
                $placed_words[] = $placed;
                $clues[] = array(
                    'word' => $word,
                    'clue' => $this->generate_clue($word),
                    'start_x' => $placed['start_x'],
                    'start_y' => $placed['start_y'],
                    'direction' => $placed['direction']
                );
            }
        }
        
        return array(
            'grid' => $grid,
            'clues' => $clues,
            'width' => $width,
            'height' => $height
        );
    }
    
    private function place_word($grid, $word, $placed_words) {
        $max_attempts = 100;
        $attempts = 0;
        
        while ($attempts < $max_attempts) {
            $direction = rand(0, 1); // 0: 横, 1: 縦
            $start_x = rand(0, count($grid[0]) - 1);
            $start_y = rand(0, count($grid) - 1);
            
            if ($this->can_place_word($grid, $word, $start_x, $start_y, $direction)) {
                $this->place_word_in_grid($grid, $word, $start_x, $start_y, $direction);
                return array(
                    'word' => $word,
                    'start_x' => $start_x,
                    'start_y' => $start_y,
                    'direction' => $direction
                );
            }
            
            $attempts++;
        }
        
        return false;
    }
    
    private function can_place_word($grid, $word, $start_x, $start_y, $direction) {
        $word_length = mb_strlen($word);
        
        if ($direction == 0) { // 横
            if ($start_x + $word_length > count($grid[0])) {
                return false;
            }
            
            for ($i = 0; $i < $word_length; $i++) {
                $x = $start_x + $i;
                $y = $start_y;
                
                if ($grid[$y][$x] !== '' && $grid[$y][$x] !== mb_substr($word, $i, 1)) {
                    return false;
                }
            }
        } else { // 縦
            if ($start_y + $word_length > count($grid)) {
                return false;
            }
            
            for ($i = 0; $i < $word_length; $i++) {
                $x = $start_x;
                $y = $start_y + $i;
                
                if ($grid[$y][$x] !== '' && $grid[$y][$x] !== mb_substr($word, $i, 1)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    private function place_word_in_grid(&$grid, $word, $start_x, $start_y, $direction) {
        $word_length = mb_strlen($word);
        
        if ($direction == 0) { // 横
            for ($i = 0; $i < $word_length; $i++) {
                $grid[$start_y][$start_x + $i] = mb_substr($word, $i, 1);
            }
        } else { // 縦
            for ($i = 0; $i < $word_length; $i++) {
                $grid[$start_y + $i][$start_x] = mb_substr($word, $i, 1);
            }
        }
    }
    
    private function generate_clue($word) {
        // 単語のヒントを生成（実際の実装ではより詳細なヒントが必要）
        return "「{$word}」に関連する言葉";
    }
    
    // AJAXハンドラー
    public function generate_crossword_ajax() {
        check_ajax_referer('crossword_nonce', 'nonce');
        
        $size = isset($_POST['size']) ? sanitize_text_field($_POST['size']) : '10x10';
        $difficulty = isset($_POST['difficulty']) ? sanitize_text_field($_POST['difficulty']) : 'medium';
        
        $crossword = $this->generate_crossword($size, $difficulty);
        
        wp_send_json_success($crossword);
    }
    
    public function check_answer_ajax() {
        check_ajax_referer('crossword_nonce', 'nonce');
        
        $answer = isset($_POST['answer']) ? sanitize_text_field($_POST['answer']) : '';
        $word = isset($_POST['word']) ? sanitize_text_field($_POST['word']) : '';
        
        $is_correct = ($answer === $word);
        
        wp_send_json_success(array('correct' => $is_correct));
    }
    
    public function give_up_ajax() {
        check_ajax_referer('crossword_nonce', 'nonce');
        
        $crossword_data = isset($_POST['crossword_data']) ? $_POST['crossword_data'] : array();
        
        // ギブアップ時の処理
        wp_send_json_success(array('message' => 'ギブアップしました'));
    }
}

// プラグインの初期化
$crossword_plugin = new CrosswordGamePlugin();
