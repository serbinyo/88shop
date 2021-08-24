<?php  
class ControllerExtensionModuleUniNewData extends Controller {
	private $uniset = [];
	
	public function index($data = []) {
		$type = isset($data['type']) ? $data['type'] : '';
		
		$start = microtime(true); 
		
		$this->uniset = $this->config->get('config_unishop2');
		
		switch($type) {
			case 'header':
				$result = $this->getHeaderData();
				break;
			case 'footer':
				$result = $this->getFooterData();
				break;
			case 'menu':
				$result = $this->getMenuData();
				break;
			case 'catalog':
				$result = $this->getCatalogData();
				break;
			case 'search':
				$result = $this->getSearchData($data);
				break;
			case 'product':
				$result = $this->getProductData($data);
				break;
			case 'cart':
				$result = $this->getCartData($data);
				break;
			case 'contact':
				$result = $this->getContactData();
				break;
			default:
				$result = [];
		}
		
		$finish = microtime(true);
		
		//echo 'Время выполнения: '.$type.' '.round(($finish - $start), 4).' сек.<br />';
		
		return $result;
	}
	
	private function getHeaderData() {
		$this->load->language('extension/module/uni_othertext');
		
		$data['shop_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
		$data['customer_name'] = $this->customer->getFirstName();
			
		$uniset = $this->uniset;
		$lang_id = $this->config->get('config_language_id');
		
		$data = array_merge($data, $this->load->controller('extension/module/uni_tool'));
			
		$data['theme_color'] = ($uniset['menu_type'] == 1) ? $uniset['main_menu_bg'] : $uniset['main_menu2_bg'];
		$data['default_view'] = isset($uniset['default_view']) ? $uniset['default_view'] : 'grid';
		$data['default_mobile_view'] = isset($uniset['default_mobile_view']) ? $uniset['default_mobile_view'] : 'grid';
		$data['module_on_mobile'] = isset($uniset['catalog']['module_type_mobile']) ? 'carousel' : 'grid';
		$data['user_js'] = isset($uniset['user_js']) ? html_entity_decode($uniset['user_js'], ENT_QUOTES, 'UTF-8') : '';
		
		$data['popup_login'] = isset($uniset['login_form']['popup']) ? true : false;
		$data['popup_register'] = isset($uniset['register_form']['popup']) ? true : false;
		
		$data['transaction_link'] = isset($uniset['account_page']['hide_transaction']) ? false : true;
		$data['download_link'] = isset($uniset['account_page']['hide_download']) ? false : true;
		
		if(isset($uniset['register_form']['page'])) {
			$data['register'] = $this->url->link('extension/module/uni_login_register/page', '', true);
		}
		
		$data['headerlinks'] = isset($uniset[$lang_id]['headerlinks']) ? $uniset[$lang_id]['headerlinks'] : [];
		$data['callback'] = isset($uniset['show_callback']) ? true : false;
		$data['search_phone_change'] = isset($uniset['search_phone_change']) ? true : false;
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$logo_size = getimagesize(DIR_IMAGE . $this->config->get('config_logo'));
			
			$data['logo_width'] = isset($logo_size[0]) ? $logo_size[0] : 180;
			$data['logo_height'] = isset($logo_size[1]) ? $logo_size[1] : 45;
		}
			
		$data['contacts'] = [
			'main'   => [],
			'addit'  => [],
			'second' => []
		];
		
		$contacts_main = isset($uniset['header']['contacts']['main']) ? $uniset['header']['contacts']['main'] : [];
			
		if($contacts_main) {
			
			$contact = [];
			
			foreach($contacts_main as $key => $contact) {
					
				$href = '';
				
				$number = isset($contact['number'][$lang_id]) ? str_replace([' ', '(', ')', '-'], '', $contact['number'][$lang_id]) : '';
				
				if($number) {
					$type = $contact['type'][$lang_id];
					
					if($type != '') {
						if($type == '?call' || $type == '?chat') {
							$href = 'skype:'.$number.$type;
						} else if($type == 'viber://chat?number=') {
							$href = str_replace('+', '%2B', $type.$number);
						} else {
							$href = $type.$number;
						}
					}
				
					$data['contacts']['main'][] = [
						'text'		=> $contact['text'][$lang_id],
						'href'		=> $href,
						'number'	=> $contact['number'][$lang_id],
						'icon' 		=> $contact['icon'][$lang_id],
					];
				
					if($key == 1 || isset($contact['is_second'][$lang_id])) {
						if(isset($contact['is_second'][$lang_id])) {
							$data['contacts']['second'] = [
								'href'		=> $href,
								'number'	=> $contact['number'][$lang_id]
							];
						}
					} else {
						if(!$contact['icon'][$lang_id] && substr($number, 0, 1) == '+') {
							$contact['icon'][$lang_id] = 'fas fa-phone-alt';
						}
					
						$data['contacts']['addit'][] = [
							'href'		=> $href,
							'number'	=> $contact['number'][$lang_id],
							'icon' 		=> $contact['icon'][$lang_id],
							'main'		=> true
						];
					}
				}
			}
		}
		
		$contacts_addit = isset($uniset['header']['contacts']['addit']) ? $uniset['header']['contacts']['addit'] : [];
			
		if($contacts_addit) {
			
			$contact = [];
			
			foreach($contacts_addit as $key => $contact) {	
								
				$href = '';
				
				$number = isset($contact['number'][$lang_id]) ? str_replace([' ', '(', ')', '-'], '', $contact['number'][$lang_id]) : '';
				
				if($number) {
					$type = $contact['type'][$lang_id];
					
					if($type != '') {
						if($type == '?call' || $type == '?chat') {
							$href = 'skype:'.$number.$type;
						} else if($type == 'viber://chat?number=') {
							$href = str_replace('+', '%2B', $type.$number);
						} else {
							$href = $type.$number;
						}
					}
			
					$data['contacts']['addit'][] = [
						'href'		=> $href,
						'number'	=> $contact['number'][$lang_id],
						'icon' 		=> $contact['icon'][$lang_id],
						'addit'		=> true
					];
				}
			}
		}
			
		$data['text_in_add_contacts'] = isset($uniset[$lang_id]['text_in_add_contacts']) ? html_entity_decode($uniset[$lang_id]['text_in_add_contacts'], ENT_QUOTES, 'UTF-8') : '';
		$data['text_in_add_contacts_position'] = isset($uniset['text_in_add_contacts_position']) ? true : false;
		
		$data['wishlist'] = [];
		$data['compare'] = [];
		
		if(!isset($uniset['wishlist']['disabled'])) {
			if($this->customer->isLogged() && isset($this->session->data['wishlist'])) {
				$wishlist_products = $this->session->data['wishlist'];
			} else {
				$wishlist_products = [];
			}
			
			$data['wishlist'] = [
				'total' 	=> count($wishlist_products),
				'products'	=> implode(',', $wishlist_products),
				'text'		=> $this->language->get('text_topmenu_wishlist'),
				'href'		=> $this->url->link('account/wishlist', '', true)
			];
		}
		
		if(!isset($uniset['compare']['disabled'])) {
			if(isset($this->session->data['compare'])){
				$compare_products = $this->session->data['compare'];
			} else {
				$compare_products = [];
			}
		
			$data['compare'] = [
				'total' 	=> count($compare_products),
				'products'	=> implode(',', $compare_products),
				'text'		=> $this->language->get('text_topmenu_compare'),
				'href'		=> $this->url->link('product/compare', '', true)
			];
		}

		return $data;
	}
	
