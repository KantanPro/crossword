<div class="crossword-game-container">
    <div class="crossword-header">
        <h2>クロスワードゲーム</h2>
        <div class="crossword-controls">
            <button id="new-puzzle-btn" class="btn btn-primary">新規問題</button>
            <button id="give-up-btn" class="btn btn-warning">ギブアップ</button>
            <button id="check-answer-btn" class="btn btn-success">回答チェック</button>
        </div>
    </div>
    
    <div class="crossword-content">
        <div class="crossword-grid-container">
            <div id="crossword-grid" class="crossword-grid" data-width="<?php echo $crossword_data['width']; ?>" data-height="<?php echo $crossword_data['height']; ?>">
                <?php for ($y = 0; $y < $crossword_data['height']; $y++): ?>
                    <div class="crossword-row">
                        <?php for ($x = 0; $x < $crossword_data['width']; $x++): ?>
                            <?php 
                            $cell_value = $crossword_data['grid'][$y][$x];
                            $cell_class = $cell_value !== '' ? 'filled' : 'empty';
                            $cell_id = "cell-{$x}-{$y}";
                            ?>
                            <div class="crossword-cell <?php echo $cell_class; ?>" id="<?php echo $cell_id; ?>" data-x="<?php echo $x; ?>" data-y="<?php echo $y; ?>">
                                <?php if ($cell_value !== ''): ?>
                                    <span class="cell-letter"><?php echo $cell_value; ?></span>
                                <?php else: ?>
                                    <input type="text" class="cell-input" maxlength="1" data-x="<?php echo $x; ?>" data-y="<?php echo $y; ?>">
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="crossword-clues">
            <h3>ヒント</h3>
            <div class="clues-container">
                <div class="across-clues">
                    <h4>横の言葉</h4>
                    <ul>
                        <?php foreach ($crossword_data['clues'] as $index => $clue): ?>
                            <?php if ($clue['direction'] == 0): ?>
                                <li class="clue-item" data-word="<?php echo $clue['word']; ?>" data-start-x="<?php echo $clue['start_x']; ?>" data-start-y="<?php echo $clue['start_y']; ?>" data-direction="0">
                                    <span class="clue-number"><?php echo $index + 1; ?>.</span>
                                    <span class="clue-text"><?php echo $clue['clue']; ?></span>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="down-clues">
                    <h4>縦の言葉</h4>
                    <ul>
                        <?php foreach ($crossword_data['clues'] as $index => $clue): ?>
                            <?php if ($clue['direction'] == 1): ?>
                                <li class="clue-item" data-word="<?php echo $clue['word']; ?>" data-start-x="<?php echo $clue['start_x']; ?>" data-start-y="<?php echo $clue['start_y']; ?>" data-direction="1">
                                    <span class="clue-number"><?php echo $index + 1; ?>.</span>
                                    <span class="clue-text"><?php echo $clue['clue']; ?></span>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="crossword-status">
        <div id="status-message" class="status-message"></div>
        <div class="progress-info">
            <span>進捗: </span>
            <span id="progress-text">0 / <?php echo count($crossword_data['clues']); ?></span>
        </div>
    </div>
</div>

<script type="text/javascript">
// 初期データをJavaScriptに渡す
var crosswordData = <?php echo json_encode($crossword_data); ?>;
</script>
