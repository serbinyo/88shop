//'use strict';

window.dataLayer = window.dataLayer || [];

var uni_touch_support;
	
if ('ontouchstart' in document.documentElement) uni_touch_support = true;

$(function() {
	if(uni_touch_support) $('body').addClass('touch-support');
	
	$('.header-phones__ul, .header-cart__dropdown').on('mouseenter', function() {
	    $(this).css('display', 'block');
	}).on('mouseleave',function () {
		$(this).css('display', '');
	});
	
	// menu	
	$('main').on('click', function() {
		$('.header-phones__ul, .header-cart__dropdown').css('display', '');
		$('#menu .navbar-collapse').removeClass('in');
	});
	
	uniMenuAim();
	uniMenuDropdownPos();
	uniMenuMobile();
	uniMenuBlur();
	// menu
	
	$('#language').on('click', 'li a', function(e) {
		e.preventDefault();
		$('#language input[name=\'code\']').val($(this).data('code'));
		$('#language').submit();
	});
	
	$('#currency').on('click', 'li a', function(e) {
		e.preventDefault();
		$('#currency input[name=\'code\']').val($(this).data('code'));
		$('#currency').submit();
	});
	
	$(document).on('click', '.header-search__category-li', function () {
		$(this).closest('.header-search').find('.header-search__category-span').text($(this).text());
		$(this).closest('.header-search').find('input[name=\'filter_category_id\']').val($(this).data('id'));
	});

	$(document).on('click', '.header-search__btn', function() {
		let url = $('base').attr('href') + 'index.php?route=product/search',
			elem = $(this).closest('.header-search'), 
			value = elem.find('input[name="search"]').val(), 
			filter_category_id = elem.find('input[name=\'filter_category_id\']').val();
		
		if (value) url += '&search='+encodeURIComponent(value);
		if (filter_category_id > 0) url += '&category_id='+encodeURIComponent(filter_category_id)+'&sub_category=true';
		url += '&description=true';
		
		window.location = url;
	});

	$(document).on('keydown', '.header-search__input', function(e) {
		if (e.keyCode == 13) {
			$(this).parent().find('.header-search__btn').click();
		}
	});
	
	$(document).on('input', '.header-search__input', function(e) {
		$('input[name="search"]').not($(this)).val($(this).val());
	});
	
	if($(window).width() > 520 && uniJsVars.search_phrase_arr.length > 1) {
		new Typed('.header-search__input', {
			strings: uniJsVars.search_phrase_arr,
			startDelay: 1500,
			typeSpeed: 80,
			backDelay: 1500,
			backSpeed: 45,
			attr: 'placeholder',
			bindInputFocusEvents: true,
			loop: true
		});
		
		$('.header-search__btn').on('click', function() {
			if($(this).prev().val() == '') $(this).prev().val($(this).prev().attr('placeholder'));
		});
	}
	
	$('.header-phones__additional').on('click', function() {
		$('.header-phones__additional').addClass('selected').not($(this)).removeClass('selected');
		
		if($('.header-phones__main').text() == $(this).data('phone') && $(this).data('href')) {
			location = $(this).data('href');
		} else {
			$('.header-phones__main').text($(this).data('phone')).data('href', $(this).data('href'));
		}
	});
	
	//if(!uni_touch_support) {
		$('[data-toggle=\'tooltip\']').tooltip({container:'body', trigger:'hover'});
	//}
	
	$('body').on('hide.bs.modal', '.modal.animated', function() {
		$(this).removeClass(uniJsVars.popup_effect_in).addClass(uniJsVars.popup_effect_out);
	});
		
	$('body').on('hidden.bs.modal', '.modal.animated', function(e) {
		setTimeout(function() {			
			e.target.remove();
		}, 600);
		
		if($('body > .modal.fade.in').length) $('body').addClass('modal-open');
	});
	
	$('.add_to_cart.disabled').each(function(){
		$(this).attr('disabled', true);
	});
	
	uniChangeBtn();
	
	$(document).ajaxStop(function() {	
		//if(!uni_touch_support) {
			$('[data-toggle=\'tooltip\']').tooltip({container:'body', trigger:'hover'});
		//}
		
		$('.add_to_cart.disabled').each(function(){
			$(this).attr('disabled', true);
		});
	});
	
	//quantity
	$('body').on('blur click', '.qty-switch__input, .qty-switch__btn', function(e) {
		let $elem = $(this);
		
		if($(this).hasClass('qty-switch__btn')) $elem = $(this).closest('.qty-switch').find('input');
		
		let qty = parseFloat($elem.val()),
			min = $elem.data('minimum') ? parseFloat($elem.data('minimum')) : 1,
			max = 10000,
			step = uniJsVars.qty_switch_step ? min : 1,
			decimal = 0,
			new_qty;
		
		if($(this).hasClass('qty-switch__input')) {
			if(step > 1) {
				qty = Math.round(qty/min)*min;
			}
			
			new_qty = (qty > min) && (qty < max) ? qty : min;
		} else {
			new_qty = $(this).hasClass('fa-plus') ? ((qty < max) ? qty+step : qty) : ((qty > min) ? qty-step : qty);
		}
		
		new_qty = new_qty.toFixed(decimal);
		
		if($(this).hasClass('qty-switch__input') && e.type == 'click') return;
		
		$elem.val(new_qty).change();
		
		if($(this).closest('.checkout-cart__quantity, .header-cart__quantity').length) {
			cart.update($elem.data('cid'), new_qty);
		}
	});
	
	$('body').on('mouseenter touchstart', '.option__img', function(e) {
		let elem = $(this),
			block = $('<div class="option__popup '+elem.data('type')+'"><img src="'+elem.data('thumb')+'" class="option__popup-img img-responsive" />'+elem.attr('alt')+'</div>'),
			imgSrc = elem.data('thumb'),
			elemTop = elem.offset().top,
			elemLeft = elem.offset().left+(elem.outerWidth()/2);
			
		$('body').append(block);
		
		if(elemLeft < block.outerWidth()/2) {
			elemLeft = elem.offset().left;
		} else if(elemLeft + (block.outerWidth()/2) > $(window).width()) {
			elemLeft = elem.offset().left + elem.outerWidth() - block.outerWidth();
		} else {
			elemLeft = elemLeft - (block.outerWidth()/2);
		}

		setTimeout(function() {
			block.css({top: elemTop-block.outerHeight()-10, left: elemLeft}).addClass('show');
		}, 100);
		
		elem.on('mouseleave', function() {
			block.remove();
		});
		
		$('body').on('touchstart', function(e){ 
			block.remove();     
		});
	});
	
	if(uniJsVars.change_opt_img) {
		$('main').on('click', '.product-thumb input[type="radio"] + .option__img', function() {
			$(this).closest('.product-thumb').find('a img:first').attr('src', $(this).data('thumb'));
		});
	}
	
	$('body').on('click', '.uni-href', function() {
		if(typeof($(this).data('href')) != 'undefined' && $(this).data('href') != '') {
			if(($(this).attr('target') || $(this).data('target')) == '_blank') {
				window.open($(this).data('href'), '_blank');
			} else {
				location = $(this).data('href');
			}
		}
	});
	
	//$('.breadcrumb').animate({scrollLeft: 1000}, 0);
	$('.breadcrumb').scrollLeft(1000);
});

