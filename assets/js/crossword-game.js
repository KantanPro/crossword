/**
 * クロスワードゲームのJavaScript
 */
(function($) {
    'use strict';
    
    class CrosswordGame {
        constructor() {
            try {
                console.log('CrosswordGame: Constructor started');
                
                // jQueryとcrossword_ajaxの存在確認
                if (typeof $ === 'undefined') {
                    console.error('CrosswordGame: jQuery is not loaded!');
                    return;
                }
                
                if (typeof crossword_ajax === 'undefined') {
                    console.error('CrosswordGame: crossword_ajax is not defined!');
                    return;
                }
                
                console.log('CrosswordGame: jQuery and crossword_ajax are available');
                console.log('CrosswordGame: crossword_ajax data:', crossword_ajax);
                
                this.gameContainer = $('.crossword-game');
                console.log('CrosswordGame: Game container found:', this.gameContainer.length);
                
                if (this.gameContainer.length === 0) {
                    console.error('CrosswordGame: No .crossword-game container found!');
                    return;
                }
                
                this.puzzleId = this.gameContainer.data('puzzle-id');
                console.log('CrosswordGame: Puzzle ID:', this.puzzleId);
                
                this.startTime = Date.now();
                this.timer = null;
                this.currentInput = null;
                this.words = {};
                this.hints = {};
                this.grid = [];
                this.size = 0;
                this.answerData = null;
                
                console.log('CrosswordGame: Constructor properties set, calling init()');
                this.init();
                
            } catch (error) {
                console.error('CrosswordGame: Constructor error:', error);
                console.error('CrosswordGame: Error stack:', error.stack);
            }
        }
        
        init() {
            try {
                console.log('CrosswordGame: Initializing...');
                console.log('CrosswordGame: Game container found:', this.gameContainer.length);
                console.log('CrosswordGame: Puzzle ID:', this.puzzleId);
                
                this.loadPuzzleData();
                console.log('CrosswordGame: loadPuzzleData completed');
                
                this.bindEvents();
                console.log('CrosswordGame: bindEvents completed');
                
                this.startTimer();
                console.log('CrosswordGame: startTimer completed');
                
                console.log('CrosswordGame: Initialization complete');
                
            } catch (error) {
                console.error('CrosswordGame: Init error:', error);
                console.error('CrosswordGame: Error stack:', error.stack);
            }
        }
        
        /**
         * パズルデータの読み込み
         */
        loadPuzzleData() {
            console.log('CrosswordGame: loadPuzzleData started');
            
            // ページに埋め込まれたデータから読み込み
            const gridData = this.parseGridData();
            if (gridData) {
                console.log('CrosswordGame: Grid data loaded successfully');
                this.size = gridData.size;
                this.grid = gridData.grid;
                this.words = this.parseWordsData();
                this.hints = this.parseHintsData();
                this.setupGrid();
            } else {
                console.error('CrosswordGame: Failed to load grid data');
                // エラーメッセージを表示
                this.showMessage('パズルデータの読み込みに失敗しました。', 'error');
            }
        }
        
        /**
         * グリッドデータの解析
         */
        parseGridData() {
            try {
                console.log('CrosswordGame: parseGridData started');
                
                // グリッドのサイズを取得
                const gridContainer = this.gameContainer.find('.crossword-grid');
                console.log('CrosswordGame: Grid container found:', gridContainer.length);
                
                if (gridContainer.length === 0) {
                    console.error('CrosswordGame: No grid container found');
                    return null;
                }
                
                const style = gridContainer.attr('style');
                console.log('CrosswordGame: Grid style:', style);
                
                const match = style.match(/repeat\((\d+), 1fr\)/);
                if (match) {
                    this.size = parseInt(match[1]);
                    console.log('CrosswordGame: Grid size from style:', this.size);
                } else {
                    console.error('CrosswordGame: Could not parse grid size from style');
                    return null;
                }
                
                if (isNaN(this.size) || this.size <= 0) {
                    console.error('CrosswordGame: Invalid grid size:', this.size);
                    return null;
                }
                
                // 既存のグリッドからデータを構築
                const grid = [];
                for (let row = 0; row < this.size; row++) {
                    grid[row] = [];
                    for (let col = 0; col < this.size; col++) {
                        const cell = this.gameContainer.find(`[data-row="${row}"][data-col="${col}"]`);
                        const input = cell.find('.crossword-input');
                        if (input.length > 0) {
                            grid[row][col] = input.val();
                        } else {
                            grid[row][col] = '';
                        }
                    }
                }
                
                console.log('CrosswordGame: Grid data parsed successfully, size:', this.size);
                return { size: this.size, grid: grid };
                
            } catch (error) {
                console.error('CrosswordGame: Grid data parsing failed:', error);
                return null;
            }
        }
        
        /**
         * 単語データの解析
         */
        parseWordsData() {
            const words = {};
            this.gameContainer.find('.hints-container li').each(function() {
                const word = $(this).data('word');
                const direction = $(this).closest('.hints-horizontal').length > 0 ? 'horizontal' : 'vertical';
                words[word] = { direction: direction };
            });
            return words;
        }
        
        /**
         * ヒントデータの解析
         */
        parseHintsData() {
            const hints = {};
            this.gameContainer.find('.hints-container li').each(function() {
                const word = $(this).data('word');
                const hint = $(this).find('.hint-text').text();
                hints[word] = hint;
            });
            return hints;
        }
        
        /**
         * グリッドのセットアップ
         */
        setupGrid() {
            console.log('CrosswordGame: setupGrid started');
            console.log('CrosswordGame: Current size:', this.size);
            console.log('CrosswordGame: Current grid:', this.grid);
            
            // 必要なプロパティの存在確認
            if (!this.size || !this.grid || !Array.isArray(this.grid)) {
                console.error('CrosswordGame: setupGrid called with invalid data');
                return;
            }
            
            // グリッドコンテナを作成
            const gridContainer = $('<div class="crossword-grid"></div>');
            
            // グリッドの作成
            for (let row = 0; row < this.size; row++) {
                const rowDiv = $('<div class="crossword-row"></div>');
                
                for (let col = 0; col < this.size; col++) {
                    const cell = $('<div class="crossword-cell" data-row="' + row + '" data-col="' + col + '"></div>');
                    
                    // セルに単語情報を設定
                    Object.keys(this.words).forEach(word => {
                        const wordData = this.words[word];
                        if (this.isCellInWord(row, col, word, wordData)) {
                            cell.attr('data-word', word);
                            cell.addClass('word-cell');
                        }
                    });
                    
                    // セルに文字がある場合（単語が配置されている場合）
                    if (this.grid[row] && this.grid[row][col] !== '') {
                        const input = $('<input type="text" class="crossword-input" maxlength="1" readonly />');
                        input.val(this.grid[row][col]);
                        cell.append(input);
                        cell.addClass('filled-cell');
                    } else {
                        // 空マスには入力フィールドを表示
                        const input = $('<input type="text" class="crossword-input" maxlength="1" />');
                        cell.append(input);
                        cell.addClass('empty-cell');
                    }
                    
                    rowDiv.append(cell);
                }
                
                gridContainer.append(rowDiv);
            }
            
            // 既存のグリッドを置き換え
            this.gameContainer.find('.crossword-grid').remove();
            this.gameContainer.append(gridContainer);
            
            console.log('CrosswordGame: setupGrid completed');
        }
        
        /**
         * イベントのバインド
         */
        bindEvents() {
            // 入力フィールドのイベント
            this.gameContainer.on('input', '.crossword-input', this.handleInput.bind(this));
            this.gameContainer.on('keydown', '.crossword-input', this.handleKeydown.bind(this));
            this.gameContainer.on('focus', '.crossword-input', this.handleFocus.bind(this));
            
            // ボタンのイベント
            this.gameContainer.on('click', '.crossword-check-btn', this.checkAnswers.bind(this));
            this.gameContainer.on('click', '.crossword-reset-btn', this.resetGame.bind(this));
            this.gameContainer.on('click', '.crossword-save-btn', this.saveProgress.bind(this));
            this.gameContainer.on('click', '.crossword-giveup-btn', this.giveUp.bind(this));
            
            // ヒントのクリックイベント
            this.gameContainer.on('click', '.hints-container li', this.highlightWord.bind(this));
            
            // デバッグ用：イベントバインドの確認
            console.log('CrosswordGame: Events bound successfully');
            console.log('CrosswordGame: Give up button found:', this.gameContainer.find('.crossword-giveup-btn').length);
        }
        
        /**
         * 入力処理
         */
        handleInput(e) {
            const input = $(e.target);
            const value = input.val().toUpperCase();
            
            // 入力値を制限
            if (value.length > 1) {
                input.val(value.charAt(0));
            }
            
            // 次のセルに移動
            if (value.length === 1) {
                this.moveToNextCell(input);
            }
        }
        
        /**
         * キーダウン処理
         */
        handleKeydown(e) {
            const input = $(e.target);
            const cell = input.closest('.crossword-cell');
            const row = parseInt(cell.data('row'));
            const col = parseInt(cell.data('col'));
            
            switch (e.keyCode) {
                case 37: // 左矢印
                    this.moveToCell(row, col - 1);
                    break;
                case 38: // 上矢印
                    this.moveToCell(row - 1, col);
                    break;
                case 39: // 右矢印
                    this.moveToCell(row, col + 1);
                    break;
                case 40: // 下矢印
                    this.moveToCell(row + 1, col);
                    break;
                case 8: // バックスペース
                    if (input.val() === '') {
                        this.moveToPreviousCell(input);
                    }
                    break;
            }
        }
        
        /**
         * フォーカス処理
         */
        handleFocus(e) {
            this.currentInput = $(e.target);
            this.currentInput.addClass('focused');
        }
        
        /**
         * 次のセルに移動
         */
        moveToNextCell(currentInput) {
            const cell = currentInput.closest('.crossword-cell');
            const row = parseInt(cell.data('row'));
            const col = parseInt(cell.data('row'));
            
            // 右のセルに移動
            this.moveToCell(row, col + 1);
        }
        
        /**
         * 前のセルに移動
         */
        moveToPreviousCell(currentInput) {
            const cell = currentInput.closest('.crossword-cell');
            const row = parseInt(cell.data('row'));
            const col = parseInt(cell.data('col'));
            
            // 左のセルに移動
            this.moveToCell(row, col - 1);
        }
        
        /**
         * 指定されたセルに移動
         */
        moveToCell(row, col) {
            if (row >= 0 && row < this.size && col >= 0 && col < this.size) {
                const cell = this.gameContainer.find(`[data-row="${row}"][data-col="${col}"]`);
                const input = cell.find('.crossword-input');
                if (input.length > 0) {
                    input.focus();
                }
            }
        }
        
        /**
         * セルが特定の単語に含まれているかを判定
         */
        isCellInWord(row, col, word, wordData) {
            if (!wordData || !wordData.row || !wordData.col || !wordData.direction) {
                return false;
            }
            
            const wordLength = word.length;
            
            if (wordData.direction === 'horizontal') {
                return row === wordData.row && 
                       col >= wordData.col && 
                       col < wordData.col + wordLength;
            } else {
                return col === wordData.col && 
                       row >= wordData.row && 
                       row < wordData.row + wordLength;
            }
        }
        
        /**
         * 単語のハイライト
         */
        highlightWord(e) {
            const word = $(e.target).data('word');
            const wordData = this.words[word];
            
            if (wordData) {
                // 既存のハイライトをクリア
                this.gameContainer.find('.crossword-cell').removeClass('highlighted');
                
                // 単語のセルをハイライト
                if (wordData.direction === 'horizontal') {
                    for (let i = 0; i < word.length; i++) {
                        const cell = this.gameContainer.find(`[data-row="${wordData.row}"][data-col="${wordData.col + i}"]`);
                        cell.addClass('highlighted');
                    }
                } else {
                    for (let i = 0; i < word.length; i++) {
                        const cell = this.gameContainer.find(`[data-row="${wordData.row + i}"][data-col="${wordData.col}"]`);
                        cell.addClass('highlighted');
                    }
                }
            }
        }
        
        /**
         * 答えのチェック
         */
        checkAnswers() {
            let allCorrect = true;
            let correctCount = 0;
            let totalCount = 0;
            
            // 各単語をチェック
            Object.keys(this.words).forEach(word => {
                const isCorrect = this.checkWord(word);
                if (isCorrect) {
                    correctCount++;
                    this.markWordAsCompleted(word);
                } else {
                    allCorrect = false;
                }
                totalCount++;
            });
            
            // 結果の表示
            if (allCorrect) {
                this.showMessage(crossword_ajax.strings.puzzle_completed, 'success');
                this.gameContainer.addClass('completed');
                this.stopTimer();
            } else {
                this.showMessage(`${correctCount}/${totalCount} の単語が正解です。`, 'info');
            }
        }
        
        /**
         * 単語のチェック
         */
        checkWord(word) {
            const wordData = this.words[word];
            if (!wordData) return false;
            
            let currentWord = '';
            
            if (wordData.direction === 'horizontal') {
                for (let i = 0; i < word.length; i++) {
                    const cell = this.gameContainer.find(`[data-row="${wordData.row}"][data-col="${wordData.col + i}"]`);
                    const input = cell.find('.crossword-input');
                    currentWord += input.val() || '';
                }
            } else {
                for (let i = 0; i < word.length; i++) {
                    const cell = this.gameContainer.find(`[data-row="${wordData.row + i}"][data-col="${wordData.col}"]`);
                    const input = cell.find('.crossword-input');
                    currentWord += input.val() || '';
                }
            }
            
            return currentWord.toUpperCase() === word.toUpperCase();
        }
        
        /**
         * 単語を完了済みとしてマーク
         */
        markWordAsCompleted(word) {
            this.gameContainer.find(`[data-word="${word}"]`).addClass('completed');
        }
        
        /**
         * ゲームのリセット
         */
        resetGame() {
            if (confirm('ゲームをリセットしますか？')) {
                this.gameContainer.find('.crossword-input').val('');
                this.gameContainer.find('.hints-container li').removeClass('completed');
                this.gameContainer.removeClass('completed');
                this.startTime = Date.now();
                this.startTimer();
                this.showMessage('ゲームをリセットしました。', 'info');
            }
        }
        
        /**
         * 進捗の保存
         */
        saveProgress() {
            try {
                const progressData = this.getProgressData();
                
                $.ajax({
                    url: crossword_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'crossword_save_progress',
                        puzzle_id: this.puzzleId,
                        progress_data: JSON.stringify(progressData),
                        nonce: crossword_ajax.nonce
                    },
                    success: function(response) {
                        try {
                            if (response.success) {
                                this.showMessage(crossword_ajax.strings.save_progress, 'success');
                            } else {
                                this.showMessage(response.data, 'error');
                            }
                        } catch (error) {
                            console.error('Save progress success handler error:', error);
                            this.showMessage('レスポンス処理中にエラーが発生しました。', 'error');
                        }
                    }.bind(this),
                    error: function(xhr, status, error) {
                        console.error('Save progress AJAX error:', {xhr, status, error});
                        this.showMessage(crossword_ajax.strings.error_occurred, 'error');
                    }.bind(this)
                });
            } catch (error) {
                console.error('Save progress error:', error);
                this.showMessage('進捗保存中にエラーが発生しました。', 'error');
            }
        }
        
        /**
         * 進捗データの取得
         */
        getProgressData() {
            const progress = {
                grid: [],
                completed_words: [],
                time_spent: Math.floor((Date.now() - this.startTime) / 1000)
            };
            
            // グリッドの状態を保存
            for (let row = 0; row < this.size; row++) {
                progress.grid[row] = [];
                for (let col = 0; col < this.size; col++) {
                    const cell = this.gameContainer.find(`[data-row="${row}"][data-col="${col}"]`);
                    const input = cell.find('.crossword-input');
                    progress.grid[row][col] = input.val() || '';
                }
            }
            
            // 完了した単語を保存
            this.gameContainer.find('.hints-container li.completed').each(function() {
                progress.completed_words.push($(this).data('word'));
            });
            
            return progress;
        }
        
        /**
         * タイマーの開始
         */
        startTimer() {
            this.timer = setInterval(() => {
                const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                this.gameContainer.find('.timer-display').text(
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
                );
            }, 1000);
        }
        
        /**
         * タイマーの停止
         */
        stopTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        }
        
        /**
         * メッセージの表示
         */
        showMessage(message, type = 'info') {
            // 既存のメッセージを削除
            this.gameContainer.find('.crossword-message').remove();
            
            const messageDiv = $(`<div class="crossword-message ${type}">${message}</div>`);
            this.gameContainer.prepend(messageDiv);
            
            // 3秒後に自動削除
            setTimeout(() => {
                messageDiv.fadeOut(() => messageDiv.remove());
            }, 3000);
        }
        
        /**
         * ギブアップ処理
         */
        giveUp() {
            console.log('CrosswordGame: giveUp function called');
            
            if (!confirm(crossword_ajax.strings.give_up_confirm)) {
                console.log('CrosswordGame: User cancelled give up');
                return;
            }
            
            console.log('CrosswordGame: Proceeding with give up');
            this.showMessage(crossword_ajax.strings.give_up_processing, 'info');
            
            // ギブアップのAJAXリクエスト
            const ajaxData = {
                action: 'crossword_give_up',
                puzzle_id: this.puzzleId,
                nonce: crossword_ajax.nonce
            };
            
            console.log('CrosswordGame: Sending AJAX request with data:', ajaxData);
            console.log('CrosswordGame: AJAX URL:', crossword_ajax.ajax_url);
            
            $.ajax({
                url: crossword_ajax.ajax_url,
                type: 'POST',
                data: ajaxData,
                success: (response) => {
                    if (response.success) {
                        // 正解データを保存
                        this.answerData = response.data;
                        this.showAnswer(response.data);
                        this.showMessage(crossword_ajax.strings.give_up_success, 'warning');
                        this.stopTimer();
                    } else {
                        this.showMessage(crossword_ajax.strings.give_up_failed + response.data, 'error');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('CrosswordGame: AJAX error details:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status
                    });
                    this.showMessage('ギブアップ処理でエラーが発生しました: ' + error, 'error');
                }
            });
        }
        
        /**
         * 正解の表示
         */
        showAnswer(answerData) {
            try {
                // 完全な正解グリッドを使用（空マスにも正解文字が設定されている）
                const completeGridData = JSON.parse(answerData.complete_answer_grid_data);
                const wordsData = JSON.parse(answerData.words_data);
                
                // グリッドに正解を表示
                for (let row = 0; row < this.size; row++) {
                    for (let col = 0; col < this.size; col++) {
                        const cell = this.gameContainer.find(`[data-row="${row}"][data-col="${col}"]`);
                        const input = cell.find('.crossword-input');
                        
                        // 完全な正解グリッドから正解文字を取得
                        if (completeGridData.grid[row] && completeGridData.grid[row][col] !== '') {
                            input.val(completeGridData.grid[row][col]);
                            input.addClass('answer-revealed');
                            input.attr('readonly', true);
                        }
                    }
                }
                
                // ヒントリストに正解を表示
                this.gameContainer.find('.hints-container li').each(function() {
                    const word = $(this).data('word');
                    if (wordsData[word]) {
                        $(this).addClass('answer-revealed');
                        $(this).append('<span class="answer-text"> - 正解: ' + word + '</span>');
                    }
                });
                
                // ギブアップボタンを無効化
                this.gameContainer.find('.crossword-giveup-btn').prop('disabled', true).text('ギブアップ済み');
                
                // 回答ボタンを表示
                this.showAnswerButton();
                
            } catch (error) {
                console.error('正解表示でエラーが発生しました:', error);
                this.showMessage('正解の表示に失敗しました。', 'error');
            }
        }
        
        /**
         * 回答ボタンの表示
         */
        showAnswerButton() {
            // 既存の回答ボタンを削除
            this.gameContainer.find('.crossword-answer-btn').remove();
            
            // 回答ボタンを作成
            const answerBtn = $('<button class="crossword-answer-btn crossword-btn">' + crossword_ajax.strings.show_answer + '</button>');
            
            // ボタンをゲームコンテナに追加（ギブアップボタンの下）
            const giveupBtn = this.gameContainer.find('.crossword-giveup-btn');
            if (giveupBtn.length > 0) {
                giveupBtn.after(answerBtn);
            } else {
                // ギブアップボタンが見つからない場合は、ボタンコンテナに追加
                const buttonContainer = this.gameContainer.find('.crossword-buttons');
                if (buttonContainer.length > 0) {
                    buttonContainer.append(answerBtn);
                } else {
                    // ボタンコンテナも見つからない場合は、ゲームコンテナの最後に追加
                    this.gameContainer.append(answerBtn);
                }
            }
            
            // 回答ボタンのクリックイベントをバインド
            answerBtn.on('click', this.toggleAnswerDisplay.bind(this));
        }
        
        /**
         * 回答表示の切り替え
         */
        toggleAnswerDisplay() {
            const answerBtn = this.gameContainer.find('.crossword-answer-btn');
            const isShowing = answerBtn.hasClass('showing');
            
            if (isShowing) {
                // 正解を隠す
                this.hideAnswers();
                answerBtn.removeClass('showing').text(crossword_ajax.strings.show_answer);
            } else {
                // 正解を表示
                this.showAnswers();
                answerBtn.addClass('showing').text(crossword_ajax.strings.hide_answer);
            }
        }
        
        /**
         * 正解を表示
         */
        showAnswers() {
            // グリッドの正解を表示
            this.gameContainer.find('.crossword-input.answer-revealed').each(function() {
                const input = $(this);
                const originalValue = input.attr('data-original-value') || input.val();
                input.val(originalValue);
                input.addClass('answer-visible');
            });
            
            // ヒントリストの正解を表示
            this.gameContainer.find('.hints-container li.answer-revealed').each(function() {
                const answerText = $(this).find('.answer-text');
                if (answerText.length > 0) {
                    answerText.show();
                }
            });
        }
        
        /**
         * 正解を隠す
         */
        hideAnswers() {
            // グリッドの正解を隠す
            this.gameContainer.find('.crossword-input.answer-visible').removeClass('answer-visible');
            
            // ヒントリストの正解を隠す
            this.gameContainer.find('.hints-container li.answer-revealed .answer-text').hide();
        }
    }
    
    // DOM読み込み完了後に初期化
    $(document).ready(function() {
        try {
            console.log('CrosswordGame: Document ready, checking for crossword game elements...');
            console.log('CrosswordGame: Found .crossword-game elements:', $('.crossword-game').length);
            
            // クロスワードゲーム要素の詳細確認
            $('.crossword-game').each(function(index) {
                console.log('CrosswordGame: Element', index, ':', {
                    element: this,
                    classes: this.className,
                    data: $(this).data(),
                    html: $(this).html().substring(0, 200) + '...'
                });
            });
            
            if ($('.crossword-game').length > 0) {
                console.log('CrosswordGame: Creating new CrosswordGame instance...');
                const gameInstance = new CrosswordGame();
                console.log('CrosswordGame: Instance created:', gameInstance);
            } else {
                console.log('CrosswordGame: No crossword game elements found');
            }
        } catch (error) {
            console.error('CrosswordGame initialization error:', error);
            console.error('CrosswordGame: Error stack:', error.stack);
        }
    });
    
    // グローバルエラーハンドラー
    window.addEventListener('error', function(event) {
        console.error('Global error in crossword game:', {
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            error: event.error
        });
    });
    
    // 未処理のPromise拒否をキャッチ
    window.addEventListener('unhandledrejection', function(event) {
        console.error('Unhandled promise rejection in crossword game:', event.reason);
    });
    
})(jQuery);