	private function getFooterData() {
		$this->load->language('extension/module/uni_othertext');
			
		$uniset = $this->uniset;
		$lang_id = $this->config->get('config_language_id');
		$currency = $this->session->data['currency'];
		
		$this_route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
		
		$dir_template = 'catalog/view/theme/unishop2/';
		$dir_style = $dir_template.'stylesheet/';
		$dir_script = $dir_template.'js/';
		
		$search_phrase_arr = [];
		
		if(isset($uniset[$lang_id]['search_phrase']) && trim($uniset[$lang_id]['search_phrase']) != '') {
			$search_phrase_arr = explode(',', trim($uniset[$lang_id]['search_phrase']));
			shuffle($search_phrase_arr);
			$this->document->addScript($dir_script.'typed.min.js');
		}
		
		if($this_route == 'product/category') {
			if($this->config->get('module_filter_status')) {
				$this->document->addStyle($dir_style.'default-filter.css');
			}
		
			if($this->config->get('module_ocfilter_status')) {
				$this->document->addStyle($dir_style.'ocfilter-filter.css');
			}
			
			//if($this->config->get('module_ocfilter_status')) {
				$this->document->addStyle($dir_style.'mfp-filter.css');
			//}
		}
		
		if($this_route == 'product/product') {
			$this->document->addStyle($dir_style.'product-page.css');
		}
		
		if($this_route == 'product/compare') {
			$this->document->addStyle($dir_style.'compare.css');
		}
		
		if(substr($this_route, 0, 7) == 'account') {
			$this->document->addStyle($dir_style.'account.css');
		}
		
		if($this_route == 'information/contact') {
			$this->document->addStyle($dir_style.'contact-page.css');
		}
		
		if(isset($uniset['catalog']['description_hover']) || isset($uniset['catalog']['attr_hover']) || isset($uniset['catalog']['option_hover'])) {
			$this->document->addScript($dir_script.'thumb-hover.js');
		}
		
		if(isset($uniset['catalog']['addit_img']) && $uniset['catalog']['addit_img'] != 'disabled') {
			$this->document->addScript($dir_script.'addit-img.js');
		}
			
		if(isset($uniset['livesearch']['enabled'])) {
			$this->document->addStyle($dir_style.'livesearch.css');
			$this->document->addScript($dir_script.'live-search.js');
		}
			
		if(isset($uniset['show_callback']) || isset($uniset['show_fly_callback']) || $this->config->get('uni_request')) {
			$this->document->addScript($dir_script.'user-request.js');
		}
			
		if(isset($uniset['liveprice'])) {
			$this->document->addScript($dir_script.'live-price.js');
		}
			
		if(isset($uniset['fly_menu']['desktop']) || isset($uniset['fly_menu']['mobile']) || isset($uniset['show_fly_cart'])) {
			$this->document->addStyle($dir_style.'flymenu.css');
			$this->document->addScript($dir_script.'fly-menu-cart.js');
		}
			
		if(isset($uniset['show_quick_order'])) {
			$this->document->addScript($dir_script.'quick-order.js');
		}
			
		if(isset($uniset['login_form']['popup']) || isset($uniset['register_form']['popup'])) {
			$this->document->addScript($dir_script.'login-register.js');
		}
			
		$uni_routes = [
			'product/uni_reviews',
			'product/category',
			'product/special',
			'product/search',
			'product/manufacturer/info',
		];
			
		if((isset($uniset['button_showmore']) || isset($uniset['ajax_pagination'])) && in_array($this_route, $uni_routes)) {
			$this->document->addScript($dir_script.'showmore-ajaxpagination.js');
		}
			
		$data['show_fly_callback'] = isset($uniset['show_fly_callback']) ? true : false;
		$data['fly_callback_text'] = isset($uniset['show_fly_callback']) ? $uniset[$lang_id]['fly_callback_text'] : '';
	
		$data['subscribe'] = isset($uniset['show_subscribe']) ? $this->load->controller('extension/module/uni_subscribe') : '';
						
		$data['footer_column'] = isset($uniset[$lang_id]['footer_column']) ? $uniset[$lang_id]['footer_column'] : [];
		
		$footerlinks = isset($uniset[$lang_id]['footerlinks']) ? $uniset[$lang_id]['footerlinks'] : [];
		
		$data['footerlinks'] = [];
		
		foreach($footerlinks as $footerlink) {
			$data['footerlinks'][$footerlink['column']][] = [
				'title'	=> $footerlink['title'],
				'link'	=> $footerlink['link']
			];
		}
		
		$data['footer_text'] = isset($uniset[$lang_id]['footer_text']) ? html_entity_decode($uniset[$lang_id]['footer_text'], ENT_QUOTES, 'UTF-8') : '';
		
		$data['footer_address'] = nl2br($this->config->get('config_address'));
		$data['footer_open'] = nl2br($this->config->get('config_open'));
		$data['footer_mail'] = $this->config->get('config_email');
		
		$data['footer_phone'] = [];
		
		$contacts = isset($uniset['header']['contacts']['main']) ? $uniset['header']['contacts']['main'] : [];
			
		if($contacts) {
			
			$contact = [];
			
			foreach($contacts as $key => $contact) {
				if($key == 1 || isset($contact['is_second'][$lang_id])) {
				
					$number = isset($contact['number'][$lang_id]) ? str_replace([' ', '(', ')', '-'], '', $contact['number'][$lang_id]) : '';
				
					if($number) {
						$href = '';
						$type = $contact['type'][$lang_id];
					
						if($type != '') {
							if($type == '?call' || $type == '?chat') {
								$href = 'skype:'.$number.$type;
							} else if($type == 'viber://chat?number=') {
								$href = str_replace('+', '%2B', $type.$number);
							} else {
								$href = $type.$number;
							}
						}

						if(!$contact['icon'][$lang_id] && substr($number, 0, 1) == '+') {
							$contact['icon'][$lang_id] = 'fas fa-phone-alt fa-fw';
						}
						
						$data['footer_phone'][] = [
							'href'		=> $href,
							'number'	=> $contact['number'][$lang_id],
							'icon' 		=> $contact['icon'][$lang_id]
						];
					}
				}		
			}
		}
		
		if(!$data['footer_phone']) {
			$data['footer_phone'] = [
				'href' 		=> str_replace([' ', '(', ')', '-'], '', $this->config->get('config_telephone')),
				'number'	=> nl2br($this->config->get('config_telephone')),
				'icon' 		=> 'fas fa-phone-alt fa-fw'
			];
		}
			
		$data['socials'] = isset($uniset['socials']) ? $uniset['socials'] : [];
		$data['payment_icons'] = isset($uniset['payment_icons']) ? $uniset['payment_icons'] : [];
		
		if(isset($uniset['payment_icons_custom'])) {
			foreach($uniset['payment_icons_custom'] as $icon) {
				if($icon != '') {
					$data['payment_icons'][] = $icon;
				}
			}
		}
		
		$data['wishlist'] = [];
		
		if($this->customer->isLogged()) {
			$this->load->model('account/wishlist');
			
			$this->session->data['wishlist'] = [];
			
			$wishlist = $this->model_account_wishlist->getWishlist();
			
			foreach($wishlist as $result) {
				$this->session->data['wishlist'][] = $result['product_id'];
			}
			
			$this->session->data['wishlist'] = array_unique($this->session->data['wishlist']);
		}
		
		if(!isset($uniset['wishlist']['disabled']) && isset($uniset['wishlist']['fly_btn'])) {
			$data['wishlist'] = [
				'total' 	=> ($this->customer->isLogged() && isset($this->session->data['wishlist'])) ? count($this->session->data['wishlist']) : 0,
				'href'		=> $this->url->link('account/wishlist', '', true)
			];
		}
		
		$data['compare'] = [];
		
		if(!isset($uniset['compare']['disabled']) && isset($uniset['compare']['fly_btn'])) {
			$data['compare'] = [
				'total' 	=> isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0,
				'href'		=> $this->url->link('product/compare', '', true)
			];
		}
		
		$data['topstripe'] = isset($uniset['topstripe']['status']) ? $this->load->controller('extension/module/uni_topstripe') : '';
		$data['pwa_notification'] = isset($uniset['pwa']['status']) ? $this->load->controller('extension/module/uni_pwa') : '';
		$data['notification'] = isset($uniset['notification']['status']) ? $this->load->controller('extension/module/uni_notification') : '';
		
		if(isset($uniset['user_js_delayed']['code']) && $uniset['user_js_delayed']['code'] != '') {
			$scripts = preg_replace('~\r?\n~', "\n", $uniset['user_js_delayed']['code']);

			$data['scripts_delayed'] = explode("\n", $scripts);
			$data['scripts_delayed_time'] = $uniset['user_js_delayed']['time'];
		} else {
			$data['scripts_delayed'] = [];
		}
		
		$uni_request = $this->config->get('uni_request') ? $this->config->get('uni_request') : [];
		
		$js_vars = [
			'menu_blur' 				=> isset($uniset['main_menu_blur']) ? $uniset['main_menu_blur'] : false,
			'search_phrase_arr'			=> $search_phrase_arr,
			'change_opt_img' 			=> isset($uniset['change_opt_img']) ? true : false,
			'addit_image' 				=> isset($uniset['catalog']['addit_img']) ? true : false,
			'ajax_pagination' 			=> isset($uniset['ajax_pagination']) ? true : false,
			'showmore' 					=> isset($uniset['button_showmore']) ? true : false,
			'showmore_text' 			=> $this->language->get('button_show_more'),
			'modal_cart'				=> [
				'text_heading'		=> $this->language->get('text_modal_heading'),								
			],
			'cart_popup_disable' 		=> isset($uniset['cart_popup_disable']) ? true : false,
			'cart_popup_autohide' 		=> isset($uniset['cart_popup_autohide']) ? true : false,
			'cart_popup_autohide_time' 	=> isset($uniset['cart_popup_autohide_time']) ? $uniset['cart_popup_autohide_time'] : 5,
			'notify'					=> isset($uni_request['heading_notify'][$lang_id]) ? true : false,
			'notify_text' 				=> isset($uni_request['heading_notify'][$lang_id]) ? $uni_request['heading_notify'][$lang_id] : '',
			'popup_effect_in' 			=> 'fade animated '.(isset($uniset['popup_effect_in']) && $uniset['popup_effect_in'] != 'disabled' ? $uniset['popup_effect_in'] : 'disabled'),
			'popup_effect_out' 			=> 'fade animated '.(isset($uniset['popup_effect_out']) && $uniset['popup_effect_out'] != 'disabled' ? $uniset['popup_effect_out'] : 'disabled'),
			'alert_effect_in' 			=> isset($uniset['alert']['effect']['in']) && $uniset['alert']['effect']['in'] != 'disabled' ? 'animated '.$uniset['alert']['effect']['in'] : '',
			'alert_effect_out' 			=> isset($uniset['alert']['effect']['out']) && $uniset['alert']['effect']['out'] != 'disabled' ? 'animated '.$uniset['alert']['effect']['out'] : '',
			'alert_time' 				=> isset($uniset['alert']['time']) ? $uniset['alert']['time'] : 5,
			'fly_menu'					=> [
				'desktop' 			=> isset($uniset['fly_menu']['desktop']) ? true : false,
				'mobile' 			=> isset($uniset['fly_menu']['mobile']) ? $uniset['fly_menu']['mobile'] : false,
				'product' 			=> isset($uniset['fly_menu']['product']) ? true : false,
				'wishlist'			=> !isset($uniset['wishlist']['disabled']) ? true : false,
				'compare'			=> !isset($uniset['compare']['disabled']) ? true : false,
			],
			'fly_cart' 					=> isset($uniset['show_fly_cart']) ? true : false,
			'descr_hover'				=> isset($uniset['catalog']['description_hover']) ? true : false,
			'attr_hover'				=> isset($uniset['catalog']['attr_hover']) ? true : false,
			'option_hover'				=> isset($uniset['catalog']['option_hover']) ? true : false,
			'qty_switch_step'			=> isset($uniset['qty_switch']['step']) ? true : false,
			'pwa'						=> [
				'text_reload'	   => $this->language->get('text_pwa_reload'),
				'text_online'      => $this->language->get('text_pwa_online'),
				'text_offline'     => $this->language->get('text_pwa_offline')	
			],
			'currency'					=> [
				'code'			   => $currency,
				'symbol_l' 		   => $this->currency->getSymbolLeft($currency),
				'symbol_r' 		   => $this->currency->getSymbolRight($currency),
				'decimal' 		   => $this->currency->getDecimalPlace($currency),
				'decimal_p' 	   => $this->language->get('decimal_point'),
				'thousand_p' 	   => $this->language->get('thousand_point'),
			],
			'callback'					=> [
				'metric_id'		   => isset($uniset['callback_metric_id']) ? $uniset['callback_metric_id'] : 0,
				'metric_target'	   => isset($uniset['callback_metric_target']) ? $uniset['callback_metric_target'] : '',
				'analytic_category'=> isset($uniset['callback_analityc_category']) ? $uniset['callback_analityc_category'] : '',
				'analytic_action'  => isset($uniset['callback_analityc_action']) ? $uniset['callback_analityc_action'] : '',
			],
			'quick_order' 				=> [
				'metric_id' 	   => isset($uniset['quick_order']['metric_id']) ? $uniset['quick_order']['metric_id'] : 0,
				'metric_taget_id'  => isset($uniset['quick_order']['metric_target_id']) ? $uniset['quick_order']['metric_target_id'] : 0,
				'metric_target'    => isset($uniset['quick_order']['metric_target']) ? $uniset['quick_order']['metric_target'] : '',
				'analytic_category'=> isset($uniset['quick_order']['analytic_category']) ? $uniset['quick_order']['analytic_category'] : '',
				'analytic_action'  => isset($uniset['quick_order']['analytic_action']) ? $uniset['quick_order']['analytic_action'] : '',
			],
			'cart_btn'					=> [
				'icon'			   => isset($uniset[$lang_id]['cart_btn_icon']) ? $uniset[$lang_id]['cart_btn_icon'] : '',
				'text'			   => isset($uniset[$lang_id]['cart_btn_text']) ? $uniset[$lang_id]['cart_btn_text'] : '',
				'icon_incart' 	   => isset($uniset[$lang_id]['cart_btn_icon_incart']) ? $uniset[$lang_id]['cart_btn_icon_incart'] : '',
				'text_incart' 	   => isset($uniset[$lang_id]['cart_btn_text_incart']) ? $uniset[$lang_id]['cart_btn_text_incart'] : '',
				'metric_id'		   => isset($uniset['cart_btn']['metric_id']) ? $uniset['cart_btn']['metric_id'] : 0,
				'metric_target'	   => isset($uniset['cart_btn']['metric_target']) ? $uniset['cart_btn']['metric_target'] : '',
				'analytic_category'=> isset($uniset['cart_btn']['analytic_category']) ? $uniset['cart_btn']['analytic_category'] : '',
				'analytic_action'  => isset($uniset['cart_btn']['analytic_action']) ? $uniset['cart_btn']['analytic_action'] : '',
			],
			'wishlist_btn'				=> [
				'text'			   => $this->language->get('button_wishlist'),
				'text_remove'	   => $this->language->get('button_wishlist_remove'),
			],
			'compare_btn'				=> [
				'text'			   => $this->language->get('button_compare'),
				'text_remove'	   => $this->language->get('button_compare_remove')
			]
		];
		
		$data['js_vars'] = base64_encode(json_encode($js_vars));
		
		return $data;
	}
	
