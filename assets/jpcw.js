/**
 * ===== フロントエンド用 JS =====
 * ファイル: assets/jpcw.js
 * WordPressプラグイン用に最適化
 */
(function($){
	'use strict';

	/**
	 * ボードの描画
	 */
	function renderBoard($wrap, data){
		const size = data.size;
		const grid = data.grid; // 文字 or '#'
		const $board = $wrap.find('.jpcw-board').empty();
		const $table = $('<table class="jpcw-grid" role="grid" aria-label="クロスワードパズル" />');
		
		for(let r=0; r<size; r++){
			const $tr = $('<tr role="row" />');
			for(let c=0; c<size; c++){
				const val = grid[r][c];
				const $td = $('<td class="jpcw-cell" role="gridcell" />');
				
				if(val === '#' || val === null){
					$td.addClass('black').attr('aria-label', '黒マス');
				} else {
					const $inp = $('<input type="text" inputmode="text" maxlength="2" autocomplete="off" aria-label="マス目 ' + (r+1) + '-' + (c+1) + '" />');
					$inp.attr('data-solution', val);
					$inp.attr('data-row', r);
					$inp.attr('data-col', c);
					
					// 日本語IME対策：compositionend で1文字確定
					let composing = false;
					$inp.on('compositionstart', () => composing = true);
					$inp.on('compositionend', (e) => { 
						composing = false; 
						handleInput(e.currentTarget); 
					});
					$inp.on('input', (e) => { 
						if(!composing) handleInput(e.currentTarget); 
					});
					
					// キーボードナビゲーション
					$inp.on('keydown', handleKeydown);
					
					$td.append($inp);
				}
				$tr.append($td);
			}
			$table.append($tr);
		}
		
		$board.append($table).append('<div class="jpcw-status" role="status" aria-live="polite"></div>');
		
		// 最初の入力フィールドにフォーカス
		$board.find('input:first').focus();
	}

	/**
	 * 入力処理
	 */
	function handleInput(el){
		const $el = $(el);
		let v = $el.val();
		
		// 先頭の1文字だけ残す（サロゲート/結合文字を考慮して最大2バイトまで許容）
		if(v.length > 1){
			// 2文字以上入力された場合は先頭の1文字に切り詰め
			v = Array.from(v)[0] || '';
			$el.val(v);
		}
		
		const ans = $el.data('solution');
		$el.toggleClass('correct', v === ans);
		
		// 正解の場合、次の入力フィールドにフォーカス
		if(v === ans && v !== ''){
			setTimeout(() => {
				const $next = $el.closest('td').next().find('input');
				if($next.length > 0){
					$next.focus();
				}
			}, 100);
		}
	}

	/**
	 * キーボードナビゲーション
	 */
	function handleKeydown(e){
		const $el = $(e.currentTarget);
		const $td = $el.closest('td');
		const $tr = $td.closest('tr');
		const $table = $tr.closest('table');
		
		let $target = null;
		
		switch(e.key){
			case 'ArrowUp':
				e.preventDefault();
				$target = $tr.prev().find('td').eq($td.index()).find('input');
				break;
			case 'ArrowDown':
				e.preventDefault();
				$target = $tr.next().find('td').eq($td.index()).find('input');
				break;
			case 'ArrowLeft':
				e.preventDefault();
				$target = $td.prev().find('input');
				break;
			case 'ArrowRight':
				e.preventDefault();
				$target = $td.next().find('input');
				break;
			case 'Tab':
				// Tabキーはデフォルトの動作を許可
				return;
		}
		
		if($target && $target.length > 0){
			$target.focus();
		}
	}

	/**
	 * 新規問題の要求
	 */
	function requestNew($wrap){
		const size = parseInt($wrap.data('size'), 10) || 10;
		const $newBtn = $wrap.find('.jpcw-new');
		const $giveupBtn = $wrap.find('.jpcw-giveup');
		
		// ボタンを無効化
		$newBtn.prop('disabled', true).text(JPCW.i18n.loading);
		$giveupBtn.prop('disabled', true);
		
		status($wrap, JPCW.i18n.loading);
		
		$.post(JPCW.ajax, { 
			action: 'jpcw_generate', 
			nonce: JPCW.nonce, 
			size: size 
		})
		.done(function(resp){
			if(resp && resp.success){
				renderBoard($wrap, resp.data);
				status($wrap, '');
			} else {
				status($wrap, resp.data?.message || JPCW.i18n.error);
			}
		})
		.fail(function(xhr, status, error){
			console.error('AJAX Error:', error);
			status($wrap, JPCW.i18n.error);
		})
		.always(function(){
			// ボタンを再有効化
			$newBtn.prop('disabled', false).text(JPCW.i18n.new);
			$giveupBtn.prop('disabled', false);
		});
	}

	/**
	 * 全解答の表示
	 */
	function revealAll($wrap){
		$wrap.find('input[data-solution]').each(function(){
			const $input = $(this);
			const ans = $input.data('solution');
			$input.val(ans).addClass('revealed');
		});
		
		status($wrap, '全ての答えを表示しました');
	}

	/**
	 * ステータス表示
	 */
	function status($wrap, text){ 
		$wrap.find('.jpcw-status').text(text); 
	}

	/**
	 * イベントハンドラーの設定
	 */
	$(document).on('click', '.jpcw-new', function(e){
		e.preventDefault();
		const $wrap = $(this).closest('.jpcw-wrapper');
		requestNew($wrap);
	});
	
	$(document).on('click', '.jpcw-giveup', function(e){
		e.preventDefault();
		const $wrap = $(this).closest('.jpcw-wrapper');
		revealAll($wrap);
	});

	/**
	 * 初期化：ショートコード描画箇所で自動生成
	 */
	$(function(){
		$('.jpcw-wrapper').each(function(){
			const $wrap = $(this);
			// 少し遅延させてDOMの準備を確実にする
			setTimeout(() => requestNew($wrap), 100);
		});
	});

})(jQuery);