var uniSelectView = {
	init:function(viewtype){
		var base = this;
		
		base.display = localStorage.getItem('display') ? localStorage.getItem('display') : default_view;
		base.displayMFP = localStorage.getItem('displayMFP');
		
		if(typeof(MegaFilter) === 'function' && base.displayMFP) base.display = base.displayMFP;
		if(typeof(viewtype) != 'undefined') base.display = viewtype;
		
		base.bind();
		base.switcher();
	},
	switcher:function() {
		var base = this, lastWindowWidth = $(window).width(), breakpoint = 992;
		
		base.switcher2 = function (new_width) {
			base.displayMobile = localStorage.getItem('displayMobile') ? localStorage.getItem('displayMobile') : default_mobile_view;
			
			let disp = (new_width <= breakpoint) ? base.displayMobile : base.display;
			
			if (disp == 'list') {
				base.list();
			} else if (disp == 'grid')  {
				base.grid();
			} else {
				base.compact();
			}
		}
			
		base.switcher2(lastWindowWidth);
			
		$(window).resize(function(){
			if($(this).width() != lastWindowWidth && $(this).width() <= breakpoint){
				base.switcher2($(this).width());
			}
		});
	},
	list:function() {
		var base = this, breakpoint = 992;
		
		let new_class = 'product-layout product-list list-view col-sm-12';
		
		if(!$('.product-layout').first().attr('class').includes('list-view')) {
			$('.list-view, .grid-view, .compact-view, .product-grid, .product-list, .product-compact').attr('class', new_class);
			$('.list-view').find('.product-thumb__name, .product-thumb__description, .product-thumb__option').height('auto');
			
			$.post('index.php?route=extension/module/uni_new_data/setDefaultView', {view: 'list'});
		}
		
		$('.sorts-block__btn').addClass('selected').not('#list-view').removeClass('selected');
		
		localStorage.setItem('display', 'list');
		localStorage.setItem('displayMFP', 'list');
		if($(window).width() <= breakpoint) localStorage.setItem('displayMobile', 'list');
	},
	grid:function() {
		var base = this, breakpoint = 992, col_left = $('#column-left').length, col_right =  $('#column-right').length, menu = $('.breadcrumb.col-md-offset-3.col-lg-offset-3').length;

		if ((col_left && col_right) || (col_right && menu)) {
			new_class = 'product-layout product-grid grid-view col-sm-12 col-md-6 col-lg-6 col-xxl-6-1';
		} else if (col_left || col_right || menu) {
			new_class = 'product-layout product-grid grid-view col-sm-6 col-md-4 col-lg-4 col-xxl-5';
		} else {
			new_class = 'product-layout product-grid grid-view col-sm-6 col-md-3 col-lg-3 col-xxl-4';
		}
		
		if(!$('.product-layout').first().attr('class').includes('grid-view')) {
			$.post('index.php?route=extension/module/uni_new_data/setDefaultView', {view: 'grid'});
		}
		
		$('.list-view, .grid-view, .compact-view, .product-grid, .product-list, .product-compact').attr('class', new_class);
		
		$('.sorts-block__btn').addClass('selected').not('#grid-view').removeClass('selected');
		
		$product = $('.product-grid');
		
		if($(window).width() > 575) {
			uniAutoHeight($product.find('.product-thumb__name'));
			uniAutoHeight($product.find('.product-thumb__description'));
			uniAutoHeight($product.find('.product-thumb__option'));
		}
		
		localStorage.setItem('display', 'grid');
		localStorage.setItem('displayMFP', 'grid');
		if($(window).width() <= breakpoint) localStorage.setItem('displayMobile', 'grid');
	},
	compact:function() {
		var base = this;
		
		let new_class = 'product-layout product-price compact-view col-sm-12';
		
		if(!$('.product-layout').first().attr('class').includes('compact-view')) {
			$('.list-view, .grid-view, .compact-view, .product-grid, .product-list, .product-compact').attr('class', new_class);
			$('.compact-view').find('.product-thumb__name, .product-thumb__description, .product-thumb__option').height('auto');
			
			$.post('index.php?route=extension/module/uni_new_data/setDefaultView', {view: 'compact'});
		}
		
		$('.sorts-block__btn').addClass('selected').not('#compact-view').removeClass('selected');
		
		if(!$('.compact-view').find('.product-thumb__option').children().length) {
			$('.compact-view').find('.product-thumb__option').remove()
		}
	
		localStorage.setItem('display', 'compact');
		localStorage.setItem('displayMFP', 'compact');
	},
	bind:function() {
		var base = this;
		$('#list-view').on('click', base.list);
		$('#grid-view').on('click', base.grid);
		$('#compact-view').on('click', base.compact);
	}
};

