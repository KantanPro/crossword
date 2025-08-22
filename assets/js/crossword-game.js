jQuery(document).ready(function($) {
    var crosswordGame = {
        currentData: null,
        userAnswers: {},
        
        init: function() {
            this.bindEvents();
            this.currentData = window.crosswordData || null;
            if (this.currentData) {
                this.updateProgress();
            }
        },
        
        bindEvents: function() {
            // 新規問題ボタン
            $('#new-puzzle-btn').on('click', function() {
                crosswordGame.generateNewPuzzle();
            });
            
            // ギブアップボタン
            $('#give-up-btn').on('click', function() {
                crosswordGame.giveUp();
            });
            
            // 回答チェックボタン
            $('#check-answer-btn').on('click', function() {
                crosswordGame.checkAllAnswers();
            });
            
            // セル入力の処理
            $(document).on('input', '.cell-input', function() {
                crosswordGame.handleCellInput($(this));
            });
            
            // セル入力のキーボードナビゲーション
            $(document).on('keydown', '.cell-input', function(e) {
                crosswordGame.handleKeyNavigation($(this), e);
            });
            
            // ヒントクリック時のハイライト
            $(document).on('click', '.clue-item', function() {
                crosswordGame.highlightClue($(this));
            });
        },
        
        generateNewPuzzle: function() {
            $.ajax({
                url: crossword_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'generate_crossword',
                    nonce: crossword_ajax.nonce,
                    size: '10x10',
                    difficulty: 'medium'
                },
                success: function(response) {
                    if (response.success) {
                        crosswordGame.currentData = response.data;
                        crosswordGame.renderNewPuzzle();
                        crosswordGame.showStatus('新しい問題が生成されました！', 'success');
                    } else {
                        crosswordGame.showStatus('問題の生成に失敗しました。', 'error');
                    }
                },
                error: function() {
                    crosswordGame.showStatus('エラーが発生しました。', 'error');
                }
            });
        },
        
        renderNewPuzzle: function() {
            if (!this.currentData) return;
            
            // グリッドをクリア
            $('.cell-input').val('');
            $('.crossword-grid').empty();
            
            // 新しいグリッドを生成
            var gridHtml = '';
            for (var y = 0; y < this.currentData.height; y++) {
                gridHtml += '<div class="crossword-row">';
                for (var x = 0; x < this.currentData.width; x++) {
                    var cellValue = this.currentData.grid[y][x];
                    var cellClass = cellValue !== '' ? 'filled' : 'empty';
                    var cellId = 'cell-' + x + '-' + y;
                    
                    if (cellValue !== '') {
                        gridHtml += '<div class="crossword-cell ' + cellClass + '" id="' + cellId + '" data-x="' + x + '" data-y="' + y + '">';
                        gridHtml += '<span class="cell-letter">' + cellValue + '</span>';
                        gridHtml += '</div>';
                    } else {
                        gridHtml += '<div class="crossword-cell ' + cellClass + '" id="' + cellId + '" data-x="' + x + '" data-y="' + y + '">';
                        gridHtml += '<input type="text" class="cell-input" maxlength="1" data-x="' + x + '" data-y="' + y + '">';
                        gridHtml += '</div>';
                    }
                }
                gridHtml += '</div>';
            }
            
            $('.crossword-grid').html(gridHtml);
            
            // ヒントを更新
            this.updateClues();
            this.updateProgress();
            this.userAnswers = {};
        },
        
        updateClues: function() {
            if (!this.currentData) return;
            
            // 横のヒント
            var acrossHtml = '<h4>横の言葉</h4><ul>';
            var downHtml = '<h4>縦の言葉</h4><ul>';
            
            this.currentData.clues.forEach(function(clue, index) {
                var clueHtml = '<li class="clue-item" data-word="' + clue.word + '" data-start-x="' + clue.start_x + '" data-start-y="' + clue.start_y + '" data-direction="' + clue.direction + '">';
                clueHtml += '<span class="clue-number">' + (index + 1) + '.</span>';
                clueHtml += '<span class="clue-text">' + clue.clue + '</span>';
                clueHtml += '</li>';
                
                if (clue.direction == 0) {
                    acrossHtml += clueHtml;
                } else {
                    downHtml += clueHtml;
                }
            });
            
            acrossHtml += '</ul>';
            downHtml += '</ul>';
            
            $('.across-clues').html(acrossHtml);
            $('.down-clues').html(downHtml);
        },
        
        handleCellInput: function($input) {
            var value = $input.val();
            var x = parseInt($input.data('x'));
            var y = parseInt($input.data('y'));
            
            // ユーザーの回答を保存
            this.userAnswers[x + ',' + y] = value;
            
            // 進捗を更新
            this.updateProgress();
            
            // 自動的に次のセルに移動
            if (value.length === 1) {
                this.moveToNextCell($input);
            }
        },
        
        handleKeyNavigation: function($input, e) {
            var x = parseInt($input.data('x'));
            var y = parseInt($input.data('y'));
            var nextX = x, nextY = y;
            
            switch(e.keyCode) {
                case 37: // 左
                    nextX = x - 1;
                    break;
                case 38: // 上
                    nextY = y - 1;
                    break;
                case 39: // 右
                    nextX = x + 1;
                    break;
                case 40: // 下
                    nextY = y + 1;
                    break;
                case 8: // バックスペース
                    if ($input.val() === '') {
                        // 前のセルに移動
                        this.moveToPreviousCell($input);
                    }
                    return;
            }
            
            if (nextX >= 0 && nextX < this.currentData.width && 
                nextY >= 0 && nextY < this.currentData.height) {
                var nextCell = $('#cell-' + nextX + '-' + nextY + ' .cell-input');
                if (nextCell.length) {
                    nextCell.focus();
                }
            }
        },
        
        moveToNextCell: function($currentInput) {
            var x = parseInt($currentInput.data('x'));
            var y = parseInt($currentInput.data('y'));
            
            // 右のセルに移動
            var nextCell = $('#cell-' + (x + 1) + '-' + y + ' .cell-input');
            if (nextCell.length) {
                nextCell.focus();
            }
        },
        
        moveToPreviousCell: function($currentInput) {
            var x = parseInt($currentInput.data('x'));
            var y = parseInt($currentInput.data('y'));
            
            // 左のセルに移動
            var prevCell = $('#cell-' + (x - 1) + '-' + y + ' .cell-input');
            if (prevCell.length) {
                prevCell.focus();
            }
        },
        
        highlightClue: function($clueItem) {
            var startX = parseInt($clueItem.data('start-x'));
            var startY = parseInt($clueItem.data('start-y'));
            var direction = parseInt($clueItem.data('direction'));
            var word = $clueItem.data('word');
            
            // 既存のハイライトをクリア
            $('.crossword-cell').removeClass('highlighted');
            
            // 新しいハイライトを適用
            for (var i = 0; i < word.length; i++) {
                var x = startX + (direction === 0 ? i : 0);
                var y = startY + (direction === 1 ? i : 0);
                $('#cell-' + x + '-' + y).addClass('highlighted');
            }
        },
        
        checkAllAnswers: function() {
            if (!this.currentData) return;
            
            var correctCount = 0;
            var totalWords = this.currentData.clues.length;
            
            this.currentData.clues.forEach(function(clue) {
                var word = clue.word;
                var isCorrect = true;
                
                for (var i = 0; i < word.length; i++) {
                    var x = clue.start_x + (clue.direction === 0 ? i : 0);
                    var y = clue.start_y + (clue.direction === 1 ? i : 0);
                    var userAnswer = crosswordGame.userAnswers[x + ',' + y] || '';
                    var correctLetter = word.charAt(i);
                    
                    if (userAnswer !== correctLetter) {
                        isCorrect = false;
                        break;
                    }
                }
                
                if (isCorrect) {
                    correctCount++;
                }
            });
            
            if (correctCount === totalWords) {
                this.showStatus('おめでとうございます！すべて正解です！', 'success');
            } else {
                this.showStatus('正解数: ' + correctCount + ' / ' + totalWords, 'info');
            }
        },
        
        giveUp: function() {
            if (!this.currentData) return;
            
            // すべての空マスに正解を表示
            this.currentData.clues.forEach(function(clue) {
                var word = clue.word;
                
                for (var i = 0; i < word.length; i++) {
                    var x = clue.start_x + (clue.direction === 0 ? i : 0);
                    var y = clue.start_y + (clue.direction === 1 ? i : 0);
                    var cell = $('#cell-' + x + '-' + y);
                    var input = cell.find('.cell-input');
                    
                    if (input.length) {
                        var correctLetter = word.charAt(i);
                        input.val(correctLetter);
                        input.prop('disabled', true);
                        input.addClass('revealed');
                    }
                }
            });
            
            this.showStatus('ギブアップしました。正解が表示されています。', 'warning');
        },
        
        updateProgress: function() {
            if (!this.currentData) return;
            
            var filledCells = 0;
            var totalEmptyCells = 0;
            
            this.currentData.clues.forEach(function(clue) {
                var word = clue.word;
                
                for (var i = 0; i < word.length; i++) {
                    var x = clue.start_x + (clue.direction === 0 ? i : 0);
                    var y = clue.start_y + (clue.direction === 1 ? i : 0);
                    var cell = $('#cell-' + x + '-' + y);
                    var input = cell.find('.cell-input');
                    
                    if (input.length) {
                        totalEmptyCells++;
                        if (input.val() && input.val().length > 0) {
                            filledCells++;
                        }
                    }
                }
            });
            
            $('#progress-text').text(filledCells + ' / ' + totalEmptyCells);
        },
        
        showStatus: function(message, type) {
            var $status = $('#status-message');
            $status.removeClass().addClass('status-message status-' + type);
            $status.text(message);
            
            setTimeout(function() {
                $status.fadeOut();
            }, 3000);
        }
    };
    
    // ゲームを初期化
    crosswordGame.init();
});
