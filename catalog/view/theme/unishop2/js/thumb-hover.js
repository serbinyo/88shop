$(function() {
	$('body').on('mouseenter', '.product-thumb', function() {
		
		if($(window).width() < 767) {
			return;
		}
		
		var product = $(this),
			container = product.parent(),
			carousel = product.closest('.owl-carousel'),
			descr = uniJsVars.descr_hover ? product.find('.description') : '',
			attr = uniJsVars.attr_hover ? product.find('.attribute') : '',
			options = uniJsVars.option_hover ? product.find('.option') : '',
			product_w = product[0].getBoundingClientRect().width;
			
		if(container.hasClass('product-list') || container.hasClass('product-price') || product.data('hover')) {
			return;
		}
			
		//if($(options).children().width() == null || $(options).children().width() == 0) {
			container.css({height: product[0].getBoundingClientRect().height+20});
		//}
			
		if(carousel.length) {
			if(container.hasClass('owl-item')) container.addClass('select');
			if(container.hasClass('owl-carousel')) container = container.find('.owl-item.select');
			
			product.css({left: product.offset().left - carousel.offset().left, width: product_w}).detach().addClass('hover').prependTo(carousel).data('hover', true);
		} else {
			product.css({width: product_w}).addClass('hover').data('hover', true);
		}
			
		if(descr) descr.show();
		if(attr) attr.show();
		if(options && options.children().length) options.show();

		product.on('mouseleave', function(e) {
			if(descr) descr.hide();
			if(attr) attr.hide();
			if(options && options.children().length) options.hide();
			if(carousel.length) product.detach().appendTo(container);
			
			product.css({left: '', width: ''}).removeClass('hover').unbind('mouseleave').data('hover', false);
			container.css({height: ''}).removeClass('select');
		});
		
		product.on('mouseleave', 'select', function(e) {
			e.stopPropagation();
		});
	});	
});