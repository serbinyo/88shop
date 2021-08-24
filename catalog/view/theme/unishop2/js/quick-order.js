function quick_order(id, flag) {
	uniAddCss('catalog/view/theme/unishop2/stylesheet/quick_order.css');
	uniAddJs('catalog/view/theme/unishop2/js/jquery.maskedinput.min.js');
	
	$.ajax({
		url:'index.php?route=extension/module/uni_quick_order',
		type:'post',
		data:{'id':id, 'flag': (typeof(flag) != 'undefined' ? 1 : 0)},
		dataType:'html',
		success:function(data) {
			$('html body').append(data);
			$('#modal-quick-order').addClass(uniJsVars.popup_effect_in).modal('show');
		}
	});
}

function uniQuickOrderAdd() {
	var form = '#modal-quick-order';
	
	$.ajax({
		url: 'index.php?route=extension/module/uni_quick_order/add',
		type: 'post',
		data: $(form+' input, '+form+' textarea, '+form+' select').serialize(),
		dataType: 'json',
		beforeSend: function() {
			$(form+' .add_to_cart').button('loading');
		},
		complete: function() {
			$(form+' .add_to_cart').button('reset');
		},
		success: function(json) {
			$('.text-danger').remove();
				
			$('.form-group').removeClass('has-error');
			
			if (json['error']) {
				if (json['error']['option']) {
					for (i in json['error']['option']) {							
						
						var element = $('#quick_order #input-option' + i.replace('_', '-'));
						
						if (element.parent().hasClass('input-group')) {
							element.parent().after('<div class="text-danger">'+json['error']['option'][i]+'</div>');
						} else {
							element.after('<div class="text-danger">'+json['error']['option'][i]+'</div>');
						}
						
						json['error'][i] = json['error']['option'][i];
					}
					
					delete json['error']['option'];
				}
				
				for (i in json['error']) {
					form_error(form, i, json['error'][i]);
				}
				
				uniFlyAlert('danger', json['error']);
			}
		
			if (json['success']) {			
				dataLayer.push({
					'ecommerce':{
						'currencyCode': uniJsVars.currency.code,
						'purchase':{
							'actionField':{
								'id': json['success']['order_id'],
								'goal_id': uniJsVars.quick_order.metric_taget_id
							},
							'products': json['success']['products']
						}
					}
				});
				
				if (typeof(gtag) === 'function') {
					gtag('event', 'purchase', {'transaction_id': json['success']['order_id'], 'currency': uniJsVars.currency.code,	'items': json['success']['products']});
				}
				
				if(uniJsVars.quick_order.metric_id && uniJsVars.quick_order.metric_target) {
					if (typeof(ym) === 'function') {
						ym(uniJsVars.quick_order.metric_id, 'reachGoal', uniJsVars.quick_order.metric_target);
					} else {
						new Function('yaCounter'+uniJsVars.quick_order.metric_id+'.reachGoal(\''+uniJsVars.quick_order.metric_target+'\')')();
					}
				}
				
				if(uniJsVars.quick_order.analytic_category && uniJsVars.quick_order.analytic_action) {
					if (typeof(gtag) === 'function') {
						gtag('event', uniJsVars.quick_order.analytic_action, {'event_category': uniJsVars.quick_order.analytic_category});
					} else if (typeof(ga) === 'function') {
						ga('send', 'event', uniJsVars.quick_order.analytic_category, uniJsVars.quick_order.analytic_action);
					}
				}
				
				$('#quick_order').html('<div class="row"><div class="col-xs-12">'+json['success']['text']+'</div></div>');
				
				setTimeout(function() {			
					$('#modal-quick-order').modal('hide');
				}, 5000);
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
	});
}