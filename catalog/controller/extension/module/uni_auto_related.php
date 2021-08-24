<?php  
class ControllerExtensionModuleUniAutoRelated extends Controller {
	public function index() {
		static $module = 0;
		
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$similar = isset($uniset['similar']) ? $uniset['similar'] : [];
		
		if(!$similar) {
			return true;
		}
		
		$this->load->model('catalog/product');
		$this->load->model('extension/module/uni_related');
		$this->load->model('tool/image');
		$this->load->model('extension/module/uni_new_data');

		$this->load->language('product/product');
		$this->load->language('extension/module/uni_othertext');
		
		$data['heading_title'] = $similar['title'][$lang_id];
		
		$data['show_quick_order_text'] = isset($uniset['show_quick_order_text']) ? $uniset['show_quick_order_text'] : '';			
		$data['quick_order_icon'] = isset($uniset['show_quick_order']) ? html_entity_decode($uniset[$lang_id]['quick_order_icon'], ENT_QUOTES, 'UTF-8') : '';	
		$data['quick_order_title'] = isset($uniset['show_quick_order']) ? $uniset[$lang_id]['quick_order_title'] : '';
		$data['show_rating'] = isset($uniset['show_rating']) ? true : false;
		$data['show_rating_count'] = isset($uniset['show_rating_count']) ? true : false;
		$data['wishlist_btn_disabled'] = isset($uniset['wishlist']['disabled']) ? true : false;
		$data['compare_btn_disabled'] = isset($uniset['compare']['disabled']) ? true : false;
			
		$img_width = $this->config->get('theme_'.$this->config->get('config_theme') . '_image_related_width');
		$img_height = $this->config->get('theme_'.$this->config->get('config_theme') . '_image_related_height');
		
		$img_size = ['width' => $img_width, 'height' => $img_height];
		
		$data['img_width'] = $img_width;
		$data['img_height'] = $img_height;
		
		$currency = $this->session->data['currency'];
		
		$data['products'] = [];
		
		$product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
		$lang_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		$customer_group_id = $this->customer->isLogged() ? (int)$this->customer->getGroupId() : (int)$this->config->get('config_customer_group_id');
		
		$cache_name = 'product.unishop.autorelated.'.$product_id.(isset($similar['stock']) ? '.stock' : '').'.'.$customer_group_id.'.'.$lang_id.'.'.$store_id;
		
		$results = isset($similar['cache']) ? $this->cache->get($cache_name) : [];
		
		if(!$results) {
			$filter_data = [
				'product_id' 	=> $product_id,
				'limit'			=> isset($similar['limit']) ? $similar['limit'] : 5,
				'main_category'	=> isset($similar['main_category']) ? true : false,
				'stock'			=> isset($similar['stock']) ? true : false,
			];
		
			$results = $this->model_extension_module_uni_related->getAutoRelated($filter_data);
			
			if($results) {
				$this->cache->set($cache_name, $results);
			}
		}
		
		foreach ($results as $result) {
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
				
			$new_data = $this->model_extension_module_uni_new_data->getNewData($result, $img_size);
			
			$show_description = isset($uniset['show_description']) && !isset($uniset['show_description_alt']) || isset($uniset['show_description_alt']) && !$new_data['attributes'] ? true : false;
				
			if($new_data['special_date_end']) {
				$data['show_timer'] = true;
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
				'href'    	 		=> $this->url->link('product/product', 'product_id='.$result['product_id'], true),
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
		
		$data['module'] = $module++;
		
		return $this->load->view('extension/module/uni_auto_related', $data);
	}
}
?>