<?php
class ModelExtensionModuleUniReviews extends Model
{
    public function getAllReviews($start = 0, $limit = 12, $page) {
		
		$cache_name = 'unishop.reviews.all.'.(int)$page;
		
		$reviews_data = $this->cache->get($cache_name);

		if(!$reviews_data) {
			$sql = "SELECT r.review_id, r.author, r.rating, r.text, p.product_id, pd.name, p.price, p.image, r.date_added FROM ".DB_PREFIX."review r LEFT JOIN `".DB_PREFIX."product` p ON (r.product_id = p.product_id) LEFT JOIN `".DB_PREFIX."product_description` pd ON (p.product_id = pd.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.date_added DESC";
			
			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = 12;
			}
	
			$sql .= " LIMIT ".(int)$start.", ".(int)$limit;
			
			$query = $this->db->query($sql);
			
			$reviews_data = $query->rows;
			
			$this->cache->set($cache_name, $reviews_data);
		}
		
		return $reviews_data;
    }

    public function getTotalReviews() {
		$reviews_data = $this->cache->get('unishop.reviews.total');

		if(!$reviews_data) {
			$query = $this->db->query("SELECT COUNT(*) AS total FROM `".DB_PREFIX."review` r LEFT JOIN `".DB_PREFIX."product` p ON (r.product_id = p.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1'");
			$reviews_data = $query->row['total'];
			$this->cache->set('unishop.reviews.total', $reviews_data);
		}
		
		return $reviews_data;
    }

    public function getLatestReviews($limit = 5, $category_id = 0) {
		$reviews_data = $this->cache->get('unishop.reviews.latest.'.(int)$category_id);
		
		if(!$reviews_data) {
			$reviews_data = $this->getReviews($limit, $category_id, FALSE);
			$this->cache->set('unishop.reviews.latest.'.(int)$category_id, $reviews_data);
		}
		
		return $reviews_data;
    }

    public function getRandomReviews($limit = 5, $category_id = 0) {
        $reviews_data = $this->cache->get('unishop.reviews.random.'.(int)$category_id);

		if(!$reviews_data) {
			$reviews_data = $this->getReviews($limit, $category_id, TRUE);
			$this->cache->set('unishop.reviews.random.'.(int)$category_id, $reviews_data);
		}
		
		return $reviews_data;
    }

    private function getReviews($limit, $category_id, $random) {	
	
		$sql = "SELECT DISTINCT r.author, r.rating, r.text, r.date_added, p.product_id, pd.name, p.price, p.image FROM ".DB_PREFIX."review r LEFT JOIN ".DB_PREFIX."product p ON (r.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON (p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."product_to_store p2s ON (p.product_id = p2s.product_id)";
		$sql .= $category_id != 0 ? " LEFT JOIN ".DB_PREFIX."product_to_category p2c ON (p.product_id = p2c.product_id)" : "";
		$sql .= " WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";
		$sql .= $category_id != 0 ? " AND p2c.category_id = '".(int)$category_id."'" : "";
        $sql .= $random ? " ORDER BY RAND() " : " ORDER BY date_added DESC";
        $sql .= " LIMIT ".(int)$limit;

		$query = $this->db->query($sql);

        return $query->rows;
    }
}
?>