	private function getMenuData() {
		$uniset = $this->uniset;
		$lang_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
			
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
		$data['home'] = ($route == 'common/home') || !$route ? true : false;
		$menu_schema = isset($uniset['menu_schema']) && $uniset['menu_type'] == 1 ? $uniset['menu_schema'] : [];
			
		$data['menu_expanded'] = in_array($route, $menu_schema) || (!$route && in_array('common/home', $menu_schema)) ? true : false;
		$data['text_menu'] = isset($uniset[$lang_id]['text_menu']) ? $uniset[$lang_id]['text_menu'] : '';
		$data['menu_type'] = isset($uniset['menu_type']) ? $uniset['menu_type'] : 1;
		$data['show_title_on_mobile'] = isset($uniset['menu']['title']['show_on_mobile']) ? true : false; 
				
		$headerlinks2 = isset($uniset[$lang_id]['headerlinks2']) ? $uniset[$lang_id]['headerlinks2'] : [];
			
		$data['headerlinks2'] = $data['additional_link'] = [];
			
		if($headerlinks2) {
			
			if(count($headerlinks2) > 1) { 
				foreach ($headerlinks2 as $key => $value) {
					$sort[$key] = $value['sort_order'];
				}
			
				array_multisort($sort, SORT_ASC, $headerlinks2);
			}
			
			foreach($headerlinks2 as $key => $headerlink2) {
				$arr_name = isset($headerlink2['show_in_cat']) ? 'additional_link' : 'headerlinks2';
				
				$children_data = [];
						
				if(isset($headerlink2['children'])) {
					foreach ($headerlink2['children'] as $child) {
								
						$children2_data = [];
							
						if(isset($child['children'])) {
							foreach ($child['children'] as $child2) {
								$children2_data[] = [
									'name'  => $child2['name'],
									'href'  => $child2['href']
								];
							}
						}
							
						$children_data[] = [
							'name'  	=> $child['name'],
							'href'  	=> $child['href'],
							'disabled'	=> !$child['href'] ? true : false,
							'children'	=> $children2_data
						];
					}
				}
					
				$data[$arr_name][] = [
					'name' 		=> $headerlink2['title'],
					'icon'		=> isset($headerlink2['icon']) ? $headerlink2['icon'] : '',
					'children'	=> $children_data,
					'column'	=> isset($headerlink2['column']) ? $headerlink2['column'] : 1,
					'href'		=> $headerlink2['link'],
					'disabled'	=> !$headerlink2['link'] ? true : false,
					'separator'	=> $key == 0 ? true : false
				];
			}
		}
		
		return $data;
	}
	
