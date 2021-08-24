$(function() {
	if(uniJsVars.showmore && $('.product-layout').length) {
		let btnHtml = '<div class="show-more" style="margin:10px 0 25px;text-align:center"><button type="button" class="show-more__btn btn btn-xl btn-default"><i class="show-more__icon fa fa-sync-alt"></i><span>'+uniJsVars.showmore_text+'</span></button></div>';
		
		if($('.pagination').find('.active').next().length){
			$('.pagination').before(btnHtml);
		}
		
		let observer = new MutationObserver(function(e) {
			if(!$('.show-more').length) $('.pagination').before(btnHtml);
			
			if($('.pagination').find('.active').next().length) {
				$('.show-more').show();
			} else {
				$('.show-more').hide();
			}
		});
		
		if($('.pagination-text').length) {
			observer.observe($('.pagination-text')[0], {childList:true, subtree:true});
		}
		
		$(document).on('click', '.show-more__btn', function() {
			let pagination = $('.pagination'),
				pagination_text = $('.pagination-text'),
				products = $('.products-block'),
				showmoreIcon = $(this).find('.show-more__icon'),
				url = pagination.find('.active').next().find('a').attr('href');
			
			if(typeof(url) == 'undefined' || url == '') return;
			
			if (document.location.protocol == 'https:') url = url.replace('http:', 'https:');
	
			$.ajax({
				url: url,
				type: 'get',
				dataType: 'html',
				beforeSend: function() {
					showmoreIcon.addClass('spin');
				},
				success: function(data) {
					let result = $(data);
						
					result.find('.product-thumb').hide();
					
					products.append(result.find('.products-block').html()).find('.product-thumb').fadeIn('slow');
					pagination.html(result.find('.pagination').html());
					
					let textString = result.find('.pagination-text').text();
					
					//if(document.location.search.indexOf('page') == -1) {
					//	let textArr = result.find('.pagination-text').text().split(' ');
					//	textArr[2] = 1;
					//	textString = textArr.join(' ');
					//}
					
					pagination_text.text(textString);
					
					showmoreIcon.removeClass('spin');
					uniSelectView.init();
					window.history.pushState('', '', url);
				},
			});
		});
	}

	if(uniJsVars.ajax_pagination && $('.products-block').length) {
		$(document).on('click', '.pagination a', function(e) {
		
			e.preventDefault();
			
			let pagination = $('.pagination'),
				pagination_text = $('.pagination-text'),
				products = $('.products-block'),
				url = $(this).attr('href');
			
			if (document.location.protocol == 'https:') url = url.replace('http:', 'https:');
	
			$.ajax({
				url: url,
				type: 'get',
				dataType: 'html',
				beforeSend: function() {
					$('html body').append('<div class="full-width-loading"></div>');
				},
				complete: function() {
					uniSelectView.init();
					uniScrollTo('.products-block');
				},
				success: function(data) {
					products.html($(data).find('.products-block').html());
					pagination.html($(data).find('.pagination').html());
					pagination_text.text($(data).find('.pagination-text').text());
				
					$('.full-width-loading').remove();
				
					window.history.pushState('', '', url);
				}
			});
		});
	}
});