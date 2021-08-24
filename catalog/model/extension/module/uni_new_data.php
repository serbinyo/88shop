<?php 
class ModelExtensionModuleUniNewData extends Controller {
	private $uniset = [];
	
	public function getNewData($result = [], $img_size = []) {
		$this->uniset = $this->config->get('config_unishop2');
		
		if(!isset($result['product_id'])) {
			return ['stickers' => '', 'special_date_end' => '', 'additional_image' => '', 'attributes' => '', 'options' => '', 'discounts' => '', 'quantity_indicator' => ''];
		}
		
		$img_width = isset($img_size['width']) ? $img_size['width'] : 220;
		$img_height = isset($img_size['height']) ? $img_size['height'] : 200;
		
		$data['stickers'] = $this->getStickers($result);
		
		if(!isset($result['product_page'])) {
			$data['additional_image'] = $this->getAdditionalImage($result['product_id'], $img_width, $img_height);
			
			$data['attributes'] = $this->getAttributes($result['product_id']);
			
			$options = $this->getOptions($result['product_id'], $result['quantity'], $result['tax_class_id'], $img_width, $img_height);
			
			$data['options'] = $options['options'];
			
			$quantity = $result['quantity'] > 0 ? $options['quantity'] : 0;
			
			$options = $options['options'] ? true : false;
			$product_page = false;
		} else {
			$quantity = $result['quantity'] > 0 ? $result['options_quantity'] : 0;
			
			$options = $result['options'] ? true : false;
			$product_page = true;
		}
		
		$special_date_end = $this->getSpecialDateEnd($result['product_id'], $result['special'], $product_page);
		
		$data['special_date_end'] = $special_date_end['timer_end'];
		$data['special_date_microdata_end'] = $special_date_end['microdata_end'];
		
		$data['discounts'] = $this->getDiscounts($result['product_id'], $result['tax_class_id']);
		$data['quantity_indicator'] = $this->getQuantityIndicator($quantity, $options);
		
		return $data;
	}
	
	private function getAttributes($product_id) {
		
		$uniset = $this->uniset;
		
		$result = [];
		
		if(isset($uniset['show_attr'])) {
			$attributes = $this->model_catalog_product->getProductAttributes((int)$product_id);
			
			foreach($attributes as $key => $attribute) {
				if($key < $uniset['show_attr_group']) {
					foreach($attribute['attribute'] as $key => $attribute_value) {
						if($key < $uniset['show_attr_item']) {
							$result[] = array(
								'name' => isset($uniset['show_attr_name']) ? $attribute_value['name'] : '',
								'text' => $attribute_value['text']
							);
						}
					}	
				}
			}
		}
		
		return $result;
	}
	