	private function getCatalogData() {
		$uniset = $this->uniset;
		$lang_id = (int)$this->config->get('config_language_id');
		
		$this->load->language('extension/module/uni_othertext');
			
		$data['shop_name'] = $this->config->get('config_name');
		
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
		$menu_schema = isset($uniset['menu_schema']) ? $uniset['menu_schema'] : [];
		$data['menu_expanded'] = ($uniset['menu_type'] == 1 && in_array($route, $menu_schema)) ? true : false;
		$data['hide_last_breadcrumb'] = isset($uniset['breadcrumbs']['hide']['last']) ? true : false;
		
		$data['cat_desc_pos'] = $uniset['catalog']['cat_description']['position'];
		$data['cat_desc_height'] = $uniset['catalog']['cat_description']['height'] > 0 ? true : false;
		
		$data['subcategory_column'] = isset($uniset['catalog']['subcategory']['column']) ? implode(' ', $uniset['catalog']['subcategory']['column']) : '';
		$data['subcategory_mobile_view'] = isset($uniset['catalog']['subcategory']['mobile_view']) ? $uniset['catalog']['subcategory']['mobile_view'] : 'default';
		
		$data['category_list_img_width'] = $this->config->get('theme_'.$this->config->get('config_theme').'_image_category_width');
		$data['category_list_img_height'] = $this->config->get('theme_'.$this->config->get('config_theme').'_image_category_height');
		
		$data['show_grid_button'] = isset($uniset['show_grid_button']) ? true : false;
		$data['show_list_button'] = isset($uniset['show_list_button']) ? true : false;
		$data['show_compact_button'] = isset($uniset['show_compact_button']) ? true : false;
		
		if(isset($this->session->data['uni_default_view'])) {
			$data['default_view'] = $this->session->data['uni_default_view'];
		} else {
			$data['default_view'] = isset($uniset['default_view']) ? $uniset['default_view'] : 'grid';
		}
		
		if(isset($uniset['catalog']['limit']['status'])) {
			$new_limit = explode(',', $uniset['catalog']['limit']['value']);
			$limit = $new_limit[0] ? (int)$new_limit[0] : $limit;
	
			$this->config->set('theme_'.$this->config->get('config_theme').'_product_limit', $limit);
		}
		
		$data['show_quick_order_text'] = isset($uniset['show_quick_order_text']) ? $uniset['show_quick_order_text'] : '';			
		$data['quick_order_icon'] = isset($uniset['show_quick_order']) ? $uniset[$lang_id]['quick_order_icon'] : '';
		$data['quick_order_title'] = isset($uniset['show_quick_order']) ? $uniset[$lang_id]['quick_order_title'] : '';
		$data['show_rating'] = isset($uniset['show_rating']) ? true : false;
		$data['wishlist_btn_disabled'] = isset($uniset['wishlist']['disabled']) ? true : false;
		$data['compare_btn_disabled'] = isset($uniset['compare']['disabled']) ? true : false;
		
		$data['img_width'] = $this->config->get('theme_'.$this->config->get('config_theme').'_image_product_width');
		$data['img_height'] = $this->config->get('theme_'.$this->config->get('config_theme').'_image_product_height');
		
		if(isset($this->request->get['product_id'])) {
			$data['img_width'] = $this->config->get('theme_'.$this->config->get('config_theme').'_image_related_width');
			$data['img_height'] = $this->config->get('theme_'.$this->config->get('config_theme').'_image_related_height');
		}
		
		return $data;
	}
	
