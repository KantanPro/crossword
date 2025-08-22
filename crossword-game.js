(function($) {
    'use strict';
    
    $(document).ready(function() {
        // グローバル変数の定義
        let crosswordGame = {
            words: [],
            gridSize: 10,
            grid: [],
            solution: [],
            clues: [],
            isGameActive: false,
            currentScore: 0
        };

        // 初期化
        function init() {
            console.log('クロスワードゲーム初期化開始');
            
            if (typeof crossword_data === 'undefined') {
                console.error('クロスワードデータが読み込まれていません。');
                return;
            }
            
            console.log('クロスワードデータ:', crossword_data);
            
            crosswordGame.words = crossword_data.words || [];
            if (crosswordGame.words.length === 0) {
                console.error('単語データが空です。');
                return;
            }
            
            console.log('単語リスト:', crosswordGame.words);
            console.log('クロスワードゲームオブジェクト:', crosswordGame);
            
            // イベントリスナーの設定
            setupEventListeners();
            
            // 初期パズルの生成
            try {
                generatePuzzle();
                renderGrid();
                crosswordGame.isGameActive = true;
                console.log('初期化完了');
            } catch (error) {
                console.error('パズル生成中にエラーが発生しました:', error);
            }
        }

        // イベントリスナーの設定
        function setupEventListeners() {
            $('#new-game-btn').on('click', function(e) {
                e.preventDefault();
                newGame();
            });

            $('#solve-btn').on('click', function(e) {
                e.preventDefault();
                solvePuzzle();
            });

            // キーボードナビゲーション
            $(document).on('keydown', '.crossword-input', function(e) {
                handleKeyboardNavigation(e);
            });
        }

        // パズルの生成
        function generatePuzzle() {
            console.log('パズル生成開始');
            
            // グリッドを空の状態で初期化
            crosswordGame.grid = Array(crosswordGame.gridSize).fill(null).map(() => Array(crosswordGame.gridSize).fill(''));
            crosswordGame.solution = Array(crosswordGame.gridSize).fill(null).map(() => Array(crosswordGame.gridSize).fill(''));
            crosswordGame.clues = [];
            crosswordGame.currentScore = 0;

            console.log('グリッド初期化完了:', {
                gridSize: crosswordGame.gridSize,
                grid: crosswordGame.grid,
                solution: crosswordGame.solution,
                clues: crosswordGame.clues
            });
            
            // グリッドの状態を詳細にログ出力
            console.log('グリッドの詳細状態:');
            for (let i = 0; i < crosswordGame.gridSize; i++) {
                for (let j = 0; j < crosswordGame.gridSize; j++) {
                    if (crosswordGame.grid[i][j] !== '') {
                        console.log(`[${i},${j}]: "${crosswordGame.grid[i][j]}"`);
                    }
                }
            }

            const placedWords = [];
            const maxAttempts = 100;
            let attempts = 0;

            while (placedWords.length < 5 && attempts < maxAttempts) {
                const word = crosswordGame.words[Math.floor(Math.random() * crosswordGame.words.length)];
                if (placedWords.includes(word)) {
                    attempts++;
                    continue;
                }
                
                const orientation = Math.random() < 0.5 ? 'across' : 'down';
                const row = Math.floor(Math.random() * crosswordGame.gridSize);
                const col = Math.floor(Math.random() * crosswordGame.gridSize);

                console.log(`単語配置試行: ${word} (${orientation}) at [${row}, ${col}]`);

                if (canPlaceWord(word, row, col, orientation)) {
                    placeWord(word, row, col, orientation);
                    crosswordGame.clues.push({ 
                        word: word, 
                        row: row, 
                        col: col, 
                        orientation: orientation,
                        clue: generateClue(word)
                    });
                    placedWords.push(word);
                    console.log(`単語配置成功: ${word} at [${row}, ${col}]`);
                } else {
                    console.log(`単語配置失敗: ${word} at [${row}, ${col}]`);
                }
                attempts++;
            }

            console.log('パズル生成完了:', {
                placedWords: placedWords,
                clues: crosswordGame.clues,
                attempts: attempts
            });

            // ヒントの表示
            displayClues();
        }

        // 単語を配置できるかチェック
        function canPlaceWord(word, row, col, orientation) {
            if (orientation === 'across') {
                if (col + word.length > crosswordGame.gridSize) return false;
                for (let i = 0; i < word.length; i++) {
                    if (crosswordGame.grid[row][col + i] !== '' && 
                        crosswordGame.grid[row][col + i] !== word[i]) {
                        return false;
                    }
                }
            } else {
                if (row + word.length > crosswordGame.gridSize) return false;
                for (let i = 0; i < word.length; i++) {
                    if (crosswordGame.grid[row + i][col] !== '' && 
                        crosswordGame.grid[row + i][col] !== word[i]) {
                        return false;
                    }
                }
            }
            return true;
        }

        // 単語を配置
        function placeWord(word, row, col, orientation) {
            if (orientation === 'across') {
                for (let i = 0; i < word.length; i++) {
                    crosswordGame.grid[row][col + i] = 'INPUT'; // 入力フィールドが必要なセルを示す
                    crosswordGame.solution[row][col + i] = word[i];
                }
            } else {
                for (let i = 0; i < word.length; i++) {
                    crosswordGame.grid[row + i][col] = 'INPUT'; // 入力フィールドが必要なセルを示す
                    crosswordGame.solution[row + i][col] = word[i];
                }
            }
        }

        // ヒントの生成（シンプルな例）
        function generateClue(word) {
            const clues = {
                'WORDPRESS': '最も人気のあるブログプラットフォーム',
                'PLUGIN': '機能を拡張するアドオン',
                'THEME': 'サイトの見た目を決めるデザイン',
                'POST': 'ブログの記事',
                'PAGE': '固定ページ',
                'WIDGET': 'サイドバーに配置する要素',
                'EDITOR': 'コンテンツを編集する画面',
                'ADMIN': '管理者',
                'USER': 'ユーザー',
                'MEDIA': '画像やファイル',
                'DATABASE': 'データを保存する場所',
                'SERVER': 'サーバー',
                'HOSTING': 'ホスティング',
                'UPDATES': '更新',
                'CUSTOMIZE': 'カスタマイズ',
                'SETTINGS': '設定',
                'SHORTCODE': 'ショートコード',
                'BLOG': 'ブログ',
                'COMMENT': 'コメント',
                'CATEGORY': 'カテゴリー'
            };
            return clues[word] || `「${word}」に関連する単語`;
        }

        // グリッドの表示
        function renderGrid() {
            const gridContainer = $('#crossword-grid');
            gridContainer.empty();
            
            // CSSグリッドの設定
            gridContainer.css({
                'grid-template-columns': `repeat(${crosswordGame.gridSize}, 30px)`,
                'grid-template-rows': `repeat(${crosswordGame.gridSize}, 30px)`
            });

            for (let i = 0; i < crosswordGame.gridSize; i++) {
                for (let j = 0; j < crosswordGame.gridSize; j++) {
                    const cell = $('<div>').addClass('crossword-cell');
                    
                    if (crosswordGame.grid[i][j] === 'INPUT') {
                        const input = $('<input>').attr({
                            'type': 'text',
                            'maxlength': '1',
                            'data-row': i,
                            'data-col': j,
                            'autocomplete': 'off',
                            'spellcheck': 'false'
                        }).addClass('crossword-input');
                        
                        // セルの番号を表示（単語の開始位置の場合）
                        if (isWordStart(i, j)) {
                            const number = getWordNumber(i, j);
                            if (number > 0) {
                                cell.append($('<span>').addClass('cell-number').text(number));
                            }
                        }
                        
                        cell.append(input);
                    } else {
                        cell.addClass('empty-cell');
                    }
                    gridContainer.append(cell);
                }
            }

            // 入力フィールドのイベント設定
            $('.crossword-input').on('input', function() {
                this.value = this.value.toUpperCase();
                checkCompletion();
            });
        }

        // 単語の開始位置かチェック
        function isWordStart(row, col) {
            return crosswordGame.clues.some(clue => 
                (clue.row === row && clue.col === col)
            );
        }

        // セル番号を取得
        function getWordNumber(row, col) {
            let number = 1;
            for (let clue of crosswordGame.clues) {
                if (clue.row === row && clue.col === col) {
                    return number;
                }
                number++;
            }
            return 0;
        }

        // ヒントの表示
        function displayClues() {
            // 必要に応じてヒントを表示する要素を追加
            // 現在は実装していませんが、拡張可能です
        }

        // キーボードナビゲーション
        function handleKeyboardNavigation(e) {
            const $current = $(e.target);
            const row = parseInt($current.data('row'));
            const col = parseInt($current.data('col'));
            
            let $next;
            
            switch(e.keyCode) {
                case 37: // 左
                    $next = $(`.crossword-input[data-row="${row}"][data-col="${col - 1}"]`);
                    break;
                case 38: // 上
                    $next = $(`.crossword-input[data-row="${row - 1}"][data-col="${col}"]`);
                    break;
                case 39: // 右
                    $next = $(`.crossword-input[data-row="${row}"][data-col="${col + 1}"]`);
                    break;
                case 40: // 下
                    $next = $(`.crossword-input[data-row="${row + 1}"][data-col="${col}"]`);
                    break;
                default:
                    return;
            }
            
            if ($next.length > 0) {
                $next.focus();
                e.preventDefault();
            }
        }

        // 完了チェック
        function checkCompletion() {
            let correct = true;
            let filledCells = 0;
            let totalCells = 0;
            
            $('.crossword-input').each(function() {
                const row = $(this).data('row');
                const col = $(this).data('col');
                const inputValue = this.value.toUpperCase();
                totalCells++;
                
                if (inputValue !== '') {
                    filledCells++;
                    if (inputValue !== crosswordGame.solution[row][col]) {
                        correct = false;
                    }
                } else {
                    correct = false;
                }
            });

            if (correct && filledCells === totalCells) {
                showMessage('おめでとうございます！正解です！', 'success');
                crosswordGame.isGameActive = false;
                // 必要に応じてスコアを保存
            } else {
                // 進捗を表示
                const progress = Math.round((filledCells / totalCells) * 100);
                if (progress > 0) {
                    showMessage(`進捗: ${progress}%`, 'info');
                }
            }
        }

        // メッセージの表示
        function showMessage(message, type) {
            const $message = $('#crossword-message');
            $message.text(message).removeClass('success info error').addClass(type);
        }

        // パズルを解く
        function solvePuzzle() {
            $('.crossword-input').each(function() {
                const row = $(this).data('row');
                const col = $(this).data('col');
                $(this).val(crosswordGame.solution[row][col]).prop('disabled', true);
            });
            showMessage('正解が表示されました。', 'info');
            crosswordGame.isGameActive = false;
        }

        // 新しいゲーム
        function newGame() {
            crosswordGame.isGameActive = true;
            generatePuzzle();
            renderGrid();
            showMessage('新しい問題が生成されました。', 'info');
        }

        // 初期化の実行
        console.log('クロスワードゲームスクリプト読み込み完了');
        init();
        console.log('クロスワードゲーム初期化関数呼び出し完了');
    });
})(jQuery);