function uniMenuBlur() {
	if(uni_touch_support || !uniJsVars.menu_blur) return
		
	var uni_blur_blocks = $('header > div:not(#main-menu), #menu2, main, footer'), blur_delay = uniJsVars.menu_blur == 1 ? 110 : 10, blur_timer;
		
	$('.menu:not(.menu-right)').on('mouseenter', function() {
		blur_timer = setTimeout(function() { 
			uni_blur_blocks.addClass('blur');
		}, blur_delay);
	}).on('mouseleave', function() {
		clearTimeout(blur_timer);
		uni_blur_blocks.removeClass('blur');
	});
};

function uniMenuAim() {
	let items = '.menu__level-1-a, .menu__level-2';
	
	$('body').on('click', '.menu__pm', function() {
		$(this).toggleClass('open');
		$(this).next().collapse('toggle');
	});
	
	$('.menu-open, .menu-close').on('click', function() {
		$('html').toggleClass('scroll-disabled');
		$('.menu-wrapper').toggleClass('show');
		$('.fly-menu__block').removeClass('show')
	});
	
	if(!uni_touch_support) {
		$('.menu.menu2 .has-children').on('mouseenter', function() {
			$('.menu.menu2').addClass('open');
			$(this).children(items).addClass('open');
		}).on('mouseleave', function() {
			$('.menu.menu2').removeClass('open');
			$(this).children(items).removeClass('open');
		});
		
		$('.menu.menu1 .menu__collapse').menuAim({
			rowSelector:'.menu__level-1-li',
			submenuSelector:'*',
			activate:function(data) {
				$(data).children(items).addClass('open');
			},
			deactivate:function(data) {
				$(data).children(items).removeClass('open');
			},
			exitMenu:function() {
				return true;
			}
		});
		
		if($(window).width() > 992) {
			$('.menu__header').removeAttr('data-toggle');
			
			let menu_click = 0;
		
			if(menu_click){
				if($(window).width() > 992) {
					$('html body').on('click', '.menu-wrapper .menu1 .menu__header, .fly-menu .menu1 .menu__header', function() {
						$(this).next().toggleClass('show');
					});
				}
				
				$('html body').on('mouseleave', '.menu-wrapper .menu1, .fly-menu .menu1', function() {
					$(this).find('.menu__collapse').removeClass('show');
				});
			}
		}
	} else {
		if($(window).width() > 992) {
			$('.menu__level-1-a.chevron, .menu__level-1-a.additional').on('click', function(e) {
				e.preventDefault();
		
				$(items).removeClass('open');
		
				$(this).addClass('open');
				$(this).parent().children('.menu__level-2').addClass('open');
			});
		}
		
		$('.menu__level-1-a.chevron.disabled').on('touchstart', function() {
			$(this).toggleClass('open');
			$(this).next().toggleClass('open');
			$(this).next().next().collapse('toggle');
		});
		
		$('main, footer').on('click touchstart', function(){
			$(items).removeClass('open');
			$('.menu__pm').removeClass('open');
			$('.menu .collapse').collapse('hide');
			
			$('html').removeClass('scroll-disabled');
			$('.menu-wrapper').removeClass('show');
		});
	}
};

