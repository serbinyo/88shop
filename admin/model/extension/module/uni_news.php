<?php
class ModelExtensionModuleUniNews extends Model {

	public function addNews($data) {
		$this->db->query("INSERT INTO ".DB_PREFIX."uni_news_story SET status = '".(int)$data['status']."', date_added = '".$this->db->escape($data['date_added'])."'");

		$news_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE ".DB_PREFIX."uni_news_story SET image = '".$this->db->escape($data['image'])."' WHERE news_id = '".(int)$news_id."'");
		}

		foreach ($data['news_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_story_description` SET news_id = '".(int)$news_id."', language_id = '".(int)$language_id."', name = '".$this->db->escape($value['name'])."', description = '".$this->db->escape($value['description'])."', meta_description = '".$this->db->escape($value['meta_description'])."', meta_keyword = '".$this->db->escape($value['meta_keyword'])."', meta_title = '".$this->db->escape($value['meta_title'])."', meta_h1 = '".$this->db->escape($value['meta_h1'])."'");
		}
		
		if (isset($data['category_id'])) {
			$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_story_to_category` SET news_id = '".(int)$news_id."', category_id = '".(int)$data['category_id']."'");
		}

		if (isset($data['news_store'])) {
			foreach ($data['news_store'] as $store_id) {
				$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_story_to_store` SET news_id = '".(int)$news_id."', store_id = '".(int)$store_id."'");
			}
		}
		
		if (isset($data['related_products']) ) {
			foreach ($data['related_products'] as $product_id) {
				$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_product_related` SET news_id = '".(int)$news_id."', product_id = '".(int)$product_id."'");
			}
		}

		if (isset($data['news_seo_url'])) {
			foreach ($data['news_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->db->query("INSERT INTO `".DB_PREFIX."seo_url` SET store_id = '".(int)$store_id."', language_id = '".(int)$language_id."', query = 'news_id=".(int)$news_id."', keyword = '".$this->db->escape($keyword)."'");
					}
				}
			}
		}

		$this->cache->delete('unishop.news');
		
		if($this->config->get('config_seo_pro')){		
			$this->cache->delete('seopro');
		}
	}

	public function editNews($news_id, $data) {
		$this->db->query("UPDATE ".DB_PREFIX."uni_news_story SET date_added = '".$this->db->escape($data['date_added'])."', status = '".(int)$data['status']."' WHERE news_id = '".(int)$news_id."'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE ".DB_PREFIX."uni_news_story SET image = '".$this->db->escape($data['image'])."' WHERE news_id = '".(int)$news_id."'");
		}

		$this->db->query("DELETE FROM ".DB_PREFIX."uni_news_story_description WHERE news_id = '".(int)$news_id."'");

		foreach ($data['news_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_story_description` SET news_id = '".(int)$news_id."', language_id = '".(int)$language_id."', name = '".$this->db->escape($value['name'])."', description = '".$this->db->escape($value['description'])."', meta_description = '".$this->db->escape($value['meta_description'])."', meta_keyword = '".$this->db->escape($value['meta_keyword'])."', meta_title = '".$this->db->escape($value['meta_title'])."', meta_h1 = '".$this->db->escape($value['meta_h1'])."'");
		}
		
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_story_to_category` WHERE news_id = '".(int)$news_id."'");
		
		if (isset($data['category_id'])) {
			$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_story_to_category` SET news_id = '".(int)$news_id."', category_id = '".(int)$data['category_id']."'");
		}

		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_story_to_store` WHERE news_id = '".(int)$news_id."'");

		if (isset($data['news_store'])) {
			foreach ($data['news_store'] as $store_id) {
				$this->db->query("INSERT INTO ".DB_PREFIX."uni_news_story_to_store SET news_id = '".(int)$news_id."', store_id = '".(int)$store_id."'");
			}
		}
		
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_product_related` WHERE news_id = '".(int)$news_id."'");
		
