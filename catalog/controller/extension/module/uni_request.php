<?php  
class ControllerExtensionModuleUniRequest extends Controller {
	public function index() {
		
		if (!isset($this->request->server['HTTP_X_REQUESTED_WITH']) || strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
			
			return;
		}
		
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('extension/module/uni_request');
		$this->load->language('account/register');
	
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$data['name_text'] = $uniset[$lang_id]['callback_name_text'];
		$data['phone_text'] = $uniset[$lang_id]['callback_phone_text'];
		$data['mail_text'] = $uniset[$lang_id]['callback_mail_text'];
		$data['comment_text'] = $uniset[$lang_id]['callback_comment_text'];
		
		$data['customer_firstname'] = $this->customer->isLogged() ? ($this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName()) : '';
		$data['customer_email'] = $this->customer->getEmail();
		$data['customer_telephone'] = $this->customer->getTelephone();
	
		$show_phone = isset($this->request->get['phone']) ? true : false;
		$show_mail = isset($this->request->get['mail']) ? true : false;
		$show_comment = isset($this->request->get['comment']) ? true : false;
		
		$data['reason'] = isset($this->request->get['reason']) && $this->request->get['reason'] != '' ? htmlspecialchars(strip_tags($this->request->get['reason'])) : '';
		$data['product_id'] = isset($this->request->get['p_id']) && $this->request->get['p_id'] != '' ? (int)$this->request->get['p_id'] : 0;
		
		$settings = $this->config->get('uni_request') ? $this->config->get('uni_request') : [];
		
		if ($settings) {
			switch ($data['reason']) {
				case $settings['heading_notify'][$lang_id]:
					$data['show_phone'] = isset($settings['notify_phone']) ? true : false;
					$data['show_email'] = isset($settings['notify_email']) ? true : false;
					$data['show_comment'] = false;
					break;
				case $settings['heading_question'][$lang_id]:
					$data['show_phone'] = isset($settings['question_phone']) ? true : false;
					$data['show_email'] = isset($settings['question_email']) ? true : false;
					$data['show_comment'] = true;
					break;
				default:
					$data['show_phone'] = $show_phone;
					$data['show_email'] = $show_mail;
					$data['show_comment'] = $show_comment;
					break;
			}
		} else {
			$data['show_phone'] = $show_phone;
			$data['show_email'] = $show_mail;
			$data['show_comment'] = $show_comment;
		}
			
		$data['mask_telephone'] = isset($uniset['callback']['mask']['telephone'][$lang_id]) ? $uniset['callback']['mask']['telephone'][$lang_id] : '';
		
		$data['show_reason1'] = isset($uniset['show_reason1']) ? true : false;
		$data['text_reason1'] = $uniset[$lang_id]['text_reason1'];
		$data['show_reason2'] = isset($uniset['show_reason2']) ? true : false;
		$data['text_reason2'] = $uniset[$lang_id]['text_reason2'];
		$data['show_reason3'] = isset($uniset['show_reason3']) ? true : false;
		$data['text_reason3'] = $uniset[$lang_id]['text_reason3'];
		
		if (isset($uniset['callback']['captcha']) && $this->config->get('captcha_'.$this->config->get('config_captcha').'_status')) {
			$data['captcha'] = $this->load->controller('extension/captcha/'.$this->config->get('config_captcha'));
		} else {
			$data['captcha'] = '';
		}
		
		if ($this->config->get('config_account_id') && isset($uniset['callback_confirm'])) {
			$this->load->model('catalog/information');
			
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
			$data['text_agree'] = $information_info ? sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_account_id'), true), $information_info['title'], $information_info['title']) : '';
		} else {
			$data['text_agree'] = '';
		}
	
		$this->response->setOutput($this->load->view('extension/module/uni_request_form', $data));
  	}
	
