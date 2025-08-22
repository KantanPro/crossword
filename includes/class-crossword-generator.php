<?php
/**
 * 日本語クロスワードパズル自動生成クラス
 */
class Crossword_Generator {
    
    private $words = array();
    private $grid = array();
    private $size = 0;
    
    public function __construct() {
        $this->load_japanese_words();
    }
    
    /**
     * 日本語に特化した単語リストを読み込み
     */
    private function load_japanese_words() {
        $this->words = array(
            'easy' => array(
                'ねこ', 'いぬ', 'うし', 'うま', 'ひつじ', 'とり', 'さかな', 'くも', 'あり', 'ちょう',
                'ほし', 'つき', 'そら', 'みず', 'ひ', 'つち', 'やま', 'うみ', 'かわ', 'もり',
                'ほん', 'かみ', 'えんぴつ', 'けしゴム', 'かばん', 'くつ', 'ぼうし', 'めがね',
                'ごはん', 'パン', 'みず', 'おちゃ', 'くだもの', 'やさい', 'にく', 'ぎゅうにゅう',
                'あか', 'あお', 'きいろ', 'みどり', 'くろ', 'しろ', 'ちゃいろ', 'ピンク',
                'たのしい', 'かなしい', 'うれしい', 'おこる', 'わらう', 'ない', 'ねむい', 'おなかすいた',
                'あるく', 'はしる', 'とぶ', 'およぐ', 'のぼる', 'おりる', 'まわる', 'とまる'
            ),
            'medium' => array(
                'コンピューター', 'インターネット', 'でんわ', 'テレビ', 'ラジオ', 'スマートフォン',
                'がっこう', 'だいがく', 'びょういん', 'としょかん', 'びじゅつかん', 'こうえん', 'レストラン',
                'くるま', 'じてんしゃ', 'でんしゃ', 'ひこうき', 'ふね', 'バス', 'タクシー',
                'せんせい', 'いしゃ', 'かんごし', 'けいさつかん', 'しょうぼうし', 'エンジニア',
                'おんがく', 'えいが', 'げきじょう', 'スポーツ', 'ゲーム', 'りょうり',
                'あさごはん', 'ひるごはん', 'ばんごはん', 'はる', 'なつ', 'あき', 'ふゆ',
                'おとうさん', 'おかあさん', 'おじいさん', 'おばあさん', 'おにいさん', 'おねえさん',
                'からだ', 'あたま', 'め', 'みみ', 'はな', 'くち', 'て', 'あし', 'こころ'
            ),
            'hard' => array(
                'せいじ', 'けいざい', 'ぶんか', 'れきし', 'ちり', 'こくさいかんけい', 'せいふ',
                'かがく', 'ぎじゅつ', 'けんきゅう', 'はっめい', 'かいめい', 'せつめい', 'りかい',
                'きょういく', 'がくしゅう', 'べんきょう', 'ちしき', 'けいけん', 'のうりょく',
                'かんきょう', 'しぜん', 'せいたいけい', 'きしょう', 'ちきゅう', 'うちゅう',
                'しんり', 'てつがく', 'りそう', 'かんがえ', 'いみ', 'もくてき',
                'げいじゅつ', 'ぶんがく', 'しが', 'しょうせつ', 'え', 'おんがく', 'ぶんか',
                'けいざい', 'さんぎょう', 'しょうぎょう', 'ぎょうむ', 'しごと', 'しゃかい',
                'こくさい', 'がいこう', 'きょうりょく', 'とりひき', 'しょうひん', 'ゆにゅう'
            )
        );
    }
    