		if (isset($data['related_products'])) {			
			foreach ($data['related_products'] as $product_id) {
				$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_product_related` SET news_id = '".(int)$news_id."', product_id = '".(int)$product_id."'");
			}
		}
		
		$this->db->query("DELETE FROM ".DB_PREFIX."seo_url WHERE query = 'news_id=".(int)$news_id."'");

		if (isset($data['news_seo_url'])) {
			foreach ($data['news_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->db->query("INSERT INTO `".DB_PREFIX ."seo_url` SET store_id = '".(int)$store_id."', language_id = '".(int)$language_id."', query = 'news_id=".(int)$news_id."', keyword = '".$this->db->escape($keyword)."'");
					}
				}
			}
		}

		$this->cache->delete('unishop.news');
		
		if($this->config->get('config_seo_pro')){		
			$this->cache->delete('seopro');
		}
	}

	public function deleteNews($news_id) {
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_story` WHERE news_id = '".(int)$news_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_story_description` WHERE news_id = '".(int)$news_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_story_to_category` WHERE news_id = '".(int)$news_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_story_to_store` WHERE news_id = '".(int)$news_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_product_related` WHERE news_id = '".(int)$news_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."seo_url` WHERE query = 'news_id=".(int)$news_id."'");

		$this->cache->delete('unishop.news');
	}

	public function resetViews($news_id) {
		$this->db->query("UPDATE ".DB_PREFIX."uni_news_story SET viewed = '0' WHERE news_id = '".(int)$news_id."'");

		$this->cache->delete('unishop.news');
	}
	
	public function getNewsStory($news_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM ".DB_PREFIX."uni_news_story n LEFT JOIN `".DB_PREFIX."uni_news_story_to_category` n2c ON (n.news_id = n2c.news_id) WHERE n.news_id = '".(int)$news_id."'");

		return $query->row;
	}

	public function getNews($data = array()) {
		
		$sql = "SELECT * FROM ".DB_PREFIX."uni_news_story n LEFT JOIN ".DB_PREFIX."uni_news_story_description nd ON (n.news_id = nd.news_id) WHERE nd.language_id = '".(int)$this->config->get('config_language_id')."'";

		$sort_data = array(
			'nd.name',
			'n.date_added',
			'n.viewed',
			'n.status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY ".$data['sort'];
		} else {
			$sql .= " ORDER BY n.date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		return $query->rows;
	}

	public function getNewsDescriptions($news_id) {
		$news_description_data = array();

		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."uni_news_story_description WHERE news_id = '".(int)$news_id."'");

		foreach ($query->rows as $result) {
			$news_description_data[$result['language_id']] = array(
				'name'            	=> $result['name'],
				'description'      	=> $result['description'],
				'meta_description' 	=> $result['meta_description'],
				'meta_keyword' 		=> $result['meta_keyword'],
				'meta_h1' 			=> $result['meta_h1'],
				'meta_title' 		=> $result['meta_title'],
			);
		}

		return $news_description_data;
	}
	
	public function getNewsRelatedProduct($news_id) {
		$products_related = array();
		
		$query = $this->db->query("SELECT product_id FROM ".DB_PREFIX."uni_news_product_related WHERE news_id = '".(int)$news_id."'");
		
		foreach ($query->rows as $result) {
			$products_related[] = $result['product_id'];
		}

		return $products_related;
	}

	public function getNewsStores($news_id) {
		$stores = array();

		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."uni_news_story_to_store WHERE news_id = '".(int)$news_id."'");

		foreach ($query->rows as $result) {
			$stores[] = $result['store_id'];
		}

		return $stores;
	}
	
	public function getNewsSeoUrls($news_id) {
		$seo_url = array();
		
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."seo_url WHERE query = 'news_id=".(int)$news_id."'");

		foreach ($query->rows as $result) {
			$seo_url[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $seo_url;
	}

	public function getTotalNews() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX."uni_news_story");

