<?php
class ModelExtensionModuleUniBannerInCategory extends Model {

	public function addBanner($data) {
		$this->db->query("INSERT INTO `".DB_PREFIX."uni_banner_in_category` SET type = '".(int)$data['type']."', width = '".(int)$data['width']."', height = '".(int)$data['height']."', position = '".(int)$data['position']."', position2 = '".(int)$data['position2']."', date_start = '".$this->db->escape($data['date_start'])."', date_end = '".$this->db->escape($data['date_end'])."', status = '".(int)$data['status']."'");

		$banner_id = $this->db->getLastId();

		foreach ($data['description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `".DB_PREFIX."uni_banner_in_category_description` SET banner_id = '".(int)$banner_id."', language_id = '".(int)$language_id."', name = '".$this->db->escape($value['name'])."', description = '".$this->db->escape($value['description'])."', image = '".$this->db->escape($value['image'])."', button = '".$this->db->escape($value['button'])."', link = '".$this->db->escape($value['link'])."'");
		}
		
		if (isset($data['categories'])) {
			foreach ($data['categories'] as $category_id) {
				$this->db->query("INSERT INTO `".DB_PREFIX."uni_banner_in_category_to_category` SET banner_id = '".(int)$banner_id."', category_id = '".(int)$category_id."'");
			}
		}

		if (isset($data['stores'])) {
			foreach ($data['stores'] as $store_id) {
				$this->db->query("INSERT INTO `".DB_PREFIX."uni_banner_in_category_to_store` SET banner_id = '".(int)$banner_id."', store_id = '".(int)$store_id."'");
			}
		}
		
		$this->cache->delete('unishop.banner');
	}

	public function editBanner($banner_id, $data) {
		$this->db->query("UPDATE `".DB_PREFIX."uni_banner_in_category` SET type = '".(int)$data['type']."', width = '".(int)$data['width']."', height = '".(int)$data['height']."', position = '".(int)$data['position']."', position2 = '".(int)$data['position2']."', date_start = '".$this->db->escape($data['date_start'])."', date_end = '".$this->db->escape($data['date_end'])."', status = '".(int)$data['status']."' WHERE banner_id = '".(int)$banner_id."'");

		$this->db->query("DELETE FROM `".DB_PREFIX."uni_banner_in_category_description` WHERE banner_id = '".(int)$banner_id."'");

		foreach ($data['description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `".DB_PREFIX."uni_banner_in_category_description` SET banner_id = '".(int)$banner_id."', language_id = '".(int)$language_id."', name = '".$this->db->escape($value['name'])."', description = '".$this->db->escape($value['description'])."', image = '".$this->db->escape($value['image'])."', button = '".$this->db->escape($value['button'])."', link = '".$this->db->escape($value['link'])."'");
		}
		
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_banner_in_category_to_category` WHERE banner_id = '" . (int)$banner_id . "'");
		
		if (isset($data['categories'])) {
			foreach ($data['categories'] as $category_id) {
				$this->db->query("INSERT INTO `".DB_PREFIX."uni_banner_in_category_to_category` SET banner_id = '".(int)$banner_id."', category_id = '".(int)$category_id."'");
			}
		}
		
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_banner_in_category_to_store` WHERE banner_id = '" . (int)$banner_id . "'");

		if (isset($data['stores'])) {
			foreach ($data['stores'] as $store_id) {
				$this->db->query("INSERT INTO `".DB_PREFIX."uni_banner_in_category_to_store` SET banner_id = '".(int)$banner_id."', store_id = '".(int)$store_id."'");
			}
		}
		
		$this->cache->delete('unishop.banner');
	}

	public function deleteBanner($banner_id) {
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_banner_in_category` WHERE banner_id = '".(int)$banner_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_banner_in_category_description` WHERE banner_id = '".(int)$banner_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_banner_in_category_to_category` WHERE banner_id = '".(int)$banner_id."'");
		$this->db->query("DELETE FROM `".DB_PREFIX."uni_banner_in_category_to_store` WHERE banner_id = '".(int)$banner_id."'");
		
		$this->cache->delete('unishop.banner');
	}
	
	public function getBanner($banner_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `".DB_PREFIX."uni_banner_in_category` WHERE banner_id = '".(int)$banner_id."'");

		return $query->row;
	}

	public function getBanners($data = []) {
		
		$sql = "SELECT * FROM `".DB_PREFIX."uni_banner_in_category` b LEFT JOIN `".DB_PREFIX."uni_banner_in_category_description` bd ON (b.banner_id = bd.banner_id) WHERE bd.language_id = '".(int)$this->config->get('config_language_id')."'";
		$sql .= " ORDER BY b.banner_id ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getBannerDescription($banner_id) {
		$description = [];

		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."uni_banner_in_category_description WHERE banner_id = '".(int)$banner_id."'");

		foreach ($query->rows as $result) {
			$description[$result['language_id']] = [
				'name'            	=> $result['name'],
				'description'      	=> $result['description'],
				'image' 			=> $result['image'],
				'button' 			=> $result['button'],
				'link' 				=> $result['link'],
			];
		}

		return $description;
	}
	
	public function getBannerCategories($banner_id) {
		$category = [];
		
		$query = $this->db->query("SELECT * FROM `".DB_PREFIX."uni_banner_in_category_to_category` WHERE banner_id = '" . (int)$banner_id . "'");

		foreach ($query->rows as $result) {
			$category[] = $result['category_id'];
		}

		return $category;
	}
	
	public function getBannerStores($banner_id) {
		$store = [];

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "uni_banner_in_category_to_store WHERE banner_id = '" . (int)$banner_id . "'");

		foreach ($query->rows as $result) {
			$store[] = $result['store_id'];
		}

		return $store;
	}

	public function install() { 		
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_banner_in_category` (`banner_id` int(11) NOT NULL AUTO_INCREMENT, `type` tinyint(2) NOT NULL DEFAULT '1', `width` int(11) NOT NULL, `height` int(11) NOT NULL, `position` int(11) NOT NULL, `position2` int(11) NOT NULL, `date_start` date NOT NULL DEFAULT '0000-00-00', `date_end` date NOT NULL DEFAULT '0000-00-00', `status` tinyint(1) NOT NULL, PRIMARY KEY (`banner_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_banner_in_category_description` (`banner_id` int(11) NOT NULL, `language_id` int(11) NOT NULL, `name` varchar(255) NOT NULL, `image` varchar(255) NOT NULL, `description` text CHARACTER SET utf8 NOT NULL, `button` VARCHAR(255) NOT NULL, `link` varchar(255) NOT NULL,  PRIMARY KEY (`banner_id`,`language_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_banner_in_category_to_category` (`banner_id` int(11) NOT NULL, `category_id` int(11) NOT NULL, PRIMARY KEY (`banner_id`,`category_id`), KEY `category_id` (`category_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."uni_banner_in_category_to_store` (`banner_id` int(11) NOT NULL, `store_id` int(11) NOT NULL, PRIMARY KEY (`banner_id`,`store_id`)) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		
	}
	
	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."uni_banner_in_category`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."uni_banner_in_category_description`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."uni_banner_in_category_to_category`");
		$this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."uni_banner_in_category_to_store`");
	}
}
?>