function uniMenuUpd(block) {
	
	if(!$(block).length) return;
	
	var init = function() {
		var lastWindowWidth = $(window).width(),
			menu_block = $(block),
			menu_items = menu_block.children('.menu__level-1-li:not(.menu__additional)');
			
		menu_items.css('display', '');
			
		if(lastWindowWidth < 992) return;
			
		var menu_width = menu_block.outerWidth(),
			max_items = lastWindowWidth > 1600 ? 8 : 6,
			total_width = 0,
			new_items = '';
		
		menu_block.find('.menu__additional').remove();
		
		if(menu_items.length > max_items) {
			menu_width = menu_width - 100;
		} else {
			return;
		}
		
		menu_items.each(function() {
			total_width += $(this).outerWidth(true);
				
			if(total_width > menu_width) {
				var item = $(this).find('> a'), item_child = $(this).find('.menu__level-2-a'), new_child_items = '';
				
				if(item_child.length) {
					new_child_items = '<div class="menu__level-3"><ul class="menu__level-3-ul">';
				
					item_child.each(function() {
						new_child_items += '<li class="menu__level-3-li"><a class="menu__level-3-a';
						
						if(typeof($(this).attr('href')) != 'undefined') {
							new_child_items += '" href="'+$(this).attr('href')+'">';
						} else {
							new_child_items += ' disabled">';
						}
						
						new_child_items += $(this).text()+'</a></li>';
					});
					
					new_child_items += '</ul></div>';
				}
				
				new_items += '<div class="menu__level-2-ul col-md-12"><a class="menu__level-2-a';
				
				if(new_child_items) {
					new_items += ' uppercase';
				}
				
				if(typeof(item.attr('href')) != 'undefined') {
					new_items += '" href="'+item.attr('href')+'">';
				} else {
					new_items += ' disabled">';
				}

				new_items += item.text()+'</a>'+new_child_items+'</div>';

				$(this).hide();
			} else {
				$(this).show();
			}
		});
		
		if (total_width > menu_width) {
			if (!menu_block.find('.menu__additional').length) {
				var html = '<li class="menu__level-1-li menu__additional has-children">';
				    html += '<a class="menu__level-1-a additional"><i class="fa fa-ellipsis-h"></i></a>';
				    html += '<div class="menu__level-2 column-1"></div>';
				    html += '</li>';
					
				menu_block.append(html);
				
				uniMenuAim();
			}
				
			menu_block.find('.menu__additional .menu__level-2').html(new_items);
		} else {
			menu_block.find('.menu__additional').remove();
		}
	}
	
	init();
	
	$(window).resize(function() {
		init();
	});
};

function uniMenuDropdownPos() {
	var menu_block = $('.menu2 .menu__collapse');
	
	var init = function() {
		if($(window).width() > 992) {
			setTimeout(function() {
				menu_block.find('.menu__level-2').each(function() {
					var child_pos = ($(this).parent().offset().left + $(this).outerWidth()) - (menu_block.offset().left + menu_block.outerWidth());
			
					if (child_pos > 0) $(this).css('margin-left', '-'+child_pos+'px');
				});
			}, 300);
		}
	}
	
	init();
	
	$(window).resize(function() {
		init();
	});
};

function uniMenuMobile() {
	var menu1 = $('.menu1 .menu__collapse'),
		menu2 = $('.menu-right .menu__collapse');
		
	if(!menu1.length) return;
	
	var init = function() {
		var windowWidth = $(window).width();
		
		if(windowWidth <= 992) {
			menu2.find('>li').addClass('new-items').appendTo(menu1);
		} else {
			menu1.find('.new-items').removeClass('new-items').appendTo(menu2);
		}
		
		$windowWidth = windowWidth;
	}
	
	init();
	
	$(window).resize(function() {
		if ($(window).width() != $windowWidth) {
			init();
		}
	});
}

function uniBannerLink(url) {
	$.ajax({
		url: url,
		type: 'get',
		dataType: 'html',
		success: function(data) {
			var data = $(data);
			
			title = data.find('.heading-h1 h1').text();
			data.find('.heading-h1').remove();
			text = data.find('#content').html();
			
			uniModalWindow('modal-banner', '', title, text);
		}
	});
}

function form_error(form, input, text) {
	var element = $(form+' input[name=\''+input+'\'], '+form+' textarea[name=\''+input+'\'], '+form+' select[name=\''+input+'\']').addClass('input-warning');
	
	setTimeout(function() { 
		$(form+' .input-warning').removeClass('input-warning');
	}, 15000);
	
	$(form+' .input-warning').click(function() {
		$(this).removeClass('input-warning');
	});
}

function uniScrollTo(target, time) {
	if (typeof(time) === 'undefined') time = 200;
	$('html, body').animate({scrollTop: $(target).offset().top-100}, time);
}

function scroll_to(target) {		
	$('html, body').animate({scrollTop: $(target).offset().top-100}, 1000);
}

function uniChangeBtn() {
	let cart = function() {
		let products = $('.header-cart__wrapper').data('products');
		
		if(typeof(products) === 'undefined') return;
		
		products = String(products).split(',').map(Number);
		
		$('.product-thumb__add-to-cart, .product-page__add-to-cart, .compare-page__cart').each(function() {
			if(products.indexOf($(this).data('pid')) !== -1) {
				$(this).addClass('in_cart');
				$(this).children('i').attr('class', uniJsVars.cart_btn.icon_incart);
				$(this).children('span').text(uniJsVars.cart_btn.text_incart);
			}
		});
	}
	
	let compare = function() {
		products = $('.top-menu__compare').data('products');
		
		if(!products) return;
		
		products = String(products).split(',').map(Number);
		
		$('.product-thumb__compare, .product-page__compare-btn').each(function() {
			let pid = Number($(this).attr('onclick').replace(/\D+/g,''));
			
			if(products.indexOf(pid) !== -1) {
				$(this).attr('onclick', $(this).attr('onclick').replace('compare.add', 'compare.remove')).attr('title', uniJsVars.compare_btn.text_remove).addClass('active');
			}
		});
	}
	
	let wishlist = function() {
		products = $('.top-menu__wishlist').data('products');
		
		if(!products) return;
		
		products = String(products).split(',').map(Number);
		
		$('.product-thumb__wishlist, .product-page__wishlist-btn').each(function() {
			let pid = Number($(this).attr('onclick').replace(/\D+/g,''));
			
			if(products.indexOf(pid) !== -1) {
				$(this).attr('onclick', $(this).attr('onclick').replace('wishlist.add', 'wishlist.remove')).attr('title', uniJsVars.wishlist_btn.text_remove).addClass('active');
			}
		});
	}
	
	cart();
	compare();
	wishlist();
	
	$(document).ajaxStop(function(){
		cart();
		compare();
		wishlist();
	});
}

