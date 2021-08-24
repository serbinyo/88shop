function uniFlyMenu() {
	if($('#unicheckout').length) return;
	
	var prodPage = uniJsVars.fly_menu.product && $('#product').length;
	
	var init = function() {		
		$('#fly-menu').remove();
		
		var windowWidth = $(window).width(),
			breakpoint = 992,
			desktop_menu = (uniJsVars.fly_menu.desktop && windowWidth > breakpoint) ? true : false,
			mobile_menu = (uniJsVars.fly_menu.mobile != 0 && windowWidth <= breakpoint) ? true : false;
			
		if(!desktop_menu && !mobile_menu) return;
		
		let html = '<div id="fly-menu" class="fly-menu">';
			
		html += '<div class="container"><div class="row">';
			
		if(desktop_menu) {
			if(prodPage) {
				html += '<div class="fly-menu__product">';
				html += '<div class="fly-menu__product-name"><span>'+$('.heading-h1 h1').text()+'</span></div>';
				html += '<div class="fly-menu__product-price price">'+$('#product .price').html()+'</div>';
				
				var btn = $('#product').find('.product-page__add-to-cart');
				
				if(btn.length) {
					html += '<button type="button" class="fly-menu__product-btn '+btn.attr('class').replace('btn-lg', '')+'" data-pid="'+btn.data('pid')+'">'+btn.html()+'</button>';
				}
				
				html += '</div>';
			} else {
				html += '<div class="fly-menu__menu col-md-3 col-lg-3 col-xxl-4"><div id="menu" class="menu menu1">'+$('header #menu').html()+'</div></div>';
				html += '<div class="fly-menu__search">'+$('header #search').html()+'</div>';
			}
			
			html += '<div class="fly-menu__phone">'+$('.header-phones__main').html()+'</div>';
		}
		
		let menuItems = 6;
		
		if(!uniJsVars.fly_menu.wishlist) menuItems--;
		if(!uniJsVars.fly_menu.compare) menuItems--;
		
		if(mobile_menu){
			html += '<div class="fly-menu__block fly-menu__menu-m item-'+menuItems+'">';
			html += '<i class="fly-menu__icon fly-menu__icon-menu fa fa-bars"></i>';
			html += '</div>';
			
			html += '<div class="fly-menu__block fly-menu__search-m item-'+menuItems+'">';
			html += '<i class="fly-menu__icon fly-menu__icon-search fa fa-search"></i>';
			html += $('header #search').html();
			html += '</div>';
			
			if(uniJsVars.fly_menu.wishlist){
				html += '<div class="fly-menu__block fly-menu__wishlist item-'+menuItems+' uni-href" data-href="'+$('.top-menu__wishlist-btn').data('href')+'">';
				html += '<i class="fly-menu__icon fly-menu__icon-wishlist fas fa-heart"></i>';
				html += '<span class="fly-menu__wishlist-total fly-menu__total">'+$('.top-menu__wishlist-total').html()+'</span>';
				html += '</div>';
			}
			
			if(uniJsVars.fly_menu.compare){
				html += '<div class="fly-menu__block fly-menu__compare item-'+menuItems+' uni-href" data-href="'+$('.top-menu__compare-btn').data('href')+'">';
				html += '<i class="fly-menu__icon fly-menu__icon-compare fas fa-align-right"></i>';
				html += '<span class="fly-menu__compare-total fly-menu__total">'+$('.top-menu__compare-total').html()+'</span>';
				html += '</div>';
			}
		}
		
		let show_phone = 0;
		
		if(mobile_menu && show_phone){
			html += '<div class="fly-menu__block fly-menu__telephone item-'+menuItems+'">';
			html += '<i class="fly-menu__icon fa fa-phone"></i>';
			html += '<ul class="fly-menu__telephone-dropdown dropdown-menu dropdown-menu-right">'+$('.header-phones__ul').html()+'</ul>';
			html += '</div>';
		} else {
			html += '<div class="fly-menu__block fly-menu__account item-'+menuItems+'">';
			html += '<i class="fly-menu__icon fly-menu__icon-account fa fa-user"></i>';
			html += '<ul class="fly-menu__account-dropdown dropdown-menu dropdown-menu-right">'+$('#top #account ul').html()+'</ul>';
			html += '</div>';
		}

		html += '<div class="fly-menu__block fly-menu__cart item-'+menuItems+'">';
		html += '<i class="fly-menu__icon fly-menu__icon-cart fa fa-shopping-bag"></i>';
		html += '<span class="fly-menu__cart-total fly-menu__total">'+$('header .header-cart__total-items').text()+'</span>';
		html += '<div class="fly-menu__cart-dropdown header-cart__dropdown dropdown-menu dropdown-menu-right">'+$('header .header-cart__dropdown').html()+'</div>';
		html += '</div>';
		
		html += '</div></div>';
		
		html += '</div>';
		
		menuData = html.replace(/main-menu__collapse/g, "fly-menu__collapse");
		
		if(!$('#fly-menu').length) {
			$('html body').append(menuData);
				
			let menuBlock = $('.fly-menu__block'),
				menuIcon = $('.fly-menu__icon');
					
			menuIcon.on('click', function() {
				
				let $this = $(this),
					$parent = $this.parent();
				
				$('html').removeClass('fly-menu-open scroll-disabled');
				
				$('.menu-wrapper').removeClass('show');
				
				$parent.toggleClass('show');
		
				menuIcon.not($this).parent().removeClass('show');
							
				if(mobile_menu && $parent.hasClass('show')) {
					if($parent.hasClass('fly-menu__menu-m')) {
						$('html').addClass('scroll-disabled');
						$('.menu-wrapper').addClass('show');
					} else {
						$('html').addClass('fly-menu-open scroll-disabled')
					}
					
					if($parent.hasClass('fly-menu__search-m')) {
						$('.fly-menu__search-m .form-control').focus();
					}
				}
			});
			
			$('.fly-menu__account li').on('click', function() {
				$(this).closest('.fly-menu__block').removeClass('show');
			});
					
			$('main, footer').on('click', function(){
				menuBlock.removeClass('show');
				$('html').removeClass('fly-menu-open scroll-disabled');
			});
						
			if(desktop_menu) {
				uniMenuAim();
				uniMenuBlur();
		
				if(prodPage) {
					$(document).on('change', '#product input, #product select', function() {
						setTimeout(function() { 
							$('.fly-menu__product-price').html($('.product-page__price').html());
						}, 350);
					});
					
					$('.fly-menu__product-btn').click(function() {
						$('#button-cart').click();
					});
					
					$('.fly-menu__product-name').mouseover(function () {
						var boxWidth = $(this).width();
			
						$text = $('.fly-menu__product-name span');
						$textWidth = $('.fly-menu__product-name span').width();

						if ($textWidth > boxWidth) {
							$($text).animate({left: -(($textWidth+20) - boxWidth)}, 500);
						}
					}).mouseout(function () {
						$($text).stop().animate({left: 0}, 500);
					});
				} else {
					$('.fly-menu__search').css('margin-left', '-'+($('.fly-menu__menu').width() - $('.fly-menu .menu__header').outerWidth())+'px');
				}
			}
		}
		
		if(mobile_menu) {
			$('.fly-block__wishlist, .fly-block__compare').css('display', 'none');
		} else {
			$('.fly-block__wishlist, .fly-block__compare').removeAttr('style');
		}
		
		if(mobile_menu && uniJsVars.fly_menu.mobile == 'bottom') {
			$('footer').css('padding-bottom', 42);
			$('.fly-block').css('bottom', 62);
			$('#fly-menu').addClass('bottom show');
		} else {
			$('footer, .fly-block').removeAttr('style');
		}
	};
	
	init();
	
	let windowWidth = $(window).width();

	$(window).resize(function() {
		if($(this).width() != windowWidth) {
			windowWidth = $(this).width();
			init();
		}
	});
	
	$(window).scroll(function(){
		if($(this).scrollTop() > 200) {
			$('#fly-menu').addClass('show');
		} else {
			$('#fly-menu, #fly-menu .row > div').removeClass('show');
			$('html body').removeClass('fly-menu-open');
		}
	});
}

$(function() {
	uniFlyMenu();
	
	if(!uniJsVars.fly_menu.desktop && uniJsVars.fly_cart) {
		if($(window).width() > 992) {
			$(window).scroll(function(){		
				$(this).scrollTop() > 200 ? $('#cart').addClass('fly') : $('#cart').removeClass('fly');
			});
		}
	}
});