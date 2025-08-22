/**
 * クロスワードゲーム管理画面のJavaScript
 */
(function($) {
    'use strict';
    
    class CrosswordAdmin {
        constructor() {
            this.gridSize = 5;
            this.grid = [];
            this.words = [];
            this.currentWord = null;
            
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.createGrid();
            this.addWordField();
        }
        
        /**
         * イベントのバインド
         */
        bindEvents() {
            // グリッドサイズの変更
            $('#puzzle-size').on('change', this.onGridSizeChange.bind(this));
            
            // 単語の追加
            $('.add-word').on('click', this.addWordField.bind(this));
            
                    // 単語の削除
        $(document).on('click', '.remove-word', this.removeWordField.bind(this));
        
        // フォームの送信
        $('#crossword-puzzle-form').on('submit', this.onFormSubmit.bind(this));
        
        // パズルの編集
        $(document).on('click', '.edit-puzzle', this.editPuzzle.bind(this));
        
        // パズルの削除
        $(document).on('click', '.delete-puzzle', this.deletePuzzle.bind(this));
        
        // 自動生成
        $('#auto-generate-btn').on('click', this.autoGeneratePuzzle.bind(this));
        }
        
        /**
         * グリッドサイズの変更
         */
        onGridSizeChange(e) {
            this.gridSize = parseInt($(e.target).val());
            this.createGrid();
        }
        
        /**
         * グリッドの作成
         */
        createGrid() {
            const container = $('#grid-container');
            container.empty();
            
            // グリッドの初期化
            this.grid = [];
            for (let row = 0; row < this.gridSize; row++) {
                this.grid[row] = [];
                for (let col = 0; col < this.gridSize; col++) {
                    this.grid[row][col] = '';
                }
            }
            
            // グリッドの表示
            const gridDiv = $('<div class="admin-grid"></div>');
            gridDiv.css('grid-template-columns', `repeat(${this.gridSize}, 1fr)`);
            
            for (let row = 0; row < this.gridSize; row++) {
                for (let col = 0; col < this.gridSize; col++) {
                    const cell = $('<div class="admin-grid-cell"></div>');
                    cell.attr('data-row', row);
                    cell.attr('data-col', col);
                    
                    const input = $('<input type="text" maxlength="1" />');
                    input.on('input', this.onCellInput.bind(this));
                    input.on('focus', this.onCellFocus.bind(this));
                    
                    cell.append(input);
                    gridDiv.append(cell);
                }
            }
            
            container.append(gridDiv);
        }
        
        /**
         * セルの入力処理
         */
        onCellInput(e) {
            const input = $(e.target);
            const cell = input.closest('.admin-grid-cell');
            const row = parseInt(cell.attr('data-row'));
            const col = parseInt(cell.attr('data-col'));
            const value = input.val().toUpperCase();
            
            // 入力値を制限
            if (value.length > 1) {
                input.val(value.charAt(0));
            }
            
            // グリッドデータを更新
            this.grid[row][col] = value;
            
            // 次のセルに移動
            if (value.length === 1) {
                this.moveToNextCell(row, col);
            }
        }
        
        /**
         * セルのフォーカス処理
         */
        onCellFocus(e) {
            const input = $(e.target);
            const cell = input.closest('.admin-grid-cell');
            
            // 既存のアクティブ状態をクリア
            $('.admin-grid-cell').removeClass('active');
            
            // 現在のセルをアクティブに
            cell.addClass('active');
        }
        
        /**
         * 次のセルに移動
         */
        moveToNextCell(row, col) {
            let nextRow = row;
            let nextCol = col + 1;
            
            if (nextCol >= this.gridSize) {
                nextCol = 0;
                nextRow++;
            }
            
            if (nextRow < this.gridSize) {
                const nextCell = $(`.admin-grid-cell[data-row="${nextRow}"][data-col="${nextCol}"]`);
                nextCell.find('input').focus();
            }
        }
        
        /**
         * 単語フィールドの追加
         */
        addWordField() {
            const wordIndex = this.words.length;
            const wordItem = $(`
                <div class="word-item" data-index="${wordIndex}">
                    <input type="text" class="word-text" placeholder="単語" />
                    <select class="word-direction">
                        <option value="horizontal">横</option>
                        <option value="vertical">縦</option>
                    </select>
                    <input type="text" class="word-hint" placeholder="ヒント" />
                    <button type="button" class="remove-word">削除</button>
                </div>
            `);
            
            // 単語データの初期化
            this.words[wordIndex] = {
                text: '',
                direction: 'horizontal',
                hint: '',
                row: 0,
                col: 0
            };
            
            // イベントのバインド
            wordItem.find('.word-text').on('input', this.onWordTextChange.bind(this));
            wordItem.find('.word-direction').on('change', this.onWordDirectionChange.bind(this));
            wordItem.find('.word-hint').on('input', this.onWordHintChange.bind(this));
            
            $('#words-container').append(wordItem);
        }
        
        /**
         * 単語フィールドの削除
         */
        removeWordField(e) {
            const wordItem = $(e.target).closest('.word-item');
            const index = parseInt(wordItem.attr('data-index'));
            
            // 単語データを削除
            this.words.splice(index, 1);
            
            // DOM要素を削除
            wordItem.remove();
            
            // インデックスを再設定
            this.updateWordIndexes();
        }
        
        /**
         * 単語のインデックスを更新
         */
        updateWordIndexes() {
            $('.word-item').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('.word-text').off('input').on('input', this.onWordTextChange.bind(this));
                $(this).find('.word-direction').off('change').on('change', this.onWordDirectionChange.bind(this));
                $(this).find('.word-hint').off('input').on('input', this.onWordHintChange.bind(this));
            }.bind(this));
        }
        
        /**
         * 単語テキストの変更
         */
        onWordTextChange(e) {
            const input = $(e.target);
            const wordItem = input.closest('.word-item');
            const index = parseInt(wordItem.attr('data-index'));
            
            this.words[index].text = input.val().toUpperCase();
        }
        
        /**
         * 単語の方向の変更
         */
        onWordDirectionChange(e) {
            const select = $(e.target);
            const wordItem = select.closest('.word-item');
            const index = parseInt(wordItem.attr('data-index'));
            
            this.words[index].direction = select.val();
        }
        
        /**
         * 単語のヒントの変更
         */
        onWordHintChange(e) {
            const input = $(e.target);
            const wordItem = input.closest('.word-item');
            const index = parseInt(wordItem.attr('data-index'));
            
            this.words[index].hint = input.val();
        }
        
        /**
         * フォームの送信
         */
        onFormSubmit(e) {
            e.preventDefault();
            
            // バリデーション
            if (!this.validateForm()) {
                return;
            }
            
            // パズルデータの構築
            const puzzleData = this.buildPuzzleData();
            
            // 送信
            this.submitPuzzle(puzzleData);
        }
        
        /**
         * フォームのバリデーション
         */
        validateForm() {
            const title = $('#puzzle-title').val().trim();
            if (!title) {
                this.showNotice('タイトルは必須です。', 'error');
                return false;
            }
            
            if (this.words.length === 0) {
                this.showNotice('少なくとも1つの単語が必要です。', 'error');
                return false;
            }
            
            // 単語の検証
            for (let i = 0; i < this.words.length; i++) {
                const word = this.words[i];
                if (!word.text.trim()) {
                    this.showNotice(`単語 ${i + 1} のテキストが入力されていません。`, 'error');
                    return false;
                }
                if (!word.hint.trim()) {
                    this.showNotice(`単語 ${i + 1} のヒントが入力されていません。`, 'error');
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * パズルデータの構築
         */
        buildPuzzleData() {
            // グリッドデータ
            const gridData = {
                size: this.gridSize,
                grid: this.grid
            };
            
            // 単語データ
            const wordsData = {};
            this.words.forEach((word, index) => {
                wordsData[word.text] = {
                    row: 0, // 実際の実装では位置を計算
                    col: 0,
                    direction: word.direction
                };
            });
            
            // ヒントデータ
            const hintsData = {};
            this.words.forEach(word => {
                hintsData[word.text] = word.hint;
            });
            
            return {
                title: $('#puzzle-title').val().trim(),
                description: $('#puzzle-description').val().trim(),
                difficulty: $('#puzzle-difficulty').val(),
                grid_data: JSON.stringify(gridData),
                words_data: JSON.stringify(wordsData),
                hints_data: JSON.stringify(hintsData)
            };
        }
        
        /**
         * パズルの送信
         */
        submitPuzzle(puzzleData) {
            // スプレッド演算子の代わりにObject.assignを使用
            const ajaxData = Object.assign({}, puzzleData, {
                action: 'crossword_save_puzzle',
                nonce: crossword_admin_ajax.nonce
            });
            
            $.ajax({
                url: crossword_admin_ajax.ajax_url,
                type: 'POST',
                data: ajaxData,
                beforeSend: () => {
                    this.showLoading();
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice(crossword_admin_ajax.strings.puzzle_saved, 'success');
                        setTimeout(() => {
                            window.location.href = 'admin.php?page=crossword-game';
                        }, 1500);
                    } else {
                        this.showNotice(response.data, 'error');
                    }
                },
                error: () => {
                    this.showNotice(crossword_admin_ajax.strings.error_occurred, 'error');
                },
                complete: () => {
                    this.hideLoading();
                }
            });
        }
        
        /**
         * パズルの編集
         */
        editPuzzle(e) {
            const puzzleId = $(e.target).data('id');
            
            $.ajax({
                url: crossword_admin_ajax.ajax_url,
                type: 'GET',
                data: {
                    action: 'crossword_get_puzzle_data',
                    puzzle_id: puzzleId,
                    nonce: crossword_admin_ajax.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.loadPuzzleForEdit(response.data);
                    } else {
                        this.showNotice(response.data, 'error');
                    }
                },
                error: () => {
                    this.showNotice(crossword_admin_ajax.strings.error_occurred, 'error');
                }
            });
        }
        
        /**
         * パズルの削除
         */
        deletePuzzle(e) {
            const puzzleId = $(e.target).data('id');
            
            if (confirm(crossword_admin_ajax.strings.confirm_delete)) {
                $.ajax({
                    url: crossword_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'crossword_delete_puzzle',
                        puzzle_id: puzzleId,
                        nonce: crossword_admin_ajax.nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            this.showNotice(crossword_admin_ajax.strings.puzzle_deleted, 'success');
                            $(e.target).closest('tr').fadeOut();
                        } else {
                            this.showNotice(response.data, 'error');
                        }
                    },
                    error: () => {
                        this.showNotice(crossword_admin_ajax.strings.error_occurred, 'error');
                    }
                });
            }
        }
        
        /**
         * 編集用パズルの読み込み
         */
        loadPuzzleForEdit(puzzle) {
            // フォームにデータを設定
            $('#puzzle-title').val(puzzle.title);
            $('#puzzle-description').val(puzzle.description);
            $('#puzzle-difficulty').val(puzzle.difficulty);
            
            // グリッドサイズを設定
            const gridData = JSON.parse(puzzle.grid_data);
            $('#puzzle-size').val(gridData.size).trigger('change');
            
            // グリッドデータを設定
            setTimeout(() => {
                this.loadGridData(gridData);
            }, 100);
            
            // 単語データを設定
            this.loadWordsData(puzzle);
        }
        
        /**
         * グリッドデータの読み込み
         */
        loadGridData(gridData) {
            for (let row = 0; row < gridData.size; row++) {
                for (let col = 0; col < gridData.size; col++) {
                    const cell = $(`.admin-grid-cell[data-row="${row}"][data-col="${col}"]`);
                    const input = cell.find('input');
                    input.val(gridData.grid[row][col]);
                    this.grid[row][col] = gridData.grid[row][col];
                }
            }
        }
        
        /**
         * 単語データの読み込み
         */
        loadWordsData(puzzle) {
            const wordsData = JSON.parse(puzzle.words_data);
            const hintsData = JSON.parse(puzzle.hints_data);
            
            // 既存の単語フィールドをクリア
            $('#words-container').empty();
            this.words = [];
            
            // 単語データを追加
            Object.keys(wordsData).forEach(word => {
                this.addWordField();
                const index = this.words.length - 1;
                const wordItem = $(`.word-item[data-index="${index}"]`);
                
                wordItem.find('.word-text').val(word);
                wordItem.find('.word-direction').val(wordsData[word].direction);
                wordItem.find('.word-hint').val(hintsData[word]);
                
                this.words[index] = {
                    text: word,
                    direction: wordsData[word].direction,
                    hint: hintsData[word],
                    row: wordsData[word].row,
                    col: wordsData[word].col
                };
            });
        }
        
        /**
         * 通知の表示
         */
        showNotice(message, type = 'info') {
            const notice = $(`<div class="crossword-notice ${type}">${message}</div>`);
            $('.wrap').prepend(notice);
            
            setTimeout(() => {
                notice.fadeOut(() => notice.remove());
            }, 5000);
        }
        
        /**
         * ローディングの表示
         */
        showLoading() {
            $('body').addClass('crossword-loading');
        }
        
        /**
         * ローディングの非表示
         */
        hideLoading() {
            $('body').removeClass('crossword-loading');
        }
        
        /**
         * パズルの自動生成
         */
        autoGeneratePuzzle() {
            try {
                const wordCount = parseInt($('#auto-word-count').val());
                const attempts = parseInt($('#auto-attempts').val());
                const difficulty = $('#puzzle-difficulty').val();
                const size = parseInt($('#puzzle-size').val());
                
                // バリデーション
                if (wordCount < 5 || wordCount > 20) {
                    this.showNotice('単語数は5-20の間で指定してください。', 'error');
                    return;
                }
                
                if (attempts < 1 || attempts > 10) {
                    this.showNotice('試行回数は1-10の間で指定してください。', 'error');
                    return;
                }
                
                // 生成中の状態を表示
                $('.crossword-auto-generator').addClass('generating');
                $('#auto-generate-btn').prop('disabled', true);
                this.showAutoGeneratorStatus('パズルを生成中...', 'info');
                
                // AJAXでパズルを生成
                $.ajax({
                    url: crossword_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'crossword_generate_puzzle',
                        difficulty: difficulty,
                        size: size,
                        word_count: wordCount,
                        attempts: attempts,
                        nonce: crossword_admin_ajax.nonce
                    },
                    success: (response) => {
                        try {
                            if (response.success) {
                                this.showAutoGeneratorStatus(response.data.message, 'success');
                                
                                // 生成されたパズルデータをフォームに設定
                                this.loadGeneratedPuzzle(response.data.data);
                                
                                // 3秒後に成功メッセージを消す
                                setTimeout(() => {
                                    this.hideAutoGeneratorStatus();
                                }, 3000);
                            } else {
                                this.showAutoGeneratorStatus(response.data, 'error');
                            }
                        } catch (error) {
                            console.error('Auto generate success handler error:', error);
                            this.showAutoGeneratorStatus('レスポンス処理中にエラーが発生しました。', 'error');
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('AJAX error:', {xhr, status, error});
                        this.showAutoGeneratorStatus(crossword_admin_ajax.strings.error_occurred, 'error');
                    },
                    complete: () => {
                        $('.crossword-auto-generator').removeClass('generating');
                        $('#auto-generate-btn').prop('disabled', false);
                    }
                });
            } catch (error) {
                console.error('Auto generate puzzle error:', error);
                this.showAutoGeneratorStatus('パズル生成中にエラーが発生しました。', 'error');
                $('.crossword-auto-generator').removeClass('generating');
                $('#auto-generate-btn').prop('disabled', false);
            }
        }
        
        /**
         * 生成されたパズルをフォームに読み込み
         */
        loadGeneratedPuzzle(puzzleData) {
            try {
                // グリッドデータを設定
                const gridData = JSON.parse(puzzleData.grid_data);
                $('#puzzle-size').val(gridData.size).trigger('change');
                
                setTimeout(() => {
                    this.loadGridData(gridData);
                }, 100);
                
                // 単語データを設定
                this.loadWordsDataFromGenerated(puzzleData);
                
                this.showNotice('パズルが自動生成されました。必要に応じて調整してください。', 'success');
                
            } catch (error) {
                console.error('パズルデータの読み込みに失敗:', error);
                this.showNotice('生成されたパズルの読み込みに失敗しました。', 'error');
            }
        }
        
        /**
         * 生成された単語データを読み込み
         */
        loadWordsDataFromGenerated(puzzleData) {
            const wordsData = JSON.parse(puzzleData.words_data);
            const hintsData = JSON.parse(puzzleData.hints_data);
            
            // 既存の単語フィールドをクリア
            $('#words-container').empty();
            this.words = [];
            
            // 単語データを追加
            Object.keys(wordsData).forEach(word => {
                this.addWordField();
                const index = this.words.length - 1;
                const wordItem = $(`.word-item[data-index="${index}"]`);
                
                wordItem.find('.word-text').val(word);
                wordItem.find('.word-direction').val(wordsData[word].direction);
                wordItem.find('.word-hint').val(hintsData[word]);
                
                this.words[index] = {
                    text: word,
                    direction: wordsData[word].direction,
                    hint: hintsData[word],
                    row: wordsData[word].row,
                    col: wordsData[word].col
                };
            });
        }
        
        /**
         * 自動生成のステータスを表示
         */
        showAutoGeneratorStatus(message, type = 'info') {
            const statusDiv = $('#auto-generator-status');
            statusDiv.removeClass('success error info').addClass(type);
            statusDiv.text(message).show();
        }
        
        /**
         * 自動生成のステータスを非表示
         */
        hideAutoGeneratorStatus() {
            $('#auto-generator-status').hide();
        }
    }
    
    // DOM読み込み完了後に初期化
    $(document).ready(function() {
        try {
            if ($('#crossword-puzzle-form').length > 0) {
                new CrosswordAdmin();
            }
        } catch (error) {
            console.error('CrosswordAdmin initialization error:', error);
        }
    });
    
    // グローバルエラーハンドラー
    window.addEventListener('error', function(event) {
        console.error('Global error in crossword admin:', {
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            error: event.error
        });
    });
    
    // 未処理のPromise拒否をキャッチ
    window.addEventListener('unhandledrejection', function(event) {
        console.error('Unhandled promise rejection in crossword admin:', event.reason);
    });
    
})(jQuery);