function uniModalWindow(id, type, title, data) {
	
	/* 	id = id modal form;	type = sm, lg, or empty; title = title modal form; data = text or other data modal form; */
	
	$('#'+id).remove();
				
	var html  = '<div id="'+id+'" class="modal '+uniJsVars.popup_effect_in+'">';
		html += '<div class="modal-dialog modal-'+type+'">';
		html += '<div class="modal-content">';
		html += '<div class="modal-header">';
		html += '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
		html += '<h4 class="modal-title">'+title+'</h4>';
		html += '</div>';
		html += '<div class="modal-body">'+data+'</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
	
	$('html body').append(html);
	$('#'+id).modal('show');
}

function uniAutoHeight(div) {
	var block_height = function() {
		$(div).css({height: ''});
		
		var maxheight = 0;
		
		$(div).each(function(){
			if($(this).height() > maxheight) {
				maxheight = $(this).height();
			}
		});
		
		if(maxheight > 0) $(div).height(maxheight);
	};
	
	block_height();
	$(window).resize(block_height);
}

function uniFlyAlert(type, data) {
	let time = 15,
		effectIn = 'animated bounceInRight',
		effectOut = 'animated bounceOutRight';
	
	if(typeof(uniJsVars) != 'undefined') {
		time = uniJsVars.alert_time;
		effectIn = uniJsVars.alert_effect_in;
		effectOut = uniJsVars.alert_effect_out;
	}
	
	let time1 = time*1000,
		time2 = time1+1000,
		time3 = 100,
		top_offset = 50,
		top_margin = 10,
		icon;
	
	if(type == 'success') icon = 'fa-check-circle';
	if(type == 'danger') icon = 'fa-times-circle';
	if(type == 'warning') icon = 'fa-exclamation-circle';
	
	$('.uni-alert').remove();
	
	let createAlert = function(data) {
		if($('.uni-alert').length) {
			top_offset = $('.uni-alert:last').position().top + $('.uni-alert:last').outerHeight() + top_margin;
		}
	
		let block = $('<div class="uni-alert alert-'+type+' '+effectIn+'" style="top:'+top_offset+'px"><i class="uni-alert__icon fa '+icon+'"></i><div>'+data+'</div></div>');
	
		$('html body').append(block);
	
		setTimeout(function() {
			block.removeClass(effectIn).addClass(effectOut);
		}, time1);
	
		setTimeout(function() { 
			block.remove();
		}, time2);
	}
	
	if(typeof(data) == 'object') {
		let arr = [];
	
		for (i in data) {
			arr.push(data[i]);
		}
	
		let index = -1,
			timer = setInterval(function () {
			if (++index == arr.length) {
				clearInterval(timer);
			} else {
				createAlert(arr[index]);
			}
		}, time3);
	} else {
		createAlert(data);
	}
}

//add css and js from script
var cssUrls = [], jsUrls = [];

function uniAddCss(url) {
	if(cssUrls.indexOf(url) == -1) {
		cssUrls.push(url);
		var style = '<link href="'+url+'" rel="preload" as="style" />';
		style += '<link href="'+url+'" type="text/css" rel="stylesheet" media="screen" />';
		$('html head').append(style)
	}
}

function uniAddJs(url) {
	if(jsUrls.indexOf(url) == -1) {
		jsUrls.push(url);
		$.getScript(url);
	}
}

