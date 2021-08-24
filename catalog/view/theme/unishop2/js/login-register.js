function uniLoginOpen() {
	uniAddCss('catalog/view/theme/unishop2/stylesheet/login_register.css');
	
	$.ajax({
		url:'index.php?route=extension/module/uni_login_register/modal',
		type:'post',
		data:{'type':'login'},
		dataType:'html',
		success:function(data) {
			$('html body').append(data);
			$('#modal-login-form').addClass(uniJsVars.popup_effect_in).modal('show');
			
			$(document).on('keydown', '#modal-login-form input', function(e) {
				if (e.keyCode == 13) {
					$(this).parent().parent().find('.btn').click();
				}
			});
		}
	});
}

function uniLoginSend() {
	var form = '#modal-login-form';
	
	$.ajax({
		url: 'index.php?route=extension/module/uni_login_register/login',
		type: 'post',
		data: $(form+' input, '+form+' textarea').serialize(),
		dataType: 'json',
		beforeSend: function() {
			$('.login_button').button('loading');
		},
		complete: function() {
			$('.login_button').button('reset');
		},
		success: function(json) {
			if (json['redirect']) {
				if (typeof(uniDelPageCache) === 'function') {
					uniDelPageCache();
				}
				
				if(window.location.pathname == '/logout/' || window.location.search == '?route=account/logout') {
					window.location = json['redirect'];
				} else {
					window.location.reload();
				}
			}
			
			if (json['error']) {
				uniFlyAlert('danger', json['error']);
			}
		}
	});
}

function uniRegisterOpen() {
	uniAddCss('catalog/view/theme/unishop2/stylesheet/login_register.css');
	uniAddJs('catalog/view/theme/unishop2/js/jquery.maskedinput.min.js');
	
	$.ajax({
		url: 'index.php?route=extension/module/uni_login_register/modal',
		type:'post',
		data:{'type':'register'},
		dataType: 'html',
		success: function(data) {
			$('html body').append(data);
			$('#modal-register-form').addClass(uniJsVars.popup_effect_in).modal('show');
		}
	});
}

function uniRegisterSend() {
	var form = '#modal-register-form';
	
	$.ajax({
		url: 'index.php?route=extension/module/uni_login_register/register',
		type: 'post',
		data: $(form+' input, '+form+' textarea').serialize(),
		dataType: 'json',
		beforeSend: function() {
			$('.register_button').button('loading');
		},
		complete: function() {
			$('.register_button').button('reset');
		},
		success: function(json) {				
			if (json['redirect']) {
				window.location = json['redirect'];
			}
			
			if (json['appruv']) {
				$('.modal-register').html($('<div class="register_success">'+json['appruv']+'</div>').fadeIn());
			}
			
			if (json['error']) {
				for (i in json['error']) {
					form_error(form, i);
				}
				
				uniFlyAlert('danger', json['error']);
			}
		}
	});
}

function uniRegisterPageSend() {
	var form = '.account-register__form';
	
	$.ajax({
		url: 'index.php?route=extension/module/uni_login_register/register',
		type: 'post',
		data: $(form+' input, '+form+' textarea').serialize(),
		dataType: 'json',
		beforeSend: function() {
			$('.register_button').button('loading');
		},
		complete: function() {
			$('.register_button').button('reset');
		},
		success: function(json) {				
			if (json['redirect']) {
				window.location = json['redirect'];
			}
			
			if (json['appruv']) {
				$('.register-form').html($('<div class="register_success">'+json['appruv']+'</div>').fadeIn());
			}
			
			if (json['error']) {
				for (i in json['error']) {
					form_error(form, i);
				}
				
				uniFlyAlert('danger', json['error']);
			}
		}
	});
}