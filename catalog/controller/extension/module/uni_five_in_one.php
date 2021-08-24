<?php
class ControllerExtensionModuleUniFiveInOne extends Controller {
	public function index($setting) {
		static $module = 0;
		
		$this->load->language('extension/module/uni_othertext');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('extension/module/uni_five_in_one');
		$this->load->model('extension/module/uni_new_data');
		
		$store_id = (int)$this->config->get('config_store_id');
		$lang_id = (int)$this->config->get('config_language_id');
		$currency = $this->session->data['currency'];
		$customer_group = $this->customer->getGroupId() ? $this->customer->getGroupId() : $this->config->get('config_customer_group_id');
		
		$uniset = $this->config->get('config_unishop2');
		$settings = isset($setting['set'][$store_id]) ? $setting['set'][$store_id] : [];
		
		$data['show_quick_order_text'] = isset($uniset['show_quick_order_text']) ? $uniset['show_quick_order_text'] : '';			
		$data['quick_order_icon'] = isset($uniset['show_quick_order']) ? $uniset[$lang_id]['quick_order_icon'] : '';
		$data['quick_order_title'] = isset($uniset['show_quick_order']) ? $uniset[$lang_id]['quick_order_title'] : '';
		$data['show_rating'] = isset($uniset['show_rating']) ? true : false;
		$data['wishlist_btn_disabled'] = isset($uniset['wishlist']['disabled']) ? true : false;
		$data['compare_btn_disabled'] = isset($uniset['compare']['disabled']) ? true : false;
		
		/*
		$module_items = $uniset['module_items'];
		
		$items_string = '{';
		
		foreach($module_items as $key => $item) {
			$items_string .= $item['key'].':{items:'.$item['item'].'}';
			
			if($key+1 < count($module_items)) {
				$items_string .= ', ';
			}
		}
		
		$items_string .= '}';
		
		$data['items_string'] = $items_string;
		*/
		
		$tabs = [];

		$i = 0;
		
		if(count($settings) > 1) {
			foreach ($settings as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $settings);
		}
			
		foreach($settings as $key => $tab_settings) {
			if(isset($tab_settings['status'])) {
			
				$tabs[$i]['title'] = isset($tab_settings['title'][$lang_id]) ? $tab_settings['title'][$lang_id] : '';
				$tabs[$i]['img_width'] = $tab_settings['thumb_width'];
				$tabs[$i]['img_height'] = $tab_settings['thumb_height'];
				$tabs[$i]['type'] = isset($tab_settings['type']) ? 'grid' : 'carousel';
				$limit = isset($tab_settings['limit']) ? $tab_settings['limit'] : 5;
				$qty = isset($tab_settings['qty']) ? $tab_settings['qty'] : 0;
					
				$cache_name = 'product.unishop.five.in.one.'.substr(md5($tabs[$i]['title'].$tabs[$i]['type']), 0, 8).'.'.$limit.'.'.$qty.'.'.$customer_group.'.'.$lang_id.'.'.$store_id;
					
				$tabs[$i]['products'] = [];
				
				$products = $this->cache->get($cache_name);
				
				if(!$products) {
					switch($key) {
						case 'latest':
							$products = $this->model_extension_module_uni_five_in_one->getLatest($limit, $qty);
							break;
						case 'special':
							$products = $this->model_extension_module_uni_five_in_one->getSpecial($limit, $qty);
							break;
						case 'bestseller':
							$products = $this->model_extension_module_uni_five_in_one->getBestseller($limit, $qty);
							break;
						case 'popular':
							$products = $this->model_extension_module_uni_five_in_one->getPopular($limit, $qty);
							break;
						default:
							if(isset($tab_settings['category_name']) && $tab_settings['category_id']) {
								$products = $this->model_extension_module_uni_five_in_one->getProductsFromCategory($tab_settings['category_id'], $limit, $qty);
							} else {
								$results = array_slice(isset($tab_settings['product']) ? $tab_settings['product'] : [], 0, (int)$limit);
								$products = $this->model_extension_module_uni_five_in_one->getFeatured($results, $qty);
							}
					}
					
					if($products) {
						$this->cache->set($cache_name, $products);
					}
				}
				
				$img_size = [
					'width'	  => $tab_settings['thumb_width'], 
					'height'  => $tab_settings['thumb_height']
				];
				
				foreach ($products as $result) {
					if($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $tab_settings['thumb_width'], $tab_settings['thumb_height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $tab_settings['thumb_width'], $tab_settings['thumb_height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);
					} else {
						$price = false;
					}

					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $currency);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $result['rating'];
					} else {
						$rating = false;
					}
				
					$new_data = $this->model_extension_module_uni_new_data->getNewData($result, $img_size);
					$show_description = isset($uniset['show_description']) && !isset($uniset['show_description_alt']) || isset($uniset['show_description_alt']) && !$new_data['attributes'] ? true : false;
				
					if($new_data['special_date_end']) {
						$tabs[$i]['show_timer'] = true;
					}
					
					$model = isset($uniset['catalog']['show_model']) && $uniset['catalog']['show_model'] != 'disabled' ? ($uniset['catalog']['show_model'] == 'model' ? $result['model'] : $result['sku']) : '';
				
					if($result['quantity'] > 0) {
						$show_quantity = isset($uniset['qty_switch']['enabled']) ? true : false;
						$cart_btn_icon = $uniset[$lang_id]['cart_btn_icon'];
						$cart_btn_text = $uniset[$lang_id]['cart_btn_text'];
						$cart_btn_class = '';
						$quick_order = isset($uniset['show_quick_order']) ? true : false;
					} else {
						$show_quantity = isset($uniset['qty_switch']['enabled_all']) ? true : false;
						$cart_btn_icon = $uniset[$lang_id]['cart_btn_icon_disabled'];
						$cart_btn_text = $uniset[$lang_id]['cart_btn_text_disabled'];
						$cart_btn_class = $uniset['cart_btn_disabled'];
						$quick_order = isset($uniset['show_quick_order_quantity']) ? true : false;
					}

					$tabs[$i]['products'][] = array(
						'product_id' 		=> $result['product_id'],
						'thumb'   	 		=> $image,
						'name'    			=> $result['name'],
						'description' 		=> utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_'.$this->config->get('config_theme').'_product_description_length')) . '..',
						'tax'         		=> $tax,
						'price'   	 		=> $price,
						'special' 	 		=> $special,
						'rating'     		=> $rating,
						'reviews'    		=> sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
						'num_reviews' 		=> isset($uniset['show_rating_count']) ? $result['reviews'] : '',
						'href'    	 		=> $this->url->link('product/product', 'product_id='.$result['product_id']),
						'model'				=> $model,
						'additional_image'	=> $new_data['additional_image'],
						'stickers' 			=> $new_data['stickers'],
						'special_date_end' 	=> $new_data['special_date_end'],
						'minimum' 			=> $result['minimum'] ? $result['minimum'] : 1,
						'quantity_indicator'=> $new_data['quantity_indicator'],
						'price_value' 		=> $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency),
						'special_value' 	=> $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency),
						'discounts'			=> $new_data['discounts'],
						'attributes' 		=> $new_data['attributes'],
						'options'			=> $new_data['options'],
						'show_description'	=> $show_description,
						'show_quantity'		=> $show_quantity,
						'cart_btn_icon'		=> $cart_btn_icon,
						'cart_btn_text'		=> $cart_btn_text,
						'cart_btn_class'	=> $cart_btn_class,
						'quick_order'		=> $quick_order,
					);
				}
					
				if($tabs[$i]['products']) {
					$i++;
				}
			}
		}

		$data['tabs'] = $tabs;
		$data['module'] = $module++;
		
		return $this->load->view('extension/module/uni_five_in_one', $data);
	}
}