(function($){
	var Modules = {
		init:function(options, el) {
            var base = this;
			
			base.$elem = $(el);
			base.$elem2 = $(el).children();
			base.options = $.extend({}, $.fn.uniModules.options, options);
			
			base.load();
        },
		load:function() {
			var base = this, wrapper = base.$elem;
			
			if(base.$elem2.closest('.tab-content').length) {
				wrapper = base.$elem2.closest('.tab-content');
			}
			
			base.wrapper = wrapper;
			
			if((base.options.type == 'grid' && module_on_mobile == 'carousel' && base.wrapper.width()+20 < 768) || base.wrapper.closest('#column-left, #column-right').length) {
				base.options.type = 'carousel';
			}
			
			if(base.wrapper.closest('#column-left, #column-right').length) {
				base.options.items = {0: {items: 1}};
			}
			
			if (base.options.type == 'grid') {
				base.$elem2.children().wrap('<div class="uni-module__item" style="width:'+base.items()+'px"></div>');
			} else {
				base.$elem2.addClass('owl-carousel').owlCarousel({
					responsive:base.options.items,
					responsiveBaseElement:wrapper,
					dots:base.options.dots,
					mouseDrag:false,
					loop:base.options.loop,
					autoplay:base.options.autoplay,
					nav:true,
					navText:['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
				});
				
				var $item = base.$elem2.find('.owl-item');
				
				if($item.width() <= 0)	{
					$item.css({width:base.items()});
					base.$elem2.find('.owl-stage').css({width:$item.length * base.items()});
				}
			}
			
			base.update();
			
			//setTimeout(function() {
				base.reload();
				base.responsive();
			//}, 3000);
		},
		items:function() {
			var base = this, match = -1, width = base.wrapper.width();
			
			width += (base.wrapper.attr('class') == 'tab-content' && width < 520) ? 10 : 20;
			
			$.each(base.options.items, function(breakpoint) {
				if (breakpoint <= width && breakpoint > match) {
					match = Number(breakpoint);
				}
			});
			
			return Math.floor(width / base.options.items[match]['items']);
		},
		update:function() {
			var base = this, div_arr = base.options.autoheight;
			
			if($(window).width() < 575 && $('.'+div_arr[0]).closest('.product-thumb').length) {
				div_arr = [];
			}
			
			for (i in div_arr) {
				var maxheight = 0, $elem = base.$elem2.find('.'+div_arr[i]);
				
				if($elem.css('display') !== 'none') {
					$elem.css('height', '');

					$.each($elem, function() {
						if($(this).height() > maxheight) {
							maxheight = $(this).height();
						}
					});
				
					if(maxheight > 0) $elem.height(maxheight);
				}
			}
			
			base.$elem2.addClass('load-complete');
		},
		responsive:function () {
            var base = this, lastWindowWidth = $(window).width();
			
			base.resizer = function () {
                if ($(window).width() != lastWindowWidth || uni_touch_support) {
					if (base.options.type == 'grid') {	
						base.$elem2.children().css('width', base.items());
					}
					
					base.update();
                }
            };
			
			$(window).resize(function() {
				base.resizer();
			});
        },
		reload:function() {
			var base = this, div = base.$elem.parent(), modal = div.hasClass('modal-body') ? true : false, tab = div.hasClass('tab-pane') ? div.attr('id') : false;
			
			if(modal) {
				setTimeout(function() { 
					base.update();
				}, 750);
			}
			
			if(tab) {
				div.parent().prev().find('li a').on('shown.bs.tab', function(e) {
					if($(this).attr('href') == '#'+tab) {
						base.update();
					}
				});
			}
		}
	};
	
	$.fn.uniModules = function(options) {		
		return this.each(function () {
            if ($(this).data('uni-modules-init') === true) {
                return false;
            }
			
            $(this).data('uni-modules-init', true);
			
            var module = Object.create(Modules);
            module.init(options, this);
        });
	};
	
	$.fn.uniModules.options = {
		type 	   :'carousel',
		items	   :{0:{items:2},700:{items:3},1050:{items:4},1400:{items:5}},
		autoheight :[],
		dots	   :true,
		loop	   :false,
		autoplay   :false
	};

	var Timer = {
		init:function(options, el) {
            var base = this;
			
			base.options = $.extend({}, $.fn.uniTimer.options, options);
			base.days = 24*60*60, base.hours = 60*60, base.minutes = 60;
			
			var date_arr = base.options.date.split('-'),
				year = parseFloat(date_arr[0]), 
				month = parseFloat(date_arr[1])-1, 
				day = parseFloat(date_arr[2]);
			
			base.$date = (new Date(year, month, day)).getTime();
			base.$elem = $(el);
			
			if(base.$date > (new Date()).getTime())	{
				base.load();
			}
        },
		load:function() {
			var base = this, i = 4;
			
			html = '<div class="uni-timer">';
			
			for(i in base.options.texts){
				
				if(i > 0) html += '<div class="uni-timer__colon">:</div>';
				
				html += '<div class="uni-timer__group">';
				html += '<span class="uni-timer__digit"></span><span class="uni-timer__digit"></span>';
				
				if(!base.options.hideText) html += '<div class="uni-timer__text">'+base.options.texts[i]+'</div>';
				
				html += '</div>';
			}
			
			html += '</div>';
			
			base.$elem.append(html);
			base.$positions = base.$elem.find('.uni-timer__digit');
			base.count();
		},
		count:function() {
			var base = this, left, d, h, m, s;
			
			left = Math.floor((base.$date - (new Date()).getTime())/1000);
			
			left = left > 0 ? left : 0;
			
			d = Math.floor(left / base.days);
			left -= d*base.days;
			h = Math.floor(left / base.hours);
			left -= h*base.hours;
			m = Math.floor(left / base.minutes);
			left -= m*base.minutes;
			s = left;
			
			base.count2(0, 1, d);
			base.count2(2, 3, h);
			base.count2(4, 5, m);
			base.count2(6, 7, s);
			
			if (d == 0) base.hideGroup(0);
			if (h == 0) base.hideGroup(1);
			
			setTimeout(function() { 
				base.count();
			}, 1000);
		}, 
		count2:function(minor, major, value) {
			var base = this;
			
			base.switchDigit(base.$positions.eq(minor), Math.floor(value/10)%10);
			base.switchDigit(base.$positions.eq(major), value%10);
		},
		switchDigit:function(position, number) {
			var base = this;
			
			if(position.data('digit') == number){
				return false;
			}
	
			position.data('digit', number).text(number);
		},
		hideGroup:function(num) {
			var base = this;
			
			if(base.options.hideIsNull) {
				base.$elem.find('.digit-group-'+num+', .digit-group-'+num+' + .colon').hide();
			}
		}
	}
	
	$.fn.uniTimer = function(options) {		
		return this.each(function () {
            if ($(this).data("uni-timer-init") === true) {
                return false;
            }
			
            $(this).data("uni-timer-init", true);
			
            var timer = Object.create(Timer);
            timer.init(options, this);
        });
	};
	
	$.fn.uniTimer.options = {
		date		:'0000-00-00',
		texts		:['Дней','Часов','Минут','Секунд'],
		hideText	:false,
		hideIsNull	:false
	};
})(jQuery);

var cart = {
	'add': function(product_id, elem) {
		
		var $elem = $(elem).closest('.product-thumb, .product-thumb-related'),
			product_qty = $elem.find('.qty-switch__input').val(), 
			product_options = $elem.find('.option input[type=\'text\'], .option input[type=\'hidden\'], .option input:checked, .option select, .option textarea'),
			data = 'product_id='+product_id+'&quantity='+(typeof(product_qty) != 'undefined' ? product_qty : 1);
			
		if (product_options.length) {
			data += '&'+product_options.serialize();
		}
		
		$.ajax({
			url: $('base').attr('href')+'index.php?route=checkout/cart/add',
			type: 'post',
			data: data,
			dataType: 'json',
			success: function(json) {
				$('.text-danger').remove();
				
				if (json['redirect'] && (!$elem.find('.option').children().length || $(window).width() < 575)) {
					window.location = json['redirect'];
					
					return;
				}
				
				$('.form-group').removeClass('has-error');

				if (json['error']) {
					if (json['error']['option']) {
						for (i in json['error']['option']) {
							var elem = $('.option .input-option' + i.replace('_', '-')), elem2 = (elem.parent().hasClass('input-group')) ? elem.parent() : elem;
							
							elem2.after('<div class="text-danger">'+json['error']['option'][i]+'</div>');
							$('.option .text-danger').delay(5000).fadeOut();
							
							uniFlyAlert('danger', json['error']['option'][i])
						}
					}
				}

				if (json['success']) {
					
					cart.uniCartUpd();
					
					if(!$('#unicheckout').length) {
						
						if(!uniJsVars.cart_popup_disable) {
							uniModalWindow('modal-cart', '', uniJsVars.modal_cart.text_heading, $('header').find('.header-cart__dropdown').html());
						
							if(uniJsVars.cart_popup_autohide) {
								setTimeout(function() { 
									$('#modal-cart').modal('hide');
								}, uniJsVars.cart_popup_autohide_time * 1000);
							}
						}
					}
					
					dataLayer.push({
						'event': 'addToCart',
						'ecommerce':{
							'currencyCode':uniJsVars.currency.code,
							'add':{
								'products':[json['products']]
							}
						}
					});
					
					if (typeof(gtag) === 'function') {
						gtag('event', 'add_to_cart', {'items': [json['products']]});
					}
					
					if(uniJsVars.cart_btn.metric_id && uniJsVars.cart_btn.metric_target) {
						if (typeof(ym) === 'function') {
							ym(uniJsVars.cart_btn.metric_id, 'reachGoal', uniJsVars.cart_btn.metric_target);
						} else {
							new Function('yaCounter'+uniJsVars.cart_btn.metric_id+'.reachGoal(\''+uniJsVars.cart_btn.metric_target+'\')')();
						}
					}
					
					if(uniJsVars.cart_btn.analytic_category && uniJsVars.cart_btn.analytic_action) {
						if (typeof(gtag) === 'function') {
							gtag('event', uniJsVars.cart_btn.analytic_action, {'event_category': uniJsVars.cart_btn.analytic_category});
						} else if (typeof(ga) === 'function') {
							ga('send', 'event', uniJsVars.cart_btn.analytic_category, uniJsVars.cart_btn.analytic_action);
						}
					}
				}
			},
	        error: function(xhr, ajaxOptions, thrownError) {
	            console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
		});
	},
	'update': function(key, quantity, product_id) {
		$.ajax({
			url: $('base').attr('href')+'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'quantity['+key+']='+quantity,
			dataType: 'html',
			beforeSend: function() {
				if(!$('#unicheckout').length) {
					$('.header-cart__wrapper, .checkout-cart__wrap').append('<div class="preloader"></div>');
				}
			},
			success: function() {
				cart.uniCartUpd();
				
				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					$('#content').load('index.php?route=checkout/cart #content > *');
				}
			},
	        error: function(xhr, ajaxOptions, thrownError) {
	            console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
		});
	},
	'remove': function(key, product_id) {
		$.ajax({
			url: $('base').attr('href')+'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key='+key,
			dataType: 'json',
			beforeSend: function() {
				if(!$('#unicheckout').length) {
					$('.header-cart__wrapper, .checkout-cart__wrap').append('<div class="preloader"></div>');
				}
			},
			success: function(json) {
				cart.uniCartUpd();

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					$('#content').load('index.php?route=checkout/cart #content > *');
				}
				
				$('.product-thumb__add-to-cart, .product-page__add-to-cart').each(function(){
					if(product_id == $(this).data('pid')) {
						$(this).removeClass('in_cart');
						$(this).children('i').attr('class', uniJsVars.cart_btn.icon);
						$(this).children('span').text(uniJsVars.cart_btn.text);
					}
				});
			},
	        error: function(xhr, ajaxOptions, thrownError) {
	            console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
		});
	},
	'uniCartUpd': function() {
		$.get('index.php?route=common/cart/info', function(data) {
			$('.header-cart__dropdown, .fly-menu__cart-dropdown').html($(data).find('.header-cart__dropdown').html());
			$('.header-cart__total-items, .fly-menu__cart-total').html($(data).find('.header-cart__total-items').text());
			
			if($('#modal-cart').length) {
				$('#modal-cart .modal-body').html($(data).find('.header-cart__dropdown').html());
			}
		});
		
		if (typeof(uniDelPageCache) === 'function') {
			uniDelPageCache();
		}
		
		if($('#unicheckout').length) {
			uniCheckoutUpdate();
		}
	}
}

var voucher = {
	'add': function() {

	},
	'remove': function(key) {
		$.ajax({
			url: $('base').attr('href')+'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				if(!$('#unicheckout').length) {
					$('.header-cart__wrapper, .checkout-cart__wrap').append('<div class="preloader"></div>');
				}
			},
			success: function(json) {
				cart.uniCartUpd();

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					window.location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
			},
	        error: function(xhr, ajaxOptions, thrownError) {
	            console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
		});
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: $('base').attr('href')+'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				if (json['redirect']) {
					window.location = json['redirect'];
				}

				if (json['success']) {
					if(json['success'].indexOf('account/login') !== -1) {
						uniFlyAlert('warning', json['success']);
					} else {
						uniFlyAlert('success', json['success']);
					}
					
					let wishlist_total = (json['success'].indexOf('account/login') == -1) ? json['total'].replace(/\s+/g, '').match(/(\d+)/g) : 0;
					
					wishlist.uniWishlistUpd(wishlist_total);
				}
			}
		});
	},
	'remove': function(product_id) {
		$.ajax({
			url: 'index.php?route=extension/module/uni_new_data/wishlistRemove',
			type: 'post',
			data: 'product_id='+product_id,
			dataType: 'json',
			success: function(json) {
				if (json['success']) {
					uniFlyAlert('warning', json['success']);
					
					wishlist.uniWishlistUpd(json['total']);
					
					$('.product-thumb__wishlist, .product-page__wishlist-btn').each(function() {
						let pid = Number($(this).attr('onclick').replace(/\D+/g,''));
			
						if(product_id == pid) {
							$(this).attr('onclick', $(this).attr('onclick').replace('wishlist.remove', 'wishlist.add')).attr('title', uniJsVars.wishlist_btn.text).removeClass('active');
						}
					});
				}
			}
		});
	},
	'uniWishlistUpd': function(total){
		$('.fly-block__wishlist-total, .fly-menu__wishlist-total, .top-menu__wishlist-total').text(total);
		
		if (typeof(uniDelPageCache) === 'function') {
			uniDelPageCache();
		}
		
		$.get('index.php?route=account/wishlist', function(data) {
			$('.top-menu__wishlist').data('products', $(data).find('.top-menu__wishlist').data('products'));
		});
	}
}

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				if (json['success']) {
					uniFlyAlert('success', json['success']);
					
					$('#compare-total').html('<i class="compare-block__icon fas fa-align-right"></i>'+json['total']);
					
					let compare_total = json['total'].replace(/\s+/g, '').match(/(\d+)/g);
					
					compare.uniCompareUpd(compare_total);
				}
			}
		});
	},
	'remove': function(product_id) {
		$.ajax({
			url: 'index.php?route=extension/module/uni_new_data/compareRemove',
			type: 'post',
			data: 'product_id='+product_id,
			dataType: 'json',
			success: function(json) {
				if (json['success']) {
					uniFlyAlert('warning', json['success']);
					
					compare.uniCompareUpd(json['total']);
					
					$('.product-thumb__compare, .product-page__compare-btn').each(function() {
						let pid = Number($(this).attr('onclick').replace(/\D+/g,''));
			
						if(product_id == pid) {
							$(this).attr('onclick', $(this).attr('onclick').replace('compare.remove', 'compare.add')).attr('title', uniJsVars.compare_btn.text).removeClass('active');
						}
					});
				}
			}
		});
	},
	'uniCompareUpd': function(total){
		$('.fly-block__compare-total, .fly-menu__compare-total, .top-menu__compare-total').text(total);
		
		if (typeof(uniDelPageCache) === 'function') {
			uniDelPageCache();
		}
		
		$.get('index.php?route=product/compare', function(data) {
			$('.top-menu__compare').data('products', $(data).find('.top-menu__compare').data('products'));
		});
	}
}

