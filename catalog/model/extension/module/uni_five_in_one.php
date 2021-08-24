<?php
class ModelExtensionModuleUniFiveInOne extends Model {	
	public function getLatest($limit, $qty) {
		$products = [];
		
		//$query = $this->db->query("SELECT p.product_id FROM `".DB_PREFIX."product` p LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."' ORDER BY p.date_added DESC LIMIT ".(int)$limit);

		$sql = "SELECT p.product_id FROM `".DB_PREFIX."product` p LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1'";
		$sql .= $qty ? " AND p.quantity > 0" : '';
		$sql .= " AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."'";
		$sql .= " ORDER BY p.date_added DESC LIMIT ".(int)$limit;
		
		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$products[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $products;
	}
	
	public function getSpecial($limit, $qty) {
		$products = [];
		
		//$query = $this->db->query("SELECT DISTINCT ps.product_id FROM `".DB_PREFIX."product_special` ps LEFT JOIN `".DB_PREFIX."product` p ON (ps.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."' AND ps.customer_group_id = '".(int)$this->config->get('config_customer_group_id')."' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id LIMIT ".(int)$limit);
		
		$sql = "SELECT DISTINCT ps.product_id FROM `".DB_PREFIX."product_special` ps LEFT JOIN `".DB_PREFIX."product` p ON (ps.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1'";
		$sql .= $qty ? " AND p.quantity > 0" : '';
		$sql .= " AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."' AND ps.customer_group_id = '".(int)$this->config->get('config_customer_group_id')."'";
		$sql .= " AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))";
		$sql .= " GROUP BY ps.product_id LIMIT ".(int)$limit;
		
		$query = $this->db->query($sql);
		
		foreach ($query->rows as $result) {
			$products[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $products;
	}
	
	public function getPopular($limit, $qty) {
		$products = [];
		
		//$query = $this->db->query("SELECT p.product_id FROM `".DB_PREFIX."product` p LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."' ORDER BY p.viewed DESC, p.date_added DESC LIMIT ".(int)$limit);
		
		$sql = "SELECT p.product_id FROM `".DB_PREFIX."product` p LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1'";
		$sql .= $qty ? " AND p.quantity > 0" : '';
		$sql .= " AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."'";
		$sql .= " ORDER BY p.viewed DESC, p.date_added DESC LIMIT ".(int)$limit;
		
		$query = $this->db->query($sql);
		
		foreach ($query->rows as $result) {
			$products[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $products;
	}
	
	public function getBestseller($limit, $qty) {
		$products = [];
		
		//$query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total FROM `".DB_PREFIX."order_product` op LEFT JOIN `".DB_PREFIX."order` o ON (op.order_id = o.order_id) LEFT JOIN `".DB_PREFIX."product` p ON (op.product_id = p.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."' GROUP BY op.product_id ORDER BY total DESC LIMIT ".(int)$limit);
		
		$sql = "SELECT op.product_id, SUM(op.quantity) AS total FROM `".DB_PREFIX."order_product` op LEFT JOIN `".DB_PREFIX."order` o ON (op.order_id = o.order_id) LEFT JOIN `".DB_PREFIX."product` p ON (op.product_id = p.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0'";
		$sql .= $qty ? " AND p.quantity > 0" : '';
		$sql .= " AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."'";
		$sql .= " GROUP BY op.product_id ORDER BY total DESC LIMIT ".(int)$limit;
		
		$query = $this->db->query($sql);
		
		foreach ($query->rows as $result) {
			$products[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $products;
	}
	
	public function getFeatured($results, $qty = '') {
		$products = [];
		
		if($results && $qty) {
			$query = $this->db->query("SELECT product_id FROM `".DB_PREFIX."product` WHERE product_id in (".$this->db->escape(implode(',', $results)).") AND quantity > 0");
			
			$results = array_column($query->rows, 'product_id');
		}
		
		foreach ($results as $product_id) {
			$products[$product_id] = $this->model_catalog_product->getProduct($product_id);
		}

		return $products;
	}
	
	public function getProductsFromCategory($category_id, $limit, $qty) {
		$products = [];
		
		$sql = "SELECT p2c.product_id FROM `".DB_PREFIX."product_to_category` p2c LEFT JOIN `".DB_PREFIX."product` p ON (p.product_id = p2c.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1'";
		$sql .= " AND p2c.category_id = '".(int)$category_id."'";
		$sql .= $qty ? " AND p.quantity > 0" : '';
		$sql .= " AND p.date_available <= NOW() AND p2s.store_id = '".(int)$this->config->get('config_store_id')."'";
		$sql .= " ORDER BY p.date_added DESC LIMIT ".(int)$limit;
		
		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$products[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $products;
	}
}
?>