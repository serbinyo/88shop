<?php 
class ControllerExtensionModuleUniSubscribe extends Controller {
	public function index() {
		$this->load->language('extension/module/uni_subscribe');
		
		$uniset = $this->config->get('config_unishop2');
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/subscribe.css');
		$this->document->addScript('catalog/view/theme/unishop2/js/subscribe.js');
		
		$points = isset($uniset['subscribe_points']) ? $uniset['subscribe_points'] : 0;
		
		$data['text_subscribe_info'] = $points ? sprintf($this->language->get('text_subscribe_info_points'), $points) : $this->language->get('text_subscribe_info');
		$data['customer_email'] = $this->customer->getEmail();
		
		$data['text_subscribe_agree'] = '';
		
		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');
			
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
			$data['text_subscribe_agree'] = $information_info ? sprintf($this->language->get('text_subscribe_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_account_id'), true), $information_info['title'], $information_info['title']) : '';
		}
		
		$newsletter = $this->customer->getNewsletter();
		
		if(!$newsletter) {
			return $this->load->view('extension/module/uni_subscribe', $data);
		} else {
			return '';
		}
	}
	
	public function add() {
		$uniset = $this->config->get('config_unishop2');
		
		if(!isset($uniset['show_subscribe'])) {
			return false;
		}
		
		$this->load->language('account/register');
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('extension/module/uni_subscribe');
		
		$this->load->model('account/customer');
		$this->load->model('extension/module/uni_subscribe');
		
		$customer_mail = isset($this->request->post['email']) ? htmlspecialchars(strip_tags($this->request->post['email'])) : '';
		$customer_password = isset($this->request->post['password']) ? html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8') : '';
		$customer_ip = $this->request->server['REMOTE_ADDR'];
		
		$link = $this->url->link('account/newsletter', '', true);
		$attempts = isset($uniset['subscribe_attempt']) ? $uniset['subscribe_attempt'] : 3;
		
		$points = isset($uniset['subscribe_points']) ? $uniset['subscribe_points'] : 0;
		$points_description = $this->language->get('text_points_description');
		
		$json = [];
		
		if ((utf8_strlen($customer_mail) > 96) || !filter_var($customer_mail, FILTER_VALIDATE_EMAIL)) {
			$json['error']['email'] = $this->language->get('error_email');
		}
		
		if (isset($this->request->post['password'])) {
			if(!$this->customer->login($customer_mail, $customer_password)) {
				$json['error']['pass'] = $this->language->get('error_password');
			}
		}
		
		if ($this->config->get('config_account_id')) {
			$this->load->model('catalog/information');
				
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
					
			if ($information_info && !isset($this->request->post['confirm'])) {
				$json['error']['agree'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}
	
		if (!$this->customer->isLogged() && $this->model_account_customer->getTotalCustomersByEmail($customer_mail)) {
			$json['alert'] = true;
		}
		
		$attempts_info = $this->model_extension_module_uni_subscribe->getAttempts($customer_ip);
		
		if ($attempts_info && ($attempts_info['total'] >= $attempts) && strtotime('-1 hour') < strtotime($attempts_info['date_modified'])) {
			$json['error'] = $this->language->get('error_limit');
		}
		
		$reward_info = '';
		
		if (!$json) {
			if($this->customer->isLogged()) {	
				$customer_id = $this->customer->isLogged();
				
				$customer_info = $this->model_account_customer->getCustomerByEmail($customer_mail);

				if ($customer_info && $customer_info['status'] && ($customer_id == $customer_info['customer_id'])) {
					$this->model_extension_module_uni_subscribe->editSubscribe($customer_id, true);		

					$reward_info = $this->model_extension_module_uni_subscribe->getRewards($customer_id, $points_description, $points);	
					
					if($points && !$reward_info) {
						$json['success'] = sprintf($this->language->get('success_customer_subscribe_points'), $this->customer->getFirstName(), $points);
					} else {
						$json['success'] = sprintf($this->language->get('success_customer_subscribe'), $this->customer->getFirstName());
					}
				} else {
					$json['error'] = $this->language->get('error_customer_mail');
				}	
			} else {
				$chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP'; 
				$pass_length = 8; 
				$chars_length = utf8_strlen($chars)-1; 
				$password = ''; 

				while($pass_length--) {
					$password .= $chars[rand(0, $chars_length)];
				}
				
				$data['firstname'] = $customer_mail;
				$data['lastname'] = '';
				$data['email'] = $customer_mail;
				$data['telephone'] = '';
				$data['password'] = $password;
				$data['customer_group_id'] = $this->config->get('config_customer_group_id');
				$data['fax'] = '';
				$data['newsletter'] = true;
				$data['company'] = '';
				$data['address_1'] = '';
				$data['address_2'] = '';
				$data['postcode'] = '';
				$data['city'] = '';
				$data['country_id'] = $this->config->get('config_country_id') ? $this->config->get('config_country_id') : 0;
				$data['zone_id'] = $this->config->get('config_zone_id') ? $this->config->get('config_zone_id') : 0;	
		
				$subject = sprintf($this->language->get('text_subscribe_mail_subject'), $this->config->get('config_name'));
				
				if($points) {
					$this->session->data['uni_subscribe_text'] = sprintf($this->language->get('text_subscribe_mail_body_points'), $points);
				} else {
					$this->session->data['uni_subscribe_text'] = $this->language->get('text_subscribe_mail_body');
				}
				
				$this->session->data['uni_add_customer'] = array(
					'email' 	=> sprintf($this->language->get('text_new_user_email'), $customer_mail),
					'password'  => sprintf($this->language->get('text_new_user_password'), $password)
				);
				
				$customer_id = $this->model_account_customer->addCustomer($data);
				
				unset($this->session->data['uni_add_customer']);
				unset($this->session->data['uni_subscribe_text']);
				
				if($points) {
					$json['success'] = sprintf($this->language->get('success_guest_subscribe_points'), $points);
				} else {
					$json['success'] = $this->language->get('success_guest_subscribe');
				}
			}
			
			$json['success_title'] = $this->language->get('success_title');
			
			if(!isset($json['error']) && !$reward_info && $points) {
				$this->model_extension_module_uni_subscribe->addReward($customer_id, $points_description, $points);
			}
			
			$this->model_extension_module_uni_subscribe->addAttempt($customer_mail, $customer_ip);
		}	
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
}
?>