$(document).on('click', '.agree', function(e) {
	e.preventDefault();
	
	var element = this;

	$.ajax({
		url: $(element).attr('href'),
		type: 'get',
		dataType: 'html',
		success: function(data) {
			const text = data.includes('article_description') ? $(data).find('.article_description').html() : data;
			
			uniModalWindow('modal-agree', '', $(element).text(), text);
		}
	});
});

function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();

			$.extend(this, option);

			$(this).attr('autocomplete', 'off');

			$(this).on('focus', function() {
				this.request();
			});

			$(this).on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			this.click = function(event) {
				event.preventDefault();

				value = $(event.target).parent().attr('data-value');

				if (value && this.items[value]) {
					this.select(this.items[value]);
				}
			}

			this.show = function() {
				var pos = $(this).position();

				$(this).siblings('ul.dropdown-menu').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});

				$(this).siblings('ul.dropdown-menu').show();
			}

			this.hide = function() {
				$(this).siblings('ul.dropdown-menu').hide();
			}

			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			this.response = function(json) {
				html = '';

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						this.items[json[i]['value']] = json[i];
					}

					for (i = 0; i < json.length; i++) {
						if (!json[i]['category']) {
							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
						}
					}

					var category = new Array();

					for (i = 0; i < json.length; i++) {
						if (json[i]['category']) {
							if (!category[json[i]['category']]) {
								category[json[i]['category']] = new Array();
								category[json[i]['category']]['name'] = json[i]['category'];
								category[json[i]['category']]['item'] = new Array();
							}

							category[json[i]['category']]['item'].push(json[i]);
						}
					}

					for (i in category) {
						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

						for (j = 0; j < category[i]['item'].length; j++) {
							html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$(this).siblings('ul.dropdown-menu').html(html);
			}

			$(this).after('<ul class="dropdown-menu"></ul>');
			$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

		});
	}
})(window.jQuery);