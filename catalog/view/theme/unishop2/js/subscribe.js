$(function() {
	$('#subscribe').on('click', 'button', function() {
		
		$('.text-danger, .tooltip').remove();
		
		var form = $(this).closest('#subscribe'), data = form.find('input').serialize(), btn = form.find('button');
		
		$.ajax({
			url:$('base').attr('href')+'index.php?route=extension/module/uni_subscribe/add',
			type:'post',
			data:data,
			dataType:'json',
			beforeSend: function() {
				//btn.button('loading');
			},
			complete: function() {
				//btn.button('reset');
			},
			success: function(json) {
				if ((!json['alert'] && json['error']) || (json['alert'] && $('.subscribe__input-password').hasClass('show-pass'))) {
					uniFlyAlert('danger', json['error']);
				}
				
				if (json['alert']) {
					$('.subscribe__input-email, .subscribe__input-password').addClass('show-pass').attr('disabled', false);
				} else {
					$('.subscribe__input-email, .subscribe__input-password').removeClass('show-pass');
					$('.subscribe__input-password').attr('disabled', true);
				}

				if (json['success']) {
					uniModalWindow('modal-subscribe-success', '', json['success_title'], json['success']);
				}
			}
		});
	});
});