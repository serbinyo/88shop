<?php
class ControllerExtensionModuleUniViewed extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/uni_viewed');
		
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$data['heading_title'] = isset($setting['title'][$lang_id]) ? $setting['title'][$lang_id] : $this->language->get('heading_title');
		
		$data['width'] = $setting['width'];
		$data['height'] = $setting['height'];
		$data['limit'] = $setting['limit'];
		$data['type_view'] = isset($setting['view_type']) ? 'grid' : 'carousel';
		
		$data['products'] = [];
		
		if(isset($this->request->cookie['viewedProducts'])) {
			return $this->load->view('extension/module/uni_viewed', $data);
		} else {
			return false;
		}
	}
	
	public function ajax() {
		$this->load->language('extension/module/uni_viewed');
		$this->load->language('extension/module/uni_othertext');
		
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('extension/module/uni_new_data');
		
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$currency = $this->session->data['currency'];
		
		$data['show_quick_order_text'] = isset($uniset['show_quick_order_text']) ? $uniset['show_quick_order_text'] : '';			
		$data['quick_order_icon'] = isset($uniset['show_quick_order']) ? $uniset[$lang_id]['quick_order_icon'] : '';
		$data['quick_order_title'] = isset($uniset['show_quick_order']) ? $uniset[$lang_id]['quick_order_title'] : '';
		$data['show_rating'] = isset($uniset['show_rating']) ? true : false;
		$data['wishlist_btn_disabled'] = isset($uniset['wishlist']['disabled']) ? true : false;
		$data['compare_btn_disabled'] = isset($uniset['compare']['disabled']) ? true : false;
		
		$data['products'] = [];
		
		if(isset($this->request->cookie['viewedProducts'])) {
			
			$img_width = isset($this->request->post['width']) && $this->request->post['width'] != '' ? (int)$this->request->post['width'] : 200;
			$img_height = isset($this->request->post['height']) && $this->request->post['height'] != '' ? (int)$this->request->post['height'] : 180;
			$limit = isset($this->request->post['limit']) && $this->request->post['limit'] != '' ? (int)$this->request->post['limit'] : 5;
			
			$setting = array(
				'width'	 => $img_width,
				'height' => $img_height
			);
			
			$data['img_width'] = $img_width;
			$data['img_height'] = $img_height;
			
			$viewed = explode(',', $this->request->cookie['viewedProducts']);
			$products = array_slice($viewed, 0, $limit);
			
			if($products) {
				foreach($products as $product_id) {
					
					$result = $this->model_catalog_product->getProduct((int)$product_id);

					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $img_width, $img_height);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $img_width, $img_height);
					}
				
					if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);
					} else {
						$price = false;
					}
						
					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);
					} else {
						$special = false;
					}
				
					if ($this->config->get('config_review_status')) {
						$rating = (int)$result['rating'];
					} else {
						$rating = false;
					}
			
					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $currency);
					} else {
						$tax = false;
					}
				
					$new_data = $this->model_extension_module_uni_new_data->getNewData($result, $setting);
					$show_description = isset($uniset['show_description']) && !isset($uniset['show_description_alt']) || isset($uniset['show_description_alt']) && !$new_data['attributes'] ? true : false;
					
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
							
					$data['products'][] = array(
						'product_id' 		=> $result['product_id'],
						'thumb'   	 		=> $image,
						'name'    			=> $result['name'],
						'description' 		=> utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_'.$this->config->get('config_theme') . '_product_description_length')) . '..',
						'tax'         		=> $tax,
						'price'   	 		=> $price,
						'special' 	 		=> $special,
						'rating'     		=> $rating,
						'reviews'    		=> sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
						'href'    	 		=> $this->url->link('product/product', 'product_id='.$result['product_id']),
						'model'				=> $model,
						'additional_image'	=> $new_data['additional_image'],
						'stickers' 			=> $new_data['stickers'],
						'num_reviews' 		=> isset($uniset['show_rating_count']) ? $result['reviews'] : '',
						'special_date_end' 	=> $new_data['special_date_end'],
						'minimum' 			=> $result['minimum'] ? $result['minimum'] : 1,
						'quantity_indicator'=> $new_data['quantity_indicator'],
						'price_value' 		=> $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency),
						'special_value' 	=> $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency),
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
			}
			
			$this->response->setOutput($this->load->view('extension/module/uni_viewed', $data));
		} else {
			return false;
		}
	}
}