	private function getSearchData($params) {
		$uniset = $this->uniset;
		
		$data['categories_search'] = [];
				
		if(isset($uniset['search']['condition']['category'])) {
			$categories = $this->model_extension_module_uni_search->getCategories($params['filter_data']);
				
			foreach ($categories as $category) {
				$data['categories_search'][] = [
					'category_id' => $category['category_id'],
					'name' 		  => $category['name'],
					'thumb' 	  => isset($uniset['search']['condition']['category_img']) && $category['image'] ? $this->model_tool_image->resize($category['image'], $this->config->get('theme_'.$this->config->get('config_theme').'_image_category_width'), $this->config->get('theme_'.$this->config->get('config_theme').'_image_category_height')) : '',
					'href'        => $this->url->link('product/category', 'path='.$category['category_id'], true)
				];
			}
		}
		
		$data['manufacturers_search'] = [];
				
		if(isset($uniset['search']['condition']['manufacturer'])) {
			$manufacturers = $this->model_extension_module_uni_search->getManufacturers($params['filter_data']);
				
			foreach ($manufacturers as $manufacturer) {
				$data['manufacturers_search'][] = [
					'manufacturer_id' => $manufacturer['manufacturer_id'],
					'name' 		  	  => $manufacturer['name'],
					'thumb' 	      => isset($uniset['search']['condition']['manufacturer_img']) && $manufacturer['image'] ? $this->model_tool_image->resize($manufacturer['image'], $this->config->get('theme_'.$this->config->get('config_theme').'_image_category_width'), $this->config->get('theme_'.$this->config->get('config_theme').'_image_category_height')) : '',
					'href'   		  => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'], true)
				];
			}
		}
		
		$data['uni_search'] = true;
		
		return $data;
	}
	
