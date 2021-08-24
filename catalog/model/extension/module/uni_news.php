<?php
class ModelExtensionModuleUniNews extends Model {
	
	public function getCategories($parent_id = 0) {
		$query = $this->db->query("SELECT * FROM `".DB_PREFIX."uni_news_category` c LEFT JOIN ".DB_PREFIX."uni_news_category_description cd ON (c.category_id = cd.category_id) LEFT JOIN `".DB_PREFIX."uni_news_category_to_store` c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '".(int)$parent_id."' AND cd.language_id = '".(int)$this->config->get('config_language_id')."' AND c2s.store_id = '".(int)$this->config->get('config_store_id')."'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");
		return $query->rows;
	}
	
	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX."uni_news_category` c LEFT JOIN ".DB_PREFIX."uni_news_category_description cd ON (c.category_id = cd.category_id) LEFT JOIN `".DB_PREFIX."uni_news_category_to_store` c2s ON (c.category_id = c2s.category_id) WHERE c.category_id = '".(int)$category_id."' AND cd.language_id = '".(int)$this->config->get('config_language_id')."' AND c2s.store_id = '".(int)$this->config->get('config_store_id')."' AND c.status = '1'");
		return $query->row;
	}

	public function updateViewed($news_id) { 
		$this->db->query("UPDATE ".DB_PREFIX."uni_news_story SET viewed = (viewed + 1) WHERE news_id = '".(int)$news_id."'");
	}
	
	public function getNews($data) {
		$language_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		
		$news_data = [];
		
		$sql = "SELECT *";
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM ".DB_PREFIX."uni_news_category_path cp LEFT JOIN ".DB_PREFIX."uni_news_story_to_category n2c ON (cp.category_id = n2c.category_id)";
			} else {
				$sql .= " FROM ".DB_PREFIX."uni_news_story_to_category n2c";
			}
			
			$sql .= " LEFT JOIN `".DB_PREFIX."uni_news_story` n ON (n2c.news_id = n.news_id)";
		} else {
			$sql .= " FROM `".DB_PREFIX."uni_news_story` n";
		}

		$sql .= " LEFT JOIN `".DB_PREFIX."uni_news_story_to_category` nc ON (n.news_id = nc.news_id)";
		$sql .= " LEFT JOIN `".DB_PREFIX."uni_news_story_description` nd ON (n.news_id = nd.news_id)";
		$sql .= " LEFT JOIN `".DB_PREFIX."uni_news_story_to_store` n2s ON (n.news_id = n2s.news_id)";
		$sql .= " WHERE nd.language_id = '".$language_id."' AND n2s.store_id = '".$store_id."' AND n.date_added <= NOW() AND n.status = '1'";	
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND LCASE(nd.name) LIKE '".$this->db->escape(utf8_strtolower($data['filter_name']))."%'";
		}
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '".(int)$data['filter_category_id']."'";
			} else {
				$sql .= " AND n2c.category_id = '".(int)$data['filter_category_id']."'";
			}
		}

		$sort_data = array(
			'nd.name',
			'nd.description',
			'n.date_added',
			'n.viewed',
			'n.status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY ".$data['sort'];
		} else {
			$sql .= " ORDER BY n.date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC, nd.name ASC";
		} else {
			$sql .= " DESC, nd.name DESC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}
	
			$sql .= " LIMIT ".(int)$data['start'].",".(int)$data['limit'];
		}

		$query = $this->db->query($sql);
		
		if($query->rows) {
			$news_data = $query->rows;
		}
			
		return $news_data;
	}

	public function getNewsStory($news_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM ".DB_PREFIX."uni_news_story n LEFT JOIN ".DB_PREFIX."uni_news_story_description nd ON (n.news_id = nd.news_id) LEFT JOIN `".DB_PREFIX."uni_news_story_to_store` n2s ON (n.news_id = n2s.news_id) LEFT JOIN `".DB_PREFIX."uni_news_story_to_category` nc ON (n.news_id = nc.news_id) WHERE n.news_id = '".(int)$news_id."' AND nd.language_id = '".(int)$this->config->get('config_language_id')."' AND n2s.store_id = '".(int)$this->config->get('config_store_id')."' AND n.status = '1'");
		
		return $query->row;
	}
	
	public function getNewsStoryRelatedProduct($news_id) {
		$products_related = [];
		
		$query = $this->db->query("SELECT nr.product_id FROM ".DB_PREFIX."uni_news_product_related nr LEFT JOIN `".DB_PREFIX."product` p ON (nr.product_id = p.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE news_id = '".(int)$news_id."' AND p2s.store_id = '".(int)$this->config->get('config_store_id')."' AND p.status = '1' AND p.date_available <= NOW()");
		
		foreach ($query->rows as $result) {
			$products_related[] = $this->model_catalog_product->getProduct((int)$result['product_id']);
		}

		return $products_related;
	}
	
	public function getNewsStoryforRelatedProduct($product_id) {
		$news_related = [];
		
		$query = $this->db->query("SELECT nr.news_id FROM `".DB_PREFIX."uni_news_product_related` nr LEFT JOIN `".DB_PREFIX."uni_news_story` n ON (nr.news_id = n.news_id) LEFT JOIN `".DB_PREFIX."uni_news_story_to_store` n2s ON (n.news_id = n2s.news_id) WHERE nr.product_id = '".(int)$product_id."' AND n2s.store_id = '".(int)$this->config->get('config_store_id')."' AND n.status = '1' ORDER BY n.date_added DESC");
		
		foreach ($query->rows as $result) {
			$news_related[] = $this->getNewsStory((int)$result['news_id']);
		}

		return $news_related;
	}

	public function getTotalNews($data) {
		$sql = "SELECT COUNT(DISTINCT n.news_id) AS total";
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM ".DB_PREFIX."uni_news_category_path cp LEFT JOIN ".DB_PREFIX."uni_news_story_to_category n2c ON (cp.category_id = n2c.category_id)";
			} else {
				$sql .= " FROM ".DB_PREFIX."uni_news_story_to_category n2c";
			}
			
			$sql .= " LEFT JOIN ".DB_PREFIX."uni_news_story n ON (n2c.news_id = n.news_id)";
			
		} else {
			$sql .= " FROM ".DB_PREFIX."uni_news_story n";
		}
		
		$sql .= " LEFT JOIN ".DB_PREFIX."uni_news_story_to_store n2s ON (n.news_id = n2s.news_id) WHERE n2s.store_id = '".(int)$this->config->get('config_store_id')."' AND n.status = '1'";
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '".(int)$data['filter_category_id']."'";
			} else {
				$sql .= " AND n2c.category_id = '".(int)$data['filter_category_id']."'";
			}
		}
		
		$query = $this->db->query($sql);
		
		return $query->row ? $query->row['total'] : false;
	}
	
	public function getPrevNextNews($news_id, $date_added, $category_id) {
		$language_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		$result = [];
			
		if($category_id) {
			$types['prev'] = ['sign' => '<', 'sort' => 'DESC'];
			$types['next'] = ['sign' => '>', 'sort' => 'ASC'];
			
			foreach($types as $key => $type) {
				$sql = "SELECT n.news_id, n.date_added, nd.name FROM `".DB_PREFIX."uni_news_story` n LEFT JOIN `".DB_PREFIX."uni_news_story_to_category` n2c ON (n.news_id = n2c.news_id) LEFT JOIN `".DB_PREFIX."uni_news_story_description` nd ON (n.news_id = nd.news_id) LEFT JOIN `".DB_PREFIX."uni_news_story_to_store` n2s ON (n.news_id = n2s.news_id)";
				$sql .= " WHERE n2c.category_id = '".(int)$category_id."' AND n.date_added ".$this->db->escape($type['sign'])." '".$this->db->escape($date_added)."' AND n.status = '1' AND nd.language_id = '".$language_id."' AND n2s.store_id = '".(int)$store_id."'";
				$sql .= " GROUP BY n.news_id ORDER BY n.date_added ".$this->db->escape($type['sort'])." LIMIT 1";
				
				$query = $this->db->query($sql);
				
				$result[$key] = $query->row;
			}
		}
		
		return $result;
	}
}
?>