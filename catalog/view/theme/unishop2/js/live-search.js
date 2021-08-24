uniLiveSearch = {
	init:function() {
		var base = this; 
		
		base.inputs = '.header-search__input';
		base.minlength = 2;
		base.timer;
		base.delay = 500;
		
		$(document).on('click', base.inputs, function() {
			base.click(this);
		});
		
		$(document).on('keyup', base.inputs, function() {
			base.keyUp(this);
		});
		
		$('header, main, footer').on('click', function() {
			$('.live-search').hide();
		});
		
		$(base.inputs).attr('autocomplete', 'off');
	},
	click:function(el) {
		var base = this, $elem = $(el).parent().parent();
		
		if ($elem.find('.live-search ul li').length > 1) {
			$elem.find('.live-search').show();
		}
	},
	keyUp:function(el) {
		var base = this, $this = $(el), $elem = $this.parent().parent(), search_phrase = $this.val().trim();
		
		if (search_phrase.length >= base.minlength) {
			
			$elem.find('.live-search ul').html('<li class="live-search__loading"></li>');
			$elem.find('.live-search').show();
		
			clearTimeout(base.timer);
			
			base.timer = setTimeout(function(){
				$.ajax({
					url:'index.php?route=extension/module/uni_live_search',
					type:'post',
					data:{'filter_name': $this.val(), 'category_id': $elem.find('input[name=\'filter_category_id\']').val(), 'flag': true},
					dataType:'html',
					success: function(html) {
						$('.live-search ul').html(html);
						
						$('.live-search').highlight(search_phrase);
					}
				});
			}, base.delay);
		} else {
			$elem.find('.live-search').hide();
		}
	}
}

$(function() {
	uniLiveSearch.init();
});