	private function getProductData($product_info) {
		
		if(!isset($product_info['product_id'])) {
			return [];
		}
		
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('extension/module/uni_request');
		
		$this->load->model('extension/module/uni_new_data');
		
		$uniset = $this->uniset;
		$lang_id = $this->config->get('config_language_id');
		$product_id = (int)$product_info['product_id'];
			
		$viewed_products = isset($this->request->cookie['viewedProducts']) ? explode(',', $this->request->cookie['viewedProducts']) : [];
		
		if (in_array($product_id, $viewed_products)) {
			unset($viewed_products[array_search($product_id, $viewed_products)]);
		}
		
		array_unshift($viewed_products, $product_id);
		setcookie('viewedProducts', implode(',', array_slice($viewed_products, 0, 20)), time()+86400, '/');
			
		$currency = $this->session->data['currency'];
			
		$data['hide_last_breadcrumb'] = isset($uniset['breadcrumbs']['hide']['last']) ? true : false;
		
		$data['thumb_width'] = $this->config->get('theme_'.$this->config->get('config_theme').'_image_thumb_width');
		$data['thumb_height'] = $this->config->get('theme_'.$this->config->get('config_theme').'_image_thumb_height');
		$data['additional_width'] = $this->config->get('theme_'.$this->config->get('config_theme').'_image_additional_width');
		$data['additional_height'] = $this->config->get('theme_'.$this->config->get('config_theme').'_image_additional_height');
		
		$data['show_model'] = isset($uniset['show_product_model']) ? true : false;
		$data['show_manuf'] = isset($uniset['show_product_manuf']) ? true : false;
		$data['show_reward'] = isset($uniset['show_product_reward']) ? $uniset['show_product_reward'] : '';
		$data['show_length'] = isset($uniset['show_product_length']) ? $uniset['show_product_length'] : '';
			
		$data['uni_popup_img_effect_in'] = isset($uniset['popup_img_effect_in']) ? 'animated '.$uniset['popup_img_effect_in'] : false;
		$data['uni_popup_img_effect_out'] = isset($uniset['popup_img_effect_out']) ? 'animated '.$uniset['popup_img_effect_out'] : false;
		
		$data['show_quick_order_text_product'] = isset($uniset['show_quick_order_text_product']) ? true : false;
		$data['quick_order_icon'] = isset($uniset['show_quick_order']) ? $uniset[$lang_id]['quick_order_icon'] : '';
		$data['quick_order_title'] = isset($uniset['show_quick_order']) ? $uniset[$lang_id]['quick_order_title'] : '';
			
		$data['text_related'] = isset($uniset[$lang_id]['related_title']) ? $uniset[$lang_id]['related_title'] : $this->language->get('text_related');
			
		$data['quantity'] = $product_info['quantity'];
			
		$data['show_attr_group'] = $uniset['show_product_attr_group'];
		$data['show_attr_item'] = $uniset['show_product_attr_item'];
		$data['show_attr'] = isset($uniset['show_product_attr']) ? true : false;
		
		$data['show_rating'] = isset($uniset['show_rating']) ? true : false;
			
		if($product_info['quantity'] > 0) {
			$data['cart_btn_icon'] = $uniset[$lang_id]['cart_btn_icon'];
			$data['cart_btn_text'] = $uniset[$lang_id]['cart_btn_text'];
			$data['cart_btn_class'] = '';
			$data['quick_order'] = isset($uniset['show_quick_order']) ? true : false;
		} else {
			$data['cart_btn_icon'] = $uniset[$lang_id]['cart_btn_icon_disabled'];
			$data['cart_btn_text'] = $uniset[$lang_id]['cart_btn_text_disabled'];
			$data['cart_btn_class'] = $uniset['cart_btn_disabled'];
			$data['quick_order'] = isset($uniset['show_quick_order_quantity']) ? true : false;
		}
			
		$data['wishlist_btn_disabled'] = isset($uniset['wishlist']['disabled']) ? true : false;
		$data['compare_btn_disabled'] = isset($uniset['compare']['disabled']) ? true : false;
			
		$data['sku'] = !isset($uniset['sku_as_sticker']) ? $product_info['sku'] : '';
		$data['upc'] = !isset($uniset['upc_as_sticker']) ? $product_info['upc'] : '';
		$data['ean'] = !isset($uniset['ean_as_sticker']) ? $product_info['ean'] : '';
		$data['jan'] = !isset($uniset['jan_as_sticker']) ? $product_info['jan'] : '';
		$data['isbn'] = !isset($uniset['isbn_as_sticker']) ? $product_info['isbn'] : '';
		$data['mpn'] = !isset($uniset['mpn_as_sticker']) ? $product_info['mpn'] : '';
		$data['location'] = $product_info['location'];
			
		$data['text_sku'] = $uniset[$lang_id]['sku_text'];
		$data['text_upc'] = $uniset[$lang_id]['upc_text'];
		$data['text_ean'] = $uniset[$lang_id]['ean_text'];
		$data['text_jan'] = $uniset[$lang_id]['jan_text'];
		$data['text_isbn'] = $uniset[$lang_id]['isbn_text'];
		$data['text_mpn'] = $uniset[$lang_id]['mpn_text'];
		$data['text_location'] = $uniset[$lang_id]['location_text'];
			
		$data['weight'] = ($product_info['weight'] > 0) ? round($product_info['weight'], 3).' '.$this->weight->getUnit($product_info['weight_class_id']) : '';
		$data['length'] = ($product_info['length'] > 0 && $product_info['width'] > 0 && $product_info['height'] > 0) ? round($product_info['length'], 2).'&times;'.round($product_info['width'], 2).'&times;'.round($product_info['height'], 2).' '.$this->length->getUnit($product_info['length_class_id']) : '';
			
		$data['socialbutton'] = isset($uniset['socialbutton']) ? $uniset['socialbutton'] : [];
		
		$data['product_banner_position'] = $uniset['product_banner_position'];
		
		$data['product_banners'] = [];
		
		if(isset($uniset[$lang_id]['product_banners'])) {
			foreach($uniset[$lang_id]['product_banners'] as $banner) {
				if(isset($banner['text'])) {
					$data['product_banners'][] = [
						'icon' 			=> $banner['icon'],
						'text' 			=> html_entity_decode($banner['text'], ENT_QUOTES, 'UTF-8'),
						'link' 			=> $banner['link'],
						'link_popup' 	=> isset($banner['link_popup']) ? true : false,
						'hide' 			=> isset($banner['hide']) ? true : false,
					];
				}
			}
		}
			
		$data['uni_product_tabs'] = [];
			
		$uni_request = $this->config->get('uni_request');
			
		if(isset($uni_request['question_list'])) {
			$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/request.css');
			
			$data['uni_product_tabs'][] = [
				'id'			=> 'question',
				'icon' 			=> 'fa fa-question',
				'title' 		=> $this->language->get('tab_question'),
				'description'	=> ''
			];
		}
			
		if(isset($uniset['show_additional_tab'])) {
			$data['uni_product_tabs'][] = [
				'id'			=> 'additional',
				'icon' 			=> $uniset[$lang_id]['additional_tab_icon'],
				'title' 		=> $uniset[$lang_id]['additional_tab_title'],
				'description'	=> html_entity_decode($uniset[$lang_id]['additional_tab_text'], ENT_QUOTES, 'UTF-8')
			];
		}
			
		if(isset($uniset['show_related_news']) && $this->config->get('uni_news')) {
			
			$news_related = $this->load->controller('extension/module/uni_news_related');
				
			if($news_related) {
				$data['uni_product_tabs'][] = [
					'id'			=> 'news',
					'icon' 			=> $uniset[$lang_id]['related_news_icon'],
					'title' 		=> $uniset[$lang_id]['related_news_title'],
					'description'	=> $news_related
				];
			}
		}
		
		if(isset($uniset['product']['download_tab']) && $uniset['product']['download_tab']['status'] != 0) {
		
			$downloads = $this->load->controller('extension/module/uni_download');
		
			if($downloads) {
				$data['uni_product_tabs'][] = [
					'id'			=> 'download',
					'icon' 			=> $uniset['product']['download_tab']['icon'][$lang_id],
					'title' 		=> $uniset['product']['download_tab']['title'][$lang_id],
					'description'	=> $downloads
				];
			}
		}
			
		$data['manufacturer_descr'] = [];
		
		if(isset($uniset['show_manufacturer'])) {
			$data['manufacturer_position'] = (isset($uniset['manufacturer_position']) ? $uniset['manufacturer_position'] : '');
			$data['manufacturer_title'] = $uniset[$lang_id]['manufacturer_title'];
				
			$this->load->model('tool/image');
			$manufacturer_descr = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);
				
			if(isset($manufacturer_descr['description']) && $manufacturer_descr['description'] != '') {
				$data['manufacturer_descr'] = [
					'name'			=> $manufacturer_descr['name'],
					'description'	=> utf8_substr(strip_tags(html_entity_decode($manufacturer_descr['description'], ENT_QUOTES, 'UTF-8')), 0, $uniset['manufacturer_text_length']),
					'image'			=> $manufacturer_descr['image'] ? $this->model_tool_image->resize($manufacturer_descr['image'], $uniset['manufacturer_logo_w'], $uniset['manufacturer_logo_h']) : '',
					'href'			=> $this->url->link('product/manufacturer/info&manufacturer_id='.$product_info['manufacturer_id'])
				];
			}
		}
		
