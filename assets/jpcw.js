/**
 * ===== フロントエンド用 JS =====
 * ファイル: assets/jpcw.js
 * WordPressプラグイン用に最適化されたクロスワードゲーム
 */
(function($){
	'use strict';

	/**
	 * 日本語の単語とヒントのリスト
	 */
	const crossword_data = {
		words: [
			{ word: 'サーバー', clue: 'データを保管したりサービスを提供したりするコンピュータ。' },
			{ word: 'ドメイン', clue: 'インターネット上の住所のこと。' },
			{ word: 'ブラウザ', clue: 'ウェブサイトを見るためのソフト。' },
			{ word: 'コーディング', clue: 'コンピュータにわかる言葉でプログラムを書くこと。' },
			{ word: 'デバッグ', clue: 'プログラムの間違いを見つけて直すこと。' },
			{ word: 'プラグイン', clue: 'ソフトウェアに機能を追加する小さなプログラム。' },
			{ word: 'キャッシュ', clue: '一度見たページの情報を一時的に保存しておく仕組み。' },
			{ word: 'クッキー', clue: 'サイトがユーザー情報を一時的に保存する小さなファイル。' },
			{ word: 'ファイアウォール', clue: 'ネットワークの安全を守るための「防火壁」。' },
			{ word: 'レスポンシブ', clue: 'スマホなど、画面サイズに合わせて表示が変わるデザイン。' }
		]
	};

	/**
	 * クロスワードゲームのメインオブジェクト
	 */
	let crosswordGame = {
		wordsData: [],
		gridSize: 12,
		grid: [],
		solution: [],
		placedWords: [],
		isGameActive: false
	};

	/**
	 * 初期化
	 */
	function init() {
		crosswordGame.wordsData = crossword_data.words || [];
		if (crosswordGame.wordsData.length === 0) return;
		
		setupEventListeners();
		newGame();
	}

	/**
	 * イベントリスナーの設定
	 */
	function setupEventListeners() {
		$(document).on('click', '.jpcw-new', newGame);
		$(document).on('click', '.jpcw-giveup', solvePuzzle);
		$(document).on('keydown', '.crossword-input', handleKeyboardNavigation);
		$(document).on('input', '.crossword-input', function() {
			checkCompletion();
		});
	}

	/**
	 * クロスワードパズルの生成
	 */
	function generatePuzzle() {
		crosswordGame.grid = Array(crosswordGame.gridSize).fill(null).map(() => Array(crosswordGame.gridSize).fill(''));
		crosswordGame.solution = Array(crosswordGame.gridSize).fill(null).map(() => Array(crosswordGame.gridSize).fill(''));
		crosswordGame.placedWords = [];

		const shuffledWords = [...crosswordGame.wordsData].sort(() => 0.5 - Math.random());
		
		// 最初の単語を配置
		const firstWordData = shuffledWords.pop();
		const firstWord = firstWordData.word;
		const orientation = Math.random() < 0.5 ? 'across' : 'down';
		const row = Math.floor(crosswordGame.gridSize / 2);
		const col = Math.floor(crosswordGame.gridSize / 2) - Math.floor(firstWord.length / 2);
		placeWord(firstWordData, row, col, orientation);

		let attempts = 0;
		while(shuffledWords.length > 0 && attempts < 200) {
			const wordData = shuffledWords[0];
			const word = wordData.word;
			let placed = false;
			
			for (let i = 0; i < 100; i++) {
				const intersection = findIntersection(word);
				if (intersection) {
					placeWord(wordData, intersection.row, intersection.col, intersection.orientation);
					shuffledWords.shift();
					placed = true;
					break;
				}
			}
			if (!placed) {
				shuffledWords.shift();
			}
			attempts++;
		}
	}

	/**
	 * 単語の交差位置を探す
	 */
	function findIntersection(word) {
		for (let i = 0; i < word.length; i++) {
			const char = word[i];
			for (const placed of crosswordGame.placedWords) {
				for (let j = 0; j < placed.word.length; j++) {
					if (placed.word[j] === char) {
						const orientation = placed.orientation === 'across' ? 'down' : 'across';
						const row = orientation === 'down' ? placed.row - i : placed.row + j;
						const col = orientation === 'down' ? placed.col + j : placed.col - i;
						
						if(canPlaceWord(word, row, col, orientation)) {
							return { row, col, orientation };
						}
					}
				}
			}
		}
		return null;
	}

	/**
	 * 単語を配置できるかチェック
	 */
	function canPlaceWord(word, row, col, orientation) {
		if (row < 0 || col < 0 || row >= crosswordGame.gridSize || col >= crosswordGame.gridSize) {
			return false;
		}

		if (orientation === 'across') {
			if (col + word.length > crosswordGame.gridSize) return false;
			for (let i = 0; i < word.length; i++) {
				const existingChar = crosswordGame.solution[row][col + i];
				if (existingChar && existingChar !== word[i]) {
					return false;
				}
			}
		} else {
			if (row + word.length > crosswordGame.gridSize) return false;
			for (let i = 0; i < word.length; i++) {
				const existingChar = crosswordGame.solution[row + i][col];
				if (existingChar && existingChar !== word[i]) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 単語を配置
	 */
	function placeWord(wordData, row, col, orientation) {
		const word = wordData.word;
		for (let i = 0; i < word.length; i++) {
			if (orientation === 'across') {
				crosswordGame.grid[row][col + i] = 'INPUT';
				crosswordGame.solution[row][col + i] = word[i];
			} else {
				crosswordGame.grid[row + i][col] = 'INPUT';
				crosswordGame.solution[row + i][col] = word[i];
			}
		}
		crosswordGame.placedWords.push({ ...wordData, row, col, orientation });
	}

	/**
	 * グリッドの描画
	 */
	function renderGrid() {
		const gridContainer = $('.jpcw-board');
		gridContainer.empty();
		
		gridContainer.css({
			'grid-template-columns': `repeat(${crosswordGame.gridSize}, 40px)`,
			'grid-template-rows': `repeat(${crosswordGame.gridSize}, 40px)`
		});

		crosswordGame.placedWords.sort((a, b) => a.row - b.row || a.col - b.col);
		let wordNumber = 1;
		const numberedPositions = new Map();

		for (const word of crosswordGame.placedWords) {
			const posKey = `${word.row},${word.col}`;
			if (!numberedPositions.has(posKey)) {
				numberedPositions.set(posKey, wordNumber);
				word.number = wordNumber;
				wordNumber++;
			} else {
				word.number = numberedPositions.get(posKey);
			}
		}

		for (let i = 0; i < crosswordGame.gridSize; i++) {
			for (let j = 0; j < crosswordGame.gridSize; j++) {
				const cell = $('<div>').addClass('jpcw-cell');
				
				if (crosswordGame.grid[i][j] === 'INPUT') {
					const input = $('<input>').attr({
						'type': 'text', 'maxlength': '1', 'data-row': i, 'data-col': j,
						'autocomplete': 'off', 'spellcheck': 'false'
					}).addClass('crossword-input');
					cell.append(input);

					const posKey = `${i},${j}`;
					if (numberedPositions.has(posKey)) {
						cell.append($('<span>').addClass('cell-number').text(numberedPositions.get(posKey)));
					}
				} else {
					cell.addClass('empty-cell');
				}
				gridContainer.append(cell);
			}
		}
		displayClues();
	}

	/**
	 * ヒントの表示
	 */
	function displayClues() {
		const acrossList = $('.jpcw-clues-across').empty();
		const downList = $('.jpcw-clues-down').empty();

		const uniqueWords = [...new Map(crosswordGame.placedWords.map(item => [item.number, item])).values()];
		uniqueWords.sort((a,b) => a.number - b.number);
		
		for (const word of uniqueWords) {
			const listItem = $('<li>').html(`<b>${word.number}.</b> ${word.clue}`);
			if (word.orientation === 'across') {
				acrossList.append(listItem);
			} else {
				downList.append(listItem);
			}
		}
	}

	/**
	 * キーボードナビゲーション
	 */
	function handleKeyboardNavigation(e) {
		const $current = $(e.target);
		const row = parseInt($current.data('row'));
		const col = parseInt($current.data('col'));
		let $next;
		
		switch(e.key) {
			case "ArrowLeft": $next = $(`.crossword-input[data-row="${row}"][data-col="${col - 1}"]`); break;
			case "ArrowUp": $next = $(`.crossword-input[data-row="${row - 1}"][data-col="${col}"]`); break;
			case "ArrowRight": $next = $(`.crossword-input[data-row="${row}"][data-col="${col + 1}"]`); break;
			case "ArrowDown": $next = $(`.crossword-input[data-row="${row + 1}"][data-col="${col}"]`); break;
			default: return;
		}
		
		if ($next && $next.length > 0) {
			$next.focus();
			e.preventDefault();
		}
	}

	/**
	 * 完了チェック
	 */
	function checkCompletion() {
		let isCorrect = true;
		let filledCells = 0, totalCells = 0;
		
		$('.crossword-input').each(function() {
			const row = $(this).data('row');
			const col = $(this).data('col');
			totalCells++;
			
			if (this.value !== '') {
				filledCells++;
				if (this.value !== crosswordGame.solution[row][col]) {
					isCorrect = false;
				}
			} else {
				isCorrect = false;
			}
		});

		if (isCorrect && filledCells === totalCells && totalCells > 0) {
			showMessage('おめでとうございます！正解です！', 'success');
			crosswordGame.isGameActive = false;
			$('.crossword-input').prop('disabled', true);
		}
	}

	/**
	 * メッセージ表示
	 */
	function showMessage(message, type) {
		$('.jpcw-status').text(message).removeClass('success info error').addClass(type);
	}

	/**
	 * パズルを解く
	 */
	function solvePuzzle() {
		if (!crosswordGame.isGameActive && $('.crossword-input:disabled').length > 0) return;
		$('.crossword-input').each(function() {
			const row = $(this).data('row');
			const col = $(this).data('col');
			$(this).val(crosswordGame.solution[row][col]).prop('disabled', true);
		});
		showMessage('正解が表示されました。', 'info');
		crosswordGame.isGameActive = false;
	}

	/**
	 * 新規ゲーム
	 */
	function newGame() {
		crosswordGame.isGameActive = true;
		generatePuzzle();
		renderGrid();
		showMessage('新しい問題が生成されました。', 'info');
	}

	/**
	 * 初期化：ショートコード描画箇所で自動生成
	 */
	$(function(){
		$('.jpcw-wrapper').each(function(){
			const $wrap = $(this);
			// 少し遅延させてDOMの準備を確実にする
			setTimeout(() => init(), 100);
		});
	});

})(jQuery);