	public function requests() {	
		$uniset = $this->config->get('config_unishop2');
		$settings = $this->config->get('uni_request') ? $this->config->get('uni_request') : [];
		
		$product_id = isset($this->request->get['p_id']) ? (int)$this->request->get['p_id'] : 0;
		
		if($settings && isset($settings['question_list']) && $product_id) { 
			$this->load->model('extension/module/uni_request');
			
			$this->load->language('extension/module/uni_othertext');
			$this->load->language('extension/module/uni_request');
			$this->load->language('product/product');
			$this->load->language('product/review');
			$this->load->language('account/register');
		
			$lang_id = $this->config->get('config_language_id');
			
			$data['customer_firstname'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			$data['customer_email'] = $this->customer->getEmail();
			$data['customer_telephone'] = $this->customer->getTelephone();
		
			$data['type'] = $settings['heading_question'][$lang_id];
			$data['show_phone'] = isset($settings['question_phone']) ? true : false;
			$data['show_email'] = isset($settings['question_email']) ? true : false;
			$data['show_email_required'] = isset($settings['question_email_required']) ? true : false;
			
			if (isset($settings['question_captcha']) && $this->config->get('captcha_'.$this->config->get('config_captcha').'_status')) {
				$data['captcha'] = $this->load->controller('extension/captcha/'.$this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}
		
			if ($this->config->get('config_account_id') && isset($uniset['callback_confirm'])) {
				$this->load->model('catalog/information');
			
				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
				$data['text_agree'] = $information_info ? sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_account_id'), true), $information_info['title'], $information_info['title']) : '';
			} else {
				$data['text_agree'] = '';
			}
		
			$this->customer->isLogged();
		
			if (isset($this->request->get['page'])) {
				$page = (int)$this->request->get['page'];
			} else {
				$page = 1;
			}
		
			$limit = 5;
	
			$data['requests'] = [];
			$data['request_guest'] = 1;
			$data['product_id'] = $product_id;
	
			$filter_data = array(
				'product_id' 	=> $product_id,
				'start' 		=> ($page - 1) * $limit,
				'limit'         => $limit,
			);
	
			$results = $this->model_extension_module_uni_request->getRequests($filter_data);
			
			$data['requests_total'] = $results_total = $this->model_extension_module_uni_request->getTotalRequests($filter_data);
				
			if($results) {
				foreach ($results as $result) {
					$data['requests'][] = array(
						'name' 			=> $result['name'],
						'date_added' 	=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
						'comment' 		=> nl2br($result['comment']),
						'admin_comment' => nl2br($result['admin_comment']),
					);
				}
			}
			
			$data['text_question_total'] = sprintf($this->language->get('text_question_total'), $results_total);
		
			$pagination = new Pagination();
			$pagination->total = $results_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('extension/module/uni_request/requests', 'p_id=' . $product_id . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($results_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($results_total - 5)) ? $results_total : ((($page - 1) * 5) + 5), $results_total, ceil($results_total / 5));
		
			$this->response->setOutput($this->load->view('extension/module/uni_request_list', $data));
		} else {
			$this->load->language('extension/module/uni_request');
			
			$this->document->setTitle($this->language->get('text_error'));
			
	     	$data['breadcrumbs'][] = array(
	        	'href'      => $this->url->link('information/news'),
	        	'text'      => $this->language->get('text_error'),
	        	'separator' => $this->language->get('text_separator')
	     	);
		
			$data['continue'] = $this->url->link('common/home');
			
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
				
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
				
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	
	public function mail() {
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('extension/module/uni_request');
		$this->load->language('account/register');
		
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		$settings = $this->config->get('uni_request') ? $this->config->get('uni_request') : [];
		
		$type = isset($this->request->post['type']) ? htmlspecialchars(strip_tags($this->request->post['type'])) : '';
		$type = isset($this->request->post['reason']) ? htmlspecialchars(strip_tags($this->request->post['reason'])) : $type;
		
		$product_id = 0;
		
		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
			$this->load->model('catalog/product');
			$product_info = $this->model_catalog_product->getProduct($product_id);
		}
		
		$customer_name = isset($this->request->post['name']) ? htmlspecialchars(strip_tags($this->request->post['name'])) : '';
		$customer_phone = isset($this->request->post['phone']) ? htmlspecialchars(strip_tags($this->request->post['phone'])) : '';
		$customer_mail = isset($this->request->post['mail']) ? htmlspecialchars(strip_tags($this->request->post['mail'])) : '';
		$customer_comment = isset($this->request->post['comment']) ? htmlspecialchars(strip_tags($this->request->post['comment'])) : ' ';
		$product_id = isset($this->request->post['product_id']) ? (int)$this->request->post['product_id'] : '';
		$location = isset($this->request->server['HTTP_REFERER']) ? html_entity_decode(strip_tags(trim($this->request->server['HTTP_REFERER']))) : '';
		
		$json = [];
		
		if (utf8_strlen($customer_name) < 3 || utf8_strlen($customer_name) > 45) {
			$json['error']['name'] = $this->language->get('text_error_name');
		}
		
		if (isset($this->request->post['phone']) && (utf8_strlen($customer_phone) < 3 || utf8_strlen($customer_phone) > 25 || strpos($customer_phone, '_'))) {
			$json['error']['phone'] = $this->language->get('text_error_phone');
		}
		
		$notify_email_required = isset($settings['notify_email_required']) ? true : false;
		$heading_notify = isset($settings['heading_notify'][$lang_id]) ? $settings['heading_notify'][$lang_id] : '';
		$question_email_required = isset($settings['question_email_required']) ? true : false;
		$heading_question = isset($settings['heading_question'][$lang_id]) ? $settings['heading_question'][$lang_id] : '';
		
		$mail_reqired = true;
		
		if ($heading_notify == $type && !$notify_email_required) {
			$mail_reqired = false;
		} else if ($heading_question == $type && !$question_email_required) {
			$mail_reqired = false;
		}
		
		if($mail_reqired) {
			if (isset($this->request->post['mail']) && ((utf8_strlen($customer_mail) > 50) || !filter_var($customer_mail, FILTER_VALIDATE_EMAIL))) {
				$json['error']['mail'] = $this->language->get('text_error_mail');
			}
		}

		if (isset($this->request->post['comment']) && ((utf8_strlen($customer_comment) < 5 || utf8_strlen($customer_comment) > 300))) {
			$json['error']['comment'] = $this->language->get('text_error_comment');
		}
		
		$form_name = isset($this->request->post['form-name']) ? trim($this->request->post['form-name']) : '';
		$form_arr = ['callback', 'question'];
		
		if(!in_array($form_name, $form_arr)) {
			$json['error']['form'] = 'Unknown form';
		}
		
		if(((isset($settings['question_captcha']) && $form_name == 'question') || (isset($uniset['callback']['captcha']) && $form_name == 'callback')) && $this->config->get('captcha_'.$this->config->get('config_captcha').'_status')) {
			$captcha = $this->load->controller('extension/captcha/'.$this->config->get('config_captcha').'/validate');

			if ($captcha) {
				$json['error']['captcha'] = $captcha;
			}
		}
		
		if(isset($uniset['callback_confirm'])) {
			if ($this->config->get('config_account_id')) {
				$this->load->model('catalog/information');
				
				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
					
				if ($information_info && !isset($this->request->post['confirm'])) {
						$json['error']['confirm'] = sprintf($this->language->get('error_agree'), $information_info['title']);
				}
			}
		}
		
		if(!isset($json['error'])) {
			
			$product_name = strip_tags(html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8'));
			
			$text = $product_id ? $this->language->get('text_product').$product_name.'<br />' : '';
			$text .= $this->language->get('text_name').$customer_name.'<br />';
			$text .= $this->language->get('text_phone').$customer_phone.'<br />';
			$text .= $this->language->get('text_mail').$customer_mail.'<br />';
			$text .= $this->language->get('text_comment').$customer_comment.'<br />';
			$text .= $this->language->get('text_location').$location.'<br />';
		
			$subject = $type && $product_id ? sprintf($this->language->get('text_reason'), $type, $product_name) : sprintf($this->language->get('text_reason2'), $type);
			
			$this->load->model('setting/setting');
		
			$from = $this->model_setting_setting->getSettingValue('config_email', $store_id);
		
			if (!$from) {
				$from = $this->config->get('config_email');
			}
			
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($from);
			
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setHtml($text);
			$mail->send();
			
			$emails = explode(',', $this->config->get('config_mail_alert_email'));
			
			foreach ($emails as $email) {
				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
			
			$request_data = array(
				'type' 			=> $type,
				'name'			=> $customer_name,
				'phone'			=> $customer_phone,
				'mail'			=> $customer_mail,
				'comment'		=> $customer_comment,
				'product_id'	=> $product_id,
				'status'		=> '1',
			); 
			
			if ($this->config->get('uni_request')) {
				$this->load->model('extension/module/uni_request');
				$this->model_extension_module_uni_request->addRequest($request_data);
			}
				
			$json['success'] = (isset($settings['heading_question'][$lang_id]) && $settings['heading_question'][$lang_id] == $type) ? $this->language->get('text_success2') : $this->language->get('text_success');
		}
		
		$this->response->setOutput(json_encode($json));
	}
}
?>