		$product_info['product_page'] = true;
			
		$new_data = $this->model_extension_module_uni_new_data->getNewData($product_info);
			
		$data['product']['stickers'] = $new_data['stickers'];
		$data['product']['show_timer'] = $new_data['special_date_end'];
		$data['product']['quantity_indicator'] = $new_data['quantity_indicator'];
			
		$data['price_value'] = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency);
		$data['special_value'] = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency);
		$data['discounts_value'] = $new_data['discounts'];
		
		$data['text_review_total'] = sprintf($this->language->get('text_review_total'), $product_info['reviews']);
		$data['text_review_score'] = sprintf($this->language->get('text_review_score'), $product_info['rating']);
		$data['show_plus_minus_review'] = isset($uniset['show_plus_minus_review']) ? true : false;
		$data['plus_minus_review_required'] = isset($uniset['plus_minus_review_required']) ? true : false;
			
		$data['auto_related'] = isset($uniset['similar']['show']) ? $this->load->controller('extension/module/uni_auto_related') : '';
			
		$data['change_opt_img_p'] = isset($uniset['change_opt_img_p']) ? true : false;
		
		$data['tabs_is_scroll'] = !isset($uniset['tabs']['mobile']['without_scroll']) ? true : false;
		
		$data['microdata'] = [
			//'name'		=> str_replace('"', "'",  preg_replace('/"([^"]*)"/', "«$1»", htmlspecialchars_decode($product_info['name'], ENT_QUOTES))),
			'name'			=> str_replace(['"', '&quot;'], '', $product_info['name']),
			'model' 		=> $product_info['model'],
			'sku' 			=> !isset($uniset['sku_as_sticker']) ? $product_info['sku'] : '',
			'mpn' 			=> !isset($uniset['mpn_as_sticker']) ? $product_info['mpn'] : '',
			'category' 		=> $product_info['category_name'],
			'manufacturer'	=> $product_info['manufacturer'],
			'description' 	=> trim(str_replace(["\r\n", "\r", "\n", '"', '&nbsp;'], ' ',  strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')))),
			'price' 		=> $this->tax->calculate($product_info['special'] ? $product_info['special'] : $product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency),
			'price_date_end'=> $new_data['special_date_microdata_end'],
			'code' 			=> $currency,
			'review_status'	=> $this->config->get('config_review_status'),
			'reviews_num' 	=> $product_info['reviews'],
			'rating' 		=> $product_info['rating'],
			'url' 			=> $this->url->link('product/product', '&product_id='.$this->request->get['product_id'])
		];
		
		$data['tab_review'] = $this->language->get('tab_uni_review');
		$data['review_total'] = (int)$product_info['reviews'];
		
		$data['uni_reviews'] = '';
		
		if($this->config->get('config_review_status')) {
			$reviews = $this->model_catalog_review->getReviewsByProductId($product_id, 0, 5);
			
			if($reviews) {
				$data_reviews = [];
				
				foreach ($reviews as $result) {
					$data_reviews['reviews'][] = [
						'author'      => $result['author'],
						'text'        => nl2br($result['text']),
						'plus'     	  => nl2br($result['plus']),
						'minus'       => nl2br($result['minus']),
						'admin_reply' => nl2br($result['admin_reply']),
						'rating'      => (int)$result['rating'],
						'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
					];
			
					$data['microdata']['reviews'][] = [
						'author'      => $result['author'],
						'text'        => $result['text'],
						'rating'      => (int)$result['rating'],
						'date_added'  => date('Y-m-d', strtotime($result['date_added'])),
					];
				}
		
				$data['uni_reviews'] = $this->load->view('product/review', $data_reviews);
			}
		}
		
		return $data;
	}
	
	private function getCartData($product = []) {
		
		if(!isset($product['product_id'])) {
			return [];
		}
		
		$option = $product['option'];
		$product_options = $product['options'];
		
		$currency = $this->session->data['currency'];
			
		$options = '';
			
		$product_price = $this->tax->calculate($product['special'] ? $product['special'] : $product['price'], $product['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency);
			
		foreach ($product_options as $key => $product_option) {
			if (!empty($option[$product_option['product_option_id']])) {
				
				$options .= (($key > 0) ? ', ' : '').$product_option['name'].':';
						
				if($product_option['type'] == 'select' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'radio') {
					foreach ($product_option['product_option_value'] as $value) {
						$option_id_arr = is_array($option[$product_option['product_option_id']]) ? $option[$product_option['product_option_id']] : array($option[$product_option['product_option_id']]);
							
						if(in_array($value['product_option_value_id'], $option_id_arr)) {
							$option_price = $this->tax->calculate($value['price'], $product['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency);
							
							switch($value['price_prefix']) {
								case '+':
									$product_price += $option_price;
									break;
								case '-':
									$product_price -= $option_price;
									break;
								case '*':
									$product_price = $product_price * $option_price;
									break;
								case '/':
									$product_price = $product_price / $option_price;
									break;
								case '=':
									$product_price = $product_price;
							}
								
							$options .= ' '.$value['name'];
						}
					}
				} elseif($product_option['type'] == 'file') {
					$this->load->model('tool/upload');
						
					$upload_info = $this->model_tool_upload->getUploadByCode($option[$product_option['product_option_id']]);

					$options .= $upload_info ? ' '.$upload_info['name'] : '';
				} else {
					$options .= ' '.$option[$product_option['product_option_id']];
				}
			}
		}
			
		return [
			'id'		=> $product['product_id'], 
			'name' 		=> $product['name'], 
			'brand' 	=> isset($product['manufacturer']) ? $product['manufacturer'] : '', 
			'variant' 	=> $options, 
			'quantity'	=> $product['quantity'], 
			'price' 	=> $product_price
		];
	}
	
	private function getContactData() {
		$uniset = $this->uniset;
		$lang_id = $this->config->get('config_language_id');
		$shop_telephone = $this->config->get('config_telephone');
		$shop_email = $this->config->get('config_email');
			
		$contacts_main = isset($uniset['header']['contacts']['main']) ? $uniset['header']['contacts']['main'] : [];
		$contacts_addit = isset($uniset['header']['contacts']['addit']) ? $uniset['header']['contacts']['addit'] : [];
		
		$contacts = array_merge($contacts_main, $contacts_addit);
		
		$data['contacts'] = [];
		
		foreach($contacts as $key => $contact) {
			if(isset($contact['contact_page'][$lang_id]) && $contact['number'][$lang_id] != $shop_telephone && $contact['number'][$lang_id] != $shop_email) {
				$href = '';
				
				$number = str_replace([' ', '(', ')', '-'], '', $contact['number'][$lang_id]);
				$type = $contact['type'][$lang_id];
					
				if($type != '') {
					if($type == '?call' || $type == '?chat') {
						$href = 'skype:'.$number.$type;
					} else if($type == 'viber://chat?number=') {
						$href = str_replace('+', '%2B', $type.$number);
					} else {
						$href = $type.$number;
					}
				}
				
				if(!$contact['icon'][$lang_id] && substr($number, 0, 1) == '+') {
					$contact['icon'][$lang_id] = 'fas fa-phone-alt';
				}
			
				$data['contacts'][] = [
					'href'		=> $href,
					'number'	=> $contact['number'][$lang_id],
					'icon' 		=> $contact['icon'][$lang_id]
				];
			}
		}
		
		$data['shop_name'] = $this->config->get('config_name');
		$data['text_in_contacts'] = isset($uniset[$lang_id]['text_in_contacts']) ? html_entity_decode($uniset[$lang_id]['text_in_contacts'], ENT_QUOTES, 'UTF-8') : '';
		$data['contact_map'] = html_entity_decode($uniset['maps'], ENT_QUOTES, 'UTF-8');
		$data['shop_email'] = $shop_email;
		
		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		
		$data['microdata'] = [
			'name'			=> $this->config->get('config_name'),
			'image' 		=> (is_file(DIR_IMAGE . $this->config->get('config_logo'))) ?  $server.'image/'.$this->config->get('config_logo') : '',
			'url' 			=> $server,
			'description'	=> $this->config->get('config_meta_description'),
			'email'			=> $shop_email,
			'telephone'		=> $shop_telephone,
			'address'		=> $this->config->get('config_address'),
			'open_hours'	=> nl2br($this->config->get('config_open')),
			'currency'		=> $this->session->data['currency']
		];
		
		return $data;
	}
	
	private function getContacts() {
		
		return $data;
	}
	
	public function setDefaultView() {
		$view = isset($this->request->post['view']) ? $this->request->post['view'] : '';
		
		if(in_array($view, ['grid', 'list', 'compact'])) {
			$this->session->data['uni_default_view'] = $view;
		}
	}
	
	public function compareRemove() {
		$this->load->language('extension/module/uni_othertext');

		$json = [];

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info && in_array($product_id, $this->session->data['compare'])) {
			$key = array_search($product_id, $this->session->data['compare']);
			
			unset($this->session->data['compare'][$key]);

			$json['success'] = sprintf($this->language->get('text_compare_remove'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('product/compare'));
			$json['total'] = isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function wishlistRemove() {
		$this->load->language('extension/module/uni_othertext');

		$json = [];

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($this->customer->isLogged() && $product_info && in_array($product_id, $this->session->data['wishlist'])) {
			$this->load->model('account/wishlist');
				
			$this->model_account_wishlist->deleteWishlist($product_id);
			
			$key = array_search($product_id, $this->session->data['wishlist']);
			
			unset($this->session->data['compare'][$key]);

			$json['success'] = sprintf($this->language->get('text_wishlist_remove'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));
			$json['total'] = $this->model_account_wishlist->getTotalWishlist();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
?>