    /**
     * パズルを自動生成
     */
    public function generate_puzzle($difficulty = 'medium', $size = 15, $word_count = 12) {
        try {
            $this->size = $size;
            $this->grid = array();
            
            $this->initialize_grid();
            $selected_words = $this->select_quality_words($difficulty, $word_count);
            $placed_words = $this->place_words_optimized($selected_words);
            
            return $this->build_enhanced_puzzle_data($placed_words);
            
        } catch (Exception $e) {
            error_log('日本語クロスワード生成エラー: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * グリッドの初期化
     */
    private function initialize_grid() {
        for ($row = 0; $row < $this->size; $row++) {
            $this->grid[$row] = array();
            for ($col = 0; $col < $this->size; $col++) {
                $this->grid[$row][$col] = '';
            }
        }
    }
    
    /**
     * 品質重視の単語選択
     */
    private function select_quality_words($difficulty, $count) {
        $available_words = array();
        
        switch ($difficulty) {
            case 'easy':
                $available_words = array_merge($this->words['easy'], array_slice($this->words['medium'], 0, 8));
                break;
            case 'medium':
                $available_words = array_merge($this->words['easy'], $this->words['medium'], array_slice($this->words['hard'], 0, 5));
                break;
            case 'hard':
                $available_words = array_merge($this->words['medium'], $this->words['hard']);
                break;
            default:
                $available_words = $this->words['medium'];
        }
        
        $filtered_words = $this->filter_words_by_size($available_words, $count);
        $selected = $this->ensure_word_variety($filtered_words, $count);
        
        return $selected;
    }
    
    /**
     * サイズに基づく単語フィルタリング
     */
    private function filter_words_by_size($words, $target_count) {
        $filtered = array();
        $size_limits = array(
            'small' => $this->size * 0.6,
            'medium' => $this->size * 0.8
        );
        
        foreach ($words as $word) {
            $length = mb_strlen($word, 'UTF-8');
            if ($length <= $size_limits['medium']) {
                $filtered[] = $word;
            }
        }
        
        foreach ($words as $word) {
            $length = mb_strlen($word, 'UTF-8');
            if ($length <= $size_limits['small'] && !in_array($word, $filtered)) {
                $filtered[] = $word;
            }
        }
        
        return $filtered;
    }
    
    /**
     * 単語の多様性を確保
     */
    private function ensure_word_variety($words, $target_count) {
        usort($words, function($a, $b) {
            return mb_strlen($a, 'UTF-8') - mb_strlen($b, 'UTF-8');
        });
        
        $short_words = array();
        $medium_words = array();
        $long_words = array();
        
        foreach ($words as $word) {
            $length = mb_strlen($word, 'UTF-8');
            if ($length <= 4) {
                $short_words[] = $word;
            } elseif ($length <= 7) {
                $medium_words[] = $word;
            } else {
                $long_words[] = $word;
            }
        }
        
        $short_count = min(ceil($target_count * 0.4), count($short_words));
        $medium_count = min(ceil($target_count * 0.4), count($medium_words));
        $long_count = min($target_count - $short_count - $medium_count, count($long_words));
        
        $selected = array_merge(
            array_slice($short_words, 0, $short_count),
            array_slice($medium_words, 0, $medium_count),
            array_slice($long_words, 0, $long_count)
        );
        
        if (count($selected) < $target_count) {
            $remaining = array_diff($words, $selected);
            $selected = array_merge($selected, array_slice($remaining, 0, $target_count - count($selected)));
        }
        
        return array_slice($selected, 0, $target_count);
    }
    
    /**
     * 最適化された単語配置
     */
    private function place_words_optimized($words) {
        $placed_words = array();
        
        usort($words, function($a, $b) {
            return mb_strlen($a, 'UTF-8') - mb_strlen($b, 'UTF-8');
        });
        
        // 最初の単語は中央に配置
        if (!empty($words)) {
            $first_word = $words[0];
            $center_row = floor($this->size / 2);
            $center_col = floor(($this->size - mb_strlen($first_word, 'UTF-8')) / 2);
            
            if ($this->can_place_word($first_word, $center_row, $center_col, 'horizontal')) {
                $this->place_word($first_word, $center_row, $center_col, 'horizontal');
                $placed_words[$first_word] = array(
                    'row' => $center_row,
                    'col' => $center_col,
                    'direction' => 'horizontal'
                );
                array_shift($words);
            }
        }
        
        foreach ($words as $word) {
            $placed = $this->place_word_strategically($word);
            if ($placed) {
                $placed_words[$word] = $placed;
            }
        }
        
        return $placed_words;
    }
    
    /**
     * 戦略的な単語配置
     */
    private function place_word_strategically($word) {
        $directions = array('horizontal', 'vertical');
        shuffle($directions);
        
        foreach ($directions as $direction) {
            $placement = $this->find_best_placement($word, $direction);
            if ($placement) {
                $this->place_word($word, $placement['row'], $placement['col'], $direction);
                return array(
                    'row' => $placement['row'],
                    'col' => $placement['col'],
                    'direction' => $direction
                );
            }
        }
        
        return false;
    }
    
    /**
     * 最適な配置位置を探索
     */
    private function find_best_placement($word, $direction) {
        $best_score = -1;
        $best_placement = null;
        
        for ($row = 0; $row < $this->size; $row++) {
            for ($col = 0; $col < $this->size; $col++) {
                if ($this->can_place_word($word, $row, $col, $direction)) {
                    $score = $this->calculate_placement_score($word, $row, $col, $direction);
                    if ($score > $best_score) {
                        $best_score = $score;
                        $best_placement = array('row' => $row, 'col' => $col);
                    }
                }
            }
        }
        
        return $best_placement;
    }
    
    /**
     * 配置位置のスコアを計算
     */
    private function calculate_placement_score($word, $row, $col, $direction) {
        $score = 0;
        
        $center = floor($this->size / 2);
        $distance_from_center = abs($row - $center) + abs($col - $center);
        $score += (10 - $distance_from_center);
        
        $crossings = $this->count_crossings($word, $row, $col, $direction);
        $score += $crossings * 5;
        
        if ($row > 0 && $row < $this->size - 1 && $col > 0 && $col < $this->size - 1) {
            $score += 3;
        }
        
        return $score;
    }
    
    /**
     * 交差数をカウント
     */
    private function count_crossings($word, $row, $col, $direction) {
        $crossings = 0;
        $length = mb_strlen($word, 'UTF-8');
        
        for ($i = 0; $i < $length; $i++) {
            if ($direction === 'horizontal') {
                $current_row = $row;
                $current_col = $col + $i;
            } else {
                $current_row = $row + $i;
                $current_col = $col;
            }
            
            if ($current_row > 0 && $this->grid[$current_row - 1][$current_col] !== '') $crossings++;
            if ($current_row < $this->size - 1 && $this->grid[$current_row + 1][$current_col] !== '') $crossings++;
            if ($current_col > 0 && $this->grid[$current_row][$current_col - 1] !== '') $crossings++;
            if ($current_col < $this->size - 1 && $this->grid[$current_row][$current_col + 1] !== '') $crossings++;
        }
        
        return $crossings;
    }
    
    /**
     * 単語が配置可能かチェック
     */
    private function can_place_word($word, $row, $col, $direction) {
        $length = mb_strlen($word, 'UTF-8');
        
        if ($row < 0 || $col < 0 || $row >= $this->size || $col >= $this->size) {
            return false;
        }
        
        if ($direction == 'horizontal') {
            if ($col + $length > $this->size) {
                return false;
            }
            
            for ($i = 0; $i < $length; $i++) {
                $char = mb_substr($word, $i, 1, 'UTF-8');
                $current_cell = $this->grid[$row][$col + $i];
                if ($current_cell !== '' && $current_cell !== $char) {
                    return false;
                }
            }
        } else {
            if ($row + $length > $this->size) {
                return false;
            }
            
            for ($i = 0; $i < $length; $i++) {
                $char = mb_substr($word, $i, 1, 'UTF-8');
                $current_cell = $this->grid[$row + $i][$col];
                if ($current_cell !== '' && $current_cell !== $char) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * 単語をグリッドに配置
     */
    private function place_word($word, $row, $col, $direction) {
        $length = mb_strlen($word, 'UTF-8');
        
        if ($direction == 'horizontal') {
            for ($i = 0; $i < $length; $i++) {
                $char = mb_substr($word, $i, 1, 'UTF-8');
                $this->grid[$row][$col + $i] = $char;
            }
        } else {
            for ($i = 0; $i < $length; $i++) {
                $char = mb_substr($word, $i, 1, 'UTF-8');
                $this->grid[$row + $i][$col] = $char;
            }
        }
    }
    
    /**
     * パズルデータを構築
     */
    private function build_enhanced_puzzle_data($placed_words) {
        $hints = $this->generate_enhanced_hints($placed_words);
        
        $word_count = count($placed_words);
        $title = "日本語クロスワードパズル（{$word_count}単語）";
        $description = "自動生成された{$word_count}個の単語を含む日本語クロスワードパズルです。";
        
        // 通常のゲーム用グリッド（空マスは空のまま）
        $game_grid = $this->create_answer_grid($placed_words);
        
        // ギブアップ用の完全な正解グリッド（空マスにも正解文字を設定）
        $complete_answer_grid = $this->create_complete_answer_grid($placed_words);
        
        return array(
            'title' => $title,
            'description' => $description,
            'difficulty' => $this->determine_difficulty($placed_words),
            'grid_data' => json_encode(array(
                'size' => $this->size,
                'grid' => $game_grid
            )),
            'complete_answer_grid_data' => json_encode(array(
                'size' => $this->size,
                'grid' => $complete_answer_grid
            )),
            'words_data' => json_encode($placed_words),
            'hints_data' => json_encode($hints)
        );
    }
    
    /**
     * 難易度を判定
     */
    private function determine_difficulty($placed_words) {
        if (empty($placed_words)) {
            return 'unknown';
        }
        
        $avg_length = 0;
        foreach ($placed_words as $word => $data) {
            $avg_length += mb_strlen($word, 'UTF-8');
        }
        $avg_length = $avg_length / count($placed_words);
        
        if ($avg_length <= 4) {
            return 'easy';
        } elseif ($avg_length <= 7) {
            return 'medium';
        } else {
            return 'hard';
        }
    }
    
    /**
     * 強化されたヒントを自動生成
     */
    private function generate_enhanced_hints($placed_words) {
        $hints = array();
        
        foreach ($placed_words as $word => $data) {
            $hints[$word] = $this->generate_enhanced_hint_for_word($word);
        }
        
        return $hints;
    }
    
    /**
     * 単語の強化されたヒントを生成
     */
    private function generate_enhanced_hint_for_word($word) {
        $japanese_hints = array(
            'ねこ' => 'ペットとして人気の動物。夜行性で、ネズミを捕る',
            'いぬ' => '忠実な友達。番犬としても活躍する',
            'うし' => '農耕に使われる大きな動物。牛乳を出す',
            'うま' => '競走や乗馬に使われる動物。足が速い',
            'ひつじ' => '毛が刈られて毛糸になる動物',
            'とり' => '空を飛ぶ動物。卵を産む',
            'さかな' => '海や川に住む生き物。泳ぐのが得意',
            'くも' => '8本の足を持つ節足動物。巣を作る',
            'あり' => '小さな昆虫。集団で生活する',
            'ちょう' => '美しい羽を持つ昆虫。花の蜜を吸う',
            'ほし' => '夜空に輝く光。願い事をすると叶うと言われる',
            'つき' => '地球の衛星。満月や三日月がある',
            'そら' => '頭上に広がる青い空間。雲が浮かぶ',
            'みず' => '生命に必要な液体。透明で無味無臭',
            'ひ' => '暖かさと光をくれるもの。太陽の光',
            'つち' => '植物が育つ場所。畑や庭にある',
            'やま' => '高い地形。登ると景色が良い',
            'うみ' => '塩水で満たされた場所。泳いだり釣りができる',
            'かわ' => '水が流れる場所。上流から下流へ',
            'もり' => '木が生い茂る場所。自然が豊か',
            'ほん' => '知識の宝庫。読書で楽しめる',
            'かみ' => '文字を書くための薄い素材',
            'えんぴつ' => '文字を書く道具。芯がある',
            'けしゴム' => '鉛筆の文字を消す道具',
            'かばん' => '物を入れて運ぶ袋',
            'くつ' => '足を保護する履物',
            'ぼうし' => '頭にかぶるもの。日差しや寒さから守る',
            'めがね' => '視力を矯正する道具',
            'ごはん' => '米を炊いた主食',
            'パン' => '小麦粉で作った主食',
            'おちゃ' => '茶葉から作る飲み物',
            'くだもの' => '甘い植物の実。ビタミンが豊富',
            'やさい' => '栄養豊富な植物。健康に良い',
            'にく' => '動物の筋肉。タンパク質が豊富',
            'ぎゅうにゅう' => '牛から取れる白い液体',
            'あか' => '情熱的な色。信号機の止まれ',
            'あお' => '空や海の色。信号機の進め',
            'きいろ' => '太陽やレモンの色',
            'みどり' => '植物の葉の色。自然を表す',
            'くろ' => '夜の色。闇を表す',
            'しろ' => '雪の色。清潔さを表す',
            'ちゃいろ' => '木や土の色。落ち着いた色',
            'ピンク' => '桜の花の色。可愛らしい色',
            'たのしい' => '心が明るくなる気持ち',
            'かなしい' => '心が痛む気持ち',
            'うれしい' => '心が喜ぶ気持ち',
            'おこる' => '怒りの感情。眉間にしわが寄る',
            'わらう' => '声を出して喜ぶ。楽しい時',
            'ない' => '涙が出る感情。悲しい時',
            'ねむい' => '眠りたい気持ち。目が重い',
            'おなかすいた' => '空腹の状態。食べ物が欲しい',
            'あるく' => '足で地面を踏んで進む',
            'はしる' => '速く移動する。競争する',
            'とぶ' => '空中に浮く。鳥のように',
            'およぐ' => '水中で移動する。魚のように',
            'のぼる' => '上に向かって進む。階段を',
            'おりる' => '下に向かって進む。坂を',
            'まわる' => '円を描くように動く',
            'とまる' => '動きを止める。静止する',
            'コンピューター' => '情報処理を行う機械。インターネットに接続できる',
            'インターネット' => '世界中のコンピューターをつなぐネットワーク',
            'でんわ' => '遠くの人と話せる道具。固定電話や携帯電話',
            'テレビ' => '映像と音声を送る放送。ニュースやドラマを見る',
            'ラジオ' => '音声を送る放送。音楽やトークを聴く',
            'スマートフォン' => '電話機能付きの小型コンピューター',
            'がっこう' => '教育を受ける場所。先生が教えてくれる',
            'だいがく' => '高等教育を受ける場所。専門的な勉強ができる',
            'びょういん' => '病気を治す場所。医師が診察する',
            'としょかん' => '本を借りられる場所。静かに読書できる',
            'びじゅつかん' => '美術品を展示する場所。絵画や彫刻がある',
            'こうえん' => '憩いの場。散歩やピクニックができる',
            'レストラン' => '食事を提供する店。外食が楽しめる',
            'くるま' => '四輪の乗り物。運転して移動する',
            'じてんしゃ' => '二輪の乗り物。ペダルを漕いで進む',
            'でんしゃ' => '線路上を走る乗り物。多くの人を運ぶ',
            'ひこうき' => '空を飛ぶ乗り物。遠くまで短時間で行ける',
            'ふね' => '海や川を進む乗り物。貨物や人を運ぶ',
            'バス' => '多くの人を運ぶ大型車両。路線バスや高速バス',
            'タクシー' => '個人用の有料車両。行きたい場所まで連れて行く',
            'せんせい' => '生徒に知識を教える人。学校で働く',
            'いしゃ' => '病気を治す人。病院で働く',
            'かんごし' => '患者の看護をする人。白衣を着ている',
            'けいさつかん' => '犯罪を防ぐ人。制服を着ている',
            'しょうぼうし' => '火事を消す人。消防車に乗る',
            'エンジニア' => '技術的な仕事をする人。設計や開発を行う',
            'おんがく' => '音の芸術。心を癒す効果がある',
            'えいが' => '映像で物語を伝える芸術。映画館で見る',
            'げきじょう' => '演劇を上演する場所。舞台がある',
            'スポーツ' => '体を動かす運動。健康に良い',
            'ゲーム' => '遊びの一種。テレビゲームやカードゲーム',
            'りょうり' => '食材を調理して作る料理。家庭料理や外食',
            'あさごはん' => '朝の食事。一日の始まり',
            'ひるごはん' => '昼の食事。午後の活動のエネルギー',
            'ばんごはん' => '夜の食事。一日の終わり',
            'はる' => '桜が咲く季節。新しい始まり',
            'なつ' => '暑い季節。海や山でレジャー',
            'あき' => '紅葉の季節。読書の秋',
            'ふゆ' => '寒い季節。雪が降ることも',
            'おとうさん' => '父親。家族の大黒柱',
            'おかあさん' => '母親。家族の中心的存在',
            'おじいさん' => '祖父。家族の長老',
            'おばあさん' => '祖母。家族の知恵袋',
            'おにいさん' => '兄。年上の兄弟',
            'おねえさん' => '姉。年上の姉妹',
            'からだ' => '人間の体。健康が大切',
            'あたま' => '体の上部。思考する場所',
            'め' => '物を見る器官。視覚を司る',
            'みみ' => '音を聞く器官。聴覚を司る',
            'はな' => '匂いを嗅ぐ器官。呼吸もする',
            'くち' => '食べ物を入れる場所。話す時も使う',
            'て' => '物を掴む器官。作業に使う',
            'あし' => '歩くための器官。移動に使う',
            'こころ' => '心。感情や意志の源',
            'せいじ' => '政治。国や地域を治めること',
            'けいざい' => '経済。お金の流れや商売',
            'ぶんか' => '文化。人々の生活様式や芸術',
            'れきし' => '歴史。過去に起こった出来事',
            'ちり' => '地理。土地の様子や位置関係',
            'こくさいかんけい' => '国際関係。国と国の関係',
            'せいふ' => '政府。国を治める組織',
            'かがく' => '科学。自然の法則を研究する',
            'ぎじゅつ' => '技術。科学の応用',
            'けんきゅう' => '研究。新しいことを調べる',
            'はっめい' => '発明。新しいものを作り出す',
            'かいめい' => '解明。謎を解く',
            'せつめい' => '説明。物事を分かりやすく伝える',
            'りかい' => '理解。物事を理解すること',
            'きょういく' => '教育。知識や技能を教えること',
            'がくしゅう' => '学習。新しいことを学ぶ',
            'べんきょう' => '勉強。知識を得るための努力',
            'ちしき' => '知識。知っていること',
            'けいけん' => '経験。実際に体験すること',
            'のうりょく' => '能力。何かをできる力',
            'かんきょう' => '環境。周囲の状況',
            'しぜん' => '自然。人工的でないもの',
            'せいたいけい' => '生態系。生き物の関係',
            'きしょう' => '気象。天気や気候',
            'ちきゅう' => '地球。私たちが住む星',
            'うちゅう' => '宇宙。星や惑星がある空間',
            'しんり' => '心理。心の働き',
            'てつがく' => '哲学。人生や世界について考える',
            'りそう' => '理想。目指すべき姿',
            'かんがえ' => '考え。思考の結果',
            'いみ' => '意味。言葉や物事の内容',
            'もくてき' => '目的。目指す目標',
            'げいじゅつ' => '芸術。美を表現する活動',
            'ぶんがく' => '文学。言葉による芸術',
            'しが' => '詩歌。短い詩や和歌',
            'しょうせつ' => '小説。物語を書いた本',
            'え' => '絵。視覚的な芸術作品',
            'おんがく' => '音楽。音による芸術',
            'ぶんか' => '文化。人々の生活様式',
            'さんぎょう' => '産業。物を作る仕事',
            'しょうぎょう' => '商業。物を売り買いする仕事',
            'ぎょうむ' => '業務。仕事の内容',
            'しごと' => '仕事。生活のための労働',
            'しゃかい' => '社会。人々が集まって生活する場',
            'こくさい' => '国際。国と国の間',
            'がいこう' => '外交。国と国の交渉',
            'きょうりょく' => '協力。力を合わせること',
            'とりひき' => '取引。商売のやり取り',
            'しょうひん' => '商品。売り買いされる物',
            'ゆにゅう' => '輸入。外国から物を買うこと'
        );
        
        if (isset($japanese_hints[$word])) {
            return $japanese_hints[$word];
        }
        
        $length = mb_strlen($word, 'UTF-8');
        if ($length <= 3) {
            return "短い単語（{$length}文字）です。基本的な生活用語です。";
        } elseif ($length <= 5) {
            return "中程度の単語（{$length}文字）です。日常的に使う言葉です。";
        } elseif ($length <= 8) {
            return "長い単語（{$length}文字）です。少し難しい概念かもしれません。";
        } else {
            return "とても長い単語（{$length}文字）です。専門的な用語かもしれません。";
        }
    }
    
    /**
     * より良いパズルを生成（複数回試行）
     */
    public function generate_best_puzzle($difficulty = 'medium', $size = 15, $word_count = 12, $attempts = 8) {
        $best_puzzle = null;
        $best_score = 0;
        
        for ($i = 0; $i < $attempts; $i++) {
            $puzzle_data = $this->generate_puzzle($difficulty, $size, $word_count);
            $placed_words = json_decode($puzzle_data['words_data'], true);
            $score = $this->evaluate_puzzle($placed_words);
            
            if ($score > $best_score) {
                $best_score = $score;
                $best_puzzle = $puzzle_data;
            }
        }
        
        return $best_puzzle;
    }
    
    /**
     * パズルの品質を評価
     */
    public function evaluate_puzzle($placed_words) {
        $score = 0;
        $total_words = count($placed_words);
        
        if ($total_words == 0) {
            return 0;
        }
        
        $filled_cells = 0;
        for ($row = 0; $row < $this->size; $row++) {
            for ($col = 0; $col < $this->size; $col++) {
                if ($this->grid[$row][$col] !== '') {
                    $filled_cells++;
                }
            }
        }
        
        $density = $filled_cells / ($this->size * $this->size);
        $score += $density * 50;
        
        $score += min($total_words * 4, 40);
        
        $lengths = array();
        foreach ($placed_words as $word => $data) {
            $lengths[] = mb_strlen($word, 'UTF-8');
        }
        $length_variety = count(array_unique($lengths));
        $score += $length_variety * 3;
        
        $crossing_score = $this->calculate_crossing_score($placed_words);
        $score += $crossing_score * 2;
        
        $character_variety = $this->calculate_character_variety($placed_words);
        $score += $character_variety * 1.5;
        
        return min($score, 100);
    }
    
    /**
     * 交差スコアを計算
     */
    private function calculate_crossing_score($placed_words) {
        $total_crossings = 0;
        
        foreach ($placed_words as $word => $data) {
            $crossings = $this->count_crossings($word, $data['row'], $data['col'], $data['direction']);
            $total_crossings += $crossings;
        }
        
        return $total_crossings;
    }
    
    /**
     * 文字の多様性を計算
     */
    private function calculate_character_variety($placed_words) {
        $characters = array();
        
        foreach ($placed_words as $word => $data) {
            $length = mb_strlen($word, 'UTF-8');
            for ($i = 0; $i < $length; $i++) {
                $char = mb_substr($word, $i, 1, 'UTF-8');
                if (!in_array($char, $characters)) {
                    $characters[] = $char;
                }
            }
        }
        
        return count($characters);
    }
    
    /**
     * 正解グリッドを作成（空マスは空のまま、単語が配置されているマスのみ正解文字を設定）
     */
    private function create_answer_grid($placed_words) {
        $answer_grid = array();
        
        // グリッドを初期化
        for ($row = 0; $row < $this->size; $row++) {
            $answer_grid[$row] = array();
            for ($col = 0; $col < $this->size; $col++) {
                $answer_grid[$row][$col] = '';
            }
        }
        
        // 配置された単語の正解文字のみ設定（空マスは空のまま）
        foreach ($placed_words as $word => $data) {
            $length = mb_strlen($word, 'UTF-8');
            $row = $data['row'];
            $col = $data['col'];
            $direction = $data['direction'];
            
            for ($i = 0; $i < $length; $i++) {
                $char = mb_substr($word, $i, 1, 'UTF-8');
                
                if ($direction === 'horizontal') {
                    $answer_grid[$row][$col + $i] = $char;
                } else {
                    $answer_grid[$row + $i][$col] = $char;
                }
            }
        }
        
        return $answer_grid;
    }
    
    /**
     * 空マスに配置されるべき正解文字を取得（ギブアップ時用）
     */
    private function get_correct_character_for_empty_cell($row, $col, $placed_words) {
        // 日本語の文字セットから適切な文字を選択
        $japanese_chars = array(
            'あ', 'い', 'う', 'え', 'お',
            'か', 'き', 'く', 'け', 'こ',
            'さ', 'し', 'す', 'せ', 'そ',
            'た', 'ち', 'つ', 'て', 'と',
            'な', 'に', 'ぬ', 'ね', 'の',
            'は', 'ひ', 'ふ', 'へ', 'ほ',
            'ま', 'み', 'む', 'め', 'も',
            'や', 'ゆ', 'よ',
            'ら', 'り', 'る', 'れ', 'ろ',
            'わ', 'を', 'ん'
        );
        
        // 周囲の文字との調和を考慮して文字を選択
        $surrounding_chars = array();
        
        // 上下左右の文字を取得
        if ($row > 0 && isset($this->grid[$row - 1][$col]) && $this->grid[$row - 1][$col] !== '') {
            $surrounding_chars[] = $this->grid[$row - 1][$col];
        }
        if ($row < $this->size - 1 && isset($this->grid[$row + 1][$col]) && $this->grid[$row + 1][$col] !== '') {
            $surrounding_chars[] = $this->grid[$row + 1][$col];
        }
        if ($col > 0 && isset($this->grid[$row][$col - 1]) && $this->grid[$row][$col - 1] !== '') {
            $surrounding_chars[] = $this->grid[$row][$col - 1];
        }
        if ($col < $this->size - 1 && isset($this->grid[$row][$col + 1]) && $this->grid[$row][$col + 1] !== '') {
            $surrounding_chars[] = $this->grid[$row][$col + 1];
        }
        
        // 周囲の文字がある場合は、それらと調和する文字を選択
        if (!empty($surrounding_chars)) {
            // 周囲の文字の種類に基づいて文字を選択
            $vowel_chars = array('あ', 'い', 'う', 'え', 'お');
            $consonant_chars = array('か', 'き', 'く', 'け', 'こ', 'さ', 'し', 'す', 'せ', 'そ', 'た', 'ち', 'つ', 'て', 'と');
            
            $has_vowel = false;
            $has_consonant = false;
            
            foreach ($surrounding_chars as $char) {
                if (in_array($char, $vowel_chars)) {
                    $has_vowel = true;
                }
                if (in_array($char, $consonant_chars)) {
                    $has_consonant = true;
                }
            }
            
            // 周囲に母音が多い場合は子音を、子音が多い場合は母音を選択
            if ($has_vowel && !$has_consonant) {
                $filtered_chars = array_diff($japanese_chars, $vowel_chars);
                return $filtered_chars[array_rand($filtered_chars)];
            } elseif ($has_consonant && !$has_vowel) {
                return $vowel_chars[array_rand($vowel_chars)];
            }
        }
        
        // デフォルト：ランダムに日本語文字を選択
        return $japanese_chars[array_rand($japanese_chars)];
    }
    
    /**
     * ギブアップ用の完全な正解グリッドを作成（空マスにも正解文字を設定）
     */
    private function create_complete_answer_grid($placed_words) {
        $answer_grid = $this->create_answer_grid($placed_words);
        
        // 空マスには正解文字を設定（ギブアップ時に表示するため）
        for ($row = 0; $row < $this->size; $row++) {
            for ($col = 0; $col < $this->size; $col++) {
                if ($answer_grid[$row][$col] === '') {
                    $answer_grid[$row][$col] = $this->get_correct_character_for_empty_cell($row, $col, $placed_words);
                }
            }
        }
        
        return $answer_grid;
    }
}