	private function getOptions($product_id, $prod_quantity, $tax_class_id, $img_width, $img_height) {
		$uniset = $this->uniset;
		$currency = $this->session->data['currency'];
		
		$o_quantity = 0;
		$required = false;
		$o_quantity_arr = [];
		
		if(isset($uniset['catalog']['option_img_prop'])) {
			$img_width = $img_height;
		}
			
		$data['options'] = [];
		
		if (isset($uniset['show_options']) && $uniset['show_options_item'] > 0) {		
			foreach ($this->model_catalog_product->getProductOptions((int)$product_id) as $key => $option) {
				if ($key < $uniset['show_options_item'] && ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox')) {
				
					$product_option_value_data = [];
					
					if($option['required']) {
						$o_quantity = 0;
						$required = true;
					}

					foreach ($option['product_option_value'] as $option_value) {
						if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
							if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
								$option_price = $this->currency->format($this->tax->calculate($option_value['price'], $tax_class_id, $this->config->get('config_tax') ? 'P' : false), $currency);
							} else {
								$option_price = false;
							}
							
							$product_option_value_data[] = array(
								'product_option_value_id' => $option_value['product_option_value_id'],
								'option_value_id'         => $option_value['option_value_id'],
								'name'                    => $option_value['name'],
								'image'                   => $option_value['image'] ? $this->model_tool_image->resize($option_value['image'], $img_width/4, $img_height/4) : '',
								'small' 				  => $this->model_tool_image->resize($option_value['image'], $img_width, $img_height),
								'price'                   => $option_price,
								'price_value'             => $this->tax->calculate($option_value['price'], $tax_class_id, $this->config->get('config_tax'))*$this->currency->getValue($currency),
								'price_prefix'            => $option_value['price_prefix']
							);
						}
						
						$o_quantity = $o_quantity + $option_value['quantity'];
					}
					
					if($option['required']) {
						$o_quantity_arr[] = $o_quantity;
					}

					$data['options'][] = array(
						'product_option_id'    => $option['product_option_id'],
						'product_option_value' => $product_option_value_data,
						'option_id'            => $option['option_id'],
						'name'                 => $option['name'],
						'type'                 => $option['type'],
						'value'                => $option['value'],
						'required'             => $option['required']
					);
				}
			}
		}
		
		$data['quantity'] = $required ? min($o_quantity_arr) : $prod_quantity + $o_quantity;

		return $data;
	}
	
	private function getStickers($result) {
		$uniset = $this->uniset;
		$lang_id = $this->config->get('config_language_id');
		$currency = $this->session->data['currency'];
		
		$stickers = [];
		
		if($result) {			
			if (isset($uniset['sticker_reward']) && isset($result['reward']) && $result['reward'] > 0) {
				$stickers[] = array(
					'name' 			=> 'reward',
					'text' 			=> $uniset[$lang_id]['sticker_reward_text'],
					'text_after'	=> $uniset[$lang_id]['sticker_reward_text_after'],
					'value' 		=> round($result['reward'], 0),
					'length' 		=> strlen($uniset[$lang_id]['sticker_reward_text']) + strlen($uniset[$lang_id]['sticker_reward_text_after'])
				);
			}
			
			if (isset($uniset['sticker_special']) && $result['special'] > 0 && $result['price'] > 0) {
				$percent = round((($result['special'] - $result['price'])/$result['price'])*100, 0) . '%';
				$value = $this->currency->format($this->tax->calculate($result['price'] - $result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);

				$stickers[] = array(
					'name' 		 => 'special',
					'text' 		 => $uniset[$lang_id]['sticker_special_text'],
					'text_after' => '',
					'value' 	 => isset($uniset['sticker_special_percent']) ? $percent : $value,
					'length'	 => strlen($uniset[$lang_id]['sticker_special_text']) + strlen(isset($uniset['sticker_special_percent']) ? $percent : $value),
				);
			}
			
			if(isset($uniset['sticker_bestseller'])) {
				$bestseller = $this->getBestSellerSticker($result['product_id']);
				
				if ($bestseller) {
					$stickers[] = array(
						'name'		 => 'bestseller',
						'text' 		 => $uniset[$lang_id]['sticker_bestseller_text'],
						'text_after' => '',
						'value' 	 => '',
						'length' 	 => strlen($uniset[$lang_id]['sticker_bestseller_text'])
					);
				}
			}
			
			$date = strtotime($result['date_available']) + $uniset['sticker_new_date'] * 24 * 3600;
				
			if (isset($uniset['sticker_new']) && $date >= strtotime('now')) {
				$stickers['new'] = array(
					'name' 		 => 'new',
					'text' 		 => $uniset[$lang_id]['sticker_new_text'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($uniset[$lang_id]['sticker_new_text'])
				);		
			}
			
			if (isset($uniset['sku_as_sticker']) && $result['sku']) {
				$stickers[] = array(
					'name'		 => 'sku',
					'text'       => $result['sku'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($result['sku'])
				);
			}
			
			if (isset($uniset['upc_as_sticker']) && $result['upc']) {
				$stickers[] = array(
					'name'		 => 'upc',
					'text'       => $result['upc'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($result['upc'])
				);
			}
			
			if (isset($uniset['ean_as_sticker']) && $result['ean']) {
				$stickers[] = array(
					'name' 		 => 'ean',
					'text' 		 => $result['ean'],
					'text_after' => '',
					'value'		 => '',
					'length' 	 => strlen($result['ean'])
				);
			}
			
			if (isset($uniset['jan_as_sticker']) && $result['jan']) {
				$stickers[] = array(
					'name' 		 => 'jan',
					'text'		 => $result['jan'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($result['jan'])
				);
			}
			
			if (isset($uniset['isbn_as_sticker']) && $result['isbn']) {
				$stickers[] = array(
					'name' 		 => 'isbn',
					'text' 	 	 => $result['isbn'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($result['isbn'])
				);
			}
			
			if (isset($uniset['mpn_as_sticker']) && $result['mpn']) {
				$stickers[] = array(
					'name' 		 => 'mpn',
					'text' 		 => $result['mpn'],
					'text_after' => '',
					'value' 	 => '',
					'length' 	 => strlen($result['mpn'])
				);
			}
			
			if(count($stickers) > 1) { 
				foreach ($stickers as $key => $value) {
					$sort[$key] = $value['length'];
				}
			
				array_multisort($sort, SORT_DESC, $stickers);
			}	
		}
		
		return $stickers;
	}
	
	private function getQuantityIndicator($quantity, $options) {
		$uniset = $this->uniset;
		$lang_id = (int)$this->config->get('config_language_id');
		
		$result = [];
		
		if(isset($uniset['show_stock_indicator']) && $uniset['show_stock_indicator'] > 0) {
			$full = $options ? (int)$uniset['stock_indicator_full_opt'] : (int)$uniset['stock_indicator_full'];
				
			$stock = round((int)$quantity / (int)$full * 100, 0);
				
			$stock = $stock > 100 ? 100 : $stock;
			$stock = $stock < 1 ? 0.5 : $stock;
			
			switch($stock) {
				case ($stock >= 80):
					$title = $uniset[$lang_id]['stock_i_t_5'];
					$items = 5;
					break;
				case ($stock >= 60):
					$title = $uniset[$lang_id]['stock_i_t_4'];
					$items = 4;
					break;
				case ($stock >= 40):
					$title = $uniset[$lang_id]['stock_i_t_3'];
					$items = 3;
					break;
				case ($stock >= 20):
					$title = $uniset[$lang_id]['stock_i_t_2'];
					$items = 2;
					break;
				case ($stock >= 1):
					$title = $uniset[$lang_id]['stock_i_t_1'];
					$items = 1;
					break;
				default:
					$title = $uniset[$lang_id]['stock_i_t_0'];
					$items = 0;
			}
			
			$result = [
				'title' => $uniset['show_stock_indicator'] != 3 ? $title : $quantity, 
				'items' => $items, 
				'width' => $stock,
				'type'  => $uniset['show_stock_indicator']
			];
		}
		
		return $result;
	}
	
	private function getAdditionalImage($product_id, $img_width, $img_height) {
		$uniset = $this->uniset;
		
		$image = '';
		$limit = 10;
		
		if(isset($uniset['catalog']['addit_img']) && $uniset['catalog']['addit_img'] != 'disabled') {
			$query = $this->db->query("SELECT * FROM `".DB_PREFIX."product_image` WHERE product_id = '".(int)$product_id."' ORDER BY sort_order ASC LIMIT ".(int)$limit);

			$results = $query->rows;
			
			foreach($results as $key => $result) {
				$image .= $this->model_tool_image->resize($result['image'], $img_width, $img_height).($key+1 < count($results) ? '||' : '');
			}
		}
		
		return $image;
	}
	
	private function getSpecialDateEnd($product_id, $special, $product_page) {
		$uniset = $this->uniset;
		
		$date_end = '';
		
		if((isset($uniset['show_special_timer']) || $product_page) && $special) {
			$query = $this->db->query("SELECT date_end FROM `".DB_PREFIX."product_special` WHERE product_id = '".(int)$product_id."' AND customer_group_id = '".(int)$this->config->get('config_customer_group_id')."' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");
			$date_end = ($query->num_rows) && ($query->row['date_end'] != '0000-00-00') ? $query->row['date_end'] : '';
			
			if($query->num_rows && ($query->row['date_end'] != '0000-00-00')) {
				$date_end = $query->row['date_end'];
			}
		}
		
		return [
			'timer_end' 	 => isset($uniset['show_special_timer']) ? $date_end : '',
			'microdata_end'  => $product_page ? $date_end : '',
		];
	}
	
	private function getDiscounts($product_id, $tax_class_id) {
		$uniset = $this->uniset;

		$result = '';
		
		if(isset($uniset['liveprice'])) {
			$currency = $this->session->data['currency'];
			$store_id = (int)$this->config->get('config_store_id');
			$customer_group_id = (int)$this->config->get('config_customer_group_id');
			$customer_group_id = $customer_group_id > 0 ? $customer_group_id : 1;
			
			$cache_name = 'product.unishop.discount.'.$currency.'.'.$customer_group_id.'.'.$store_id;
			
			$discount = $this->cache->get($cache_name);
		
			if(!$discount) {
				$query = $this->db->query("SELECT product_id, quantity, price FROM ".DB_PREFIX."product_discount WHERE customer_group_id = '".$customer_group_id."' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC");

				$discount[0] = [];
				
				foreach($query->rows as $product) {
					$discount[$product['product_id']][] = array(
						'quantity' => $product['quantity'],
						'price'    => $this->tax->calculate($product['price'], $tax_class_id, $this->config->get('config_tax'))*$this->currency->getValue($currency),
					);
				}
		
				$this->cache->set($cache_name, $discount);
			}
			
			if(isset($discount[$product_id])) {
				$result = str_replace('"', "'", json_encode($discount[$product_id]));
			}
		}
		
		return $result;
	}
	
	private function getBestSellerSticker($product_id) {
		$uniset = $this->uniset;
		
		$store_id = (int)$this->config->get('config_store_id');
		
		$cache_name = 'unishop.sticker.bestseller.'.$store_id;
		
		$result = $this->cache->get($cache_name);
		
		if(!$result) {
			$query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total FROM `".DB_PREFIX."order_product` op LEFT JOIN `".DB_PREFIX."product` p ON (op.product_id = p.product_id) LEFT JOIN `".DB_PREFIX."order` o ON (op.order_id = o.order_id) WHERE o.order_status_id > '0' AND p.date_available <= NOW() AND p.status = '1' GROUP BY op.product_id");
			
			$result = [0];
			
			foreach($query->rows as $product) {
				if((int)$product['total'] >= $uniset['sticker_bestseller_item']) {
					$result[] = $product['product_id'];
				}
			}
			
			$this->cache->set($cache_name, $result);
		}
		
		if(in_array($product_id, $result)) {
			return true;
		} else {
			return false;
		}
	}
}
?>