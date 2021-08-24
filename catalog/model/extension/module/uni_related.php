<?php
class ModelExtensionModuleUniRelated extends Model {	
	public function getAutoRelated($data) {
		
		$product_id = (int)$data['product_id'];
		$limit = (int)$data['limit'];
		$main_category = (int)$data['main_category'];
		$stock = $data['stock'];
		
		$result = $this->getAutoRelatedProducts($product_id, $limit, $main_category, $stock, '>');
			
		if(count($result) < (int)$limit) {
			$result = $this->getAutoRelatedProducts($product_id, $limit, $main_category, $stock, '<>');
		}
			
		$result = array_slice($result, 0, $limit);
			
		$product_data = [];
			
		foreach ($result as $product_id) {
			$product_data[] = $this->model_catalog_product->getProduct((int)$product_id);
		}

		return $product_data;
	}
	
	private function getAutoRelatedProducts($product_id, $limit, $main_category, $stock, $sign) {
		$result = [];
		
		$sql = "SELECT category_id FROM `".DB_PREFIX."product_to_category` WHERE product_id = '".(int)$product_id."'";
		
		if($main_category) {
			$query = $this->db->query("show columns FROM `".DB_PREFIX."product_to_category` WHERE Field = 'main_category'");
			
			if ($query->num_rows) {
				$sql .= " AND main_category = '1'";
			}
		}
			
		$category = $this->db->query($sql);
			
		if($category->rows) {
			foreach ($category->rows as $category) {
				$sql = "SELECT p.product_id FROM ".DB_PREFIX."product p LEFT JOIN `".DB_PREFIX."product_to_category` p2c ON (p.product_id = p2c.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2c.category_id = '".(int)$category['category_id']."'";
				$stock ? $sql .=" AND p.quantity > '0'" : '';
				$sql .="  AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."' AND p.product_id ".$this->db->escape($sign)." '".(int)$product_id."' GROUP BY p.product_id ORDER BY p.product_id ASC LIMIT ".(int)$limit;
				$query = $this->db->query($sql);
				
				foreach($query->rows as $product) {
					if(!in_array($product['product_id'], $result)) {
						$result[] = $product['product_id'];
					}
				}
			}
		}
		
		return $result;
	}
	
	public function getRelated() {
		$uniset = $this->config->get('config_unishop2');
		
		$product_data = $results = $in_cart = [];
		
		if($this->cart->getProducts()) {
			
			$related1 = isset($uniset['checkout_related_product1']) ? true : '';
			$related2 = isset($uniset['checkout_related_product2']) ? true : '';	
			
			foreach($this->cart->getProducts() as $result) {			
				if ($related1) {
					$result1 = $this->getRelated1($result['product_id']);
					
					if($result1) {
						$results = array_merge($results, $result1);
					}
				} 
				
				if($related2) {
					$result2 = $this->getRelated2($result['product_id']);
					
					if($result2) {
						$results = array_merge($results, $result2);
					}
				}
				
				$in_cart[] = $result['product_id'];
			}
			
			$products = array_unique(array_diff($results, $in_cart));
			
			foreach ($products as $product_id) {
				$product_data[] = $this->model_catalog_product->getProduct((int)$product_id);
			}
		}
		
		return $product_data;
	}
	
	public function getRelated1($product_id) {
		$product_data = [];
		$limit = 10;

		$query = $this->db->query("SELECT pr.related_id FROM ".DB_PREFIX."product_related pr LEFT JOIN ".DB_PREFIX."product p ON (pr.related_id = p.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '".(int)$product_id."' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."' LIMIT ".(int)$limit);
		
		if($query->rows) {
			$product_data = array_column($query->rows, 'related_id');
		}
		
		return $product_data;
	}

	public function getRelated2($product_id) {
		$product_data = [];
		$limit = 5;
		$limit2 = 2;
		
		$query = $this->db->query("SELECT op.product_id FROM `".DB_PREFIX."order_product` op LEFT JOIN `".DB_PREFIX."product` p ON (op.product_id = p.product_id) JOIN (SELECT op.order_id FROM `".DB_PREFIX."order_product` op JOIN `".DB_PREFIX."order` o ON (op.order_id = o.order_id) WHERE o.order_status_id > '0' AND op.product_id = '".(int)$product_id."' AND o.store_id = '".(int)$this->config->get('config_store_id')."' LIMIT ".(int)$limit.") mp ON (op.order_id = mp.order_id) WHERE 1 AND op.product_id != '".(int)$product_id."' AND p.status = '1' AND p.date_available <= NOW() LIMIT ".(int)$limit2);
		
		if($query->rows) {
			$product_data = array_column($query->rows, 'product_id');
		}
		
		return $product_data;
	}
}
?>