		return $query->row['total'];
	}
	
	public function addCategory($data) {
		$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_category` SET parent_id = '".(int)$data['parent_id']."', sort_order = '".(int)$data['sort_order']."', status = '".(int)$data['status']."'");

		$category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `".DB_PREFIX."uni_news_category` SET image = '".$this->db->escape($data['image'])."' WHERE category_id = '".(int)$category_id."'");
		}

		foreach ($data['description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_category_description` SET category_id = '".(int)$category_id."', language_id = '".(int)$language_id."', name = '".$this->db->escape($value['name'])."', meta_description = '".$this->db->escape($value['meta_description'])."', meta_keyword = '".$this->db->escape($value['meta_keyword'])."', description = '".$this->db->escape($value['description'])."', meta_title = '".$this->db->escape($value['meta_title'])."', meta_h1 = '".$this->db->escape($value['meta_h1'])."'");
		}

		if (isset($data['stores'])) {
			foreach ($data['stores'] as $store_id) {
				$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_category_to_store` SET category_id = '".(int)$category_id."', store_id = '".(int)$store_id."'");
			}
		}

		if (isset($data['seo_url'])) {
			foreach ($data['seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->db->query("INSERT INTO `".DB_PREFIX."seo_url` SET store_id = '".(int)$store_id."', language_id = '".(int)$language_id."', query = 'news_category_id=".(int)$category_id."', keyword = '".$this->db->escape($keyword)."'");
					}
				}
			}
		}
		
		$level = 0;

		$query = $this->db->query("SELECT * FROM `".DB_PREFIX."uni_news_category_path` WHERE category_id = '".(int)$data['parent_id']."' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_category_path` SET `category_id` = '".(int)$category_id."', `path_id` = '".(int)$result['path_id']."', `level` = '".(int)$level."'");

			$level++;
		}

		$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_category_path` SET `category_id` = '".(int)$category_id."', `path_id` = '".(int)$category_id."', `level` = '".(int)$level."'");

		$this->cache->delete('unishop.news.category');
		
		if($this->config->get('config_seo_pro')){		
			$this->cache->delete('seopro');
		}
	}

	public function editCategory($category_id, $data) {
		$this->db->query("UPDATE `".DB_PREFIX."uni_news_category` SET parent_id = '".(int)$data['parent_id']."', sort_order = '".(int)$data['sort_order']."', status = '".(int)$data['status']."' WHERE category_id = '".(int)$category_id."'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `".DB_PREFIX."uni_news_category` SET image = '".$this->db->escape($data['image'])."' WHERE category_id = '".(int)$category_id."'");
		}

		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_category_description` WHERE category_id = '".(int)$category_id."'");

		foreach ($data['description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_category_description` SET category_id = '".(int)$category_id."', language_id = '".(int)$language_id."', name = '".$this->db->escape($value['name'])."', meta_description = '".$this->db->escape($value['meta_description'])."', meta_keyword = '".$this->db->escape($value['meta_keyword'])."', description = '".$this->db->escape($value['description'])."', meta_title = '".$this->db->escape($value['meta_title'])."', meta_h1 = '".$this->db->escape($value['meta_h1'])."'");
		}

		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_category_to_store` WHERE category_id = '".(int)$category_id."'");

		if (isset($data['stores'])) {
			foreach ($data['stores'] as $store_id) {
				$this->db->query("INSERT INTO ".DB_PREFIX."uni_news_category_to_store SET category_id = '".(int)$category_id."', store_id = '".(int)$store_id."'");
			}
		}
		
		$this->db->query("DELETE FROM `".DB_PREFIX."seo_url` WHERE query = 'news_category_id=".(int)$category_id."'");

		if (isset($data['seo_url'])) {
			foreach ($data['seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->db->query("INSERT INTO `".DB_PREFIX ."seo_url` SET store_id = '".(int)$store_id."', language_id = '".(int)$language_id."', query = 'news_category_id=".(int)$category_id."', keyword = '".$this->db->escape($keyword)."'");
					}
				}
			}
		}
		
		$query = $this->db->query("SELECT * FROM `".DB_PREFIX."uni_news_category_path` WHERE path_id = '".(int)$category_id."' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {

				$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_category_path` WHERE category_id = '".(int)$category_path['category_id']."' AND level < '".(int)$category_path['level']."'");

				$path = array();

				$query = $this->db->query("SELECT * FROM `".DB_PREFIX."uni_news_category_path` WHERE category_id = '".(int)$data['parent_id']."' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				$query = $this->db->query("SELECT * FROM `".DB_PREFIX."uni_news_category_path` WHERE category_id = '".(int)$category_path['category_id']."' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `".DB_PREFIX."uni_news_category_path` SET category_id = '".(int)$category_path['category_id']."', `path_id` = '".(int)$path_id."', level = '".(int)$level."'");

					$level++;
				}
			}
		} else {
			$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_category_path` WHERE category_id = '".(int)$category_id."'");

			$level = 0;

			$query = $this->db->query("SELECT * FROM `".DB_PREFIX."uni_news_category_path` WHERE category_id = '".(int)$data['parent_id']."' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `".DB_PREFIX."uni_news_category_path` SET category_id = '".(int)$category_id."', `path_id` = '".(int)$result['path_id']."', level = '".(int)$level."'");

				$level++;
			}

			$this->db->query("REPLACE INTO `".DB_PREFIX."uni_news_category_path` SET category_id = '".(int)$category_id."', `path_id` = '".(int)$category_id."', level = '".(int)$level."'");
		}

		$this->cache->delete('unishop.news.category');
		
		if($this->config->get('config_seo_pro')){		
			$this->cache->delete('seopro');
		}
	}

	public function deleteCategory($category_id) {
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_category` WHERE category_id = '".(int)$category_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_category_description` WHERE category_id = '".(int)$category_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_news_category_to_store` WHERE category_id = '".(int)$category_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."seo_url` WHERE query = 'news_category_id = ".(int)$category_id."'");
		
		$this->db->query("DELETE FROM ".DB_PREFIX."uni_news_category_path WHERE category_id = '".(int)$category_id."'");

		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."uni_news_category_path WHERE path_id = '".(int)$category_id."'");

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['category_id']);
		}

		$this->cache->delete('unishop.news.category');
	}
	
	public function getCategories($data = array()) {
		
		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order FROM `".DB_PREFIX."uni_news_category_path` cp LEFT JOIN `".DB_PREFIX."uni_news_category` c1 ON (cp.category_id = c1.category_id) LEFT JOIN `".DB_PREFIX."uni_news_category` c2 ON (cp.path_id = c2.category_id) LEFT JOIN `".DB_PREFIX."uni_news_category_description` cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN `".DB_PREFIX."uni_news_category_description` cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '".(int)$this->config->get('config_language_id')."' AND cd2.language_id = '".(int)$this->config->get('config_language_id')."'";
		
		if (!empty($data['name'])) {
			$sql .= " AND cd2.name LIKE '%".$this->db->escape($data['name'])."%'";
		}
		
		$sql .= " GROUP BY cp.category_id";
		
		$sort_data = array(
			'name',
			'sort_order',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY ".$data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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

		return $query->rows;
	}
	
	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM `".DB_PREFIX."uni_news_category_path` cp LEFT JOIN `".DB_PREFIX."uni_news_category_description` cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '".(int)$this->config->get('config_language_id')."' GROUP BY cp.category_id) AS path FROM `".DB_PREFIX."uni_news_category` c LEFT JOIN `".DB_PREFIX."uni_news_category_description` cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = '".(int)$category_id."' AND cd2.language_id = '".(int)$this->config->get('config_language_id')."'");
		
		return $query->row;
	}
	
	public function getCategoryDescriptions($category_id) {
		$data = array();

		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."uni_news_category_description WHERE category_id = '".(int)$category_id."'");

		foreach ($query->rows as $result) {
			$data[$result['language_id']] = array(
				'name'            	=> $result['name'],
				'description'      	=> $result['description'],
				'meta_keyword'      => $result['meta_keyword'],
				'meta_description' 	=> $result['meta_description'],
				'meta_h1' 			=> $result['meta_h1'],
				'meta_title' 		=> $result['meta_title'],
			);
		}

		return $data;
	}
	
	public function getCategoryStores($category_id) {
		$stores = array();

		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."uni_news_category_to_store WHERE category_id = '".(int)$category_id."'");

		foreach ($query->rows as $result) {
			$stores[] = $result['store_id'];
		}

		return $stores;
	}
	
	public function getCategorySeoUrls($category_id) {
		$seo_url = array();
		
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."seo_url WHERE query = 'news_category_id=".(int)$category_id."'");

		foreach ($query->rows as $result) {
			$seo_url[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $seo_url;
	}
	
	public function getCategoryPath($category_id) {
		$query = $this->db->query("SELECT category_id, path_id, level FROM ".DB_PREFIX."uni_news_category_path WHERE category_id = '" . (int)$category_id . "'");

		return $query->rows;
	}
	
	public function getTotalCategory() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX."uni_news_category");

		return $query->row['total'];
	}

	public function install() { 		
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_news_story` (`news_id` int(11) NOT NULL AUTO_INCREMENT, `image` varchar(255) DEFAULT NULL, `date_added` datetime NOT NULL, `viewed` int(11) NOT NULL DEFAULT '0', `status` tinyint(1) NOT NULL, PRIMARY KEY (`news_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_news_story_description` (`news_id` int(11) NOT NULL, `language_id` int(11) NOT NULL, `name` varchar(255) NOT NULL, `description` text CHARACTER SET utf8 NOT NULL, `meta_description` VARCHAR(255) NOT NULL, `meta_keyword` varchar(255) NOT NULL,  PRIMARY KEY (`news_id`,`language_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_news_product_related` (`news_id` int(11) NOT NULL,  `product_id` int(11) NOT NULL, PRIMARY KEY (`news_id`,`product_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_news_story_to_category` (`news_id` int(11) NOT NULL, `category_id` int(11) NOT NULL, PRIMARY KEY (`news_id`,`category_id`), KEY `category_id` (`category_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_news_story_to_store` (`news_id` int(11) NOT NULL, `store_id` int(11) NOT NULL, PRIMARY KEY (`news_id`,`store_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_news_category` (`category_id` int(11) NOT NULL AUTO_INCREMENT, `image` varchar(255) DEFAULT NULL, `parent_id` int(11) NOT NULL DEFAULT '0', `sort_order` int(3) NOT NULL DEFAULT '0', `status` tinyint(1) NOT NULL, PRIMARY KEY (`category_id`), KEY `parent_id` (`parent_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_news_category_description` (`category_id` int(11) NOT NULL, `language_id` int(11) NOT NULL, `name` varchar(255) NOT NULL, `description` text NOT NULL, `meta_description` varchar(255) NOT NULL, `meta_keyword` varchar(255) NOT NULL, PRIMARY KEY (`category_id`,`language_id`), KEY `name` (`name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_news_category_path` (`category_id` int(11) NOT NULL, `path_id` int(11) NOT NULL, `level` int(11) NOT NULL, PRIMARY KEY (`category_id`,`path_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_news_category_to_store` (`category_id` int(11) NOT NULL, `store_id` int(11) NOT NULL, PRIMARY KEY (`category_id`,`store_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		
		
		$query = $this->db->query("SELECT layout_id FROM `".DB_PREFIX."layout` WHERE `name` LIKE 'News' LIMIT 1");
		if ($query->num_rows == 0) {
			$this->db->query("INSERT INTO `".DB_PREFIX."layout` SET `name`= 'News'");
		}

		$stores = array(0);

		$sql = "SELECT store_id FROM `".DB_PREFIX."store`";

		$query_store = $this->db->query($sql);

		foreach ($query_store->rows as $store) {
			$stores[] = $store['store_id'];
		}

		$newRoutes = array('information/uni_news');

		foreach ($newRoutes as $newRoute) {
			foreach ($stores as $store_id) {
				$sql = "SELECT layout_id FROM `".DB_PREFIX."layout_route` WHERE `store_id`= '".(int)$store_id."' AND `route` LIKE '".$newRoute."' LIMIT 1";

				$query = $this->db->query($sql);

				if ($query->num_rows == 0) {
					$this->db->query("INSERT INTO `".DB_PREFIX."layout_route` SET `layout_id`= (SELECT layout_id FROM `".DB_PREFIX."layout` WHERE `name` LIKE 'News' LIMIT 1), `store_id`='".(int)$store_id."', `route`='".$newRoute."'");
				}
			}
		}
		
		$this->upgrade();
		$this->upgrade2();
		$this->upgrade3();
	}
	
	private function upgrade() {
		
		$query = $this->db->query("show tables FROM `".DB_DATABASE."` LIKE '".DB_PREFIX."uni_news'");

		if ($query->num_rows) {
			$this->load->model('localisation/language');
			$languages = $this->model_localisation_language->getLanguages();
		
			$description = $seo_url = array();
			
			foreach($languages as $language) {
				$description[$language['language_id']]['name'] = 'Новости';
				$description[$language['language_id']]['description'] = '';
				$description[$language['language_id']]['meta_description'] = '';
				$description[$language['language_id']]['meta_keyword'] = '';
				$seo_url[0][$language['language_id']] = 'news';
			}
		
			$data = array(
				'description'	=> $description,
				'parent_id' 	=> 0,
				'sort_order'	=> 0,
				'status' 		=> 1,
				'stores'		=> array(0),
				'seo_url' 		=> $seo_url
			);
		
			$this->addCategory($data);
		
			$query = $this->db->query("SELECT DISTINCT * FROM ".DB_PREFIX."uni_news");
		
			foreach($query->rows as $result) {
				$this->db->query("INSERT INTO ".DB_PREFIX."uni_news_story SET news_id = '".(int)$result['news_id']."', image = '".$this->db->escape($result['image'])."', date_added = '".$this->db->escape($result['date_added'])."', viewed = '".(int)$result['viewed']."', status = '".(int)$result['status']."'");
				$this->db->query("INSERT INTO ".DB_PREFIX."uni_news_story_to_category SET news_id = '".(int)$result['news_id']."', category_id = '1'");
			}
		
			$query2 = $this->db->query("show columns FROM `".DB_PREFIX."uni_news` WHERE Field = 'related_products'");

			if ($query2->num_rows) {
				foreach($query->rows as $result) {
					if($result['related_products']) {
						foreach(json_decode($result['related_products'], true) as $product_id) {
							$this->db->query("INSERT INTO ".DB_PREFIX."uni_news_product_related SET news_id = '".(int)$result['news_id']."', product_id = '".$product_id."'");
						}
					}
				}
				
				$this->db->query("ALTER TABLE `".DB_PREFIX."uni_news` DROP related_products");
			}
		
			$query = $this->db->query("SELECT DISTINCT * FROM ".DB_PREFIX."uni_news_description");
		
			foreach($query->rows as $result) {
				$this->db->query("INSERT INTO ".DB_PREFIX."uni_news_story_description SET news_id = '".(int)$result['news_id']."', language_id = '".(int)$result['language_id']."', name = '".$this->db->escape($result['title'])."', description = '".$this->db->escape($result['description'])."', meta_description = '".$this->db->escape($result['meta_description'])."', meta_keyword = '".$this->db->escape($result['keyword'])."'");
			}
		
			$query = $this->db->query("SELECT DISTINCT * FROM ".DB_PREFIX."uni_news_to_store");
		
			foreach($query->rows as $result) {
				$this->db->query("INSERT INTO ".DB_PREFIX."uni_news_story_to_store SET news_id = '".(int)$result['news_id']."', store_id = '".(int)$result['store_id']."'");
			}
		
			$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."uni_news`");
			$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."uni_news_description`");
			$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."uni_news_to_store`");
		
			$query = $this->db->query("SELECT query FROM `".DB_PREFIX."seo_url` WHERE `keyword` LIKE 'news' AND query != 'news_category_id=1' LIMIT 10");
		
			if ($query->num_rows) {
				foreach ($query->rows as $result) {
					$this->db->query("DELETE FROM ".DB_PREFIX."seo_url WHERE query = '".$result['query']."'");
				}
			}
		}
	}
	
	private function upgrade2() {
		$query = $this->db->query("show columns FROM `".DB_PREFIX."uni_news_story_description` WHERE Field = 'meta_h1' OR Field = 'meta_title'");
		
		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `".DB_PREFIX."uni_news_story_description` ADD meta_h1 varchar(255) NOT NULL AFTER meta_keyword, ADD meta_title varchar(255) NOT NULL AFTER meta_keyword");
		}
		
		$query = $this->db->query("show columns FROM `".DB_PREFIX."uni_news_category_description` WHERE Field = 'meta_h1' OR Field = 'meta_title'");
		
		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `".DB_PREFIX."uni_news_category_description` ADD meta_h1 varchar(255) NOT NULL AFTER meta_keyword, ADD meta_title varchar(255) NOT NULL AFTER meta_keyword");
		}
	}
	
	private function upgrade3() {
		
		$query = $this->db->query("show COLUMNS FROM `".DB_PREFIX."uni_news_story` WHERE Field = 'date_added'");
		
		$result = $query->row;
		
		if($result['Type'] == 'date') {
			$query = $this->db->query("SELECT news_id, date_added FROM ".DB_PREFIX."uni_news_story");
		
			$dates = $query->rows;
		
			$t = '10';
		
			$this->db->query("ALTER TABLE `".DB_PREFIX."uni_news_story` CHANGE COLUMN `date_added` `date_added` datetime");
		
			foreach($dates as $date) {
				$news_date = $date['date_added'] . ' 10:'.$t.':00';
			
				$this->db->query("UPDATE ".DB_PREFIX."uni_news_story SET date_added = '".$this->db->escape($news_date)."' WHERE news_id = '".(int)$date['news_id']."'");

				$t = ($t <= 20) ? $t+1 : 10;
			}
		}
	}
}
?>